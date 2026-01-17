<?php

namespace App\Listeners;

use App\Events\SaleCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogSaleToJournal
{
    protected $journalLogger;

    public function __construct(\App\Services\BIR\ElectronicJournalService $journalLogger)
    {
        $this->journalLogger = $journalLogger;
    }

    /**
     * Handle the event.
     */
    public function handle(SaleCreated $event): void
    {
        $sale = $event->sale;

        // Format the Journal Content (Simple text representation for now)
        // In a real ESD/EJ, this should match the printed receipt EXACTLY.
        // We will construct a basic text version here.

        $content = "SI #: " . $sale->invoice_number . "\n";
        $content .= "Date: " . $sale->created_at . "\n";
        $content .= "Total: " . $sale->total_amount . "\n";
        // ... (We can expand this later to be a full replica)

        $this->journalLogger->log(
            'INVOICE',
            $sale->invoice_number,
            $content,
            $sale->store_id
        );
    }
}
