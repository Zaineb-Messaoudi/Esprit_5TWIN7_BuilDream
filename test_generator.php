<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$generator = app(\L5Swagger\Generator::class);
echo 'Generator class: ' . get_class($generator) . PHP_EOL;
$reflection = new ReflectionClass($generator);
echo 'Methods: ' . implode(', ', array_map(fn($m) => $m->getName(), $reflection->getMethods())) . PHP_EOL;