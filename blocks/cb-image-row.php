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
$shape      = get_field( 'shape' ) ? get_field( 'shape' ) : 'natural';

if ( empty( $images ) ) {
	return;
}

// A circle is cropped from the centre and can be rendered large, so it needs a
// bigger source than a logo fitted to a modest height.
$image_size = 'circle' === $shape ? 'large' : 'medium_large';
?>
<section class="cb-image-row cb-image-row--<?= esc_attr( $size ); ?> cb-image-row--<?= esc_attr( $align ); ?> cb-image-row--<?= esc_attr( $shape ); ?> <?= esc_attr( $extra ); ?>" id="<?= esc_attr( $section_id ); ?>">
	<div class="container">
		<div class="cb-image-row__items">
			<?php
			foreach ( $images as $image_id ) {
				?>
			<div class="cb-image-row__item"><?= wp_get_attachment_image( $image_id, $image_size, false, array( 'loading' => 'lazy' ) ); ?></div>
				<?php
			}
			?>
		</div>
	</div>
</section>
