<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ebook extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'cover_image',
        'price',
        'file_url',
        'created_by',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    /**
     * Get the user that created the ebook.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the audiobook files for this ebook.
     */
    public function audiobookFiles(): HasMany
    {
        return $this->hasMany(AudiobookFile::class);
    }

    /**
     * Get the user interactions with this ebook.
     */
    public function interactions(): HasMany
    {
        return $this->hasMany(EbookInteraction::class);
    }

    /**
     * Get the full URL for the cover image.
     */
    public function getFullCoverImageUrlAttribute(): string
    {
        if (!$this->cover_image) {
            return asset('images/default-ebook-cover.jpg');
        }

        if (filter_var($this->cover_image, FILTER_VALIDATE_URL)) {
            return $this->cover_image;
        }
        
        return asset('storage/' . $this->cover_image);
    }

    /**
     * Get the full URL for the ebook file.
     */
    public function getFullFileUrlAttribute(): string
    {
        if (filter_var($this->file_url, FILTER_VALIDATE_URL)) {
            return $this->file_url;
        }
        
        return asset('storage/' . $this->file_url);
    }

    /**
     * Check if the ebook is free.
     */
    public function getIsFreeAttribute(): bool
    {
        return $this->price == 0;
    }

    /**
     * Get the formatted price.
     */
    public function getFormattedPriceAttribute(): string
    {
        if ($this->is_free) {
            return 'Free';
        }
        
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    /**
     * Get the read count for this ebook.
     */
    public function getReadCountAttribute(): int
    {
        return $this->interactions()->where('action', 'read')->count();
    }

    /**
     * Get the download count for this ebook.
     */
    public function getDownloadCountAttribute(): int
    {
        return $this->interactions()->where('action', 'download')->count();
    }

    /**
     * Get the listen count for this ebook.
     */
    public function getListenCountAttribute(): int
    {
        return $this->interactions()->where('action', 'listen')->count();
    }

    /**
     * Get the total interaction count.
     */
    public function getTotalInteractionsAttribute(): int
    {
        return $this->interactions()->count();
    }

    /**
     * Check if the ebook has audiobook files.
     */
    public function getHasAudiobookAttribute(): bool
    {
        return $this->audiobookFiles()->exists();
    }

    /**
     * Get the file extension.
     */
    public function getFileExtensionAttribute(): string
    {
        if (!$this->file_url) {
            return '';
        }
        
        return pathinfo($this->file_url, PATHINFO_EXTENSION);
    }

    /**
     * Check if the ebook is a PDF.
     */
    public function getIsPdfAttribute(): bool
    {
        return strtolower($this->file_extension) === 'pdf';
    }

    /**
     * Check if the ebook is an EPUB.
     */
    public function getIsEpubAttribute(): bool
    {
        return strtolower($this->file_extension) === 'epub';
    }

    /**
     * Get the file size in human readable format.
     */
    public function getHumanFileSizeAttribute(): string
    {
        // This would need to be implemented with actual file size
        // For now, return a placeholder
        return 'Unknown';
    }

    /**
     * Scope to get only free ebooks.
     */
    public function scopeFree($query)
    {
        return $query->where('price', 0);
    }

    /**
     * Scope to get only paid ebooks.
     */
    public function scopePaid($query)
    {
        return $query->where('price', '>', 0);
    }

    /**
     * Scope to get ebooks with audiobooks.
     */
    public function scopeWithAudiobook($query)
    {
        return $query->whereHas('audiobookFiles');
    }

    /**
     * Scope to get popular ebooks based on interactions.
     */
    public function scopePopular($query, $limit = 10)
    {
        return $query->withCount('interactions')
            ->orderBy('interactions_count', 'desc')
            ->limit($limit);
    }
}
