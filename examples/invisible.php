<?php
// ATick for PHP example — an invisible signature (no on-page appearance): placements = [].
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';

use Aniketc068\ATick\Atick;

$pfx = file_get_contents(SAMPLES . '/ABC12.pfx');
$pdf = file_get_contents(SAMPLES . '/blank.pdf');

$signed = Atick::signPfx($pdf, $pfx, [
    'password'   => 'ABC12',
    'cn'         => 'DS TEST CERTIFICATE 06',
    'reason'     => 'Approved',
    'placements' => [],   // invisible — cryptographically signed, nothing drawn
    'pades'      => true,
]);
save('07_invisible.pdf', $signed);
