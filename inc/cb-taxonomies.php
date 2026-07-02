<?php
/**
 * Custom taxonomies for the HTS theme.
 *
 * This file defines and registers custom taxonomies.
 *
 * @package cb-andwislifts2026
 */

/**
 * Register custom taxonomies for the theme.
 *
 * @return void
 */
function cb_register_taxes() {

	$args = array(
		'labels'             => array(
			'name'          => 'Applications',
			'singular_name' => 'Application',
		),
		'public'             => true,
		'publicly_queryable' => true,
		'hierarchical'       => true,
		'show_ui'            => true,
		'show_in_nav_menus'  => false,
		'show_tagcloud'      => false,
		'show_in_quick_edit' => true,
		'show_admin_column'  => true,
		'show_in_rest'       => true,
		'rewrite'            => array(
			'slug'         => 'application-cat',
			'with_front'   => false,
			'hierarchical' => true,
		),
		'rest_base'          => 'application_cat',
	);
	register_taxonomy( 'application_cat', array( 'project' ), $args );
}
add_action( 'init', 'cb_register_taxes' );
