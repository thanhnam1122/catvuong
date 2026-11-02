<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Debt extends Model
{
    protected $fillable = [
        'code',
        'company_id',
        'partner_id',
        'type', // receivable (Phải thu - Họ nợ tôi), payable (Phải trả - Tôi nợ họ)
        'title',
        'amount',
        'paid_amount',
        'issue_date',
        'due_date',
        'status', // pending, partial, completed, overdue
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'issue_date' => 'date',
        'due_date' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(DebtPayment::class);
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, (float)$this->amount - (float)$this->paid_amount);
    }

    public function updateStatusAutomatically(): void
    {
        $remaining = (float)$this->amount - (float)$this->paid_amount;
        if ($remaining <= 0) {
            $this->status = 'completed';
        } elseif ((float)$this->paid_amount > 0) {
            if ($this->due_date && $this->due_date->isPast()) {
                $this->status = 'overdue';
            } else {
                $this->status = 'partial';
            }
        } else {
            if ($this->due_date && $this->due_date->isPast()) {
                $this->status = 'overdue';
            } else {
                $this->status = 'pending';
            }
        }
        $this->save();
    }
}
