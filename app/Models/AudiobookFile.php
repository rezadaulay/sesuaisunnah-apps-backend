<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AudiobookFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'ebook_id',
        'name',
        'file_url',
        'order_number',
    ];

    protected $casts = [
        'order_number' => 'integer',
    ];

    /**
     * Get the ebook that owns this audiobook file.
     */
    public function ebook(): BelongsTo
    {
        return $this->belongsTo(Ebook::class);
    }

    /**
     * Get the full URL for the audio file.
     */
    public function getFullFileUrlAttribute(): string
    {
        if (filter_var($this->file_url, FILTER_VALIDATE_URL)) {
            return $this->file_url;
        }
        
        return asset('storage/' . $this->file_url);
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
     * Check if the file is an MP3.
     */
    public function getIsMp3Attribute(): bool
    {
        return strtolower($this->file_extension) === 'mp3';
    }

    /**
     * Check if the file is an M4A.
     */
    public function getIsM4aAttribute(): bool
    {
        return strtolower($this->file_extension) === 'm4a';
    }

    /**
     * Check if the file is an AAC.
     */
    public function getIsAacAttribute(): bool
    {
        return strtolower($this->file_extension) === 'aac';
    }

    /**
     * Get the display name for the audio file.
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->name) {
            return $this->name;
        }
        
        return "Chapter " . ($this->order_number ?? 'Unknown');
    }

    /**
     * Get the duration in human readable format.
     */
    public function getHumanDurationAttribute(): string
    {
        // This would need to be implemented with actual audio duration
        // For now, return a placeholder
        return 'Unknown';
    }

    /**
     * Scope to order by order number.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order_number', 'asc');
    }

    /**
     * Scope to get files by extension.
     */
    public function scopeByExtension($query, $extension)
    {
        return $query->where('file_url', 'like', '%.' . $extension);
    }
}
