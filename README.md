<div align="center">

<img src="https://raw.githubusercontent.com/Aniketc068/ATick-PHP/main/assets/atick_logo.png" alt="ATick" width="260"/>

# ATick for PHP

**Standalone PDF digital-signature library for PHP — PAdES / CMS signing with no external services.**

[![Packagist](https://img.shields.io/packagist/v/aniketc068/atick?color=2ea44f&label=packagist)](https://packagist.org/packages/aniketc068/atick)
[![PHP](https://img.shields.io/badge/php-7.4%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![PAdES](https://img.shields.io/badge/PAdES-B--B%20%7C%20B--T%20%7C%20B--LT%20%7C%20B--LTA-success)](#pades-levels)
[![Cross-platform](https://img.shields.io/badge/platform-Windows%20%7C%20Linux%20%7C%20macOS-brightgreen)](#compatibility--one-package-everywhere)
[![License: AGPL v3](https://img.shields.io/badge/license-AGPL--3.0-blue)](LICENSE)
[![Also for Python](https://img.shields.io/badge/also%20for-Python-3776AB?logo=python&logoColor=white)](https://github.com/Aniketc068/ATick-Python)
[![Also for Java](https://img.shields.io/badge/also%20for-Java-007396?logo=openjdk&logoColor=white)](https://github.com/Aniketc068/ATick-Java)
[![Also for .NET](https://img.shields.io/badge/also%20for-.NET-512BD4?logo=dotnet&logoColor=white)](https://github.com/Aniketc068/ATick-DotNet)
[![Also for Node.js](https://img.shields.io/badge/also%20for-Node.js-339933?logo=node.js&logoColor=white)](https://github.com/Aniketc068/ATick-Node)

</div>

**Also available in other languages** — the same ATick engine, the same API, native to each ecosystem:

| Language | Install | Source · Docs |
|---|---|---|
| **Python** | `pip install atick` | [ATick-Python](https://github.com/Aniketc068/ATick-Python) · [docs](https://atick.readthedocs.io/docs/python/) |
| **Java** | `io.github.aniketc068:atick` (Maven) | [ATick-Java](https://github.com/Aniketc068/ATick-Java) · [docs](https://atick.readthedocs.io/docs/java/) |
| **.NET** | `dotnet add package ATick` | [ATick-DotNet](https://github.com/Aniketc068/ATick-DotNet) · [docs](https://atick.readthedocs.io/docs/dotnet/) |
| **Node.js** | `npm install atick` | [ATick-Node](https://github.com/Aniketc068/ATick-Node) · [docs](https://atick.readthedocs.io/docs/node/) |

---

ATick signs PDFs the way Adobe Acrobat and the EU DSS do — **PAdES baseline** signatures with
timestamps and long-term validation. The matching engine for your platform ships **inside the
package** and is loaded automatically through PHP **FFI** — there is **no external service**,
**nothing to compile**, and **no PHP dependencies**. Run `composer require aniketc068/atick` and
you are done.

```php
<?php
require 'vendor/autoload.php';

use Aniketc068\ATick\Atick;

$signed = Atick::signPfx(
    file_get_contents('doc.pdf'),
    file_get_contents('my.pfx'),
    [
        'password'   => '••••',
        'cn'         => 'Axonate Tech',
        'reason'     => 'Approved',
        'green_tick' => true,
        'page'       => 1,
        'rect'       => [300, 55, 575, 175],
        'pades'      => true, 'timestamp' => true, 'ltv' => true,   // PAdES-B-LT
    ]
);

file_put_contents('signed.pdf', $signed);
```

> **Options** are a plain PHP array (shown above) — or a JSON **string** if you prefer. **Buffers**
> (PDF / PFX / output) are PHP binary strings. Any failure throws an `Aniketc068\ATick\AtickException`.

> **Runs anywhere PHP runs** — Laravel, Symfony, WordPress, plain PHP, CLI scripts, queue workers.
> It uses FFI, so the FFI extension must be enabled (see [Requirements](#requirements)).

---

## The green tick your readers trust

ATick draws a verified-signature appearance with a green tick. When the certificate is valid and
trusted, Adobe Reader / Acrobat shows **“Signed and all signatures are valid.”**

<div align="center">
<img src="https://raw.githubusercontent.com/Aniketc068/ATick-PHP/main/assets/valid_signature_adobe.png" alt="Adobe — signed and all signatures are valid" width="560"/>
</div>

<table align="center">
<tr>
<td align="center"><img src="https://raw.githubusercontent.com/Aniketc068/ATick-PHP/main/assets/signature_appearance.png" width="190"/><br/><b>Valid &amp; trusted</b><br/>green tick</td>
<td align="center"><img src="https://raw.githubusercontent.com/Aniketc068/ATick-PHP/main/assets/sig_unknown.png" width="190"/><br/><b>Validity unknown</b><br/>yellow “?”</td>
<td align="center"><img src="https://raw.githubusercontent.com/Aniketc068/ATick-PHP/main/assets/sig_notverified.png" width="190"/><br/><b>Not verified</b><br/>“?” not validated</td>
<td align="center"><img src="https://raw.githubusercontent.com/Aniketc068/ATick-PHP/main/assets/sig_invalid.png" width="190"/><br/><b>Invalid</b><br/>red cross</td>
</tr>
</table>

---

## Install

```bash
composer require aniketc068/atick
```

The engine for your platform comes with the package. There is no build step and no extra download.

## Requirements

- **PHP 7.4 or newer** with the **FFI extension** enabled.
  - On the **CLI** SAPI, FFI is usually available out of the box.
  - On **web** SAPIs (PHP-FPM / Apache) set in `php.ini`:

    ```ini
    extension=ffi
    ffi.enable=1          ; or: ffi.enable=preload  (then set opcache.preload)
    ```
- No other PHP extension is required — the cryptography, PKCS#12 / PEM, PKCS#11, image decoding,
  timestamping and LTV are all built into the bundled engine.

---

## Features (A → Z)

| Feature | How |
|---|---|
| **Sign with a `.pfx` / `.p12` / `.pem`** | `Atick::signPfx($pdf, $pfx, $options)` — PKCS#12 or PEM (key + certs), auto-detected |
| **PAdES levels** B-B / B-T / B-LT / B-LTA | `'pades' => true` + `'timestamp' => true` + `'ltv' => true` + `'lta' => true` |
| **Hash algorithm** | `'hash_algo' => 'sha256' \| 'sha384' \| 'sha512'` |
| **Timestamp authority** | built in — or your own with `'tsa_url' => '…'` (and `'tsa_auth' => ['user','pass']`) |
| **Long-term validation (LTV)** | `'ltv' => true` embeds the chain + revocation (CRL/OCSP) |
| **Multi-page / custom coordinates** | `'placements' => [[page, [x1,y1,x2,y2]], …]` |
| **Signature layout** | `'mode' => 'single'` (one signature on many pages) · `'mode' => 'shared'` (many fields, same value) |
| **Multi-signatory** | sign an already-signed PDF again — each signature is its own revision, all stay valid |
| **Certification (DocMDP)** | `'certify' => 1` (no changes) · `2` (form filling) · `3` (form filling + annotations) |
| **Field locking (FieldMDP)** | `'lock_fields' => ['*']` or `['FieldA', …]` |
| **Pre-sign checks** | `'verify_expiry' => true`, `'verify_crl' => true`, `'verify_ocsp' => true` (or `'verify' => true`) |
| **Document metadata** | `Atick::setMetadata($pdf, $options)` |
| **Password protection** | `'encrypt_password'` (+ `'owner_password'`) for output; `'open_password'` for input; `Atick::decrypt($pdf, $pw)` |
| **Appearance** | options `cn, org, ou, location, reason, text, date, dn, body, heading, image` — auto-fit text, transparent logo |
| **The mark** | the `?` (Adobe greens it), an always-green tick, or nothing — see [The mark](#the-mark) |
| **CN on the left** (Adobe-style) | `'image' => 'cn'` |
| **Distinguished name** | `'dn' => 'CN=…, O=…, C=IN'` |
| **Custom-text-only appearance** | `'body' => "*APPROVED*\nby *Aniket*"` — `\n` = line, `*x*` = bold |
| **Invisible signature** | `'placements' => []` |
| **Sign an already-signed PDF** | sign again (incremental) — existing signatures stay valid; use a fresh `'field_name'` |
| **Container only** | `Atick::prepareFields($pdf, $options)` |
| **Document timestamp** | `'lta' => true` while signing; `Atick::addDocTimestamp($pdf, $options)` afterwards (PAdES-B-LTA) |
| **Fast signing** | revocation cache (ON by default) — `Atick::setFastSigning(false)` to disable |
| **Deferred / eSign (2-step)** | `Atick::prepare($pdf, $options)` → external CMS → `Atick::embed($prepared, $cms)` |
| **Detached CMS** | `Atick::cmsPfx($data, $pfx, $options)` |

---

## The API

```php
Atick::signPfx($pdf, $pfx, $options);        // sign with a .pfx / .p12 / .pem (auto-detected)
Atick::prepare($pdf, $options);              // deferred / eSign: returns [$prepared, $bytesToSign]
Atick::cmsPfx($data, $pfx, $options);        // detached CMS over data
Atick::embed($prepared, $cms);               // embed a detached CMS into a prepared PDF
Atick::prepareFields($pdf, $options);        // make an empty signature field (template)
Atick::signField($pdf, $pfx, $options);      // sign an existing empty field
Atick::setMetadata($pdf, $options);          // Title / Author / Subject / Keywords / …
Atick::addDocTimestamp($pdf, $options);      // archive DocTimeStamp (PAdES-B-LTA)
Atick::setFastSigning(true | false);         // revocation-cache toggle
Atick::decrypt($pdf, $password);             // decrypt a password-protected PDF
Atick::version();                            // engine version
```

Every method is **static**. `$options` is a PHP **array** (recommended) or a JSON **string**. All
buffers are PHP binary strings. Any failure throws `Aniketc068\ATick\AtickException` whose
`getMessage()` is the reason.

### Options

`cn, org, ou, location, reason, text, date, dn, body, heading, show_mark, green_tick, always_check,
mark_color (hex / name / [r,g,b]), mark_gradient, mark_scale, text_color, bg_color, border, font_size,
width, height, page, rect, placements ([[page,[x1,y1,x2,y2]], …]), mode (single/shared), field_name,
pades, hash_algo (sha256/384/512), timestamp, tsa_url, tsa_auth, ltv, lta, certify, lock_fields,
verify, verify_expiry, verify_crl, verify_ocsp, open_password, encrypt_password, owner_password,
contents_size`.

---

## The mark

```php
['green_tick' => true]      // the "?" mark — Adobe paints it GREEN for valid+trusted, RED if invalid
['always_check' => true]    // the green-tick graphic as the base
['green_tick' => false]     // no mark — a plain signature
```

Colour it: `'mark_color' => '#E53935'`, `'blue'`, `[255,140,0]` — or a gradient
`'mark_gradient' => ['red','orange','yellow']`.

---

## Deferred signing & Indian eSign (two-step)

When the private key lives elsewhere (a token / HSM / smart-card, or an eSign ESP):

```php
[$prepared, $bytesToSign] = Atick::prepare($pdf, [
    'cn' => 'DS TEST', 'reason' => 'eSign',
    'placements' => [[1, [300, 55, 575, 175]]], 'contents_size' => 16384,
]);

// the eSign InputHash is the SHA-256 of $bytesToSign:
$inputHash = hash('sha256', $bytesToSign);
// ... sign with your provider / eSign ESP, get back a detached CMS ...

$signed = Atick::embed($prepared, $cms);
```

---

## PAdES levels

```php
Atick::signPfx($pdf, $pfx, ['pades' => true]);                                            // B-B
Atick::signPfx($pdf, $pfx, ['pades' => true, 'timestamp' => true]);                       // B-T
Atick::signPfx($pdf, $pfx, ['pades' => true, 'timestamp' => true, 'ltv' => true]);        // B-LT
Atick::signPfx($pdf, $pfx, ['pades' => true, 'timestamp' => true, 'lta' => true]);        // B-LTA
```

---

## Compatibility — one package everywhere

- **PHP 7.4 → the latest 8.x** — FFI is part of every supported PHP version.
- **Every OS/arch** — the matching engine ships for each platform and is selected automatically:

  | OS · arch | Covers |
  |---|---|
  | `windows-x86_64` / `windows-i686` | **Windows 7 → 11**, 64 / 32-bit |
  | `windows-aarch64` | Windows on ARM64 |
  | `linux-x86_64` / `linux-aarch64` / `linux-arm` / `linux-i686` | Linux x64 / ARM64 / ARM / 32-bit (glibc 2.17+, every distro) |
  | `darwin-x86_64` / `darwin-aarch64` | macOS Intel / Apple Silicon |

---

## Errors

```php
use Aniketc068\ATick\AtickException;

try {
    Atick::signPfx($pdf, $pfx, ['password' => 'wrong']);
} catch (AtickException $e) {
    error_log('signing failed: ' . $e->getMessage());
}
```

---

## License

ATick is **dual-licensed** — free for personal & open use, paid if you sell:

- **Free under [GNU AGPL-3.0](LICENSE)** — personal projects, learning, internal use, and
  open-source projects (released publicly under AGPL-3.0).
- **Commercial license (paid)** — if you **build a product with ATick and sell it**, or use it in a
  **closed-source / commercial** product, you must buy a commercial license first. Contact
  **info@axonatetech.com** for a quote.

See [LICENSING.md](LICENSING.md) for details. © 2026 Axonate Tech.
