<?php
/**
 * Block template for CB CTA.
 *
 * A closing call to action for pages that want to end on a prompt rather than a
 * full contact form. A bare button in whitespace reads as leftover markup, so
 * the button always arrives with a heading and its own banded background.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

$section_id = $block['anchor'] ?? $block['id'] ?? wp_unique_id( 'cb-cta-' );
$extra      = $block['className'] ?? '';
$heading    = get_field( 'heading' );
$body       = get_field( 'body' );
$cta        = get_field( 'cta' );
$cta_alt    = get_field( 'cta_secondary' );
$style      = get_field( 'style' ) ? get_field( 'style' ) : 'forest';
$layout     = get_field( 'layout' ) ? get_field( 'layout' ) : 'split';

if ( ! $heading && empty( $cta['url'] ) ) {
	return;
}

$classes = array(
	'cb-cta',
	'cb-cta--' . $style,
	'cb-cta--' . $layout,
);

if ( $extra ) {
	$classes[] = $extra;
}
?>
<section class="<?= esc_attr( implode( ' ', $classes ) ); ?>" id="<?= esc_attr( $section_id ); ?>">
	<div class="container">
		<div class="cb-cta__inner">
			<div class="cb-cta__copy">
				<?php
				if ( $heading ) {
					?>
				<h2><?= esc_html( $heading ); ?></h2>
					<?php
				}
				if ( $body ) {
					?>
				<p><?= esc_html( $body ); ?></p>
					<?php
				}
				?>
			</div>
			<?php
			if ( ! empty( $cta['url'] ) || ! empty( $cta_alt['url'] ) ) {
				?>
			<div class="cb-cta__actions">
				<?php
				foreach ( array( $cta, $cta_alt ) as $index => $link ) {
					if ( empty( $link['url'] ) ) {
						continue;
					}

					$target = ! empty( $link['target'] ) ? $link['target'] : '';
					$button = 0 === $index ? 'btn btn-primary' : 'btn btn-ghost';
					$label  = $link['title'] ? $link['title'] : __( 'Get in touch', 'cb-andwislifts2026' );
					$rel    = $target ? sprintf( ' target="%s" rel="noopener"', esc_attr( $target ) ) : '';
					?>
				<a class="<?= esc_attr( $button ); ?>" href="<?= esc_url( $link['url'] ); ?>"<?= $rel; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?= esc_html( $label ); ?></a>
					<?php
				}
				?>
			</div>
				<?php
			}
			?>
		</div>
	</div>
</section>
