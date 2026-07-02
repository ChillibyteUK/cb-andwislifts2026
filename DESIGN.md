# andwis lifts Design Breakdown

## Implementation Decisions

- The page will be built manually in WordPress using modular ACF blocks.
- Header and footer are global theme templates, not blocks, and will be created later based on another theme.
- Contact data should come from Site-Wide Settings where practical.
- Images should be optional and render gracefully when empty.
- Image fields should still be present in ACF for all image-led sections.
- The sector chip layout should support exactly six fixed positions, matching the supplied HTML.
- Poppins should be self-hosted, downloaded from Google Fonts, and wired into the theme Sass/header in place of the current Inter setup.

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
- Eyebrow text
- Eyebrow pulse toggle
- Heading
- Highlighted heading text
- Subline
- Intro text
- Show wave divider toggle

### CB Image Text Checklist

Source HTML: `.reassure`

Fields:

- Kicker
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

- Kicker
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

- Kicker
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

- Kicker
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

- Kicker
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

- Kicker
- Heading
- Customer repeater:
  - Name
  - Optional logo
- Note text

### CB Contact Cards

Source HTML: `.contact`

Fields:

- Kicker
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
