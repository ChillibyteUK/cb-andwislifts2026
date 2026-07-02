<?php
/**
 * Template Name: Text Page
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;
get_header();

?>
<main id="main" class="text-page">
	<div class="container">
		<h1 class="pb-5"><?= esc_html( get_the_title() ); ?></h1>
		<?php
		echo apply_filters( 'the_content', get_the_content() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		?>
	</div>
</main>
<?php
get_footer();
?>