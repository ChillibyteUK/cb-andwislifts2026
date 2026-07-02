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

$sector_labels = array_values(
	array_filter(
		array_map(
			function ( $sector ) {
				return trim( (string) ( $sector['label'] ?? '' ) );
			},
			is_array( $sectors ) ? $sectors : array()
		)
	)
);

if ( empty( $sector_labels ) ) {
	$sector_labels = array(
		'Healthcare',
		'Education',
		'Government',
		'Retail & Leisure',
		'Residential',
		'Commercial',
	);
}

$display_labels      = $sector_labels;
$display_label_count = count( $display_labels );
while ( $display_label_count < 10 ) {
	$display_labels      = array_merge( $display_labels, $sector_labels );
	$display_label_count = count( $display_labels );
}
$display_labels = array_slice( $display_labels, 0, 10 );
?>
<section class="cb-sectors <?= esc_attr( $extra ); ?>" id="<?= esc_attr( $section_id ); ?>">
	<div class="cb-wrap cb-sectors__grid">
		<div class="cb-sectors__copy">
			<?php if ( $heading ) : ?>
				<h2><?= esc_html( $heading ); ?></h2>
			<?php endif; ?>
			<?php if ( $body ) : ?>
				<div class="cb-sectors__body"><?= wp_kses_post( wpautop( $body ) ); ?></div>
			<?php endif; ?>
		</div>

		<div class="cb-sectors__highlight highlight-text rellax" style="--x:180px;--y:350px;--cb-sector-count:<?= esc_attr( count( $sector_labels ) ); ?>;" data-rellax-speed="2" data-rellax-xs-speed="0" data-rellax-mobile-speed="0">
			<ul class="base-text" aria-hidden="true">
				<?php foreach ( $display_labels as $label ) : ?>
					<li><?= esc_html( $label ); ?></li>
				<?php endforeach; ?>
			</ul>
			<ul class="hover-text">
				<?php foreach ( $display_labels as $label ) : ?>
					<li><?= esc_html( $label ); ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
</section>

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
