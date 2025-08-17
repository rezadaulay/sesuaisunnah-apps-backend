<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventApiResource extends JsonResource
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
            'slug' => $this->slug,
            'description' => $this->description,
            'featured_image' => $this->featured_image,
            'start_date' => $this->start_date?->format('Y-m-d H:i:s'),
            'end_date' => $this->end_date?->format('Y-m-d H:i:s'),
            'location' => $this->location,
            'documentation_desc' => $this->documentation_desc,
            'status' => $this->status,
            'requires_registration' => $this->requires_registration,
            'max_participants' => $this->max_participants,
            'current_participants' => $this->current_participants,
            'registration_opens_at' => $this->registration_opens_at?->format('Y-m-d H:i:s'),
            'registration_closes_at' => $this->registration_closes_at?->format('Y-m-d H:i:s'),
            'created_by' => $this->created_by,
            'creator' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                ];
            }),
            'registrations_count' => $this->whenCounted('registrations', function () {
                return $this->registrations_count;
            }),
            'gallery_count' => $this->whenCounted('gallery', function () {
                return $this->gallery_count;
            }),
            'is_featured' => $this->is_featured,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
