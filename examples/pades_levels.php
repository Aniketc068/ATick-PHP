<?php
// ATick for PHP example — the four PAdES baseline levels: B-B, B-T, B-LT, B-LTA.
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';

use Aniketc068\ATick\Atick;

$pfx = file_get_contents(SAMPLES . '/ABC12.pfx');
$pdf = file_get_contents(SAMPLES . '/blank3.pdf');

$base = static function (): array {
    return [
        'password'   => 'ABC12',
        'cn'         => 'DS TEST CERTIFICATE 06',
        'reason'     => 'Approved',
        'date'       => now(),
        'placements' => [[1, [40, 640, 260, 750]]],
        'pades'      => true,
    ];
};

save('02_pades_B_B.pdf', Atick::signPfx($pdf, $pfx, $base()));
save('02_pades_B_T.pdf', Atick::signPfx($pdf, $pfx, $base() + ['timestamp' => true]));
save('02_pades_B_LT.pdf', Atick::signPfx($pdf, $pfx, $base() + ['timestamp' => true, 'ltv' => true]));
save('02_pades_B_LTA.pdf', Atick::signPfx($pdf, $pfx, $base() + ['timestamp' => true, 'lta' => true]));
