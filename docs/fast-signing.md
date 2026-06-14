# Fast signing

Fast signing is **ON by default**. When LTV is enabled, the first signature fetches the
certificate's CRL/OCSP over the network; ATick then keeps that revocation in an in-memory cache, so
every later signature **with the same certificate** reuses it instead of fetching again. This is a
large speed-up for batch and multi-signature runs (≈ 6× in practice).

```php
<?php
require 'vendor/autoload.php';

use Aniketc068\ATick\Atick;

Atick::setFastSigning(true);    // default — reuse cached revocation for the same certificate
Atick::setFastSigning(false);   // always fetch fresh (also clears the cache)
```

## Signing with LTV

Pass options as a PHP associative array. The first signature with a given certificate populates the
cache; the rest reuse it.

```php
<?php
require 'vendor/autoload.php';

use Aniketc068\ATick\Atick;

$pdf = file_get_contents("in.pdf");
$pfx = file_get_contents("my.pfx");

$options = ["password" => "secret", "ltv" => true];

$signed = Atick::signPfx($pdf, $pfx, $options);
file_put_contents("out.pdf", $signed);
```

## Batch signing

Because the cache is keyed per certificate, signing many PDFs with the **same** `.pfx` fetches
revocation once and reuses it for the rest of the run.

```php
<?php
require 'vendor/autoload.php';

use Aniketc068\ATick\Atick;
use Aniketc068\ATick\AtickException;

$pfx = file_get_contents("my.pfx");
$options = ["password" => "secret", "ltv" => true];

$inputs = ["a.pdf", "b.pdf", "c.pdf", "d.pdf"];

Atick::setFastSigning(true);   // default; shown here for clarity

foreach ($inputs as $name) {
  $pdf = file_get_contents($name);
  try {
    $signed = Atick::signPfx($pdf, $pfx, $options);   // first call fetches, rest reuse cache
    file_put_contents("signed-" . $name, $signed);
  } catch (AtickException $err) {
    fwrite(STDERR, "Failed to sign " . $name . ": " . $err->getMessage() . "\n");
  }
}
```

## Disabling the cache

To force a fresh CRL/OCSP fetch on every signature, turn fast signing off before signing.

```php
<?php
require 'vendor/autoload.php';

use Aniketc068\ATick\Atick;

Atick::setFastSigning(false);   // always fetch fresh, also clears the cache
```

```{tip}
Leave fast signing on for batch runs. Turn it off only when you need each signature to reflect the
very latest revocation state.
```

## Behaviour at a glance

| Setting | First signature | Later signatures (same certificate) | Timestamps |
| --- | --- | --- | --- |
| `setFastSigning(true)` (default) | Fetch CRL/OCSP, cache it | Reuse cached revocation | Always fresh |
| `setFastSigning(false)` | Fetch CRL/OCSP | Fetch CRL/OCSP again | Always fresh |

## Notes

- The cache lives in **process memory** only and is gone when the process ends.
- It is keyed per request, so a **different / removed certificate** simply misses and is fetched
  fresh — there is no risk of reusing the wrong certificate's revocation.
- **Timestamps are never cached** — each signature must carry its own unique RFC-3161 token, so the
  timestamp authority is always contacted per signature.
- Any failure (bad password, network error, malformed PDF) throws an `AtickException`; wrap calls
  in a `try`/`catch` as shown in the batch loop above.
```
