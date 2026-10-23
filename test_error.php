<?php
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Disable exception handling to see raw errors
$app->instance('Illuminate\Contracts\Debug\ExceptionHandler', new class implements Illuminate\Contracts\Debug\ExceptionHandler {
    public function report(Throwable $e) {}
    public function render($request, Throwable $e) {
        throw $e;
    }
    public function renderForConsole($output, Throwable $e) {
        throw $e;
    }
});

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create("/", "GET");

try {
    $response = $kernel->handle($request);
    echo "Status: " . $response->getStatusCode() . "\n";
    echo "Content: " . $response->getContent() . "\n";
} catch (Throwable $e) {
    echo "EXCEPTION: " . get_class($e) . "\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}