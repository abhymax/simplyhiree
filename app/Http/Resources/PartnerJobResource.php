<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PartnerJobResource extends JsonResource
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
            'company_name' => $this->company_name,
            'location' => $this->location,
            'job_type' => $this->job_type,
            'salary' => $this->salary,
            'status' => $this->status,
            'skills_required' => $this->skills_required,
            'category' => [
                'id' => $this->category?->id,
                'name' => $this->category?->name,
            ],
            'experience' => [
                'min' => $this->min_experience,
                'max' => $this->max_experience,
                'level' => $this->experienceLevel?->name,
            ],
            'education' => [
                'id' => $this->educationLevel?->id,
                'name' => $this->educationLevel?->name,
            ],
            'application_deadline' => optional($this->application_deadline)?->toDateString(),
            'created_at' => optional($this->created_at)?->toIso8601String(),
            'partner_applications_count' => (int) ($this->partner_applications_count ?? 0),
        ];
    }
}
