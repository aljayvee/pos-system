<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleChangeRequest extends Model
{
    protected $fillable = [
        'requester_id', 'approver_id', 'target_user_id', 'new_role', 'status', 'expires_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime'
    ];

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function targetUser()
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }
}
