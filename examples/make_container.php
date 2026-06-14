<?php
// ATick for PHP example — produce a detached CMS / PKCS#7 over arbitrary data (no PDF).
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';

use Aniketc068\ATick\Atick;

$pfx  = file_get_contents(SAMPLES . '/ABC12.pfx');
$data = 'The quick brown fox jumps over the lazy dog.';

$cms = Atick::cmsPfx($data, $pfx, [
    'password'  => 'ABC12',
    'hash_algo' => 'sha256',
]);

file_put_contents(SIGNED . '/12_detached.p7s', $cms);
echo '  12_detached.p7s (' . strlen($cms) . " bytes)\n";
