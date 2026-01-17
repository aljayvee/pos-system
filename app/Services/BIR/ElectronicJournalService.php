<?php

namespace App\Services\BIR;

use App\Contracts\BIR\JournalLoggerInterface;
use App\Models\ElectronicJournal;
use Illuminate\Support\Facades\DB;

class ElectronicJournalService implements JournalLoggerInterface
{
    public function log(string $type, ?string $referenceNumber, string $content, int $storeId)
    {
        return ElectronicJournal::create([
            'store_id' => $storeId,
            'type' => $type,
            'reference_number' => $referenceNumber,
            'content' => $content,
            'generated_at' => now(),
        ]);
    }

    public function getLogs(int $storeId, $startDate = null, $endDate = null)
    {
        $query = ElectronicJournal::where('store_id', $storeId)->orderBy('generated_at', 'desc');

        if ($startDate && $endDate) {
            $query->whereBetween('generated_at', [$startDate, $endDate]);
        }

        return $query->get();
    }
}
