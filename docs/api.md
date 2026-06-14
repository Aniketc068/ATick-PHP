# API reference

All operations are **static methods** on the `Atick` class. Bootstrap it once:

```php
<?php
require 'vendor/autoload.php';

use Aniketc068\ATick\Atick;
```

Every method takes raw PHP binary strings for PDFs and certificates (read with `file_get_contents`,
write the result with `file_put_contents`), and an options argument where applicable — a PHP
associative array (preferred) or a JSON string. On any failure a method throws
`Aniketc068\ATick\AtickException` (which extends `RuntimeException`); the message is available from
`$e->getMessage()`.

```php
// prepare() returns a two-element array:
//   [$prepared, $bytesToSign]   — both PHP binary strings
```

ATick runs server-side only. There is no browser build and no command-line interface.

## Signing

```php
Atick::signPfx(string $pdf, string $pfx, array|string $options = []): string
```

Sign `$pdf` with a `.pfx`/`.p12`/`.pem` credential (the format is auto-detected). For a PEM file pass
the password as the empty string `""` inside the options. Returns the signed PDF bytes.

- **$pdf** — the PDF to sign (binary string).
- **$pfx** — the credential bytes (`.pfx`, `.p12`, or `.pem`) as a binary string.
- **$options** — the options array (see the [Options](#options) table). Pass the credential password
  as the `password` key; use `""` for PEM.
- **returns** — the signed PDF as a binary string.

```php
<?php
require 'vendor/autoload.php';

use Aniketc068\ATick\Atick;
use Aniketc068\ATick\AtickException;

$pdf = file_get_contents("in.pdf");
$pfx = file_get_contents("signer.pfx");

$options = [
    "password"  => "secret",
    "cn"        => "Aniket Chaturvedi",
    "reason"    => "Approval",
    "page"      => 1,
    "rect"      => [40, 40, 240, 140],
    "pades"     => true,
    "timestamp" => true,
    "tsa_url"   => "http://timestamp.example/tsa",
];

try {
    $signed = Atick::signPfx($pdf, $pfx, $options);
    file_put_contents("signed.pdf", $signed);
} catch (AtickException $e) {
    fwrite(STDERR, "signing failed: " . $e->getMessage() . "\n");
}
```

```php
Atick::signField(string $pdf, string $pfx, array|string $options = []): string
```

Sign an existing empty signature field. Use the `field_name` option to select the field. Returns the
signed PDF bytes.

- **$pdf** — a PDF containing an empty signature field (see [`prepareFields`](#field-templates)).
- **$pfx** — the credential bytes (binary string).
- **$options** — must include `field_name`; same credential and signing keys as `signPfx`.
- **returns** — the signed PDF as a binary string.

## Deferred / remote-key signing

These three methods cover the deferred (eSign / HSM / remote-key) flow: prepare the PDF, sign the
returned bytes elsewhere, then embed the resulting CMS.

```php
Atick::prepare(string $pdf, array|string $options = []): array  // -> [$prepared, $bytesToSign]
```

Step 1 of deferred signing. Adds an empty signature field, the appearance, and the signature
container, then returns the exact bytes that must be signed. Returns a two-element array:

- **$prepared** — the **prepared PDF** (binary string).
- **$bytesToSign** — the **bytes to sign** (binary string); hash and sign these with the remote key.

For an eSign flow, the InputHash sent to the ASP is the SHA-256 of `$bytesToSign`:

```php
$inputHash = hash('sha256', $bytesToSign);
```

- **$pdf** — the PDF to prepare (binary string).
- **$options** — appearance and signing options (see the [Options](#options) table).
- **returns** — `[$prepared, $bytesToSign]`.

```php
Atick::cmsPfx(string $data, string $pfx, array|string $options = []): string
```

Produce a detached PKCS#7 / CMS signature over `$data` using a PFX. Useful for producing the CMS that
[`embed`](#embed) expects when the signing credential is a local PFX.

- **$data** — the bytes to sign (typically `$bytesToSign` from `prepare`) as a binary string.
- **$pfx** — the credential bytes (binary string).
- **$options** — `password`, `hash_algo`, `pades`, `timestamp`, `tsa_url`, `tsa_auth`, `ltv`.
- **returns** — the detached CMS as a binary string.

<a id="embed"></a>

```php
Atick::embed(string $prepared, string $cms): string
```

Embed a detached CMS / PKCS#7 into a prepared PDF. Returns the signed PDF bytes.

- **$prepared** — the prepared PDF (`$prepared` from `prepare`) as a binary string.
- **$cms** — the detached CMS (from `cmsPfx`, an eSign reply, or an HSM) as a binary string.
- **returns** — the signed PDF as a binary string.

```php
[$prepared, $bytesToSign] = Atick::prepare($pdf, $options);

$cms    = Atick::cmsPfx($bytesToSign, $pfx, ["password" => "secret"]);
$signed = Atick::embed($prepared, $cms);
```

## Field templates

<a id="field-templates"></a>

```php
Atick::prepareFields(string $pdf, array|string $options = []): string
```

Create an empty signature field as a template: the appearance is drawn, but the signature is left
empty so it can be signed later with [`signField`](#signing). Returns the PDF bytes.

- **$pdf** — the PDF to add the field to (binary string).
- **$options** — appearance options plus `field_name`, `page`, `rect` / `placements`.
- **returns** — the PDF with an empty field as a binary string.

## Long-term validation & timestamps

```php
Atick::addDocTimestamp(string $pdf, array|string $options = []): string
```

Add an archive DocTimeStamp (and the DSS validation material) to an already-signed PDF, producing a
PAdES-B-LTA document. Returns the timestamped PDF bytes.

- **$pdf** — an already-signed PDF (binary string).
- **$options** — `tsa_url`, `tsa_auth`, `ltv`, `contents_size`.
- **returns** — the timestamped PDF as a binary string.

## Documents & utilities

<a id="documents-utilities"></a>

```php
Atick::setMetadata(string $pdf, array|string $options = []): string
```

Set the document information (`/Info`) metadata on a PDF. Returns the updated PDF bytes.

- **$pdf** — the PDF to update (binary string).
- **$options** — `title`, `author`, `subject`, `keywords`, `application`, `created`, `modified`
  (see the [Metadata options](#metadata-options) table).
- **returns** — the updated PDF as a binary string.

```php
Atick::decrypt(string $pdf, string $password): string
```

Decrypt a password-protected PDF. Returns the plaintext PDF bytes.

- **$pdf** — the encrypted PDF (binary string).
- **$password** — the open (user) password as a string.
- **returns** — the decrypted PDF as a binary string.

```php
Atick::setFastSigning(bool $on): void
```

Enable or disable the in-memory revocation cache (used to speed up repeated CRL/OCSP lookups).
Passing `false` disables it.

- **$on** — `true` to enable the cache, `false` to disable it.

```php
Atick::version(): string
```

Return the engine version string.

- **returns** — the version as a string.

```php
echo "ATick " . Atick::version();
```

<a id="options"></a>

## Options

The `$options` argument is a PHP associative array (preferred) or a JSON string built with
`json_encode([...])`. All keys are optional unless a method note says otherwise. Keys are grouped
below by purpose.

### Identity & appearance text

| Key | Type | Meaning |
| --- | --- | --- |
| `cn` | string | Common name shown in the appearance. |
| `org` | string | Organisation line. |
| `ou` | string | Organisational unit line. |
| `location` | string | Signing location, also written to the signature. |
| `reason` | string | Reason for signing, also written to the signature. |
| `text` | string | Free text shown in the appearance. |
| `date` | string | Date string shown in the appearance. |
| `dn` | string | Full distinguished name line. |
| `body` | string | Custom-text-only appearance body (`\n` = new line, `*x*` = bold). |
| `heading` | string | Heading line above the signature details. |

### Verified mark

| Key | Type | Meaning |
| --- | --- | --- |
| `show_mark` | bool | Draw the verified mark. |
| `green_tick` | bool | Use the "?" verified mark. |
| `always_check` | bool | Always draw the verified/checked mark. |
| `mark_color` | string hex / name / `[r,g,b]` | Colour of the mark. |
| `mark_gradient` | array of colours | Gradient fill for the mark. |
| `mark_scale` | number | Scale factor for the mark size. |

### Layout & styling

| Key | Type | Meaning |
| --- | --- | --- |
| `text_color` | string hex / name / `[r,g,b]` | Text colour. |
| `bg_color` | string hex / name / `[r,g,b]` | Background colour of the appearance. |
| `border` | bool | Draw a border around the appearance. |
| `font_size` | number | Font size of the appearance text. |
| `width` | number | Appearance width. |
| `height` | number | Appearance height. |

### Placement

| Key | Type | Meaning |
| --- | --- | --- |
| `page` | int | Page number for the signature (1-based). |
| `rect` | `[x1, y1, x2, y2]` | Rectangle of the appearance on `page`. |
| `placements` | `[[page, [x1, y1, x2, y2]], ...]` | Multiple appearance placements (one signature, several pages). |
| `mode` | `"single"` \| `"shared"` | Whether placements share one signature (`"single"`) or are separate. |
| `field_name` | string | Name of the signature field. |

### Cryptography & PAdES

| Key | Type | Meaning |
| --- | --- | --- |
| `pades` | bool | Produce a PAdES signature. |
| `hash_algo` | `"sha256"` \| `"sha384"` \| `"sha512"` | Digest algorithm. |
| `timestamp` | bool | Add an RFC-3161 signature timestamp. |
| `tsa_url` | string | Timestamp authority URL. |
| `tsa_auth` | `["user", "pass"]` | Basic-auth credentials for the TSA. |
| `ltv` | bool | Add long-term validation material (DSS). |
| `lta` | bool | Add an archive DocTimeStamp (PAdES-B-LTA). |
| `contents_size` | int | Size of the signature `/Contents` placeholder (default `16384`). |

### Certification & locking

| Key | Type | Meaning |
| --- | --- | --- |
| `certify` | int | Certification level: `1` = no changes, `2` = form filling, `3` = form filling + annotations. |
| `lock_fields` | `["*"]` or names | Fields to lock after signing (`["*"]` = all). |

### Verification

| Key | Type | Meaning |
| --- | --- | --- |
| `verify` | bool | Verify the certificate before signing. |
| `verify_expiry` | bool | Check certificate validity dates. |
| `verify_crl` | bool | Check the CRL. |
| `verify_ocsp` | bool | Check OCSP. |

### Document security

| Key | Type | Meaning |
| --- | --- | --- |
| `open_password` | string | User/open password for the output PDF. |
| `encrypt_password` | string | Password used to encrypt the output PDF. |
| `owner_password` | string | Owner/permissions password for the output PDF. |

<a id="metadata-options"></a>

## Metadata options

These keys apply to [`setMetadata`](#documents-utilities).

| Key | Type | Meaning |
| --- | --- | --- |
| `title` | string | Document title. |
| `author` | string | Document author. |
| `subject` | string | Document subject. |
| `keywords` | string | Document keywords. |
| `application` | string | Creating/producing application. |
| `created` | string | Creation date. |
| `modified` | string | Modification date. |

## Errors

Every `Atick` method throws `Aniketc068\ATick\AtickException` (extends `RuntimeException`) on failure
— bad password, malformed PDF, network error, invalid options, and so on. The error text is
available from `$e->getMessage()`.

```php
use Aniketc068\ATick\AtickException;

try {
    $signed = Atick::signPfx($pdf, $pfx, $options);
} catch (AtickException $e) {
    fwrite(STDERR, "ATick error: " . $e->getMessage() . "\n");
}
```
