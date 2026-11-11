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
$kicker           = get_field( 'subline' );
$intro            = get_field( 'intro' );
$show_wave        = get_field( 'show_wave' );
// Light: a pale band with dark text and no photo, for a page such as
// Resources that gets straight to its content.
$is_light         = 'light' === get_field( 'style' );
$buttons          = array_filter(
	array(
		'btn btn-primary' => get_field( 'cta_button' ),
		'btn btn-ghost'   => get_field( 'cta_secondary' ),
	),
	static function ( $link ) {
		return ! empty( $link['url'] );
	}
);
$stats            = array_filter(
	(array) get_field( 'stats' ),
	static function ( $stat ) {
		return is_array( $stat ) && '' !== trim( (string) ( $stat['value'] ?? '' ) );
	}
);

if ( null === $show_wave ) {
	$show_wave = true;
}

// The stats band takes the place of the white curve along the bottom.
$classes = array( 'cb-hero' );

if ( $stats ) {
	$classes[] = 'cb-hero--has-stats';
} elseif ( $show_wave ) {
	$classes[] = 'cb-hero--bottom-curve';
}

if ( $is_light ) {
	$classes[] = 'cb-hero--light';
}

if ( $extra ) {
	$classes[] = $extra;
}
?>
<section class="<?= esc_attr( implode( ' ', $classes ) ); ?>" id="<?= esc_attr( $section_id ); ?>">
	<?php
	if ( ! $is_light && ! empty( $background_image['ID'] ) ) {
		?>
	<div class="cb-hero__bg" style="background-image:url('<?= esc_url( wp_get_attachment_image_url( $background_image['ID'], 'full' ) ); ?>');"></div>
		<?php
	}
	?>
	<?php if ( ! $is_light ) { ?>
	<div class="cb-hero__scrim"></div>
	<?php } ?>
	<div class="container cb-hero__content">
		<?php
		// The kicker reads before the heading but is not part of it.
		if ( $kicker ) {
			?>
		<p class="cb-hero__kicker"><?= esc_html( $kicker ); ?></p>
			<?php
		}
		if ( $heading ) {
			?>
		<h1><?= wp_kses_post( $heading ); ?></h1>
			<?php
		}
		if ( $intro ) {
			?>
		<p class="cb-hero__intro"><?= esc_html( $intro ); ?></p>
			<?php
		}
		if ( $buttons ) {
			?>
		<div class="cb-hero__cta">
			<?php foreach ( $buttons as $button_class => $button ) { ?>
			<a href="<?= esc_url( $button['url'] ); ?>" class="<?= esc_attr( $button_class ); ?>"<?= cb_link_target( $button ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?= esc_html( $button['title'] ); ?></a>
			<?php } ?>
		</div>
			<?php
		}
		?>
	</div>
	<?php
	if ( $stats ) {
		?>
	<div class="cb-hero__stats">
		<div class="container">
			<dl class="cb-hero__stats-list">
				<?php
				foreach ( $stats as $stat ) {
					// "Top 5" is drawn as a small word and a large figure.
					$value = trim( (string) $stat['value'] );
					$small = '';

					if ( preg_match( '/^([^\d]+?)\s+(\d.*)$/u', $value, $parts ) ) {
						$small = $parts[1];
						$value = $parts[2];
					}
					?>
				<div class="cb-hero__stat">
					<dt class="cb-hero__stat-label"><?= esc_html( $stat['description'] ?? '' ); ?></dt>
					<dd class="cb-hero__stat-value"><?= $small ? '<small>' . esc_html( $small ) . '</small> ' : ''; ?><?= esc_html( $value ); ?></dd>
				</div>
					<?php
				}
				?>
			</dl>
		</div>
	</div>
		<?php
	}
	?>
</section>

<?php
if ( ! $is_light && ! empty( $background_image['ID'] ) ) {
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
