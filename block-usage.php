<?php
/**
 * Find every post that uses a given block.
 *
 * Block instances live as HTML comments in post_content, so this is a content
 * search rather than anything WordPress indexes.
 *
 *   wp eval-file block-usage.php "CB Emergency"
 *   wp eval-file block-usage.php cb-emergency
 *   wp eval-file block-usage.php acf/cb-emergency
 *
 * @package cb-andwislifts2026
 */

// A dev tool, not a web endpoint.
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit;
}
$arg = $args[0] ?? '';

if ( '' === $arg ) {
	WP_CLI::error( 'Pass a block name, e.g. "CB Emergency" or acf/cb-emergency' );
}

$slug = strtolower( trim( $arg ) );
$slug = preg_replace( '#^(wp:)?acf/#', '', $slug );
$slug = str_replace( ' ', '-', $slug );
$needle = 'wp:acf/' . $slug;

global $wpdb;

$rows = $wpdb->get_results(
	$wpdb->prepare(
		"SELECT ID, post_title, post_type, post_status
		 FROM {$wpdb->posts}
		 WHERE post_content LIKE %s
		   AND post_type != 'revision'
		   AND post_status NOT IN ('auto-draft','trash','inherit')
		 ORDER BY post_type, post_title",
		'%' . $wpdb->esc_like( $needle ) . '%'
	)
);

if ( ! $rows ) {
	WP_CLI::warning( "No content uses {$needle}" );
	return;
}

$out = array();

foreach ( $rows as $r ) {
	// How many times the block appears in that one post.
	$count = substr_count( get_post( $r->ID )->post_content, '<!-- ' . $needle );

	$out[] = array(
		'ID'     => $r->ID,
		'type'   => $r->post_type,
		'status' => $r->post_status,
		'title'  => $r->post_title,
		'uses'   => $count,
		'url'    => get_permalink( $r->ID ),
	);
}

WP_CLI\Utils\format_items( 'table', $out, array( 'ID', 'type', 'status', 'title', 'uses', 'url' ) );
WP_CLI::success( count( $out ) . ' item(s) use ' . $needle );
