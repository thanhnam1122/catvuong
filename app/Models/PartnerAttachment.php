<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerAttachment extends Model
{
    protected $fillable = [
        'partner_id',
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

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'license' => 'Giấy phép ĐKKD',
            'contract' => 'HĐ Nguyên Tắc',
            'profile' => 'Hồ Sơ Năng Lực',
            'catalogue' => 'Báo Giá / Catalogue',
            'doc' => 'Tài Liệu Khác',
            default => 'Tài liệu',
        };
    }

    public function getCategoryShortBadgeAttribute(): string
    {
        return match ($this->category) {
            'license' => '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1">📜 ĐKKD</span>',
            'contract' => '<span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-1">📑 HĐ Nguyên Tắc</span>',
            'profile' => '<span class="badge bg-info-subtle text-info-emphasis border border-info-subtle rounded-pill px-2 py-1">🏢 Hồ Sơ NL</span>',
            'catalogue' => '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-1">📊 Báo Giá/Catalogue</span>',
            'doc' => '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2 py-1">📁 Khác</span>',
            default => '<span class="badge bg-light text-secondary border rounded-pill px-2 py-1">📎 File</span>',
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

