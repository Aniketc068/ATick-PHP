<?php
// ATick for PHP example — set document metadata (Title / Author / Subject / Keywords / ...).
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';

use Aniketc068\ATick\Atick;

$pdf = file_get_contents(SAMPLES . '/blank.pdf');

$out = Atick::setMetadata($pdf, [
    'Title'    => 'Quarterly Report',
    'Author'   => 'Aniket Chaturvedi',
    'Subject'  => 'Signed with ATick',
    'Keywords' => 'pdf, signature, pades, atick',
    'Creator'  => 'ATick for PHP',
]);
save('09_metadata.pdf', $out);
