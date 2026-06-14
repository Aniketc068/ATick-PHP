# Indian eSign (CCA)

ATick for PHP supports the CCA eSign Online Electronic Signature Service for **every API version**
(v1.x … v3.x). The flow is the same across versions — only the request XML attributes differ. The
same two-step pattern also covers any **remote key**: an HSM, USB token, smart-card, or the Windows
certificate store.

```
PDF  ->  SHA-256 of the ByteRange (the InputHash, hex)
     ->  build the <Esign …> request XML for your version, put the InputHash in <InputHash>
     ->  sign the request XML (your own means / your ESP's SDK)   [enveloped W3C XML-DSig]
     ->  POST it (multipart/form-data) to the ESP
     ->  EsignResp -> <DocSignature> (pkcs7 / pkcs7Pdf / pkcs7complete)
     ->  embed it into the PDF
```

```php
<?php
require 'vendor/autoload.php';

use Aniketc068\ATick\Atick;
```

Every call takes its configuration as a PHP associative array (or a JSON options string), and every
failure throws an `AtickException`.

## Step 1 — prepare + hash

`Atick::prepare` returns an array: the first element is the prepared PDF, and the second is the exact
bytes that must be signed (the ByteRange). The eSign **InputHash** is simply the SHA-256 of
`$bytesToSign`.

```php
$pdf = file_get_contents("in.pdf");

// options: cn, reason, placements / page+rect, field_name, pades, contents_size.
// Leave room for the chain + revocation + timestamp that a pkcs7Pdf reply carries.
[$prepared, $bytesToSign] = Atick::prepare($pdf, [
  "cn" => "DS TEST",
  "reason" => "eSign",
  "placements" => [[1, [300, 55, 575, 175]]],
  "contents_size" => 16384
]);

// The InputHash that goes into <InputHash> (hex).
$inputHashHex = hash("sha256", $bytesToSign);
```

## Step 2 — build and sign the request XML

Put `$inputHashHex` into `<InputHash>`, then sign the request XML (an enveloped W3C XML-DSig) with
your own means — your ASP signing key or your ESP's SDK — and POST it to the ESP.

```php
$request =
    '<Esign ver="2.1" sc="Y" ts="…" txn="TXN1" ekycIdType="A" aspId="…" '
  . 'AuthMode="1" responseSigType="pkcs7Pdf" responseUrl="https://…/"><Docs>'
  . '<InputHash id="1" hashAlgorithm="SHA256" docInfo="Agreement">'
  . $inputHashHex
  . '</InputHash></Docs></Esign>';

// Sign $request (enveloped XML-DSig) with your own means / your ESP's SDK,
// then POST the signed XML (multipart/form-data) to the ESP.
```

```{note}
The request XML is signed with **your ASP credential**, not with ATick. ATick's job is the PDF: it
produced `$inputHashHex` from the ByteRange in step 1, and it will embed the ESP's reply in step 3.
```

## Step 3 — embed the ESP response

The `EsignResp` carries the signature in `<DocSignature>` (Base64). Decode it and pass the resulting
CMS bytes to `Atick::embed`, together with the prepared PDF from step 1.

```php
$cms = base64_decode($docSignatureBase64);  // from <DocSignature>

$signed = Atick::embed($prepared, $cms);
file_put_contents("signed.pdf", $signed);
```

`pkcs7Pdf` and `pkcs7complete` responses already carry the full chain, the revocation (under
`pdfRevocationInfoArchival`) and a CA timestamp — so the embedded signature is **LTV-complete and
timestamped** out of the box.

## `responseSigType`

| Value | Returns | Embed with |
|---|---|---|
| `pkcs7` | a CMS, signer cert only (no revocation) | `Atick::embed` |
| `pkcs7Pdf` | a CMS, full chain + CRL/OCSP (signed attr) + timestamp | `Atick::embed` |
| `pkcs7complete` | a CMS, full chain + revocation (unsigned attr) | `Atick::embed` |

Request a `pkcs7Pdf` or `pkcs7complete` reply so the embedded signature is LTV-complete.

## Other remote keys — HSM, token, card, Windows store

The same three steps cover any key that never leaves its holder. Instead of POSTing to an ESP, sign
`$bytesToSign` directly with your own signer and produce a **detached CMS / PKCS#7 SignedData**:

- **HSM / USB token / smart-card** — your vendor's PKCS#11 module.
- **Windows certificate store** — the native certificate store API.

```php
[$prepared, $bytesToSign] = Atick::prepare($pdf, [
  "cn" => "DS TEST",
  "reason" => "Approved",
  "pades" => true
]);

// Sign $bytesToSign with your own signer; return a detached CMS over those exact bytes.
$cms = signWithMyProvider($bytesToSign);   // PKCS#11 module / native store / vendor signer

$signed = Atick::embed($prepared, $cms);
file_put_contents("signed.pdf", $signed);
```

```{tip}
The CMS you build in step 2 must cover **`$bytesToSign`** exactly and use the same hash algorithm
(SHA-256 by default) that ATick used to prepare the document. ATick owns the PDF structure; your
signer owns the private key.
```

## Simulating the ESP for testing

To run the whole flow end-to-end without a live ESP, build the detached CMS yourself from a
credential file with `Atick::cmsPfx`. It stands in for the external signer, producing a
`pkcs7Pdf`-style CMS over `$bytesToSign`:

```php
$pfx = file_get_contents("signer.pfx");

[$prepared, $bytesToSign] = Atick::prepare($pdf, ["cn" => "DS TEST", "pades" => true]);
$cms = Atick::cmsPfx($bytesToSign, $pfx, [
  "password" => "••••",
  "pades" => true,
  "timestamp" => true
]);
$done = Atick::embed($prepared, $cms);

file_put_contents("signed.pdf", $done);
```
