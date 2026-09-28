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
    
    for ($i = 0; $i < count($lines); $i++) {
        $line = $lines[$i];
        
        foreach ($components as $comp) {
            // Match any spacing, optional namespace, the component name, and ::make
            if (preg_match("/^(\s*)(?:[A-Za-z0-9_\\\\]*)?{$comp}::make\(/", $line)) {
                
                // Scan forward to see if ->required() is already chained
                $hasReq = false;
                $j = $i;
                while ($j < count($lines)) {
                    // if we hit ->required(), it's already there
                    if (strpos($lines[$j], '->required(') !== false) {
                        $hasReq = true;
                        break;
                    }
                    // if we hit a line that is NOT a chained method (does not start with -> or ::make line itself)
                    if ($j > $i && trim($lines[$j]) !== '') {
                        if (substr(trim($lines[$j]), 0, 2) !== '->') {
                            break;
                        }
                    }
                    $j++;
                }
                
                if (!$hasReq) {
                    $lines[$i] = rtrim($lines[$i]) . '->required()';
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
