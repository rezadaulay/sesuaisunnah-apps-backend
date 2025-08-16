<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EbookInteraction extends Model
{
    use HasFactory;

    protected $fillable = [
        'ebook_id',
        'user_id',
        'action',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Get the ebook that was interacted with.
     */
    public function ebook(): BelongsTo
    {
        return $this->belongsTo(Ebook::class);
    }

    /**
     * Get the user who performed the interaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get read interactions.
     */
    public function scopeRead($query)
    {
        return $query->where('action', 'read');
    }

    /**
     * Scope to get download interactions.
     */
    public function scopeDownload($query)
    {
        return $query->where('action', 'download');
    }

    /**
     * Scope to get listen interactions.
     */
    public function scopeListen($query)
    {
        return $query->where('action', 'listen');
    }

    /**
     * Scope to get interactions by user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get interactions by ebook.
     */
    public function scopeByEbook($query, $ebookId)
    {
        return $query->where('ebook_id', $ebookId);
    }

    /**
     * Scope to get recent interactions.
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Get the action text in human readable format.
     */
    public function getActionTextAttribute(): string
    {
        return ucfirst($this->action);
    }

    /**
     * Check if this is a read interaction.
     */
    public function getIsReadAttribute(): bool
    {
        return $this->action === 'read';
    }

    /**
     * Check if this is a download interaction.
     */
    public function getIsDownloadAttribute(): bool
    {
        return $this->action === 'download';
    }

    /**
     * Check if this is a listen interaction.
     */
    public function getIsListenAttribute(): bool
    {
        return $this->action === 'listen';
    }
}
