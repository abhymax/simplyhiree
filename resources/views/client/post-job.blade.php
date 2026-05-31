@extends('layouts.client')

@section('client_content')
    <div class="relative z-10 max-w-3xl mx-auto">
        <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-3xl p-6 shadow-2xl">
            <div class="mb-5 border-b border-white/10 pb-4">
                <span class="px-3 py-1 rounded-full bg-blue-500/20 border border-blue-400/30 text-blue-200 text-xs font-bold uppercase tracking-wider">
                    Client Workspace
                </span>
                <h1 class="text-3xl md:text-4xl font-extrabold mt-3">Post Job</h1>
            </div>

            <form action="/client/post-job" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label for="title" class="block text-sm text-blue-100 mb-1">Job Title</label>
                    <input type="text" name="title" id="title" required class="w-full rounded-xl border border-white/20 bg-slate-900/40 text-white">
                </div>

                <div>
                    <label for="description" class="block text-sm text-blue-100 mb-1">Description</label>
                    <textarea name="description" id="description" required rows="5" class="w-full rounded-xl border border-white/20 bg-slate-900/40 text-white"></textarea>
                </div>

                <div>
                    <label for="category_id" class="block text-sm text-blue-100 mb-1">Category</label>
                    <select name="category_id" id="category_id" required class="w-full rounded-xl border border-white/20 bg-slate-900/40 text-white">
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" class="text-slate-900">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="pt-2">
                    <button type="submit" class="inline-flex items-center px-6 py-2.5 rounded-xl font-bold bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 text-white transition shadow-lg">
                        Post Job
                    </button>
                </div>
            </form>
        </div>
@endsection