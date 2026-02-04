<div class="relative" x-data="{ open: false }" @click.outside="open = false">
    <button @click="open = !open" class="relative p-2 text-gray-400 hover:text-gray-900 transition-colors group">
        <i data-lucide="bell" class="w-5 h-5 group-hover:scale-110 transition-transform"></i>
        @if($unreadCount > 0)
            <span class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full border-2 border-white"></span>
        @endif
    </button>

    <div x-show="open" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2"
        class="absolute right-0 mt-2 w-96 bg-white rounded-lg shadow-xl  z-50 divide-y divide-gray-100"
        style="display: none;">

        <div class="px-4 py-3 flex items-center justify-between bg-gray-50 rounded-t-lg">
            <span class="text-sm font-semibold text-gray-900">Notifications</span>
            @if($unreadCount > 0)
                <button wire:click="markAllAsRead" class="text-xs font-medium text-indigo-600 hover:text-indigo-800">
                    Mark all as read
                </button>
            @endif
        </div>

        <div class="max-h-96 overflow-y-auto">
            @forelse($notifications as $notification)
                <div
                    class="p-4 hover:bg-gray-50 transition-colors {{ $notification->read_at ? 'opacity-75' : 'bg-blue-50/30' }}">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 mt-1">
                            @if($notification->data['type'] ?? 'info' == 'success')
                                <i data-lucide="check-circle" class="w-4 h-4 text-green-500"></i>
                            @elseif($notification->data['type'] ?? 'info' == 'error')
                                <i data-lucide="alert-circle" class="w-4 h-4 text-red-500"></i>
                            @elseif($notification->data['type'] ?? 'info' == 'warning')
                                <i data-lucide="alert-triangle" class="w-4 h-4 text-yellow-500"></i>
                            @else
                                <i data-lucide="info" class="w-4 h-4 text-blue-500"></i>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">
                                {{ $notification->data['title'] ?? 'Notification' }}
                            </p>
                            <p class="text-xs text-gray-600 mt-0.5 line-clamp-2">
                                {{ $notification->data['message'] ?? '' }}
                            </p>
                            <div class="flex items-center gap-3 mt-2">
                                <span class="text-[10px] text-gray-400">
                                    {{ $notification->created_at->diffForHumans() }}
                                </span>
                                @if(!empty($notification->data['action_url']))
                                    <a href="{{ $notification->data['action_url'] }}"
                                        class="text-[10px] font-medium text-indigo-600 hover:text-indigo-800">
                                        View Details
                                    </a>
                                @endif
                                @if(!$notification->read_at)
                                    <button wire:click="markAsRead('{{ $notification->id }}')"
                                        class="ml-auto text-[10px] text-gray-400 hover:text-gray-600" title="Mark as read">
                                        <i data-lucide="check" class="w-3 h-3"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-400">
                    <i data-lucide="bell-off" class="w-8 h-8 mx-auto mb-2 opacity-50"></i>
                    <p class="text-sm">No notifications yet</p>
                </div>
            @endforelse
        </div>

        <div class="px-4 py-2 border-t border-gray-100 text-center bg-gray-50 rounded-b-lg">
            <a href="#" class="text-xs font-medium text-gray-500 hover:text-gray-900">View all notifications</a>
        </div>
    </div>
</div>