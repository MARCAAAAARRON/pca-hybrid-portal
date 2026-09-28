<?php
$directory = new RecursiveDirectoryIterator('app/Filament/Resources');
$iterator = new RecursiveIteratorIterator($directory);
$files = new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

foreach ($files as $file) {
    $path = $file[0];
    $content = file_get_contents($path);
    $lines = explode("\n", $content);
    $modified = false;
    
    for ($i = 0; $i < count($lines); $i++) {
        $line = $lines[$i];
        
        // Find TextInput::make('something')
        if (preg_match("/^(.*)(?:TextInput|Forms\\\\Components\\\\TextInput)::make\('([^']+)'\)(.*)$/", $line, $matches)) {
            $prefix = $matches[1];
            $fieldName = $matches[2];
            $suffix = $matches[3];
            
            // Numeric keywords
            $isNumeric = false;
            $numKeywords = ['seednuts', 'seedlings', 'harvested', 'distributed', 'balance', 'utilization', 'received', 'total_', 'week', 'quantity', 'age'];
            foreach ($numKeywords as $kw) {
                if (strpos($fieldName, $kw) !== false && strpos($fieldName, 'source') === false && strpos($fieldName, 'date_') === false) {
                    $isNumeric = true;
                    break;
                }
            }
            
            // Check what is already chained on the same line or next lines
            $hasNumeric = false;
            $hasInteger = false;
            $hasMinValue = false;
            $hasMaxLength = false;
            
            $j = $i;
            while ($j < count($lines)) {
                if (strpos($lines[$j], '->numeric(') !== false) $hasNumeric = true;
                if (strpos($lines[$j], '->integer(') !== false) $hasInteger = true;
                if (strpos($lines[$j], '->minValue(') !== false) $hasMinValue = true;
                if (strpos($lines[$j], '->maxLength(') !== false) $hasMaxLength = true;
                
                if ($j > $i && trim($lines[$j]) !== '' && substr(trim($lines[$j]), 0, 2) !== '->') {
                    break;
                }
                $j++;
            }
            
            if ($isNumeric) {
                $adds = "";
                if (!$hasNumeric) $adds .= "->numeric()";
                if (!$hasInteger) $adds .= "->integer()";
                if (!$hasMinValue) $adds .= "->minValue(0)";
                
                if ($adds !== "") {
                    $lines[$i] = rtrim($lines[$i]) . $adds;
                    $modified = true;
                }
            } else {
                // Is string
                if (!$hasMaxLength && strpos($fieldName, 'email') === false && strpos($fieldName, 'password') === false) {
                    $lines[$i] = rtrim($lines[$i]) . "->maxLength(255)";
                    $modified = true;
                }
            }
            
            // Fix email specifically
            if ($fieldName === 'email' && strpos(implode("\n", array_slice($lines, $i, 3)), '->email(') === false) {
                $lines[$i] = rtrim($lines[$i]) . "->email()";
                $modified = true;
            }
        }
    }
    
    if ($modified) {
        file_put_contents($path, implode("\n", $lines));
        echo "Modified $path\n";
    }
}
