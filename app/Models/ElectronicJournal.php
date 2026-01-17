<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ElectronicJournal extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'user_id',
        'type', // e.g., 'SALES_INVOICE', 'Z_READING'
        'reference_number', // SI No
        'generated_at',
        'content', // Full text content
        'data_snapshot', // JSON
        'hash', // For integrity (optional but good)
        'previous_hash',
        'signature'
    ];

    protected $casts = [
        'generated_at' => 'datetime',
        'data_snapshot' => 'array',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
