<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'company_id',
        'partner_id',
        'debt_id',
        'type', // out (đầu ra - bán hàng), in (đầu vào - mua hàng)
        'is_concerned', // boolean: true (Quan tâm: nguyên liệu, hàng hóa...), false (Không quan tâm: tiếp khách, chi phí vặt...)
        'title',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'total_amount',
        'paid_amount',
        'issue_date',
        'due_date',
        'status', // unpaid, paid, cancelled
        'notes',
    ];

    protected $casts = [
        'is_concerned' => 'boolean',
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
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

    public function debt(): BelongsTo
    {
        return $this->belongsTo(Debt::class);
    }

    public function attachments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(InvoiceAttachment::class)->orderBy('id', 'desc');
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, (float)$this->total_amount - (float)$this->paid_amount);
    }
}
