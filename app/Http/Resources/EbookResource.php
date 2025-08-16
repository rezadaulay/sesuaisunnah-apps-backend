<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EbookResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'cover_image' => $this->cover_image,
            'cover_image_url' => $this->full_cover_image_url,
            'price' => $this->price,
            'price_formatted' => $this->formatted_price,
            'is_free' => $this->is_free,
            'file_url' => $this->file_url,
            'file_url_full' => $this->full_file_url,
            'file_extension' => $this->file_extension,
            'is_pdf' => $this->is_pdf,
            'is_epub' => $this->is_epub,
            'human_file_size' => $this->human_file_size,
            
            // Creator information
            'creator' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                ];
            }),
            
            // Audiobook information
            'has_audiobook' => $this->has_audiobook,
            'audiobook_files_count' => $this->whenCounted('audiobookFiles', function () {
                return $this->audiobook_files_count;
            }),
            'audiobook_files' => $this->whenLoaded('audiobookFiles', function () {
                return $this->audiobookFiles->map(function ($audioFile) {
                    return [
                        'id' => $audioFile->id,
                        'name' => $audioFile->name,
                        'display_name' => $audioFile->display_name,
                        'file_url' => $audioFile->file_url,
                        'file_url_full' => $audioFile->full_file_url,
                        'file_extension' => $audioFile->file_extension,
                        'order_number' => $audioFile->order_number,
                        'is_mp3' => $audioFile->is_mp3,
                        'is_m4a' => $audioFile->is_m4a,
                        'is_aac' => $audioFile->is_aac,
                        'human_duration' => $audioFile->human_duration,
                    ];
                });
            }),
            
            // Interaction statistics
            'interactions_count' => $this->whenCounted('interactions', function () {
                return $this->interactions_count;
            }),
            'read_count' => $this->read_count,
            'download_count' => $this->download_count,
            'listen_count' => $this->listen_count,
            'total_interactions' => $this->total_interactions,
            
            // Recent interactions
            'recent_interactions' => $this->whenLoaded('interactions', function () {
                return $this->interactions->map(function ($interaction) {
                    return [
                        'id' => $interaction->id,
                        'action' => $interaction->action,
                        'action_text' => $interaction->action_text,
                        'user' => $interaction->whenLoaded('user', function () use ($interaction) {
                            return [
                                'id' => $interaction->user->id,
                                'name' => $interaction->user->name,
                            ];
                        }),
                        'created_at' => $interaction->created_at->format('Y-m-d H:i:s'),
                        'created_at_formatted' => $interaction->created_at->format('d/m/Y H:i'),
                    ];
                });
            }),
            
            // Timestamps
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'created_at_formatted' => $this->created_at->format('d/m/Y H:i'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'updated_at_formatted' => $this->updated_at->format('d/m/Y H:i'),
        ];
    }
}
