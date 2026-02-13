<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PartnerApplicationResource extends JsonResource
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
            'status' => $this->status,
            'hiring_status' => $this->hiring_status,
            'joined_status' => $this->joined_status,
            'interview_at' => optional($this->interview_at)?->toIso8601String(),
            'applied_at' => optional($this->created_at)?->toIso8601String(),
            'job' => [
                'id' => $this->job?->id,
                'title' => $this->job?->title,
                'company_name' => $this->job?->company_name,
                'location' => $this->job?->location,
            ],
            'candidate' => [
                'id' => $this->candidate?->id,
                'name' => trim(($this->candidate?->first_name ?? '') . ' ' . ($this->candidate?->last_name ?? '')),
                'email' => $this->candidate?->email,
                'phone_number' => $this->candidate?->phone_number,
            ],
        ];
    }
}
