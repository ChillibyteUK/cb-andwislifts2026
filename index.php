<?php
/**
 * Template for displaying the blog index page.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

$page_for_posts = get_option( 'page_for_posts' );

$block_id = 'blog-index-hero';

$hero_id          = get_post_thumbnail_id( $page_for_posts );
$hero_url         = $hero_id ? wp_get_attachment_image_url( $hero_id, 'full' ) : '';
$posts_page_intro = $page_for_posts ? get_post_field( 'post_excerpt', $page_for_posts ) : '';

get_header();

?>
<main id="main">
	<section id="<?= esc_attr( $block_id ); ?>" class="cb-hero cb-hero--bottom-curve">
		<?php
		if ( $hero_url ) {
			?>
		<div class="cb-hero__bg" style="background-image:url('<?= esc_url( $hero_url ); ?>');"></div>
			<?php
		}
		?>
		<div class="cb-hero__scrim"></div>
		<div class="container">
			<h1><?= esc_html( get_the_title( $page_for_posts ) ); ?></h1>
			<?php
			if ( $posts_page_intro ) {
				?>
			<p class="cb-hero__intro"><?= esc_html( $posts_page_intro ); ?></p>
				<?php
			}
			?>
		</div>
	</section>
    <section class="cb-post-index mt-5">
        <div class="container pb-5">
            <?php
            $all_categories = get_categories(
				array(
					'hide_empty' => true,
					'orderby'    => 'name',
					'order'      => 'ASC',
				)
			);

            if ( count( $all_categories ) > 1 ) {
				?>
            <div class="cb-post-index__filter" role="group" aria-label="Filter news by topic">
                <button type="button" class="cb-post-index__chip is-active" data-filter="all">All</button>
				<?php
				foreach ( $all_categories as $category ) {
					?>
                <button type="button" class="cb-post-index__chip" data-filter="<?= esc_attr( $category->slug ); ?>"><?= esc_html( $category->name ); ?></button>
					<?php
				}
				?>
            </div>
				<?php
			}
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
					$cat_slugs      = ( $categories && ! is_wp_error( $categories ) ) ? implode( ' ', wp_list_pluck( $categories, 'slug' ) ) : '';
					?>
				<a href="<?= esc_url( get_permalink() ); ?>" class="cb-post-card" data-categories="<?= esc_attr( $cat_slugs ); ?>">
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
            <p class="cb-post-index__note">Media enquiries: <a href="mailto:media@andwislifts.com">media@andwislifts.com</a></p>
        </div>
    </section>
</main>
<?php
add_action(
	'wp_footer',
	function () {
		?>
<script>
document.addEventListener('DOMContentLoaded', function () {
	var index = document.querySelector('.cb-post-index');
	if (!index) return;

	var chips = index.querySelectorAll('.cb-post-index__chip');
	var cards = index.querySelectorAll('.cb-post-card');
	if (!chips.length) return;

	chips.forEach(function (chip) {
		chip.addEventListener('click', function () {
			var filter = chip.getAttribute('data-filter');

			chips.forEach(function (c) { c.classList.remove('is-active'); });
			chip.classList.add('is-active');

			cards.forEach(function (card) {
				var cats = (card.getAttribute('data-categories') || '').split(' ');
				card.hidden = !(filter === 'all' || cats.indexOf(filter) !== -1);
			});
		});
	});
});
</script>
		<?php
	},
	9999
);
?>
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