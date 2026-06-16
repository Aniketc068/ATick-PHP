# Security Policy

ATick signs documents, so we take security seriously and appreciate responsible
disclosure.

## Supported versions

| Version | Supported |
|---------|-----------|
| 1.0.x   | Yes       |

## Reporting a vulnerability

**Please do not report security issues through public GitHub issues.**

Instead, email **info@axonatetech.com** with:

- a description of the issue and its impact,
- steps to reproduce (a minimal proof of concept if possible),
- the ATick version (`Atick::version()`), `php --version`, and operating system.

You will receive an acknowledgement within **72 hours**, and we will keep you
informed as we investigate and prepare a fix. Please give us a reasonable amount
of time to release a fix before any public disclosure.

## Scope

Issues of interest include (but are not limited to):

- ways to make an invalid or tampered signature appear valid,
- incorrect signature byte-range or container construction,
- mishandling of private keys, PINs, or passwords,
- weaknesses in timestamp or revocation (LTV) handling.

Thank you for helping keep ATick and its users safe.
