<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventRegistrationResource extends JsonResource
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
            'event' => $this->whenLoaded('event', function () {
                return [
                    'id' => $this->event->id,
                    'title' => $this->event->title,
                    'event_date' => $this->event->event_date->format('Y-m-d'),
                    'event_date_formatted' => $this->event->event_date->format('d/m/Y'),
                    'description' => $this->event->description,
                    'featured_image' => $this->event->featured_image,
                ];
            }),
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'phone' => $this->user->phone,
                    'gender' => $this->user->gender,
                    'email' => $this->user->email,
                ];
            }),
            'referral_source' => $this->referral_source,
            'registered_at' => $this->registered_at->format('Y-m-d H:i:s'),
            'registered_at_formatted' => $this->registered_at->format('d/m/Y H:i'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'created_at_formatted' => $this->created_at->format('d/m/Y H:i'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'updated_at_formatted' => $this->updated_at->format('d/m/Y H:i'),
        ];
    }
}
