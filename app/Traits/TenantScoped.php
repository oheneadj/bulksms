<?php

namespace App\Traits;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait TenantScoped
{
    protected static function bootTenantScoped(): void
    {
        static::addGlobalScope('tenant', function (Builder $query) {
            if (Auth::check() && Auth::user()->tenant_id) {
                $query->where('tenant_id', Auth::user()->tenant_id);
            }
        });

        static::creating(function ($model) {
            if (Auth::check() && !$model->tenant_id) {
                $model->tenant_id = Auth::user()->tenant_id;
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
