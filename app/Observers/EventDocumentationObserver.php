<?php

namespace App\Observers;

use App\Models\EventDocumentation;

class EventDocumentationObserver
{
    /**
     * Handle the EventDocumentation "saving" event.
     */
    public function saving(EventDocumentation $eventDocumentation): void
    {
        if ($eventDocumentation->isDirty('image_path') && $eventDocumentation->image_path) {
            $path = storage_path('app/public/' . $eventDocumentation->image_path);
            if (file_exists($path) && function_exists('exif_read_data')) {
                try {
                    $exif = @exif_read_data($path);
                    if ($exif) {
                        // Extract DateTime
                        if (isset($exif['DateTimeOriginal'])) {
                            $eventDocumentation->captured_at = \Carbon\Carbon::parse($exif['DateTimeOriginal']);
                        } elseif (isset($exif['DateTime'])) {
                            $eventDocumentation->captured_at = \Carbon\Carbon::parse($exif['DateTime']);
                        }

                        // Extract GPS
                        if (isset($exif['GPSLatitude']) && isset($exif['GPSLatitudeRef']) && 
                            isset($exif['GPSLongitude']) && isset($exif['GPSLongitudeRef'])) {
                            
                            $lat = $this->getGps($exif['GPSLatitude'], $exif['GPSLatitudeRef']);
                            $lon = $this->getGps($exif['GPSLongitude'], $exif['GPSLongitudeRef']);
                            
                            $eventDocumentation->latitude = $lat;
                            $eventDocumentation->longitude = $lon;
                        }
                    }
                } catch (\Exception $e) {
                    // Ignore EXIF read errors
                }
            }
        }
    }

    private function getGps($coordinate, $hemisphere) 
    {
        if (is_string($coordinate)) return $coordinate;
        
        $degrees = count($coordinate) > 0 ? $this->gps2Num($coordinate[0]) : 0;
        $minutes = count($coordinate) > 1 ? $this->gps2Num($coordinate[1]) : 0;
        $seconds = count($coordinate) > 2 ? $this->gps2Num($coordinate[2]) : 0;
        
        $sign = ($hemisphere == 'W' || $hemisphere == 'S') ? -1 : 1;
        return $sign * ($degrees + $minutes/60 + $seconds/3600);
    }

    private function gps2Num($coordPart) 
    {
        $parts = explode('/', $coordPart);
        if (count($parts) <= 0) return 0;
        if (count($parts) == 1) return $parts[0];
        
        $val = floatval($parts[0]);
        $div = floatval($parts[1]);
        if ($div == 0) return 0;
        
        return $val / $div;
    }

    /**
     * Handle the EventDocumentation "updated" event.
     */
    public function updated(EventDocumentation $eventDocumentation): void
    {
        //
    }

    /**
     * Handle the EventDocumentation "deleted" event.
     */
    public function deleted(EventDocumentation $eventDocumentation): void
    {
        //
    }

    /**
     * Handle the EventDocumentation "restored" event.
     */
    public function restored(EventDocumentation $eventDocumentation): void
    {
        //
    }

    /**
     * Handle the EventDocumentation "force deleted" event.
     */
    public function forceDeleted(EventDocumentation $eventDocumentation): void
    {
        //
    }
}
