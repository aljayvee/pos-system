<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = ['user_id', 'action', 'description', 'hash', 'previous_hash'];

    protected static function booted()
    {
        static::creating(function ($log) {
            // 1. Ensure created_at is set for consistent hashing
            if (!$log->created_at) {
                $log->setCreatedAt(now());
            }

            // 2. Find previous hash
            // We use raw DB query for speed or model query. 
            // Note: Race condition exists in high web traffic, but acceptable for this use case.
            $lastLog = static::latest('id')->first();
            $log->previous_hash = $lastLog ? $lastLog->hash : 'GENESIS_BLOCK';

            // 3. Generate Hash
            // We use the service logic here or inline it. Inlining is safer for model events to ensure atomic behavior without circular deps.
            $dataToSign = implode('|', [
                $log->user_id ?? 'SYSTEM',
                $log->action,
                $log->description ?? '',
                $log->created_at->toIso8601String(),
                $log->previous_hash,
                config('app.key')
            ]);

            $log->hash = hash('sha256', $dataToSign);
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}