<?php
namespace App\Filament\Resources\PollenProductionResource\Pages;
use App\Filament\Resources\PollenProductionResource;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Traits\HasCarryForward;
class CreatePollenProduction extends CreateRecord
{
    use HasCarryForward;

    protected static string $resource = PollenProductionResource::class;

    protected array $carryForwardFields = [
        'pollen_variety',
        'ending_balance_prev' // Map ending_balance to this in trait
    ];

    public function mount(): void
    {
        parent::mount();

        if ((auth()->user()?->isSupervisor() || auth()->user()?->isSubSupervisor())) {
            $this->loadLatestRecordData();
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['field_site_id'])) {
            $data['field_site_id'] = auth()->user()->field_site_id;
        }

        return $data;
    }
}
