<?php

namespace Database\Seeders;

use App\Models\FieldSite;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TestFieldDataSeeder extends Seeder
{
    // ─── Site-specific configuration ──────────────────────────────────────────

    private array $siteConfig = [
        'Loay Farm' => [
            'location'       => 'Brgy. Boctol, Loay, Bohol',
            'farm_name'      => 'PCA Loay Sub-Station',
            'area_ha'        => '3.62',
            'age_of_palms'   => '12 yrs',
            'num_palms'      => 450,
            'varieties'      => [
                ['name' => 'Catigan Green Dwarf × TALL', 'type' => 'Hybrid'],
                ['name' => 'Laguna Tall × Dwarf',        'type' => 'Hybrid'],
            ],
            'region'         => 'Region VII',
            'province'       => 'Bohol',
            'district'       => 'III',
            'municipality'   => 'Loay',
            'barangay'       => 'Boctol',
            'rpd'            => 'VII-Bohol/III',
            'bm'             => 'Boctol, Loay',
            'proponent'      => 'Loay On-Farm Nursery',
            'rep'            => 'Juan D. Mahinay',
            'target_sn'      => 5000,
            'pollen_variety' => 'LAGUNA TALL POLLENS',
            'pollen_source'  => 'CVSPC',
            'pollen_mult'    => 1.00,
            'terminal_month' => 4,
        ],
        'Balilihan Farm' => [
            'location'       => 'Brgy. Cabad, Balilihan, Bohol',
            'farm_name'      => 'PCA Balilihan Sub-Station',
            'area_ha'        => '4.10',
            'age_of_palms'   => '14 yrs',
            'num_palms'      => 520,
            'varieties'      => [
                ['name' => 'Tacunan Green Dwarf × TALL',  'type' => 'Hybrid'],
                ['name' => 'Malayan Yellow Dwarf × TALL', 'type' => 'Hybrid'],
            ],
            'region'         => 'Region VII',
            'province'       => 'Bohol',
            'district'       => 'III',
            'municipality'   => 'Balilihan',
            'barangay'       => 'Cabad',
            'rpd'            => 'VII-Bohol/III',
            'bm'             => 'Cabad, Balilihan',
            'proponent'      => 'Balilihan On-Farm Nursery',
            'rep'            => 'Maria S. Caballes',
            'target_sn'      => 6000,
            'pollen_variety' => 'CATIGAN GREEN DWARF POLLENS',
            'pollen_source'  => 'Loay Farm',
            'pollen_mult'    => 1.15,
            'terminal_month' => 5,
        ],
    ];

    /** Seednut counts per month (Jan–Aug index 0–7), keyed by site name */
    private array $seednutsPerMonth = [
        'Loay Farm'      => [120, 145, 180, 210, 190, 165, 140, 125],
        'Balilihan Farm' => [135, 160, 200, 235, 215, 188, 158, 140],
    ];

    /**
     * Base pollen data for Loay (index 0–7 = Jan–Aug).
     * [prev, received, w1, w2, w3, w4, total, ending]
     * Other sites are scaled by pollen_mult.
     */
    private array $pollenBase = [
        [  0, 500,  80,  90,  85,  75, 330, 170],
        [170, 300,  70,  85,  80,  65, 300, 170],
        [170, 400,  90,  95, 100,  85, 370, 200],
        [200, 350,  85,  90,  95,  80, 350, 200],
        [200, 280,  75,  80,  85,  70, 310, 170],
        [170, 320,  80,  85,  90,  75, 330, 160],
        [160, 300,  70,  80,  85,  65, 300, 160],
        [160, 250,  65,  75,  80,  60, 280, 130],
    ];

    /** 15 farmers per site — same farmers appear every month (carry-forward) */
    private array $farmers = [
        'Loay Farm' => [
            ['last'=>'Dela Cruz',  'first'=>'Juan',      'mi'=>'D.','male'=>1,'female'=>0,'brgy'=>'Boctol',       'mun'=>'Loay','prov'=>'Bohol','variety'=>'Catigan Green Dwarf'],
            ['last'=>'Reyes',      'first'=>'Maria',     'mi'=>'S.','male'=>0,'female'=>1,'brgy'=>'Tangnan',      'mun'=>'Loay','prov'=>'Bohol','variety'=>'Laguna Tall'],
            ['last'=>'Santos',     'first'=>'Pedro',     'mi'=>'A.','male'=>1,'female'=>0,'brgy'=>'Ubayon',       'mun'=>'Loay','prov'=>'Bohol','variety'=>'Catigan Green Dwarf'],
            ['last'=>'Mendoza',    'first'=>'Ernesto',   'mi'=>'G.','male'=>1,'female'=>0,'brgy'=>'Poblacion',    'mun'=>'Loay','prov'=>'Bohol','variety'=>'Laguna Tall'],
            ['last'=>'Ramos',      'first'=>'Lourdes',   'mi'=>'T.','male'=>0,'female'=>1,'brgy'=>'Aghaoy',       'mun'=>'Loay','prov'=>'Bohol','variety'=>'Catigan Green Dwarf'],
            ['last'=>'Villanueva', 'first'=>'Carlos',    'mi'=>'B.','male'=>1,'female'=>0,'brgy'=>'Bonbon',       'mun'=>'Loay','prov'=>'Bohol','variety'=>'Laguna Tall'],
            ['last'=>'Navarro',    'first'=>'Elena',     'mi'=>'M.','male'=>0,'female'=>1,'brgy'=>'Calvario',     'mun'=>'Loay','prov'=>'Bohol','variety'=>'Catigan Green Dwarf'],
            ['last'=>'Bautista',   'first'=>'Antonio',   'mi'=>'R.','male'=>1,'female'=>0,'brgy'=>'Concepcion',   'mun'=>'Loay','prov'=>'Bohol','variety'=>'Laguna Tall'],
            ['last'=>'Torres',     'first'=>'Rosario',   'mi'=>'L.','male'=>0,'female'=>1,'brgy'=>'Hinawanan',    'mun'=>'Loay','prov'=>'Bohol','variety'=>'Catigan Green Dwarf'],
            ['last'=>'Gonzales',   'first'=>'Manuel',    'mi'=>'P.','male'=>1,'female'=>0,'brgy'=>'Lourdes',      'mun'=>'Loay','prov'=>'Bohol','variety'=>'Laguna Tall'],
            ['last'=>'Flores',     'first'=>'Josefa',    'mi'=>'C.','male'=>0,'female'=>1,'brgy'=>'Mocpoc Norte', 'mun'=>'Loay','prov'=>'Bohol','variety'=>'Catigan Green Dwarf'],
            ['last'=>'Castillo',   'first'=>'Ricardo',   'mi'=>'E.','male'=>1,'female'=>0,'brgy'=>'Mocpoc Sur',   'mun'=>'Loay','prov'=>'Bohol','variety'=>'Laguna Tall'],
            ['last'=>'Soriano',    'first'=>'Norma',     'mi'=>'D.','male'=>0,'female'=>1,'brgy'=>'Tayong',       'mun'=>'Loay','prov'=>'Bohol','variety'=>'Catigan Green Dwarf'],
            ['last'=>'Aquino',     'first'=>'Fernando',  'mi'=>'V.','male'=>1,'female'=>0,'brgy'=>'Ynihan',       'mun'=>'Loay','prov'=>'Bohol','variety'=>'Laguna Tall'],
            ['last'=>'Jamil',      'first'=>'Conchita',  'mi'=>'A.','male'=>0,'female'=>1,'brgy'=>'Agape',        'mun'=>'Loay','prov'=>'Bohol','variety'=>'Catigan Green Dwarf'],
        ],
        'Balilihan Farm' => [
            ['last'=>'Garcia',     'first'=>'Rosa',      'mi'=>'M.','male'=>0,'female'=>1,'brgy'=>'Cabad',        'mun'=>'Balilihan','prov'=>'Bohol','variety'=>'Tacunan Green Dwarf'],
            ['last'=>'Mahinay',    'first'=>'Epigenio',  'mi'=>'L.','male'=>1,'female'=>0,'brgy'=>'Abucay',       'mun'=>'Balilihan','prov'=>'Bohol','variety'=>'Malayan Yellow Dwarf'],
            ['last'=>'Caballes',   'first'=>'Luisa',     'mi'=>'R.','male'=>0,'female'=>1,'brgy'=>'Owac',         'mun'=>'Balilihan','prov'=>'Bohol','variety'=>'Tacunan Green Dwarf'],
            ['last'=>'Padilla',    'first'=>'Arturo',    'mi'=>'S.','male'=>1,'female'=>0,'brgy'=>'Poblacion',    'mun'=>'Balilihan','prov'=>'Bohol','variety'=>'Malayan Yellow Dwarf'],
            ['last'=>'Lugue',      'first'=>'Remedios',  'mi'=>'P.','male'=>0,'female'=>1,'brgy'=>'Baucan Norte', 'mun'=>'Balilihan','prov'=>'Bohol','variety'=>'Tacunan Green Dwarf'],
            ['last'=>'Bernales',   'first'=>'Vicente',   'mi'=>'T.','male'=>1,'female'=>0,'brgy'=>'Baucan Sur',   'mun'=>'Balilihan','prov'=>'Bohol','variety'=>'Malayan Yellow Dwarf'],
            ['last'=>'Dagohoy',    'first'=>'Erlinda',   'mi'=>'C.','male'=>0,'female'=>1,'brgy'=>'Candumayao',   'mun'=>'Balilihan','prov'=>'Bohol','variety'=>'Tacunan Green Dwarf'],
            ['last'=>'Tumbok',     'first'=>'Gregorio',  'mi'=>'M.','male'=>1,'female'=>0,'brgy'=>'Cantuod',      'mun'=>'Balilihan','prov'=>'Bohol','variety'=>'Malayan Yellow Dwarf'],
            ['last'=>'Gallares',   'first'=>'Patricia',  'mi'=>'A.','male'=>0,'female'=>1,'brgy'=>'Datag Norte',  'mun'=>'Balilihan','prov'=>'Bohol','variety'=>'Tacunan Green Dwarf'],
            ['last'=>'Aumentado',  'first'=>'Isidro',    'mi'=>'R.','male'=>1,'female'=>0,'brgy'=>'Datag Sur',    'mun'=>'Balilihan','prov'=>'Bohol','variety'=>'Malayan Yellow Dwarf'],
            ['last'=>'Piscal',     'first'=>'Felicidad', 'mi'=>'B.','male'=>0,'female'=>1,'brgy'=>'Del Carmen',   'mun'=>'Balilihan','prov'=>'Bohol','variety'=>'Tacunan Green Dwarf'],
            ['last'=>'Loigoy',     'first'=>'Demetrio',  'mi'=>'G.','male'=>1,'female'=>0,'brgy'=>'Hanopol',      'mun'=>'Balilihan','prov'=>'Bohol','variety'=>'Malayan Yellow Dwarf'],
            ['last'=>'Batiquin',   'first'=>'Gloria',    'mi'=>'V.','male'=>0,'female'=>1,'brgy'=>'Limocon',      'mun'=>'Balilihan','prov'=>'Bohol','variety'=>'Tacunan Green Dwarf'],
            ['last'=>'Carvajal',   'first'=>'Marcelo',   'mi'=>'D.','male'=>1,'female'=>0,'brgy'=>'San Isidro',   'mun'=>'Balilihan','prov'=>'Bohol','variety'=>'Malayan Yellow Dwarf'],
            ['last'=>'Ampo',       'first'=>'Juliana',   'mi'=>'E.','male'=>0,'female'=>1,'brgy'=>'Magsija',      'mun'=>'Balilihan','prov'=>'Bohol','variety'=>'Tacunan Green Dwarf'],
        ],
    ];

    /**
     * Nursery data per month [harvested, germinated, ungerm, culled, good, ready, dispatched]
     */
    private array $nurseryPerMonth = [
        [ 900, 720, 100, 30, 590, 400,   0],
        [ 950, 760,  90, 28, 640, 480,   0],
        [1000, 800,  85, 25, 690, 540, 100],
        [1100, 880,  80, 22, 778, 620, 280],
        [ 950, 760,  88, 24, 648, 520, 440],
        [ 850, 680,  92, 26, 562, 460, 480],
        [ 800, 640,  95, 28, 517, 420, 400],
        [ 750, 600, 100, 30, 470, 380, 350],
    ];

    private array $monthLabels = [
        1=>'january',2=>'february',3=>'march',4=>'april',
        5=>'may',6=>'june',7=>'july',8=>'august',
    ];

    private array $monthNames = [
        1=>'January',2=>'February',3=>'March',4=>'April',
        5=>'May',6=>'June',7=>'July',8=>'August',
    ];

    // ─── Run ──────────────────────────────────────────────────────────────────

    public function run(): void
    {
        $sites = FieldSite::whereIn('name', array_keys($this->siteConfig))->get()->keyBy('name');

        if ($sites->isEmpty()) {
            $this->command->error('No field sites found. Run FieldSiteSeeder first.');
            return;
        }

        $supervisors = [
            'Loay Farm'      => User::where('email', 'loay@pca.gov.ph')->first(),
            'Balilihan Farm' => User::where('email', 'balilihan@pca.gov.ph')->first(),
        ];
        $manager = User::where('email', 'manager@pca.gov.ph')->first();
        $admin   = User::where('email', 'admin@pca.gov.ph')->first();

        foreach ($sites as $siteName => $site) {
            $this->command->info("Seeding {$siteName}...");
            $cfg        = $this->siteConfig[$siteName];
            $supervisor = $supervisors[$siteName];

            for ($m = 1; $m <= 8; $m++) {
                $monthDate = Carbon::create(2026, $m, 1);
                $status    = $this->statusFor($m);

                $this->seedMonthlyHarvest($site, $cfg, $siteName, $monthDate, $m, $status, $supervisor, $manager, $admin);
                $this->seedHybridDistributions($site, $cfg, $siteName, $monthDate, $m, $status, $supervisor, $manager, $admin);
                $this->seedNurseryOperation($site, $cfg, $siteName, $monthDate, $m, $status, $supervisor, $manager, $admin, 'operation');
            }

            // Terminal report — once per site at end of nursery cycle
            $terminalMonth = Carbon::create(2026, $cfg['terminal_month'], 1);
            $this->seedNurseryOperation($site, $cfg, $siteName, $terminalMonth, $cfg['terminal_month'], 'noted', $supervisor, $manager, $admin, 'terminal');

            // Pollen with carry-forward
            $this->seedPollenProductions($site, $cfg, $siteName, $supervisor, $manager, $admin);

            $this->command->line("  Done.");
        }

        $this->command->info('Demo data seeded successfully!');
    }

    // ─── Status helper ────────────────────────────────────────────────────────

    private function statusFor(int $month): string
    {
        return match (true) {
            $month <= 5  => 'noted',
            $month === 6 => 'reviewed',
            $month === 7 => 'prepared',
            default      => 'draft',
        };
    }

    private function signatories(string $status, ?User $supervisor, ?User $manager, ?User $admin, Carbon $monthDate): array
    {
        $next = $monthDate->copy()->addMonth();

        return match ($status) {
            'noted' => [
                'prepared_by'   => $supervisor?->id,
                'date_prepared' => $next->copy()->setDay(5)->toDateTimeString(),
                'reviewed_by'   => $manager?->id,
                'date_reviewed' => $next->copy()->setDay(10)->toDateTimeString(),
                'noted_by'      => $admin?->id,
                'date_noted'    => $next->copy()->setDay(15)->toDateTimeString(),
            ],
            'reviewed' => [
                'prepared_by'   => $supervisor?->id,
                'date_prepared' => $next->copy()->setDay(5)->toDateTimeString(),
                'reviewed_by'   => $manager?->id,
                'date_reviewed' => $next->copy()->setDay(10)->toDateTimeString(),
                'noted_by'      => null,
                'date_noted'    => null,
            ],
            'prepared' => [
                'prepared_by'   => $supervisor?->id,
                'date_prepared' => $next->copy()->setDay(5)->toDateTimeString(),
                'reviewed_by'   => null,
                'date_reviewed' => null,
                'noted_by'      => null,
                'date_noted'    => null,
            ],
            default => [
                'prepared_by'   => null,
                'date_prepared' => null,
                'reviewed_by'   => null,
                'date_reviewed' => null,
                'noted_by'      => null,
                'date_noted'    => null,
            ],
        };
    }

    // ─── 1. Monthly Harvest ───────────────────────────────────────────────────

    private function seedMonthlyHarvest(
        FieldSite $site, array $cfg, string $siteName,
        Carbon $monthDate, int $m, string $status,
        ?User $supervisor, ?User $manager, ?User $admin
    ): void {
        if (DB::table('monthly_harvests')
            ->where('field_site_id', $site->id)
            ->where('report_month', $monthDate->format('Y-m-d'))
            ->whereNull('deleted_at')
            ->exists()
        ) {
            return;
        }

        $sig      = $this->signatories($status, $supervisor, $manager, $admin, $monthDate);
        $now      = now()->toDateTimeString();
        $snCounts = $this->seednutsPerMonth[$siteName];

        // Cumulative production columns (all months up to current)
        $colMap = ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'];
        $prodCols = [];
        foreach ($colMap as $idx => $col) {
            $prodCols["production_{$col}"] = ($idx + 1) <= $m ? $snCounts[$idx] : 0;
        }

        $harvestId = DB::table('monthly_harvests')->insertGetId(array_merge([
            'field_site_id'        => $site->id,
            'report_month'         => $monthDate->format('Y-m-d'),
            'status'               => $status,
            'location'             => $cfg['location'],
            'farm_name'            => $cfg['farm_name'],
            'area_ha'              => $cfg['area_ha'],
            'age_of_palms'         => $cfg['age_of_palms'],
            'num_hybridized_palms' => $cfg['num_palms'],
            'variety'              => implode(' / ', array_column($cfg['varieties'], 'name')),
            'seednuts_produced'    => 'Hybrid',
            'remarks'              => "Monthly harvest report for {$this->monthNames[$m]} 2026.",
            'created_at'           => $now,
            'updated_at'           => $now,
        ], $prodCols, $sig));

        foreach ($cfg['varieties'] as $idx => $variety) {
            $count = $idx === 0 ? $snCounts[$m - 1] : (int) round($snCounts[$m - 1] * 0.82);
            DB::table('harvest_varieties')->insert([
                'monthly_harvest_id' => $harvestId,
                'variety'            => $variety['name'],
                'seednuts_type'      => $variety['type'],
                'seednuts_count'     => $count,
                'remarks'            => null,
                'created_at'         => $now,
                'updated_at'         => $now,
            ]);
        }
    }

    // ─── 2. Hybrid Distributions ─────────────────────────────────────────────

    private function seedHybridDistributions(
        FieldSite $site, array $cfg, string $siteName,
        Carbon $monthDate, int $m, string $status,
        ?User $supervisor, ?User $manager, ?User $admin
    ): void {
        $sig = $this->signatories($status, $supervisor, $manager, $admin, $monthDate);
        $now = now()->toDateTimeString();

        foreach ($this->farmers[$siteName] as $farmer) {
            if (DB::table('hybrid_distributions')
                ->where('field_site_id', $site->id)
                ->where('report_month', $monthDate->format('Y-m-d'))
                ->where('farmer_last_name', $farmer['last'])
                ->whereNull('deleted_at')
                ->exists()
            ) {
                continue;
            }

            $seedlings = 50 + ($m * 10) + ($farmer['male'] ? 5 : 0);

            DB::table('hybrid_distributions')->insert(array_merge([
                'field_site_id'         => $site->id,
                'report_month'          => $monthDate->format('Y-m-d'),
                'status'                => $status,
                'region'                => $cfg['region'],
                'province'              => $cfg['province'],
                'district'              => $cfg['district'],
                'municipality'          => $cfg['municipality'],
                'barangay'              => $cfg['barangay'],
                'farmer_last_name'      => $farmer['last'],
                'farmer_first_name'     => $farmer['first'],
                'farmer_middle_initial' => $farmer['mi'],
                'is_male'               => $farmer['male'],
                'is_female'             => $farmer['female'],
                'farm_barangay'         => $farmer['brgy'],
                'farm_municipality'     => $farmer['mun'],
                'farm_province'         => $farmer['prov'],
                'seedlings_received'    => (string) $seedlings,
                'date_received'         => $monthDate->copy()->setDay(15)->format('Y-m-d'),
                'variety'               => $farmer['variety'],
                'seedlings_planted'     => $seedlings,
                'date_planted'          => $monthDate->copy()->setDay(20)->format('Y-m-d'),
                'remarks'               => null,
                'created_at'            => $now,
                'updated_at'            => $now,
            ], $sig));
        }
    }

    // ─── 3 & 3b. Nursery Operations ──────────────────────────────────────────

    private function seedNurseryOperation(
        FieldSite $site, array $cfg, string $siteName,
        Carbon $monthDate, int $m, string $status,
        ?User $supervisor, ?User $manager, ?User $admin,
        string $reportType
    ): void {
        if (DB::table('nursery_operations')
            ->where('field_site_id', $site->id)
            ->where('report_month', $monthDate->format('Y-m-d'))
            ->where('report_type', $reportType)
            ->whereNull('deleted_at')
            ->exists()
        ) {
            return;
        }

        $sig = $this->signatories($status, $supervisor, $manager, $admin, $monthDate);
        $now = now()->toDateTimeString();

        $nurseryStart = null;
        $readyDate    = null;
        if ($reportType === 'terminal') {
            $nurseryStart = Carbon::create(2026, 1, 15)->format('Y-m-d');
            $readyDate    = $monthDate->copy()->subMonths(2)->setDay(28)->format('Y-m-d');
        }

        $opId = DB::table('nursery_operations')->insertGetId(array_merge([
            'field_site_id'               => $site->id,
            'report_month'                => $monthDate->format('Y-m-d'),
            'report_type'                 => $reportType,
            'status'                      => $status,
            'region_province_district'    => $cfg['rpd'],
            'barangay_municipality'       => $cfg['bm'],
            'proponent_entity'            => $cfg['proponent'],
            'proponent_representative'    => $cfg['rep'],
            'target_seednuts'             => $cfg['target_sn'],
            'nursery_start_date'          => $nurseryStart,
            'date_ready_for_distribution' => $readyDate,
            'created_at'                  => $now,
            'updated_at'                  => $now,
        ], $sig));

        // Batch values
        if ($reportType === 'terminal') {
            [$harvested, $germinated, $ungerm, $culled, $good, $ready, $dispatched]
                = [1200, 960, 72, 25, 863, 800, 800];
        } else {
            [$harvested, $germinated, $ungerm, $culled, $good, $ready, $dispatched]
                = $this->nurseryPerMonth[$m - 1];
        }

        $harvestStr = $monthDate->copy()->setDay(8)->format('F d, Y');
        $recvStr    = $monthDate->copy()->setDay(10)->format('F d, Y');
        $sownStr    = $monthDate->copy()->setDay(12)->format('F d, Y');

        $batchId = DB::table('nursery_batches')->insertGetId([
            'nursery_operation_id' => $opId,
            'seednuts_harvested'   => $harvested,
            'culled_seednuts'      => $culled,
            'date_harvested'       => $harvestStr,
            'date_received'        => $recvStr,
            'source_of_seednuts'   => 'PCA Bohol Station',
            'created_at'           => $now,
            'updated_at'           => $now,
        ]);

        // Two varieties per batch, split 60/40
        foreach ($cfg['varieties'] as $idx => $variety) {
            $ratio = $idx === 0 ? 0.60 : 0.40;
            $sown  = (int) round($harvested * $ratio);
            $germ  = (int) round($sown * 0.88);

            DB::table('nursery_batch_varieties')->insert([
                'nursery_batch_id'     => $batchId,
                'variety'              => $variety['name'],
                'seednuts_sown'        => $sown,
                'date_sown'            => $sownStr,
                'seedlings_germinated' => $germ,
                'ungerminated_seednuts'=> max(0, $sown - $germ - (int) round($sown * 0.05)),
                'culled_seedlings'     => (int) round($germ * 0.04),
                'good_seedlings'       => (int) round($germ * 0.92),
                'ready_to_plant'       => (int) round($germ * 0.88),
                'seedlings_dispatched' => $reportType === 'terminal'
                    ? (int) round($germ * 0.88)
                    : (int) round($dispatched * $ratio),
                'remarks'              => null,
                'created_at'           => $now,
                'updated_at'           => $now,
            ]);
        }
    }

    // ─── 4. Pollen Productions (carry-forward) ────────────────────────────────

    private function seedPollenProductions(
        FieldSite $site, array $cfg, string $siteName,
        ?User $supervisor, ?User $manager, ?User $admin
    ): void {
        $now   = now()->toDateTimeString();
        $mult  = $cfg['pollen_mult'];
        $carry = 0; // carry-forward ending balance

        for ($m = 1; $m <= 8; $m++) {
            $monthDate = Carbon::create(2026, $m, 1);

            // If record exists, sync carry-forward and skip
            $existing = DB::table('pollen_productions')
                ->where('field_site_id', $site->id)
                ->where('report_month', $monthDate->format('Y-m-d'))
                ->whereNull('deleted_at')
                ->first();

            if ($existing) {
                $carry = (int) $existing->ending_balance;
                continue;
            }

            $status = $this->statusFor($m);
            $sig    = $this->signatories($status, $supervisor, $manager, $admin, $monthDate);

            [, $recvBase, $w1b, $w2b, $w3b, $w4b] = $this->pollenBase[$m - 1];

            $recv  = (int) round($recvBase * $mult);
            $w1    = (int) round($w1b * $mult);
            $w2    = (int) round($w2b * $mult);
            $w3    = (int) round($w3b * $mult);
            $w4    = (int) round($w4b * $mult);
            $total = $w1 + $w2 + $w3 + $w4;
            $end   = max(0, $carry + $recv - $total);

            DB::table('pollen_productions')->insert(array_merge([
                'field_site_id'     => $site->id,
                'report_month'      => $monthDate->format('Y-m-d'),
                'status'            => $status,
                'month_label'       => $this->monthLabels[$m],
                'pollen_variety'    => $cfg['pollen_variety'],
                'ending_balance_prev'=> (string) $carry,
                'pollen_source'     => $cfg['pollen_source'],
                'date_received'     => $monthDate->copy()->setDay(5)->format('F d, Y'),
                'pollens_received'  => (string) $recv,
                'week1'             => (string) $w1,
                'week2'             => (string) $w2,
                'week3'             => (string) $w3,
                'week4'             => (string) $w4,
                'week5'             => '0',
                'total_utilization' => (string) $total,
                'ending_balance'    => (string) $end,
                'remarks'           => null,
                'created_at'        => $now,
                'updated_at'        => $now,
            ], $sig));

            $carry = $end;
        }
    }
}
