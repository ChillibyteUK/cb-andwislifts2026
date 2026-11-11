<?php
/**
 * Case study cards, and the related-case-studies section on sector pages.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

/**
 * The sectors and services a case study is tagged with.
 *
 * Service names lose their leading "Lift", so a tag reads "Maintenance", as
 * the design has it, not "Lift maintenance".
 *
 * @param integer $post_id The case study ID.
 * @return array<int, array{type: string, label: string, slug: string}>
 */
function cb_case_study_tags( $post_id ) {
	$tags = array();

	foreach ( array(
		'sector'  => 'related_sectors',
		'service' => 'related_services',
	) as $type => $field ) {
		foreach ( (array) get_field( $field, $post_id ) as $related ) {
			// An empty field comes back as an empty string, and get_the_title( 0 )
			// would answer with the title of whatever page this is.
			$related_id = is_object( $related ) ? (int) $related->ID : (int) $related;
			$label      = $related_id ? get_the_title( $related_id ) : '';

			if ( ! $label ) {
				continue;
			}

			if ( 'service' === $type ) {
				$label = ucfirst( preg_replace( '/^lift\s+/i', '', $label ) );
			}

			$tags[] = array(
				'type'  => $type,
				'label' => $label,
				'slug'  => sanitize_title( $label ),
			);
		}
	}

	return $tags;
}

/**
 * Print a case study's tags: sectors filled, services outlined.
 *
 * @param array  $tags  From cb_case_study_tags().
 * @param string $extra Extra class for the list, e.g. the light-on-dark one.
 * @return void
 */
function cb_case_study_tags_list( $tags, $extra = '' ) {
	if ( ! $tags ) {
		return;
	}

	echo '<ul class="cb-case-tags ' . esc_attr( $extra ) . '">';

	foreach ( $tags as $tag ) {
		printf( '<li class="is-%s">%s</li>', esc_attr( $tag['type'] ), esc_html( $tag['label'] ) );
	}

	echo '</ul>';
}

/**
 * A case study's teaser: its Card summary, or failing that the start of its
 * first section.
 *
 * @param integer $post_id The case study ID.
 * @param integer $words   Words to trim it to.
 * @return string
 */
function cb_case_study_summary( $post_id, $words = 26 ) {
	$summary = trim( (string) get_field( 'card_summary', $post_id ) );

	if ( '' === $summary ) {
		foreach ( parse_blocks( (string) get_post_field( 'post_content', $post_id ) ) as $block ) {
			if ( ! empty( $block['attrs']['data']['body'] ) ) {
				$summary = wp_strip_all_tags( (string) $block['attrs']['data']['body'] );
				break;
			}
		}
	}

	return wp_trim_words( $summary, (int) $words );
}

/**
 * Output a single case study card.
 *
 * Shared by the CB Case Study Index block, the related section injected into
 * sector pages and "More case studies" on a case study, so they cannot drift
 * apart. The whole card is one link, carried by the title.
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

	$summary = cb_case_study_summary( $item->ID, $words );
	$kicker  = trim( (string) get_field( 'client_name', $item->ID ) );
	$image   = get_post_thumbnail_id( $item->ID );
	?>
	<article class="cb-case-study-card">
		<div class="cb-case-study-card__media">
			<?php
			if ( $image ) {
				echo wp_get_attachment_image( $image, 'medium_large', false, array( 'loading' => 'lazy' ) );
			}
			?>
		</div>
		<div class="cb-case-study-card__body">
			<h3><a href="<?= esc_url( get_permalink( $item ) ); ?>"><?= esc_html( get_the_title( $item ) ); ?></a></h3>
			<?php if ( $kicker ) { ?>
			<p class="cb-case-study-card__kicker"><?= esc_html( $kicker ); ?></p>
			<?php } ?>
			<?php if ( $summary ) { ?>
			<p class="cb-case-study-card__summary"><?= esc_html( $summary ); ?></p>
			<?php } ?>
			<?php cb_case_study_tags_list( cb_case_study_tags( $item->ID ), 'cb-case-study-card__tags' ); ?>
		</div>
	</article>
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
<section class="cb-case-study__more cb-case-study__more--related">
	<div class="container">
		<h2>
			<?php
			printf(
				/* translators: %s: sector name, e.g. Healthcare. */
				esc_html__( '%s case studies', 'cb-andwislifts2026' ),
				esc_html( get_the_title( $sector_id ) )
			);
			?>
		</h2>
		<div class="cb-case-study-grid">
			<?php
			foreach ( $case_studies as $item ) {
				cb_case_study_card( $item );
			}
			?>
		</div>
		<?php
		if ( $hub ) {
			?>
		<p class="cb-case-study__more-link">
			<a class="btn btn-ghost" href="<?= esc_url( get_permalink( $hub ) ); ?>"><?php esc_html_e( 'View all case studies', 'cb-andwislifts2026' ); ?></a>
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
	$name = $block['blockName'] ?? '';

	// The band a sector page ends on: the mint call to action in phase two,
	// the enquiry form before it.
	$closes = 'acf/cb-form' === $name || ( 'acf/cb-cta' === $name && 'mint' === ( $block['attrs']['data']['style'] ?? '' ) );

	if ( ! $closes ) {
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
