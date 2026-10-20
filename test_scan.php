<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$generator = app(\L5Swagger\Generator::class);

try {
    $reflection = new ReflectionMethod($generator, 'scanFilesForDocumentation');
    $reflection->setAccessible(true);
    $files = $reflection->invoke($generator);
    echo "Files found: " . count($files) . PHP_EOL;
    foreach ($files as $file) {
        echo "  - $file" . PHP_EOL;
    }
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}