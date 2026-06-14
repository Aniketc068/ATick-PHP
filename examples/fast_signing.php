<?php
// ATick for PHP example — fast batch signing: reuse the revocation cache across many documents.
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';

use Aniketc068\ATick\Atick;

$pfx = file_get_contents(SAMPLES . '/ABC12.pfx');
$pdf = file_get_contents(SAMPLES . '/blank.pdf');

// Fast signing is ON by default; this is how you would toggle it explicitly.
Atick::setFastSigning(true);

$start = microtime(true);
for ($i = 1; $i <= 5; $i++) {
    $signed = Atick::signPfx($pdf, $pfx, [
        'password' => 'ABC12', 'cn' => 'DS TEST CERTIFICATE 06', 'reason' => 'Batch ' . $i,
        'date' => now(), 'placements' => [[1, [300, 55, 575, 175]]],
        'field_name' => 'Sig' . $i, 'pades' => true, 'timestamp' => true, 'ltv' => true,
    ]);
    save(sprintf('11_batch_%02d.pdf', $i), $signed);
}
printf("  signed 5 documents in %.2f s (revocation fetched once)\n", microtime(true) - $start);
