---
sd_hide_title: true
---

# ATick for PHP

```{raw} html
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "SoftwareApplication",
  "name": "ATick for PHP",
  "alternateName": "ATick PHP PDF digital signature library",
  "description": "Standalone PHP library for PDF digital signatures — PAdES and CMS signing with a PFX/PEM file, deferred / remote-key (eSign / HSM / token) signing, RFC-3161 timestamps, long-term validation (LTV), and a green-tick verified-signature appearance that Adobe shows as valid. Bundled engine via PHP FFI, cross-platform, composer require.",
  "applicationCategory": "DeveloperApplication",
  "operatingSystem": "Windows, Linux, macOS",
  "programmingLanguage": "PHP",
  "softwareVersion": "1.0.5",
  "offers": { "@type": "Offer", "price": "0", "priceCurrency": "USD" },
  "license": "https://www.gnu.org/licenses/agpl-3.0.html",
  "author": { "@type": "Person", "name": "Aniket Chaturvedi" },
  "url": "https://atick-php.readthedocs.io/",
  "codeRepository": "https://github.com/Aniketc068/ATick-PHP",
  "downloadUrl": "https://packagist.org/packages/aniketc068/atick",
  "keywords": "PDF digital signature PHP, sign PDF PHP, PAdES, eSign, LTV, RFC-3161 timestamp, Adobe valid signature, green tick"
}
</script>
```

```{image} _static/green_tick.png
:class: hero-mark
:width: 78px
:align: center
```

```{rst-class} hero-title
Sign PDFs with confidence
```

```{rst-class} hero-sub
ATick for PHP is the standalone PDF digital-signature library for PHP — PAdES & CMS signing,
deferred / remote-key signing and a green-tick appearance Adobe shows as valid, in **one Composer
package** with the engine bundled in.
```

::::{div} hero-buttons
:::{button-ref} quickstart
:color: primary
:class: hero-btn
Get started  →
:::
:::{button-ref} api
:color: primary
:outline:
:class: hero-btn
API reference
:::
::::

```php
<?php
require 'vendor/autoload.php';

use Aniketc068\ATick\Atick;

$signed = Atick::signPfx(
    file_get_contents('doc.pdf'),
    file_get_contents('my.pfx'),
    ['password' => '••••', 'cn' => 'Aniket Chaturvedi', 'reason' => 'Approved',
     'green_tick' => true, 'page' => 1, 'rect' => [300, 55, 575, 175],
     'pades' => true, 'timestamp' => true, 'ltv' => true],
);
file_put_contents('signed.pdf', $signed);
```

```{note}
ATick runs anywhere PHP runs — Laravel, Symfony, WordPress, plain PHP, CLI scripts and queue
workers. It uses PHP **FFI**, so the FFI extension must be enabled (`ffi.enable=1`).
```

---

## Everything you need to sign PDFs

::::{grid} 1 2 2 3
:gutter: 3

:::{grid-item-card} {octicon}`key;1.3em;sd-text-success` Sign anywhere
PFX/P12 **or PEM** files directly, and tokens / HSMs / eSign ESPs via the deferred flow.
+++
[Signing »](signing.md)
:::

:::{grid-item-card} {octicon}`shield-check;1.3em;sd-text-success` Full PAdES
B-B, B-T, **B-LT**, **B-LTA** with RFC-3161 timestamps and long-term validation.
+++
[PAdES levels »](pades.md)
:::

:::{grid-item-card} {octicon}`globe;1.3em;sd-text-success` Deferred / eSign
Two-step `prepare` → external CMS → `embed`; the InputHash is the SHA-256 of the bytes-to-sign.
+++
[Deferred / eSign »](esign.md)
:::

:::{grid-item-card} {octicon}`paintbrush;1.3em;sd-text-success` Rich appearance
Logo or CN-on-the-left, the validity mark, distinguished name, custom text, invisible signatures.
+++
[Appearance »](appearance.md)
:::

:::{grid-item-card} {octicon}`lock;1.3em;sd-text-success` Trust & control
Certification (DocMDP), field-locking, pre-sign checks, password protection and metadata.
+++
[Certification »](certification.md)
:::

:::{grid-item-card} {octicon}`rocket;1.3em;sd-text-success` Built for scale
A revocation cache speeds up batch signing; every failure is a normal PHP exception.
+++
[Fast signing »](fast-signing.md)
:::

::::

---

## The green tick your readers trust

```{image} _static/valid_signature_adobe.png
:alt: Adobe Reader — signed and all signatures are valid, with the ATick green tick
:width: 600px
:align: center
:class: shadow-img
```

---

## Why ATick

```{list-table}
:header-rows: 1
:widths: 32 68

* - 
  - ATick for PHP
* - **External services**
  - none — the crypto, PKCS#12/PEM, image decode, timestamping and LTV are all in the bundled engine
* - **Install**
  - `composer require aniketc068/atick` — the engine ships with the package, nothing to compile
* - **PHP**
  - PHP 7.4 → the latest 8.x (uses the FFI extension)
* - **Platforms**
  - Windows 7+ (x64/x86/ARM64), Linux (x64/ARM64/ARM, every glibc distro), macOS (Intel/Apple Silicon)
* - **Errors**
  - every failure is an `Aniketc068\ATick\AtickException` you can catch
```

```{toctree}
:hidden:

getting-started
guide
reference
```
