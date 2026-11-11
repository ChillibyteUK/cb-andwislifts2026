<?php
/**
 * News: the article card and the related articles.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

/**
 * An article's categories, without WordPress's catch-all one.
 *
 * @param integer $post_id The post ID.
 * @return WP_Term[]
 */
function cb_news_categories( $post_id ) {
	$categories = get_the_category( $post_id );

	if ( ! $categories || is_wp_error( $categories ) ) {
		return array();
	}

	return array_values(
		array_filter(
			$categories,
			static function ( $category ) {
				return 'uncategorized' !== $category->slug;
			}
		)
	);
}

/**
 * Print an article's categories as tags.
 *
 * @param integer $post_id The post ID.
 * @param string  $extra   Extra class for the list.
 * @return void
 */
function cb_news_tags_list( $post_id, $extra = '' ) {
	$categories = cb_news_categories( $post_id );

	if ( ! $categories ) {
		return;
	}

	echo '<ul class="cb-case-tags ' . esc_attr( $extra ) . '">';

	foreach ( $categories as $category ) {
		echo '<li>' . esc_html( $category->name ) . '</li>';
	}

	echo '</ul>';
}

/**
 * Output a single article card: photo, title, excerpt and category. The whole
 * card is one link, carried by the title.
 *
 * @param WP_Post|integer $item  The post or its ID.
 * @param integer         $words Words to trim the excerpt to.
 * @return void
 */
function cb_news_card( $item, $words = 22 ) {
	$item = get_post( $item );

	if ( ! $item ) {
		return;
	}

	$image   = get_post_thumbnail_id( $item->ID );
	$excerpt = wp_trim_words( get_the_excerpt( $item ), (int) $words );
	?>
	<article class="cb-news-card">
		<div class="cb-news-card__media">
			<?php
			if ( $image ) {
				echo wp_get_attachment_image( $image, 'medium_large', false, array( 'loading' => 'lazy' ) );
			}
			?>
		</div>
		<div class="cb-news-card__body">
			<h3><a href="<?= esc_url( get_permalink( $item ) ); ?>"><?= esc_html( get_the_title( $item ) ); ?></a></h3>
			<?php if ( $excerpt ) { ?>
			<p class="cb-news-card__summary"><?= esc_html( $excerpt ); ?></p>
			<?php } ?>
			<?php cb_news_tags_list( $item->ID, 'cb-news-card__tags' ); ?>
		</div>
	</article>
	<?php
}

/**
 * Up to three other articles: from the same categories first, then the latest.
 *
 * @param integer $post_id The article being read.
 * @return WP_Post[]
 */
function cb_news_related( $post_id ) {
	$base = array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => 3,
		'post__not_in'   => array( $post_id ),
		'no_found_rows'  => true,
	);

	$related    = array();
	$categories = wp_list_pluck( cb_news_categories( $post_id ), 'term_id' );

	if ( $categories ) {
		foreach ( get_posts( $base + array( 'category__in' => $categories ) ) as $item ) {
			$related[ $item->ID ] = $item;
		}
	}

	if ( count( $related ) < 3 ) {
		foreach ( get_posts( array( 'posts_per_page' => 6 ) + $base ) as $item ) {
			$related[ $item->ID ] = $related[ $item->ID ] ?? $item;
		}
	}

	return array_slice( $related, 0, 3 );
}
