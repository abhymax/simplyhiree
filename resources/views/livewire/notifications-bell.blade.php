<div class="relative" style="z-index: 2147483646; isolation: isolate;" x-data="{ open: false }">
    <button @click="open = !open" type="button" class="relative flex h-10 w-10 items-center justify-center rounded-xl border border-white/10 bg-slate-950/35 text-slate-200 transition hover:-translate-y-0.5 hover:border-cyan-300/45 hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-cyan-300/45">
        <span class="sr-only">View notifications</span>
        <i class="fa-regular fa-bell fa-lg"></i>
        @if($notificationCount > 0)
            <span class="absolute top-1.5 right-1.5 block h-2.5 w-2.5 rounded-full bg-rose-400 ring-2 ring-slate-950 animate-pulse"></span>
        @endif
    </button>

    <template x-teleport="body">
    <div x-show="open" 
         @click.away="open = false" 
         class="w-80 origin-top-right overflow-hidden rounded-2xl border border-cyan-200/30 shadow-2xl focus:outline-none"
         style="position: fixed; top: 84px; right: 136px; z-index: 2147483647; background: linear-gradient(145deg, #102a62, #091632 68%, #071022); box-shadow: 0 24px 58px rgba(1, 8, 28, .72);"
         role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button" tabindex="-1"
         x-transition:enter="transition ease-out duration-200" 
         x-transition:enter-start="transform opacity-0 scale-95" 
         x-transition:enter-end="transform opacity-100 scale-100" 
         x-transition:leave="transition ease-in duration-75" 
         x-transition:leave-start="transform opacity-100 scale-100" 
         x-transition:leave-end="transform opacity-0 scale-95">
        
        <div class="flex items-center justify-between border-b border-white/10 px-4 py-3">
            <h3 class="text-sm font-bold text-white">Notifications</h3>
            @if($notificationCount > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">@csrf
                    <button type="submit" class="text-xs font-semibold text-cyan-300 hover:text-white focus:outline-none">Mark all as read</button>
                </form>
            @endif
        </div>

        <div class="py-1 max-h-96 overflow-y-auto" role="none">
            @forelse($notifications as $notification)
                @php($notificationIcon = $notification->data['icon'] ?? null)
                <form method="POST" action="{{ route('notifications.read', $notification->id) }}" class="border-b border-white/5" role="none">@csrf
                    <button type="submit" class="flex w-full px-4 py-3 text-left text-sm text-slate-200 transition hover:bg-white/5" role="menuitem" tabindex="-1">
                    
                    <div class="flex-shrink-0 mr-3">
                        @if($notificationIcon === 'calendar-event')
                            <i class="fa-solid fa-calendar-alt w-5 h-5 text-blue-500"></i>
                        @elseif($notificationIcon === 'check-circle')
                            <i class="fa-solid fa-circle-check w-5 h-5 text-green-500"></i>
                        @elseif($notificationIcon === 'x-circle')
                            <i class="fa-solid fa-circle-xmark w-5 h-5 text-red-500"></i>
                        @elseif($notificationIcon === 'user-check')
                            <i class="fa-solid fa-user-check w-5 h-5 text-green-500"></i>
                        @elseif($notificationIcon === 'user-xmark')
                            <i class="fa-solid fa-user-xmark w-5 h-5 text-red-500"></i>
                        @elseif($notificationIcon === 'user-clock')
                            <i class="fa-solid fa-user-clock w-5 h-5 text-gray-500"></i>
                        @else
                            <i class="fa-solid fa-circle-info w-5 h-5 text-indigo-500"></i>
                        @endif
                    </div>
                    
                    <div>
                        <p class="text-sm text-slate-100">{{ $notification->data['message'] ?? 'New notification' }}</p>
                        <p class="text-xs text-slate-400">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                    </button>
                </form>
            @empty
                <p class="px-4 py-5 text-sm text-slate-400">You have no notifications yet.</p>
            @endforelse
        </div>
        @if(auth()->user()?->hasRole('client'))
            <div class="border-t border-white/10 bg-slate-950/30 px-4 py-3 text-center">
                <a href="{{ route('client.notifications.index') }}" class="text-xs font-extrabold text-cyan-200 transition hover:text-white">View all notifications</a>
            </div>
        @endif
    </div>
    </template>
</div>
