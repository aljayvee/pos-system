<?php

namespace App\Services\POS;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\CustomerCredit;
use App\Models\Inventory;
use App\Services\BIR\InvoiceNumberService;
use App\Services\BIR\DiscountCalculatorService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    protected $invoiceNumberService;
    protected $discountCalculator;

    public function __construct(
        InvoiceNumberService $invoiceNumberService,
        DiscountCalculatorService $discountCalculator
    ) {
        $this->invoiceNumberService = $invoiceNumberService;
        $this->discountCalculator = $discountCalculator;
    }

    /**
     * Process the sale checkout transaction.
     *
     * @param array $data
     * @param int $storeId
     * @param int $userId
     * @return Sale
     * @throws \Exception
     */
    public function processCheckout(array $data, int $storeId, int $userId)
    {
        // Start ACID Transaction
        return DB::transaction(function () use ($data, $storeId, $userId) {

            // 1. IDENTIFY & LOCK CUSTOMER
            $customer = $this->resolveCustomer($data, $storeId);

            // 2. FETCH SETTINGS
            $taxType = \App\Models\Setting::where('key', 'tax_type')->value('value') ?? 'inclusive';
            $taxRate = 0.12;
            $loyaltyEnabled = \App\Models\Setting::where('key', 'enable_loyalty')->value('value') ?? '0';
            $loyaltyRatio = \App\Models\Setting::where('key', 'loyalty_ratio')->value('value') ?? 100;
            $pointsValue = \App\Models\Setting::where('key', 'points_conversion')->value('value') ?? 1;

            // 3. SERVER-SIDE CALCULATION & INVENTORY LOCKING
            $calculatedTotal = 0;
            $validatedItems = [];

            foreach ($data['cart'] as $item) {
                $validatedItem = $this->validateAndLockItem($item, $storeId, $userId);
                $calculatedTotal += ($validatedItem['price'] * $validatedItem['qty']);
                $validatedItems[] = $validatedItem;
            }

            // 4. HANDLE POINTS REDEMPTION
            $pointsUsed = 0;
            $pointsDiscount = 0;

            if ($loyaltyEnabled == '1' && $customer && ($data['points_used'] ?? 0) > 0) {
                $pointsUsed = $data['points_used'];

                if ($customer->points < $pointsUsed) {
                    throw new \Exception("Insufficient points! You have {$customer->points}, but tried to use {$pointsUsed}.");
                }

                $pointsDiscount = $pointsUsed * $pointsValue;
                $customer->decrement('points', $pointsUsed);
            }

            // 5. CALCULATE FINANCIALS (Tax)
            $financials = $this->calculateFinancials($validatedItems, $taxType, $taxRate, $data);

            // Adjust Final Total with Points Discount
            $finalTotal = $financials['finalTotal'];
            $netPayable = max(0, $finalTotal - $pointsDiscount);

            // Verify Payment Amount
            $this->verifyPayment($data, $netPayable);

            // 6. CREATE SALE RECORD
            $sale = Sale::create([
                'store_id' => $storeId,
                'user_id' => $userId,
                'customer_id' => $customer ? $customer->id : null,
                'total_amount' => $finalTotal,
                'vatable_sales' => $financials['totalVatable'],
                'vat_exempt_sales' => $financials['totalVatExempt'],
                'vat_zero_rated_sales' => $financials['totalZeroRated'],
                'vat_amount' => $financials['totalVatAmount'],

                // Discount Details
                'discount_type' => $data['discount']['type'] ?? null,
                'discount_card_no' => $data['discount']['card_no'] ?? null,
                'discount_name' => $data['discount']['name'] ?? null,
                'discount_amount' => $financials['totalDiscountAmount'],

                'amount_paid' => $data['payment_method'] === 'credit' ? 0 : ($data['amount_paid'] ?? 0),
                'payment_method' => $data['payment_method'],
                'reference_number' => $data['payment_method'] === 'digital' ? ($data['reference_number'] ?? null) : null,
                'points_used' => $pointsUsed,
                'points_discount' => $pointsDiscount,
            ]);

            // BIR Invoice Number
            if (config('safety_flag_features.bir_tax_compliance')) {
                $sale->invoice_number = $this->invoiceNumberService->getNext($storeId);
            } else {
                $sale->invoice_number = 'OR-' . strtoupper(uniqid());
            }
            $sale->save();

            // 7. PROCESS VALIDATED CART ITEMS (Stock Deduction & SaleItem Creation)
            foreach ($validatedItems as $item) {
                // Decrement Stock
                $item['inventory']->decrement('stock', $item['qty']);

                // Create Item Record
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['qty'],
                    'price' => $item['price'],
                    'cost' => $item['cost']
                ]);
            }

            // 8. AWARD NEW POINTS
            if ($loyaltyEnabled == '1' && $customer) {
                $pointsEarned = floor($finalTotal / $loyaltyRatio);
                if ($pointsEarned > 0) {
                    $customer->increment('points', $pointsEarned);
                }
            }

            // 9. RECORD CREDIT (Utang)
            if ($data['payment_method'] === 'credit' && $customer) {
                CustomerCredit::create([
                    'customer_id' => $customer->id,
                    'sale_id' => $sale->id,
                    'total_amount' => $finalTotal,
                    'remaining_balance' => $finalTotal,
                    'amount_paid' => 0,
                    'is_paid' => false,
                    'due_date' => $data['credit_details']['due_date'] ?? null,
                ]);
            }

            // 10. ACTIVITY LOG
            \App\Models\ActivityLog::create([
                'user_id' => $userId,
                'store_id' => $storeId,
                'action' => 'Sale Created',
                'description' => "Sale ID: #{$sale->id} | Total: " . number_format($finalTotal, 2) . " | Items: " . count($validatedItems),
            ]);

            // Dispatch Event
            if (config('safety_flag_features.bir_tax_compliance')) {
                \App\Events\SaleCreated::dispatch($sale);
            }

            return $sale;
        });
    }

    private function resolveCustomer($data, $storeId)
    {
        $customerId = $data['customer_id'] ?? null;

        if ($customerId === 'new') {
            return Customer::create([
                'store_id' => $storeId,
                'name' => $data['credit_details']['name'],
                'address' => $data['credit_details']['address'],
                'contact' => $data['credit_details']['contact'],
                'points' => 0
            ]);
        } elseif ($customerId && $customerId !== 'walk-in') {
            $customer = Customer::where('id', $customerId)->lockForUpdate()->first();
            if (!$customer) {
                throw new \Exception("Customer not found.");
            }
            return $customer;
        }

        return null;
    }

    private function validateAndLockItem($item, $storeId, $userId)
    {
        $query = Inventory::with('product')
            ->where('product_id', $item['id'])
            ->where('store_id', $storeId);

        if (DB::getDriverName() !== 'sqlite') {
            $query->lockForUpdate();
        }

        $inventory = $query->first();

        if (!$inventory) {
            $prod = Product::find($item['id']);
            throw new \Exception("Stock record not found for '" . ($prod->name ?? 'Unknown') . "' in this branch.");
        }

        if ($inventory->stock < $item['qty']) {
            throw new \Exception("Insufficient stock for '{$inventory->product->name}'. Available: {$inventory->stock}");
        }

        // Price Override Logic
        $serverPrice = $inventory->product->price;
        $finalPrice = $serverPrice;

        if (isset($item['is_overridden']) && $item['is_overridden']) {
            $user = \App\Models\User::find($userId);
            if ($user && $user->hasPermission(\App\Enums\Permission::PRICE_OVERRIDE)) {
                $finalPrice = $item['price'];
            }
        }

        return [
            'inventory' => $inventory,
            'product_id' => $item['id'],
            'qty' => $item['qty'],
            'price' => $finalPrice,
            'cost' => $inventory->product->cost ?? 0
        ];
    }

    private function calculateFinancials($validatedItems, $storeTaxType, $taxRate, $data)
    {
        $financials = [
            'totalVatable' => 0,
            'totalVatExempt' => 0,
            'totalZeroRated' => 0,
            'totalVatAmount' => 0,
            'totalDiscountAmount' => 0,
            'finalTotal' => 0,
        ];

        foreach ($validatedItems as $item) {
            $product = $item['inventory']->product;
            $taxTypeProd = $product->tax_type ?? 'vatable';
            $lineAmount = $item['price'] * $item['qty'];

            if (!config('safety_flag_features.bir_tax_compliance')) {
                // Generic Mode
                $financials['totalVatExempt'] += $lineAmount;
                $financials['finalTotal'] += $lineAmount;
                continue;
            }

            // BIR Compliance Mode
            $discountType = $data['discount']['type'] ?? 'na'; // Fix: use array access
            $isDiscounted = in_array($discountType, ['sc', 'pwd']);

            if ($isDiscounted) {
                $isInclusive = ($storeTaxType === 'inclusive');
                $calc = $this->discountCalculator->calculate($lineAmount, $taxTypeProd, $discountType, $isInclusive);

                $financials['totalVatExempt'] += $calc['base_price'];
                $financials['totalDiscountAmount'] += $calc['discount_amount'];
                $financials['finalTotal'] += $calc['final_total'];
            } else {
                if ($taxTypeProd === 'vat_exempt') {
                    $financials['totalVatExempt'] += $lineAmount;
                    $financials['finalTotal'] += $lineAmount;
                } elseif ($taxTypeProd === 'zero_rated') {
                    $financials['totalZeroRated'] += $lineAmount;
                    $financials['finalTotal'] += $lineAmount;
                } else {
                    // VATABLE
                    if ($storeTaxType === 'inclusive') {
                        $base = $lineAmount / (1 + $taxRate);
                        $vat = $lineAmount - $base;
                        $financials['totalVatable'] += $base;
                        $financials['totalVatAmount'] += $vat;
                        $financials['finalTotal'] += $lineAmount;
                    } elseif ($storeTaxType === 'exclusive') {
                        $financials['totalVatable'] += $lineAmount;
                        $vat = $lineAmount * $taxRate;
                        $financials['totalVatAmount'] += $vat;
                        $financials['finalTotal'] += ($lineAmount + $vat);
                    } else {
                        // Non-VAT
                        $financials['totalVatExempt'] += $lineAmount; // Or track as non-vat
                        $financials['finalTotal'] += $lineAmount;
                    }
                }
            }
        }

        return $financials;
    }

    private function verifyPayment($data, $netPayable)
    {
        if ($data['payment_method'] === 'cash') {
            $tendered = $data['amount_paid'] ?? 0;
            if (($tendered - $netPayable) < -0.05) {
                throw new \Exception("Insufficient payment amount. Total Due: ₱" . number_format($netPayable, 2) . ", Tendered: ₱" . number_format($tendered, 2));
            }
        }
    }
}
