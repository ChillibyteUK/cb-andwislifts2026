# andwis lifts Design Breakdown

## Implementation Decisions

- The page will be built manually in WordPress using modular ACF blocks.
- Header and footer are global theme templates, not blocks.
- Contact data should come from Site-Wide Settings where practical.
- Images should be optional and render gracefully when empty.
- Poppins is self-hosted and wired into the theme.
- Kicker fields have been removed from all blocks.
- Every block ACF field group must start with a message field whose label is the block name (message content blank). Added by `add_block.sh` — do not remove it.
- Use Bootstrap `.container` / `.row` / `.col` grid classes, not custom CSS grids. Stats is the exception (5-col layout).
- Full-bleed backgrounds go on the `<section>`, never on a child `.container` wrapper. No border-radius on full-bleed sections.

## PHP Coding Standards

### Indentation

- **Tabs only**, one tab per nesting level. Never spaces.
- Variables aligned by padding with spaces after the longest varname:

```php
$section_id = $block['anchor'] ?? '';
$extra      = $block['className'] ?? '';
$heading    = get_field( 'heading' );
```

### Control structures

- **Braces on same line**: `if () {` not `{` on its own line.
- **No colon syntax**: never `if:` / `endif;` / `foreach:` / `endforeach;`.

### PHP/HTML interleaving (block templates)

`<?php` and `if` go on separate lines. Do not cuddle them (`<?php if ( ... ) { ?>` is wrong).

HTML is indented to its DOM nesting level (where it sits in the markup), independent of PHP indentation. PHP tags and control structures sit at the HTML level. Only `?>` / `<?php` delimiters go one deeper (inside the braces).

```php
<div class="container">
    <div class="cb-section-head">
        <?php
        if ( $heading ) {
            ?>
        <h2><?= esc_html( $heading ); ?></h2>
            <?php
        }
        ?>
    </div>
</div>
```

No redundant `?>` / `<?php` pairs — every time you exit PHP with `?>`, there must be meaningful HTML before re-entering with `<?php`. If there's nothing to output, don't exit PHP.

````

### Output

- Use short-tag syntax: `<?= esc_html( $var ); ?>` or `<?= esc_url( $url ); ?>`. Never `<?php echo`.

### Section IDs

```php
$section_id = $block['anchor'] ?? $block['id'] ?? wp_unique_id( 'cb-blockname-' );
````

Always output the ID unconditionally:

```php
<section class="..." id="<?= esc_attr( $section_id ); ?>">
```

### Block structure

- Wrapping element is `<section>` (or `<div>` for non-semantic wrappers like cb-pill-strip).
- No unnecessary wrapper divs. Background lives on the `<section>`, grid lives on Bootstrap `.row` / `.col-*` directly.
- BEM naming: `.cb-blockname__element`

### Never edit compiled files

- `css/child-theme.css` / `css/child-theme.min.css` — edit `src/sass/` instead
- `js/child-theme.js` / `js/child-theme.min.js` — edit `src/js/` instead

## Enqueued Assets

All enqueued via `cb_theme_enqueue()` in `inc/cb-theme.php` and `cb_enqueue_*()` in `functions.php`.

### Stylesheets

| Handle        | Source                     | Note                                         |
| ------------- | -------------------------- | -------------------------------------------- |
| `cb-theme`    | `/css/child-theme.min.css` | Main compiled theme CSS (Bootstrap + custom) |
| `swiper`      | CDN (swiper@10)            | Sliders/carousels                            |
| `lenis-style` | CDN (lenis@1.3.11)         | Smooth scrolling CSS                         |

### Scripts

| Handle               | Source                   | Dependencies | Note                                   |
| -------------------- | ------------------------ | ------------ | -------------------------------------- |
| `cb-theme-js`        | `/js/child-theme.min.js` | —            | Understrap bootstrap bundle + theme JS |
| `swiper`             | CDN (swiper@10)          | —            | Sliders                                |
| `lenis`              | CDN (lenis@1.3.11)       | —            | Smooth scrolling                       |
| `gsap`               | CDN (gsap@3.12.7)        | —            | Animations                             |
| `gsap-scrolltrigger` | CDN (gsap@3.12.7)        | `gsap`       | Scroll-triggered animations            |

### Commented-out / available

Splide, GLightbox, Tom Select, AOS, lightbox — commented out in `cb_theme_enqueue()`. Uncomment when needed.

## Global Theme Components

### Header

Source HTML: `.topnav`

Global template: `header.php`

Status: deferred, except for replacing existing font preloads with self-hosted Poppins.

Includes:

- Sticky forest-green header
- Site logo
- Primary nav menu
- Anchor links to page sections
- Mobile horizontal-scroll nav as supplied in the design

### Footer

Source HTML: `footer.site`

Global template: `footer.php`

Status: deferred.

Includes:

- Site logo
- Footer tagline
- Copyright line
- Launch/status message

## ACF Block Components

### CB Hero

Source HTML: `.hero`

Fields:

- Background image
- Eyebrow pulse toggle
- Heading
- Highlighted heading text
- Subline
- Intro text
- Show wave divider toggle

### CB Image Text Checklist

Source HTML: `.reassure`

Fields:

- Heading
- Body copy
- Small list label
- Checklist repeater
- Image
- Image badge/tag text
- Image position
- Background style

### CB Service Cards

Source HTML: `.services .section-head` and `.service-cols`

Fields:

- Heading
- Intro text
- Cards repeater:
  - Number/label
  - Title
  - Description
  - Optional link

### CB Feature Accordion

Source HTML: `.mobility-block`

Fields:

- Image
- Heading
- Intro text
- Accordion repeater:
  - Title
  - Body
  - Included-items repeater
  - Open by default toggle
- Background style

### CB Compliance

Source HTML: `.compliance`

Fields:

- Heading
- Body copy
- Image
- Accreditation/logo repeater:
  - Logo image
  - Fallback label
- Image position

### CB Logo Flow

Source HTML: `.merger`

Fields:

- Heading
- Intro text
- Legacy brand logo repeater
- Result brand logo
- Optional text fallback for missing logos

### CB Stats

Source HTML: `.numbers`

Fields:

- Heading
- Optional intro text
- Stats repeater:
  - Value
  - Description

### CB Sectors

Source HTML: `.sectors`

Fields:

- Heading
- Body copy
- CTA label
- CTA link
- Central image
- Six fixed sector labels:
  - Top
  - Upper left
  - Upper right
  - Lower left
  - Lower right
  - Bottom

### CB Customer Grid

Source HTML: `.customers`

Fields:

- Heading
- Customer repeater:
  - Name
  - Optional logo
- Note text

### CB Contact Cards

Source HTML: `.contact`

Fields:

- Heading
- Intro text
- Contact card repeater:
  - Icon
  - Title
  - Description
  - Link value
  - Link URL
- Note text

Contact values should preferably default from Site-Wide Settings where useful.

### CB Pill Strip

Source HTML: `.legacy-strip`

Fields:

- Label
- Pills repeater:
  - Text

## Suggested Page Order

1. CB Hero
2. CB Image Text Checklist
3. CB Service Cards
4. CB Feature Accordion
5. CB Compliance
6. CB Logo Flow
7. CB Stats
8. CB Sectors
9. CB Customer Grid
10. CB Contact Cards
11. CB Pill Strip
