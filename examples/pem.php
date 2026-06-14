<?php
// ATick for PHP example — sign with a PEM file (private key + certificate chain) instead of a PFX.
// signPfx auto-detects PKCS#12 vs PEM, so the same call works for both.
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';

use Aniketc068\ATick\Atick;

$pem = file_get_contents(SAMPLES . '/ABC12.pem');
$pdf = file_get_contents(SAMPLES . '/blank.pdf');

$signed = Atick::signPfx($pdf, $pem, [
    'password'   => 'ABC12',   // the PEM key passphrase (omit if the key is unencrypted)
    'cn'         => 'DS TEST CERTIFICATE 06',
    'reason'     => 'Approved',
    'date'       => now(),
    'placements' => [[1, [300, 55, 575, 175]]],
    'pades'      => true,
]);
save('15_pem.pdf', $signed);
