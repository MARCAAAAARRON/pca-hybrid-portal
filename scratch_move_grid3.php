<?php
$files = [
    'app/Filament/Resources/NurseryOperationResource.php' => '])->columns(3),',
    'app/Filament/Resources/PollenProductionResource.php' => '])->columns(4),', // Wait, Pollen is columns(4) for the first section? Let's check Pollen layout: it was `Forms\Components\Grid::make(4)->schema([...`
];

foreach ($files as $file => $endStr) {
    if (!file_exists($file)) continue;
    
    $content = file_get_contents($file);
    $lines = explode("\n", $content);
    $startIdx = -1;
    $endIdx = -1;
    
    // Find the first Grid::make(2) inside form
    for ($i = 0; $i < count($lines); $i++) {
        if (strpos($lines[$i], "Forms\Components\Grid::make(2)->schema([") !== false && strpos($lines[$i], "loadPrevious") === false) {
            if (isset($lines[$i+2]) && strpos($lines[$i+2], "loadPrevious") !== false) {
                $startIdx = $i;
                break;
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
            
            // For Nursery, we look for `])->columns(3),` AFTER the startIdx.
            // But wait! Pollen uses Grid::make(4)->schema([...]) which doesn't have `->columns(4)` at the end.
            // Let's just find the first `])->columns(3),` or `])->columnSpanFull()` that corresponds to the section end.
            
            $insertIdx = -1;
            $brackets = 0;
            // Let's search from the start of the section schema
            $sectionStart = 0;
            for ($i = $startIdx; $i >= 0; $i--) {
                if (strpos($lines[$i], "->schema([") !== false) {
                    $sectionStart = $i;
                    break;
                }
            }
            
            if ($sectionStart > 0) {
                for ($i = $sectionStart; $i < count($lines); $i++) {
                    $brackets += substr_count($lines[$i], '[');
                    $brackets -= substr_count($lines[$i], ']');
                    if ($brackets == 0 && strpos($lines[$i], "])") !== false) {
                        $insertIdx = $i;
                        break;
                    }
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
