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
				<svg xmlns="http://www.w3.org/2000/svg" role="img" aria-label="andwis lifts" viewBox="0 0 223.94 33.32" xml:space="preserve"><path class="dot" d="M150.13 24.31c2.27 0 4.1 1.83 4.1 4.09s-1.84 4.09-4.1 4.09-4.1-1.83-4.1-4.09 1.84-4.09 4.1-4.09"/><path class="andwis" d="M43.73 32.52v-12.6c0-1.07-.03-2.72-1.31-3.96-.77-.73-2.01-1.24-3.16-1.24s-2.34.47-3.08 1.12c-1.54 1.35-1.51 3.22-1.51 4.66v12.03h-6.66v-12.15c0-2.69.12-5.5 2.9-8.3 2.16-2.19 4.86-3.19 8.47-3.19s6.93 1.39 8.52 3.08c2.24 2.42 2.51 5.7 2.51 8.42v12.15h-6.68v-.02ZM116.02 9.19h7.16V32.5h-7.16z"/><path class="andwis" d="M119.61 0c2.25 0 4.07 1.82 4.07 4.06s-1.82 4.06-4.07 4.06-4.07-1.82-4.07-4.06 1.82-4.06 4.07-4.06"/><path class="andwis" d="M71.35 29.91c-2.04 1.99-4.12 2.72-6.9 2.72s-5.26-.9-7.28-2.72c-2.7-2.39-4.09-5.5-4.09-9.14s1.3-6.32 3.73-8.66c2.18-2.12 4.82-3.21 7.77-3.21s4.99.95 6.55 2.94V.89h6.85v31.63h-6.63v-2.61ZM65.98 26.45c2.95 0 5.42-2.42 5.42-5.63s-2.48-5.85-5.67-5.85-5.78 2.64-5.78 5.77 2.51 5.71 6.03 5.71"/><path class="andwis" d="M131.94 25.35v.22c0 1.25 1 2.14 2.44 2.14s2.26-.72 2.26-1.97c0-1.84-1.81-1.75-4.62-2.64-3.9-1.22-5.79-3.31-5.79-6.7s3.23-7.5 8.61-7.5 7.65 2.64 7.9 6.92h-6.01c-.25-1.47-.8-2.09-1.98-2.09s-2.06.75-2.06 1.84c0 1.55 1.39 1.92 3.7 2.56 4.62 1.25 6.93 3.02 6.93 6.75s-3.57 7.8-9.02 7.8-8.15-2.72-8.6-7.3h6.26-.02v-.02ZM114.33 9.21 104.05 33.32l-7.3-15.11-6.68 15.11L79.69 9.21h7.1l4.22 9.64 3.18-7.04-1.2-2.6h6.21l4.28 10.06 3.75-10.06h7.1ZM18.3 29.9c-2.04 1.99-4.12 2.72-6.92 2.72s-5.26-.9-7.3-2.72C1.39 27.51 0 24.38 0 20.75s1.31-6.33 3.73-8.67c2.18-2.12 4.82-3.21 7.77-3.21s4.99.95 6.56 2.94v-2.61h6.86v23.28h-6.65v-2.61h.02v.02ZM12.91 26.44c2.95 0 5.42-2.42 5.42-5.63s-2.48-5.85-5.69-5.85-5.78 2.64-5.78 5.77 2.51 5.71 6.04 5.71"/><path class="dot" d="M157.1 1.83h6.96v30.71h-6.96zM185.2 9.9v-.56c0-1.13.2-1.85.64-2.28.47-.46 1.29-.65 2.6-.61h.36V1.08h-.34c-3.45-.1-5.92.62-7.52 2.17-1.37 1.34-2.07 3.32-2.07 5.89v.75h-2.49v5.26h2.49v17.38h6.33V15.16h3.84V9.9h-3.84ZM204.61 15.16V9.9h-4.91V4.48h-6.29V9.9h-2.61v5.26h2.61v10.22c0 4.75 2.48 7.16 7.36 7.16h3.84v-5.38h-2.93c-1.68 0-1.98-.59-1.98-1.74V15.16h4.91ZM166.96 9.23h7.16v23.31h-7.16z"/><path class="dot" d="M170.54.04c2.25 0 4.07 1.82 4.07 4.06s-1.82 4.06-4.07 4.06-4.07-1.82-4.07-4.06S168.3.04 170.54.04"/><path class="dot" d="M212.55 25.22v.22c0 1.25 1 2.14 2.44 2.14s2.26-.72 2.26-1.97c0-1.84-1.81-1.75-4.62-2.64-3.9-1.22-5.79-3.31-5.79-6.7s3.23-7.5 8.61-7.5 7.65 2.64 7.9 6.92h-6.01c-.25-1.47-.8-2.09-1.98-2.09s-2.06.75-2.06 1.84c0 1.55 1.39 1.92 3.7 2.56 4.62 1.25 6.93 3.02 6.93 6.75s-3.57 7.8-9.02 7.8-8.15-2.72-8.61-7.3h6.26-.02v-.02Z"/></svg>
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
