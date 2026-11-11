<?php
/**
 * The news hub, and the fallback for any other archive.
 *
 * WordPress shows this in place of the News page, because that page is set as
 * the posts page. So the page's own blocks are rendered here: CB Hero, CB
 * Intro, CB News Index and the closing band, all editable on the News page as
 * on any other. With nothing on that page, the bare listing is shown.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

$news_page = get_post( (int) get_option( 'page_for_posts' ) );
$content   = $news_page ? $news_page->post_content : '';

if ( '' === trim( $content ) ) {
	$content = '<!-- wp:acf/cb-news-index {"name":"acf/cb-news-index","data":{},"mode":"preview"} /-->';
}

get_header();
?>
<main id="main" class="cb-news">
	<?php
	// Rendered block output, already filtered by WordPress.
	echo apply_filters( 'the_content', $content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
	?>
</main>
<?php
get_footer();
