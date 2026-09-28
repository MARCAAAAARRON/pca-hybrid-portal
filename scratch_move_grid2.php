<?php
$files = [
    'app/Filament/Resources/NurseryOperationResource.php',
    'app/Filament/Resources/PollenProductionResource.php',
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    
    $content = file_get_contents($file);
    
    $lines = explode("\n", $content);
    $startIdx = -1;
    $endIdx = -1;
    
    for ($i = 0; $i < count($lines); $i++) {
        if (strpos($lines[$i], "Forms\Components\Grid::make(2)->schema([") !== false && strpos($lines[$i], "loadPrevious") === false) {
            if (isset($lines[$i+2]) && strpos($lines[$i+2], "loadPrevious") !== false) {
                $startIdx = $i;
                break; // only first grid
            }
        }
    }
    
    if ($startIdx !== -1) {
        $brackets = 0;
        for ($i = $startIdx; $i < count($lines); $i++) {
            $brackets += substr_count($lines[$i], '[');
            $brackets -= substr_count($lines[$i], ']');
            
            if (strpos($lines[$i], "])->columnSpanFull(),") !== false && $brackets <= 2) {
                $endIdx = $i;
                break;
            }
        }
        
        if ($endIdx !== -1) {
            $gridLines = array_slice($lines, $startIdx, $endIdx - $startIdx + 1);
            array_splice($lines, $startIdx, $endIdx - $startIdx + 1);
            
            $insertIdx = -1;
            for ($i = $startIdx; $i < count($lines); $i++) {
                if (preg_match("/\]\)->columns\(\d+\),/", $lines[$i])) {
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
