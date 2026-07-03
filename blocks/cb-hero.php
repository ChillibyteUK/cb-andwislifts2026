<?php
/**
 * Block template for CB Hero.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

$section_id       = $block['anchor'] ?? $block['id'] ?? wp_unique_id( 'cb-hero-' );
$extra            = $block['className'] ?? '';
$background_image = get_field( 'background_image' );
$heading          = get_field( 'heading' );
$subline          = get_field( 'subline' );
$intro            = get_field( 'intro' );
$show_wave        = get_field( 'show_wave' );

if ( null === $show_wave ) {
	$show_wave = true;
}

?>
<section class="cb-hero<?= $show_wave ? ' cb-hero--bottom-curve' : ''; ?> <?= esc_attr( $extra ); ?>" id="<?= esc_attr( $section_id ); ?>">
	<?php
	if ( ! empty( $background_image['ID'] ) ) {
		?>
	<div class="cb-hero__bg" style="background-image:url('<?= esc_url( wp_get_attachment_image_url( $background_image['ID'], 'full' ) ); ?>');"></div>
		<?php
	}
	?>
	<div class="cb-hero__scrim"></div>
	<div class="cb-hero__inner">
		<?php
		if ( $heading ) {
			?>
		<h1><?= wp_kses_post( $heading ); ?></h1>
			<?php
		}
		if ( $subline ) {
			?>
		<p class="cb-hero__subline"><?= esc_html( $subline ); ?></p>
			<?php
		}
		if ( $intro ) {
			?>
		<p class="cb-hero__intro"><?= esc_html( $intro ); ?></p>
			<?php
		}
		?>
	</div>
</section>

<?php
if ( ! empty( $background_image['ID'] ) ) {
	add_action(
		'wp_footer',
		function () use ( $section_id ) {
			?>
<script>
document.addEventListener('DOMContentLoaded', function () {
	var section = document.getElementById(<?= wp_json_encode( $section_id ); ?>);
	if (!section) return;

	var ticking = false;

	function update() {
		var rect = section.getBoundingClientRect();
		var windowHeight = window.innerHeight;

		if (rect.bottom > 0 && rect.top < windowHeight) {
			var percent = (windowHeight - rect.top) / (windowHeight + rect.height);
			percent = Math.max(0, Math.min(1, percent));
			var translateY = (percent - 0.5) * 160;
			section.style.setProperty('--cb-hero-parallax-y', translateY.toFixed(1) + 'px');
		}

		ticking = false;
	}

	function onScroll() {
		if (!ticking) {
			window.requestAnimationFrame(update);
			ticking = true;
		}
	}

	window.addEventListener('scroll', onScroll, { passive: true });
	window.addEventListener('resize', onScroll);
	onScroll();
});
</script>
			<?php
		},
		9999
	);
}
