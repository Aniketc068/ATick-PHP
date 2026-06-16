# Appearance

The signature appearance is controlled entirely by option keys passed to
`Atick::signPfx($pdf, $pfx, $options)`. By default ATick shows its logo on the
left, the signer details on the right, and the validity mark.

```php
require 'vendor/autoload.php';
use Aniketc068\ATick\Atick;

$pdf = file_get_contents("doc.pdf");
$pfx = file_get_contents("my.pfx");

$signed = Atick::signPfx($pdf, $pfx, [
    "cn"         => "Axonate Tech",   // common name (shown bold after "Digitally Signed by:")
    "org"        => "Acme Corp",           // organisation line
    "reason"     => "Approved",            // "Reason: …"
    "location"   => "New Delhi",           // "Location: …"
    "green_tick" => true,
]);

file_put_contents("signed.pdf", $signed);
```

Long signer names **wrap** onto more lines instead of shrinking the font, so the box never overflows.

## Date / time

```php
Atick::signPfx($pdf, $pfx, ["cn" => "Aniket"]);                                  // current time (default)
Atick::signPfx($pdf, $pfx, ["cn" => "Aniket", "date" => "Signed on 10-Jun-2026"]); // a fixed string
Atick::signPfx($pdf, $pfx, ["cn" => "Aniket", "date" => ""]);                    // no date line
```

## The left side

The `image` key controls what is drawn on the left of the appearance:

```php
Atick::signPfx($pdf, $pfx, ["cn" => "Aniket"]);                    // default: the ATick logo
Atick::signPfx($pdf, $pfx, ["cn" => "Aniket", "image" => "none"]); // no logo
Atick::signPfx($pdf, $pfx, ["cn" => "Aniket", "image" => "cn"]);   // the CN as large text on the LEFT (Adobe-style)
```

| `image` value | Result |
|---|---|
| omitted | the default ATick logo |
| `"none"` | no logo on the left |
| `"cn"` | the signer name as text on the left instead of a logo |

## The validity mark — ATick's signature look

```{image} _static/green_tick.png
:alt: ATick green tick
:width: 150px
:align: center
```

The mark sits centred in the appearance and tells the reader the signature's status at a glance:

```php
Atick::signPfx($pdf, $pfx, ["cn" => "Aniket", "green_tick" => true]);    // the "?" mark — Adobe paints it GREEN if valid+trusted, RED if invalid
Atick::signPfx($pdf, $pfx, ["cn" => "Aniket", "always_check" => true]);  // ATick's green-tick graphic as the base (Adobe still reds a bad signature)
Atick::signPfx($pdf, $pfx, ["cn" => "Aniket", "green_tick" => false]);   // no mark — a plain signature
```

- **`green_tick => true`** — the classic validity mark: a `?` that Adobe Acrobat repaints **green**
  for a valid, trusted signature and **red** for a broken one.
- **`always_check => true`** — uses ATick's own green-tick graphic (above) as the base, so the tick
  shows in every viewer; Adobe still overlays a red mark if the signature is actually invalid.
- **`green_tick => false`** — no mark; a plain signature appearance.

### What it looks like

The appearance ATick draws — the signer details with the green tick centred over them:

```{image} _static/signature_appearance.png
:alt: ATick signature appearance with the green tick
:width: 430px
:align: center
:class: shadow-img
```

### How Adobe shows it

When the certificate is valid and trusted, **Adobe Reader / Acrobat reports “Signed and all
signatures are valid”** and paints the tick green — exactly the reassurance your readers expect:

```{image} _static/valid_signature_adobe.png
:alt: Adobe Reader — signed and all signatures are valid, with the ATick green tick
:width: 580px
:align: center
:class: shadow-img
```

### Every state Adobe can show

ATick draws the appearance and the mark; **Adobe then colours the mark based on the signature's
validity and whether it trusts the certificate**, so your reader instantly sees the status:

::::{grid} 1 2 2 4
:gutter: 3

:::{grid-item-card} {octicon}`check-circle-fill;1.2em;sd-text-success` Valid & trusted
```{image} _static/signature_appearance.png
:alt: valid - green tick
:class: shadow-img
```
+++
Signature intact **and** the certificate chains to a root Adobe trusts - *"Signed and all signatures are valid."*
:::

:::{grid-item-card} {octicon}`question;1.2em;sd-text-warning` Validity unknown
```{image} _static/sig_unknown.png
:alt: validity unknown
:class: shadow-img
```
+++
Signature intact, but Adobe **doesn't trust the certificate's root** - *"Validity unknown."*
:::

:::{grid-item-card} {octicon}`unverified;1.2em;sd-text-warning` Not verified
```{image} _static/sig_notverified.png
:alt: signature not verified
:class: shadow-img
```
+++
Adobe **hasn't validated** the signature yet (no trust information) - *"Signature not verified."*
:::

:::{grid-item-card} {octicon}`x-circle-fill;1.2em;sd-text-danger` Invalid
```{image} _static/sig_invalid.png
:alt: invalid - red cross
:class: shadow-img
```
+++
The document was **changed after signing** (or the signature is broken) - *"Signature is invalid."*
:::

::::

So the **green** tick appears only when the signature is valid *and* the signer's certificate chains
to a root Adobe trusts (the Adobe Approved Trust List, or your organisation's trust). The same ATick
appearance shows the question-mark or red-cross state automatically — you don't draw those; Adobe does.

### Colouring the mark

Colour the mark with a hex string, a CSS colour name, or an `[r, g, b]` array — or fill it with an
axial gradient:

```php
Atick::signPfx($pdf, $pfx, ["cn" => "Aniket", "green_tick" => true, "mark_color" => "#E53935"]);        // hex
Atick::signPfx($pdf, $pfx, ["cn" => "Aniket", "green_tick" => true, "mark_color" => "blue"]);           // CSS name
Atick::signPfx($pdf, $pfx, ["cn" => "Aniket", "green_tick" => true, "mark_color" => [255, 140, 0]]);    // RGB array
Atick::signPfx($pdf, $pfx, ["cn" => "Aniket", "green_tick" => true, "mark_gradient" => ["red", "orange", "yellow"]]);  // gradient
```

Use `mark_scale` to resize the mark relative to the appearance box.

## Distinguished name

```php
Atick::signPfx($pdf, $pfx, [
    "cn" => "Axonate Tech",
    "dn" => "CN=Axonate Tech, O=Personal, C=IN",
]);
```

The DN is shown directly under the "Digitally Signed by:" line.

## Custom-text-only appearance

Show **only** your own text — no "Signed by", no date, no CN structure. Inside `body`, `\n` starts a
new line and `*word*` makes that run **bold**:

```php
Atick::signPfx($pdf, $pfx, [
    "body" => "*APPROVED*\nReviewed by: *Axonate Tech*\nThis document is *legally binding*.",
]);
```

```{note}
In a PHP **double-quoted** string, `\n` is a real newline character — use double quotes for `body`
(single-quoted strings leave `\n` literal). ATick reads each newline as a line break.
```

## Positioning the appearance

Place the appearance with `page` + `rect`, or stamp several positions at once with `placements`.
Coordinates are PDF points as `[x1, y1, x2, y2]`.

```php
Atick::signPfx($pdf, $pfx, [
    "cn" => "Aniket", "green_tick" => true, "page" => 1, "rect" => [300, 55, 575, 175],
]);

// one stamp per entry: [page, [x1,y1,x2,y2]]
Atick::signPfx($pdf, $pfx, [
    "cn" => "Aniket", "green_tick" => true,
    "placements" => [[1, [300, 55, 575, 175]], [2, [300, 55, 575, 175]]],
]);
```

You can also size the box directly with `width` and `height`.

## Invisible signature

A cryptographically valid signature that draws nothing on the page — pass an empty `placements`
array:

```php
Atick::signPfx($pdf, $pfx, ["cn" => "Aniket", "placements" => []]);   // empty placements
```

## Other appearance options

| Key | Purpose |
|---|---|
| `heading` | the heading line at the top of the appearance |
| `text` | extra free text line |
| `ou` | organisational-unit line |
| `font_size` | size of the appearance text |
| `text_color` | colour of the text |
| `bg_color` | background fill of the box |
| `border` | draw a border around the box |
| `width`, `height` | the box size |
| `mark_scale` | scale factor for the validity mark |

## Errors

Every failure throws an `Aniketc068\ATick\AtickException`:

```php
try {
    Atick::signPfx($pdf, $pfx, ["cn" => "Aniket", "image" => "missing.png"]);
} catch (\Aniketc068\ATick\AtickException $e) {
    echo "signing failed: " . $e->getMessage();
}
```
