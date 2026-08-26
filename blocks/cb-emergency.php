<?php
/**
 * Block template for CB Emergency.
 *
 * A high-contrast 24/7 callout banner. The number defaults to contact_phone in
 * Site-Wide Settings so it is maintained in one place, with a per-block override
 * where a region or campaign needs a different line.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

$section_id = $block['anchor'] ?? $block['id'] ?? wp_unique_id( 'cb-emergency-' );
$extra      = $block['className'] ?? '';
$heading    = get_field( 'heading' );
$intro      = get_field( 'intro' );
$note       = get_field( 'note' );
$phone      = get_field( 'phone_override' ) ? get_field( 'phone_override' ) : get_field( 'contact_phone', 'option' );
$tel        = $phone ? preg_replace( '/[^0-9+]/', '', $phone ) : '';
?>
<section class="cb-emergency <?= esc_attr( $extra ); ?>" id="<?= esc_attr( $section_id ); ?>">
	<div class="container">
		<div class="cb-emergency__inner">
			<?php
			if ( $heading ) {
				?>
			<h2><?= esc_html( $heading ); ?></h2>
				<?php
			}
			if ( $intro ) {
				?>
			<p class="cb-emergency__intro"><?= esc_html( $intro ); ?></p>
				<?php
			}
			if ( $phone ) {
				?>
			<a class="cb-emergency__number" href="tel:<?= esc_attr( $tel ); ?>"><?= esc_html( $phone ); ?></a>
				<?php
			} else {
				// No number configured yet - say so rather than render an empty banner.
				?>
			<p class="cb-emergency__pending">Emergency callout number to be confirmed. Add it under Site-Wide Settings.</p>
				<?php
			}
			if ( $note ) {
				?>
			<p class="cb-emergency__note"><?= wp_kses_post( $note ); ?></p>
				<?php
			}
			?>
		</div>
	</div>
</section>
