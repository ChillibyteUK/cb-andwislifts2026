<?php
/**
 * The header for the theme
 *
 * Displays all of the <head> section and everything up till <div id="content">
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

if ( session_status() === PHP_SESSION_NONE ) {
    session_start();
}



?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta
        charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, minimum-scale=1">

    <!-- Start cookieyes banner -->
    <?php
    // Consent manager. Deliberately first in <head> and deliberately not async:
    // it has to run before Google Analytics and GTM below so it can block their
    // cookies until consent is given. Moving it after them, or deferring it,
    // defeats the point of having it.
    //
    // Loaded for everyone, including logged-in users, unlike the analytics tags
    // below - the banner needs to be testable without logging out.
    ?>
    <script id="cookieyes" type="text/javascript"
        src="https://cdn-cookieyes.com/client_data/d3de0529e2ed6607cc7662698e9ac24e/script.js"></script>
    <!-- End cookieyes banner -->

	<link rel="preload"
		href="<?= esc_url( get_stylesheet_directory_uri() . '/fonts/poppins-400-latin.woff2' ); ?>"
		as="font" type="font/woff2" crossorigin="anonymous">
	<link rel="preload"
        href="<?= esc_url( get_stylesheet_directory_uri() . '/fonts/poppins-500-latin.woff2' ); ?>"
        as="font" type="font/woff2" crossorigin="anonymous">
	<link rel="preload"
        href="<?= esc_url( get_stylesheet_directory_uri() . '/fonts/poppins-600-latin.woff2' ); ?>"
        as="font" type="font/woff2" crossorigin="anonymous">
	<link rel="preload"
        href="<?= esc_url( get_stylesheet_directory_uri() . '/fonts/poppins-700-latin.woff2' ); ?>"
        as="font" type="font/woff2" crossorigin="anonymous">
	<link rel="preload"
        href="<?= esc_url( get_stylesheet_directory_uri() . '/fonts/poppins-800-latin.woff2' ); ?>"
        as="font" type="font/woff2" crossorigin="anonymous">
	
    <?php
    if ( ! is_user_logged_in() ) {
        if ( get_field( 'ga_property', 'options' ) ) {
            ?>
            <!-- Global site tag (gtag.js) - Google Analytics -->
            <script async
                src="<?= esc_url( 'https://www.googletagmanager.com/gtag/js?id=' . get_field( 'ga_property', 'options' ) ); ?>">
            </script>
            <script>
                window.dataLayer = window.dataLayer || [];

                function gtag() {
                    dataLayer.push(arguments);
                }
                gtag('js', new Date());
                gtag('config',
                    '<?= esc_js( get_field( 'ga_property', 'options' ) ); ?>'
                );
            </script>
        	<?php
        }
        if ( get_field( 'gtm_property', 'options' ) ) {
            ?>
            <!-- Google Tag Manager -->
            <script>
                (function(w, d, s, l, i) {
                    w[l] = w[l] || [];
                    w[l].push({
                        'gtm.start': new Date().getTime(),
                        event: 'gtm.js'
                    });
                    var f = d.getElementsByTagName(s)[0],
                        j = d.createElement(s),
                        dl = l != 'dataLayer' ? '&l=' + l : '';
                    j.async = true;
                    j.src =
                        'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
                    f.parentNode.insertBefore(j, f);
                })(window, document, 'script', 'dataLayer',
                    '<?= esc_js( get_field( 'gtm_property', 'options' ) ); ?>'
                );
            </script>
            <!-- End Google Tag Manager -->
    		<?php
        }
    }
	if ( get_field( 'google_site_verification', 'options' ) ) {
		echo '<meta name="google-site-verification" content="' . esc_attr( get_field( 'google_site_verification', 'options' ) ) . '" />';
	}
	if ( get_field( 'bing_site_verification', 'options' ) ) {
		echo '<meta name="msvalidate.01" content="' . esc_attr( get_field( 'bing_site_verification', 'options' ) ) . '" />';
	}
	wp_head();
	?>
</head>

<body <?php body_class( is_front_page() ? 'homepage' : '' ); ?>
    <?php understrap_body_attributes(); ?>>
    <?php
	do_action( 'wp_body_open' );
	if ( ! is_user_logged_in() ) {
    	if ( get_field( 'gtm_property', 'options' ) ) {
        	?>
            <!-- Google Tag Manager (noscript) -->
            <noscript><iframe
                    src="<?= esc_url( 'https://www.googletagmanager.com/ns.html?id=' . get_field( 'gtm_property', 'options' ) ); ?>"
                    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
            <!-- End Google Tag Manager (noscript) -->
    		<?php
    	}
	}
	?>
<!-- PRIMARY NAV -->
<header id="wrapper-navbar" class="sticky-top">
	<nav class="navbar navbar-expand-xl" aria-label="Primary navigation">
		<div class="container">
			<a class="navbar-brand" href="<?= esc_url( home_url( '/' ) ); ?>" aria-label="andwis Lifts home">
				<?= cb_site_logo_svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>

			<button class="navbar-toggler" type="button"
				data-bs-toggle="collapse" data-bs-target="#primary-navbar"
				aria-controls="primary-navbar" aria-expanded="false"
				aria-label="Toggle navigation">
				<?= cb_nav_icon( 'menu' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</button>

			<div id="primary-navbar" class="collapse navbar-collapse">
				<?php cb_render_primary_nav(); ?>
			</div>
		</div>
	</nav>
</header>
