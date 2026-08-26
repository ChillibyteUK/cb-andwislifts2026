<?php
/**
 * Block template for CB News Index.
 *
 * Latest posts as cards, reusing the .cb-post-card markup from index.php so the
 * homepage teaser and the news archive look identical.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

$section_id = $block['anchor'] ?? $block['id'] ?? wp_unique_id( 'cb-news-index-' );
$extra      = $block['className'] ?? '';
$heading    = get_field( 'heading' );
$intro      = get_field( 'intro' );
$limit      = (int) get_field( 'limit' );
$view_all   = get_field( 'view_all' );

$query = new WP_Query(
	array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => $limit > 0 ? $limit : 3,
		'orderby'        => 'date',
		'order'          => 'DESC',
		'no_found_rows'  => true,
	)
);

if ( ! $query->have_posts() ) {
	return;
}
?>
<section class="cb-news-index <?= esc_attr( $extra ); ?>" id="<?= esc_attr( $section_id ); ?>">
	<div class="container">
		<div class="cb-section-head pb-5">
			<?php
			if ( $heading ) {
				?>
			<h2><?= esc_html( $heading ); ?></h2>
				<?php
			}
			if ( $intro ) {
				?>
			<p><?= esc_html( $intro ); ?></p>
				<?php
			}
			?>
		</div>
		<div class="cb-post-grid">
			<?php
			foreach ( $query->posts as $item ) {
				$categories = get_the_category( $item->ID );
				$first      = ! empty( $categories ) && ! is_wp_error( $categories ) ? $categories[0] : null;
				?>
			<a href="<?= esc_url( get_permalink( $item ) ); ?>" class="cb-post-card">
				<div class="cb-post-card__image-wrap">
					<?php
					if ( has_post_thumbnail( $item->ID ) ) {
						echo get_the_post_thumbnail( $item->ID, 'medium_large', array( 'class' => 'cb-post-card__image' ) );
					}
					if ( $first ) {
						?>
					<span class="cb-post-card__cat"><?= esc_html( $first->name ); ?></span>
						<?php
					}
					?>
				</div>
				<div class="cb-post-card__body">
					<div class="cb-post-card__meta">
						<span><?= esc_html( get_the_date( 'j M Y', $item ) ); ?></span>
					</div>
					<h3 class="cb-post-card__title"><?= esc_html( get_the_title( $item ) ); ?></h3>
					<div class="cb-post-card__excerpt"><?= esc_html( get_the_excerpt( $item ) ); ?></div>
					<span class="cb-post-card__cta">Read more</span>
				</div>
			</a>
				<?php
			}
			wp_reset_postdata();
			?>
		</div>
		<?php
		if ( ! empty( $view_all['url'] ) ) {
			?>
		<p class="cb-news-index__more">
			<a class="cb-post-card__cta" href="<?= esc_url( $view_all['url'] ); ?>"><?= esc_html( $view_all['title'] ? $view_all['title'] : 'View all news' ); ?></a>
		</p>
			<?php
		}
		?>
	</div>
</section>
