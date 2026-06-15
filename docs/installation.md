# Installation

ATick for PHP is one Composer package with the matching engine bundled inside — no compiler, no
build step, no other PHP dependency.

## Requirements

- **PHP 7.4 or newer** (through the latest 8.x), with the **FFI extension** enabled.
- On the CLI the FFI extension is usually available out of the box. On a web SAPI (PHP-FPM / Apache)
  enable it in `php.ini`:

```ini
extension=ffi
ffi.enable=1        ; or: ffi.enable=preload
```

- Any supported OS/arch — Windows 7+ (x64/x86/ARM64), Linux (x64/ARM64/ARM, every glibc distro), macOS (Intel/Apple Silicon).

## Install

```bash
composer require aniketc068/atick
```

## Verify

```php
<?php
require 'vendor/autoload.php';

use Aniketc068\ATick\Atick;

echo Atick::version(), "\n";   // e.g. 1.0.6
```

## How the bundled engine is loaded

The package ships the matching ATick engine for each platform inside the package. At
`require 'vendor/autoload.php'` the right one for your OS and architecture is loaded automatically
through **PHP FFI** — there is no compilation on install.

| Platform | Target |
| --- | --- |
| `windows-x86_64` / `windows-i686` | Windows 7 → 11 (64/32-bit) |
| `windows-aarch64` | Windows on ARM64 |
| `linux-x86_64` / `linux-aarch64` / `linux-arm` / `linux-i686` | Linux x64 / ARM64 / ARM / 32-bit (glibc 2.17+, every distro) |
| `darwin-x86_64` / `darwin-aarch64` | macOS Intel / Apple Silicon |

PHP supported: **7.4 → latest 8.x**.

## Other languages

ATick is the same engine across runtimes. The same code translates directly:

- Python — `pip install atick`
- Java — `io.github.aniketc068:atick`
- .NET — `dotnet add package ATick`
- Node.js — `npm install atick`
