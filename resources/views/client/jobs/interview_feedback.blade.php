@extends('layouts.client')

@section('client_content')
    <div class="max-w-3xl mx-auto">
        <a href="{{ route('client.jobs.applicants', $application->job_id) }}" class="inline-flex items-center text-cyan-300 hover:text-white text-sm font-bold uppercase mb-3">
            <i class="fa-solid fa-arrow-left mr-2"></i> Back to applicants
        </a>

        <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-3xl p-6 md:p-8">
            <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight text-white">Interview Feedback</h1>
            <p class="text-blue-200 mt-1">
                {{ $application->job->title }} —
                <span class="font-bold text-white">{{ $application->candidate ? $application->candidate->first_name . ' ' . $application->candidate->last_name : $application->candidateUser?->name }}</span>
            </p>
            @if($application->interview_at)
                <p class="text-xs text-blue-300 mt-1">Interviewed on {{ $application->interview_at->format('d M Y, h:i A') }}</p>
            @endif

            <form method="POST" action="{{ route('client.applications.feedback.store', $application) }}" class="mt-6 space-y-5">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-blue-200 uppercase tracking-wider mb-2">Overall Rating *</label>
                    <div class="flex items-center gap-1" id="star-row">
                        @for($i = 1; $i <= 5; $i++)
                            <button type="button" onclick="setRating({{ $i }})" class="star-btn text-3xl text-slate-500 hover:text-amber-300 transition" data-val="{{ $i }}">
                                <i class="fa-solid fa-star"></i>
                            </button>
                        @endfor
                        <span id="rating-label" class="ml-2 text-sm text-blue-200">Click a star</span>
                    </div>
                    <input type="hidden" name="interview_rating" id="interview_rating" value="{{ old('interview_rating', $application->interview_rating) }}" required>
                    @error('interview_rating')<p class="text-rose-300 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-blue-200 uppercase tracking-wider mb-2">Detailed Feedback *</label>
                    <textarea name="interview_feedback" rows="6" maxlength="5000" required
                        placeholder="Strengths, gaps, communication, role-fit, anything the team should know..."
                        class="w-full bg-slate-900/40 border border-white/10 rounded-xl text-white px-4 py-3 focus:ring-2 focus:ring-blue-400 focus:border-blue-400">{{ old('interview_feedback', $application->interview_feedback) }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-blue-200 uppercase tracking-wider mb-2">Your Recommendation *</label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        @php $rec = old('interview_recommendation', $application->interview_recommendation); @endphp
                        @foreach([
                            'select'       => ['Select', 'fa-circle-check', 'emerald'],
                            'second_round' => ['Second Round', 'fa-rotate', 'cyan'],
                            'on_hold'      => ['On Hold', 'fa-pause', 'amber'],
                            'reject'       => ['Reject', 'fa-circle-xmark', 'rose'],
                        ] as $val => [$label, $icon, $color])
                            <label class="cursor-pointer flex flex-col items-center gap-1 bg-slate-900/60 border border-white/10 rounded-xl px-3 py-3 {{ $rec === $val ? 'ring-2 ring-' . $color . '-400' : '' }}">
                                <input type="radio" name="interview_recommendation" value="{{ $val }}" {{ $rec === $val ? 'checked' : '' }} class="hidden" required onchange="document.querySelectorAll('label').forEach(l=>l.classList.remove('ring-2','ring-emerald-400','ring-cyan-400','ring-amber-400','ring-rose-400')); this.parentElement.classList.add('ring-2','ring-{{ $color }}-400');">
                                <i class="fa-solid {{ $icon }} text-2xl text-{{ $color }}-300"></i>
                                <span class="text-white font-bold text-sm">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-white/10">
                    <a href="{{ route('client.jobs.applicants', $application->job_id) }}" class="bg-white/10 border border-white/10 text-slate-100 font-bold py-2.5 px-5 rounded-xl">Cancel</a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-extrabold py-2.5 px-7 rounded-xl shadow-lg flex items-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i> Save Feedback
                    </button>
                </div>
            </form>
        </div>
    </div>
<script>
    function setRating(v) {
        document.getElementById('interview_rating').value = v;
        document.getElementById('rating-label').textContent = v + ' / 5';
        document.querySelectorAll('.star-btn').forEach(b => {
            b.classList.toggle('text-amber-300', parseInt(b.dataset.val) <= v);
            b.classList.toggle('text-slate-500', parseInt(b.dataset.val) > v);
        });
    }
    // Init from existing value
    const init = parseInt(document.getElementById('interview_rating').value || 0);
    if (init > 0) setRating(init);
</script>
@endsection
