<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class JobApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id', 
        'candidate_user_id', 
        'candidate_id', 
        'status',
        'hiring_status',
        'interview_at',
        'client_notes',
        'selected_by_admin_id',
        'selected_by_admin_at',
        'approved_digest_sent_at',
        'meeting_link',
        'meeting_provider',
        'interview_location',
        'interview_reminder_sent_at',
        'interview_rating',
        'interview_feedback',
        'interview_feedback_at',
        'interview_recommendation',
        'joining_date',
        'joined_status',
        'left_at',
        'replacement_requested_at',
        'replacement_reason',
        'auto_forwarded_at',
        'final_ctc',
        'invoice_amount',
        'invoice_generated_at',
        'replacement_window_days',
        'replacement_status',
        'replacement_deadline',
        'replacement_application_id',
        'partner_replacement_window_days',
        'submitted_by_user_id',
        // New Billing Fields
        'payment_status',
        'paid_at',
        'billing_due_alerted_at',
    ];

    protected $casts = [
        'interview_at' => 'datetime',
        'joining_date' => 'datetime',
        'left_at' => 'datetime',
        'paid_at' => 'datetime',
        'billing_due_alerted_at' => 'datetime',
        'replacement_requested_at' => 'datetime',
        'auto_forwarded_at' => 'datetime',
        'invoice_generated_at' => 'datetime',
        'selected_by_admin_at' => 'datetime',
        'approved_digest_sent_at' => 'datetime',
        'interview_reminder_sent_at' => 'datetime',
        'interview_feedback_at' => 'datetime',
        'final_ctc' => 'decimal:2',
        'invoice_amount' => 'decimal:2',
        'replacement_deadline' => 'datetime',
    ];

    /**
     * Get the job associated with the application.
     */
    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    /**
     * Multi-round interview history (up to 5 rounds per application).
     */
    public function interviewRounds()
    {
        return $this->hasMany(InterviewRound::class)->orderBy('round_number');
    }

    /**
     * Get the candidate (from the 'users' table - Direct Applicants).
     */
    public function candidateUser()
    {
        return $this->belongsTo(User::class, 'candidate_user_id');
    }

    /**
     * Get the candidate (from the 'candidates' table - Agency Applicants).
     */
    public function candidate()
    {
        return $this->belongsTo(Candidate::class, 'candidate_id');
    }

    /**
     * Effective invoice-release days for this application:
     *   jobs.invoice_release_days (per-job override)
     *   -> client_commercials.invoice_raise_days (permanent-hiring contract)
     *   -> users.billable_period_days (legacy client default)
     *   -> 30
     */
    public function effectiveInvoiceReleaseDays(): int
    {
        $job = $this->relationLoaded('job') ? $this->getRelation('job') : $this->job;
        if ($job && $job->invoice_release_days !== null) {
            return (int) $job->invoice_release_days;
        }
        $client = $job?->user;
        $commercial = $this->clientCommercial();
        if ($commercial && $commercial->invoice_raise_days !== null) {
            return (int) $commercial->invoice_raise_days;
        }
        if ($client && $client->billable_period_days !== null) {
            return (int) $client->billable_period_days;
        }
        return 30;
    }

    /**
     * Date on which the invoice becomes due. Null until the candidate joins.
     */
    public function invoiceDueAt(): ?\Carbon\Carbon
    {
        if (!$this->joining_date) {
            return null;
        }
        return $this->joining_date->copy()->addDays($this->effectiveInvoiceReleaseDays());
    }

    /**
     * The client_commercials row for this application's client, if any.
     */
    public function clientCommercial(): ?\App\Models\ClientCommercial
    {
        $job = $this->relationLoaded('job') ? $this->getRelation('job') : $this->job;
        $clientId = $job?->user_id;
        if (!$clientId) return null;
        return \App\Models\ClientCommercial::where('user_id', $clientId)->first();
    }

    /**
     * Derive a profile-wise tier from the job's experience band. Used when
     * the client's billing is profile_wise but no explicit override is set.
     */
    public function derivedProfileTier(): string
    {
        $job = $this->relationLoaded('job') ? $this->getRelation('job') : $this->job;
        $years = (int) ($job?->min_experience ?? 0);
        if ($years >= 15) return 'Leader/CXO Level';
        if ($years >= 8)  return 'Sr. Level';
        if ($years >= 3)  return 'Mid-Level';
        return 'Entry Level';
    }

    /**
     * Resolve the per-job commercial outcome for this application.
     * Returns null if final_ctc isn't set or the client has no commercial.
     *
     * Shape:
     *   [
     *     'billing_type'    => 'percentage_based'|'profile_wise'|'flat',
     *     'matched_row'     => array|null,
     *     'fee_percent'     => float|null,
     *     'fee_amount_flat' => float|null,
     *     'invoice_amount'  => float,
     *     'replacement_days'=> int|null,
     *     'invoice_due_at'  => Carbon|null,
     *     'payment_due_at'  => Carbon|null,
     *     'gst_applicable'  => bool,
     *   ]
     */
    public function resolveCommercial(): ?array
    {
        $commercial = $this->clientCommercial();
        if (!$commercial) return null;
        $finalCtc = (float) ($this->final_ctc ?? 0);

        $type = $commercial->billing_type ?? 'percentage_based';
        $data = is_array($commercial->contract_data) ? $commercial->contract_data : [];

        $matched = null;
        $feePercent = null;
        $feeFlat = null;
        $replacementDays = null;
        $invoiceAmount = 0.0;

        if ($type === 'percentage_based') {
            $rows = $data['percentage_based'] ?? [];
            foreach ($rows as $r) {
                $min = $r['min_ctc'] !== null ? (float) $r['min_ctc'] : null;
                $max = $r['max_ctc'] !== null ? (float) $r['max_ctc'] : null;
                $inMin = $min === null || $finalCtc >= $min;
                $inMax = $max === null || $finalCtc <= $max;
                if ($finalCtc > 0 && $inMin && $inMax) {
                    $matched         = $r;
                    $feePercent      = (float) ($r['fee_percent'] ?? 0);
                    $replacementDays = (int) ($r['replacement_days'] ?? 0);
                    $invoiceAmount   = round($finalCtc * $feePercent / 100, 2);
                    break;
                }
            }
        } elseif ($type === 'profile_wise') {
            $rows = $data['profile_wise'] ?? [];
            $tier = $this->derivedProfileTier();
            foreach ($rows as $r) {
                if (strcasecmp((string) ($r['profile'] ?? ''), $tier) === 0) {
                    $matched = $r;
                    break;
                }
            }
            if ($matched) {
                $replacementDays = (int) ($matched['replacement_days'] ?? 0);
                $rowFeeType      = $matched['fee_type'] ?? 'percent';
                if ($rowFeeType === 'flat') {
                    $feeFlat       = (float) ($matched['fee_flat'] ?? 0);
                    $invoiceAmount = $feeFlat;
                } elseif ($finalCtc > 0) {
                    $feePercent    = (float) ($matched['fee_percent'] ?? 0);
                    $invoiceAmount = round($finalCtc * $feePercent / 100, 2);
                }
            }
        } else { // flat
            $rows = $data['flat'] ?? [];
            // If only one flat row, use it. Otherwise the admin/client UI
            // should let the operator pick; for v1 we use the first row.
            if (!empty($rows)) {
                $matched         = $rows[0];
                $feeFlat         = (float) ($matched['fee_amount'] ?? 0);
                $replacementDays = (int) ($matched['replacement_days'] ?? 0);
                $invoiceAmount   = $feeFlat;
            }
        }

        $invoiceDueAt = null;
        $paymentDueAt = null;
        if ($this->joining_date) {
            $invoiceDueAt = $this->joining_date->copy()->addDays((int) ($commercial->invoice_raise_days ?? 30));
            $paymentDueAt = $invoiceDueAt->copy()->addDays((int) ($commercial->payment_terms_days ?? 30));
        }

        // Lock-in: once stamped on the application, prefer the persisted
        // values over live-recomputed ones so contract edits don't rewrite
        // history.
        if ($this->invoice_amount !== null && (float) $this->invoice_amount > 0) {
            $invoiceAmount = (float) $this->invoice_amount;
        }
        if ($this->replacement_window_days !== null) {
            $replacementDays = (int) $this->replacement_window_days;
        }

        return [
            'billing_type'     => $type,
            'matched_row'      => $matched,
            'fee_percent'      => $feePercent,
            'fee_amount_flat'  => $feeFlat,
            'invoice_amount'   => $invoiceAmount,
            'replacement_days' => $replacementDays,
            'invoice_due_at'   => $invoiceDueAt,
            'payment_due_at'   => $paymentDueAt,
            'gst_applicable'   => (bool) ($commercial->is_gst_applicable ?? true),
        ];
    }

    /**
     * Replacement candidate (the one provided in place of this failed hire).
     */
    public function replacementApplication()
    {
        return $this->belongsTo(JobApplication::class, 'replacement_application_id');
    }

    /**
     * Inverse — if this application IS the replacement for another, this points back.
     */
    public function replacesApplication()
    {
        return $this->hasOne(JobApplication::class, 'replacement_application_id');
    }

    public function partnerCreditNote()
    {
        return $this->hasOne(\App\Models\PartnerCreditNote::class, 'source_application_id');
    }

    /**
     * Spec-aligned candidate lifecycle status:
     *   Active             - joined, replacement_status null/none
     *   Left               - joined_status='Left', no replacement requested yet
     *   Replacement Given  - replacement_status='replacement_given' or replacement_application_id set
     *   Credit Pending     - replacement_status='credit_pending'
     *   Closed             - replacement_status='closed' (replacement accepted)
     */
    /**
     * Returns the most-progressed status that should be shown to clients,
     * partners and candidates. The plain `status` field is just the review
     * stage (Pending Review / Approved / Rejected). Once a candidate moves
     * through the hiring journey (interview / selected / joined), those
     * milestones take precedence.
     *
     * Returns one of:
     *   Pending Review | Approved | Rejected
     *   Interview Scheduled | Interviewed | No-Show | Client Rejected
     *   Selected | Selected by Superadmin
     *   Joined | Did Not Join | Left
     */
    public function effectiveStatus(): string
    {
        if (($this->joined_status ?? null) === 'Joined')       return 'Joined';
        if (($this->joined_status ?? null) === 'Left')         return 'Left';
        if (($this->joined_status ?? null) === 'Did Not Join') return 'Did Not Join';

        if (($this->hiring_status ?? null) === 'Selected') {
            return $this->selected_by_admin_id ? 'Selected by Superadmin' : 'Selected';
        }
        if (!empty($this->hiring_status)) {
            return $this->hiring_status; // Interview Scheduled / Interviewed / No-Show / Client Rejected
        }
        return $this->status ?: '—';
    }

    public function candidateStatus(): string
    {
        if ($this->replacement_status === 'closed')             return 'Closed';
        if ($this->replacement_status === 'credit_pending')     return 'Credit Pending';
        if ($this->replacement_status === 'replacement_given')  return 'Replacement Given';
        if ($this->replacement_status === 'window_open')        return 'Replacement Pending';

        if (($this->joined_status ?? null) === 'Left')          return 'Left';
        if (($this->joined_status ?? null) === 'Joined')        return 'Active';
        return ucfirst($this->joined_status ?? '—');
    }

    /**
     * Status bucket for billing views. Falls back gracefully when the
     * commercial isn't configured yet (treats joining_date + effective
     * release days as the invoice-due date).
     *
     *   Maturing      - joined but invoice-due in the future
     *   Due to Raise  - invoice-due reached, invoice_generated_at still null
     *   Raised        - invoice generated, not paid, payment-due in future
     *   Overdue       - invoice generated, not paid, payment-due passed
     *   Paid          - payment_status === 'paid'
     */
    public function billingStatus(): string
    {
        if (($this->payment_status ?? null) === 'paid') {
            return 'Paid';
        }

        $cb = $this->resolveCommercial();
        $invoiceDueAt = $cb['invoice_due_at'] ?? $this->invoiceDueAt();
        $paymentDueAt = $cb['payment_due_at'] ?? null;

        if ($this->invoice_generated_at) {
            if ($paymentDueAt && $paymentDueAt->isPast()) return 'Overdue';
            return 'Raised';
        }

        if ($invoiceDueAt && ($invoiceDueAt->isPast() || $invoiceDueAt->isToday())) {
            return 'Due to Raise';
        }
        return 'Maturing';
    }

    /**
     * Rich row data for the billing screens. Combines the commercial
     * resolver, fallback dates, status bucket, and presentation strings.
     */
    public function billingSnapshot(): array
    {
        $cb = $this->resolveCommercial();
        $invoiceDueAt = $cb['invoice_due_at'] ?? $this->invoiceDueAt();
        $paymentDueAt = $cb['payment_due_at'] ?? null;

        $amount = $cb['invoice_amount'] ?? null;
        if (!$amount && $this->invoice_amount) {
            $amount = (float) $this->invoice_amount;
        }

        return [
            'application'         => $this,
            'candidate_name'      => $this->candidate_name,
            'job_title'           => $this->job?->title,
            'client_name'         => $this->job?->user?->name,
            'joining_date'        => $this->joining_date,
            'final_ctc'           => $this->final_ctc,
            'billing_type'        => $cb['billing_type'] ?? null,
            'matched_row'         => $cb['matched_row'] ?? null,
            'fee_percent'         => $cb['fee_percent'] ?? null,
            'fee_amount_flat'     => $cb['fee_amount_flat'] ?? null,
            'invoice_amount'      => $amount,
            'gst_applicable'      => $cb['gst_applicable'] ?? false,
            'replacement_days'    => $cb['replacement_days'] ?? null,
            'invoice_due_at'      => $invoiceDueAt,
            'payment_due_at'      => $paymentDueAt,
            'invoice_generated_at'=> $this->invoice_generated_at,
            'paid_at'             => $this->paid_at,
            'payment_status'      => $this->payment_status,
            'status'              => $this->billingStatus(),
        ];
    }

    // --- NEW ACCESSOR ---
    /**
     * Get the candidate's full name, regardless of source (User or Agency Candidate).
     * Usage: $application->candidate_name
     */
    protected function candidateName(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                // Check for Agency Candidate first
                if ($this->candidate) {
                    return $this->candidate->first_name . ' ' . $this->candidate->last_name;
                }
                
                // Check for Direct User Candidate
                if ($this->candidateUser) {
                    return $this->candidateUser->name;
                }

                return 'Unknown Candidate';
            }
        );
    }

    protected function applicationCode(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->id ? sprintf('SH-APP-%06d', (int) $this->id) : null,
        );
    }

    protected function hiringCode(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->id ? sprintf('SH-HIR-%06d', (int) $this->id) : null,
        );
    }
}
