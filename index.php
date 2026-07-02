<?php
/**
 * Template for displaying the blog index page.
 *
 * @package cb-starlight2025
 */

defined( 'ABSPATH' ) || exit;

$page_for_posts = get_option( 'page_for_posts' );

$block_id = 'blog-index-hero';

get_header();

?>
<main id="main">
	<section id="<?= esc_attr( $block_id ); ?>" class="cb-hero cb-hero--short">
		<div class="cb-hero__bg" aria-hidden="true">
			<?= get_the_post_thumbnail( $page_for_posts, 'full', array( 'class' => 'cb-hero__img' ) ); ?>
		</div>
		<div class="cb-hero__overlay" aria-hidden="true"></div>
		<div class="container">
			<div class="row">
				<div class="col-md-8 has-white-color">
					<h1 class="mb-4"><?= esc_html( get_the_title( $page_for_posts ) ); ?></h1>
					<div class="cb-hero__ctas">
						<a href="/contact/" class="button button--primary">Contact our team</a>
					</div>
				</div>
			</div>
		</div>
	</section>
    <section class="cb-post-index mt-5">
        <div class="container pb-5">
            <?php
			// phpcs:disable
			/*
            // Get all categories for filter buttons.
            $all_categories = get_categories(
				array(
					'hide_empty' => true,
					'orderby'    => 'name',
					'order'      => 'ASC',
				)
			);

            if ( ! empty( $all_categories ) ) {
                ?>
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="filter-buttons text-center">
                            <button class="btn btn-outline-primary filter-btn active" data-filter="all">All</button>
                            <?php
							foreach ( $all_categories as $category ) {
								?>
                                <button class="btn btn-outline-primary filter-btn" data-filter="<?= esc_attr( $category->slug ); ?>"><?= esc_html( $category->name ); ?></button>
                            	<?php
							}
							?>
                        </div>
                    </div>
                </div>
                <?php
            }
			*/
			// phpcs:enable
            ?>
            <div class="cb-post-grid">
            <?php
            $q = new WP_Query(
				array(
					'post_type'      => 'post',
					'post_status'    => array( 'publish', 'future' ),
					'orderby'        => 'date',
					'order'          => 'DESC',
					'posts_per_page' => -1,
				)
			);

            if ( $q->have_posts() ) {
				while ( $q->have_posts() ) {
					$q->the_post();
					$categories     = get_the_category();
					$first_category = ( ! empty( $categories ) && ! is_wp_error( $categories ) ) ? $categories[0] : null;
					?>
				<a href="<?= esc_url( get_permalink() ); ?>" class="cb-post-card">
					<div class="cb-post-card__image-wrap">
						<?php
						if ( has_post_thumbnail() ) {
							echo get_the_post_thumbnail( get_the_ID(), 'medium_large', array( 'class' => 'cb-post-card__image' ) );
						}
						?>
						<?php if ( $first_category ) { ?>
						<span class="cb-post-card__cat"><?= esc_html( $first_category->name ); ?></span>
						<?php } ?>
					</div>
					<div class="cb-post-card__body">
						<div class="cb-post-card__meta">
							<span><?= esc_html( get_the_date( 'j M Y' ) ); ?></span>
							<span><?= esc_html( estimate_reading_time_in_minutes( get_the_content() ) ); ?> min read</span>
						</div>
						<h3 class="cb-post-card__title"><?= esc_html( get_the_title() ); ?></h3>
						<div class="cb-post-card__excerpt"><?= esc_html( get_the_excerpt() ); ?></div>
						<span class="cb-post-card__cta">Read more</span>
					</div>
				</a>
					<?php
				}
            } else {
                echo '<p>No posts found.</p>';
            }

            wp_reset_postdata();
            ?>
            </div>
        </div>
    </section>
</main>
<?php
add_action(
	'wp_footer',
	function () use ( $block_id ) {
		static $printed = false;
		if ( $printed ) {
			return;
		}
		$printed = true;
		?>
<script>
(function () {
	if ( window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) return;

	const section = document.querySelector( '.cb-hero' );
	if ( ! section ) return;

	const targets = [
		section.querySelector( 'h1' ),
		section.querySelector( '.font-lede' ),
		section.querySelector( '.cb-hero__intro' ),
		section.querySelector( '.cb-hero__ctas' ),
	].filter( Boolean );

	gsap.from( targets, {
		y: 20,
		opacity: 0,
		duration: 0.6,
		ease: 'power2.out',
		stagger: 0.15,
	} );
})();
</script>
<script>
(function () {
	if ( window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) return;

	gsap.registerPlugin( ScrollTrigger );

	const cards = document.querySelectorAll( '.cb-post-card' );
	if ( ! cards.length ) return;

	gsap.from( cards, {
		y: 30,
		opacity: 0,
		duration: 0.5,
		ease: 'power2.out',
		stagger: 0.08,
		scrollTrigger: {
			trigger: '.cb-post-index',
			start: 'top 85%',
			once: true,
		},
	} );
})();
</script>
		<?php
	},
	20
);

get_footer();