<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$finder = new \OpenApi\Utils\SourceFinder([base_path('app'), base_path('app/Http/Controllers')], ['*.php']);
$files = iterator_to_array($finder);
echo 'Files found: ' . count($files) . PHP_EOL;
foreach ($files as $file) {
    echo '  - ' . $file->getPathname() . PHP_EOL;
}