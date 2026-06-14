<?php
// ATick for PHP example — password-protect the output, then decrypt it again.
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';

use Aniketc068\ATick\Atick;

$pfx = file_get_contents(SAMPLES . '/ABC12.pfx');
$pdf = file_get_contents(SAMPLES . '/blank.pdf');

// Sign and encrypt the result (user password + owner password).
$encrypted = Atick::signPfx($pdf, $pfx, [
    'password'         => 'ABC12',
    'cn'               => 'DS TEST CERTIFICATE 06',
    'reason'           => 'Approved',
    'placements'       => [[1, [300, 55, 575, 175]]],
    'pades'            => true,
    'encrypt_password' => 'open-me',
    'owner_password'   => 'owner-secret',
]);
save('13_encrypted.pdf', $encrypted);

// Decrypt it back with the user password.
$plain = Atick::decrypt($encrypted, 'open-me');
save('13_decrypted.pdf', $plain);
