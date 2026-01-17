<?php

namespace App\Services\BIR;

use App\Models\Store;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ZReadingProcessor
{
    /**
     * Generate a Z-Reading for the day.
     * This resets the daily accumulated sales (via logic) but increments the Grand Total (Persistent).
     * 
     * @param int $storeId
     * @return array
     */
    public function generate(int $storeId)
    {
        return DB::transaction(function () use ($storeId) {
            $store = Store::where('id', $storeId)->lockForUpdate()->firstOrFail();

            // 1. Calculate Today's Sales
            // In a real Z-reading, this should be "Sales since last Z-Reading".
            // For simplicity/demo, using "Sales Date = Today".
            // Ideally, we'd check the `generated_at` of the last Z-Reading from ElectronicJournal.

            $lastZReading = \App\Models\ElectronicJournal::where('store_id', $storeId)
                ->where('type', 'Z-READING')
                ->latest('generated_at')
                ->first();

            $startDate = $lastZReading ? $lastZReading->generated_at : Carbon::now()->startOfDay(); // Fallback if no previous Z

            // Query sales strictly AFTER the last Z-reading
            $salesData = Sale::where('store_id', $storeId)
                ->where('created_at', '>', $startDate)
                ->selectRaw('
                    COUNT(*) as trans_count,
                    SUM(total_amount) as net_sales,
                    SUM(vatable_sales) as vatable_sales,
                    SUM(vat_amount) as vat_amount,
                    SUM(vat_exempt_sales) as vat_exempt_sales,
                    SUM(vat_zero_rated_sales) as vat_zero_rated_sales,
                    MIN(invoice_number) as beg_si,
                    MAX(invoice_number) as end_si
                ')
                ->first();

            // Handle no sales
            $grossSales = $salesData->net_sales ?? 0;

            // 2. Capture Accumulators
            $oldGrandTotal = $store->accumulated_grand_total;
            $newGrandTotal = $oldGrandTotal + $grossSales;

            // 3. Update Persistent Accumulators
            $store->accumulated_grand_total = $newGrandTotal;
            $store->z_reading_counter += 1;
            $store->save();

            // 4. Construct Data
            $zData = [
                'store_name' => $store->name,
                'tin' => $store->tin ?? $store->settings()->where('key', 'store_tin')->value('value'),
                'min' => $store->min_number,
                'serial' => $store->serial_number,
                'date' => now()->format('Y-m-d H:i:s'),
                'z_counter' => $store->z_reading_counter,
                'beg_si' => $salesData->beg_si ?? 'N/A',
                'end_si' => $salesData->end_si ?? 'N/A',
                'trans_count' => $salesData->trans_count ?? 0,
                'gross_sales' => $grossSales,
                'vatable_sales' => $salesData->vatable_sales ?? 0,
                'vat_amount' => $salesData->vat_amount ?? 0,
                'vat_exempt' => $salesData->vat_exempt_sales ?? 0,
                'zero_rated' => $salesData->vat_zero_rated_sales ?? 0,
                'old_grand_total' => $oldGrandTotal,
                'new_grand_total' => $newGrandTotal,
            ];

            // 5. Log to Electronic Journal
            \App\Models\ElectronicJournal::create([
                'store_id' => $storeId,
                'user_id' => auth()->id(), // Assuming triggered by auth user
                'type' => 'Z-READING',
                'generated_at' => now(),
                'data_snapshot' => $zData, // Casts to JSON automatically if model configured
                'content' => json_encode($zData) // Placeholder or actual text format
            ]);

            return $zData;
        });
    }
}
