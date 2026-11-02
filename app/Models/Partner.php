<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Partner extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'type', // customer, supplier, both
        'phone',
        'email',
        'address',
        'notes',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function debts(): HasMany
    {
        return $this->hasMany(Debt::class);
    }
}
