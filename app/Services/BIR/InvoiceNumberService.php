<?php

namespace App\Services\BIR;

use App\Models\Store;
use Illuminate\Support\Facades\DB;

class InvoiceNumberService
{
    /**
     * Get the next persistent Sales Invoice Number for a store.
     * 
     * @param int $storeId
     * @return string
     */
    public function getNext(int $storeId): string
    {
        return DB::transaction(function () use ($storeId) {
            // Lock the store record to prevent race conditions on the SI Number
            $store = Store::where('id', $storeId)->lockForUpdate()->firstOrFail();

            $current = $store->last_si_number;

            // If first time, start at 1, else increment
            // Handling mixed alphanumeric might be tricky, assuming numeric for now based on typical POS
            // Format: 8 digits, zero padded. e.g. 00000001
            $nextVal = (intval($current) > 0) ? intval($current) + 1 : 1;

            $nextSiNumber = str_pad($nextVal, 8, '0', STR_PAD_LEFT);

            $store->last_si_number = $nextSiNumber;
            $store->save();

            return $nextSiNumber;
        });
    }
}
