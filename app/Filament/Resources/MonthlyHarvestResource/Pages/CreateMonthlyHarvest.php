<?php
namespace App\Filament\Resources\MonthlyHarvestResource\Pages;
use App\Filament\Resources\MonthlyHarvestResource;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Traits\HasCarryForward;
class CreateMonthlyHarvest extends CreateRecord
{
    use HasCarryForward;

    protected static string $resource = MonthlyHarvestResource::class;

    protected array $carryForwardFields = [
        'location',
        'farm_name',
        'area_ha',
        'age_of_palms',
        'num_hybridized_palms',
        'varieties' => ['variety', 'seednuts_type', 'seednuts_count', 'remarks']
    ];

    public function mount(): void
    {
        parent::mount();

        // Pre-fill field_site_id from QR code redirect (?field_site_id=X)
        $qrSiteId = request()->query('field_site_id');
        if ($qrSiteId) {
            $this->form->fill([
                'field_site_id' => (int) $qrSiteId,
            ]);
        }

        if ((auth()->user()?->isSupervisor() || auth()->user()?->isSubSupervisor())) {
            $this->loadLatestRecordData();
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $palms = (int) ($data['num_hybridized_palms'] ?? 0);
        $totalSeednuts = 0;
        
        if (!empty($data['varieties'])) {
            foreach ($data['varieties'] as $variety) {
                $totalSeednuts += (int) ($variety['seednuts_count'] ?? 0);
            }
        }
        
        // Logical Validation: Max ~30 seednuts per palm per month (historical anomaly threshold)
        $maxExpected = $palms * 30; 
        
        if ($palms > 0 && $totalSeednuts > $maxExpected) {
            \Filament\Notifications\Notification::make()
                ->danger()
                ->title('Data Anomaly Detected')
                ->body("Total seednuts ({$totalSeednuts}) is abnormally high for {$palms} palms. The maximum expected is {$maxExpected}. Please verify your inputs.")
                ->persistent()
                ->send();
                
            throw new \Filament\Support\Exceptions\Halt();
        }
        
        return $data;
    }
}
