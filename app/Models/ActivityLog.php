<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = ['user_id', 'action', 'description', 'hash', 'previous_hash'];

    protected static function booted()
    {
        static::creating(function ($log) {
            // Start Transaction to ensure atomicity of previous_hash fetch + insert
            // This prevents race conditions where multiple logs pick the same previous_hash
            \Illuminate\Support\Facades\DB::beginTransaction();

            try {
                // 1. Ensure created_at is set for consistent hashing
                if (!$log->created_at) {
                    $log->setCreatedAt(now());
                }

                // 2. Find previous hash with PESSIMISTIC WRITE LOCK
                // lockForUpdate() ensures no other transaction can read/write this row until we commit
                $lastLog = static::lockForUpdate()->latest('id')->first();
                $log->previous_hash = $lastLog ? $lastLog->hash : 'GENESIS_BLOCK';

                // 3. Generate Hash
                $dataToSign = implode('|', [
                    $log->user_id ?? 'SYSTEM',
                    $log->action,
                    $log->description ?? '',
                    $log->created_at->toIso8601String(),
                    $log->previous_hash,
                    config('app.key')
                ]);

                $log->hash = hash('sha256', $dataToSign);
                
            } catch (\Exception $e) {
                // If anything goes wrong during hash generation, rollback
                \Illuminate\Support\Facades\DB::rollBack();
                throw $e;
            }
        });

        static::created(function ($log) {
            // Commit the transaction after successful insert
            \Illuminate\Support\Facades\DB::commit();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}