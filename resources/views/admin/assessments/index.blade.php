<x-app-layout>
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10">
        <div class="max-w-6xl mx-auto">
            <div class="mb-8 border-b border-white/10 pb-6">
                <h1 class="text-4xl font-extrabold text-white tracking-tight">Questionnaires</h1>
                <p class="text-blue-200 mt-1 text-lg">Create and manage assessment questionnaires (global library, usable across mandates).</p>
            </div>
            @include('assessments._list')
        </div>
    </div>
</x-app-layout>
