<?php
/**
 * Block template for CB Image Text Checklist.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

$section_id     = $block['anchor'] ?? $block['id'] ?? wp_unique_id( 'cb-itc-' );
$extra          = $block['className'] ?? '';
$heading        = get_field( 'heading' );
$body           = get_field( 'body' );
$list_label     = get_field( 'list_label' );
$items          = get_field( 'checklist' );
$image          = get_field( 'image' );
$image_2        = get_field( 'image_2' );
$image_3        = get_field( 'image_3' );
$image_position = get_field( 'image_position' ) ?: 'right';

$has_any_image = ! empty( $image['ID'] ) || ! empty( $image_2['ID'] ) || ! empty( $image_3['ID'] );
?>
<section class="cb-image-text-checklist cb-image-text-checklist--white <?= esc_attr( $extra ); ?>" id="<?= esc_attr( $section_id ); ?>">
	<div class="container">
		<div class="row gy-5 align-items-center">
			<div class="col-lg-6 <?= 'left' === $image_position ? 'order-lg-last' : ''; ?>">
				<div class="cb-image-text-checklist__copy  cb-gsap-fade">
					<?php
					if ( $heading ) {
						?>
					<h2><?= esc_html( $heading ); ?></h2>
						<?php
					}
					if ( $body ) {
						?>
					<div class="cb-prose"><?= wp_kses_post( wpautop( $body ) ); ?></div>
						<?php
					}
					if ( $list_label ) {
						?>
					<p class="cb-small-label"><?= esc_html( $list_label ); ?></p>
						<?php
					}
					if ( $items ) {
						?>
						<ul class="cb-check-list">
							<?php
							foreach ( $items as $item ) {
								if ( ! empty( $item['text'] ) ) {
									?>
							<li><span class="cb-check" aria-hidden="true">&#10003;</span><?= esc_html( $item['text'] ); ?></li>
									<?php
								}
							}
							?>
						</ul>
						<?php
					}
					?>
				</div>
			</div>
			<?php
			if ( $has_any_image ) {
				?>
			<div class="col-lg-6 <?= 'left' === $image_position ? 'order-lg-first' : ''; ?>">
				<div class="cb-image-text-checklist__media">
					<svg class="cb-image-text-checklist__dots" viewBox="0 0 518 518" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
						<path d="M258.85 450.87a33.5 33.5 0 0 0-33.56 33.56A33.5 33.5 0 0 0 258.85 518a33.5 33.5 0 0 0 33.57-33.57 33.5 33.5 0 0 0-33.57-33.56ZM258.85 0a33.5 33.5 0 0 0-33.56 33.57 33.5 33.5 0 0 0 33.56 33.56 33.5 33.5 0 0 0 33.57-33.56A33.5 33.5 0 0 0 258.85 0ZM354.96 425.03c-15.94 9.17-21.5 29.7-12.32 45.88 9.18 15.94 29.7 21.5 45.88 12.31 16.18-9.17 21.5-29.7 12.32-45.88-9.42-15.94-29.95-21.5-45.88-12.31ZM129.43 34.77c-16.18 9.18-21.5 29.7-12.32 45.89 9.18 15.94 29.7 21.5 45.88 12.31 16.18-9.17 21.5-29.7 12.32-45.88a33.32 33.32 0 0 0-45.88-12.32ZM470.86 342.68c-16.18-9.18-36.7-3.87-45.88 12.31-9.17 15.94-3.86 36.71 12.32 45.89 16.18 9.17 36.7 3.86 45.88-12.32 9.17-16.18 3.86-36.7-12.32-45.88ZM80.65 117.12c-16.18-9.17-36.7-3.86-45.88 12.32-9.17 16.18-3.86 36.7 12.32 45.88 16.17 9.18 36.7 3.87 45.88-12.31 9.17-15.94 3.62-36.47-12.32-45.89ZM484.38 225.31a33.5 33.5 0 0 0-33.56 33.57 33.5 33.5 0 0 0 33.56 33.57 33.5 33.5 0 0 0 33.57-33.57 33.65 33.65 0 0 0-33.57-33.57ZM67.13 258.88a33.5 33.5 0 0 0-33.57-33.57A33.5 33.5 0 0 0 0 258.88a33.5 33.5 0 0 0 33.56 33.57 33.5 33.5 0 0 0 33.57-33.57ZM470.86 175.32c15.94-9.17 21.5-29.7 12.32-45.88-9.18-16.18-29.7-21.5-45.88-12.32-15.94 9.18-21.5 29.7-12.32 45.89a33.64 33.64 0 0 0 45.88 12.31ZM47.09 342.68c-15.94 9.17-21.5 29.7-12.32 45.88 9.18 15.94 29.7 21.5 45.88 12.32 15.94-9.18 21.5-29.7 12.31-45.89-9.41-16.18-29.94-21.73-45.87-12.31ZM388.52 34.77c-15.94-9.17-36.7-3.86-45.88 12.32-9.17 16.18-3.86 36.7 12.32 45.88 15.93 9.18 36.7 3.87 45.88-12.31 9.17-16.18 3.62-36.7-12.32-45.88ZM163 425.03c-15.95-9.18-36.71-3.87-45.89 12.31-9.17 16.18-3.86 36.7 12.32 45.89 15.93 9.17 36.7 3.86 45.88-12.32a33.65 33.65 0 0 0-12.32-45.88Z" fill="currentColor"/>
					</svg>
					<?php if ( ! empty( $image['ID'] ) ) { ?>
					<figure class="cb-image-text-checklist__fig cb-image-text-checklist__fig--1 rellax" data-rellax-speed="0.3">
						<?= wp_get_attachment_image( $image['ID'], 'large' ); ?>
					</figure>
					<?php } ?>
					<?php if ( ! empty( $image_2['ID'] ) ) { ?>
					<figure class="cb-image-text-checklist__fig cb-image-text-checklist__fig--2 rellax" data-rellax-speed="0.6">
						<?= wp_get_attachment_image( $image_2['ID'], 'large' ); ?>
					</figure>
					<?php } ?>
					<?php if ( ! empty( $image_3['ID'] ) ) { ?>
					<figure class="cb-image-text-checklist__fig cb-image-text-checklist__fig--3 rellax" data-rellax-speed="-0.4">
						<?= wp_get_attachment_image( $image_3['ID'], 'large' ); ?>
					</figure>
					<?php } ?>
				</div>
			</div>
				<?php
			}
			?>
		</div>
	</div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
	var section = document.getElementById(<?= wp_json_encode( $section_id ); ?>);
	if (!section) return;

	var rellaxEls = section.querySelectorAll('.rellax');

	function updateParallax() {
		if (window.innerWidth < 768) {
			rellaxEls.forEach(function (el) { el.style.transform = ''; });
			return;
		}
		var rect = section.getBoundingClientRect();
		var windowHeight = window.innerHeight;
		if (rect.bottom > 0 && rect.top < windowHeight) {
			rellaxEls.forEach(function (el) {
				var speed = parseFloat(el.getAttribute('data-rellax-speed') || '0');
				var progress = (windowHeight - rect.top) / (windowHeight + rect.height);
				progress = Math.max(0, Math.min(1, progress));
				var y = ((progress - 0.5) * speed * 80) - 40;
				el.style.transform = 'translate3d(0,' + y.toFixed(1) + 'px,0)';
			});
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

	window.addEventListener('scroll', onScroll, { passive: true });
	window.addEventListener('resize', onScroll);
	updateParallax();
});
</script>
