<table class="pca-header-table" style="width: auto; margin: 0 auto 10px auto; border: none;">
    <tr>
        <td style="width: 60px; text-align: right; padding-right: 8px; border: none;">
            @if(!empty($logoBase64))
                <img src="{{ $logoBase64 }}" style="width: 50px; height: 50px;" alt="PCA Logo">
            @endif
        </td>
        <td style="text-align: center; border: none;">
            <p style="font-size: 13px; font-weight: bold; margin: 0; color: #000;">PHILIPPINE COCONUT AUTHORITY</p>
            <p style="font-size: 10px; font-weight: bold; text-transform: uppercase; margin: 2px 0; color: #000;">COCONUT HYBRIDIZATION PROJECT-CFIDP</p>
            <p style="font-size: 10px; text-transform: uppercase; margin: 2px 0; color: #000;">{{ $categoryLabels[$pageCategory] ?? strtoupper($pageCategory) }}</p>
            <p style="font-size: 10px; font-weight: 600; text-decoration: underline; margin-top: 3px; color: #000;">{{ $periodStr }}</p>
        </td>
        <td style="width: 60px; border: none;"></td>
    </tr>
</table>
