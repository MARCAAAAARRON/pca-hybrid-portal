<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

class SignatureHelper
{
    /**
     * Safely get the signature URL from Cloudinary.
     * Returns null if Cloudinary is unreachable (e.g. localhost without internet).
     */
    public static function getSignatureUrl(?string $signatureImage): ?string
    {
        if (!$signatureImage) {
            return null;
        }

        $cloudinaryUrl = env('CLOUDINARY_URL', '');
        preg_match('/@([^\/]+)/', $cloudinaryUrl, $matches);
        $cloudName = $matches[1] ?? 'dlvgoszbo';

        return "https://res.cloudinary.com/{$cloudName}/image/upload/{$signatureImage}";
    }

    /**
     * Check if Cloudinary is available.
     */
    public static function isCloudinaryAvailable(): bool
    {
        try {
            Storage::disk('cloudinary')->url('test');
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
