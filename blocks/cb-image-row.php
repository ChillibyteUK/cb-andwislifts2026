<?php
/**
 * Block template for CB Image Row.
 *
 * Images side by side. Built for a pair of accreditation logos sitting under
 * another section, so the images are constrained by height rather than width -
 * logos arrive at wildly different aspect ratios and matching their heights is
 * what makes a row of them look level.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

$section_id = $block['anchor'] ?? $block['id'] ?? wp_unique_id( 'cb-image-row-' );
$extra      = $block['className'] ?? '';
$images     = get_field( 'images' );
$size       = get_field( 'size' ) ? get_field( 'size' ) : 'medium';
$align      = get_field( 'align' ) ? get_field( 'align' ) : 'center';

if ( empty( $images ) ) {
	return;
}
?>
<section class="cb-image-row cb-image-row--<?= esc_attr( $size ); ?> cb-image-row--<?= esc_attr( $align ); ?> <?= esc_attr( $extra ); ?>" id="<?= esc_attr( $section_id ); ?>">
	<div class="container">
		<div class="cb-image-row__items">
			<?php
			foreach ( $images as $image_id ) {
				?>
			<div class="cb-image-row__item"><?= wp_get_attachment_image( $image_id, 'medium_large', false, array( 'loading' => 'lazy' ) ); ?></div>
				<?php
			}
			?>
		</div>
	</div>
</section>
