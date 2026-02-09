<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Storage;

$logFile = 'reproduce_error.log';
file_put_contents($logFile, "Starting reproduction...\n");

try {
    file_put_contents($logFile, "Attempting to list files on Wasabi disk...\n", FILE_APPEND);
    $files = Storage::disk('Wasabi')->files();
    file_put_contents($logFile, "Files found: " . count($files) . "\n", FILE_APPEND);

    file_put_contents($logFile, "Attempting to put a file...\n", FILE_APPEND);
    $result = Storage::disk('Wasabi')->put('test-upload.txt', 'This is a test upload from the reproduction script.');

    if ($result) {
        file_put_contents($logFile, "File uploaded successfully.\n", FILE_APPEND);
        file_put_contents($logFile, "URL: " . Storage::disk('Wasabi')->url('test-upload.txt') . "\n", FILE_APPEND);
    } else {
        file_put_contents($logFile, "File upload failed (returned false).\n", FILE_APPEND);
    }

} catch (\Exception $e) {
    file_put_contents($logFile, "Error: " . $e->getMessage() . "\n", FILE_APPEND);
    file_put_contents($logFile, $e->getTraceAsString() . "\n", FILE_APPEND);
}

echo "Reproduction complete. Check $logFile for details.\n";
