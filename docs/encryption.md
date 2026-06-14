# Encryption

ATick reads and writes password-protected PDFs through the same `Atick::signPfx` entry point,
plus a dedicated `Atick::decrypt` helper. All passwords are passed as keys inside the
options array.

```php
<?php
require 'vendor/autoload.php';

use Aniketc068\ATick\Atick;
```

| Option key        | Applies to    | Meaning                                                              |
| ----------------- | ------------- | -------------------------------------------------------------------- |
| `open_password`   | Input PDF     | Password used to open an already-encrypted PDF before signing it.    |
| `encrypt_password`| Output PDF    | User password — required to open the signed PDF that ATick produces. |
| `owner_password`  | Output PDF    | Owner/permissions password for the signed output (optional).         |

## Password-protect the output

Add `encrypt_password` to encrypt the signed PDF that ATick writes. Supply `owner_password`
as well to set a separate owner/permissions password; if you omit it, the owner password
defaults to the user password.

```php
<?php
require 'vendor/autoload.php';

use Aniketc068\ATick\Atick;

$pdf = file_get_contents("contract.pdf");
$pfx = file_get_contents("signer.pfx");

$signed = Atick::signPfx($pdf, $pfx, [
  "password" => "••••",
  "encrypt_password" => "open-me",
  "owner_password" => "owner",
]);

file_put_contents("contract-signed.pdf", $signed);
```

```{admonition} The signature stays valid
:class: note
The output is AES-128 encrypted. The signature's `/Contents` is exempt from encryption,
so the signed byte range still verifies in any compliant PDF reader.
```

## Sign an encrypted input

If the input PDF is already password-protected, pass `open_password` so ATick can open it
before signing. The decrypted document is signed and then written back out (encrypt the
output again with `encrypt_password` if you want the result to stay protected).

```php
<?php
require 'vendor/autoload.php';

use Aniketc068\ATick\Atick;

$pdf = file_get_contents("locked.pdf");
$pfx = file_get_contents("signer.pfx");

$signed = Atick::signPfx($pdf, $pfx, [
  "password" => "••••",
  "open_password" => "the-input-password",
]);

file_put_contents("locked-signed.pdf", $signed);
```

```{tip}
You can combine the keys: open an encrypted input with `open_password` and re-encrypt the
signed output in one call by also passing `encrypt_password` (and optionally `owner_password`).
```

## Decrypt a PDF

Use `Atick::decrypt` to strip the password protection from a PDF and obtain its plaintext bytes.

```php
<?php
require 'vendor/autoload.php';

use Aniketc068\ATick\Atick;

$encrypted = file_get_contents("locked.pdf");

$plain = Atick::decrypt($encrypted, "the-password");

file_put_contents("unlocked.pdf", $plain);
```

## Handling failures

Both `Atick::signPfx` and `Atick::decrypt` throw an `AtickException` on failure — for
example, when a password is wrong or the input PDF is not actually encrypted.

```php
<?php
require 'vendor/autoload.php';

use Aniketc068\ATick\Atick;
use Aniketc068\ATick\AtickException;

try {
  $plain = Atick::decrypt(file_get_contents("locked.pdf"), "wrong-pw");
  file_put_contents("unlocked.pdf", $plain);
} catch (AtickException $err) {
  fwrite(STDERR, "Could not decrypt PDF: " . $err->getMessage() . "\n");
}
```
