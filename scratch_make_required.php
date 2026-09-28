<?php
$directory = new RecursiveDirectoryIterator('app/Filament/Resources');
$iterator = new RecursiveIteratorIterator($directory);
$files = new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

$components = [
    'TextInput', 'Select', 'DatePicker', 'Textarea', 'RichEditor', 'FileUpload', 'Toggle'
];

foreach ($files as $file) {
    $path = $file[0];
    $content = file_get_contents($path);
    $lines = explode("\n", $content);
    $modified = false;
    
    $inComponent = false;
    $componentIndent = "";
    $componentEndLine = -1;
    $hasRequired = false;
    
    for ($i = 0; $i < count($lines); $i++) {
        $line = $lines[$i];
        
        // Find component start
        foreach ($components as $comp) {
            if (preg_match("/^(\s*)(?:\\\\?Filament\\\\Forms\\\\Components\\\\)?{$comp}::make\(/", $line, $matches) || preg_match("/^(\s*){$comp}::make\(/", $line, $matches)) {
                $indent = $matches[1];
                
                // Now scan forward to see if ->required() exists before the next component or array end
                $j = $i;
                $hasReq = false;
                $endJ = $i;
                while ($j < count($lines)) {
                    if (strpos($lines[$j], '->required(') !== false) {
                        $hasReq = true;
                        break;
                    }
                    if ($j > $i && preg_match("/^\s*([A-Za-z]+::make|\]|,)/", ltrim($lines[$j]))) {
                        // Found next component or end of array
                        // Wait, if it's just chained methods, it starts with ->
                        if (!preg_match("/^\s*->/", $lines[$j]) && !preg_match("/::make/", $lines[$i])) {
                            // end of chain
                            $endJ = $j - 1;
                        }
                    }
                    
                    // Simple heuristic: if we hit a blank line or a line that doesn't start with -> after the first line
                    if ($j > $i && trim($lines[$j]) !== '' && substr(trim($lines[$j]), 0, 2) !== '->') {
                        $endJ = $j - 1;
                        break;
                    }
                    $j++;
                }
                
                if (!$hasReq && $endJ >= $i) {
                    // insert required() after the first line
                    // Actually, let's just append it to the make() line directly to be safe
                    $lines[$i] = $line . '->required()';
                    $modified = true;
                }
                break;
            }
        }
    }
    
    if ($modified) {
        file_put_contents($path, implode("\n", $lines));
        echo "Modified $path\n";
    }
}
