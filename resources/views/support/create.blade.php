@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-900 text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-12 relative overflow-hidden">

    <div class="absolute top-0 left-0 w-96 h-96 bg-blue-500 rounded-full mix-blend-overlay filter blur-[100px] opacity-20 animate-pulse"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-purple-500 rounded-full mix-blend-overlay filter blur-[100px] opacity-20"></div>

    <div class="relative z-10 max-w-3xl mx-auto">

        <div class="mb-8 border-b border-white/10 pb-6">
            <div class="flex items-center gap-2 mb-2">
                <span class="px-3 py-1 rounded-full bg-blue-500/20 border border-blue-400/30 text-blue-200 text-xs font-bold uppercase tracking-wider">
                    Help &amp; Support
                </span>
            </div>
            <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight text-white">Contact Support</h1>
            <p class="text-blue-200 mt-2">Describe your issue and our team will get back to you on <span class="text-white font-semibold">{{ Auth::user()->email }}</span>.</p>
        </div>

        @if(session('success'))
            <div class="mb-5 p-4 rounded-xl bg-emerald-500/15 border border-emerald-400/40 text-emerald-100">
                <i class="fa-solid fa-circle-check mr-1"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-5 p-4 rounded-xl bg-rose-500/15 border border-rose-400/40 text-rose-100">
                <i class="fa-solid fa-triangle-exclamation mr-1"></i> {{ session('error') }}
            </div>
        @endif
        @if($errors->any())
            <div class="mb-5 p-4 rounded-xl bg-rose-500/15 border border-rose-400/40 text-rose-100">
                <p class="font-bold"><i class="fa-solid fa-triangle-exclamation mr-1"></i> Please fix the following:</p>
                <ul class="list-disc ml-6 mt-1 text-sm">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-3xl p-6 md:p-8 shadow-lg">
            <form method="POST" action="{{ route('support.submit') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-blue-200 uppercase tracking-wider mb-2">Subject</label>
                    <input type="text" name="subject" maxlength="200" required
                        value="{{ old('subject') }}"
                        placeholder="e.g. Cannot upload candidate resume"
                        class="w-full bg-slate-900/40 border border-white/10 rounded-xl text-white px-4 py-3 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-blue-200 uppercase tracking-wider mb-2">Describe Your Issue</label>
                    <textarea name="message" rows="7" maxlength="5000" required
                        placeholder="Tell us what's happening, what you expected, and the steps to reproduce..."
                        class="w-full bg-slate-900/40 border border-white/10 rounded-xl text-white px-4 py-3 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition">{{ old('message') }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-blue-200 uppercase tracking-wider mb-2">Attach Screenshots (Optional)</label>
                    <input type="file" name="attachments[]" multiple accept=".png,.jpg,.jpeg,.gif,.webp,.pdf"
                        class="block w-full text-sm text-slate-200 file:mr-3 file:py-2 file:px-4 file:rounded-md file:border-0 file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-500">
                    <p class="mt-1 text-xs text-slate-400">Up to 5 files. PNG, JPG, GIF, WebP, or PDF. Max 5MB each.</p>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-white/10">
                    <a href="javascript:history.back()" class="bg-white/10 border border-white/10 text-slate-100 font-bold py-2.5 px-5 rounded-xl hover:bg-white/20 transition">
                        Cancel
                    </a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-2.5 px-6 rounded-xl transition flex items-center gap-2 shadow-lg hover:shadow-blue-500/40">
                        <i class="fa-solid fa-paper-plane"></i> Send to Support
                    </button>
                </div>
            </form>
        </div>

        <div class="mt-6 text-center text-slate-300 text-sm">
            Or email us directly at <a href="mailto:{{ env('SUPPORT_EMAIL', 'support@simplyhiree.com') }}" class="text-blue-300 hover:text-white font-semibold">{{ env('SUPPORT_EMAIL', 'support@simplyhiree.com') }}</a>
        </div>
    </div>
</div>
@endsection
