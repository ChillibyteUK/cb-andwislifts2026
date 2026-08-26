<?php
/**
 * Aggregated FAQPage schema output.
 *
 * Collects FAQ entries from every `cb-faqs` block instance on a page and emits a
 * single JSON-LD FAQPage in the footer. Ported from cb-turnpower2025.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

// Global store for FAQ entries collected from blocks.
global $cb_faq_entries;
$cb_faq_entries = array();

/**
 * Collect a FAQ item for the page's schema.
 *
 * @param string $question Question text (raw, may contain markup).
 * @param string $answer   Answer text (raw, may contain markup).
 * @return void
 */
function cb_collect_faq( $question, $answer ) {
	if ( empty( $question ) || empty( $answer ) ) {
		return;
	}

	global $cb_faq_entries;

	// Hash the pair so the same question rendered twice is only emitted once.
	$hash = md5( wp_strip_all_tags( $question ) . wp_strip_all_tags( $answer ) );

	$cb_faq_entries[ $hash ] = array(
		'@type'          => 'Question',
		'name'           => wp_strip_all_tags( $question ),
		'acceptedAnswer' => array(
			'@type' => 'Answer',
			'text'  => wp_strip_all_tags( $answer ),
		),
	);
}

/**
 * Output the aggregated FAQPage JSON-LD.
 *
 * Runs once per page regardless of how many FAQ blocks are present - multiple
 * FAQPage blocks on one URL is invalid.
 *
 * @return void
 */
function cb_output_faq_schema() {
	if ( is_admin() ) {
		return;
	}

	global $cb_faq_entries;

	if ( empty( $cb_faq_entries ) ) {
		return;
	}

	$data = array(
		'@context'   => 'https://schema.org',
		'@type'      => 'FAQPage',
		'mainEntity' => array_values( $cb_faq_entries ),
	);

	echo '<script type="application/ld+json">' . wp_json_encode( $data ) . '</script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
add_action( 'wp_footer', 'cb_output_faq_schema' );
