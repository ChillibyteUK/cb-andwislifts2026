<?php
/**
 * Case study cards, and the related-case-studies section on sector pages.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

/**
 * Output a single case study card.
 *
 * Shared by the CB Case Study Index block and the related section injected into
 * sector pages, so the two cannot drift apart.
 *
 * @param WP_Post|integer $item  The case study post or its ID.
 * @param integer         $words Words to trim the card summary to.
 * @return void
 */
function cb_case_study_card( $item, $words = 26 ) {
	$item = get_post( $item );

	if ( ! $item ) {
		return;
	}

	$summary = get_field( 'card_summary', $item->ID );
	$image   = get_post_thumbnail_id( $item->ID );
	?>
	<a class="cb-case-study-card h-100" href="<?= esc_url( get_permalink( $item ) ); ?>">
		<?php
		if ( $image ) {
			?>
		<div class="cb-case-study-card__media"><?= wp_get_attachment_image( $image, 'medium_large' ); ?></div>
			<?php
		}
		?>
		<div class="cb-case-study-card__body">
			<h3><?= esc_html( get_the_title( $item ) ); ?></h3>
			<?php
			if ( $summary ) {
				?>
			<p><?= esc_html( wp_trim_words( $summary, (int) $words ) ); ?></p>
				<?php
			}
			?>
			<span class="cb-case-study-card__cta">Read the case study</span>
		</div>
	</a>
	<?php
}

/**
 * Fetch published case studies tagged with a given sector.
 *
 * The Sectors field on a case study is an ACF relationship storing a serialised
 * array of post IDs, hence the LIKE against the quoted ID.
 *
 * @param integer $sector_id The sector post ID.
 * @param integer $limit     Maximum case studies to return.
 * @return WP_Post[]
 */
function cb_get_sector_case_studies( $sector_id, $limit = 3 ) {
	$sector_id = (int) $sector_id;

	if ( ! $sector_id ) {
		return array();
	}

	return get_posts(
		array(
			'post_type'        => 'case_study',
			'post_status'      => 'publish',
			'posts_per_page'   => $limit,
			'orderby'          => array(
				'menu_order' => 'ASC',
				'date'       => 'DESC',
			),
			'no_found_rows'    => true,
			'suppress_filters' => false,
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'meta_query'       => array(
				array(
					'key'     => 'related_sectors',
					'value'   => '"' . $sector_id . '"',
					'compare' => 'LIKE',
				),
			),
		)
	);
}

/**
 * Build the related case studies section for a sector.
 *
 * Returns an empty string when the sector has no tagged case studies, so an
 * untagged sector page simply does not grow an empty band.
 *
 * @param integer $sector_id The sector post ID.
 * @return string
 */
function cb_sector_case_studies_section( $sector_id ) {
	$case_studies = cb_get_sector_case_studies( $sector_id );

	if ( empty( $case_studies ) ) {
		return '';
	}

	$hub = get_page_by_path( 'case-studies' );

	ob_start();
	?>
<section class="cb-case-study-index cb-case-study-index--related">
	<div class="container">
		<div class="cb-section-head pb-5">
			<h2>
				<?php
				printf(
					/* translators: %s: sector name, e.g. Healthcare. */
					esc_html__( '%s case studies', 'cb-andwislifts2026' ),
					esc_html( get_the_title( $sector_id ) )
				);
				?>
			</h2>
		</div>
		<div class="row g-3">
			<?php
			foreach ( $case_studies as $item ) {
				?>
			<div class="col-lg-4 col-md-6">
				<?php cb_case_study_card( $item ); ?>
			</div>
				<?php
			}
			?>
		</div>
		<?php
		if ( $hub ) {
			?>
		<p class="cb-case-study-index__more">
			<a class="cb-case-study-card__cta" href="<?= esc_url( get_permalink( $hub ) ); ?>"><?php esc_html_e( 'View all case studies', 'cb-andwislifts2026' ); ?></a>
		</p>
			<?php
		}
		?>
	</div>
</section>
	<?php
	return (string) ob_get_clean();
}

/**
 * Track whether the related section has been placed on the current sector page.
 *
 * @param integer $post_id The sector post ID.
 * @param boolean $set     Whether to mark it as placed.
 * @return boolean Whether it had already been placed.
 */
function cb_sector_case_studies_placed( $post_id, $set = false ) {
	static $placed = array();

	$was = ! empty( $placed[ $post_id ] );

	if ( $set ) {
		$placed[ $post_id ] = true;
	}

	return $was;
}

/**
 * Decide whether the current sector page should get the related section.
 *
 * Skipped when the page already carries a case study index block, so a block
 * placed by hand in the editor wins over the automatic one.
 *
 * The in_the_loop() test matters more than it looks. SEO plugins build the
 * meta description by calling get_the_excerpt(), which runs wp_trim_excerpt(),
 * which applies the_content to the post - outside the loop. Without this guard
 * the section is generated during that pass, where it both leaks into the
 * description tag and latches the flag below so the real render is skipped.
 *
 * @return integer The sector post ID, or 0 when it should be skipped.
 */
function cb_sector_case_studies_target() {
	if ( is_admin() || ! is_singular( 'sector' ) || ! is_main_query() || ! in_the_loop() ) {
		return 0;
	}

	$post_id = get_queried_object_id();

	if ( ! $post_id ) {
		return 0;
	}

	$content = get_post_field( 'post_content', $post_id );

	if ( false !== strpos( (string) $content, 'wp:acf/cb-case-study-index' ) ) {
		return 0;
	}

	return (int) $post_id;
}

/**
 * Insert the related case studies section above the contact form on sector pages.
 *
 * Sector pages are built from blocks stored in post content, so the section is
 * injected at render time rather than written into every sector page. It goes in
 * ahead of the CB Form block, which is the contact band each sector page ends
 * on. Sector pages without a form block fall back to the_content below.
 *
 * @param string $block_content The rendered block HTML.
 * @param array  $block         The parsed block.
 * @return string
 */
function cb_inject_sector_case_studies( $block_content, $block ) {
	if ( 'acf/cb-form' !== ( $block['blockName'] ?? '' ) ) {
		return $block_content;
	}

	$post_id = cb_sector_case_studies_target();

	if ( ! $post_id || cb_sector_case_studies_placed( $post_id ) ) {
		return $block_content;
	}

	cb_sector_case_studies_placed( $post_id, true );

	return cb_sector_case_studies_section( $post_id ) . $block_content;
}
add_filter( 'render_block', 'cb_inject_sector_case_studies', 10, 2 );

/**
 * Append the related case studies section when the sector page has no form block.
 *
 * Runs after do_blocks, so the render_block pass above has already had its turn.
 *
 * @param string $content The post content.
 * @return string
 */
function cb_append_sector_case_studies( $content ) {
	$post_id = cb_sector_case_studies_target();

	if ( ! $post_id || cb_sector_case_studies_placed( $post_id ) ) {
		return $content;
	}

	cb_sector_case_studies_placed( $post_id, true );

	return $content . cb_sector_case_studies_section( $post_id );
}
add_filter( 'the_content', 'cb_append_sector_case_studies', 15 );
