<?php
// ATick for PHP example — sign a PDF with a .pfx in one call; same signature shown on 3 pages.
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';

use Aniketc068\ATick\Atick;

$pfx = file_get_contents(SAMPLES . '/ABC12.pfx');
$pdf = file_get_contents(SAMPLES . '/blank3.pdf');

$signed = Atick::signPfx($pdf, $pfx, [
    'password'   => 'ABC12',
    'cn'         => 'DS TEST CERTIFICATE 06',
    'reason'     => 'Approved',
    'date'       => now(),
    'placements' => [[1, [40, 640, 260, 750]], [2, [330, 380, 560, 490]], [3, [180, 60, 400, 170]]],
    'mode'       => 'single',
    'pades'      => true,
]);

save('01_pfx.pdf', $signed);
