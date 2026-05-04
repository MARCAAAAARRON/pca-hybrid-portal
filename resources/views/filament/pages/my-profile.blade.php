<x-filament-panels::page>
    <div class="flex flex-col md:flex-row gap-6 items-start">
        <!-- Profile Info Card (Left) -->
        <div class="w-full md:w-1/3 lg:w-1/4 sticky top-6">
            <div class="relative overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm text-center">
                {{-- Accent gradient strip --}}
                <div class="h-1.5 w-full" style="background: linear-gradient(90deg, #0B9E4F 0%, #10B981 50%, #34D399 100%);"></div>
                
                <div class="flex flex-col items-center p-6">
                    <div class="relative mb-4">
                        @if(auth()->user()->avatar_url)
                            <img src="{{ Storage::url(auth()->user()->avatar_url) }}" alt="Avatar" class="w-24 h-24 rounded-full object-cover border-4 border-primary-50">
                        @else
                            <div class="rounded-full flex items-center justify-center border-8 border-white dark:border-gray-800 shadow-2xl transition-all hover:scale-105 active:scale-95 mb-8" 
                                 style="background: linear-gradient(135deg, #059669 0%, #10b981 100%); width: 160px; height: 160px; min-width: 160px; min-height: 160px;">
                                <span class="font-black text-white tracking-tighter drop-shadow-md" style="font-size: 4rem;">
                                    {{ strtoupper(substr(auth()->user()->first_name ?? 'U', 0, 1) . substr(auth()->user()->last_name ?? '', 0, 1)) }}
                                </span>
                            </div>
                        @endif
                    </div>
                    
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-1">
                        {{ auth()->user()->name ?: auth()->user()->email }}
                    </h2>
                    
                    <x-filament::badge color="success" class="mb-4">
                        {{ auth()->user()->role_display }}
                    </x-filament::badge>
                    
                    <div class="w-full space-y-3 text-sm text-gray-600 dark:text-gray-400">
                        <div class="flex items-center justify-center gap-2">
                            <x-heroicon-m-envelope class="w-4 h-4" />
                            <span class="truncate max-w-[150px]">{{ auth()->user()->email ?: 'No email set' }}</span>
                        </div>
                        
                        @if(auth()->user()->fieldSite)
                            <div class="flex items-center justify-center gap-2">
                                <x-heroicon-m-map-pin class="w-4 h-4" />
                                <span>{{ auth()->user()->fieldSite->name }}</span>
                            </div>
                        @endif

                        @if(auth()->user()->signature_image)
                            <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800">
                                <p class="text-xs uppercase tracking-wider text-gray-500 mb-2">Digital Signature</p>
                                <img src="{{ Storage::url(auth()->user()->signature_image) }}" alt="Signature" class="max-h-12 mx-auto object-contain bg-white rounded p-1 shadow-sm border border-gray-100">
                            </div>
                        @endif
                    </div>

                    <div class="w-full mt-6 pt-6 border-t border-gray-100 dark:border-gray-800 text-xs text-gray-500 space-y-2">
                        <div class="flex justify-between px-2">
                            <span class="font-semibold">Joined:</span>
                            <span>{{ auth()->user()->created_at->format('M j, Y') }}</span>
                        </div>
                        <div class="flex justify-between px-2">
                            <span class="font-semibold">Last Login:</span>
                            <span>{{ now()->format('M j, Y g:i A') }}</span>
                        </div>

                        <div class="pt-4">
                            <x-filament::button 
                                color="danger" 
                                icon="heroicon-m-arrow-left-on-rectangle"
                                wire:click="logout"
                                wire:confirm="Are you sure you want to log out?"
                                class="w-full"
                            >
                                Log Out
                            </x-filament::button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Profile Card (Right) -->
        <div class="w-full md:w-2/3 lg:w-3/4">
            <div class="relative overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm">
                {{-- Accent gradient strip --}}
                <div class="h-1.5 w-full" style="background: linear-gradient(90deg, #0B9E4F 0%, #10B981 50%, #34D399 100%);"></div>
                
                <div class="p-6">
                    <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-100 dark:border-gray-800">
                        <div class="flex items-center justify-center w-10 h-10 rounded-xl" style="background: linear-gradient(135deg, #0B9E4F, #10B981);">
                            <x-heroicon-m-user-circle class="w-5 h-5 text-white" />
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Edit Profile</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Update your account information and settings</p>
                        </div>
                    </div>

                    <form wire:submit="submit" class="space-y-6">
                        {{ $this->form }}

                        <div class="flex justify-end mt-6 pt-5 border-t border-gray-100 dark:border-gray-800">
                            <x-filament::button type="submit" icon="heroicon-m-check" color="success">
                                Save Changes
                            </x-filament::button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
