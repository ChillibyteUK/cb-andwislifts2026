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

- `/news/` is a WP Page set as **Posts page** in Reading settings. `index.php`
  takes its H1 from that page's title, its intro from the page **excerpt**, and
  its hero image from the page's **featured image** - so the page needs all three.
- Filter chips are built from categories in use and filter client-side on
  `data-categories` (all posts are already in the DOM). Categories from the brief:
  Contract awards, Company news, Community, Industry.
- `single.php` opens with a dark hero carrying the title, category and date. That
  is structural, not decorative - see the Header note above. The title, featured
  image and meta live in the hero only; do not reintroduce them in the body.

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

Source HTML: `.topnav`

Global template: `header.php`

**Every page must open with a CB Hero block.** `#wrapper-navbar` is `position: fixed`
with `background: transparent`, and the logo's `.andwis` paths are `fill: var(--cb-paper)`
- white, so it reads against a dark hero image. It only flips to a white bar with
`fill: var(--cb-forest)` once `.scrolled` is applied. A page that opens on a light
background therefore has an invisible logo and nav until the user scrolls.

If a page ever needs to open without a hero, the header needs a light-background
variant (apply the `.scrolled` treatment from page load) rather than dropping the hero.

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
- Card source (Manual / All services / Selected services / Services linked to this page / All sectors / Selected sectors)
- Choose posts (relationship, shown for the "selected" sources)
- Maximum cards (shown for all CPT sources)
- Cards repeater (shown for Manual only):
  - Number/label
  - Title
  - Description
  - Optional link

CPT sources map each post to a card via `cb_get_cpt_cards()` in `inc/cb-posttypes.php`:
post title → card title, `card_summary` → description, `card_icon` → icon, permalink → link.
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

### CB Emergency

High-contrast 24/7 callout banner. The number comes from `contact_phone` in
Site-Wide Settings, with a per-block override. With no number configured it says
so rather than rendering an empty banner or a dead `tel:` link.

Fields: Heading, Intro, Phone override, Note.

### CB News Index

Latest posts as cards, reusing the `.cb-post-card` markup from `index.php` so the
homepage teaser and the news archive match. Renders nothing when there are no
posts.

Fields: Heading, Intro, How many posts (default 3), View all link.

### CB Case Study Index

Lists published case studies as cards with filter chips built from the sector and
service relationship fields actually in use, so the filter never offers an empty
option. Filtering is client-side on `data-terms`.

Fields: Heading, No results message, Maximum cards, View all link.

Setting a limit turns it into a teaser row: the filter chips and their script are
suppressed, since filtering a truncated list is misleading.

`single-case_study.php` renders the brief's fixed shape - challenge, what we did,
result - from ACF fields, then falls back to the post body for studies imported as
prose that have not been split into those sections yet.

**Gotcha:** `post_status => 'any'` in `WP_Query` / `get_posts` **excludes drafts** -
core registers `draft` with `exclude_from_search => true`. Use an explicit
`array( 'draft', 'publish' )` or `get_page_by_path()` when looking up unpublished
posts, or duplicate-checks in import scripts will silently miss them.

### CB Downloads

Document library for `/resources/downloads/`. A **flat** repeater that the template
groups by each row's Group field, in the order groups first appear - flat rather
than nested repeaters so an editor can regroup or reorder a document without
rebuilding the structure.

Fields:

- Heading, Intro
- Documents repeater: document, group, file, version, review date
- Note (basic HTML allowed)

Rows with no file still render, marked "Available on request", so the page reads
correctly while certificates are still being supplied.

### CB FAQs

Bootstrap accordion with optional topic filtering. Block markup is ported from
`cb-turnpower2025`, with a `category` sub-field and filter chips added (their
version had no filtering, which the brief's `/faqs/` page calls for).

Fields:

- Heading
- Intro text
- Show topic filter (needs two or more distinct categories to appear)
- Questions repeater: question, category, answer

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
