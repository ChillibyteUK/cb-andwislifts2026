<?php
/**
 * Block template for CB Sectors.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

$section_id = $block['anchor'] ?? $block['id'] ?? wp_unique_id( 'cb-sectors-' );
$extra      = $block['className'] ?? '';
$heading    = get_field( 'heading' );
$body       = get_field( 'body' );
$sectors    = get_field( 'sectors' );

if ( ! $sectors ) {
	$legacy_labels = array_filter(
		array(
			get_field( 'sector_top' ),
			get_field( 'sector_upper_left' ),
			get_field( 'sector_upper_right' ),
			get_field( 'sector_lower_left' ),
			get_field( 'sector_lower_right' ),
			get_field( 'sector_bottom' ),
		)
	);

	$sectors = array_map(
		function ( $label ) {
			return array( 'label' => $label );
		},
		$legacy_labels
	);
}

$sector_items = array();

foreach ( is_array( $sectors ) ? $sectors : array() as $sector ) {
	$label = trim( (string) ( $sector['label'] ?? '' ) );

	if ( '' === $label ) {
		continue;
	}

	// An explicit sector page wins. Otherwise the label is matched to a sector
	// post by name, so labels written before this field existed still link.
	$chosen = $sector['sector_page'] ?? 0;
	$url    = $chosen ? (string) get_permalink( (int) $chosen ) : cb_resolve_sector_link( $label );

	$sector_items[] = array(
		'label' => $label,
		'url'   => $url,
	);
}

if ( empty( $sector_items ) ) {
	$fallback_labels = array(
		'Healthcare',
		'Education',
		'Government',
		'Retail & Leisure',
		'Residential',
		'Commercial',
	);

	foreach ( $fallback_labels as $label ) {
		$sector_items[] = array(
			'label' => $label,
			'url'   => cb_resolve_sector_link( $label ),
		);
	}
}

// The column repeats its labels until it is long enough to fill the panel.
$sector_count  = count( $sector_items );
$display_items = $sector_items;

while ( count( $display_items ) < 10 ) {
	$display_items = array_merge( $display_items, $sector_items );
}

$display_items = array_slice( $display_items, 0, 10 );

/**
 * Render the label list.
 *
 * Both layers get identical markup so the highlight overlay stays aligned to
 * the base text - an anchor on one layer and bare text on the other would shift
 * the line boxes apart.
 *
 * @param array   $items      Label/url pairs to render.
 * @param integer $real_count How many leading items are real rather than padding.
 * @param boolean $decorative Whether this layer is the highlight overlay.
 * @return void
 */
$cb_sectors_render_list = function ( $items, $real_count, $decorative ) {
	foreach ( $items as $index => $item ) {
		// Padding repeats the same labels, so keep it out of the tab order and
		// the accessibility tree. The overlay layer is decorative throughout.
		$muted = $decorative || $index >= $real_count;
		?>
	<li<?= $muted && ! $decorative ? ' aria-hidden="true"' : ''; ?>>
		<?php if ( ! empty( $item['url'] ) ) { ?>
		<a href="<?= esc_url( $item['url'] ); ?>"<?= $muted ? ' tabindex="-1"' : ''; ?>><?= esc_html( $item['label'] ); ?></a>
		<?php } else { ?>
		<?= esc_html( $item['label'] ); ?>
		<?php } ?>
	</li>
		<?php
	}
};
?>
<section class="cb-sectors <?= esc_attr( $extra ); ?>" id="<?= esc_attr( $section_id ); ?>">
	<div class="container">
		<div class="row">
			<div class="col-lg-6">
				<div class="cb-sectors__copy">
					<?php if ( $heading ) { ?>
						<h2><?= esc_html( $heading ); ?></h2>
					<?php } ?>
					<?php if ( $body ) { ?>
						<div class="cb-sectors__body"><?= wp_kses_post( wpautop( $body ) ); ?></div>
					<?php } ?>
				</div>
			</div>
			<div class="col-lg-6 ps-lg-5">
				<div class="cb-sectors__highlight highlight-text rellax" style="--x:180px;--y:350px;--cb-sector-count:<?= esc_attr( $sector_count ); ?>;" data-rellax-speed="2" data-rellax-xs-speed="0" data-rellax-mobile-speed="0">
					<ul class="base-text">
						<?php $cb_sectors_render_list( $display_items, $sector_count, false ); ?>
					</ul>
					<ul class="hover-text" aria-hidden="true">
						<?php $cb_sectors_render_list( $display_items, $sector_count, true ); ?>
					</ul>
				</div>
			</div>
		</div>
	</div>
</section>

<?php
// Emitted on wp_footer rather than inline. An inline <script> inside an ACF
// block's output breaks its preview in the editor ("This block has encountered
// an error and cannot be previewed") - the other blocks in this theme already
// use this pattern.
add_action(
	'wp_footer',
	function () use ( $section_id ) {
		?>
<script>
document.addEventListener('DOMContentLoaded', function () {
	var section = document.getElementById(<?= wp_json_encode( $section_id ); ?>);
	if (!section) return;

	var highlight = section.querySelector('.highlight-text');
	if (!highlight) return;

	function updatePosition(event) {
		var rect = highlight.getBoundingClientRect();
		highlight.style.setProperty('--x', (event.clientX - rect.left).toFixed(1) + 'px');
		highlight.style.setProperty('--y', (event.clientY - rect.top).toFixed(1) + 'px');
	}

	function updateParallax() {
		if (window.innerWidth < 768) {
			highlight.style.transform = '';
			return;
		}

		var rect = section.getBoundingClientRect();
		var windowHeight = window.innerHeight;

		if (rect.bottom > 0 && rect.top < windowHeight) {
			var speed = parseFloat(highlight.getAttribute('data-rellax-speed') || '0');
			var progress = (windowHeight - rect.top) / (windowHeight + rect.height);
			progress = Math.max(0, Math.min(1, progress));
			var y = ((progress - 0.5) * speed * 80) - 40;
			highlight.style.transform = 'translate3d(0,' + y.toFixed(1) + 'px,0)';
		}
	}

	var ticking = false;
	function onScroll() {
		if (!ticking) {
			window.requestAnimationFrame(function () {
				updateParallax();
				ticking = false;
			});
			ticking = true;
		}
	}

	highlight.addEventListener('mousemove', updatePosition);
	highlight.addEventListener('mouseenter', updatePosition);
	window.addEventListener('scroll', onScroll, { passive: true });
	window.addEventListener('resize', onScroll);
	updateParallax();
});
</script>
		<?php
	},
	9999
);
