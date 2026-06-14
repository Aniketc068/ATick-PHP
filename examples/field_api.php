<?php
// ATick for PHP example — low-level: prepare an empty field, then sign it.
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';

use Aniketc068\ATick\Atick;

$pfx = file_get_contents(SAMPLES . '/ABC12.pfx');
$pdf = file_get_contents(SAMPLES . '/blank.pdf');

// 1) prepare an empty signing field (template) with the ATick appearance
$template = Atick::prepareFields($pdf, [
    'cn'         => 'DS TEST CERTIFICATE 06',
    'reason'     => 'Approved',
    'date'       => now(),
    'field_name' => 'Sig1',
    'page'       => 1,
    'rect'       => [300, 55, 575, 175],
    'pades'      => true,
]);
save('14_prepared_fields_template.pdf', $template);

// 2) sign that field
$signed = Atick::signField($template, $pfx, [
    'password'   => 'ABC12',
    'field_name' => 'Sig1',
    'reason'     => 'Approved',
    'pades'      => true,
]);
save('14_sign_field.pdf', $signed);
