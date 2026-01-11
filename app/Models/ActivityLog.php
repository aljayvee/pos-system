<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = ['user_id', 'action', 'description', 'hash', 'previous_hash'];

    protected static function booted()
    {
        static::creating(function ($log) {
            // 1. Ensure created_at is strictly set BEFORE hashing
            if (!$log->created_at) {
                $log->setCreatedAt(now());
            }

            // 2. Fetch Previous Hash (Critical for Chain)
            // We must find the VERY last record committed to DB.
            $lastLog = static::latest('id')->first();
            $log->previous_hash = $lastLog ? $lastLog->hash : 'GENESIS_BLOCK';

            // 3. Generate HMAC Hash
            // We use the service directly here to ensure the hash is generated with the EXACT Same logic
            // Note: We avoid Dependency Injection in model events if possible, but for simplicity:
            $service = app(\App\Services\LogIntegrityService::class);
            $log->hash = $service->generateHash($log);
        });

        static::created(function ($log) {
            // 4. (Optional) We could double check or backup here, 
            // but 'creating' set the hash, so we are good.
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}