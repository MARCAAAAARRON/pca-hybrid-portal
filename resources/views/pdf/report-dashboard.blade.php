<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'PCA Report' }}</title>
    <style>
        @page {
            margin: 0.4in 0.3in 0.4in 0.3in;
        }
        body {
            font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif;
            font-size: 9px;
            line-height: 1.3;
            color: #000;
            margin: 0;
            padding: 0;
        }

        /* ── Page Break ── */
        .page-break {
            page-break-before: always;
        }

        /* ── PCA Header ── */
        .pca-header {
            text-align: center;
            margin-bottom: 15px;
        }
        .pca-header-table {
            width: auto;
            margin: 0 auto;
            border: none;
        }
        .pca-header-table td {
            border: none;
            vertical-align: middle;
        }
        .pca-logo {
            width: 55px;
            height: 55px;
        }
        .pca-title {
            font-size: 13px;
            font-weight: bold;
            margin: 0;
        }
        .pca-subtitle {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 2px 0;
        }
        .pca-category {
            font-size: 10px;
            text-transform: uppercase;
            margin: 2px 0;
        }
        .pca-period {
            font-size: 10px;
            font-weight: 600;
            text-decoration: underline;
            margin-top: 3px;
        }

        /* ── Signature Block ── */
        .signature-table {
            width: 100%;
            margin-top: 40px;
            border: none;
        }
        .signature-table td {
            border: none;
            width: 33.33%;
            text-align: center;
            vertical-align: bottom;
            padding: 0 15px;
        }
        .signature-label {
            text-align: left;
            padding-left: 15px;
            font-size: 9px;
            color: #333;
            padding-bottom: 10px;
        }
        .signature-img {
            max-height: 45px;
            max-width: 150px;
            margin-bottom: -5px;
        }
        .signatory-name {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
        }
        .signatory-line {
            border-top: 1px solid #000;
            margin-top: 3px;
            padding-top: 3px;
            font-size: 8px;
        }
        .digitally-signed {
            font-style: italic;
            font-size: 8px;
            color: #666;
            margin-bottom: 4px;
        }

        /* ── Footer ── */
        .page-footer {
            margin-top: 30px;
            text-align: center;
            font-size: 7px;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    @php
        // Encode the PCA logo as base64 for reliable dompdf rendering
        $logoPath = public_path('images/PCA_DA_Logo.png');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }

        $categoryLabels = [
            'monthly_harvest'     => 'ON-FARM HYBRID SEEDNUT PRODUCTION',
            'pollen_production'   => 'POLLEN PRODUCTION AND PROCESSING',
            'hybrid_distribution' => 'DISTRIBUTION OF HYBRID SEEDLINGS',
            'nursery_operation'   => 'MONTHLY NURSERY OPERATION REPORT',
            'terminal_report'     => 'TERMINAL REPORT FOR NURSERY OPERATIONS',
        ];
    @endphp

    @foreach($pages as $pageIdx => $page)
        @if($pageIdx > 0)
            <div class="page-break"></div>
        @endif

        @php
            $pageSiteRecords = $page['records'];
            $pageReportFarms = $page['farms'] ?? null;
            $pageCategory = $page['category'];
            $site = $pageSiteRecords->first()->fieldSite ?? null;
        @endphp

        {{-- ═══ PCA HEADER ═══ --}}
        <table class="pca-header-table">
            <tr>
                <td style="width: 65px; text-align: right; padding-right: 8px;">
                    @if($logoBase64)
                        <img src="{{ $logoBase64 }}" class="pca-logo" alt="PCA Logo">
                    @endif
                </td>
                <td style="text-align: center;">
                    <p class="pca-title">PHILIPPINE COCONUT AUTHORITY</p>
                    <p class="pca-subtitle">COCONUT HYBRIDIZATION PROJECT-CFIDP</p>
                    <p class="pca-category">{{ $categoryLabels[$pageCategory] ?? strtoupper($pageCategory) }}</p>
                    <p class="pca-period">{{ $periodStr }}</p>
                </td>
                <td style="width: 65px;"></td>
            </tr>
        </table>

        {{-- ═══ DATA TABLE ═══ --}}
        <div style="margin-top: 20px;">
            @if($pageCategory === 'monthly_harvest')
                @include('pdf.partials.monthly_harvest', ['reportData' => $pageSiteRecords, 'reportFarms' => $pageReportFarms])
            @elseif($pageCategory === 'pollen_production')
                @include('pdf.partials.pollen_production', ['reportData' => $pageSiteRecords])
            @elseif($pageCategory === 'hybrid_distribution')
                @include('pdf.partials.hybrid_distribution', ['reportData' => $pageSiteRecords])
            @elseif(in_array($pageCategory, ['nursery_operation', 'terminal_report']))
                @include('pdf.partials.nursery_operation', ['reportData' => $pageSiteRecords])
            @endif
        </div>

        {{-- ═══ SIGNATURE BLOCK ═══ --}}
        @php
            $selectedMonth = $filterMonth ?? null;
            $selectedYear = $filterYear ?? null;
            $currentMonthRecords = ($selectedMonth && $pageCategory !== 'terminal_report')
                ? $pageSiteRecords->filter(fn($r) => \Carbon\Carbon::parse($r->report_month)->month == $selectedMonth && \Carbon\Carbon::parse($r->report_month)->year == $selectedYear)
                : $pageSiteRecords;
            $statusOrder = ['draft' => 0, 'prepared' => 1, 'reviewed' => 2, 'noted' => 3];
            $minStatus = $currentMonthRecords->isNotEmpty() ? $currentMonthRecords->min(fn($r) => $statusOrder[$r->status] ?? 0) : 0;
            $refRecord = $currentMonthRecords->first();
            $showPrepared = $minStatus >= 1;
            $showReviewed = $minStatus >= 2;
            $showNoted = $minStatus >= 3;

            $prepUser = $showPrepared ? $refRecord?->preparedByUser : null;
            $revUser = $showReviewed ? $refRecord?->reviewedByUser : null;
            $notedUser = $showNoted ? $refRecord?->notedByUser : null;

            $prepName = $showPrepared ? strtoupper($site?->prepared_by_name ?? $prepUser?->name ?? '_______________________') : '_______________________';
            $prepTitle = $site?->prepared_by_title ?? $prepUser?->role_title ?? 'COS/Agriculturist';
            $revName = $showReviewed ? strtoupper($site?->reviewed_by_name ?? $revUser?->name ?? '_______________________') : '_______________________';
            $revTitle = $site?->reviewed_by_title ?? $revUser?->role_title ?? 'Senior Agriculturist';
            $notedName = $showNoted ? strtoupper($site?->noted_by_name ?? $notedUser?->name ?? '_______________________') : '_______________________';
            $notedTitle = $site?->noted_by_title ?? $notedUser?->role_title ?? 'PCDM/Division Chief I';

            // Signature images — try Cloudinary URL then convert to base64 for dompdf
            $prepSigBase64 = null;
            $revSigBase64 = null;
            $notedSigBase64 = null;

            if ($showPrepared && $prepUser?->signature_image) {
                $prepSigUrl = \App\Helpers\SignatureHelper::getSignatureUrl($prepUser->signature_image);
                if ($prepSigUrl) {
                    try {
                        $imgData = @file_get_contents($prepSigUrl);
                        if ($imgData) {
                            $prepSigBase64 = 'data:image/png;base64,' . base64_encode($imgData);
                        }
                    } catch (\Exception $e) {}
                }
            }
            if ($showReviewed && $revUser?->signature_image) {
                $revSigUrl = \App\Helpers\SignatureHelper::getSignatureUrl($revUser->signature_image);
                if ($revSigUrl) {
                    try {
                        $imgData = @file_get_contents($revSigUrl);
                        if ($imgData) {
                            $revSigBase64 = 'data:image/png;base64,' . base64_encode($imgData);
                        }
                    } catch (\Exception $e) {}
                }
            }
            if ($showNoted && $notedUser?->signature_image) {
                $notedSigUrl = \App\Helpers\SignatureHelper::getSignatureUrl($notedUser->signature_image);
                if ($notedSigUrl) {
                    try {
                        $imgData = @file_get_contents($notedSigUrl);
                        if ($imgData) {
                            $notedSigBase64 = 'data:image/png;base64,' . base64_encode($imgData);
                        }
                    } catch (\Exception $e) {}
                }
            }
        @endphp

        <table class="signature-table">
            <tr>
                <td class="signature-label" style="text-align: left;">{{ $site?->prepared_by_label ?? 'Prepared by:' }}</td>
                <td class="signature-label" style="text-align: left;">{{ $site?->reviewed_by_label ?? 'Reviewed by:' }}</td>
                <td class="signature-label" style="text-align: left;">{{ $site?->noted_by_label ?? 'Noted by:' }}</td>
            </tr>
            <tr>
                <td style="border: none; padding: 0 15px; height: 70px; vertical-align: bottom; text-align: center;">
                    @if($prepSigBase64)
                        <img src="{{ $prepSigBase64 }}" class="signature-img" alt="Signature"><br>
                    @elseif($showPrepared && $prepUser?->signature_image)
                        <span class="digitally-signed">Digitally Signed</span><br>
                    @endif
                    <span class="signatory-name">{{ $prepName }}</span>
                    <div class="signatory-line">{{ $prepTitle }}</div>
                </td>
                <td style="border: none; padding: 0 15px; height: 70px; vertical-align: bottom; text-align: center;">
                    @if($revSigBase64)
                        <img src="{{ $revSigBase64 }}" class="signature-img" alt="Signature"><br>
                    @elseif($showReviewed && $revUser?->signature_image)
                        <span class="digitally-signed">Digitally Signed</span><br>
                    @endif
                    <span class="signatory-name">{{ $revName }}</span>
                    <div class="signatory-line">{{ $revTitle }}</div>
                </td>
                <td style="border: none; padding: 0 15px; height: 70px; vertical-align: bottom; text-align: center;">
                    @if($notedSigBase64)
                        <img src="{{ $notedSigBase64 }}" class="signature-img" alt="Signature"><br>
                    @elseif($showNoted && $notedUser?->signature_image)
                        <span class="digitally-signed">Digitally Signed</span><br>
                    @endif
                    <span class="signatory-name">{{ $notedName }}</span>
                    <div class="signatory-line">{{ $notedTitle }}</div>
                </td>
            </tr>
        </table>

    @endforeach

    <div class="page-footer">
        Generated by PCA Hybridization Portal on {{ now()->format('F d, Y h:i A') }}
    </div>

</body>
</html>
