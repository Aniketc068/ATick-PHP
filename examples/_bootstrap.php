<?php
// Shared bootstrap for the ATick examples.
// Uses Composer's autoloader when the package is installed (vendor/autoload.php);
// otherwise registers a tiny PSR-4 autoloader for ../src so the examples run straight
// from a checkout of this repository.

declare(strict_types=1);

$vendor = __DIR__ . '/../vendor/autoload.php';
if (is_file($vendor)) {
    require $vendor;
} else {
    spl_autoload_register(function (string $class): void {
        $prefix = 'Aniketc068\\ATick\\';
        if (strpos($class, $prefix) === 0) {
            $rel = substr($class, strlen($prefix));
            require __DIR__ . '/../src/' . str_replace('\\', '/', $rel) . '.php';
        }
    });
}

const SAMPLES = __DIR__ . '/../samples';
const SIGNED = __DIR__ . '/signed';

if (!is_dir(SIGNED)) {
    mkdir(SIGNED, 0777, true);
}

function save(string $name, string $data): void
{
    file_put_contents(SIGNED . '/' . $name, $data);
    echo '  ' . $name . ' (' . strlen($data) . " bytes)\n";
}

function now(): string
{
    return date('d-M-Y h:i A');
}
