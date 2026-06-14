<?php
// ATick for PHP example — appearance variations: CN-on-the-left, distinguished name, custom text.
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';

use Aniketc068\ATick\Atick;

$pfx = file_get_contents(SAMPLES . '/ABC12.pfx');
$pdf = file_get_contents(SAMPLES . '/blank.pdf');

// CN drawn on the left (Adobe-style), with the validity mark on the right.
save('03_cn_left.pdf', Atick::signPfx($pdf, $pfx, [
    'password' => 'ABC12', 'cn' => 'DS TEST CERTIFICATE 06', 'reason' => 'Approved', 'date' => now(),
    'image' => 'cn', 'green_tick' => true, 'page' => 1, 'rect' => [300, 55, 575, 175], 'pades' => true,
]));

// Distinguished name block.
save('03_dn.pdf', Atick::signPfx($pdf, $pfx, [
    'password' => 'ABC12', 'cn' => 'DS TEST CERTIFICATE 06', 'date' => now(),
    'dn' => 'CN=DS TEST CERTIFICATE 06, O=ATick, C=IN', 'page' => 1, 'rect' => [300, 55, 575, 175], 'pades' => true,
]));

// Custom-text-only appearance — \n = new line, *x* = bold.
save('03_custom_text.pdf', Atick::signPfx($pdf, $pfx, [
    'password' => 'ABC12', 'body' => "*APPROVED*\nby *Aniket Chaturvedi*\n" . now(),
    'page' => 1, 'rect' => [300, 55, 575, 175], 'pades' => true,
]));
