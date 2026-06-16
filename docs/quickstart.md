# Quickstart

Sign a PDF with a `.pfx` (or `.p12` / `.pem`) and a visible green-tick appearance.

```php
<?php
require 'vendor/autoload.php';

use Aniketc068\ATick\Atick;

$pdf = file_get_contents("doc.pdf");
$pfx = file_get_contents("my.pfx");

$signed = Atick::signPfx($pdf, $pfx, [
    "password" => "••••", "cn" => "Axonate Tech", "reason" => "Approved",
    "green_tick" => true, "page" => 1, "rect" => [300, 55, 575, 175],
    "pades" => true, "timestamp" => true, "ltv" => true,   // PAdES-B-LT
]);

file_put_contents("signed.pdf", $signed);
```

Open `signed.pdf` in Adobe Reader — for a trusted certificate it shows a valid green tick and
**“Signed and all signatures are valid.”**

## Options as a JSON string

`$options` is a PHP associative array (preferred), but a JSON string works too:

```php
<?php
require 'vendor/autoload.php';

use Aniketc068\ATick\Atick;

$signed = Atick::signPfx(file_get_contents("doc.pdf"), file_get_contents("my.pfx"),
    json_encode(["password" => "••••", "cn" => "Aniket", "pades" => true, "page" => 1, "rect" => [300, 55, 575, 175]]));
file_put_contents("signed.pdf", $signed);
```

## A minimal, invisible signature

```php
$signed = Atick::signPfx($pdf, $pfx, ["password" => "••••", "placements" => [], "pades" => true]);
```

## Catching errors

```php
use Aniketc068\ATick\AtickException;

try {
    Atick::signPfx($pdf, $pfx, ["password" => "wrong"]);
} catch (AtickException $e) {
    fwrite(STDERR, "signing failed: " . $e->getMessage() . "\n");
}
```

Next: see [Signing](signing.md) for all the options, or the [API reference](api.md).
