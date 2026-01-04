<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class StoreScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (Auth::check()) {
            // Determine the active store context
            // Default to user's assigned store if session is empty (e.g. API or first login)
            // But strict "active_store_id" session logic is preferred for UI switching.

            $storeId = Session::get('active_store_id');

            if (!$storeId && Auth::user()->store_id) {
                $storeId = Auth::user()->store_id;
            }

            // Fallback to 1 (Master) if nothing found, to ensure *some* valid query for safety?
            // Or maybe don't filter if no store context?
            // Strict isolation implies we MUST filter.
            if ($storeId) {
                $builder->where($model->getTable() . '.store_id', $storeId);
            } else {
                // If no store context found, maybe show nothing or Master?
                // Let's default to Master (1) as a safe fallback
                $builder->where($model->getTable() . '.store_id', 1);
            }
        }
    }
}
