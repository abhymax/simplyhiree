<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PartnerCandidateResource extends JsonResource
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'name' => trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? '')),
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'alternate_phone_number' => $this->alternate_phone_number,
            'location' => $this->location,
            'date_of_birth' => $this->date_of_birth,
            'gender' => $this->gender,
            'experience_status' => $this->experience_status,
            'job_interest' => $this->job_interest,
            'education_level' => $this->education_level,
            'expected_ctc' => $this->expected_ctc,
            'notice_period' => $this->notice_period,
            'job_role_preference' => $this->job_role_preference,
            'languages_spoken' => $this->languages_spoken,
            'skills' => $this->skills,
            'resume_path' => $this->resume_path,
            'created_at' => optional($this->created_at)?->toIso8601String(),
        ];
    }
}
