<?php
/**
 * Custom Post Types Registration
 *
 * This file contains the code to register custom post types for the theme.
 *
 * @package cb-andwislifts2026
 */

/**
 * Register custom post types for the theme.
 *
 * @return void
 */
function cb_register_post_types() {

	/*
	Applications and Products are not part of the andwis lifts site. Left here,
	disabled, in case a later phase needs them. Both were empty when disabled.

	register_post_type( 'application', ... );
	register_post_type( 'product', ... );
	*/

	register_post_type(
		'service',
		array(
			'labels'              => array(
				'name'               => 'Services',
				'singular_name'      => 'Service',
				'add_new_item'       => 'Add New Service',
				'edit_item'          => 'Edit Service',
				'new_item'           => 'New Service',
				'view_item'          => 'View Service',
				'search_items'       => 'Search Services',
				'not_found'          => 'No services found',
				'not_found_in_trash' => 'No services in trash',
			),
			'has_archive'         => false,
			'public'              => true,
			'publicly_queryable'  => true,
			'exclude_from_search' => false,
			'show_in_nav_menus'   => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => true,
			'menu_position'       => 21,
			'menu_icon'           => 'dashicons-admin-tools',
			'supports'            => array( 'title', 'editor', 'thumbnail', 'page-attributes', 'excerpt' ),
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'rewrite'             => array(
				'slug'       => 'lift-services',
				'with_front' => false,
			),
		)
	);

	register_post_type(
		'sector',
		array(
			'labels'              => array(
				'name'               => 'Sectors',
				'singular_name'      => 'Sector',
				'add_new_item'       => 'Add New Sector',
				'edit_item'          => 'Edit Sector',
				'new_item'           => 'New Sector',
				'view_item'          => 'View Sector',
				'search_items'       => 'Search Sectors',
				'not_found'          => 'No sectors found',
				'not_found_in_trash' => 'No sectors in trash',
			),
			'has_archive'         => false,
			'public'              => true,
			'publicly_queryable'  => true,
			'exclude_from_search' => false,
			'show_in_nav_menus'   => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => true,
			'menu_position'       => 22,
			'menu_icon'           => 'dashicons-building',
			'supports'            => array( 'title', 'editor', 'thumbnail', 'page-attributes', 'excerpt' ),
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'rewrite'             => array(
				'slug'       => 'sectors',
				'with_front' => false,
			),
		)
	);

	register_post_type(
		'case_study',
		array(
			'labels'              => array(
				'name'               => 'Case Studies',
				'singular_name'      => 'Case Study',
				'add_new_item'       => 'Add New Case Study',
				'edit_item'          => 'Edit Case Study',
				'new_item'           => 'New Case Study',
				'view_item'          => 'View Case Study',
				'search_items'       => 'Search Case Studies',
				'not_found'          => 'No case studies found',
				'not_found_in_trash' => 'No case studies in trash',
			),
			'has_archive'         => false,
			'public'              => true,
			'publicly_queryable'  => true,
			'exclude_from_search' => false,
			'show_in_nav_menus'   => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => true,
			'menu_position'       => 23,
			'menu_icon'           => 'dashicons-portfolio',
			'supports'            => array( 'title', 'editor', 'thumbnail', 'page-attributes', 'excerpt' ),
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'rewrite'             => array(
				'slug'       => 'case-studies',
				'with_front' => false,
			),
		)
	);

	register_post_type(
		'vacancy',
		array(
			'labels'              => array(
				'name'               => 'Vacancies',
				'singular_name'      => 'Vacancy',
				'add_new_item'       => 'Add New Vacancy',
				'edit_item'          => 'Edit Vacancy',
				'new_item'           => 'New Vacancy',
				'view_item'          => 'View Vacancy',
				'search_items'       => 'Search Vacancies',
				'not_found'          => 'No vacancies found',
				'not_found_in_trash' => 'No vacancies in trash',
			),
			'has_archive'         => false,
			'public'              => true,
			'publicly_queryable'  => true,
			'exclude_from_search' => false,
			'show_in_nav_menus'   => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => true,
			'menu_position'       => 24,
			'menu_icon'           => 'dashicons-groups',
			'supports'            => array( 'title', 'editor', 'thumbnail', 'page-attributes', 'excerpt' ),
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'rewrite'             => array(
				'slug'       => 'careers/vacancies',
				'with_front' => false,
			),
		)
	);
}

add_action( 'init', 'cb_register_post_types' );

/**
 * Use the page template for block-built single views.
 *
 * These post types are composed from ACF blocks in the editor, exactly like a
 * page, so they render through page.php rather than single.php.
 *
 * @param string $template Path to the template file.
 * @return string
 */
function cb_cpt_single_template( $template ) {
	if ( is_singular( array( 'service', 'sector' ) ) ) {
		$page_template = locate_template( 'page.php' );
		if ( $page_template ) {
			return $page_template;
		}
	}
	return $template;
}
add_filter( 'single_template', 'cb_cpt_single_template' );

/**
 * Build a normalised set of cards from the Service or Sector post types.
 *
 * Returns rows shaped like the manual "cards" repeater on the CB Service Cards
 * block, so both sources render through the same markup.
 *
 * @param string $source   One of service_all, service_select, service_related,
 *                         sector_all or sector_select.
 * @param int    $limit    Maximum cards to return. 0 for no limit.
 * @param array  $selected Post IDs chosen in the editor, for the _select sources.
 * @return array
 */
function cb_get_cpt_cards( $source, $limit = 0, $selected = array() ) {
	$cards     = array();
	$post_type = ( 0 === strpos( $source, 'sector' ) ) ? 'sector' : 'service';

	$args = array(
		'post_type'              => $post_type,
		'post_status'            => 'publish',
		'posts_per_page'         => $limit > 0 ? $limit : -1,
		'orderby'                => array(
			'menu_order' => 'ASC',
			'title'      => 'ASC',
		),
		'no_found_rows'          => true,
		'update_post_term_cache' => false,
	);

	if ( 'service_related' === $source ) {
		$selected = get_field( 'related_services', get_the_ID() );
	}

	if ( 'service_all' !== $source && 'sector_all' !== $source ) {
		if ( empty( $selected ) ) {
			return $cards;
		}
		$args['post__in'] = array_map( 'intval', (array) $selected );
		$args['orderby']  = 'post__in';
	}

	$query = new WP_Query( $args );

	foreach ( $query->posts as $item ) {
		// Fall back to the featured image so the photo styles have something to
		// show on posts that only ever had an icon set.
		$card_icon = get_field( 'card_icon', $item->ID );

		if ( empty( $card_icon['ID'] ) && has_post_thumbnail( $item->ID ) ) {
			$card_icon = array( 'ID' => get_post_thumbnail_id( $item->ID ) );
		}

		$cards[] = array(
			'icon'        => $card_icon,
			'title'       => get_the_title( $item ),
			'description' => get_field( 'card_summary', $item->ID ),
			'link'        => array(
				'url'    => get_permalink( $item ),
				'title'  => __( 'Learn more', 'cb-andwislifts2026' ),
				'target' => '',
			),
		);
	}

	return $cards;
}

/**
 * Flush rewrite rules on theme activation so the custom post type slugs work.
 */
function cb_flush_rewrites() {
	cb_register_post_types();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'cb_flush_rewrites' );

/**
 * Build a lookup of published sector posts keyed by normalised title and slug.
 *
 * Normalising lets a block label written for display ("Retail & Leisure") find
 * the post it refers to ("Retail") without the two having to match character
 * for character.
 *
 * @return array Map of normalised key => permalink.
 */
function cb_sector_link_map() {
	static $map = null;

	if ( null !== $map ) {
		return $map;
	}

	$map = array();

	$sectors = get_posts(
		array(
			'post_type'        => 'sector',
			'post_status'      => 'publish',
			'posts_per_page'   => -1,
			'orderby'          => 'title',
			'order'            => 'ASC',
			'suppress_filters' => false,
		)
	);

	foreach ( $sectors as $sector ) {
		$permalink = get_permalink( $sector );

		foreach ( array( $sector->post_title, $sector->post_name ) as $candidate ) {
			$key = cb_normalise_sector_key( $candidate );

			if ( '' !== $key && ! isset( $map[ $key ] ) ) {
				$map[ $key ] = $permalink;
			}
		}
	}

	return $map;
}

/**
 * Reduce a sector title, slug or label to a comparable key.
 *
 * Lowercases, expands ampersands and collapses everything that is not a letter
 * or number to single spaces, so "Retail & Leisure", "retail-and-leisure" and
 * "Retail and leisure" all normalise the same way.
 *
 * @param string $value The raw title, slug or label.
 * @return string
 */
function cb_normalise_sector_key( $value ) {
	$value = strtolower( wp_strip_all_tags( (string) $value ) );
	$value = str_replace( '&', ' and ', $value );
	$value = preg_replace( '/[^a-z0-9]+/', ' ', $value );

	return trim( $value );
}

/**
 * Resolve a sector label to the permalink of its sector post.
 *
 * Tries an exact match on the normalised title or slug first, then a prefix
 * match in either direction so "Retail & Leisure" finds "Retail" and
 * "Industrial" finds "Industrial and logistics". The prefix match must land on
 * a word boundary and needs at least four characters, which keeps short or
 * coincidental overlaps from producing a wrong link.
 *
 * Returns an empty string when nothing matches - the caller renders plain text
 * rather than a link that goes nowhere.
 *
 * @param string $label The label as written in the block.
 * @return string Permalink, or an empty string when there is no match.
 */
function cb_resolve_sector_link( $label ) {
	$key = cb_normalise_sector_key( $label );

	if ( '' === $key ) {
		return '';
	}

	$map = cb_sector_link_map();

	if ( isset( $map[ $key ] ) ) {
		return $map[ $key ];
	}

	foreach ( $map as $candidate => $permalink ) {
		$shorter = strlen( $candidate ) < strlen( $key ) ? $candidate : $key;
		$longer  = strlen( $candidate ) < strlen( $key ) ? $key : $candidate;

		if ( strlen( $shorter ) < 4 ) {
			continue;
		}

		if ( 0 === strpos( $longer, $shorter . ' ' ) ) {
			return $permalink;
		}
	}

	return '';
}
