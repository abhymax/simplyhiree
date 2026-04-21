<x-app-layout>
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-900 py-8 px-4">
        <div class="max-w-5xl mx-auto">

            <div class="flex items-center gap-4 mb-8">
                <a href="{{ route('admin.landing-pages.index') }}" class="text-blue-300 hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-white">Create Landing Page</h1>
                    <p class="text-blue-200 mt-1">Build a new conversion-focused landing page</p>
                </div>
            </div>

            @if($errors->any())
            <div class="mb-6 px-4 py-3 bg-rose-500/20 border border-rose-400/30 text-rose-200 rounded-xl">
                <ul class="list-disc list-inside text-sm space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
            @endif

            <form method="POST" action="{{ route('admin.landing-pages.store') }}" enctype="multipart/form-data">
                @csrf
                @php $landingPage = null; @endphp
                @include('admin.landing_pages._form')

                <div class="mt-8 flex gap-4 justify-end">
                    <a href="{{ route('admin.landing-pages.index') }}" class="px-6 py-2.5 bg-white/10 hover:bg-white/20 text-white rounded-xl transition font-semibold">Cancel</a>
                    <button type="submit" class="px-8 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-xl transition shadow-lg">Create Landing Page</button>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>
