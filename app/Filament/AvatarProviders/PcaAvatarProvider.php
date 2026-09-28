<?php

namespace App\Filament\AvatarProviders;

use Filament\AvatarProviders\Contracts\AvatarProvider;
use Filament\Facades\Filament;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PcaAvatarProvider implements AvatarProvider
{
    public function get(Model | Authenticatable $record): string
    {
        // If the user has an uploaded avatar, return its URL from storage
        if (!empty($record->avatar_url)) {
            return Storage::disk('cloudinary')->url($record->avatar_url);
        }

        // Fallback: generate initials avatar with PCA green
        $name = str(Filament::getNameForDefaultAvatar($record))
            ->trim()
            ->explode(' ')
            ->map(fn (string $segment): string => filled($segment) ? mb_substr($segment, 0, 1) : '')
            ->join(' ');

        // PCA Green background with white text
        return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&color=FFFFFF&background=0b9e4f';
    }
}
