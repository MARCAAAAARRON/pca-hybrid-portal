<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Widgets\OrganizationCalendarWidget;

class OrganizationCalendarPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    
    // Match the navigation group of EventDocumentationResource
    protected static ?string $navigationGroup = 'Strategic Documentation';
    
    protected static ?string $navigationLabel = 'Organization Calendar';
    protected static ?string $title = 'Organization Calendar';
    
    protected static string $view = 'filament.pages.organization-calendar';
    
    public static function canAccess(): bool
    {
        // Accessible by all roles except superadmin (who has a separate admin panel)
        return in_array(auth()->user()?->role, ['supervisor', 'sub_supervisor', 'manager', 'admin', 'superadmin']);
    }

    protected function getHeaderActions(): array
    {
        $canCreateOrgReminder = in_array(auth()->user()?->role, ['manager', 'admin', 'superadmin']);

        return [
            \Filament\Actions\Action::make('create_reminder')
                ->label('Create Reminder')
                ->icon('heroicon-o-bell')
                ->form([
                    // Supervisor & sub-supervisor: forced to personal, type selector hidden
                    // Manager/admin/superadmin: can choose personal or organizational
                    \Filament\Forms\Components\Select::make('type')
                        ->label('Reminder Type')
                        ->options([
                            'personal' => 'Personal (Only you can see this)',
                            'organizational' => 'Organizational (Everyone can see this)',
                        ])
                        ->default('personal')
                        ->required()
                        ->visible($canCreateOrgReminder)
                        ->disableOptionWhen(fn (string $value): bool => $value === 'organizational' && !(auth()->user()?->isSuperAdmin() ?? false))
                        ->helperText(fn () => !(auth()->user()?->isSuperAdmin() ?? false) ? 'Only Superadmins can create organizational reminders.' : ''),
                    \Filament\Forms\Components\TextInput::make('title')
                        ->required()
                        ->maxLength(255),
                    \Filament\Forms\Components\Textarea::make('description')
                        ->maxLength(65535),
                    \Filament\Forms\Components\DatePicker::make('reminder_date')
                        ->required()
                        ->default(now()),
                ])
                ->action(function (array $data) {
                    // Force personal type for supervisor/sub_supervisor
                    $type = in_array(auth()->user()?->role, ['supervisor', 'sub_supervisor'])
                        ? 'personal'
                        : ($data['type'] ?? 'personal');

                    $reminder = \App\Models\CalendarReminder::create([
                        'title' => $data['title'],
                        'description' => $data['description'],
                        'reminder_date' => $data['reminder_date'],
                        'type' => $type,
                        'user_id' => auth()->id(),
                    ]);

                    if ($reminder->type === 'organizational') {
                        $users = \App\Models\User::all();
                        foreach ($users as $user) {
                            \Filament\Notifications\Notification::make()
                                ->title('New Org Reminder: ' . $reminder->title)
                                ->body($reminder->description)
                                ->info()
                                ->sendToDatabase($user);
                        }
                    } else {
                        \Filament\Notifications\Notification::make()
                            ->title('Personal Reminder Created')
                            ->body($reminder->title)
                            ->success()
                            ->sendToDatabase(auth()->user());
                    }
                    
                    \Filament\Notifications\Notification::make()
                        ->title('Reminder Created')
                        ->success()
                        ->send();
                })
        ];
    }
}
