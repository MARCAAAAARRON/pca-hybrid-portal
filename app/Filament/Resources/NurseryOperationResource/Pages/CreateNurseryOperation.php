<?php
namespace App\Filament\Resources\NurseryOperationResource\Pages;
use App\Filament\Resources\NurseryOperationResource;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Traits\HasCarryForward;
class CreateNurseryOperation extends CreateRecord
{
    use HasCarryForward;

    protected static string $resource = NurseryOperationResource::class;

    protected string $carryForwardType = 'operation';

    protected array $carryForwardFields = [
        'region_province_district',
        'barangay_municipality',
        'proponent_entity',
        'proponent_representative',
        'target_seednuts',
        'batches' => [
            'seednuts_harvested',
            'date_harvested',
            'date_received',
            'source_of_seednuts',
            'varieties' => [
                'variety', 'seednuts_sown', 'date_sown', 'seedlings_germinated', 
                'ungerminated_seednuts', 'culled_seedlings', 'good_seedlings', 
                'ready_to_plant', 'seedlings_dispatched', 'remarks'
            ]
        ]
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

        $data = $this->normalizeVarietyNumericFields($data);

        return $data;
    }

    protected function normalizeVarietyNumericFields(array $data): array
    {
        $numericFields = [
            'seednuts_sown',
            'seedlings_germinated',
            'ungerminated_seednuts',
            'culled_seedlings',
            'good_seedlings',
            'ready_to_plant',
            'seedlings_dispatched',
        ];

        if (!is_array($data['batches'] ?? null)) {
            return $data;
        }

        foreach ($data['batches'] as $batchIndex => $batch) {
            if (!is_array($batch['varieties'] ?? null)) {
                continue;
            }

            foreach ($batch['varieties'] as $varietyIndex => $variety) {
                foreach ($numericFields as $field) {
                    $value = $variety[$field] ?? null;
                    $data['batches'][$batchIndex]['varieties'][$varietyIndex][$field] = $this->normalizeNumericValue($value);
                }
            }
        }

        return $data;
    }

    protected function normalizeNumericValue(mixed $value): int
    {
        if ($value === null || $value === '') {
            return 0;
        }

        return max(0, (int) $value);
    }
}
