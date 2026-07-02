<?php
/**
 * Template for displaying single posts.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;
get_header();
?>
<main id="main" class="cb-post">
	<div class="container pt-4 pb-5">
		<?php
		if ( function_exists( 'yoast_breadcrumb' ) ) {
			yoast_breadcrumb( '<div id="breadcrumbs" class="mb-4">', '</div>' );
		}
		?>
		<div class="row">
			<div class="col-lg-8">
				<?php if ( has_post_thumbnail() ) { ?>
				<div class="cb-post__hero">
					<?= get_the_post_thumbnail( get_the_ID(), 'full', array( 'class' => 'cb-post__hero-img' ) ); ?>
				</div>
				<?php } ?>
				<h1 class="cb-post__title"><?= esc_html( get_the_title() ); ?></h1>
				<div class="cb-post__meta">
					<span><?= esc_html( get_the_date( 'j M Y' ) ); ?></span>
					<span><?= esc_html( estimate_reading_time_in_minutes( get_the_content() ) ); ?> min read</span>
				</div>
				<div class="cb-post__content">
					<?= wp_kses_post( get_the_content() ); ?>
				</div>
				<?php
				$prev = get_previous_post();
				$next = get_next_post();

				if ( $prev || $next ) {
					$justify = $prev && $next ? 'justify-content-between' : ( $next ? 'justify-content-end' : 'justify-content-start' );
					?>
				<nav class="cb-post__nav d-flex <?= esc_attr( $justify ); ?>" aria-label="Post navigation">
					<?php if ( $prev ) { ?>
					<a href="<?= esc_url( get_permalink( $prev ) ); ?>" class="button button--secondary">&larr; Previous</a>
					<?php } ?>
					<?php if ( $next ) { ?>
					<a href="<?= esc_url( get_permalink( $next ) ); ?>" class="button button--primary">Next &rarr;</a>
					<?php } ?>
				</nav>
					<?php
				}
				?>
			</div>
			<div class="col-lg-4">
				<?php
				$q = new WP_Query(
					array(
						'post_type'      => 'post',
						'posts_per_page' => 5,
						'post__not_in'   => array( get_the_ID() ),
						'orderby'        => 'date',
						'order'          => 'DESC',
					)
				);
				if ( $q->have_posts() ) {
					?>
				<aside class="cb-post-sidebar">
					<h2 class="cb-post-sidebar__title has-underline">Latest News &amp; Advice</h2>
					<?php
					while ( $q->have_posts() ) {
						$q->the_post();
						?>
					<a class="cb-post-sidebar__item" href="<?= esc_url( get_permalink() ); ?>">
						<?php if ( has_post_thumbnail() ) { ?>
						<div class="cb-post-sidebar__image-wrap">
							<?= get_the_post_thumbnail( get_the_ID(), 'medium', array( 'class' => 'cb-post-sidebar__image' ) ); ?>
						</div>
						<?php } ?>
						<div class="cb-post-sidebar__body">
							<div class="cb-post-sidebar__meta">
								<span><?= esc_html( get_the_date( 'j M Y' ) ); ?></span>
								<span><?= esc_html( estimate_reading_time_in_minutes( get_the_content() ) ); ?> min read</span>
							</div>
							<div class="cb-post-sidebar__post-title"><?= esc_html( get_the_title() ); ?></div>
						</div>
					</a>
					<?php } ?>
				</aside>
					<?php
					wp_reset_postdata();
				}
				?>
			</div>
		</div>
	</div>
</main>
<?php
get_footer();
