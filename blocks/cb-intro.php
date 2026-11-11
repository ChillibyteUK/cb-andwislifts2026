<?php
/**
 * Block template for CB Intro.
 *
 * A centred heading, a paragraph or two and an optional button: the opening
 * statement most phase two pages put straight after the hero.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

$section_id = $block['anchor'] ?? $block['id'] ?? wp_unique_id( 'cb-intro-' );
$extra      = $block['className'] ?? '';
$heading    = get_field( 'heading' );
$body       = get_field( 'body' );
$cta        = get_field( 'cta' );
$background = in_array( get_field( 'background' ), array( 'tint', 'green', 'forest' ), true ) ? get_field( 'background' ) : 'paper';
$align      = 'left' === get_field( 'align' ) ? ' cb-intro--left' : '';
// A logo row is worth showing if it has an image or at least a name.
$logos      = array_filter(
	(array) get_field( 'logos' ),
	static function ( $row ) {
		return is_array( $row ) && ( ! empty( $row['logo']['ID'] ) || '' !== trim( (string) ( $row['fallback_label'] ?? '' ) ) );
	}
);
$crumbs     = get_field( 'show_breadcrumb' ) && function_exists( 'yoast_breadcrumb' ) ? yoast_breadcrumb( '<p class="cb-intro__crumbs">', '</p>', false ) : '';

if ( ! $heading && ! $body ) {
	return;
}
?>
<section class="cb-intro cb-intro--<?= esc_attr( $background . $align ); ?> <?= esc_attr( $extra ); ?>" id="<?= esc_attr( $section_id ); ?>">
	<div class="container">
		<?= $crumbs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Yoast's own markup. ?>
		<?php if ( $heading ) { ?>
			<h2><?= cb_highlight( $heading ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h2>
		<?php } ?>
		<?php if ( $body ) { ?>
			<div class="cb-intro__body"><?= cb_highlight_body( wp_kses_post( $body ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
		<?php } ?>
		<?php if ( ! empty( $cta['url'] ) ) { ?>
			<a class="btn btn-ghost cb-intro__button" href="<?= esc_url( $cta['url'] ); ?>"<?= cb_link_target( $cta ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?= esc_html( $cta['title'] ); ?></a>
		<?php } ?>
		<?php if ( $logos ) { ?>
			<ul class="cb-intro__logos">
				<?php
				foreach ( $logos as $logo ) {
					$label = trim( (string) ( $logo['fallback_label'] ?? '' ) );

					// No image yet: a dashed box carrying the name, the same
					// convention CB Compliance uses for its logos.
					if ( empty( $logo['logo']['ID'] ) ) {
						?>
						<li class="cb-intro__logo cb-intro__logo--placeholder"><?= esc_html( $label ); ?></li>
						<?php
						continue;
					}
					?>
					<li class="cb-intro__logo"><?= wp_get_attachment_image( $logo['logo']['ID'], 'medium', false, array( 'loading' => 'lazy', 'alt' => $label ) ); ?></li>
					<?php
				}
				?>
			</ul>
		<?php } ?>
	</div>
</section>
