<?php
/**
 * Block template for CB Gallery.
 *
 * A set of photographs, either as a carousel or a plain grid. The carousel uses
 * Swiper, which the theme already loads on every page, so it costs nothing extra
 * here.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

$section_id = $block['anchor'] ?? $block['id'] ?? wp_unique_id( 'cb-gallery-' );
$extra      = $block['className'] ?? '';
$heading    = get_field( 'heading' );
$intro      = get_field( 'intro' );
$images     = get_field( 'images' );
$layout     = get_field( 'layout' ) ? get_field( 'layout' ) : 'carousel';
$per_view   = (int) get_field( 'per_view' ) ? (int) get_field( 'per_view' ) : 3;
$captions   = (bool) get_field( 'show_captions' );

if ( empty( $images ) ) {
	return;
}

// A carousel of one has nothing to move through, and Swiper's loop misbehaves
// when there are fewer slides than it shows at once.
if ( 'carousel' === $layout && count( $images ) <= $per_view ) {
	$layout = 'grid';
}

$grid_class = array(
	2 => 'col-md-6',
	3 => 'col-lg-4 col-md-6',
	4 => 'col-lg-3 col-md-6',
)[ $per_view ] ?? 'col-lg-4 col-md-6';

/**
 * One gallery figure.
 *
 * @param integer $image_id The attachment ID.
 * @param boolean $captions Whether to show the media library caption.
 * @return void
 */
$cb_gallery_figure = function ( $image_id, $captions ) {
	$caption = $captions ? wp_get_attachment_caption( $image_id ) : '';
	?>
	<figure class="cb-gallery__figure">
		<?= wp_get_attachment_image( $image_id, 'large', false, array( 'loading' => 'lazy' ) ); ?>
		<?php if ( $caption ) { ?>
		<figcaption><?= esc_html( $caption ); ?></figcaption>
		<?php } ?>
	</figure>
	<?php
};
?>
<section class="cb-gallery cb-gallery--<?= esc_attr( $layout ); ?> <?= esc_attr( $extra ); ?>" id="<?= esc_attr( $section_id ); ?>">
	<div class="container">
		<?php if ( $heading || $intro ) { ?>
		<div class="cb-section-head pb-5">
			<?php if ( $heading ) { ?>
			<h2><?= esc_html( $heading ); ?></h2>
			<?php } ?>
			<?php if ( $intro ) { ?>
			<p><?= esc_html( $intro ); ?></p>
			<?php } ?>
		</div>
		<?php } ?>
		<?php if ( 'carousel' === $layout ) { ?>
		<div class="swiper cb-gallery__swiper" data-per-view="<?= esc_attr( $per_view ); ?>">
			<div class="swiper-wrapper">
				<?php foreach ( $images as $image_id ) { ?>
				<div class="swiper-slide cb-gallery__slide">
					<?php $cb_gallery_figure( $image_id, $captions ); ?>
				</div>
				<?php } ?>
			</div>
			<div class="cb-gallery__controls">
				<button type="button" class="cb-gallery__nav cb-gallery__nav--prev" aria-label="<?php esc_attr_e( 'Previous image', 'cb-andwislifts2026' ); ?>">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7"></path></svg>
				</button>
				<div class="cb-gallery__pagination"></div>
				<button type="button" class="cb-gallery__nav cb-gallery__nav--next" aria-label="<?php esc_attr_e( 'Next image', 'cb-andwislifts2026' ); ?>">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"></path></svg>
				</button>
			</div>
		</div>
		<?php } else { ?>
		<div class="row g-3">
			<?php foreach ( $images as $image_id ) { ?>
			<div class="<?= esc_attr( $grid_class ); ?>">
				<?php $cb_gallery_figure( $image_id, $captions ); ?>
			</div>
			<?php } ?>
		</div>
		<?php } ?>
	</div>
</section>

<?php
// Emitted on wp_footer rather than inline, matching the theme's other blocks -
// an inline <script> in a block's output breaks its editor preview.
if ( 'carousel' === $layout ) {
	add_action(
		'wp_footer',
		function () use ( $section_id ) {
			?>
<script>
document.addEventListener('DOMContentLoaded', function () {
	var block = document.getElementById(<?= wp_json_encode( $section_id ); ?>);
	if (!block) return;

	var el = block.querySelector('.cb-gallery__swiper');
	if (!el) return;

	// Swiper is enqueued site-wide, but never assume it arrived.
	if (!window.Swiper) {
		block.classList.add('cb-gallery--no-js');
		return;
	}

	var perView = parseInt(el.getAttribute('data-per-view'), 10) || 3;

	new window.Swiper(el, {
		slidesPerView: 1,
		spaceBetween: 16,
		loop: true,
		watchOverflow: true,
		keyboard: { enabled: true },
		a11y: {
			enabled: true,
			prevSlideMessage: 'Previous image',
			nextSlideMessage: 'Next image'
		},
		pagination: {
			el: block.querySelector('.cb-gallery__pagination'),
			clickable: true
		},
		navigation: {
			prevEl: block.querySelector('.cb-gallery__nav--prev'),
			nextEl: block.querySelector('.cb-gallery__nav--next')
		},
		breakpoints: {
			576: { slidesPerView: Math.min(2, perView) },
			992: { slidesPerView: perView }
		}
	});
});
</script>
			<?php
		},
		9999
	);
}
