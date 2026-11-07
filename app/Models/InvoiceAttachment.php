<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceAttachment extends Model
{
    protected $fillable = [
        'invoice_id',
        'category',
        'file_name',
        'mime_type',
        'file_size',
        'file_path',
        'file_data',
        'notes',
        'uploaded_by',
    ];

    protected $hidden = [
        'file_data',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'contract' => 'HĐ (Hợp đồng)',
            'quote' => 'BG (Báo giá)',
            'doc' => 'HS liên quan',
            'invoice' => 'Hóa đơn',
            'unc' => 'UNC (Ủy nhiệm chi)',
            default => 'Tài liệu',
        };
    }

    public function getCategoryShortBadgeAttribute(): string
    {
        return match ($this->category) {
            'contract' => '<span class="badge bg-primary-subtle text-primary border rounded-pill px-2 py-1">📑 HĐ</span>',
            'quote' => '<span class="badge bg-warning-subtle text-warning-emphasis border rounded-pill px-2 py-1">📊 BG</span>',
            'doc' => '<span class="badge bg-info-subtle text-info-emphasis border rounded-pill px-2 py-1">📁 HS liên quan</span>',
            'invoice' => '<span class="badge bg-success-subtle text-success border rounded-pill px-2 py-1">📄 Hóa đơn</span>',
            'unc' => '<span class="badge bg-danger-subtle text-danger border rounded-pill px-2 py-1">🏦 UNC</span>',
            default => '<span class="badge bg-secondary-subtle text-secondary border rounded-pill px-2 py-1">📎 Khác</span>',
        };
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = (int)$this->file_size;
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $power = floor(log($bytes, 1024));
        $power = min($power, count($units) - 1);

        return round($bytes / pow(1024, $power), 1) . ' ' . $units[$power];
    }

    public function getIsImageAttribute(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }

    public function getIsPdfAttribute(): bool
    {
        return ($this->mime_type === 'application/pdf') || str_ends_with(strtolower($this->file_name), '.pdf');
    }
}

