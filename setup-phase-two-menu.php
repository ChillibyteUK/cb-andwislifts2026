<?php
/**
 * Rebuild the Primary Nav menu to the phase two structure.
 *
 * Menus live in the database, so this cannot ship with the theme the way the
 * templates do. Run it once on each environment after deploying the new header:
 *
 *   wp eval-file wp-content/themes/cb-andwislifts2026/setup-phase-two-menu.php
 *
 * That only prints what it would do. To make the change, add "apply":
 *
 *   wp eval-file wp-content/themes/cb-andwislifts2026/setup-phase-two-menu.php apply
 *
 * Applying REPLACES every item in the menu assigned to the Primary Nav location.
 * It is safe to run again: the result is the same each time.
 *
 * Only published pages are added. Anything in the sitemap that is still a draft
 * (or missing) is listed as skipped - publish it and run this again, or add it
 * by hand in Appearance > Menus.
 *
 * The CSS classes and title attributes set here are what inc/cb-nav.php reads to
 * choose each dropdown's panel style and heading.
 *
 * @package cb-andwislifts2026
 */

// A dev tool, not a web endpoint.
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit;
}

$cb_menu_apply = isset( $args[0] ) && 'apply' === $args[0];

/*
 * Each item is: title, then either [ post type, path ] or a URL.
 * Top-level items may add: class (panel style), heading (panel heading), children.
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

$cb_menu_locations = get_nav_menu_locations();
$cb_menu_id        = isset( $cb_menu_locations['primary_nav'] ) ? (int) $cb_menu_locations['primary_nav'] : 0;

if ( ! $cb_menu_id ) {
	WP_CLI::error( 'No menu is assigned to the Primary Nav location.' );
}

$cb_menu_existing = wp_get_nav_menu_items( $cb_menu_id, array( 'post_status' => 'any' ) );
$cb_menu_existing = $cb_menu_existing ? $cb_menu_existing : array();
$cb_menu_skipped  = array();
$cb_menu_position = 0;

WP_CLI::log( sprintf( '%s menu #%d: %d existing item(s) will be replaced.', $cb_menu_apply ? 'Rebuilding' : 'Dry run for', $cb_menu_id, count( $cb_menu_existing ) ) );

if ( $cb_menu_apply ) {
	foreach ( $cb_menu_existing as $cb_menu_old ) {
		wp_delete_post( $cb_menu_old->ID, true );
	}
}

foreach ( $cb_menu_structure as $cb_menu_entry ) {
	list( $cb_menu_args, $cb_menu_reason ) = $cb_menu_item_args( $cb_menu_entry );

	// A parent whose page is missing still opens its panel; it just has no link.
	if ( ! $cb_menu_args ) {
		if ( empty( $cb_menu_entry['children'] ) ) {
			$cb_menu_skipped[] = $cb_menu_entry['title'] . ' (' . $cb_menu_reason . ')';
			continue;
		}

		$cb_menu_skipped[] = $cb_menu_entry['title'] . ' link (' . $cb_menu_reason . ') - added without a destination';
		$cb_menu_fallback  = $cb_menu_entry;

		unset( $cb_menu_fallback['post'] );
		$cb_menu_fallback['url']  = '#';
		list( $cb_menu_args )     = $cb_menu_item_args( $cb_menu_fallback );
	}

	$cb_menu_args['menu-item-position'] = ++$cb_menu_position;
	$cb_menu_parent                     = 0;

	WP_CLI::log( sprintf( '+ %s%s', $cb_menu_entry['title'], empty( $cb_menu_entry['class'] ) ? '' : '  [' . $cb_menu_entry['class'] . ']' ) );

	if ( $cb_menu_apply ) {
		$cb_menu_parent = wp_update_nav_menu_item( $cb_menu_id, 0, $cb_menu_args );

		if ( is_wp_error( $cb_menu_parent ) ) {
			WP_CLI::error( $cb_menu_entry['title'] . ': ' . $cb_menu_parent->get_error_message() );
		}
	}

	if ( empty( $cb_menu_entry['children'] ) ) {
		continue;
	}

	foreach ( $cb_menu_entry['children'] as $cb_menu_child ) {
		list( $cb_menu_child_args, $cb_menu_reason ) = $cb_menu_item_args( $cb_menu_child );

		if ( ! $cb_menu_child_args ) {
			$cb_menu_skipped[] = $cb_menu_entry['title'] . ' > ' . $cb_menu_child['title'] . ' (' . $cb_menu_reason . ')';
			continue;
		}

		$cb_menu_child_args['menu-item-position']  = ++$cb_menu_position;
		$cb_menu_child_args['menu-item-parent-id'] = $cb_menu_parent;

		WP_CLI::log( '    + ' . $cb_menu_child['title'] );

		if ( $cb_menu_apply ) {
			$cb_menu_result = wp_update_nav_menu_item( $cb_menu_id, 0, $cb_menu_child_args );

			if ( is_wp_error( $cb_menu_result ) ) {
				WP_CLI::error( $cb_menu_child['title'] . ': ' . $cb_menu_result->get_error_message() );
			}
		}
	}
}

if ( $cb_menu_skipped ) {
	WP_CLI::log( '' );
	WP_CLI::warning( 'Skipped, because the page is not published:' );

	foreach ( $cb_menu_skipped as $cb_menu_line ) {
		WP_CLI::log( '    - ' . $cb_menu_line );
	}
}

WP_CLI::log( '' );

if ( $cb_menu_apply ) {
	WP_CLI::success( 'Primary Nav rebuilt.' );
} else {
	WP_CLI::success( 'Nothing changed. Run again with "apply" to rebuild the menu.' );
}
