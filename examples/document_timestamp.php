<?php
// ATick for PHP example — add an archive DocTimeStamp (+ DSS) to an already-signed PDF (PAdES-B-LTA).
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';

use Aniketc068\ATick\Atick;

$pfx = file_get_contents(SAMPLES . '/ABC12.pfx');
$pdf = file_get_contents(SAMPLES . '/blank.pdf');

// First produce a B-LT signature ...
$signed = Atick::signPfx($pdf, $pfx, [
    'password' => 'ABC12', 'cn' => 'DS TEST CERTIFICATE 06', 'reason' => 'Approved', 'date' => now(),
    'placements' => [[1, [300, 55, 575, 175]]], 'pades' => true, 'timestamp' => true, 'ltv' => true,
]);

// ... then seal it with an archive document timestamp -> PAdES-B-LTA.
$lta = Atick::addDocTimestamp($signed, []);
save('10_doctimestamp_LTA.pdf', $lta);
