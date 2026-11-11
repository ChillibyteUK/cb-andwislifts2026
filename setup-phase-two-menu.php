<?php
/**
 * Rebuild the navigation menus to the phase two structure.
 *
 * Menus live in the database, so they cannot ship with the theme the way the
 * templates do. Run this on an environment after deploying the phase two theme.
 * Locally, WP-CLI has to run inside the DevKinsta container:
 *
 *   docker exec devkinsta_fpm sh -c 'cd /www/kinsta/public/andwisliftscom && wp eval-file wp-content/themes/cb-andwislifts2026/setup-phase-two-menu.php --allow-root'
 *
 * On a server, from the site root:
 *
 *   wp eval-file wp-content/themes/cb-andwislifts2026/setup-phase-two-menu.php
 *
 * That only prints what it would do. Add words after the path to act:
 *
 *   apply            Make the changes.
 *   primary          Only the header menu.
 *   footer           Only the three footer column menus.
 *
 * So "... setup-phase-two-menu.php footer apply" builds just the footer menus.
 *
 * What it does:
 *
 * - Primary: REPLACES every item in the menu assigned to the Primary Nav
 *   location. The CSS classes and title attributes it sets are what
 *   inc/cb-nav.php reads to choose each dropdown's panel style and heading.
 * - Footer: makes sure three menus exist, named after the column headings
 *   ("About andwis lifts", "Services", "Sectors"), REPLACES their items and
 *   assigns them to Footer column 1, 2 and 3. Menus that were assigned there
 *   before are left alone, just no longer shown.
 *
 * It is safe to run again: the result is the same each time.
 *
 * Only published pages are added. Anything in the sitemap that is still a draft
 * (or missing) is listed as skipped - publish it and run this again, or add it
 * by hand in Appearance > Menus.
 *
 * @package cb-andwislifts2026
 */

// A dev tool, not a web endpoint.
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit;
}

$cb_menu_words   = isset( $args ) ? (array) $args : array();
$cb_menu_apply   = in_array( 'apply', $cb_menu_words, true );
$cb_menu_primary = ! in_array( 'footer', $cb_menu_words, true ) || in_array( 'primary', $cb_menu_words, true );
$cb_menu_footer  = ! in_array( 'primary', $cb_menu_words, true ) || in_array( 'footer', $cb_menu_words, true );

/*
 * Each item is: title, then either [ post type, path ] or a URL.
 * Top-level primary items may add: class (panel style), heading (panel heading), children.
 */
$cb_menu_structure = array(
	array(
		'title'    => 'Services',
		'post'     => array( 'page', 'lift-services' ),
		'class'    => 'nav-split',
		'heading'  => 'By service',
		'children' => array(
			array(
				'title' => 'Lift installation',
				'post'  => array( 'service', 'lift-installation' ),
			),
			array(
				'title' => 'Lift maintenance',
				'post'  => array( 'service', 'lift-maintenance' ),
			),
			array(
				'title' => 'Lift modernisation',
				'post'  => array( 'service', 'lift-modernisation' ),
			),
			array(
				'title' => 'Lift repairs',
				'post'  => array( 'service', 'lift-repairs' ),
			),
			array(
				'title' => 'Lift types',
				'post'  => array( 'service', 'lift-types' ),
			),
		),
	),
	array(
		'title'    => 'Sectors and Coverage',
		'post'     => array( 'page', 'sectors' ),
		'class'    => 'nav-bar',
		'children' => array(
			array(
				'title' => 'Sectors hub',
				'post'  => array( 'page', 'sectors' ),
			),
			array(
				'title' => 'Areas we cover',
				'post'  => array( 'page', 'areas-we-cover' ),
			),
			array(
				'title' => 'Case studies',
				'post'  => array( 'page', 'case-studies' ),
			),
		),
	),
	array(
		'title' => 'Resources',
		'post'  => array( 'page', 'resources' ),
	),
	array(
		'title'    => 'About',
		'post'     => array( 'page', 'about' ),
		'class'    => 'nav-dark',
		'heading'  => 'About andwis Lifts',
		'children' => array(
			array(
				'title' => 'Our story',
				'post'  => array( 'page', 'about' ),
			),
			array(
				'title' => 'Careers',
				'post'  => array( 'page', 'careers' ),
			),
			array(
				'title' => 'Accreditations',
				'post'  => array( 'page', 'about/accreditations' ),
			),
			array(
				'title' => 'Areas we cover',
				'post'  => array( 'page', 'areas-we-cover' ),
			),
			array(
				'title' => 'News & insights',
				'post'  => array( 'page', 'news' ),
			),
			array(
				'title' => 'Leadership',
				'post'  => array( 'page', 'our-team' ),
			),
			array(
				'title'  => 'Discover the andwis group',
				'url'    => 'https://www.andwis.com/',
				'target' => '_blank',
			),
		),
	),
	array(
		'title'    => 'Contact',
		'post'     => array( 'page', 'contact' ),
		'class'    => 'nav-mint',
		'heading'  => 'Contact us',
		'children' => array(
			array(
				'title' => 'Ask the expert',
				'post'  => array( 'page', 'contact/ask-the-expert' ),
			),
			array(
				'title' => 'Get a quote',
				'post'  => array( 'page', 'get-a-quote' ),
			),
			array(
				'title' => 'Emergency call out',
				'post'  => array( 'page', 'emergency-callout' ),
			),
			array(
				'title' => 'FAQs',
				'post'  => array( 'page', 'faqs' ),
			),
		),
	),
	array(
		'title' => 'Ask the experts',
		'post'  => array( 'page', 'contact/ask-the-expert' ),
		'class' => 'button',
	),
);

/*
 * Footer columns: location => menu name (the column heading) and its links.
 */
$cb_menu_footer_columns = array(
	'footer_menu'   => array(
		'name'  => 'About andwis lifts',
		'items' => array(
			array(
				'title' => 'Our story',
				'post'  => array( 'page', 'about' ),
			),
			array(
				'title' => 'Careers',
				'post'  => array( 'page', 'careers' ),
			),
			array(
				'title' => 'Accreditations',
				'post'  => array( 'page', 'about/accreditations' ),
			),
			array(
				'title' => 'Areas we cover',
				'post'  => array( 'page', 'areas-we-cover' ),
			),
			array(
				'title' => 'News and insights',
				'post'  => array( 'page', 'news' ),
			),
			array(
				'title' => 'Leadership',
				'post'  => array( 'page', 'our-team' ),
			),
			array(
				'title' => 'Resources',
				'post'  => array( 'page', 'resources' ),
			),
		),
	),
	'footer_menu_2' => array(
		'name'  => 'Services',
		'items' => array(
			array(
				'title' => 'Lift installation',
				'post'  => array( 'service', 'lift-installation' ),
			),
			array(
				'title' => 'Lift maintenance',
				'post'  => array( 'service', 'lift-maintenance' ),
			),
			array(
				'title' => 'Lift modernisation',
				'post'  => array( 'service', 'lift-modernisation' ),
			),
			array(
				'title' => 'Lift repairs',
				'post'  => array( 'service', 'lift-repairs' ),
			),
			array(
				'title' => 'Lift types',
				'post'  => array( 'service', 'lift-types' ),
			),
		),
	),
	'footer_menu_3' => array(
		'name'  => 'Sectors',
		'items' => array(
			array(
				'title' => 'Residential',
				'post'  => array( 'sector', 'residential' ),
			),
			array(
				'title' => 'Commercial',
				'post'  => array( 'sector', 'commercial' ),
			),
			array(
				'title' => 'Healthcare',
				'post'  => array( 'sector', 'healthcare' ),
			),
			array(
				'title' => 'Retail',
				'post'  => array( 'sector', 'retail' ),
			),
			array(
				'title' => 'Education',
				'post'  => array( 'sector', 'education' ),
			),
			array(
				'title' => 'Local authority / public sector',
				'post'  => array( 'sector', 'local-authority' ),
			),
			array(
				'title' => 'High sensitivity sites',
				'post'  => array( 'sector', 'high-sensitivity' ),
			),
			array(
				'title' => 'Industrial and logistics',
				'post'  => array( 'sector', 'industrial' ),
			),
		),
	),
);

/**
 * Find a published post by type and path, wherever it sits in the page tree.
 *
 * @param string $type Post type.
 * @param string $path Slug, or parent/child path.
 * @return array{0: WP_Post|null, 1: string} The post and, when unusable, why.
 */
$cb_menu_find = static function ( $type, $path ) {
	$found = get_page_by_path( $path, OBJECT, $type );

	// A page may have been moved under a different parent; fall back to its slug.
	if ( ! $found ) {
		$matches = get_posts(
			array(
				'name'        => basename( $path ),
				'post_type'   => $type,
				'post_status' => array( 'publish', 'draft', 'pending', 'private', 'future' ),
				'numberposts' => 1,
			)
		);
		$found   = $matches ? $matches[0] : null;
	}

	if ( ! $found ) {
		return array( null, 'not found' );
	}

	if ( 'publish' !== $found->post_status ) {
		return array( null, $found->post_status . ', ID ' . $found->ID );
	}

	return array( $found, '' );
};

/**
 * Turn one structure entry into wp_update_nav_menu_item() arguments.
 *
 * @param array $entry Structure entry.
 * @return array{0: array|null, 1: string} Arguments and, when skipped, why.
 */
$cb_menu_item_args = static function ( $entry ) use ( $cb_menu_find ) {
	$item = array(
		'menu-item-title'      => $entry['title'],
		'menu-item-status'     => 'publish',
		'menu-item-classes'    => isset( $entry['class'] ) ? $entry['class'] : '',
		'menu-item-attr-title' => isset( $entry['heading'] ) ? $entry['heading'] : '',
		'menu-item-target'     => isset( $entry['target'] ) ? $entry['target'] : '',
	);

	if ( isset( $entry['url'] ) ) {
		$item['menu-item-type'] = 'custom';
		$item['menu-item-url']  = $entry['url'];

		return array( $item, '' );
	}

	list( $target, $reason ) = $cb_menu_find( $entry['post'][0], $entry['post'][1] );

	if ( ! $target ) {
		return array( null, $reason );
	}

	$item['menu-item-type']      = 'post_type';
	$item['menu-item-object']    = $target->post_type;
	$item['menu-item-object-id'] = $target->ID;

	return array( $item, '' );
};

/**
 * Empty a menu, then fill it from a list of structure entries.
 *
 * @param int     $menu_id Menu term ID, or 0 on a dry run for a menu not made yet.
 * @param array   $entries Structure entries; top-level ones may carry children.
 * @param boolean $apply   Whether to write, or only report.
 * @return string[] Entries skipped, with the reason.
 */
$cb_menu_fill = static function ( $menu_id, $entries, $apply ) use ( $cb_menu_item_args ) {
	$skipped  = array();
	$position = 0;
	$existing = $menu_id ? wp_get_nav_menu_items( $menu_id, array( 'post_status' => 'any' ) ) : array();
	$existing = $existing ? $existing : array();

	WP_CLI::log( sprintf( '  %d existing item(s) will be replaced.', count( $existing ) ) );

	if ( $apply ) {
		foreach ( $existing as $old ) {
			wp_delete_post( $old->ID, true );
		}
	}

	foreach ( $entries as $entry ) {
		list( $item, $reason ) = $cb_menu_item_args( $entry );

		// A parent whose page is missing still opens its panel; it just has no link.
		if ( ! $item ) {
			if ( empty( $entry['children'] ) ) {
				$skipped[] = $entry['title'] . ' (' . $reason . ')';
				continue;
			}

			$skipped[] = $entry['title'] . ' link (' . $reason . ') - added without a destination';
			$fallback  = $entry;

			unset( $fallback['post'] );
			$fallback['url'] = '#';
			list( $item )    = $cb_menu_item_args( $fallback );
		}

		$item['menu-item-position'] = ++$position;
		$parent                     = 0;

		WP_CLI::log( sprintf( '  + %s%s', $entry['title'], empty( $entry['class'] ) ? '' : '  [' . $entry['class'] . ']' ) );

		if ( $apply ) {
			$parent = wp_update_nav_menu_item( $menu_id, 0, $item );

			if ( is_wp_error( $parent ) ) {
				WP_CLI::error( $entry['title'] . ': ' . $parent->get_error_message() );
			}
		}

		if ( empty( $entry['children'] ) ) {
			continue;
		}

		foreach ( $entry['children'] as $child ) {
			list( $child_item, $reason ) = $cb_menu_item_args( $child );

			if ( ! $child_item ) {
				$skipped[] = $entry['title'] . ' > ' . $child['title'] . ' (' . $reason . ')';
				continue;
			}

			$child_item['menu-item-position']  = ++$position;
			$child_item['menu-item-parent-id'] = $parent;

			WP_CLI::log( '      + ' . $child['title'] );

			if ( $apply ) {
				$result = wp_update_nav_menu_item( $menu_id, 0, $child_item );

				if ( is_wp_error( $result ) ) {
					WP_CLI::error( $child['title'] . ': ' . $result->get_error_message() );
				}
			}
		}
	}

	return $skipped;
};

$cb_menu_skipped = array();

WP_CLI::log( $cb_menu_apply ? 'Rebuilding menus.' : 'Dry run - nothing will be changed.' );

if ( $cb_menu_primary ) {
	$cb_menu_locations = get_nav_menu_locations();
	$cb_menu_id        = isset( $cb_menu_locations['primary_nav'] ) ? (int) $cb_menu_locations['primary_nav'] : 0;

	if ( ! $cb_menu_id ) {
		WP_CLI::error( 'No menu is assigned to the Primary Nav location.' );
	}

	WP_CLI::log( '' );
	WP_CLI::log( sprintf( 'Primary Nav (menu #%d)', $cb_menu_id ) );

	$cb_menu_skipped = array_merge( $cb_menu_skipped, $cb_menu_fill( $cb_menu_id, $cb_menu_structure, $cb_menu_apply ) );
}

if ( $cb_menu_footer ) {
	foreach ( $cb_menu_footer_columns as $cb_menu_location => $cb_menu_column ) {
		$cb_menu_object = wp_get_nav_menu_object( $cb_menu_column['name'] );
		$cb_menu_id     = $cb_menu_object ? (int) $cb_menu_object->term_id : 0;

		WP_CLI::log( '' );
		WP_CLI::log( sprintf( 'Footer column "%s" (%s)', $cb_menu_column['name'], $cb_menu_id ? 'menu #' . $cb_menu_id : 'new menu' ) );

		if ( $cb_menu_apply && ! $cb_menu_id ) {
			$cb_menu_id = wp_create_nav_menu( $cb_menu_column['name'] );

			if ( is_wp_error( $cb_menu_id ) ) {
				WP_CLI::error( $cb_menu_column['name'] . ': ' . $cb_menu_id->get_error_message() );
			}
		}

		$cb_menu_skipped = array_merge( $cb_menu_skipped, $cb_menu_fill( $cb_menu_id, $cb_menu_column['items'], $cb_menu_apply ) );

		if ( $cb_menu_apply ) {
			// Read fresh each time: the previous column has just changed it.
			$cb_menu_assigned                      = (array) get_theme_mod( 'nav_menu_locations', array() );
			$cb_menu_assigned[ $cb_menu_location ] = (int) $cb_menu_id;

			set_theme_mod( 'nav_menu_locations', $cb_menu_assigned );
		}
	}
}

if ( $cb_menu_skipped ) {
	WP_CLI::log( '' );
	WP_CLI::log( 'Skipped, because the page is not published:' );

	foreach ( $cb_menu_skipped as $cb_menu_line ) {
		WP_CLI::log( '  - ' . $cb_menu_line );
	}
}

WP_CLI::log( '' );

if ( $cb_menu_apply ) {
	WP_CLI::success( 'Menus rebuilt.' );
} else {
	WP_CLI::success( 'Nothing changed. Add "apply" to rebuild the menus.' );
}
