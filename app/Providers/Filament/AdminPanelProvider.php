<?php

namespace App\Providers\Filament;

use App\Filament\AvatarProviders\PcaAvatarProvider;
use App\Filament\Pages\Login;
use App\Models\User;
use App\Settings\KaidoSetting;
use Filament\Http\Middleware\Authenticate;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use DutchCodingCompany\FilamentSocialite\FilamentSocialitePlugin;
use DutchCodingCompany\FilamentSocialite\Provider;
use Filament\Forms\Components\FileUpload;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Hasnayeen\Themes\Http\Middleware\SetTheme;
use Hasnayeen\Themes\ThemesPlugin;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Jeffgreco13\FilamentBreezy\BreezyCore;
use Rupadana\ApiService\ApiServicePlugin;

use Laravel\Socialite\Contracts\User as SocialiteUserContract;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Schema;

class AdminPanelProvider extends PanelProvider
{
    private ?KaidoSetting $settings = null;
    //constructor
    public function __construct()
    {
        //this is feels bad but this is the solution that i can think for now :D
        // Check if settings table exists first
        try {
            if (
                \Illuminate\Support\Facades\Schema::hasTable('settings')
                && \Illuminate\Support\Facades\DB::table('settings')->count() > 0
            ) {
                $this->settings = app(KaidoSetting::class);
            }
        } catch (\Throwable $e) {
            $this->settings = null;
        }
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('portal')
            ->when($this->settings->login_enabled ?? true, fn($panel) => $panel->login(\App\Filament\Pages\Auth\CustomLogin::class))
            ->when($this->settings->registration_enabled ?? false, fn($panel) => $panel->registration())
            ->when($this->settings->password_reset_enabled ?? true, fn($panel) => $panel->passwordReset())
            ->brandLogo(fn() => new \Illuminate\Support\HtmlString('
                <div class="flex items-center gap-2">
                    <img src="' . asset('images/PCA_Logo.png') . '" class="h-8 w-auto shrink-0" alt="PCA Logo" />
                    <span class="pca-logo-text">PCA Hybridization Portal</span>
                </div>
            '))
            ->colors([
                'primary' => Color::hex('#0b9e4f'), // PCA Green
                'warning' => Color::hex('#dfed1f'), // PCA Yellow
                'success' => Color::hex('#028c42'), // PCA Dark Green
            ])
            ->font('Sora', 'https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&display=swap')
            ->defaultAvatarProvider(PcaAvatarProvider::class)
            ->renderHook(
                \Filament\View\PanelsRenderHook::HEAD_END,
                function (): string {
                    $html = <<<HTML
<style>
    /* --- Brand Logo Text (prevent wrapping on mobile) --- */
    .pca-logo-text {
        white-space: nowrap;
        font-weight: 700;
        font-size: 1rem;
    }
    
    /* --- Global Primary Color Override (PCA Green) --- */
    :root {
        --primary-50: 236, 253, 245 !important;
        --primary-100: 209, 250, 229 !important;
        --primary-200: 167, 243, 208 !important;
        --primary-300: 110, 231, 183 !important;
        --primary-400: 52, 211, 153 !important;
        --primary-500: 16, 185, 129 !important;
        --primary-600: 11, 158, 79 !important; /* #0b9e4f */
        --primary-700: 4, 120, 87 !important;
        --primary-800: 6, 95, 70 !important;
        --primary-900: 6, 78, 59 !important;
        --primary-950: 2, 44, 34 !important;
    }
    
    /* --- Coconut Theme Background --- */
    body::before {
        content: "";
        position: fixed;
        inset: 0;
        background-image: url("/images/coconut_farm.png");
        background-repeat: no-repeat;
        background-size: cover;
        background-position: center bottom;
        opacity: 0.45;
        z-index: -10;
        pointer-events: none;
    }
    
    /* Ensure all content containers remain solid above the background */
    .fi-section, .fi-modal-window, .fi-ta-content, .fi-wi-stats-overview-stat { background-color: #ffffff !important; }
    .dark .fi-section, .dark .fi-modal-window, .dark .fi-ta-content, .dark .fi-wi-stats-overview-stat { background-color: #111827 !important; }
    
    
    /* Sidebar Base */
    .fi-sidebar { background-color: #0b9e4f !important; border-right: none !important; }
    .fi-sidebar-header { background-color: #0b9e4f !important; border-bottom: 1px solid rgba(255,255,255,0.1) !important; container-type: inline-size; }
    .fi-sidebar .fi-sidebar-item-label, .fi-sidebar .fi-sidebar-item-icon { color: #ffffff !important; }
    .fi-sidebar .fi-sidebar-item-button:hover { background-color: #d4e122 !important; }
    .fi-sidebar .fi-sidebar-item-button:hover .fi-sidebar-item-label, .fi-sidebar .fi-sidebar-item-button:hover .fi-sidebar-item-icon { color: #0b9e4f !important; }
    .fi-sidebar .fi-sidebar-item-active > .fi-sidebar-item-button { background-color: #d4e122 !important; box-shadow: 0 4px 12px rgba(212, 225, 34, 0.3) !important; }
    .fi-sidebar .fi-sidebar-item-active > .fi-sidebar-item-button .fi-sidebar-item-label, .fi-sidebar .fi-sidebar-item-active > .fi-sidebar-item-button .fi-sidebar-item-icon { color: #0b9e4f !important; }
    .fi-sidebar .fi-sidebar-group-label { color: rgba(255, 255, 255, 0.9) !important; font-weight: 600 !important; }
    
    /* Logo Visibility Fixes & Responsive Collapse */
    .fi-logo { font-weight: 700 !important; font-size: 1.25rem !important; }
    .fi-sidebar .fi-logo { color: #ffffff !important; }
    .fi-topbar .fi-logo { color: #0b9e4f !important; }
    
    /* Make the collapse < arrow yellow! */
    .fi-sidebar-collapse-button svg,
    .fi-topbar-open-sidebar-button svg { 
        color: #d4e122 !important; 
        fill: #d4e122 !important;
    }
    
    @container (max-width: 150px) {
        /* Hide the real logo link entirely so it does not conflict or navigate away */
        .fi-logo { display: none !important; }
        
        /* Transform the native expand button into the PCA Logo */
        .fi-sidebar-collapse-button { 
            display: block !important; 
            width: 2.5rem !important; 
            height: 2.5rem !important; 
            margin: 0 auto !important;
            background-image: url("/images/PCA_Logo.png") !important;
            background-size: contain !important;
            background-repeat: no-repeat !important;
            background-position: center !important;
            background-color: transparent !important;
            border: none !important;
        }
        
        /* Hide the native SVG ">" arrow inside the button */
        .fi-sidebar-collapse-button svg { display: none !important; }
    }

    /* Dashboard Stat Cards (PCA Green Unified Gradients) */
    .stat-gradient-1 { background: linear-gradient(135deg, #f0fdf4, #dcfce7) !important; border: 1px solid #bbf7d0 !important; box-shadow: 0 4px 12px rgba(11, 158, 79, 0.05) !important; }
    .stat-gradient-2 { background: linear-gradient(135deg, #ecfdf5, #d1fae5) !important; border: 1px solid #a7f3d0 !important; box-shadow: 0 4px 12px rgba(11, 158, 79, 0.05) !important; }
    .stat-gradient-3 { background: linear-gradient(135deg, #f0fdfa, #ccfbf1) !important; border: 1px solid #99f6e4 !important; box-shadow: 0 4px 12px rgba(11, 158, 79, 0.05) !important; }
    .stat-gradient-4 { background: linear-gradient(135deg, #eff6ff, #dbeafe) !important; border: 1px solid #bfdbfe !important; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.05) !important; }
    .stat-gradient-5 { background: linear-gradient(135deg, #fdf4ff, #fae8ff) !important; border: 1px solid #f5d0fe !important; box-shadow: 0 4px 12px rgba(192, 38, 211, 0.05) !important; }
    .stat-gradient-6 { background: linear-gradient(135deg, #fef2f2, #fee2e2) !important; border: 1px solid #fecaca !important; box-shadow: 0 4px 12px rgba(220, 38, 38, 0.05) !important; }

    /* Table Headers */
    .fi-ta-header-cell { background-color: #0b9e4f !important; border-bottom: 2px solid #098a44 !important; }
    .fi-ta-header-cell-button .fi-ta-header-cell-label, .fi-ta-header-cell>span { color: #ffffff !important; text-transform: uppercase !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 600 !important; }
    .fi-ta-header-cell .fi-ta-header-cell-sort-icon { color: rgba(255,255,255,0.8) !important; }


    /* --- Dark Mode Styles --- */
    /* Sidebar */
    .dark .fi-sidebar { background-color: #022c22 !important; border-right: 1px solid rgba(255,255,255,0.05) !important; }
    .dark .fi-sidebar-header { background-color: #022c22 !important; border-bottom: 1px solid rgba(255,255,255,0.05) !important; }
    .dark .fi-sidebar .fi-sidebar-item-button:hover { background-color: #064e3b !important; }
    .dark .fi-sidebar .fi-sidebar-item-button:hover .fi-sidebar-item-label, .dark .fi-sidebar .fi-sidebar-item-button:hover .fi-sidebar-item-icon { color: #ffffff !important; }
    .dark .fi-sidebar .fi-sidebar-item-active > .fi-sidebar-item-button { background-color: #059669 !important; box-shadow: 0 4px 12px rgba(0,0,0, 0.3) !important; }
    .dark .fi-sidebar .fi-sidebar-item-active > .fi-sidebar-item-button .fi-sidebar-item-label, .dark .fi-sidebar .fi-sidebar-item-active > .fi-sidebar-item-button .fi-sidebar-item-icon { color: #ffffff !important; }
    
    /* Logo Visibility Dark Mode */
    .dark .fi-logo { color: #ffffff !important; }
    
    /* Dashboard Stat Cards (Dark Mode tinted) */
    .dark .stat-gradient-1, .dark .stat-gradient-2, .dark .stat-gradient-3, .dark .stat-gradient-4, .dark .stat-gradient-5, .dark .stat-gradient-6 {
        background: linear-gradient(135deg, rgba(6,78,59,0.4), rgba(2,44,34,0.4)) !important; border: 1px solid rgba(11, 158, 79, 0.2) !important; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3) !important;
    }
    .dark .stat-gradient-6 {
        background: linear-gradient(135deg, rgba(127,29,29,0.4), rgba(69,10,10,0.4)) !important; border: 1px solid rgba(220, 38, 38, 0.2) !important;
    }

    /* Table Headers */
    .dark .fi-ta-header-cell { background-color: #064e3b !important; border-bottom: 2px solid #022c22 !important; }
    .dark .fi-ta-header-cell-button .fi-ta-header-cell-label, .dark .fi-ta-header-cell>span { color: #e5e7eb !important; }
    .dark .fi-ta-header-cell .fi-ta-header-cell-sort-icon { color: rgba(255,255,255,0.6) !important; }

    /* Colored Card Outlines for Sections */
    .fi-section {
        border: 2px solid rgb(var(--primary-600)) !important;
        box-shadow: 0 4px 12px rgba(var(--primary-600), 0.1) !important;
        border-radius: 0.75rem !important;
        background-color: #ffffff !important;
    }
    .dark .fi-section {
        border-color: rgb(var(--primary-900)) !important;
        box-shadow: 0 4px 12px rgba(0,0,0, 0.4) !important;
        background-color: #111827 !important;
    }
    
    /* Subtle tinted section headers */
    .fi-section-header {
        background-color: rgba(var(--primary-600), 0.05) !important;
        border-bottom: 1px solid rgba(var(--primary-600), 0.15) !important;
    }
    .dark .fi-section-header {
        background-color: rgba(var(--primary-900), 0.1) !important;
        border-bottom: 1px solid rgba(var(--primary-900), 0.2) !important;
    }

    /* --- Login Page Decorations --- */
    .pca-bg-circles {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: -1;
        overflow: hidden;
        background-color: #f9fafb;
        pointer-events: none;
    }
    
    .circle-top-right {
        position: absolute;
        top: -350px;
        right: -350px;
        width: 800px;
        height: 800px;
        border-radius: 50%;
        border: 120px solid #0b9e4f;
        background-color: #d4e122;
        opacity: 0.9;
    }
    
    .circle-top-right::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 300px;
        height: 300px;
        background-color: white;
        border-radius: 50%;
        transform: translate(-50%, -50%);
        opacity: 1;
    }
    
    .circle-bottom-left {
        position: absolute;
        bottom: -300px;
        left: -300px;
        width: 700px;
        height: 700px;
        border-radius: 50%;
        background-color: #0b9e4f;
        opacity: 0.9;
    }

    /* Auth Card Styling */
    .fi-simple-main-ctn {
        position: relative;
        z-index: 10;
        background: transparent !important;
    }
    
    .fi-simple-main {
        background: white !important;
        border-radius: 1.25rem !important;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
        padding: 3rem 2.5rem !important;
        border: 1px solid rgba(0,0,0,0.05) !important;
        max-width: 28rem !important;
    }

    .dark .fi-simple-main {
        background: #111827 !important;
        border-color: rgba(255,255,255,0.1) !important;
    }

    .fi-simple-logo {
        margin-bottom: 1.5rem !important;
    }

    /* Form Elements */
    .fi-simple-main input {
        border-radius: 0.5rem !important;
    }
    
    .fi-simple-main button[type="submit"] {
        border-radius: 0.5rem !important;
        padding-top: 0.75rem !important;
        padding-bottom: 0.75rem !important;
        font-weight: 600 !important;
        font-size: 1rem !important;
    }

    /* Social Login */
    .fi-socialite-button {
        border-radius: 0.5rem !important;
        border: 1px solid #d1d5db !important;
        font-weight: 500 !important;
        transition: all 0.2s !important;
    }
    
    .fi-socialite-button:hover {
        background-color: #f9fafb !important;
    }

    .fi-simple-header-heading {
        font-size: 1.875rem !important;
        font-weight: 800 !important;
        color: #111827 !important;
        margin-top: 0.5rem !important;
    }

    .dark .fi-simple-header-heading {
        color: white !important;
    }

    .fi-simple-header-subheading a {
        color: #0b9e4f !important;
        font-weight: 600 !important;
    }
    
    /* Main Page Headings (Global) */
    .fi-header-heading {
        color: #248b54ff !important;
        font-weight: 800 !important;
    }
    
    /* Breadcrumbs */
    .fi-breadcrumbs-item-label,
    .fi-breadcrumbs-item-separator,
    .fi-breadcrumbs-item-icon {
        color: #12471f !important;
        font-weight: 700 !important;
    }
    
    .dark .fi-breadcrumbs-item-label,
    .dark .fi-breadcrumbs-item-separator,
    .dark .fi-breadcrumbs-item-icon {
        color: #d1d5db !important;
    }
    
    .dark .fi-header-heading {
        color: #ffffff !important;
    }
</style>
HTML;

                    return $html;
                }
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                \App\Filament\Pages\Dashboard::class,
            ])
            ->navigationGroups([
                'Hybridization',
                'Field Data',
                'Reports',
                'Activity',
                'Filament Shield',
                'Settings',
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Custom widgets are auto-discovered
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->sidebarCollapsibleOnDesktop(true)
            ->authMiddleware([
                Authenticate::class,
            ])
            ->middleware([
                SetTheme::class
            ])
            ->plugins(
                $this->getPlugins()
            )
            ->databaseNotifications();
    }

    private function getPlugins(): array
    {
        $plugins = [
            ThemesPlugin::make(),
            FilamentShieldPlugin::make(),
            ApiServicePlugin::make(),
            BreezyCore::make()
                ->myProfile(
                    shouldRegisterUserMenu: true, // Sets the 'account' link in the panel User Menu (default = true)
                    shouldRegisterNavigation: true, // Adds a main navigation item for the My Profile page (default = false)
                    navigationGroup: 'Settings', // Sets the navigation group for the My Profile page (default = null)
                    hasAvatars: true, // Enables the avatar upload form component (default = false)
                    slug: 'my-profile'
                )
                ->customMyProfilePage(\App\Filament\Pages\MyProfile::class)
                ->withoutMyProfileComponents([
                    'personal_info',
                    'update_password',
                ])
                ->avatarUploadComponent(fn($fileUpload) => $fileUpload->disableLabel())
                // OR, replace with your own component
                ->avatarUploadComponent(
                    fn() => FileUpload::make('avatar_url')
                        ->image()
                        ->disk('public')
                )
                ->enableTwoFactorAuthentication(),
        ];

        if ($this->settings->sso_enabled ?? true) {
            $plugins[] =
                FilamentSocialitePlugin::make()
                    ->providers([
                        Provider::make('google')
                            ->label('Google')
                            ->icon('fab-google')
                            ->color(Color::hex('#2f2a6b'))
                            ->outlined(true)
                            ->stateless(false)
                    ])->registration(true)
                    ->createUserUsing(function (string $provider, SocialiteUserContract $oauthUser, FilamentSocialitePlugin $plugin) {
                        $user = User::where('email', $oauthUser->getEmail())->first();

                        if (!$user) {
                            // If user doesn't exist, we don't allow auto-registration for PCA roles.
                            // They must be created by an Admin first.
                            throw new \Exception('Your email is not registered in the PCA Hybrid System. Please contact your administrator.');
                        }

                        // Update existing user with latest info if needed
                        $user->name = $oauthUser->getName();
                        $user->email_verified_at = $user->email_verified_at ?? now();
                        $user->save();

                        return $user;
                    });
        }
        return $plugins;
    }
}
