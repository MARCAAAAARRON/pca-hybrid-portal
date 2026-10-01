<?php

namespace App\Filament\Resources\HybridDistributionResource\Pages;

use App\Filament\Resources\HybridDistributionResource;
use App\Models\HybridDistribution;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

use App\Filament\Traits\HasCarryForward;

class CreateHybridDistribution extends CreateRecord
{
    use HasCarryForward;

    protected static string $resource = HybridDistributionResource::class;

    protected array $carryForwardFields = [
        'region',
        'province',
        'district',
        'municipality',
        'barangay',
    ];

    public function mount(): void
    {
        parent::mount();

        if ((auth()->user()?->isSupervisor() || auth()->user()?->isSubSupervisor())) {
            $this->loadLatestRecordData();
        }
    }
    
    /**
     * Override handleRecordCreation to batch-create one HybridDistribution
     * record per farmer entry in the repeater (matching Django's behavior).
     */
    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $farmers = $data['farmers'] ?? [];
        $sharedData = [
            'field_site_id' => $data['field_site_id'] ?? auth()->user()->field_site_id,
            'report_month' => $data['report_month'],
        ];

        $firstRecord = null;

        foreach ($farmers as $farmer) {
            // Map 'gender' virtual field to is_male/is_female booleans
            $gender = $farmer['gender'] ?? null;
            unset($farmer['gender']);
            $farmer['is_male'] = $gender === 'M';
            $farmer['is_female'] = $gender === 'F';

            $record = HybridDistribution::create(array_merge($sharedData, $farmer));

            if (!$firstRecord) {
                $firstRecord = $record;
            }
        }

        $count = count($farmers);
        if ($count > 1) {
            Notification::make()
                ->success()
                ->title("Created {$count} distribution records")
                ->body("All {$count} farmer entries were saved successfully.")
                ->send();
        }

        return $firstRecord;
    }
}
