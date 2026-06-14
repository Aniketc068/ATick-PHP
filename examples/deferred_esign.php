<?php
// ATick for PHP example — Deferred (TWO-STEP) signing for a REMOTE key
// (eSign ESP / HSM / smart card), shown on several pages.
//
// When the private key lives elsewhere, ATick splits signing into two steps:
//   1) Atick::prepare($pdf, $options) -> [$prepared, $bytesToSign]
//        $bytesToSign         = the exact bytes that must be signed (the ByteRange)
//        sha256($bytesToSign) = their hash -> send THIS to your eSign service if it wants a hash
//   2) your signer produces a DETACHED PKCS#7/CMS over $bytesToSign
//   3) Atick::embed($prepared, $cms) -> signed PDF
//
// Below, ATick itself (Atick::cmsPfx) stands in for the external signer so the demo runs with no
// extra setup — replace that block with YOUR eSign ESP / HSM / token call (it just returns a
// detached CMS over $bytesToSign).
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';

use Aniketc068\ATick\Atick;

$pfx = file_get_contents(SAMPLES . '/ABC12.pfx');
$pdf = file_get_contents(SAMPLES . '/blank3.pdf');

// ---- STEP 1: prepare (no key needed) -> appearance on every page + bytes-to-sign ----
[$prepared, $bytesToSign] = Atick::prepare($pdf, [
    'cn'           => 'DS TEST CERTIFICATE 06',
    'reason'       => 'eSign',
    'date'         => now(),
    'placements'   => [[1, [40, 640, 260, 750]], [2, [330, 380, 560, 490]], [3, [180, 60, 400, 170]]],
    'mode'         => 'single',
    'field_name'   => 'Signature1',
    'signer_name'  => 'DS TEST CERTIFICATE 06',
    'contents_size' => 16384,
]);

$hash = hash('sha256', $bytesToSign);
echo 'STEP 1: hash to send to the signer: ' . $hash . "\n";

// ---- STEP 2: the EXTERNAL signer makes a detached CMS over $bytesToSign ----
//   >>> Replace this whole block with your eSign ESP / HSM / token call. <<<
$cms = Atick::cmsPfx($bytesToSign, $pfx, ['password' => 'ABC12', 'hash_algo' => 'sha256']);

// ---- STEP 3: embed the CMS ----
save('08_deferred.pdf', Atick::embed($prepared, $cms));
echo "  signed on 3 pages via the two-step eSign flow\n";
