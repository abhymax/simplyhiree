<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PartnerJobResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $jobCategory = $this->jobCategory;
        $educationLevel = $this->educationLevel;
        $experienceLevel = $this->experienceLevel;

        $categoryName = null;
        if ($jobCategory) {
            $categoryName = $jobCategory->name;
        } elseif (is_string($this->category)) {
            $categoryName = $this->category;
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'company_name' => $this->company_name,
            'company_website' => $this->company_website,
            'location' => $this->location,
            'job_type' => $this->job_type,
            'salary' => $this->salary,
            'status' => $this->status,
            'description' => $this->description,
            'openings' => $this->openings,
            'is_walkin' => (bool) ($this->is_walkin ?? false),
            'skills_required' => $this->skills_required,
            'payout_amount' => $this->payout_amount,
            'minimum_stay_days' => $this->minimum_stay_days,
            'work_mode' => $this->work_mode,
            'shift' => $this->shift,
            'specialization' => $this->specialization,
            'notice_period' => $this->notice_period,
            'languages' => $this->languages,
            'industry' => $this->industry,
            'department' => $this->department,
            'reporting_manager' => $this->reporting_manager,
            'travel_required' => is_null($this->travel_required) ? null : (bool) $this->travel_required,
            'benefits' => is_array($this->benefits) ? $this->benefits : [],
            'category' => [
                'id' => $jobCategory ? $jobCategory->id : $this->category_id,
                'name' => $categoryName,
            ],
            'experience' => [
                'min' => $this->min_experience,
                'max' => $this->max_experience,
                'level' => $experienceLevel ? $experienceLevel->name : null,
            ],
            'education' => [
                'id' => $educationLevel ? $educationLevel->id : null,
                'name' => $educationLevel ? $educationLevel->name : null,
            ],
            'application_deadline' => optional($this->application_deadline)->toDateString(),
            'created_at' => optional($this->created_at)->toIso8601String(),
            'partner_applications_count' => (int) ($this->partner_applications_count ?? 0),
        ];
    }
}
