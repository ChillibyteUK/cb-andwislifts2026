<?php
/**
 * Footer template.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

$linkedin_url  = get_field( 'linkedin_url', 'option' );
$facebook_url  = get_field( 'facebook_url', 'option' );
$instagram_url = get_field( 'instagram_url', 'option' );
$current_year  = gmdate( 'Y' );

$social_links = array_filter(
	array(
		'LinkedIn'  => $linkedin_url,
		'Facebook'  => $facebook_url,
		'Instagram' => $instagram_url,
	)
);
?>
<footer id="footer" class="site-footer">
	<div class="site-footer__spacer" aria-hidden="true"></div>
	<div class="site-footer__inner cb-wrap">
		<div class="site-footer__brand">
			<a class="site-footer__logo" href="<?= esc_url( home_url( '/' ) ); ?>" aria-label="andwis lifts home">
				<span class="site-footer__logo-text">andwis<span>.</span></span>
			</a>
			<h2>Expertise, built in.</h2>
		</div>

		<div id="footer-nav" class="site-footer__nav">
			<?php
			wp_nav_menu(
				array(
					'theme_location'       => 'footer_menu',
					'container'            => 'nav',
					'container_id'         => 'footer-nav-menu',
					'container_aria_label' => __( 'Footer navigation', 'cb-andwislifts2026' ),
					'menu_class'           => 'site-footer__menu',
					'fallback_cb'          => false,
					'depth'                => 2,
				)
			);
			?>
		</div>

		<?php if ( $social_links ) : ?>
			<div id="footer-social" class="site-footer__social" aria-label="Social links">
				<?php foreach ( $social_links as $label => $url ) : ?>
					<a href="<?= esc_url( $url ); ?>" aria-label="<?= esc_attr( $label ); ?>" target="_blank" rel="noopener">
						<?php if ( 'LinkedIn' === $label ) : ?>
							<svg viewBox="0 0 36 36" aria-hidden="true"><path d="M33.34 0H2.66A2.62 2.62 0 0 0 0 2.6v30.8C0 34.83 1.19 36 2.66 36h30.68A2.64 2.64 0 0 0 36 33.4V2.6C36 1.15 34.8 0 33.34 0zM10.68 30.68H5.34V13.49h5.34v17.19zM8.01 11.15A3.1 3.1 0 1 1 8 4.96a3.1 3.1 0 0 1 0 6.2zm22.67 19.53h-5.34v-8.36c0-1.99-.03-4.55-2.78-4.55-2.77 0-3.2 2.17-3.2 4.41v8.5h-5.32V13.49h5.11v2.35h.07a5.6 5.6 0 0 1 5.05-2.78c5.4 0 6.4 3.56 6.4 8.19v9.43z"/></svg>
						<?php elseif ( 'Facebook' === $label ) : ?>
							<svg viewBox="0 0 36 36" aria-hidden="true"><path d="M36 18.11C36 8.1 27.94 0 18 0S0 8.1 0 18.11C0 27.16 6.58 34.66 15.19 36V23.35h-4.57v-5.24h4.57v-3.99c0-4.54 2.68-7.04 6.79-7.04 1.97 0 4.03.35 4.03.35v4.46h-2.27c-2.23 0-2.93 1.4-2.93 2.82v3.4h4.99l-.8 5.24h-4.19V36C29.42 34.66 36 27.16 36 18.11z"/></svg>
						<?php else : ?>
							<svg viewBox="0 0 36 36" aria-hidden="true"><path d="M18 8.76A9.24 9.24 0 1 0 18 27.24 9.24 9.24 0 0 0 18 8.76zm0 15.24A6 6 0 1 1 18 12a6 6 0 0 1 0 12z"/><path d="M27.61 8.39a2.16 2.16 0 1 1-4.32 0 2.16 2.16 0 0 1 4.32 0z"/><path d="M18 0c-4.89 0-5.5.02-7.42.11-1.91.09-3.22.39-4.36.83a8.78 8.78 0 0 0-3.18 2.1A8.78 8.78 0 0 0 .94 6.22C.5 7.36.2 8.67.11 10.58.02 12.5 0 13.11 0 18s.02 5.5.11 7.42c.09 1.91.39 3.22.83 4.36a8.78 8.78 0 0 0 2.1 3.18 8.78 8.78 0 0 0 3.18 2.1c1.14.44 2.45.74 4.36.83 1.92.09 2.53.11 7.42.11s5.5-.02 7.42-.11c1.91-.09 3.22-.39 4.36-.83a9.14 9.14 0 0 0 5.28-5.28c.44-1.14.74-2.45.83-4.36.09-1.92.11-2.53.11-7.42s-.02-5.5-.11-7.42c-.09-1.91-.39-3.22-.83-4.36a8.78 8.78 0 0 0-2.1-3.18 8.78 8.78 0 0 0-3.18-2.1C28.64.5 27.33.2 25.42.11 23.5.02 22.89 0 18 0zm0 3.24c4.8 0 5.37.02 7.27.11 1.76.08 2.71.37 3.34.62.84.33 1.44.72 2.07 1.35.63.63 1.02 1.23 1.35 2.07.25.63.54 1.58.62 3.34.09 1.9.11 2.47.11 7.27s-.02 5.37-.11 7.27c-.08 1.76-.37 2.71-.62 3.34a5.9 5.9 0 0 1-3.42 3.42c-.63.25-1.58.54-3.34.62-1.9.09-2.47.11-7.27.11s-5.37-.02-7.27-.11c-1.76-.08-2.71-.37-3.34-.62a5.54 5.54 0 0 1-2.07-1.35 5.54 5.54 0 0 1-1.35-2.07c-.25-.63-.54-1.58-.62-3.34-.09-1.9-.11-2.47-.11-7.27s.02-5.37.11-7.27c.08-1.76.37-2.71.62-3.34.33-.84.72-1.44 1.35-2.07a5.54 5.54 0 0 1 2.07-1.35c.63-.25 1.58-.54 3.34-.62 1.9-.09 2.47-.11 7.27-.11z"/></svg>
						<?php endif; ?>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>

	<div id="footer-legal" class="site-footer__legal cb-wrap">
		<div>&copy; <?= esc_html( $current_year ); ?> andwis lifts</div>
		<div class="site-footer__legal-links">
			<a href="<?= esc_url( home_url( '/privacy-policy/' ) ); ?>">Privacy Policy</a>
			<a href="<?= esc_url( home_url( '/cookie-policy/' ) ); ?>">Cookies</a>
		</div>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
