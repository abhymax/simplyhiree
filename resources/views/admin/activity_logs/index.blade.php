<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Superadmin Activity Logs</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <form method="GET" class="bg-white p-4 rounded-lg shadow grid grid-cols-1 md:grid-cols-5 gap-3">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search title/message/actor"
                    class="border-gray-300 rounded-md shadow-sm">

                <input type="text" name="event_key" value="{{ request('event_key') }}" placeholder="Event key"
                    class="border-gray-300 rounded-md shadow-sm">

                <input type="date" name="date_from" value="{{ request('date_from') }}" class="border-gray-300 rounded-md shadow-sm">
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="border-gray-300 rounded-md shadow-sm">

                <button class="inline-flex justify-center items-center rounded-md bg-indigo-600 px-4 py-2 text-white font-semibold hover:bg-indigo-500">
                    Filter Logs
                </button>
            </form>

            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Time</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Event</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Message</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Actor</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">WhatsApp</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($logs as $log)
                                <tr>
                                    <td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ optional($log->occurred_at)->format('d M Y, h:i A') }}</td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900">{{ $log->title }}</div>
                                        <div class="text-xs text-gray-500">{{ $log->event_key }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">{{ $log->message }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $log->actor_name ?? 'System' }}</td>
                                    <td class="px-4 py-3">
                                        @php
                                            $badge = [
                                                'sent' => 'bg-green-100 text-green-700',
                                                'failed' => 'bg-red-100 text-red-700',
                                                'skipped' => 'bg-slate-100 text-slate-700',
                                            ][$log->whatsapp_status] ?? 'bg-slate-100 text-slate-700';
                                        @endphp
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $badge }}">{{ strtoupper($log->whatsapp_status) }}</span>
                                        @if($log->whatsapp_last_error)
                                            <div class="text-xs text-red-500 mt-1">{{ $log->whatsapp_last_error }}</div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-gray-500">No activity logs found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3 border-t border-gray-100">
                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
