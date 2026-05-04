<x-filament-widgets::widget>
    <style>
        /* Services Outer Container */
        .services-container {
            background-color: white;
            border-color: #f3f4f6;
        }

        .services-header-icon-bg {
            background-color: #ffedd5;
        }

        .services-header-icon {
            color: #ea580c;
        }

        .services-title {
            color: #1f2937;
        }

        /* Service Sub-Cards */
        .service-sub-card {
            background-color: #ccebfa;
            border-color: #e0f2fe;
        }

        .service-img-container {
            background-color: white;
        }

        .service-icon-bg {
            background-color: white;
            border-color: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .service-btn {
            border-color: #0284c7;
            color: #0284c7;
            background-color: transparent;
        }

        .service-btn:hover {
            background-color: white;
        }

        /* Quick Links Outer Container */
        .quicklinks-container {
            background-color: #f0f9ff;
            border-color: #dbeafe;
        }

        .quicklinks-swirl {
            background-color: #dbeafe;
            mix-blend-mode: multiply;
            opacity: 0.5;
        }

        .quicklinks-header-icon-bg {
            background-color: #dbeafe;
            border-color: #bfdbfe;
        }

        .quicklinks-item-bg {
            background-color: white;
            border-color: #dbeafe;
        }

        .quicklinks-item-title {
            color: #0369a1;
        }

        .quicklinks-item-desc {
            color: #6b7280;
        }

        .quicklinks-item:hover .quicklinks-item-title {
            color: #0c4a6e;
        }

        .quicklinks-item:hover .quicklinks-item-bg {
            background-color: #bae6fd;
        }

        /* General layout classes */
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        /* Dark Mode Classes */
        .dark .services-container {
            background-color: #18181b;
            border-color: #27272a;
        }

        .dark .services-header-icon-bg {
            background-color: rgba(234, 88, 12, 0.1);
        }

        .dark .services-header-icon {
            color: #fdba74;
        }

        .dark .services-title {
            color: #f4f4f5;
        }

        .dark .service-sub-card {
            background-color: rgba(2, 132, 199, 0.15);
            border-color: #0c4a6e;
        }

        .dark .service-img-container {
            background-color: #27272a;
        }

        .dark .service-icon-bg {
            background-color: #18181b;
            border-color: #27272a;
        }

        .dark .service-btn {
            border-color: #38bdf8;
            color: #38bdf8;
        }

        .dark .service-btn:hover {
            background-color: rgba(56, 189, 248, 0.1);
        }

        .dark .quicklinks-container {
            background-color: #18181b;
            border-color: #27272a;
        }

        .dark .quicklinks-swirl {
            background-color: #0c4a6e;
            opacity: 0.2;
        }

        .dark .quicklinks-header-icon-bg {
            background-color: rgba(2, 132, 199, 0.2);
            border-color: #0c4a6e;
        }

        .dark .quicklinks-item-bg {
            background-color: #27272a;
            border-color: #3f3f46;
        }

        .dark .quicklinks-item-title {
            color: #38bdf8;
        }

        .dark .quicklinks-item-desc {
            color: #a1a1aa;
        }

        .dark .quicklinks-item:hover .quicklinks-item-title {
            color: #7dd3fc;
        }

        .dark .quicklinks-item:hover .quicklinks-item-bg {
            background-color: rgba(2, 132, 199, 0.2);
        }
    </style>

    <div style="display: flex; flex-wrap: wrap; gap: 1.5rem;">
        <!-- Services Section (Spans 2 cols conceptually) -->
        <div class="services-container"
            style="flex: 2; min-width: 300px; border-radius: 0.75rem; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); border-width: 1px; border-style: solid; padding: 1.5rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 2rem;">
                <div class="services-header-icon-bg" style="padding: 0.375rem; border-radius: 0.5rem;">
                    <x-filament::icon icon="heroicon-s-cursor-arrow-rays" class="services-header-icon"
                        style="width: 1.25rem; height: 1.25rem;" />
                </div>
                <h3 class="services-title"
                    style="font-size: 1.25rem; font-weight: 500; letter-spacing: -0.025em; margin: 0;">Services</h3>
            </div>

            <div style="display: flex; flex-wrap: wrap; gap: 1.25rem; padding-bottom: 1rem;" class="hide-scrollbar">
                <!-- Service Card 1 -->
                <div class="service-sub-card"
                    style="flex: 1; min-width: 180px; border-radius: 1.25rem; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border-width: 1px; border-style: solid; position: relative; min-height: 17.5rem; padding-bottom: 0.5rem; display: flex; flex-direction: column; transition: transform 0.2s;"
                    onmouseover="this.style.transform='translateY(-4px)'"
                    onmouseout="this.style.transform='translateY(0)'">
                    <div class="service-img-container" style="height: 8.5rem; position: relative;">
                        <img src="{{ asset('images/card_harvest.png') }}"
                            style="width: 100%; height: 100%; object-fit: cover; border-bottom-right-radius: 5rem;"
                            alt="Generate Report" />
                    </div>

                    <!-- Floating Icon -->
                    <div class="service-icon-bg"
                        style="position: absolute; top: 6.5rem; left: 50%; transform: translateX(-50%); padding: 0.75rem; border-radius: 1rem; border-style: solid; border-width: 1px; z-index: 10; display: flex; justify-content: center; align-items: center;">
                        <x-filament::icon icon="heroicon-o-document-chart-bar"
                            style="width: 2rem; height: 2rem; color: #0284c7;" />
                    </div>

                    <div
                        style="padding: 1rem; padding-top: 2.5rem; text-align: center; flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
                        <h4
                            style="font-size: 1.05rem; font-weight: 700; color: #0369a1; margin-bottom: 0.5rem; line-height: 1.2;">
                            Generate<br />Report</h4>
                        <a href="{{ App\Filament\Resources\ReportResource::getUrl('index') ?? '#' }}"
                            class="service-btn"
                            style="display: inline-flex; align-items: center; justify-content: center; border-width: 1px; border-style: solid; gap: 0.25rem; padding: 0.375rem 0; width: 70%; margin: 0 auto; border-radius: 0.5rem; font-size: 0.875rem; font-weight: 500; text-decoration: none; transition: all 0.2s;">
                            Apply <x-filament::icon icon="heroicon-o-arrow-right"
                                style="width: 0.875rem; height: 0.875rem; margin-left: 0.125rem;" />
                        </a>
                    </div>
                </div>

                <!-- Service Card 2 -->
                <div class="service-sub-card"
                    style="flex: 1; min-width: 180px; border-radius: 1.25rem; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border-width: 1px; border-style: solid; position: relative; min-height: 17.5rem; padding-bottom: 0.5rem; display: flex; flex-direction: column; transition: transform 0.2s;"
                    onmouseover="this.style.transform='translateY(-4px)'"
                    onmouseout="this.style.transform='translateY(0)'">
                    <div class="service-img-container" style="height: 8.5rem; position: relative;">
                        <img src="{{ asset('images/coconut-palm.jpg') }}"
                            style="width: 100%; height: 100%; object-fit: cover; border-bottom-right-radius: 5rem;"
                            alt="Hybrid Records" />
                    </div>

                    <!-- Floating Icon -->
                    <div class="service-icon-bg"
                        style="position: absolute; top: 6.5rem; left: 50%; transform: translateX(-50%); padding: 0.75rem; border-radius: 1rem; border-style: solid; border-width: 1px; z-index: 10; display: flex; justify-content: center; align-items: center;">
                        <x-filament::icon icon="heroicon-o-squares-plus"
                            style="width: 2rem; height: 2rem; color: #16a34a;" />
                    </div>

                    <div
                        style="padding: 1rem; padding-top: 2.5rem; text-align: center; flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
                        <h4
                            style="font-size: 1.05rem; font-weight: 700; color: #0369a1; margin-bottom: 0.5rem; line-height: 1.2;">
                            Hybrid<br />Records</h4>
                        <a href="{{ App\Filament\Resources\HybridizationRecordResource::getUrl('create') ?? '#' }}"
                            class="service-btn"
                            style="display: inline-flex; align-items: center; justify-content: center; gap: 0.25rem; border-style: solid; border-width: 1px; padding: 0.375rem 0; width: 70%; margin: 0 auto; border-radius: 0.5rem; font-size: 0.875rem; font-weight: 500; text-decoration: none; transition: all 0.2s;">
                            Apply <x-filament::icon icon="heroicon-o-arrow-right"
                                style="width: 0.875rem; height: 0.875rem; margin-left: 0.125rem;" />
                        </a>
                    </div>
                </div>

                <!-- Service Card 3 -->
                <div class="service-sub-card"
                    style="flex: 1; min-width: 180px; border-radius: 1.25rem; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border-width: 1px; border-style: solid; position: relative; min-height: 17.5rem; padding-bottom: 0.5rem; display: flex; flex-direction: column; transition: transform 0.2s;"
                    onmouseover="this.style.transform='translateY(-4px)'"
                    onmouseout="this.style.transform='translateY(0)'">
                    <div class="service-img-container" style="height: 8.5rem; position: relative;">
                        <img src="{{ asset('images/card_nursery.png') }}"
                            style="width: 100%; height: 100%; object-fit: cover; border-bottom-right-radius: 5rem;"
                            alt="Hybrid Distribution" />
                    </div>

                    <!-- Floating Icon -->
                    <div class="service-icon-bg"
                        style="position: absolute; top: 6.5rem; left: 50%; transform: translateX(-50%); padding: 0.75rem; border-radius: 1rem; border-style: solid; border-width: 1px; z-index: 10; display: flex; justify-content: center; align-items: center;">
                        <x-filament::icon icon="heroicon-o-truck" style="width: 2rem; height: 2rem; color: #0284c7;" />
                    </div>

                    <div
                        style="padding: 1rem; padding-top: 2.5rem; text-align: center; flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
                        <h4
                            style="font-size: 1.05rem; font-weight: 700; color: #0369a1; margin-bottom: 0.5rem; line-height: 1.2;">
                            Hybrid<br />Distribution</h4>
                        <a href="{{ App\Filament\Resources\HybridDistributionResource::getUrl('create') ?? '#' }}"
                            class="service-btn"
                            style="display: inline-flex; align-items: center; border-style: solid; border-width: 1px; justify-content: center; gap: 0.25rem; padding: 0.375rem 0; width: 70%; margin: 0 auto; border-radius: 0.5rem; font-size: 0.875rem; font-weight: 500; text-decoration: none; transition: all 0.2s;">
                            Apply <x-filament::icon icon="heroicon-o-arrow-right"
                                style="width: 0.875rem; height: 0.875rem; margin-left: 0.125rem;" />
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Links (Spans 1 col conceptually) -->
        <div class="quicklinks-container"
            style="flex: 1; min-width: 250px; border-radius: 0.75rem; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); border-width: 1px; border-style: solid; padding: 1.5rem; position: relative; overflow: hidden;">
            <!-- Decorative light blue swirl -->
            <div class="quicklinks-swirl"
                style="position: absolute; top: 0; right: 0; width: 8rem; height: 8rem; border-bottom-left-radius: 9999px; z-index: 0;">
            </div>

            <div
                style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.5rem; position: relative; z-index: 10;">
                <div class="quicklinks-header-icon-bg"
                    style="padding: 0.375rem; border-radius: 9999px; border-style: solid; border-width: 1px; box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.06);">
                    <x-filament::icon icon="heroicon-s-link" style="width: 1rem; height: 1rem; color: #0284c7;" />
                </div>
                <h3 class="services-title"
                    style="font-size: 1.25rem; font-weight: 500; letter-spacing: -0.025em; margin: 0;">Quick Links</h3>
            </div>

            <ul
                style="list-style: none; padding: 0; margin: 0; position: relative; z-index: 10; padding-left: 0.5rem; display: flex; flex-direction: column; gap: 1rem;">
                <li>
                    <a href="{{ App\Filament\Resources\MonthlyHarvestResource::getUrl('index') ?? '#' }}"
                        class="quicklinks-item"
                        style="display: flex; align-items: center; gap: 1rem; text-decoration: none; transition: color 0.2s;">
                        <div class="quicklinks-item-bg"
                            style="padding: 0.625rem; border-radius: 0.5rem; border-width: 1px; border-style: solid; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); transition: background-color 0.2s;">
                            <x-filament::icon icon="heroicon-s-clipboard-document-list"
                                style="width: 1.25rem; height: 1.25rem; color: #3b82f6; opacity: 0.8;" />
                        </div>
                        <span class="quicklinks-item-title"
                            style="font-weight: 500; font-size: 0.875rem; line-height: 1.25; transition: color 0.2s;">Harvest
                            Records<br /><span class="quicklinks-item-desc"
                                style="font-size: 0.75rem; font-weight: 400; opacity: 0.8;">Track yields</span></span>
                    </a>
                </li>
                <li>
                    <a href="{{ App\Filament\Resources\NurseryOperationResource::getUrl('index') ?? '#' }}"
                        class="quicklinks-item"
                        style="display: flex; align-items: center; gap: 1rem; text-decoration: none; transition: color 0.2s;">
                        <div class="quicklinks-item-bg"
                            style="padding: 0.625rem; border-radius: 0.5rem; border-width: 1px; border-style: solid; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); transition: background-color 0.2s;">
                            <x-filament::icon icon="heroicon-s-building-storefront"
                                style="width: 1.25rem; height: 1.25rem; color: #3b82f6; opacity: 0.8;" />
                        </div>
                        <span class="quicklinks-item-title"
                            style="font-weight: 500; font-size: 0.875rem; line-height: 1.25; transition: color 0.2s;">Nursery
                            Enrollments<br /><span class="quicklinks-item-desc"
                                style="font-size: 0.75rem; font-weight: 400; opacity: 0.8;">Manage batches</span></span>
                    </a>
                </li>
                <li>
                    <a href="{{ App\Filament\Resources\PollenProductionResource::getUrl('index') ?? '#' }}"
                        class="quicklinks-item"
                        style="display: flex; align-items: center; gap: 1rem; text-decoration: none; transition: color 0.2s;">
                        <div class="quicklinks-item-bg"
                            style="padding: 0.625rem; border-radius: 0.5rem; border-width: 1px; border-style: solid; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); transition: background-color 0.2s;">
                            <x-filament::icon icon="heroicon-s-beaker"
                                style="width: 1.25rem; height: 1.25rem; color: #3b82f6; opacity: 0.8;" />
                        </div>
                        <span class="quicklinks-item-title"
                            style="font-weight: 500; font-size: 0.875rem; line-height: 1.25; transition: color 0.2s;">Pollen
                            Production<br /><span class="quicklinks-item-desc"
                                style="font-size: 0.75rem; font-weight: 400; opacity: 0.8;">Extracts &
                                stocks</span></span>
                    </a>
                </li>
                <li>
                    <a href="{{ App\Filament\Resources\HybridDistributionResource::getUrl('index') ?? '#' }}"
                        class="quicklinks-item"
                        style="display: flex; align-items: center; gap: 1rem; text-decoration: none; transition: color 0.2s;">
                        <div class="quicklinks-item-bg"
                            style="padding: 0.625rem; border-radius: 0.5rem; border-width: 1px; border-style: solid; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); transition: background-color 0.2s;">
                            <x-filament::icon icon="heroicon-s-truck"
                                style="width: 1.25rem; height: 1.25rem; color: #3b82f6; opacity: 0.8;" />
                        </div>
                        <span class="quicklinks-item-title"
                            style="font-weight: 500; font-size: 0.875rem; line-height: 1.25; transition: color 0.2s;">Distribution
                            Tracker<br /><span class="quicklinks-item-desc"
                                style="font-size: 0.75rem; font-weight: 400; opacity: 0.8;">Transfer logs</span></span>
                    </a>
                </li>
                <li>
                    <a href="{{ App\Filament\Pages\ReportsDashboard::getUrl() }}" class="quicklinks-item"
                        style="display: flex; align-items: center; gap: 1rem; text-decoration: none; transition: color 0.2s;">
                        <div class="quicklinks-item-bg"
                            style="padding: 0.625rem; border-radius: 0.5rem; border-width: 1px; border-style: solid; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); transition: background-color 0.2s; background-color: #22c55e; border-color: #16a34a;">
                            <x-filament::icon icon="heroicon-s-document-chart-bar"
                                style="width: 1.25rem; height: 1.25rem; color: white;" />
                        </div>
                        <span class="quicklinks-item-title"
                            style="font-weight: 700; font-size: 0.875rem; line-height: 1.25; transition: color 0.2s; color: #16a34a;">Excel
                            Export Center<br /><span class="quicklinks-item-desc"
                                style="font-size: 0.75rem; font-weight: 400; opacity: 0.8;">Standard
                                reports</span></span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</x-filament-widgets::widget>