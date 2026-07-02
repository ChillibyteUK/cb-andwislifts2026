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
		href="<?= esc_url( get_stylesheet_directory_uri() . '/fonts/inter-v20-latin-regular.woff2' ); ?>"
		as="font" type="font/woff2" crossorigin="anonymous">
	<link rel="preload"
        href="<?= esc_url( get_stylesheet_directory_uri() . '/fonts/inter-v20-latin-500.woff2' ); ?>"
        as="font" type="font/woff2" crossorigin="anonymous">
	<link rel="preload"
        href="<?= esc_url( get_stylesheet_directory_uri() . '/fonts/inter-v20-latin-600.woff2' ); ?>"
        as="font" type="font/woff2" crossorigin="anonymous">
	<link rel="preload"
        href="<?= esc_url( get_stylesheet_directory_uri() . '/fonts/inter-v20-latin-700.woff2' ); ?>"
        as="font" type="font/woff2" crossorigin="anonymous">
	<link rel="preload"
        href="<?= esc_url( get_stylesheet_directory_uri() . '/fonts/inter-v20-latin-800.woff2' ); ?>"
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
<!-- UTILITY -->
<div class="utility fs-caption">
	<div class="container">
		<div class="d-flex justify-content-between align-items-center">
			<div class="d-flex align-items-center gap-2">
				<span class="utility-pill">Live Build</span>
				<span>Currently delivering 4 UK structures this month — average build time of 8 days</span>
			</div>
			<div class="d-flex align-items-center gap-4 fw-medium">
				<a href="tel:<?= esc_attr( parse_phone( get_field( 'contact_phone', 'option' ) ) ); ?>"><?= esc_html( get_field( 'contact_phone', 'option' ) ); ?></a>
				<div class="vr"></div>
				<a href="mailto:<?= esc_attr( antispambot( get_field( 'contact_email', 'option' ) ) ); ?>"><?= esc_html( antispambot( get_field( 'contact_email', 'option' ) ) ); ?></a>
				<div class="vr"></div>
				<a href="https://hts-tentiq.com/eu-en/" target="_blank" rel="noopener">HTS-Tentiq Global ↗</a>
			</div>
		</div>
	</div>
</div>

<!-- PRIMARY NAV -->
<header id="wrapper-navbar" class="sticky-top">
	<nav class="navbar navbar-expand-xl" aria-label="Primary navigation">
		<div class="container">
			<a class="navbar-brand" href="<?= esc_url( home_url( '/' ) ); ?>" aria-label="HTS Industries home">
				<img src="<?= esc_url( get_stylesheet_directory_uri() . '/img/HTS_Logo.png' ); ?>"
					alt="HTS Industries">
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
						'menu_class'     => 'navbar-nav mx-xl-auto align-items-xl-center',
						'fallback_cb'    => '',
						'depth'          => 1,
						'walker'         => new Understrap_WP_Bootstrap_Navwalker(),
					)
				);
				?>

				<div class="nav-cta d-flex flex-column flex-xl-row align-items-stretch align-items-xl-center gap-2 mt-3 mt-lg-0">
					<a href="#configurator" class="btn btn-outline-dark">Start Designing</a>
					<a href="#contact" class="btn btn-primary">Get an Estimate</a>
				</div>
			</div>
		</div>
	</nav>
</header>
