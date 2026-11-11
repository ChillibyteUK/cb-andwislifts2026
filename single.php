<?php
/**
 * Template for a news article.
 *
 * The same frame as a case study: a green hero with the category, date, title
 * and - when the article has a hand-written excerpt - that as an intro, with
 * the pictures across its bottom edge. Then the article, three related ones,
 * the group cross-sell and the closing band. The hero and the article's
 * layout are in inc/cb-story.php.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

get_header();

the_post();

$article_id = get_the_ID();

ob_start();
cb_news_tags_list( $article_id, 'cb-case-tags--light' );
$article_tags = (string) ob_get_clean();
?>
<main id="main" class="cb-article">
	<?php
	cb_story_hero(
		array(
			'tags'    => $article_tags,
			'meta'    => get_the_date( 'j F Y' ),
			'title'   => get_the_title(),
			// Only an excerpt someone wrote: an automatic one would just repeat
			// the opening of the article underneath it.
			'intro'   => has_excerpt() ? wp_strip_all_tags( get_the_excerpt() ) : '',
			'images'  => cb_story_images( $article_id ),
			'variant' => 'article',
		)
	);

	cb_story_content();

	$related = cb_news_related( $article_id );

	if ( $related ) {
		?>
	<section class="cb-case-study__more cb-article__related">
		<div class="container">
			<h2><?php esc_html_e( 'Related news', 'cb-andwislifts2026' ); ?></h2>
			<div class="cb-case-study-grid">
				<?php
				foreach ( $related as $item ) {
					cb_news_card( $item );
				}
				?>
			</div>
		</div>
	</section>
		<?php
	}

	// Every article closes on the group cross-sell, as in the phase two design.
	cb_cross_sell_render(
		array(
			'card_heading' => __( 'Which services do you look after?', 'cb-andwislifts2026' ),
		)
	);

	cb_story_closing_band(
		'Have questions?',
		"Whether you need a quote, have a technical question, or need someone on site right now, we're here.",
		'Contact us',
		'/contact/'
	);
	?>
</main>
<?php
get_footer();
