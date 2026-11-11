<?php
/**
 * Block template for CB Offices.
 *
 * Regional offices in one of two layouts: cards, each an image beside a panel
 * carrying the address, the main number and the named contacts for that
 * region; or a plain list in three columns, with an optional call to action as
 * its last cell.
 *
 * Offices are a repeater and contacts a repeater within it, so a row can be
 * duplicated in the editor to add a region without touching the structure.
 *
 * On a phone the plain list is long, so each office folds down to its name
 * and opens when pressed (cb_offices_list_script() in inc/cb-office-finder.php).
 *
 * With filters on, a search box and a region select sit above the list.
 * Filtering is client-side - every office is already in the DOM - so it is
 * instant and the page still works with no JavaScript.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

$section_id  = $block['anchor'] ?? $block['id'] ?? wp_unique_id( 'cb-offices-' );
$extra       = $block['className'] ?? '';
$layout      = 'list' === get_field( 'layout' ) ? 'list' : 'cards';
$heading     = get_field( 'heading' );
$intro       = get_field( 'intro' );
$has_filters = null === get_field( 'show_filters' ) ? true : (bool) get_field( 'show_filters' );

// One list of offices, kept on the Contact page. A block set to share shows
// that list instead of holding a second copy that would drift out of date.
// The heading, intro and layout stay this block's own.
$offices = get_field( 'use_shared' ) ? cb_offices_shared() : get_field( 'offices' );

if ( empty( $offices ) ) {
	return;
}

if ( 'list' === $layout ) {
	$aside_heading = get_field( 'aside_heading' );
	$aside_text    = get_field( 'aside_text' );
	$aside_cta     = get_field( 'aside_cta' );
	?>
<section class="cb-offices cb-offices--list <?= esc_attr( $extra ); ?>" id="<?= esc_attr( $section_id ); ?>">
	<div class="container">
		<?php if ( $heading ) { ?>
		<h2 class="cb-offices__list-heading"><?= esc_html( $heading ); ?></h2>
		<?php } ?>
		<?php if ( $intro ) { ?>
		<p class="cb-offices__lead"><?= esc_html( $intro ); ?></p>
		<?php } ?>
		<div class="cb-offices__grid">
			<?php
			foreach ( $offices as $office ) {
				$name      = trim( (string) ( $office['name'] ?? '' ) );
				$locations = cb_offices_locations( $office );

				if ( '' === $name ) {
					continue;
				}

				// A label only helps where it tells two addresses apart.
				$labelled = count( array_unique( wp_list_pluck( $locations, 'label' ) ) ) > 1;
				$email    = '';

				foreach ( (array) ( $office['contacts'] ?? array() ) as $contact ) {
					if ( ! empty( $contact['email'] ) ) {
						$email = trim( (string) $contact['email'] );
						break;
					}
				}
				?>
			<article class="cb-offices__entry">
				<h3><?= esc_html( $name ); ?></h3>
				<div class="cb-offices__entry-body">
				<?php foreach ( $locations as $location ) { ?>
				<div class="cb-offices__entry-location">
					<?php if ( $labelled && $location['label'] ) { ?>
					<p class="cb-offices__entry-label">(<?= esc_html( $location['label'] ); ?>)</p>
					<?php } ?>
					<p class="cb-offices__entry-lines">
						<?php if ( $location['address'] ) { ?>
						<?= nl2br( esc_html( $location['address'] ) ); ?><br>
						<?php } ?>
						<?php if ( $location['phone'] ) { ?>
						<?= esc_html__( 'Tel:', 'cb-andwislifts2026' ); ?> <a href="tel:<?= esc_attr( preg_replace( '/[^0-9+]/', '', $location['phone'] ) ); ?>"><?= esc_html( $location['phone'] ); ?></a>
						<?php } ?>
					</p>
				</div>
				<?php } ?>
				<?php if ( $email ) { ?>
				<p class="cb-offices__entry-lines"><?= esc_html__( 'Email:', 'cb-andwislifts2026' ); ?> <a href="mailto:<?= esc_attr( $email ); ?>"><?= esc_html( $email ); ?></a></p>
				<?php } ?>
				</div>
			</article>
				<?php
			}

			if ( $aside_heading || $aside_text || $aside_cta ) {
				?>
			<div class="cb-offices__aside">
				<?php if ( $aside_heading ) { ?>
				<h3><?= esc_html( $aside_heading ); ?></h3>
				<?php } ?>
				<?php if ( $aside_text ) { ?>
				<p><?= esc_html( $aside_text ); ?></p>
				<?php } ?>
				<?php if ( ! empty( $aside_cta['url'] ) ) { ?>
				<a class="btn btn-primary" href="<?= esc_url( $aside_cta['url'] ); ?>"<?= cb_link_target( $aside_cta ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?= esc_html( $aside_cta['title'] ); ?></a>
				<?php } ?>
			</div>
				<?php
			}
			?>
		</div>
	</div>
</section>
	<?php
	add_action( 'wp_footer', 'cb_offices_list_script', 30 );

	return;
}

// Region drives the select and falls back to the office name, so the filter
// works before anyone has thought about regions as a separate idea.
$regions = array();

foreach ( $offices as $office ) {
	$region = trim( (string) ( $office['region'] ?? '' ) );

	if ( '' === $region ) {
		$region = trim( (string) ( $office['name'] ?? '' ) );
	}

	if ( '' !== $region ) {
		$regions[ $region ] = $region;
	}
}

$total = count( $offices );
?>
<section class="cb-offices <?= esc_attr( $extra ); ?>" id="<?= esc_attr( $section_id ); ?>">
	<div class="container">
		<div class="cb-section-head pb-5">
			<?php
			if ( $heading ) {
				?>
			<h2><?= esc_html( $heading ); ?></h2>
				<?php
			}
			if ( $intro ) {
				?>
			<p><?= esc_html( $intro ); ?></p>
				<?php
			}
			?>
		</div>
		<?php
		if ( $has_filters ) {
			$search_id = $section_id . '-search';
			$region_id = $section_id . '-region';
			?>
		<div class="cb-offices__filters">
			<div class="cb-offices__field cb-offices__field--search">
				<label for="<?= esc_attr( $search_id ); ?>"><?= esc_html__( 'Search', 'cb-andwislifts2026' ); ?></label>
				<input type="search" id="<?= esc_attr( $search_id ); ?>" data-cb-offices-search placeholder="<?= esc_attr__( 'Town, postcode or name', 'cb-andwislifts2026' ); ?>" autocomplete="off">
			</div>
			<?php if ( count( $regions ) > 1 ) { ?>
			<div class="cb-offices__field">
				<label for="<?= esc_attr( $region_id ); ?>"><?= esc_html__( 'Region', 'cb-andwislifts2026' ); ?></label>
				<select id="<?= esc_attr( $region_id ); ?>" data-cb-offices-region>
					<option value=""><?= esc_html__( 'All regions', 'cb-andwislifts2026' ); ?></option>
					<?php foreach ( $regions as $region ) { ?>
					<option value="<?= esc_attr( $region ); ?>"><?= esc_html( $region ); ?></option>
					<?php } ?>
				</select>
			</div>
			<?php } ?>
			<button type="button" class="cb-offices__clear" data-cb-offices-clear hidden><?= esc_html__( 'Clear', 'cb-andwislifts2026' ); ?></button>
		</div>
		<p class="cb-offices__count" data-cb-offices-count role="status" aria-live="polite"></p>
			<?php
		}

		foreach ( $offices as $office ) {
			$name     = trim( (string) ( $office['name'] ?? '' ) );
			$region   = trim( (string) ( $office['region'] ?? '' ) );
			$locations = cb_offices_locations( $office );
			$image     = $office['image'] ?? null;
			$image_id  = is_array( $image ) ? (int) ( $image['ID'] ?? 0 ) : (int) $image;
			$contacts = $office['contacts'] ?? array();

			if ( '' === $name ) {
				continue;
			}

			if ( '' === $region ) {
				$region = $name;
			}
			?>
		<article class="cb-offices__office" data-cb-offices-item data-region="<?= esc_attr( $region ); ?>">
			<?php if ( $image_id ) { ?>
			<div class="cb-offices__media"><?= wp_get_attachment_image( $image_id, 'large', false, array( 'loading' => 'lazy' ) ); ?></div>
			<?php } ?>
			<div class="cb-offices__panel">
				<h3><?= esc_html( $name ); ?></h3>
				<?php if ( $locations ) { ?>
				<div class="cb-offices__locations">
					<?php foreach ( $locations as $location ) { ?>
					<div class="cb-offices__location">
						<?php if ( $location['label'] ) { ?>
						<span class="cb-offices__location-label"><?= esc_html( $location['label'] ); ?></span>
						<?php } ?>
						<?php if ( $location['address'] ) { ?>
						<p class="cb-offices__address"><?= nl2br( esc_html( $location['address'] ) ); ?></p>
						<?php } ?>
						<?php if ( $location['phone'] ) { ?>
						<p class="cb-offices__phone"><a href="tel:<?= esc_attr( preg_replace( '/[^0-9+]/', '', $location['phone'] ) ); ?>"><?= esc_html( $location['phone'] ); ?></a></p>
						<?php } ?>
					</div>
					<?php } ?>
				</div>
				<?php } ?>
				<?php if ( $contacts ) { ?>
				<ul class="cb-offices__contacts">
					<?php
					foreach ( $contacts as $contact ) {
						$role          = trim( (string) ( $contact['role'] ?? '' ) );
						$contact_name  = trim( (string) ( $contact['name'] ?? '' ) );
						$contact_email = trim( (string) ( $contact['email'] ?? '' ) );
						$contact_phone = trim( (string) ( $contact['phone'] ?? '' ) );

						if ( '' === $role && '' === $contact_name && '' === $contact_email ) {
							continue;
						}
						?>
					<li class="cb-offices__contact">
						<?php if ( $role ) { ?>
						<span class="cb-offices__role"><?= esc_html( $role ); ?></span>
						<?php } ?>
						<?php if ( $contact_name ) { ?>
						<span class="cb-offices__name"><?= esc_html( $contact_name ); ?></span>
						<?php } ?>
						<?php if ( $contact_email ) { ?>
						<a class="cb-offices__email" href="mailto:<?= esc_attr( $contact_email ); ?>"><?= esc_html( $contact_email ); ?></a>
						<?php } ?>
						<?php if ( $contact_phone ) { ?>
						<a class="cb-offices__contact-phone" href="tel:<?= esc_attr( preg_replace( '/[^0-9+]/', '', $contact_phone ) ); ?>"><?= esc_html( $contact_phone ); ?></a>
						<?php } ?>
					</li>
						<?php
					}
					?>
				</ul>
				<?php } ?>
			</div>
		</article>
			<?php
		}

		if ( $has_filters ) {
			?>
		<p class="cb-offices__empty" data-cb-offices-empty hidden><?= esc_html__( 'No offices match that search. Try a town, postcode or region, or clear the filters.', 'cb-andwislifts2026' ); ?></p>
			<?php
		}
		?>
	</div>
</section>

<?php
// Emitted on wp_footer rather than inline, matching the other blocks in this
// theme - an inline <script> in a block's output breaks its editor preview.
if ( $has_filters ) {
	add_action(
		'wp_footer',
		function () use ( $section_id, $total ) {
			?>
<script>
document.addEventListener('DOMContentLoaded', function () {
	var block = document.getElementById(<?= wp_json_encode( $section_id ); ?>);
	if (!block) return;

	var search = block.querySelector('[data-cb-offices-search]');
	var region = block.querySelector('[data-cb-offices-region]');
	var clear  = block.querySelector('[data-cb-offices-clear]');
	var count  = block.querySelector('[data-cb-offices-count]');
	var empty  = block.querySelector('[data-cb-offices-empty]');
	var total  = <?= (int) $total; ?>;

	// Cached on load: the panel text covers name, address, postcode and contacts,
	// which is what someone types into a box like this.
	var items = Array.prototype.slice.call(block.querySelectorAll('[data-cb-offices-item]')).map(function (el) {
		return {
			el: el,
			text: (el.textContent || '').toLowerCase().replace(/\s+/g, ' '),
			region: el.getAttribute('data-region') || ''
		};
	});

	function apply() {
		var term = (search && search.value || '').trim().toLowerCase().replace(/\s+/g, ' ');
		var wantRegion = region && region.value || '';
		var shown = 0;

		items.forEach(function (item) {
			var match = (!term || item.text.indexOf(term) !== -1) &&
				(!wantRegion || item.region === wantRegion);
			item.el.hidden = !match;
			if (match) shown++;
		});

		var filtering = !!(term || wantRegion);
		if (clear) clear.hidden = !filtering;
		if (empty) empty.hidden = shown !== 0;
		if (count) {
			count.textContent = filtering ? shown + ' of ' + total + ' offices' : '';
		}
	}

	var timer;
	function debounced() {
		window.clearTimeout(timer);
		timer = window.setTimeout(apply, 120);
	}

	if (search) search.addEventListener('input', debounced);
	if (region) region.addEventListener('change', apply);
	if (clear) {
		clear.addEventListener('click', function () {
			if (search) search.value = '';
			if (region) region.value = '';
			apply();
			if (search) search.focus();
		});
	}

	apply();
});
</script>
			<?php
		},
		9999
	);
}
