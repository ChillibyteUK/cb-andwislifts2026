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
				<svg xmlns="http://www.w3.org/2000/svg" role="img" aria-label="andwis." viewBox="0 0 194 46" xml:space="preserve"><path class="dot" d="M193.54 37.77c0 2.9-2.3 5.29-5.14 5.29a5.22 5.22 0 0 1-5.14-5.3c0-2.9 2.3-5.28 5.14-5.28a5.2 5.2 0 0 1 5.14 5.29z"/><path class="andwis" d="m157.03 33.92-.06-.6h8.59v.55-.54h.53v.81c.02 1.35.96 2.23 2.58 2.27 1.62-.06 2.3-.68 2.35-2.05-.04-1.02-.33-1.28-1.26-1.71-.93-.39-2.42-.66-4.25-1.25-5.09-1.59-7.8-4.64-7.76-9.33a9.93 9.93 0 0 1 3.1-7.5c2-1.85 4.89-2.9 8.42-2.9 6.15-.05 10.3 3.73 10.6 9.6l.03.57h-8.7l-.07-.46c-.38-1.87-.74-2.24-1.98-2.3-1.44.04-2.05.71-2.1 1.9.03.85.3 1.22 1 1.66a15 15 0 0 0 3.32 1.17c5.93 1.62 9.26 4.2 9.22 9.39 0 3.11-1.2 5.84-3.32 7.76-2.13 1.92-5.14 3.03-8.73 3.03h-.1c-6.56 0-10.85-3.86-11.4-10.07zM154.3 12.04v31.38h-10.11V12.04M154.5 8.06a5.69 5.69 0 0 1-5.25 3.6h-.02a5.5 5.5 0 0 1-2.16-.44 5.84 5.84 0 0 1-3.5-5.39c0-.74.14-1.5.43-2.22A5.68 5.68 0 0 1 149.25 0c.73 0 1.47.14 2.18.45 2.19.93 3.5 3.1 3.5 5.39 0 .74-.14 1.5-.43 2.22M98.4 12.11h10.04l4.94 11.62 3.38-7.72-1.73-3.9h8.96l4.94 12.01 4.35-12.01h9.26v.54-.54h.81l-13.64 33.01-9.15-19.52-8.38 19.57zM88.13 2.17v12.34c-1.99-1.99-4.61-2.95-7.76-2.94-3.85 0-7.37 1.45-10.2 4.33a16.11 16.11 0 0 0-4.89 11.68c0 4.88 1.83 9.13 5.34 12.32 2.67 2.45 5.89 3.7 9.58 3.7h.06c3.18 0 5.77-.81 8.16-2.87v2.72h9.47V2.17h-9.76zm-6 32.27c-4.17-.02-7.07-2.94-7.08-6.9 0-3.8 2.96-6.99 6.77-7 3.74.01 6.63 3 6.65 7.1 0 3.92-2.91 6.8-6.34 6.8zM54.3 43.47v-.54h.53-.54v.54h-.54V26.34c0-1.39-.07-3.33-1.48-4.73a5.42 5.42 0 0 0-3.58-1.44c-1.3 0-2.7.56-3.5 1.3-1.64 1.47-1.7 3.54-1.7 5.34v16.66h-9.43V26.94c0-3.48.16-7.35 3.77-11.1 2.8-2.95 6.38-4.3 11-4.3 5.2 0 8.97 1.84 11.07 4.16 2.95 3.3 3.27 7.68 3.28 11.24v16.54h-8.89zM21.95 13.18v2.35c-1.9-1.9-4.41-2.8-7.42-2.8-3.73 0-7.12 1.44-9.84 4.21A15.58 15.58 0 0 0 0 28.19c0 4.68 1.76 8.77 5.15 11.85a13.13 13.13 0 0 0 9.21 3.57h.07c3.03 0 5.5-.78 7.79-2.73v2.55h9.16V13.18h-9.43zm-5.73 21.56c-4.02-.02-6.78-2.85-6.82-6.62a6.58 6.58 0 0 1 6.49-6.68c3.6 0 6.36 2.87 6.38 6.79 0 3.75-2.77 6.51-6.05 6.51z"/></svg>
			</a>

			<button class="navbar-toggler" type="button"
				data-bs-toggle="collapse" data-bs-target="#primary-navbar"
				aria-controls="primary-navbar" aria-expanded="false"
				aria-label="Toggle navigation">
				<i class="fas fa-bars" aria-hidden="true"></i>
			</button>

			<div id="primary-navbar" class="collapse navbar-collapse">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'primary_nav',
						'container'      => false,
						'menu_class'     => 'navbar-nav align-items-xl-center',
						'fallback_cb'    => '',
						'depth'          => 1,
						'walker'         => new Understrap_WP_Bootstrap_Navwalker(),
					)
				);
				?>
			</div>
		</div>
	</nav>
</header>
