# ZplKit
PHP library for generating Zebra Programming Language (ZPL) code for Zebra printers

### Layout options

| Section/type | Options |
| --- | --- |
| `label` | Required `width`; optional `height`, `top` (default 0), `left` (default 0), `darkness` (0–30). Omitted height and darkness retain printer settings. |
| `text` | Required `x`, `y`, `width`, and either `field` (a binding name) or `value` (fixed text). Optional `font_size` (30), `lines` (1), `line_spacing` (0), `align` (`L`, `C`, `R`, `J`; default `L`). Uses scalable font 0. |
| `box` / `line` | Required `x`, `y`, `width`, `height`; optional `thickness` (1). Set height to 0 for a horizontal line or width to 0 for a vertical line. |
| `image` | Required `filename` (PNG), `x`, `y`. Converted using ImageMagick. |
| `graphic` | Required `filename` (converted ZPL graphic), `x`, `y`. Loads an existing graphic without requiring Imagick. |
