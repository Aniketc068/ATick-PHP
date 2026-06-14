# ATick for PHP — examples

Runnable examples for [ATick for PHP](https://github.com/Aniketc068/ATick-PHP). Each one writes its
output into `examples/signed/`.

## Run

From a checkout of this repository (no Composer install needed — the examples fall back to loading
`../src`):

```bash
php examples/sign_pfx.php
```

Or, after `composer require aniketc068/atick`, copy an example into your project — it will use your
Composer autoloader automatically.

> PHP must have the **FFI** extension enabled (`extension=ffi`, `ffi.enable=1`). On the CLI this is
> usually already the case.

## What each example shows

| File | Shows |
|---|---|
| `sign_pfx.php` | Sign a PDF with a `.pfx` in one call (one signature on three pages) |
| `pades_levels.php` | The four PAdES baseline levels — B-B, B-T, B-LT, B-LTA |
| `appearance.php` | CN-on-the-left, distinguished name, custom-text appearance |
| `multi_placement.php` | One signature drawn at several places / pages |
| `invisible.php` | An invisible signature (no on-page appearance) |
| `field_api.php` | Prepare an empty signature field, then sign it |
| `deferred_esign.php` | Two-step (deferred) signing for a remote key — eSign ESP / HSM |
| `make_container.php` | A detached CMS / PKCS#7 over arbitrary data |
| `metadata.php` | Set document metadata (Title / Author / Subject / …) |
| `document_timestamp.php` | Add an archive DocTimeStamp (PAdES-B-LTA) |
| `fast_signing.php` | Fast batch signing with the revocation cache |
| `encrypted.php` | Password-protect the output, then decrypt it again |
| `pem.php` | Sign with a PEM file (key + chain) instead of a PFX |

Sample certificates and PDFs live in [`../samples`](../samples). The test certificate password is
`ABC12`.
