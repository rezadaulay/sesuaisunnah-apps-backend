<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
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
            'featured_image' => $this->featured_image,
            'event_date' => $this->event_date->format('Y-m-d'),
            'event_date_formatted' => $this->event_date->format('d/m/Y'),
            'documentation_desc' => $this->documentation_desc,
            'created_by' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                ];
            }),
            'participants_count' => $this->whenCounted('registrations', function () {
                return $this->registrations_count;
            }),
            'registrations' => $this->whenLoaded('registrations', function () {
                return $this->registrations->map(function ($registration) {
                    return [
                        'id' => $registration->id,
                        'user' => [
                            'id' => $registration->user->id,
                            'name' => $registration->user->name,
                            'phone' => $registration->user->phone,
                            'gender' => $registration->user->gender,
                        ],
                        'referral_source' => $registration->referral_source,
                        'registered_at' => $registration->registered_at->format('Y-m-d H:i:s'),
                        'registered_at_formatted' => $registration->registered_at->format('d/m/Y H:i'),
                    ];
                });
            }),
            'gallery' => $this->whenLoaded('gallery', function () {
                return $this->gallery->map(function ($photo) {
                    return [
                        'id' => $photo->id,
                        'photo_url' => $photo->full_photo_url,
                    ];
                });
            }),
            'status' => [
                'is_upcoming' => $this->is_upcoming,
                'is_today' => $this->is_today,
                'is_past' => $this->is_past,
                'status_text' => $this->is_today ? 'Today' : ($this->is_upcoming ? 'Upcoming' : 'Past'),
            ],
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'created_at_formatted' => $this->created_at->format('d/m/Y H:i'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'updated_at_formatted' => $this->updated_at->format('d/m/Y H:i'),
        ];
    }
}
