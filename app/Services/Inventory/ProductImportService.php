<?php

namespace App\Services\Inventory;

use App\Models\Product;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class ProductImportService
{
    /**
     * Handle CSV Import
     */
    public function importCsv(UploadedFile $file, int $storeId): int
    {
        $handle = fopen($file->getPathname(), 'r');
        fgetcsv($handle); // Skip header

        $count = 0;

        // Use a transaction from the caller or start one here? 
        // Best practice: Service methods should be atomic or caller handles transaction.
        // We'll handle transaction in the controller for this refactor to avoid nested transaction complexity unless we move it all here.
        // Actually, let's move the logic here but let the controller wrap it in Try/Catch + Transaction if desired, 
        // OR we can wrap it here. Let's wrap it here for safety.

        try {
            while (($row = fgetcsv($handle)) !== false) {
                // Expected CSV Format: Name, Category, Price, Stock, SKU
                $name = $row[0] ?? null;
                $categoryName = $row[1] ?? 'General';
                $price = $row[2] ?? 0;
                $stock = $row[3] ?? 0;
                $sku = $row[4] ?? null;

                if (!$name)
                    continue;

                // Find or Create Category
                $category = Category::firstOrCreate(['name' => trim($categoryName)]);

                // Create Product Record (Scoped to Store)
                $product = Product::create([
                    'store_id' => $storeId,
                    'name' => $name,
                    'category_id' => $category->id,
                    'price' => floatval($price),
                    'stock' => 0, // Legacy field
                    'sku' => $sku,
                ]);

                // Create/Update Inventory
                Inventory::updateOrCreate(
                    ['product_id' => $product->id, 'store_id' => $storeId],
                    ['stock' => intval($stock), 'reorder_point' => 10]
                );

                $count++;
            }

            fclose($handle);
            return $count;

        } catch (\Exception $e) {
            fclose($handle);
            throw $e;
        }
    }

    /**
     * Handle Batch Creation from Form Data
     */
    public function batchCreate(array $productsData, int $storeId): int
    {
        $count = 0;

        foreach ($productsData as $item) {
            // Skip empty rows if name is missing
            if (empty($item['name']))
                continue;

            // Check SKU Uniqueness if SKU is provided
            if (!empty($item['sku'])) {
                $exists = Product::where('store_id', $storeId)->where('sku', $item['sku'])->exists();
                if ($exists) {
                    throw new \Exception("SKU '{$item['sku']}' already exists for product '{$item['name']}'.");
                }
            }

            $product = Product::create([
                'store_id' => $storeId,
                'name' => Str::title($item['name']),
                'category_id' => $item['category_id'],
                'price' => $item['price'],
                'cost' => $item['cost'] ?? null,
                'unit' => $item['unit'],
                'sku' => $item['sku'] ?? null,
                'tax_type' => $item['tax_type'] ?? 'vatable',
                'expiration_date' => $item['expiration_date'] ?? null,
                'stock' => 0, // Legacy
            ]);

            // Create Inventory
            Inventory::create([
                'product_id' => $product->id,
                'store_id' => $storeId,
                'stock' => isset($item['stock']) ? intval($item['stock']) : 0,
                'reorder_point' => isset($item['reorder_point']) ? intval($item['reorder_point']) : 10,
            ]);

            $count++;
        }

        // Log Activity
        if ($count > 0) {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'store_id' => $storeId,
                'action' => 'Batch Created Products',
                'description' => "Batch created {$count} products."
            ]);
        }

        return $count;
    }
}
