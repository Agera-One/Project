<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Documents extends Model
{
    use HasFactory, SoftDeletes;

protected $table = 'documents';

    protected $fillable = [
        'user_id',
        'status_id',
        'title',
        'size',
        'file_path',
        'mime_type',
        'original_name',
        'is_starred',
        'is_archived',
    ];

    protected $casts = [
        'size'        => 'integer',
        'is_starred'  => 'boolean',
        'is_archived' => 'boolean',
    ];

    public function getExtensionAttribute(): string
    {
        return match ($this->mime_type) {
            'application/pdf' => 'pdf',
            'image/jpeg', 'image/jpg' => 'jpeg',
            'image/png' => 'png',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
            'application/vnd.ms-powerpoint' => 'ppt',
            default => pathinfo($this->original_name ?? '', PATHINFO_EXTENSION) ?: 'unknown',
        };
    }

    public function getFileUrlAttribute(): ?string
    {
        return $this->file_path
            ? \Storage::disk('public')->url($this->file_path)
            : null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }
}
