<?php
$files = array_merge(
    glob('c:\Test\Testv2\pca-hybrid-portal\app\Filament\Widgets\*.php'),
    glob('c:\Test\Testv2\pca-hybrid-portal\resources\views\filament\widgets\*.blade.php'),
    ['c:\Test\Testv2\pca-hybrid-portal\resources\views\welcome.blade.php']
);

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    $newContent = $content;
    
    // Replace auth()->user()?->role === 'sub_supervisor' with auth()->user()?->isSubSupervisor()
    $newContent = str_replace("auth()->user()?->role === 'sub_supervisor'", "auth()->user()?->isSubSupervisor()", $newContent);
    $newContent = str_replace("auth()->user()->role === 'sub_supervisor'", "auth()->user()?->isSubSupervisor()", $newContent);
    $newContent = str_replace("auth()->user()?->role !== 'sub_supervisor'", "!auth()->user()?->isSubSupervisor()", $newContent);
    
    if ($content !== $newContent) {
        file_put_contents($file, $newContent);
        echo 'Updated: ' . basename($file) . PHP_EOL;
    }
}
