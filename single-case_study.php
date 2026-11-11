<?php
/**
 * Single Case Study template.
 *
 * The page's frame is fixed here: a green hero with the tags, kicker, title
 * and intro, the pictures across its bottom edge, then the story, an optional
 * quote, three more case studies and the closing band.
 *
 * The story itself is the post's own blocks - CB Text Media sections in the
 * main - so a case study can have as many sections as it needs. The hero and
 * the story's layout are shared with news articles: inc/cb-story.php.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

get_header();

the_post();

$case_id  = get_the_ID();
$quote    = trim( (string) get_field( 'quote', $case_id ) );
$quote_by = trim( (string) get_field( 'quote_by', $case_id ) );

ob_start();
cb_case_study_tags_list( cb_case_study_tags( $case_id ), 'cb-case-tags--light' );
$case_tags = (string) ob_get_clean();
?>
<main id="main" class="cb-case-study">
	<?php
	cb_story_hero(
		array(
			'tags'   => $case_tags,
			'kicker' => trim( (string) get_field( 'client_name', $case_id ) ),
			'title'  => get_the_title(),
			'intro'  => trim( (string) get_field( 'hero_intro', $case_id ) ),
			'images' => cb_story_images( $case_id ),
		)
	);

	cb_story_content();

	if ( $quote ) {
		?>
	<section class="cb-case-quote">
		<div class="container">
			<blockquote>
				<span class="cb-case-quote__mark" aria-hidden="true">&ldquo;</span>
				<p><?= nl2br( esc_html( $quote ) ); ?></p>
				<?php if ( $quote_by ) { ?>
				<footer><?= esc_html( $quote_by ); ?></footer>
				<?php } ?>
			</blockquote>
		</div>
	</section>
		<?php
	}

	// Three more: from the same sector first, then the rest in their usual order.
	$more = array();

	foreach ( (array) get_field( 'related_sectors', $case_id, false ) as $sector_id ) {
		foreach ( cb_get_sector_case_studies( $sector_id, 4 ) as $item ) {
			$more[ $item->ID ] = $item;
		}
	}

	unset( $more[ $case_id ] );

	if ( count( $more ) < 3 ) {
		$others = get_posts(
			array(
				'post_type'      => 'case_study',
				'post_status'    => 'publish',
				'posts_per_page' => 6,
				'post__not_in'   => array( $case_id ),
				'orderby'        => array(
					'menu_order' => 'ASC',
					'date'       => 'DESC',
				),
				'no_found_rows'  => true,
			)
		);

		foreach ( $others as $item ) {
			$more[ $item->ID ] = $more[ $item->ID ] ?? $item;
		}
	}

	$more = array_slice( $more, 0, 3 );

	if ( $more ) {
		?>
	<section class="cb-case-study__more">
		<div class="container">
			<h2><?php esc_html_e( 'More case studies', 'cb-andwislifts2026' ); ?></h2>
			<div class="cb-case-study-grid">
				<?php
				foreach ( $more as $item ) {
					cb_case_study_card( $item, 20 );
				}
				?>
			</div>
		</div>
	</section>
		<?php
	}

	// The closing band, the same on every case study.
	cb_story_closing_band(
		'Talk to us about a similar project',
		"Tell us about your building, we'll recommend the right solution and give you an honest view on cost and feasibility.",
		'Ask the experts',
		get_page_by_path( 'contact/ask-the-expert' ) ? '/contact/ask-the-expert/' : '/contact/'
	);
	?>
</main>
<?php
get_footer();
