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
            // 2. Set hash to NULL explicitly (to be processed by Queue)
            $log->hash = null; 
            $log->previous_hash = null; 
        });

        static::created(function ($log) {
            // 3. Dispatch Background Job (Only if enabled)
            if (config('safety_flag_features.log_integrity')) {
                \App\Jobs\ProcessLogHash::dispatch();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}