<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Jeffgreco13\FilamentBreezy\Pages\MyProfilePage as BreezyProfilePage;

class MyProfile extends BreezyProfilePage
{
    protected static string $view = 'filament.pages.my-profile';
    
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationLabel = 'Profile';

    protected static ?string $title = 'My Profile';

    public ?array $data = [];

    public function mount(): void
    {
        $user = auth()->user();
        
        $this->form->fill([
            'first_name' => $user->first_name,
            'middle_initial' => $user->middle_initial,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'avatar_url' => $user->avatar_url,
            'signature_image' => $user->signature_image,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                \Filament\Forms\Components\Tabs::make('Profile Tabs')
                    ->tabs([
                        \Filament\Forms\Components\Tabs\Tab::make('Profile Info')
                            ->icon('heroicon-o-user-circle')
                            ->schema([
                                Section::make('General Information')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                TextInput::make('first_name')
                                                    ->label('First Name')
                                                    ->placeholder('Enter first name')
                                                    ->required(),
                                                TextInput::make('middle_initial')
                                                    ->label('M.I.')
                                                    ->placeholder('M.I.')
                                                    ->maxLength(10),
                                                TextInput::make('last_name')
                                                    ->label('Last Name')
                                                    ->placeholder('Enter last name')
                                                    ->required(),
                                            ]),
                                        TextInput::make('email')
                                            ->label('Email Address')
                                            ->placeholder('example@email.com')
                                            ->email()
                                            ->required()
                                            ->unique('users', 'email', ignorable: auth()->user()),
                                    ]),
                            ]),
                        
                        \Filament\Forms\Components\Tabs\Tab::make('Digital Signature')
                            ->icon('heroicon-o-pencil')
                            ->schema([
                                Section::make('Signature Management')
                                    ->description('This signature will be used for automated report signing.')
                                    ->schema([
                                        FileUpload::make('signature_image')
                                            ->label('Upload/Snap Signature')
                                            ->image()
                                            ->disk('cloudinary')
                                            ->directory('signatures')
                                            ->imageEditor()
                                            ->imageEditorAspectRatios([
                                                null,
                                                '16:9',
                                                '4:3',
                                                '1:1',
                                            ])
                                            ->extraInputAttributes(['capture' => 'environment'])
                                            ->disabled(fn () => !auth()->user()->canUpdateSignature())
                                            ->helperText(fn () => auth()->user()->canUpdateSignature()
                                                ? 'Snap/Upload your signature. IMPORTANT: Click the PENCIL icon on the image to CROP it before saving.'
                                                : 'You can only update your digital signature once every 3 months. Next update available: ' . auth()->user()->signature_updated_at->addMonths(3)->format('M d, Y')
                                            ),
                                    ]),
                            ]),
                        
                        \Filament\Forms\Components\Tabs\Tab::make('Security Settings')
                            ->icon('heroicon-o-key')
                            ->schema([
                                Section::make('Change Password')
                                    ->description('Ensure your account is using a long, random password to stay secure.')
                                    ->schema([
                                        TextInput::make('current_password')
                                            ->label('Current Password')
                                            ->password()
                                            ->revealable()
                                            ->requiredWith('new_password')
                                            ->currentPassword()
                                            ->visible(fn() => filament('filament-breezy')->getPasswordUpdateRequiresCurrent()),
                                        TextInput::make('new_password')
                                            ->label('New Password')
                                            ->password()
                                            ->revealable()
                                            ->rule(Password::default()),
                                        TextInput::make('new_password_confirmation')
                                            ->label('Confirm New Password')
                                            ->password()
                                            ->revealable()
                                            ->same('new_password')
                                            ->requiredWith('new_password'),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->persistTabInQueryString(),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();
        $user = auth()->user();

        $updateData = [
            'first_name' => $data['first_name'],
            'middle_initial' => $data['middle_initial'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
        ];

        if (array_key_exists('signature_image', $data)) {
            $updateData['signature_image'] = $data['signature_image'];
            if ($data['signature_image'] !== $user->signature_image) {
                $updateData['signature_updated_at'] = now();
            }
        }

        if (!empty($data['new_password'])) {
            $updateData['password'] = $data['new_password'];
        }

        $user->update($updateData);

        if (!empty($data['new_password'])) {
            $this->data['current_password'] = null;
            $this->data['new_password'] = null;
            $this->data['new_password_confirmation'] = null;
        }

        Notification::make()
            ->success()
            ->title('Profile updated successfully.')
            ->send();
    }

    public function logout(): void
    {
        auth()->logout();
        session()->invalidate();
        session()->regenerateToken();

        $this->redirect(filament()->getLoginUrl());
    }
}
