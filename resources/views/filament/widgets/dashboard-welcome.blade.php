<x-filament-widgets::widget>
    <style>
        .welcome-card-wrapper {
            display: flex;
            flex-wrap: wrap;
            position: relative;
            overflow: hidden;
        }

        .welcome-text-title {
            font-size: 1.5rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .welcome-text-desc {
            font-size: 0.875rem;
            line-height: 1.5;
            margin-bottom: 1.5rem;
        }

        .welcome-user-name {
            font-size: 1.125rem;
            font-weight: 500;
            text-transform: uppercase;
        }

        .welcome-user-info {
            font-size: 0.875rem;
            display: flex;
            gap: 1rem;
            margin-top: 0.25rem;
            flex-wrap: wrap;
        }

        /* Light Mode */
        .welcome-text-title {
            color: #0b9e4f;
        }

        .welcome-text-desc {
            color: #4b5563;
        }

        .welcome-user-name {
            color: #1f2937;
        }

        .welcome-user-info {
            color: #6b7280;
        }

        .welcome-icon {
            color: #9ca3af;
        }

        .welcome-avatar-border {
            border: 2px solid white;
            box-shadow: 0 0 0 2px #34d399, inset 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .welcome-banner-gradient {
            background: linear-gradient(to right, white 0%, rgba(255, 255, 255, 0.7) 40%, rgba(255, 255, 255, 0.2) 100%);
        }

        .welcome-banner-text {
            text-shadow: 2px 2px 0px white, -1px -1px 0px white, 1px -1px 0px white, -1px 1px 0px white;
        }

        .welcome-calendar-bg {
            background-color: rgba(209, 250, 229, 0.9);
            border: 1px solid #6ee7b7;
        }

        /* Dark Mode */
        .dark .welcome-text-title {
            color: #34d399;
        }

        .dark .welcome-text-desc {
            color: #9ca3af;
        }

        .dark .welcome-user-name {
            color: #f3f4f6;
        }

        .dark .welcome-user-info {
            color: #9ca3af;
        }

        .dark .welcome-icon {
            color: #6b7280;
        }

        .dark .welcome-avatar-border {
            border: 2px solid #18181b;
            box-shadow: 0 0 0 2px #059669, inset 0 1px 2px rgba(0, 0, 0, 0.5);
        }

        .dark .welcome-banner-gradient {
            background: linear-gradient(to right, #18181b 0%, rgba(24, 24, 27, 0.7) 40%, rgba(24, 24, 27, 0.2) 100%);
        }

        .dark .welcome-banner-text {
            text-shadow: 2px 2px 0px #18181b, -1px -1px 0px #18181b, 1px -1px 0px #18181b, -1px 1px 0px #18181b;
            color: #10b981 !important;
        }

        .dark .welcome-calendar-bg {
            background-color: rgba(2, 44, 34, 0.8);
            border: 1px solid #064e3b;
        }

        .dark .welcome-subtitle {
            color: #34d399 !important;
        }

        .dark .welcome-hashtag {
            color: #9ca3af !important;
        }

        /* Layout Adjustments */
        .welcome-left {
            flex: 1 1 50%;
            padding: 2rem;
            position: relative;
            z-index: 10;
            min-width: 300px;
        }

        .welcome-right {
            flex: 1 1 50%;
            position: relative;
            min-width: 300px;
            min-height: 200px;
        }

        @media (max-width: 768px) {
            .welcome-left {
                padding: 1.5rem;
            }

            .welcome-right {
                min-height: 180px;
            }
        }
        @keyframes slideDownFadeOut {
            0% { opacity: 0; transform: translate(-50%, -20px); }
            10% { opacity: 1; transform: translate(-50%, 0); }
            80% { opacity: 1; transform: translate(-50%, 0); }
            100% { opacity: 0; transform: translate(-50%, -20px); visibility: hidden; }
        }

        .welcome-overlay {
            position: fixed;
            top: 2rem;
            left: 50%;
            transform: translateX(-50%);
            z-index: 9999;
            width: 90%;
            max-width: 600px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(11, 158, 79, 0.2);
            border-radius: 1rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            animation: slideDownFadeOut 5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            pointer-events: none;
        }

        .dark .welcome-overlay {
            background: rgba(17, 24, 39, 0.95);
            border-color: rgba(52, 211, 153, 0.2);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5);
        }

        .welcome-overlay-content {
            padding: 1.5rem;
            text-align: center;
        }
    </style>

    @if(!session()->has('welcome_shown'))
        @php session()->put('welcome_shown', true); @endphp
        <!-- Floating Overlay -->
        <div id="welcome-floating-overlay" class="welcome-overlay">
            <div class="h-1 w-full" style="background: linear-gradient(90deg, #0B9E4F 0%, #10B981 50%, #34D399 100%);"></div>
            <div class="welcome-overlay-content">
                <h2 class="welcome-text-title" style="margin-bottom: 0.5rem; color: #0b9e4f; font-weight: 700;">Mabuting araw!</h2>
                <p class="welcome-text-desc" style="margin-bottom: 0;">
                    We're thrilled to present our improved Field Portal designed with you in mind.
                    Experience a seamless journey as you access your records, site information,
                    and performance metrics - all at your fingertips.
                </p>
            </div>
        </div>
        
        <script>
            // Ensure the element is completely removed from DOM after animation completes
            setTimeout(() => {
                const el = document.getElementById('welcome-floating-overlay');
                if (el) el.remove();
            }, 5500);
        </script>
    @endif

    <div class="relative overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm">
        {{-- Accent gradient strip --}}
        <div class="h-1.5 w-full" style="background: linear-gradient(90deg, #0B9E4F 0%, #10B981 50%, #34D399 100%);"></div>

        <div class="welcome-card-wrapper">
            <!-- Left Side Data -->
            <div class="welcome-left" style="display: flex; align-items: center;">
                <div class="flex items-center gap-4" style="display: flex; align-items: center; gap: 1rem;">
                    <!-- User Avatar -->
                    <div class="welcome-avatar-border"
                        style="height: 4rem; width: 4rem; border-radius: 9999px; background-color: #10b981; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.25rem; font-weight: bold; overflow: hidden; flex-shrink: 0;">
                        @if($user?->avatar_url)
                            <img src="{{ Storage::url($user->avatar_url) }}" alt="{{ $user->name }}"
                                style="object-fit: cover; width: 100%; height: 100%;" />
                        @else
                            {{ substr($user?->name ?? 'U', 0, 1) }}
                        @endif
                    </div>
                    <div>
                        <h3 class="welcome-user-name">{{ $user?->name ?? 'User' }}</h3>
                        <div class="welcome-user-info">
                            <span style="display: flex; align-items: center; gap: 0.25rem;">
                                <x-heroicon-o-identification class="welcome-icon" style="width: 1rem; height: 1rem;" />
                                Role: {{ $user?->getRoleNames()->first() ?? 'Staff' }}
                            </span>
                            <span style="display: flex; align-items: center; gap: 0.25rem;">
                                <x-heroicon-o-map-pin class="welcome-icon" style="width: 1rem; height: 1rem;" /> Site:
                                {{ $user?->fieldSite?->name ?? 'Global' }}
                            </span>
                            <span style="display: flex; align-items: center; gap: 0.25rem;">
                                <x-heroicon-o-clock class="welcome-icon" style="width: 1rem; height: 1rem;" /> Login:
                                {{ \Carbon\Carbon::parse($user?->last_login_at ?? now())->format('M d, Y h:iA') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Banner Content -->
            <div class="welcome-right">
                <div
                    style="position: absolute; inset: 0; overflow: hidden; border-top-left-radius: 40px; border-bottom-left-radius: 40px; background-color: rgba(0,0,0,0.05);">
                    <img src="{{ asset('images/pca_main_banner1.png') }}" alt="PCA Hybridization Program"
                        style="position: absolute; inset: 0; object-fit: cover; object-position: right top; width: 100%; height: 100%; opacity: 0.9;"
                        onerror="this.style.display='none'" />

                    <!-- Gradient overlay so the text is easily readable on the left -->
                    <div class="welcome-banner-gradient" style="position: absolute; inset: 0;"></div>

                    <div
                        style="position: absolute; inset: 0; display: flex; flex-direction: column; justify-content: center; padding: 1.5rem; padding-left: 2.5rem; z-index: 5;">
                        <span class="welcome-subtitle"
                            style="font-size: 0.75rem; font-weight: bold; color: #0b9e4f; letter-spacing: 0.1em; text-transform: uppercase; margin-bottom: 0.25rem;">Welcome
                            to the</span>
                        <h3 class="welcome-banner-text"
                            style="font-size: 1.6rem; font-weight: 900; color: #064e3b; line-height: 1.15; margin: 0;">
                            PCA HYBRIDIZATION<br />PROGRAM <span style="color: #10b981;">↗</span></h3>
                        <p class="welcome-hashtag"
                            style="font-size: 0.70rem; color: #4b5563; font-weight: 600; font-style: italic; margin-top: 0.5rem;">
                            #SecuringTheFutureOfCoconut</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>