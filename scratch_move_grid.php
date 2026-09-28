<?php
$files = [
    'app/Filament/Resources/MonthlyHarvestResource.php',
    'app/Filament/Resources/NurseryOperationResource.php',
    'app/Filament/Resources/PollenProductionResource.php',
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    
    $content = file_get_contents($file);
    
    // The exact string to find:
    $startStr = "                            Forms\Components\Grid::make(2)->schema([";
    $endStr = "                            ])->columnSpanFull(),";
    
    $lines = explode("\n", $content);
    $startIdx = -1;
    $endIdx = -1;
    
    for ($i = 0; $i < count($lines); $i++) {
        if (strpos($lines[$i], "Forms\Components\Grid::make(2)->schema([") !== false && strpos($lines[$i], "loadPrevious") === false) {
            // Check if next lines contain loadPrevious
            if (strpos($lines[$i+2], "loadPrevious") !== false) {
                $startIdx = $i;
            }
        }
    }
    
    if ($startIdx !== -1) {
        // find endIdx
        $brackets = 0;
        for ($i = $startIdx; $i < count($lines); $i++) {
            $brackets += substr_count($lines[$i], '[');
            $brackets -= substr_count($lines[$i], ']');
            
            if (strpos($lines[$i], "])->columnSpanFull(),") !== false && $brackets <= 2) { // 2 because of schema([ ...
                $endIdx = $i;
                break;
            }
        }
        
        if ($endIdx !== -1) {
            // extract the grid lines
            $gridLines = array_slice($lines, $startIdx, $endIdx - $startIdx + 1);
            
            // remove them from original position
            array_splice($lines, $startIdx, $endIdx - $startIdx + 1);
            
            // find where to insert them. 
            // We want to insert them right before "])->columns(4)," which is the end of the section schema.
            $insertIdx = -1;
            for ($i = $startIdx; $i < count($lines); $i++) {
                if (strpos($lines[$i], "])->columns(4),") !== false) {
                    $insertIdx = $i;
                    break;
                }
            }
            
            if ($insertIdx !== -1) {
                array_splice($lines, $insertIdx, 0, $gridLines);
                file_put_contents($file, implode("\n", $lines));
                echo "Modified $file\n";
            }
        }
    }
}
