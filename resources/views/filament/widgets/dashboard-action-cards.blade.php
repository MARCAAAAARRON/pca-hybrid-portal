<x-filament-widgets::widget>
    <style>
        .action-cards-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .action-cards-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (min-width: 769px) and (max-width: 1024px) {
            .action-cards-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .action-card {
            border-radius: 0.75rem;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            transition: box-shadow 0.3s;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        .action-card:hover {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .action-card-header {
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            border-bottom-width: 1px;
        }

        .action-card-title {
            font-weight: 500;
            font-size: 1.125rem;
        }

        .action-card-body {
            padding: 1rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .action-img-container {
            height: 9rem;
            border-radius: 0.5rem;
            overflow: hidden;
            margin-bottom: 1rem;
            position: relative;
        }

        .action-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }

        .action-img:hover {
            transform: scale(1.05);
        }

        .action-count {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
        }

        .action-subtext {
            font-size: 0.75rem;
            margin: 0;
        }

        .action-info-box {
            margin-top: auto;
            padding: 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            border-width: 1px;
            border-style: solid;
        }

        /* Light Mode Classes */
        .action-card {
            background-color: white;
            border: 1px solid #f3f4f6;
        }

        .action-card-header {
            color: #085a8a;
            border-bottom-color: #f9fafb;
        }

        .action-img-container {
            background-color: #f3f4f6;
        }

        .action-count {
            color: #1f2937;
        }

        .action-subtext {
            color: #6b7280;
        }

        .btn-primary {
            background-color: #2d62a2;
            color: white;
            transition: background-color 0.2s;
        }

        .btn-primary:hover {
            background-color: #1e487a;
        }

        .btn-secondary {
            background-color: #bae6fd;
            color: #0369a1;
            transition: background-color 0.2s;
        }

        .btn-secondary:hover {
            background-color: #7dd3fc;
        }

        .info-harvest {
            background-color: #f0f9ff;
            color: #085a8a;
            border-color: #bae6fd;
        }

        .info-nursery {
            background-color: #ecfdf5;
            color: #047857;
            border-color: #a7f3d0;
        }

        .info-pollen {
            background-color: #fefce8;
            color: #854d0e;
            border-color: #fef08a;
        }

        .btn-yellow {
            background-color: #fde047;
            color: #854d0e;
            transition: background-color 0.2s;
        }

        .btn-yellow:hover {
            background-color: #facc15;
        }

        .btn-outline {
            background-color: transparent;
            color: #0369a1;
            border: 1px solid #bae6fd;
            transition: background-color 0.2s;
        }

        .btn-outline:hover {
            background-color: #f9fafb;
        }

        /* Dark Mode Classes */
        .dark .action-card {
            background-color: #18181b;
            border-color: #27272a;
        }

        .dark .action-card-header {
            color: #38bdf8;
            border-bottom-color: #27272a;
        }

        .dark .action-img-container {
            background-color: #27272a;
        }

        .dark .action-count {
            color: #f4f4f5;
        }

        .dark .action-subtext {
            color: #a1a1aa;
        }

        .dark .btn-primary {
            background-color: #2563eb;
            color: white;
        }

        .dark .btn-primary:hover {
            background-color: #1d4ed8;
        }

        .dark .btn-secondary {
            background-color: #0284c7;
            color: white;
        }

        .dark .btn-secondary:hover {
            background-color: #0369a1;
        }

        .dark .info-harvest {
            background-color: rgba(14, 165, 233, 0.1);
            color: #7dd3fc;
            border-color: #0284c7;
        }

        .dark .info-nursery {
            background-color: rgba(16, 185, 129, 0.1);
            color: #6ee7b7;
            border-color: #059669;
        }

        .dark .info-pollen {
            background-color: rgba(234, 179, 8, 0.1);
            color: #fdf08a;
            border-color: #ca8a04;
        }

        .dark .btn-yellow {
            background-color: #ca8a04;
            color: white;
        }

        .dark .btn-yellow:hover {
            background-color: #a16207;
        }

        .dark .btn-outline {
            color: #7dd3fc;
            border-color: #0284c7;
        }

        .dark .btn-outline:hover {
            background-color: rgba(2, 132, 199, 0.2);
        }

        .badge-new {
            position: absolute;
            bottom: 0.5rem;
            right: 0.5rem;
            padding: 0.25rem 0.75rem;
            border-radius: 0.25rem;
            font-weight: bold;
            font-size: 0.75rem;
            text-transform: uppercase;
        }

        .badge-light {
            background-color: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(4px);
            border: 1px solid #bae6fd;
            color: #0284c7;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        .dark .badge-light {
            background-color: rgba(24, 24, 27, 0.85);
            border-color: #0c4a6e;
            color: #38bdf8;
        }
    </style>

    <div class="action-cards-grid">
        @php
            $isSubSupervisor = auth()->user()?->role === 'sub_supervisor';
        @endphp

        @if($isSubSupervisor)
            <!-- Card 1 (Sub-Supervisor): Distribution -->
            <div class="action-card w-full">
                <div class="action-card-header">
                    <x-filament::icon icon="heroicon-o-truck" style="height: 1.25rem; width: 1.25rem;" />
                    <span class="action-card-title">Hybrid Distribution</span>
                </div>
                <div class="action-card-body">
                    <div class="action-img-container">
                        <img src="{{ asset('images/coconut-palm.jpg') }}" class="action-img" alt="Distribution" />
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 1.5rem;">
                        <div>
                            <h4 class="action-count">{{ App\Models\HybridDistribution::whereYear('report_month', $year)->count() }}</h4>
                            <p class="action-subtext">Distributions in {{ $year }}</p>
                        </div>
                        <a href="{{ App\Filament\Resources\HybridDistributionResource::getUrl('index') }}" class="btn-primary"
                            style="padding: 0.625rem 1.25rem; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 500; text-decoration: none;">View Log</a>
                    </div>
                    <div class="action-info-box info-harvest">
                        <x-filament::icon icon="heroicon-o-information-circle" style="width: 1.25rem; height: 1.25rem; flex-shrink: 0;" />
                        <span style="line-height: 1.375;">Monitor external seedling distributions and track farmer allocations.</span>
                    </div>
                </div>
            </div>

            <!-- Card 2 (Sub-Supervisor): Nursery Operations -->
            <div class="action-card w-full">
                <div class="action-card-header">
                    <x-filament::icon icon="heroicon-o-sun" style="height: 1.25rem; width: 1.25rem;" />
                    <span class="action-card-title">Nursery Operations</span>
                </div>
                <div class="action-card-body">
                    <div class="action-img-container">
                        <img src="{{ asset('images/card_nursery.png') }}" class="action-img" alt="Nursery" />
                        <div class="badge-new badge-light">+ New Event</div>
                    </div>
                    <div class="action-info-box info-nursery" style="margin-bottom: 1.5rem; min-height: 4.5rem;">
                        <x-filament::icon icon="heroicon-o-information-circle" style="width: 1.25rem; height: 1.25rem; flex-shrink: 0;" />
                        <span style="line-height: 1.375;">Track sowing, germination and seedling development phases in real-time.</span>
                    </div>
                    <a href="{{ App\Filament\Resources\NurseryOperationResource::getUrl('create') }}" class="btn-secondary"
                        style="display: block; width: 100%; text-align: center; padding: 0.625rem 1rem; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 600; text-decoration: none;">Start Operation &rarr;</a>
                </div>
            </div>

            <!-- Card 3 (Sub-Supervisor): Terminal Reports -->
            <div class="action-card w-full">
                <div class="action-card-header">
                    <x-filament::icon icon="heroicon-o-clipboard-document-check" style="height: 1.25rem; width: 1.25rem;" />
                    <span class="action-card-title">Terminal Reports</span>
                </div>
                <div class="action-card-body">
                    <div class="action-img-container">
                        <img src="{{ asset('images/card_harvest.png') }}" class="action-img" alt="Terminal" />
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 1.5rem;">
                        <div>
                            <h4 class="action-count">{{ App\Models\NurseryOperation::where('report_type', 'terminal')->whereYear('report_month', $year)->count() }}</h4>
                            <p class="action-subtext">Reports in {{ $year }}</p>
                        </div>
                        <a href="{{ App\Filament\Resources\TerminalResource::getUrl('index') ?? '#' }}" class="btn-primary"
                            style="padding: 0.625rem 1.25rem; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 500; text-decoration: none;">View Reports</a>
                    </div>
                    <div class="action-info-box info-pollen">
                        <x-filament::icon icon="heroicon-o-information-circle" style="width: 1.25rem; height: 1.25rem; flex-shrink: 0;" />
                        <span style="line-height: 1.375;">Manage terminal nursery operations and finalize crop cycles.</span>
                    </div>
                </div>
            </div>
        @else
        <!-- Card 1 -->
        <div class="action-card w-full">
            <div class="action-card-header">
                <x-filament::icon icon="heroicon-o-clipboard-document-list" style="height: 1.25rem; width: 1.25rem;" />
                <span class="action-card-title">Hybrid Harvests</span>
            </div>
            <div class="action-card-body">
                <div class="action-img-container">
                    <img src="{{ asset('images/card_harvest.png') }}" class="action-img" alt="Harvest" />
                </div>
                <div
                    style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 1.5rem;">
                    <div>
                        <h4 class="action-count">{{ App\Models\MonthlyHarvest::whereYear('report_month', $year)->count() }}</h4>
                        <p class="action-subtext">Harvest records in {{ $year }}</p>
                    </div>
                    <a href="{{ App\Filament\Resources\MonthlyHarvestResource::getUrl('index') }}" class="btn-primary"
                        style="padding: 0.625rem 1.25rem; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 500; text-decoration: none;">View
                        Harvests</a>
                </div>
                <div class="action-info-box info-harvest">
                    <x-filament::icon icon="heroicon-o-information-circle"
                        style="width: 1.25rem; height: 1.25rem; flex-shrink: 0;" />
                    <span style="line-height: 1.375;">View and analyze all seednut records across testing sites and
                        monitor yields.</span>
                </div>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="action-card w-full">
            <div class="action-card-header">
                <x-filament::icon icon="heroicon-o-sun" style="height: 1.25rem; width: 1.25rem;" />
                <span class="action-card-title">Nursery Operations</span>
            </div>
            <div class="action-card-body">
                <div class="action-img-container">
                    <img src="{{ asset('images/card_nursery.png') }}" class="action-img" alt="Nursery" />
                    <!-- Overlay Badge -->
                    <div class="badge-new badge-light">
                        + New Event
                    </div>
                </div>
                <div class="action-info-box info-nursery" style="margin-bottom: 1.5rem; min-height: 4.5rem;">
                    <x-filament::icon icon="heroicon-o-information-circle"
                        style="width: 1.25rem; height: 1.25rem; flex-shrink: 0;" />
                    <span style="line-height: 1.375;">Track sowing, germination and seedling development phases in
                        real-time.</span>
                </div>
                <a href="{{ App\Filament\Resources\NurseryOperationResource::getUrl('create') }}" class="btn-secondary"
                    style="display: block; width: 100%; text-align: center; padding: 0.625rem 1rem; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 600; text-decoration: none;">Start
                    Operation &rarr;</a>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="action-card w-full">
            <div class="action-card-header">
                <x-filament::icon icon="heroicon-o-beaker" style="height: 1.25rem; width: 1.25rem;" />
                <span class="action-card-title">Pollen & Distribution</span>
            </div>
            <div class="action-card-body">
                <div class="action-img-container">
                    <img src="{{ asset('images/coconut-palm.jpg') }}" class="action-img" alt="Pollen" />
                </div>
                <div class="action-info-box info-pollen" style="margin-bottom: 1.5rem; min-height: 4.5rem;">
                    <x-filament::icon icon="heroicon-o-information-circle"
                        style="width: 1.25rem; height: 1.25rem; flex-shrink: 0;" />
                    <span style="line-height: 1.375;">Manage internal pollen stocks, extraction records, and monitor
                        external seedling distributions.</span>
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <a href="{{ App\Filament\Resources\PollenProductionResource::getUrl('index') }}" class="btn-yellow"
                        style="flex: 1; text-align: center; padding: 0.625rem 0.75rem; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 600; text-decoration: none;">Pollen
                        Stock</a>
                    <a href="{{ App\Filament\Resources\HybridDistributionResource::getUrl('index') }}"
                        class="btn-outline"
                        style="flex: 1; text-align: center; padding: 0.625rem 0.75rem; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 600; text-decoration: none;">Distribution</a>
                </div>
            </div>
        </div>
        @endif
    </div>
</x-filament-widgets::widget>