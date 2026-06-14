<?php

declare(strict_types=1);

namespace Aniketc068\ATick;

/**
 * ATick for PHP — a complete PDF digital-signature library.
 *
 * PAdES and CMS signing with a PFX/P12 or PEM file, USB tokens / smart-cards / HSMs (PKCS#11),
 * the Windows certificate store and Indian eSign (CCA); RFC-3161 timestamps, long-term
 * validation (LTV) and a green-tick verified-signature appearance that Adobe shows as valid.
 *
 * The library is self-contained: the matching ATick engine for the running OS/CPU is bundled
 * with the package and loaded automatically through PHP FFI. There are no PHP dependencies and
 * nothing to compile. Requires PHP 7.4+ with the FFI extension enabled (`ffi.enable=1`).
 *
 * Every operation either returns the resulting bytes (a PHP string) or throws an
 * {@see AtickException}.
 *
 * Example:
 * ```php
 * use Aniketc068\ATick\Atick;
 *
 * $signed = Atick::signPfx(
 *     file_get_contents('doc.pdf'),
 *     file_get_contents('my.pfx'),
 *     [
 *         'password'   => 'secret',
 *         'cn'         => 'Aniket Chaturvedi',
 *         'reason'     => 'Approved',
 *         'placements' => [[1, [300, 55, 575, 175]]],
 *         'pades'      => true,
 *         'timestamp'  => true,
 *         'ltv'        => true,
 *     ]
 * );
 * file_put_contents('signed.pdf', $signed);
 * ```
 */
final class Atick
{
    /** @var \FFI|null Lazily-created, shared FFI handle bound to the bundled engine. */
    private static $ffi = null;

    /** This is the PHP binding -> "ATick_php" everywhere branding is fixed. */
    private const BRAND = 'php';

    private function __construct()
    {
    }

    /**
     * The C interface exposed by the ATick engine. size_t maps cleanly to the FFI
     * `size_t` type, which is pointer-sized on every platform (32-bit and 64-bit alike).
     */
    private const CDEF = <<<'CDEF'
        const char* atick_version();
        void atick_free(uint8_t* ptr, size_t len);
        void atick_set_brand(const char* tag);
        void atick_set_fast_signing(int on);
        int atick_sign_pfx(const uint8_t* pdf, size_t pdf_len, const uint8_t* pfx, size_t pfx_len, const char* opt, uint8_t** out, size_t* out_len);
        int atick_embed(const uint8_t* prep, size_t prep_len, const uint8_t* cms, size_t cms_len, uint8_t** out, size_t* out_len);
        int atick_cms_pfx(const uint8_t* data, size_t data_len, const uint8_t* pfx, size_t pfx_len, const char* opt, uint8_t** out, size_t* out_len);
        int atick_decrypt(const uint8_t* pdf, size_t pdf_len, const char* pw, uint8_t** out, size_t* out_len);
        int atick_prepare(const uint8_t* pdf, size_t pdf_len, const char* opt, uint8_t** out, size_t* out_len, uint8_t** out_data, size_t* out_data_len);
        int atick_add_doctimestamp(const uint8_t* pdf, size_t pdf_len, const char* opt, uint8_t** out, size_t* out_len);
        int atick_set_metadata(const uint8_t* pdf, size_t pdf_len, const char* opt, uint8_t** out, size_t* out_len);
        int atick_prepare_fields(const uint8_t* pdf, size_t pdf_len, const char* opt, uint8_t** out, size_t* out_len);
        int atick_sign_field(const uint8_t* pdf, size_t pdf_len, const uint8_t* pfx, size_t pfx_len, const char* opt, uint8_t** out, size_t* out_len);
        CDEF;

    /** Load (once) and return the FFI handle bound to the bundled engine for this OS/CPU. */
    private static function lib(): \FFI
    {
        if (self::$ffi !== null) {
            return self::$ffi;
        }
        if (!\extension_loaded('ffi')) {
            throw new AtickException(
                'ATick needs the PHP FFI extension. Enable it in php.ini (extension=ffi) '
                . 'and set ffi.enable=1 (or ffi.enable=preload).'
            );
        }
        $path = self::enginePath();
        try {
            $ffi = \FFI::cdef(self::CDEF, $path);
        } catch (\Throwable $e) {
            throw new AtickException('Failed to load the ATick engine at ' . $path . ': ' . $e->getMessage());
        }
        // FIXED branding for the PHP binding.
        try {
            $ffi->atick_set_brand(self::BRAND);
        } catch (\Throwable $ignored) {
        }
        self::$ffi = $ffi;
        return $ffi;
    }

    /** Locate the bundled engine for the running OS and CPU architecture. */
    private static function enginePath(): string
    {
        $os = self::osKey();
        $arch = self::archKey();
        $file = ($os === 'windows') ? 'atick.dll' : (($os === 'darwin') ? 'libatick.dylib' : 'libatick.so');
        $base = \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'native' . \DIRECTORY_SEPARATOR;
        $dir = $os . '-' . $arch;
        $path = $base . $dir . \DIRECTORY_SEPARATOR . $file;
        if (!\is_file($path)) {
            throw new AtickException(
                "No bundled ATick engine for this platform ($os-$arch). Expected: $path"
            );
        }
        return $path;
    }

    private static function osKey(): string
    {
        switch (\PHP_OS_FAMILY) {
            case 'Windows':
                return 'windows';
            case 'Darwin':
                return 'darwin';
            default:
                return 'linux'; // Linux / BSD / Solaris -> Linux engine
        }
    }

    private static function archKey(): string
    {
        $m = \strtolower(\trim((string) \php_uname('m')));
        if ($m === 'amd64' || $m === 'x86_64' || $m === 'x64') {
            return 'x86_64';
        }
        if ($m === 'arm64' || $m === 'aarch64') {
            return 'aarch64';
        }
        if ($m === 'i386' || $m === 'i486' || $m === 'i586' || $m === 'i686' || $m === 'x86') {
            return 'i686';
        }
        if (\strpos($m, 'armv7') === 0 || $m === 'armv6l' || $m === 'arm') {
            return 'arm';
        }
        // Fall back to 64-bit vs 32-bit guess from the pointer size.
        return (\PHP_INT_SIZE === 8) ? 'x86_64' : 'i686';
    }

    /** Build a uint8_t[] FFI buffer from a PHP (binary) string. */
    private static function buf(\FFI $ffi, string $s)
    {
        $n = \strlen($s);
        if ($n === 0) {
            // A valid, non-null 1-byte buffer (the engine ignores it because the length is 0).
            return $ffi->new('uint8_t[1]', false);
        }
        $b = $ffi->new("uint8_t[$n]", false);
        \FFI::memcpy($b, $s, $n);
        return $b;
    }

    /**
     * Read the engine's output buffer into a PHP string, free it, and turn a non-zero
     * return code into an AtickException carrying the engine's UTF-8 error message.
     */
    private static function take(\FFI $ffi, int $rc, $out, $outLen): string
    {
        $n = (int) $outLen->cdata;
        $bytes = ($out !== null && $n > 0) ? \FFI::string($out, $n) : '';
        if ($n > 0) {
            $ffi->atick_free($out, $n);
        }
        if ($rc !== 0) {
            throw new AtickException($bytes !== '' ? $bytes : 'ATick operation failed (code ' . $rc . ').');
        }
        return $bytes;
    }

    /** Accept options as an associative array (preferred) or a ready JSON string. */
    private static function opt($options): string
    {
        if (\is_array($options)) {
            return \json_encode($options, \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE);
        }
        return (string) ($options ?? '{}');
    }

    // ---------------------------------------------------------------------
    // Public API
    // ---------------------------------------------------------------------

    /** The library version string (matches the package version). */
    public static function version(): string
    {
        return self::lib()->atick_version();
    }

    /**
     * Enable or disable fast signing — reuse fetched revocation information across many
     * documents signed in the same run (large batch jobs become several times faster).
     */
    public static function setFastSigning(bool $on): void
    {
        self::lib()->atick_set_fast_signing($on ? 1 : 0);
    }

    /**
     * Sign a PDF with a PFX/P12 (or PEM) in one call.
     *
     * @param string       $pdf     The PDF bytes.
     * @param string       $pfx     The PFX/P12 (or PEM) bytes.
     * @param array|string $options password, appearance and flags (cn, reason, date,
     *                              placements, mode, pades, timestamp, ltv, ...).
     * @return string The signed PDF bytes.
     */
    public static function signPfx(string $pdf, string $pfx, $options = []): string
    {
        $ffi = self::lib();
        $out = $ffi->new('uint8_t*');
        $outLen = $ffi->new('size_t');
        $rc = $ffi->atick_sign_pfx(
            self::buf($ffi, $pdf),
            \strlen($pdf),
            self::buf($ffi, $pfx),
            \strlen($pfx),
            self::opt($options),
            \FFI::addr($out),
            \FFI::addr($outLen)
        );
        return self::take($ffi, $rc, $out, $outLen);
    }

    /**
     * Prepare a PDF for deferred / remote signing (eSign / HSM / external key).
     *
     * @return array{0:string,1:string} [preparedPdf, bytesToSign] — sign the second value
     *                                  externally, then call {@see embed()}.
     */
    public static function prepare(string $pdf, $options = []): array
    {
        $ffi = self::lib();
        $out = $ffi->new('uint8_t*');
        $outLen = $ffi->new('size_t');
        $outData = $ffi->new('uint8_t*');
        $outDataLen = $ffi->new('size_t');
        $rc = $ffi->atick_prepare(
            self::buf($ffi, $pdf),
            \strlen($pdf),
            self::opt($options),
            \FFI::addr($out),
            \FFI::addr($outLen),
            \FFI::addr($outData),
            \FFI::addr($outDataLen)
        );

        $n = (int) $outLen->cdata;
        $prepared = ($out !== null && $n > 0) ? \FFI::string($out, $n) : '';
        if ($n > 0) {
            $ffi->atick_free($out, $n);
        }
        if ($rc !== 0) {
            throw new AtickException($prepared !== '' ? $prepared : 'ATick prepare failed.');
        }
        $dn = (int) $outDataLen->cdata;
        $data = ($outData !== null && $dn > 0) ? \FFI::string($outData, $dn) : '';
        if ($dn > 0) {
            $ffi->atick_free($outData, $dn);
        }
        return [$prepared, $data];
    }

    /** Embed a detached CMS/PKCS#7 into a PDF prepared by {@see prepare()}. */
    public static function embed(string $prepared, string $cms): string
    {
        $ffi = self::lib();
        $out = $ffi->new('uint8_t*');
        $outLen = $ffi->new('size_t');
        $rc = $ffi->atick_embed(
            self::buf($ffi, $prepared),
            \strlen($prepared),
            self::buf($ffi, $cms),
            \strlen($cms),
            \FFI::addr($out),
            \FFI::addr($outLen)
        );
        return self::take($ffi, $rc, $out, $outLen);
    }

    /** Produce a detached CMS/PKCS#7 over arbitrary data, signed with a PFX/P12/PEM. */
    public static function cmsPfx(string $data, string $pfx, $options = []): string
    {
        $ffi = self::lib();
        $out = $ffi->new('uint8_t*');
        $outLen = $ffi->new('size_t');
        $rc = $ffi->atick_cms_pfx(
            self::buf($ffi, $data),
            \strlen($data),
            self::buf($ffi, $pfx),
            \strlen($pfx),
            self::opt($options),
            \FFI::addr($out),
            \FFI::addr($outLen)
        );
        return self::take($ffi, $rc, $out, $outLen);
    }

    /** Decrypt a password-protected PDF and return the decrypted bytes. */
    public static function decrypt(string $pdf, string $password): string
    {
        $ffi = self::lib();
        $out = $ffi->new('uint8_t*');
        $outLen = $ffi->new('size_t');
        $rc = $ffi->atick_decrypt(
            self::buf($ffi, $pdf),
            \strlen($pdf),
            $password,
            \FFI::addr($out),
            \FFI::addr($outLen)
        );
        return self::take($ffi, $rc, $out, $outLen);
    }

    /** Add a standalone archive DocTimeStamp (+ DSS) to an already-signed PDF. */
    public static function addDocTimestamp(string $pdf, $options = []): string
    {
        $ffi = self::lib();
        $out = $ffi->new('uint8_t*');
        $outLen = $ffi->new('size_t');
        $rc = $ffi->atick_add_doctimestamp(
            self::buf($ffi, $pdf),
            \strlen($pdf),
            self::opt($options),
            \FFI::addr($out),
            \FFI::addr($outLen)
        );
        return self::take($ffi, $rc, $out, $outLen);
    }

    /** Set document metadata (Title/Author/Subject/Keywords/Creator/CreationDate/ModDate). */
    public static function setMetadata(string $pdf, $options = []): string
    {
        $ffi = self::lib();
        $out = $ffi->new('uint8_t*');
        $outLen = $ffi->new('size_t');
        $rc = $ffi->atick_set_metadata(
            self::buf($ffi, $pdf),
            \strlen($pdf),
            self::opt($options),
            \FFI::addr($out),
            \FFI::addr($outLen)
        );
        return self::take($ffi, $rc, $out, $outLen);
    }

    /** Prepare an empty signature field (template) — appearance drawn, signature left empty. */
    public static function prepareFields(string $pdf, $options = []): string
    {
        $ffi = self::lib();
        $out = $ffi->new('uint8_t*');
        $outLen = $ffi->new('size_t');
        $rc = $ffi->atick_prepare_fields(
            self::buf($ffi, $pdf),
            \strlen($pdf),
            self::opt($options),
            \FFI::addr($out),
            \FFI::addr($outLen)
        );
        return self::take($ffi, $rc, $out, $outLen);
    }

    /** Sign an existing empty field (e.g. from {@see prepareFields()}) with a PFX/P12/PEM. */
    public static function signField(string $pdf, string $pfx, $options = []): string
    {
        $ffi = self::lib();
        $out = $ffi->new('uint8_t*');
        $outLen = $ffi->new('size_t');
        $rc = $ffi->atick_sign_field(
            self::buf($ffi, $pdf),
            \strlen($pdf),
            self::buf($ffi, $pfx),
            \strlen($pfx),
            self::opt($options),
            \FFI::addr($out),
            \FFI::addr($outLen)
        );
        return self::take($ffi, $rc, $out, $outLen);
    }
}
