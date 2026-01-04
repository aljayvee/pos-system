<?php

namespace App\Contracts\BIR;

interface JournalLoggerInterface
{
    /**
     * Log an event to the Electronic Journal.
     *
     * @param string $type The type of document (INVOICE, Z-READING, etc.)
     * @param string|null $referenceNumber The unique reference (SI Number, etc.)
     * @param string $content The exact printed content
     * @param int $storeId The store ID
     * @return mixed
     */
    public function log(string $type, ?string $referenceNumber, string $content, int $storeId);
}
