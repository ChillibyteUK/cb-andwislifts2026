<?php
/**
 * Block template for CB Text Media.
 *
 * Heading, copy and a button beside an image. The image is either shown whole
 * and unframed, for graphics such as the coverage map, or as a rounded photo.
 *
 * With no image it becomes two columns of text instead: the heading on the
 * left and the copy on the right, or the copy on the left and the highlighted
 * box on the right when one is filled in. With no heading either it is a
 * single column of copy.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

$section_id = $block['anchor'] ?? $block['id'] ?? wp_unique_id( 'cb-text-media-' );
$extra      = $block['className'] ?? '';
$kicker     = get_field( 'kicker' );
$heading    = get_field( 'heading' );
$subheading = get_field( 'subheading' );
$body       = get_field( 'body' );
$cta        = get_field( 'cta' );
$image      = get_field( 'image' );
$has_image  = ! empty( $image['ID'] );
$has_aside  = get_field( 'aside_heading' ) || get_field( 'aside_body' );

if ( ! $heading && ! $body ) {
	return;
}

$classes = array(
	'cb-text-media',
	'cb-text-media--image-' . ( 'left' === get_field( 'image_position' ) ? 'left' : 'right' ),
	// The photo layouts only mean something when there is a photo.
	'cb-text-media--' . ( $has_image && in_array( get_field( 'image_style' ), array( 'photo', 'large' ), true ) ? get_field( 'image_style' ) : 'plain' ),
	'cb-text-media--' . ( 'tint' === get_field( 'background' ) ? 'tint' : 'paper' ),
);

if ( ! $has_image ) {
	$classes[] = 'cb-text-media--no-image';
}

// Text and nothing else: one column, not a heading column left empty.
$solo = ! $has_image && ! $has_aside && ! $heading && ! $kicker && ! $subheading;

if ( $solo ) {
	$classes[] = 'cb-text-media--solo';
}

if ( $extra ) {
	$classes[] = $extra;
}

$cta_class  = 'ghost' === get_field( 'cta_style' ) ? 'btn-ghost' : 'btn-primary';

$print_body = static function () use ( $body, $cta, $cta_class ) {
	if ( $body ) {
		echo '<div class="cb-text-media__body">' . cb_highlight_body( wp_kses_post( $body ) ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	if ( ! empty( $cta['url'] ) ) {
		printf(
			'<a class="btn %s cb-text-media__button" href="%s"%s>%s</a>',
			esc_attr( $cta_class ),
			esc_url( $cta['url'] ),
			cb_link_target( $cta ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			esc_html( $cta['title'] )
		);
	}
};

$print_aside = static function () {
	echo '<div class="cb-text-media__aside">';

	if ( get_field( 'aside_heading' ) ) {
		echo '<h3>' . esc_html( get_field( 'aside_heading' ) ) . '</h3>';
	}

	if ( get_field( 'aside_body' ) ) {
		echo '<p>' . nl2br( esc_html( get_field( 'aside_body' ) ) ) . '</p>';
	}

	echo '</div>';
};
?>
<section class="<?= esc_attr( implode( ' ', $classes ) ); ?>" id="<?= esc_attr( $section_id ); ?>">
	<div class="container">
		<div class="cb-text-media__inner">
			<?php if ( ! $solo ) { ?>
			<div class="cb-text-media__copy">
				<?php if ( $kicker ) { ?>
					<p class="cb-text-media__kicker"><?= esc_html( $kicker ); ?></p>
				<?php } ?>
				<?php if ( $heading ) { ?>
					<h2><?= cb_highlight( $heading ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h2>
				<?php } ?>
				<?php if ( $subheading ) { ?>
					<p class="cb-text-media__sub"><?= esc_html( $subheading ); ?></p>
				<?php } ?>
				<?php
				// Beside an image, or beside the box, the copy stays with the heading.
				if ( $has_image || $has_aside ) {
					$print_body();
				}

				if ( $has_image && $has_aside ) {
					$print_aside();
				}
				?>
			</div>
			<?php } ?>
			<?php if ( $has_image ) { ?>
				<div class="cb-text-media__media">
					<?= wp_get_attachment_image( $image['ID'], 'large', false, array( 'loading' => 'lazy' ) ); ?>
				</div>
			<?php } elseif ( $has_aside ) { ?>
				<?php $print_aside(); ?>
			<?php } else { ?>
				<div class="cb-text-media__side">
					<?php $print_body(); ?>
				</div>
			<?php } ?>
		</div>
	</div>
</section>
