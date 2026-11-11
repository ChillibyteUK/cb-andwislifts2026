<?php
/**
 * Block template for CB Carousel.
 *
 * A row of cards the visitor can drag, swipe or step through with the dots
 * underneath. Two kinds: sectors, as tall photo cards that run off both edges
 * of the page, and the latest news, as three article cards.
 *
 * Swiper is already loaded site-wide. Without JavaScript the cards sit in a
 * row that scrolls sideways.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

$section_id = $block['anchor'] ?? $block['id'] ?? wp_unique_id( 'cb-carousel-' );
$extra      = $block['className'] ?? '';
$heading    = get_field( 'heading' );
$intro      = get_field( 'intro' );
$outro      = get_field( 'outro' );
$cta        = get_field( 'cta' );
$source     = 'post' === get_field( 'source' ) ? 'post' : 'sector';
$limit      = (int) get_field( 'limit' );
$limit      = $limit > 0 ? $limit : 8;

$items = get_posts(
	array(
		'post_type'      => $source,
		'post_status'    => 'publish',
		'posts_per_page' => $limit,
		'orderby'        => 'post' === $source ? array( 'date' => 'DESC' ) : array(
			'menu_order' => 'ASC',
			'title'      => 'ASC',
		),
		'no_found_rows'  => true,
	)
);

// Nothing to show yet, for instance news before the first article: say nothing.
if ( ! $items ) {
	return;
}
?>
<section class="cb-carousel cb-carousel--<?= esc_attr( $source ); ?> <?= esc_attr( $extra ); ?>" id="<?= esc_attr( $section_id ); ?>" data-cb-carousel="<?= esc_attr( $source ); ?>">
	<?php if ( $heading || $intro ) { ?>
		<div class="container cb-carousel__head">
			<?php if ( $heading ) { ?>
				<h2><?= cb_highlight( $heading ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h2>
			<?php } ?>
			<?php if ( $intro ) { ?>
				<p><?= esc_html( $intro ); ?></p>
			<?php } ?>
		</div>
	<?php } ?>

	<div class="cb-carousel__stage<?= 'post' === $source ? ' container' : ''; ?>">
		<div class="swiper cb-carousel__swiper">
			<ul class="swiper-wrapper cb-carousel__track">
				<?php
				foreach ( $items as $item ) {
					$image = get_post_thumbnail_id( $item );
					$title = get_the_title( $item );

					if ( 'sector' === $source ) {
						$summary = function_exists( 'get_field' ) ? (string) get_field( 'card_summary', $item->ID ) : '';
						?>
						<li class="swiper-slide cb-carousel__slide">
							<a class="cb-carousel__card cb-carousel__card--overlay" href="<?= esc_url( get_permalink( $item ) ); ?>">
								<?= $image ? wp_get_attachment_image( $image, 'large', false, array( 'loading' => 'lazy' ) ) : ''; ?>
								<span class="cb-carousel__card-body">
									<span class="cb-carousel__card-title"><?= esc_html( $title ); ?></span>
									<?php if ( $summary ) { ?>
										<span class="cb-carousel__card-text"><?= esc_html( wp_trim_words( $summary, 14, '...' ) ); ?></span>
									<?php } ?>
								</span>
							</a>
						</li>
						<?php
						continue;
					}

					$categories = get_the_category( $item->ID );
					?>
					<li class="swiper-slide cb-carousel__slide">
						<a class="cb-carousel__card cb-carousel__card--article" href="<?= esc_url( get_permalink( $item ) ); ?>">
							<span class="cb-carousel__card-image"><?= $image ? wp_get_attachment_image( $image, 'medium_large', false, array( 'loading' => 'lazy' ) ) : ''; ?></span>
							<span class="cb-carousel__card-body">
								<span class="cb-carousel__card-title"><?= esc_html( $title ); ?></span>
								<span class="cb-carousel__card-text"><?= esc_html( wp_trim_words( get_the_excerpt( $item ), 22, '...' ) ); ?></span>
								<?php if ( $categories ) { ?>
									<span class="cb-carousel__card-pill"><?= esc_html( $categories[0]->name ); ?></span>
								<?php } ?>
							</span>
						</a>
					</li>
					<?php
				}
				?>
			</ul>
		</div>
		<div class="cb-carousel__dots"></div>
	</div>

	<?php if ( $outro || ! empty( $cta['url'] ) ) { ?>
		<div class="container cb-carousel__foot">
			<?php if ( $outro ) { ?>
				<p><?= esc_html( $outro ); ?></p>
			<?php } ?>
			<?php if ( ! empty( $cta['url'] ) ) { ?>
				<a class="btn <?= 'post' === $source ? 'btn-ghost' : 'btn-primary'; ?> cb-carousel__button" href="<?= esc_url( $cta['url'] ); ?>"<?= cb_link_target( $cta ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?= esc_html( $cta['title'] ); ?></a>
			<?php } ?>
		</div>
	<?php } ?>
</section>
<?php
add_action( 'wp_footer', 'cb_carousel_script', 30 );

if ( ! function_exists( 'cb_carousel_script' ) ) {
	/**
	 * Start every CB Carousel on the page. Printed once.
	 *
	 * @return void
	 */
	function cb_carousel_script() {
		static $done = false;

		if ( $done ) {
			return;
		}

		$done = true;
		?>
		<script id="cb-carousel-js">
		document.addEventListener('DOMContentLoaded', function () {
			if (typeof Swiper === 'undefined') { return; }

			Array.prototype.forEach.call(document.querySelectorAll('[data-cb-carousel]'), function (root) {
				var slides = root.querySelectorAll('.swiper-slide').length;
				var options = {
					grabCursor: true,
					watchOverflow: true,
					keyboard: { enabled: true, onlyInViewport: true },
					pagination: { el: root.querySelector('.cb-carousel__dots'), clickable: true }
				};

				if (root.getAttribute('data-cb-carousel') === 'sector') {
					// Cards keep their own width and run off both edges, with
					// the current one in the middle.
					options.slidesPerView = 'auto';
					options.spaceBetween = 20;
					options.centeredSlides = true;
					options.loop = slides > 4;
				} else {
					options.slidesPerView = 1.1;
					options.spaceBetween = 16;
					options.breakpoints = {
						768: { slidesPerView: 2, spaceBetween: 24 },
						1200: { slidesPerView: 3, spaceBetween: 30 }
					};
				}

				root.classList.add('cb-carousel--ready');
				new Swiper(root.querySelector('.swiper'), options);
			});
		});
		</script>
		<?php
	}
}
