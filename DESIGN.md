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

## Copy Style

- **Plain hyphens only.** Never em dashes (`—`) or en dashes (`–`). The source copy
  brief uses em dashes heavily, so convert them when lifting copy from it.
- WordPress `wptexturize()` rewrites a spaced hyphen back into an en dash at priority
  10 on `the_content` / `the_title`, so clean stored copy is not enough on its own.
  `cb_plain_hyphens()` in `inc/cb-theme.php` runs at priority 20 to revert them.
  Smart quotes and apostrophes are deliberately left alone.
- When seeding content programmatically, `wp_insert_post()` / `wp_update_post()`
  expect **slashed** data - pass `post_content` through `wp_slash()`, and build any
  block-attribute JSON with `JSON_UNESCAPED_UNICODE`, or `wp_unslash()` will strip
  the backslash from escapes like `—` and leave a literal `u2014` in the copy.

## Content Model

Registered in `inc/cb-posttypes.php`. Each of these is built from ACF blocks in the
editor and rendered through `page.php`, exactly like a page.

| Post type    | Admin label  | Single URL                  | Hub                              |
| ------------ | ------------ | --------------------------- | -------------------------------- |
| `service`    | Services     | `/lift-services/{slug}/`    | Page at `/lift-services/`        |
| `sector`     | Sectors      | `/sectors/{slug}/`          | Page at `/sectors/`              |
| `case_study` | Case Studies | `/case-studies/{slug}/`     | Page at `/case-studies/`         |
| `vacancy`    | Vacancies    | `/careers/vacancies/{slug}/` | Page at `/careers/vacancies/`   |

`case_study` and `vacancy` both use dedicated single templates
(`single-case_study.php`, `single-vacancy.php`) rather than page.php, because both
have a fixed shape driven by ACF fields rather than blocks.

Hubs are ordinary WP Pages carrying a CB Service Cards block, so the intro copy,
CTAs and section order stay editable and each hub keeps its own Yoast fields.
Listing order follows the Page Attributes "Order" field, then title.

`vacancy` is the exception: it renders through `single-vacancy.php`, not page.php.
Vacancies have a fixed structure that HR populate through ACF fields rather than
blocks, which keeps every role consistent and guarantees the JobPosting JSON-LD
in that template has the data it needs. Fields and schema are ported from
`cb-turnpower2025` (sister company) with field names kept identical.

`application` and `product` are commented out — not part of this site.
`inc/cb-taxonomies.php` is still disabled and its `application_cat` taxonomy
references the old `project` post type; revisit or delete when convenient.

## News

News uses the built-in `post` type, not a CPT. Permalink structure is
`/news/%postname%/`; every custom post type is registered `with_front => false`
so none of them pick up the `/news/` prefix.

- `/news/` is a WP Page set as **Posts page** in Reading settings, so WordPress
  hands it to `index.php` and ignores the page's content. `index.php` renders
  that content itself: the News page is built from blocks like any other hub
  (CB Hero, CB Intro, CB News Index, CB CTA) and edited on the page.
- Filter chips are built from categories in use, each with its count, and
  filter client-side (all posts are already in the DOM). Categories: Contract
  awards, Company news, Community, Industry, Group company news.
- `single.php` is described under **News** in the phase two notes.

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

### Escaping rendered output

Never wrap rendered block output in `wp_kses_post()`. It strips `<script>` and
`<iframe>` tags but **keeps their contents**, so block JS gets dumped onto the page
as visible text and embeds silently vanish. Several blocks emit inline scripts
(the parallax in CB Image Text Checklist, the filters in CB FAQs and CB Vacancy
Index), so this bites the moment a template renders blocks.

- Post/blocks content: `the_content()`, or `echo apply_filters( 'the_content', $raw )`
  with a phpcs ignore. Never `get_the_content()` raw - blocks will not render.
- oEmbed markup: `wp_kses()` with an explicit iframe allowlist (see CB Video).
- Plain ACF text/wysiwyg fields: `wp_kses_post()` is correct.

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

Global template: `header.php`. Navigation markup: `inc/cb-nav.php`.
Styles: `src/sass/theme/_header.scss`.

Phase two (November 2026) replaced the transparent-over-hero header with the
redesign's solid white bar: 90px tall on desktop, 72px below 1200px, dark logo,
ink links. It is still `position: fixed`, so the page still starts underneath
it. `.scrolled` now only adds a hairline and a soft shadow.

A page no longer needs to open on a dark hero for the header to be legible.

#### Primary navigation

The menu is still edited in Appearance > Menus ("Primary Nav" location), two
levels deep. `cb_render_primary_nav()` reads it with `wp_get_nav_menu_items()`
and builds the markup itself - the Understrap Bootstrap walker is no longer
used, because each dropdown needs its own layout.

A top-level item **with children** renders as a `<button>` that opens a panel,
not as a link. The panel's heading carries the link to the parent page instead.
A top-level item without children is a plain link.

The panel style comes from a CSS class on the top-level menu item (Screen
Options > CSS Classes if the field is hidden):

| Class | Panel | Used for |
| --- | --- | --- |
| `nav-split` | Green link list plus a white "Case study examples" column | Services |
| `nav-bar` | Full-width green bar of links, flush under the header | Sectors and Coverage |
| `nav-dark` | Dark green panel. The default when no class is set | About |
| `nav-mint` | Mint panel | Contact |
| `button` | Not a panel: the item becomes the mint CTA pill | Ask the experts |

The panel heading is the menu item's **Title Attribute** when one is set, and
its navigation label otherwise. That is how "Services" opens a panel headed
"By service" and "Contact" one headed "Contact us".

`setup-phase-two-menu.php` in the theme root rebuilds the menu to the sitemap
with those classes and headings. Menus are database content, so it has to be
run once per environment:

    wp eval-file wp-content/themes/cb-andwislifts2026/setup-phase-two-menu.php
    wp eval-file wp-content/themes/cb-andwislifts2026/setup-phase-two-menu.php apply

The first only prints the plan. `apply` replaces every item in the menu. It
skips pages that are not published and lists them.

#### Case study examples (Services panel)

Each service link in a `nav-split` panel shows up to two case studies whose
`related_services` field includes that service; hovering or focusing a link
swaps them. A service with none, and any link that is not a service, shows the
two most recent case studies under "Recent projects". The supporting line under
each title is `client_name`, falling back to `card_summary` cut to one line.

The lookup is one meta query per service, so `cb_nav_case_study_map()` caches it
in the `cb_nav_case_studies_v1` transient for 12 hours and clears it whenever a
case study or service is saved, trashed or deleted.

#### Behaviour

The script is printed on `wp_footer` by `cb_nav_footer_script()`, like the
blocks' scripts, so the nav does not depend on the JS build.

- Devices that can hover open a panel on hover. A mouse click keeps it open
  rather than toggling it shut.
- Touch and keyboard toggle with the button. Escape closes the panel and
  returns focus to its button. Focus leaving a panel, or a click outside the
  nav, closes it.
- The script keeps each panel inside the page container and lines a bar's links
  up under the item that opened it (`--cb-nav-bar-offset`).
- Below 1200px the same markup is an accordion inside the Bootstrap collapse.
  The case study column is hidden there.

Each nav item is stretched to the full height of the header, and the gap above
a floating panel is padding on the panel wrapper. Both are deliberate: the
pointer must not leave the item on its way down to the panel, or it would close.

Sizes in `_header.scss` are px, not rem. The root font-size is 22px and the
header is drawn to fixed pixel sizes.

### Footer

Global template: `footer.php`. Data and behaviour: `inc/cb-footer.php`.
Styles: `src/sass/theme/_footer.scss`.

Rebuilt for phase two (November 2026). Dark green, with:

- **Brand block**: logo, strapline, short paragraph and a link to the group
  site. The three pieces of copy are on Site-Wide Settings > Footer and fall
  back to the design's wording when left empty.
- **Three link columns**: one per footer menu location ("Footer column 1", 2
  and 3 in Appearance > Menus). A column's heading is the **name of the menu**
  assigned to it, so the menus are called "About andwis lifts", "Services" and
  "Sectors". Columns are flat lists; nested items are ignored. The first
  column is set semibold, as in the design.
- **Social icons**: from the Social group on Site-Wide Settings, as white
  discs. LinkedIn has its own glyph here (`cb_footer_social_icon()`): the shared
  icon is the rounded-square logo, which on a disc reads as a dark box.
- **Contact us** and the **legal links** (see "Footer legal disclosure").
- **Back to top**: a link to `#top`, upgraded by script to a smooth scroll
  through Lenis that also moves keyboard focus to the header logo.
- **Copyright and statutory details**, small and centred at the very bottom.
  The design does not draw these. They stay because the law requires them.

Below 992px everything stacks and centres, and the three columns become an
accordion. The lists only collapse once the script has added `site-footer--js`,
so without JavaScript they stay open. Each column has both an `<h2>` and a
`<button>` carrying the heading; CSS shows one or the other, so only one is
exposed at a time.

`setup-phase-two-menu.php footer apply` creates the three menus and assigns
them (see Header > Primary navigation for how to run it).

#### The curve

The footer's top edge is concave: the section above dips into the footer. In
phase one it was the other way up, with the footer rising into the page.

`.site-footer::before` draws the footer's own colour in the two corners under
that curve, sitting on the last `--cb-footer-curve` of whatever the page ends
with. The token is 80px deep at the 1440px design width and lives in
`_tokens.scss`, because sections that leave room for the curve need it too.

The footer keeps a top margin a little larger than the curve, so on a page that
ends in ordinary content the corners land on white space, not on text. A page
that ends with a **mint CB CTA** is the designed case: `main:has(> .cb-cta--mint:last-child)`
removes that margin and the band adds the curve's depth to its own bottom
padding, so the two join with the curve drawn across the mint.

Sizes are px, not rem, as in the header.

## ACF Block Components

### CB Hero

Reworked for phase two. A photo with a green wash down the left for the copy,
in place of the old all-over darkening.

Fields: Background image, Heading, Kicker (optional small line above the
heading; this is the old "subline" field, which used to sit below it), Intro,
Button (mint), Second button (outline), Show wave, Stats.

**Stats** is an optional repeater of up to four figures. When it has rows the
hero ends in a dark band of figures whose top edge domes up into the photo, and
the white curve is dropped - the band replaces it. A value such as "Top 5" is
drawn as a small word and a large figure.

The header is fixed, so the hero's top padding starts from the header's height
(90px, or 72px below 1200px). Heading and intro sizes are px, drawn to the
design: 56px and 24px at full size.

The parallax on the background image is unchanged.

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
- Card source (Manual / All services / Selected services / Services linked to this page / All sectors / Selected sectors)
- Choose posts (relationship, shown for the "selected" sources)
- Maximum cards (shown for all CPT sources)
- Media style (Icon / Top / Beside / Rollover)
- Cards repeater (shown for Manual only):
  - Icon / image
  - Title
  - Description
  - Optional link

Media style decides how the card's image field is treated. **Icon** is the
original behaviour and the default, so blocks built before the option existed
are unchanged: a 2.5rem `object-fit: contain` mark above the title, served at
`thumbnail`. **Top**, **Beside** and **Rollover** treat the same field as a
photograph and serve `medium_large` with `object-fit: cover`.

Rollover fades the photograph up behind the copy on hover, under a gradient that
keeps the text legible. It only applies inside `@media (hover: hover) and
(pointer: fine)` - a touch screen never fires hover, so the image would never be
seen there. Below that it keeps the Top treatment, which is why the two share
the same banded rules.

Feeding a photograph into the Icon style is what makes it look wrong: a 150px
crop squeezed into a 2.5rem contain box. Match the style to the asset.

CPT sources map each post to a card via `cb_get_cpt_cards()` in `inc/cb-posttypes.php`:
post title → card title, `card_summary` → description, `card_icon` → icon, permalink → link.
Where a post has no `card_icon`, its featured image is used instead, so the photo
styles have something to show.
"Services linked to this page" reads the `related_services` field on the current post.

Layout is four-up (`col-lg-3 col-md-6`). Exactly five cards switch to the shared
`cb-col-lg-5` 20% helper so they fill one row instead of orphaning a fifth card
below - the same exception CB Stats uses. The row is `justify-content-center` so
any other remainder centres rather than hanging left.

Card copy should be one short sentence, matching the homepage rhythm. Longer
per-service copy belongs in the service page body, not `card_summary`.

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

This is the block for a row of accreditation or certification logos. Leave the
Image empty and it drops the background, curve and two-column split and renders
as a centred `col-lg-8` column - heading, body, logo row - which is what suits a
short accreditations section sitting under another block.

Each logo cell falls back to its label when no image is set, and only an empty
cell carries the dashed outline (`&__logo--placeholder`). So a row can be built
with labelled placeholders and have real logos dropped in later without the
slots still reading as gaps.

### CB Gallery

A set of photographs, as a carousel or a grid.

Fields:

- Heading, Intro (both optional)
- Images (gallery, required)
- Layout (Carousel / Grid)
- Images across (2 / 3 / 4)
- Show captions (uses each image's media library caption)

Renders nothing with no images. A carousel with no more images than it shows at
once falls back to a grid - there is nothing to move through, and Swiper's loop
misbehaves with fewer slides than slidesPerView.

The carousel is Swiper, which `inc/cb-theme.php` already enqueues site-wide, so
it adds no new dependency. The init runs on `wp_footer` like the theme's other
block scripts, and bails to a horizontally scrolling row if Swiper is absent.

Two traps worth knowing, both hit while building this:

**Auto-height slides.** Swiper's CSS gives `.swiper-wrapper` and
`.swiper-slide` `height: 100%`. Against a container sized by its content that is
circular: the wrapper collapsed and the figures overflowed out of the flow, so
slides rendered blank. Both need `height: auto`.

**`aria-live` and the carousel.** Swiper's a11y module marks `.swiper-wrapper`
`aria-live="polite"`. `_a11y.scss` used to hide every `[aria-live]` element
10000px off screen, which hid the whole carousel - and the visible filter counts
on CB Downloads and CB Offices with it. That rule is now the opt-in `.cb-sr-live`
class. Do not reintroduce a blanket attribute selector: a live region is not
inherently invisible.

### CB Image Row

Images side by side. Built for a pair of accreditation logos sitting under
another section.

Fields:

- Images (gallery)
- Shape (Natural / Circle)
- Size (Small / Medium / Large)
- Alignment (Left / Centre / Right)

Renders nothing with no images.

Size is one control read differently by each shape, via the
`--cb-image-row-height` / `--cb-image-row-diameter` pair set on the size
modifier.

**Natural** fits each image to a `max-height` (4 / 6 / 9rem) rather than a
width. Logos arrive at wildly different aspect ratios, and matching their
heights is what makes a row of them look level - constraining width instead
leaves a tall badge towering over a wide wordmark.

**Circle** uses the size as a diameter (11 / 15 / 19rem) and applies the same
treatment as the circular imagery elsewhere on the site: `border-radius: 50%`,
`aspect-ratio: 1 / 1`, `object-fit: cover` and `--cb-shadow-soft`. Width is
`min(diameter, 38vw)` so a pair stays side by side on a narrow screen instead of
a fixed diameter forcing a wrap, rising to `62vw` below 576px. Circles request a
`large` source rather than `medium_large`, since they render bigger and crop
from the centre.

Two sit side by side; more wrap.

Vertical padding is half a normal section, since it is meant to sit beneath
another block rather than stand on its own.

For a logo row that also needs a heading, body copy, or labelled placeholders
where logos are still missing, use CB Compliance with its Image left empty.

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

### Related case studies on sector pages

Sector pages are built from blocks stored in post content, so this section is
injected at render time by `inc/cb-case-studies.php` rather than written into
every sector page. It renders immediately before the band each sector page
ends on - the mint CB CTA in phase two, the CB Form block before it - and falls
back to appending after the content on a sector page with neither.

Case studies are matched through the **Sectors** relationship field on the case
study (`related_sectors`). A sector with no tagged case studies renders nothing
rather than an empty band. A sector page that already contains a CB Case Study
Index block placed by hand is skipped, so the manual block wins.

Card markup is shared - `cb_case_study_card()` is used by this section, the CB
Case Study Index block, and the "More case studies" band on a single case study.

Both injection filters bail unless `in_the_loop()`. SEO plugins build the meta
description by calling `get_the_excerpt()`, which applies `the_content` outside
the loop; without that guard the section leaks into the description tag and the
real render is skipped.

### CB CTA

A closing call to action for pages that should end on a prompt rather than a
full contact form.

Fields:

- Heading (required)
- Body (optional, one short line)
- Button
- Secondary button (optional, renders as `.btn-ghost`)
- Third button (optional, also `.btn-ghost`)
- Background: Forest, Sage, Paper or Mint
- Layout: Split (copy left, buttons right) or Centred

The block renders nothing without a heading or a button link.

A bare button between two paper sections reads as leftover markup rather than an
invitation, so the button always arrives with a heading and its own banded
background. Forest is the default because it breaks the run of white around it.

Note `.btn-ghost` in `_buttons.scss`. Until it existed the theme had a single
button style, so a supporting action rendered identically to the main one and
the two competed. It keeps the pill shape and sizing, drops the fill, and takes
its colour from `--cb-ghost-ink`, which the forest variant flips to paper.

**Mint** is the phase two closing band: the last block on a page, running
straight into the footer (see Footer > The curve). It is always centred - the
Layout field is ignored - with a large heading and 64px tall buttons. The
primary button is dark there, because the usual mint fill would vanish, and its
hover sweep runs to white. A single button is drawn 320px wide; two or three
share the row.

### CB Cross Sell

The andwis Group cross-sell from the phase two design: copy on the left, a
two-step card on the right. Markup and script are in `inc/cb-cross-sell.php`,
so the block template is only a list of fields and templates can call
`cb_cross_sell_render()` directly.

Where it sits, per the designer's notes:

- **Our Story** (About page): dark, above the closing section.
- **Contact**: light, the last thing on the page.
- **Every news article**: dark, output by `single.php` after the related news.

How it behaves:

1. **Choose.** A checklist of the group's divisions. This division is ticked,
   fixed and marked "You're here"; it does not count. The button is disabled
   until another service is ticked, with "Tick at least one to continue" beside
   it while it is. The whole row is the label, so the whole row ticks the box.
2. **Summary.** "You've identified N areas of interest" (singular for one), a
   pill per ticked service in checklist order, and a paragraph naming them as
   "A", "A and B" or "A, B and C". "Change my answers" goes back with the ticks
   kept.

Nothing is submitted and the page does not reload. Focus moves into the new
step each time, so the change is announced and Tab carries on from there.

Block fields (what changes per placement): Heading, Text, Small line, Group
link, Card question, Card button, Background (dark or light green).

Site-Wide Settings > Cross-sell (the same everywhere): this division, the other
divisions one per line, summary heading, summary text with a `{services}`
placeholder, benefits one per line, final button, and three optional extras -
customer quote with attribution, stat line, text link. The design marks those
three as placeholders, so each stays hidden until it is filled in. Every field
falls back to the design's wording when empty.

#### Passing the ticks on

The designer's brief: "Pass the selected services data through to the CTA
destination so the agent knows what the visitor ticked."

The summary's button goes to the Contact page by default, with the ticked
services added as `?interested_services=Fire & Security, M&E`
(`CB_CROSS_SELL_PARAM`). The **General enquiry** Gravity Form has a hidden field,
"Services of interest", with "Allow field to be populated dynamically" on and
the parameter name `interested_services`, so the value arrives with the entry
and in the notification email. Point the final button at a page with a
different form and that form needs the same hidden field.

The same list is pushed to `dataLayer` for Tag Manager: `cross_sell_summary`
when the summary is shown and `cross_sell_contact` when the button is followed,
each with `cross_sell_services` and `cross_sell_count`.

Step one is a real form whose action is the final destination. Without
JavaScript the button is enabled and takes the ticks straight there.

### CB Sectors

Source HTML: `.sectors`

Fields:

- Heading
- Body copy
- Sectors (repeater)
  - Label
  - Sector page (optional post object, `sector` CPT)

The labels render as a tall column of type beside the copy. A second copy of the
list sits on top of the first, tinted mint and masked to a radial gradient that
follows the cursor, which produces the spotlight. The list repeats itself until
it is at least ten items long so the column fills the panel.

Both layers must render identical markup, or the highlight slides out of
register with the text beneath it. The overlay is `pointer-events: none`, so the
base layer takes clicks; the overlay is `aria-hidden` and the repeated padding
items are kept out of the tab order.

Labels link to their sector page. The Sector page field wins when set; otherwise
`cb_resolve_sector_link()` matches the label to a published `sector` post by
normalised title or slug, including a word-boundary prefix match in either
direction, so "Retail & Leisure" finds "Retail" and "Industrial" finds
"Industrial and logistics". A label with no match renders as plain text rather
than a dead link - set the Sector page field to link those.

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

The card is a flex column with `__value` on `margin-top: auto`, so the email is
pinned to the bottom. The cards sit in one row at equal height, but a
description that wraps to two lines used to push its email down a line and break
the alignment across the row. Pinning holds them level whatever the copy length.

Horizontal padding is 16px rather than the 26px it started at. At 26px the
longest description missed fitting on one line by a single pixel; 16px buys
enough room on a wide desktop, which is where it is reviewed. It does not fit
between 1200 and 1399px, where the column is 273px - the description wraps there
and the pinned value keeps the row aligned anyway.

### CB Emergency

High-contrast 24/7 callout banner. The number comes from `contact_phone` in
Site-Wide Settings, with a per-block override. With no number configured it says
so rather than rendering an empty banner or a dead `tel:` link.

Fields: Heading, Intro, Phone override, Note.

The number renders as `.btn.btn-primary` with a phone icon, not a text link, at
`--fs-h5` rather than normal button text - it is the primary action on the
banner and someone reading it is usually mid-breakdown. Its hover fill is
overridden to paper, because `.btn-primary` slides a forest fill in and the
banner panel is itself forest, so the default would dissolve the button into its
own background.

### CB News Index

Reworked in phase two: see **News** under the phase two notes.

### CB Case Study Index

Reworked in phase two: see **Case studies** under the phase two notes.

### CB Downloads

Document library for `/resources/downloads/`. A **flat** repeater that the template
groups by each row's Group field, in the order groups first appear - flat rather
than nested repeaters so an editor can regroup or reorder a document without
rebuilding the structure.

Fields:

- Heading, Intro
- Show search and filters (default on)
- Documents repeater: document, group, file, version, review date
- Note (basic HTML allowed)

Rows with no file still render, marked "Available on request", so the page reads
correctly while certificates are still being supplied.

With filters on, the block adds a search box, a Topic select built from the
groups in use, and a File type select built from the extensions in use.
Filtering is client-side - every document is already in the DOM - so it is
instant and the page still works with no JavaScript.

A select only appears when it has more than one option to offer, so the File
type filter stays hidden until documents of a second type are uploaded. Item
text is cached on load rather than read from the DOM on every keystroke, and
input is debounced.

A group whose documents are all filtered out is hidden too - a heading with
nothing under it reads as a fault. The result count is a `role="status"` live
region, so a screen reader hears the list change.

File type is taken from the filename extension rather than the mime type, since
that is what a reader recognises, and drives both the badge on the row and the
filter.

### CB FAQs

Bootstrap accordion with optional topic filtering. Block markup is ported from
`cb-turnpower2025`, with a `category` sub-field and filter chips added (their
version had no filtering, which the brief's `/faqs/` page calls for).

Fields:

- Heading
- Intro text
- Show topic filter (needs two or more distinct categories to appear)
- Questions repeater: question, category, answer, call to action

The call to action is an optional ACF link appended to the end of that answer -
a route on to the relevant form or page. Empty adds nothing, and a link with no
title falls back to "Find out more". It renders as a link with a trailing arrow
rather than a button, so it sits inside an answer without competing with the
page's own CTAs.

It is deliberately outside the answer text passed to `cb_collect_faq()`: a link
label is an affordance, not part of the answer, and it should not end up in the
FAQPage schema.

Every question calls `cb_collect_faq()`. `inc/cb-faq-schema.php` then emits **one**
aggregated `FAQPage` JSON-LD per page on `wp_footer`, deduped by question hash -
multiple FAQPage blocks on a single URL would be invalid, so never output the
schema from the block itself.

Note: since 2023 Google only surfaces FAQ rich results for authoritative
government and health sites, so this will not produce rich snippets for andwis.
It stays worthwhile for other schema consumers, and costs nothing.

### CB Video

New - neither this theme nor `cb-turnpower2025` had a reusable video block
(Turnpower's home hero hardcodes a `<video>` src). Takes an oEmbed URL or an
uploaded MP4 and renders it in a 16:9 frame. With neither set it falls back to
the poster image, so a section reads properly while the video is still to come.

Fields:

- Heading
- Intro text
- Video URL (YouTube / Vimeo, takes priority)
- Video file (MP4 upload)
- Poster image
- Caption

### CB Vacancy Index

Ported from `cb-turnpower2025`'s careers index. Lists published vacancies as cards
with employment type, tenure and location badges, salary and a teaser. Roles whose
`valid_through` date has passed drop out of the listing automatically.

Fields:

- Heading
- No vacancies message

### CB Form

Renders a Gravity Form inside the standard `<section>` / `.container` wrapper, so
forms pick up the page width and vertical rhythm. The Gravity Forms core block is
not used - it renders outside the theme's container.

Fields:

- Heading
- Intro text
- Form (select, populated from the active Gravity Forms by
  `cb_form_id_choices()` in `inc/cb-blocks.php` rather than hardcoded IDs)

Forms in use: 1 General enquiry, 2 Get a quote, 3 Ask the expert,
4 Careers application.

Gravity Forms theming lives in `src/sass/theme/blocks/_cb_form.scss`, scoped to a
selector list (`.cb-form .gform_wrapper, .cb-vacancy .gform_wrapper`) rather than
bare `.gform_wrapper`, so forms embedded outside the theme's own surfaces keep GF
defaults. **Add new form scopes to that selector list**, don't duplicate the block -
GF's orbital theme wins on specificity, so its `--gf-ctrl-btn-*` custom properties
are the only reliable way to colour the submit button.

### CB Offices

Regional offices for the contact page. Each office is an image beside a forest
panel carrying the address, the main number and the named contacts for that
region.

Fields:

- Heading, Intro
- Show search and region filter (default on)
- Offices repeater:
  - Image, Office name, Region
  - Locations repeater: label, phone, address
  - Address / Main phone (single site, superseded by Locations)
  - Contacts repeater: role, name, email, phone

A region often has more than one site - a service office and a projects office,
say - each with its own address and number. Locations is a repeater inside the
office for exactly that, with an optional label per row. Before it existed the
only way to show two sites was to run both addresses into the one Address field,
which left them sharing a single phone number.

Where Locations is empty the office falls back to the single Address and Main
phone, so rows entered before the repeater existed still render. Those two
fields are kept only for that; new entries should use Locations.

Offices are a repeater and contacts a repeater within them, so an editor
duplicates a row to add a region rather than the structure having to change.
Renders nothing with no offices, and an office with no name is skipped.

Region drives the select and falls back to the office name when empty, so the
filter works before anyone has thought about regions as a separate idea. Search
matches the whole panel - name, address, postcode and contacts - because that is
what people type. Filtering is client-side and shares its markup and behaviour
with CB Downloads, so the two filtered listings on the site work the same way.

Addresses are entered a line per line and rendered with `nl2br`. Phone numbers
and emails are real `tel:` and `mailto:` links, which the source site did not
have. Emails set `overflow-wrap: anywhere` - a long address would otherwise push
the panel wider than its column.

### CB Pill Strip

Source HTML: `.legacy-strip`

Fields:

- Label
- Layout (Pills / Columns)
- Pills repeater:
  - Text

**Pills** is the original layout and the default: short labels as dashed
capsules, centred and wrapping. It only works while the labels are short - the
homepage's "Formerly known as" row is what it was built for.

**Columns** renders the same rows as a two-column list. Fourteen full sentences
as centred wrapping capsules produce a ragged block with no alignment to read
against, which is what the accreditations page had.

The list uses CSS `columns`, so it fills down the first column then across, and
a list ordered by reference number still reads in order. `break-inside: avoid`
keeps a row from splitting across the break, and it drops to one column below
768px.

Rows written as "EN81-21 - New lifts in existing buildings" are split on the
first " - " and the reference is set on its own line in forest semibold. The
split only applies when the leading segment is 32 characters or fewer, so a row
that merely contains a hyphen renders whole.

## Phase two home page

The Home page is these blocks, in order. Each is reusable on other pages.

| Block | What it is |
| --- | --- |
| CB Hero | With the Stats band. |
| CB Intro | Centred heading, text and outline button. White or pale grey. |
| CB Statement | A short statement inside a ring, with the lift motif turning on its edge, and a paragraph beside it. |
| CB Service Floors | The services as numbered floors with a card for the selected one. |
| CB Text Media | Heading, text and button beside an unframed image - here the coverage map. |
| CB Carousel (Sectors) | Tall photo cards that run off both edges of the page. |
| CB Reasons | Four reasons in a fixed arrangement on pale green. |
| CB Case Study Showcase | Photo columns, one open at a time. |
| CB Carousel (Latest news) | Three article cards. Renders nothing until there is a published post. |
| CB CTA (Mint) | The closing band that joins the footer. |

Headings in CB Intro, CB Text Media, CB Service Floors, CB Carousel, CB Reasons
and CB Case Study Showcase accept `*asterisks*` around a phrase to turn it
green (`cb_highlight()`), for the two-tone headings in the design.

**CB Service Floors.** Lists the service posts in menu order, numbered
downwards so the first is the top floor. Each card's text is the service's card
summary and its photo is the new **Photo** field on the service (falling back
to the featured image; with neither, the card is text and button only). The
designer's note: "As you scroll, this section switches up or down, dependent on
the direction of travel, to navigate through all services as if you were
passing each floor. You can also click independently." So the script divides
the section's journey through the window into one band per floor and selects
the matching tab. A click or arrow key takes over until the section has left
the window. With reduced motion set, scrolling does not change the selection.
Without JavaScript every card is shown.

**CB Carousel.** Uses the Swiper already loaded site-wide. Sectors come from
the sector posts (featured image, title, card summary); news from the latest
posts. The designer's note for sectors: "Desktop users can grab and drag the
cards horizontally and mobile users can press and drag. Or the users can
utilise the radio buttons below." Without JavaScript the row scrolls sideways.

**CB Reasons.** Position decides the style: 1 large photo card, 2 dark card, 3
small photo card, 4 wide pale card. Only 1 and 3 use their photo.

**CB Case Study Showcase.** The latest case studies that have a featured image.
Hover or focus opens a column; only `flex-grow` changes, which is what lets the
widths animate. Below 992px it is a row to swipe, every card open. Tags are the
case study's related sectors and services.

## Phase two service pages

The Services hub, a service page and the Types of lifts page are built from
blocks the Home page already uses, plus three extensions.

| Page | Blocks, in order |
| --- | --- |
| Services hub | CB Hero, CB Intro (breadcrumb), CB Service Cards (Photo, all services, promo card), CB CTA (Mint) |
| Service page | CB Hero, CB Intro (breadcrumb), CB Text Media (photo, pale grey), CB Service Cards (Centred mark) for the contract options, CB Intro (pale green), CB Text Media (photo), CB Text Media (no image), CB CTA (Mint) |
| Types of lifts | CB Hero, CB Intro (breadcrumb), then CB Text Media (photo on the left) and CB Service Cards (Photo, three manual cards) alternating, CB CTA (Mint) |

Lift maintenance follows the design's service template exactly. Installation,
modernisation and repairs were moved into the same blocks section by section,
keeping whatever sections each already had: the hero's paragraph became the
intro, each image-and-text section became a CB Text Media (photo) on
alternating pale grey and white, card rows became CB Service Cards, and the
closing enquiry form became the mint CB CTA. Pills, compliance logos and
downloads were left as they were.

**CB Service Cards** gained two styles and a promo card.

- *Photo, no card*: a rounded photo, title, line and outline button. For the
  automatic sources the photo is the service's Photo field, then its featured
  image. Manual cards use their image field.
- *Centred mark, no card*: for the contract options. The title is split after
  its first word, so "Essential Care+" is drawn as a large "Essential" over a
  smaller "Care+", with the plus sign in green (`cb_split_title()`).
- *Promo card*: three optional fields that add a pale green prompt after the
  last card, as tall as a photo so it sits level with the row.

Straight after a CB Intro, either style closes up under it and drops its own
top padding. In both styles the button is pushed to the bottom of its card, so
the buttons in a row line up however long each description is.

**CB Intro** gained Show breadcrumb (Yoast's trail, small and left aligned,
for the first section after the hero), a Pale green background and an optional
row of Logos under the text. Each logo row is an image and a name. A row with a
name and no image is drawn as a dashed placeholder, the same convention CB
Compliance uses, so a missing logo reads as a gap to fill.

**CB Text Media** gained a Subheading, an Image style (whole and unframed, or a
cropped and rounded photo), a Pale grey background and an optional highlighted
box. With no image it becomes two columns of text: heading left and copy right,
or copy left and the box right when the box is filled in.

### Sectors and Coverage

The Sectors hub (still at `/sectors/`, retitled "Sectors and Coverage") is: CB
Hero, CB Intro (breadcrumb), CB Service Cards (all sectors, promo card), CB
Intro (pale grey), CB Office Finder (postcode search, the coverage map and the
"Multi site operator?" box), CB Offices (plain list), CB CTA (Mint).

**CB Service Cards** has a third phase two style for it, *Photo, name and
button only*: the photo style without the description, the name and the button
side by side under the picture.

**CB Offices** has a "Use the site's offices" switch. The list of offices is
entered once, in Site-wide Settings > Offices; a block with the switch on shows
that list instead of holding a second copy that would drift. Only the office
rows are shared: the layout, heading and intro are each block's own
(`cb_offices_shared()` in `inc/cb-office-finder.php`).

It also has a **Layout** choice. *Cards with photos* is the phase one look,
still used on Contact. *Plain list in three columns* is the phase two one used
here: a centred intro paragraph, then each office as a name, its addresses and
phone numbers, with an optional call to action ("Multi site operator?" and a
button) as the cell after the last office. Two columns below 992px, one below
576px.

- One entry per office, not per address. An office with two addresses lists
  both under its name.
- A location's label is shown in brackets only where an office's locations
  have different labels (London and Home Counties), because that is the only
  time it tells two addresses apart.
- An email line appears if the office has a contact with an email address.
  None has one at present.
- The address is printed as entered. The design's "Postcode:" prefix is left
  off, since the postcode is already the last line.

**CB Office Finder** (`blocks/cb-office-finder.php`, `inc/cb-office-finder.php`)
is the postcode search. A visitor enters a postcode, or just its first half
("LS1"), and gets the three nearest offices, closest first, each with its
distance in miles, address, phone number and a "Get in touch" button.

- **Nearest three, not a radius.** There are ten addresses across seven
  offices, so any radius would leave some visitors with nothing. To add a
  cut-off later, filter on `miles` in `cb_office_finder_nearest()`; the number
  returned is `CB_OFFICE_FINDER_LIMIT`.
- **Offices come from Site-wide Settings > Offices**, the one list the site
  keeps. Each address must end with its postcode; that is how an office
  gets its place on the map. Add or move an office there and the finder
  follows, with nothing to update here.
- **Postcodes are placed with postcodes.io**: free, open UK data, no account or
  key. Retired postcodes are still found. The request is made by the site, not
  the visitor's browser, through `GET /wp-json/cb/v1/nearest-offices?postcode=`,
  so the visitor's postcode and IP address never go to a third party from their
  device. A full address service such as Loqate is not needed.
- **Caching.** Each postcode's location is kept for 30 days in a transient
  (`cb_pc_` plus a hash); an unknown postcode is remembered for an hour.
- **Distance is a straight line**, not a drive time.
- The map image is a picture and does not react to the search.
- The script is printed on `wp_footer`, only on pages that use the block.
  Without JavaScript the form goes to the Contact page, which lists every
  office.

**The eight individual sector pages** are not drawn in the design. They were
brought into the same pattern as a service page: CB Hero ("Sectors" kicker,
two buttons), CB Intro (breadcrumb, the old hero intro), CB Text Media
(Photo, rounded) for each section, alternating pale grey and white, CB Service
Cards (Photo, no card) for the linked services, the related case studies
(added automatically, on pale grey), and CB CTA (Mint) in place of the enquiry
form. Their phase one content is in `_cb_phase_one_content` and in each page's
revisions. On High sensitivity sites the hand-written "Environments we work
in" cards use the Centred mark style.

### Case studies

**The hub** (`/case-studies/`) is: CB Hero (kicker, two buttons), CB Intro, CB
Case Study Index, CB CTA (Mint).

**CB Case Study Index**, with no limit set, is the full listing. A "Browse by"
switch chooses between Sector and Service; under it is a row of chips for
whichever is chosen, each with its count, and a line saying how many are
showing. The chips come from the Sectors and Services picked on each case
study, so an unused one is never offered, and a way of browsing with fewer than
two choices is left out. Filtering happens in the browser. The cards fade out,
change, then fade back in with a slight rise (about half a second in all);
with reduced motion set they just change. The design draws
every chip in the same pale green; here the chosen one turns dark so it is
clear what is selected, and the chips are a little larger than drawn (32px
tall, 12px type, against 26px and 10px) so they can be tapped and read.

**The card** (`cb_case_study_card()` in `inc/cb-case-studies.php`) is used by
the index, "More case studies" and the related list on sector pages: photo,
title, optional green kicker, teaser, then tags - sectors filled, services
outlined with the leading "Lift" dropped. The whole card is one link. The
teaser is the Card summary, or the start of the first section where that is
empty.

**A single case study** (`single-case_study.php`) has a fixed frame around a
free middle:

1. Green hero: tags, kicker, title, and an intro beside the title when one is
   entered.
2. The pictures across the hero's bottom edge. The featured image comes first;
   add to **More images** and they become a carousel with dots.
3. The story: the post's own blocks, normally CB Text Media set to *Photo,
   rounded*, alternating white and pale grey. Each can have a **Kicker** (the
   small green line, "The challenge"), a heading, text and a picture.
4. An optional quote and who said it.
5. More case studies: three, from the same sector first.
6. The mint closing band, the same on every case study. It replaced the
   enquiry form that used to end the page.

Case study fields: Card summary, Kicker (was "Client or project name"), Intro,
More images, Quote, Quote by, Services, Sectors. The Challenge, What we did and
Result boxes were removed: nothing used them, and sections do that job.

**CB Text Media** gained the Kicker, and with no image, heading or highlighted
box it is now a single column of text.

The existing twenty were converted from CB Image Text Checklist sections to CB
Text Media ones: checklists became bulleted lists in the text, a section whose
picture was the featured image lost it (it is at the top now), the second and
third pictures of a "layered" section went to More images, and the hand-placed
index at the end of two of them was removed. Their old content is in
`_cb_phase_one_content` and in each post's revisions.

### News

**The hub** (`/news/`) is the News page's blocks: CB Hero (kicker, two
buttons), CB Intro, CB News Index, CB CTA (Mint).

**CB News Index**, with no limit set, lists every article with a row of
category chips: outlined, filled once chosen, each with its count. The cards
fade as the case study ones do. With a limit it is a short row of the latest.
Its **Line underneath** field holds the media enquiries line; an email address
in it becomes a link. Fields: Heading, Intro, How many, View all link, Line
underneath.

**The card** (`cb_news_card()` in `inc/cb-news.php`) is the case study card
under its own name: photo, title, excerpt, category.

**An article** (`single.php`) has the same frame as a case study, and shares
its code (`inc/cb-story.php`):

1. Green hero: category, date, title, and beside the title the excerpt - only
   when someone has written one, since an automatic excerpt would repeat the
   opening of the article. The design shows no date; it was kept because a
   news article needs one.
2. The featured image across the hero's bottom edge; **More images** on the
   article make it a carousel.
3. The article. Paragraphs, headings and lists sit in a reading column down
   the left (798px). A picture on its own runs the full content width. CB Text
   Media and other sections run full width, with their headings set at
   sub-heading size. `cb_story_content()` sorts the blocks into these.
4. Related news: three, from the same category first.
5. The group cross-sell.
6. The mint "Have questions?" band.

The old article sidebar, the previous and next links and the `.cb-post-*`
styles are gone.

The theme wraps every core paragraph, heading and list in a `.container`
(`modify_core_add_container()` in `inc/cb-blocks.php`). Inside an article that
wrapper is flattened, or each paragraph would centre itself.

### Resources

The Resources page (`/resources/`) is: CB Hero (Light), CB Resources, CB CTA
(Mint).

**CB Hero** has a **Style** choice. *Light, no photo* is a pale band with dark
text and the same curved bottom edge: a page header for a page that gets
straight to its content.

**CB Resources** (`blocks/cb-resources.php`) is the body of the page: three
cards down the left and a column of call-to-action cards on the right, which
stays in view as the page scrolls. Its fields are in tabs.

- **Technical information.** Topics in columns; topics with the same Group
  share a column. A topic with a File is a download, with a Link it goes to a
  page, and with neither it opens in place to show its Description. The
  "Click to download" badge only appears once a topic has a file.
- **Product technical data.** One chip per product; choosing one shows that
  product's files as tiles. A tile with no file reads "On request". The design
  draws three product types (passenger, platform, service and goods) each with
  a CAD block, BIM object and datasheet; the files the site has are passenger
  lift drawings by load, so the chips are 630, 1000, 1275 and 1600 Kg for now.
- **Downloads and certificates.** Documents by Group. The first group runs
  the full width and shows a set number of rows (4) before "Show all"; the
  others sit two across. Each row shows the file type and when the file was
  last changed, or the row's Note instead ("£10m cover").
- **Side cards.** Heading, text, button, and a pale or bright style.
- **Line underneath.** An email address in it becomes a link.

Without JavaScript every product and every document is shown. The cards have
the anchors `#technical-information`, `#product-data` and `#downloads`. Type
is set slightly larger than drawn (15px body against 13px).

The older pages under it, `/resources/downloads/` and
`/resources/technical-information/`, were moved to the bin on 2026-11-10 once
their content was here. Both addresses redirect (301) to the matching card on
this page, `/resources/#downloads` and `/resources/#technical-information`.
The redirects are in the Redirection plugin (Tools > Redirection), not in the
theme.

### Our story and Careers

**Our story** (`/about/`) is: CB Hero (kicker, two buttons), CB Intro, CB Text
Media (large photo), CB Intro, CB Intro (dark green), CB Intro, CB Text Media
(large photo, on the left), CB Cross Sell, CB CTA (Mint). The copy is the
design's. The three values sections, the image row, the group cards and the
enquiry form that used to be on the page are not in the design and were
dropped; they are in the page's revisions.

**Careers** (`/careers/`, still a draft) is: CB Hero, CB Intro (left), CB Video
(green, with a card), CB Text Media (large photo), CB Intro (pale grey), CB CTA
(Mint, two buttons).

What the blocks gained for these:

- **CB Intro**: a *Dark green* background with white text, and an Alignment
  choice (centred or left; left also narrows the text).
- **CB Text Media**: a third image style, *Photo, large*. The picture takes
  the wider column and is cropped to stand as tall as the copy beside it. A
  Heading 3 in the text is a sub-heading.
- **Bold green in body copy**: in CB Intro, CB Text Media and the CB Video
  card, a phrase between *asterisks* is set in bold emerald
  (`cb_highlight_body()`), the same convention as in headings. Ordinary bold
  is left alone, so existing pages do not change.
- **CB Video**: a *Green, patterned* background; a white **card beside the
  video** for a few lines of text; and a **Videos** list, which shows two or
  more videos two across with a title and a line under each. A video with a
  poster image is not loaded until its play button is pressed. An address that
  cannot be played shows "Video to follow" in the video's place.

### Contact

The Contact page (`/contact/`) is: CB Hero (Light), CB Contact, CB Cross Sell.
There is no mint closing band: the pale green cross-sell runs into the footer.

**The footer's curve and the section above it.** The footer draws its curved
top edge over the bottom of whatever ends the page. A section with a colour of
its own must run straight into the footer, or the curve sits on a strip of
white. Two do: the mint CB CTA and CB Cross Sell. Each is named in the
`main:has(...) + .site-footer` rule in `_footer.scss`, which removes the
footer's top margin, and each adds `--cb-footer-curve` to its own bottom
padding when it is the last thing in `main`. A new coloured section that can
end a page needs both.

**CB Contact** (`blocks/cb-contact.php`) is the body of the page, on pale grey.
Its fields are in tabs.

- **Action cards.** Two large cards (dark green and bright green) pulled up
  over the hero's curve, each with a heading, a line and a button.
- **Other ways to reach us.** A row of chips. A chip with a Link goes there. A
  chip with Questions shows them underneath, one set at a time, and only one
  answer open at once. A chip with neither is not shown, and nor is one whose
  link is to a page still in draft, so chips can be entered ahead of their
  content.
- **Enquiry form.** Heading, intro, a Gravity Form and a line underneath. The
  form keeps its visible labels; the design draws placeholder-only fields,
  which are harder to use.
- **Offices.** The head office address, phone and email (from Site-wide
  Settings > General), a line of text, a "Find your nearest team" link, a
  highlighted box for opening hours, and every office from Site-wide Settings >
  Offices, each opening in place.

On this page the hero is white and the grey begins at its curve
(`.cb-hero--light:has(+ .cb-contact)`).

**Where the offices live.** The office list moved from the Contact page's CB
Offices block to **Site-wide Settings > Offices** (`cb_offices_shared()`). That
one list feeds the Contact page, the office list on Sectors and Coverage (CB
Offices with "Use the site's offices" on) and the postcode finder. Each address
must still end with its postcode. Notes above that say the offices are kept on
the Contact page predate this.

### Phone layouts

The design has 390px frames for Home, Sectors and Coverage, Resources and
Contact. What they ask for that the desktop design does not is in
`src/sass/theme/_phone.scss`, imported after the blocks, plus a few lines in
the blocks concerned:

- **Hero** (below 768px): the photo fills the top, the kicker, heading and
  intro sit along its bottom edge over a green wash, and the buttons drop
  below the photo on a band of their own, full width. The band is white, or
  green where the hero has figures under it. The figures are two by two,
  ruled into quarters. The light hero is unchanged.
- **Body copy is 16px** on a phone and still 24px at 1440px
  (`clamp(16px, 13px + 0.76vw, 24px)`, where it was 18px at the small end).
- **Buttons run the full width** below 576px in CB Intro, CB Text Media, CB
  Carousel, CB Case Study Showcase, CB News Index and the related lists.
- **CB Text Media**, when stacked: heading, then the picture, then the text
  and button.
- **CB Service Floors**, when stacked: each service opens in place under its
  own row, not below the whole list.
- **Home's section headings** (services, sectors, reasons, case studies) sit
  on the left; an opening CB Intro stays centred.
- **CB Offices, plain list** (below 576px): each office folds down to its name
  and opens when pressed.
- **CB Resources**: the first two cards start closed behind "Click to expand".
- **CB Contact**: the pills stack the full width, and the form, the offices,
  the opening hours and the office list are each a card of their own.

Not carried over from the phone frames: the case studies section on Home is
drawn as a swipe of white cards, and here keeps its photo columns; the sector
swipe shows a name only, and here keeps its line of text.

**CB Text Media** also gained a **Button style** (filled or outlined). "Find
your local team" on Home and Our story is outlined, as drawn.

### The designer's notes on Resources and Contact

Two frames carry written notes for the build, in pink beside the frame. They
ask for behaviour the pictures alone do not show.

**Resources.** The whole row of a document downloads it, and its accessible
name says what it is ("ISO 9001 (PDF, updated Sep 2026)"). Any group folds
after its first rows, with `aria-expanded` on the control. Every download is
pushed to the dataLayer as `resource_download` with `resource_title` and
`resource_type`. Counts in group titles are worked out, not typed. The side
cards stay in view. The notes describe the technical topics as articles from
the CMS; there are none yet, so a topic opens to show its description.

**Contact.** "What appears changes depending on what the visitor selects."

- The general enquiry form shows to begin with, and no answers.
- **Get a quote** and **Ask the expert** swap the form for their own (the
  Gravity Forms chosen on each action card) in place, close any open answers
  and bring the form into view. Under a swapped-in form, "Send a general
  enquiry instead" goes back. Without JavaScript the button follows its link.
- A **pill** shows its email address inside itself when pressed, and its
  questions underneath; pressing another replaces them, pressing it again
  closes it. Careers and the customer log-in link out.
- The notes list as still to confirm: the email address for each pill, and
  the FAQ content, which the client is supplying.

### Revisions and backups

Services, sectors, case studies and vacancies did not keep revisions, so
rebuilding one overwrote it for good. They do now (`'revisions'` in each
`supports`). Every post of those types also carries its phase one content in
the `_cb_phase_one_content` post meta, saved before any rebuild, except Lift
maintenance and Lift types, which were rebuilt before this was noticed. Their
phase one content is still on the live site.

### Page width and heading size

Phase two narrowed the widest container from 1360px to 1245px (1212px of
content, as drawn in the redesign) in `_child_theme_variables.scss`, and raised
`--fs-h2` to 56px at full size in `_tokens.scss`. Both are site-wide.

## Nav CTA pill

The primary nav item with the CSS class `button` ("Ask the experts") renders as
a mint pill, matching the group site at andwis.com. `button` is deliberately the
same class name andwis.com uses for its own nav CTA. `cb_render_primary_nav()`
outputs it as `.cb-nav__cta`; see the Header section for the other menu classes.

The hover is the gradient sweep `.btn-primary` already uses: a 400% wide
linear-gradient parked at `background-position: 100%` and slid to `0%` on hover.
andwis.com does exactly the same thing, and its tokens are ours - its `--dark` is
`rgb(28 66 32)`, our `--cb-forest`, and its `--highlight` is `#00ff99`, our
`--cb-mint` - so this matches rather than approximates it.

## Mobile and horizontal overflow

Heading tokens `--fs-hero` to `--fs-h4` are fluid `clamp()` values: the original
fixed size at 1280px and above, scaling down to a 375px phone. Root is 22px, so
fixed rem headings were 49.5px on a phone and single long words overflowed.

Three things used to make pages wider than the viewport on a phone. All are
fixed, and all are easy to reintroduce:

- The phase one footer curve was a `120vw` pseudo-element and needed
  `overflow-x: clip` on `.site-footer`. The phase two curve is exactly as wide
  as the footer, so that is gone. If a curve ever overhangs again, use `clip`,
  not `hidden` - it sits above the footer's top edge and needs the vertical
  axis left visible.
- The CB Image Text Checklist dots ring is a rotating square whose corners sweep
  wider than its column, so the section also carries `overflow-x: clip`.
- The photo image style must reset `min-height` wherever it sets
  `aspect-ratio`. With both in force the browser widens the box to satisfy them.

The layered style's three circles are sized and positioned as percentages of
the media square, which equal the original rem sizes at the 500px desktop width
and scale down together below it.

## Cookie consent

CookieYes is loaded in `header.php`, immediately after the viewport meta and
before everything else in `<head>`.

Two things about that placement are deliberate and should not be changed
casually. It sits **before** the Google Analytics and GTM tags further down the
head, because it has to run first to block their cookies until consent is given.
And it carries no `async` or `defer`, for the same reason - deferring it lets the
tags it is meant to gate fire first.

Unlike the analytics tags, it is not wrapped in `! is_user_logged_in()`, so the
banner is testable without logging out.

## Footer legal disclosure

The footer carries the statutory company details and the legal links. Both are
driven from data rather than hardcoded, by `cb_footer_legal_lines()` and
`cb_footer_legal_links()` in `inc/cb-footer.php`.

**Details** come from Site-Wide Settings > Legal: registered company name,
company registration number, place of registration, VAT registration number and
registered office. They are composed into one statement per line, and nothing
at all is printed when the fields are empty, so the footer never shows a stub.
Partial data degrades sensibly - a number with no name renders "Company number
12345678."

Each statement sits on its own line rather than running together. As a single
paragraph the three ran to two wrapped lines and broke mid-way through the VAT
number. Lines are escaped individually and joined with literal `<br>`.

A UK limited company has to show its registered name, number, place of
registration and registered office on its website, and its VAT number where it
is registered, which is why those five fields exist rather than one free-text
box. The registered office is entered a line per line and joined with commas.

**Links** are Privacy policy, Cookies, Terms and conditions and Sitemap, in
that order, found by slug with `get_page_by_path()`: `privacy-policy`,
`cookie-policy` or `cookies`, `terms-and-conditions` or `terms-of-use`, and
`sitemap`. A page that is missing or not published is dropped rather than linked
to a 404, and the permalink is looked up so a page nested under a parent still
resolves. Add an entry to the list in `cb_footer_legal_links()` to add a link.

## Finding where a block is used

Block instances are stored as HTML comments in `post_content`, so there is
nothing WordPress indexes to query - it is a content search.

`block-usage.php` in the theme root does it. `wp eval-file` resolves its path
against the current directory rather than the theme, so from a site root - which
is where an SSH session starts - pass the full path:

```
wp eval-file wp-content/themes/cb-andwislifts2026/block-usage.php "CB Emergency"
```

From the theme directory itself, `wp eval-file block-usage.php "CB Emergency"`
is enough.

It takes the title, the slug or the full name (`acf/cb-emergency`), and lists
every post using it with its type, status, permalink and how many times the
block appears in that post. Revisions, auto-drafts and trash are excluded, so a
post is not counted twice.

It exits immediately unless `WP_CLI` is defined - it sits in the theme root
alongside the other dev scripts and must not be reachable over the web.

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
