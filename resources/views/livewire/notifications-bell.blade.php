<div class="relative ml-3" x-data="{ open: false }">
    <button @click="open = !open" class="relative p-1 text-gray-400 rounded-full hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
        <span class="sr-only">View notifications</span>
        
        <i class="fa-regular fa-bell fa-lg"></i>

        @if($notificationCount > 0)
            <span class="absolute top-0 right-0 block h-2.5 w-2.5 rounded-full bg-red-500 ring-2 ring-white"></span>
        @endif
    </button>

    <div x-show="open" 
         @click.away="open = false" 
         x-transition:enter="transition ease-out duration-200" 
         x-transition:enter-start="transform opacity-0 scale-95" 
         x-transition:enter-end="transform opacity-100 scale-100" 
         x-transition:leave="transition ease-in duration-75" 
         x-transition:leave-start="transform opacity-100 scale-100" 
         x-transition:leave-end="transform opacity-0 scale-95" 
         class="absolute right-0 z-50 mt-2 w-80 origin-top-right bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none" 
         role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button" tabindex="-1">
        
        <div class="flex items-center justify-between px-4 py-2 border-b">
            <h3 class="text-sm font-medium text-gray-700">Notifications</h3>
            @if($notificationCount > 0)
                <button wire:click="markAllAsRead" class="text-xs text-indigo-600 hover:text-indigo-800 focus:outline-none">
                    Mark all as read
                </button>
            @endif
        </div>

        <div class="py-1 max-h-96 overflow-y-auto" role="none">
            @forelse($unreadNotifications as $notification)
                <a href="#" 
                   wire:click.prevent="markAsRead('{{ $notification->id }}')" 
                   class="flex px-4 py-3 text-sm text-gray-700 hover:bg-gray-100" 
                   role="menuitem" tabindex="-1">
                    
                    <div class="flex-shrink-0 mr-3">
                        @if($notification->data['icon'] == 'calendar-event')
                            <i class="fa-solid fa-calendar-alt w-5 h-5 text-blue-500"></i>
                        @elseif($notification->data['icon'] == 'check-circle')
                            <i class="fa-solid fa-circle-check w-5 h-5 text-green-500"></i>
                        @elseif($notification->data['icon'] == 'x-circle')
                            <i class="fa-solid fa-circle-xmark w-5 h-5 text-red-500"></i>
                        @endif
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-700">{{ $notification->data['message'] }}</p>
                        <p class="text-xs text-gray-500">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                </a>
            @empty
                <p class="px-4 py-3 text-sm text-gray-500">You have no unread notifications.</p>
            @endforelse
        </div>
    </div>
</div>