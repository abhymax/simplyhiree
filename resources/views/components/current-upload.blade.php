@props([
    'path' => null,
    'label' => 'Uploaded file',
    'meta' => null,
])

@if($path)
    @php
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        $isAvailable = $disk->exists($path);
        $url = $isAvailable ? $disk->url($path) : null;
    @endphp

    <div class="mt-2 flex items-center gap-3 rounded-xl border border-cyan-300/20 bg-cyan-400/[0.06] p-3">
        @if($isImage && $isAvailable)
            <img src="{{ $url }}" alt="{{ $label }}" class="h-11 w-11 shrink-0 rounded-lg border border-white/15 object-cover">
        @else
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg border border-white/15 bg-slate-950/50 text-lg {{ $isAvailable ? 'text-cyan-300' : 'text-rose-300' }}">
                <i class="fa-solid {{ $extension === 'pdf' ? 'fa-file-pdf' : 'fa-file-lines' }}"></i>
            </div>
        @endif

        <div class="min-w-0 flex-1">
            <p class="truncate text-xs font-bold text-white">{{ $label }}</p>
            <p class="mt-0.5 text-[11px] {{ $isAvailable ? 'text-emerald-200' : 'text-rose-200' }}">
                {{ $isAvailable ? ($meta ?: strtoupper($extension ?: 'FILE') . ' saved to your profile') : 'Stored file is currently unavailable' }}
            </p>
        </div>

        @if($isAvailable)
            <div class="flex shrink-0 items-center gap-1.5">
                <a href="{{ $url }}" target="_blank" rel="noopener" title="View {{ $label }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-cyan-300/30 bg-cyan-400/10 text-cyan-200 transition hover:bg-cyan-400/20 hover:text-white">
                    <i class="fa-solid fa-eye"></i>
                    <span class="sr-only">View {{ $label }}</span>
                </a>
                <a href="{{ $url }}" download title="Download {{ $label }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-violet-300/30 bg-violet-400/10 text-violet-200 transition hover:bg-violet-400/20 hover:text-white">
                    <i class="fa-solid fa-download"></i>
                    <span class="sr-only">Download {{ $label }}</span>
                </a>
            </div>
        @endif
    </div>
@endif
