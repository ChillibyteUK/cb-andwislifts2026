<?php
/**
 * Footer template.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

$current_year = gmdate( 'Y' );

// Social URLs live in the "social" group on Site-Wide Settings, matching
// cb-utility.php's social icon shortcodes. Empty channels are dropped.
//
// Keyed by slug so cb_social_icon() can look up the right glyph. Every key here
// must have a matching case in that function - there is deliberately no generic
// fallback icon, because a wrong logo is worse than none.
$social       = (array) get_field( 'social', 'option' );
$social_links = array_filter(
	array(
		'linkedin'  => $social['linkedin_url'] ?? '',
		'facebook'  => $social['facebook_url'] ?? '',
		'instagram' => $social['instagram_url'] ?? '',
		'x'         => $social['twitter_url'] ?? '',
		'youtube'   => $social['youtube_url'] ?? '',
		'pinterest' => $social['pinterest_url'] ?? '',
	)
);

$social_labels = array(
	'linkedin'  => 'LinkedIn',
	'facebook'  => 'Facebook',
	'instagram' => 'Instagram',
	'x'         => 'X',
	'youtube'   => 'YouTube',
	'pinterest' => 'Pinterest',
);
?>
<footer id="footer" class="site-footer">
	<div class="site-footer__spacer" aria-hidden="true"></div>
	<div class="container site-footer__main">
		<div class="row">
			<div class="col-lg-3 site-footer__brand">
				<a class="site-footer__logo" href="<?= esc_url( home_url( '/' ) ); ?>" aria-label="andwis lifts home">
					<span class="site-footer__logo-text">andwis<span>.</span></span>
				</a>
				<h2>Expertise, built in.</h2>
			</div>

			<div class="col-lg">
				<nav id="footer-nav" class="site-footer__nav" aria-label="<?php esc_attr_e( 'Footer navigation', 'cb-andwislifts2026' ); ?>">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'footer_menu',
							'container'      => false,
							'menu_class'     => 'site-footer__menu',
							'fallback_cb'    => false,
							'depth'          => 1,
						)
					);
					?>
				</nav>
			</div>

			<?php
			// Second column, for the contact routes that are not in the primary nav.
			if ( has_nav_menu( 'footer_menu_2' ) ) {
				?>
			<div class="col-lg">
				<nav id="footer-nav-2" class="site-footer__nav" aria-label="<?php esc_attr_e( 'Contact navigation', 'cb-andwislifts2026' ); ?>">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'footer_menu_2',
							'container'      => false,
							'menu_class'     => 'site-footer__menu',
							'fallback_cb'    => false,
							'depth'          => 1,
						)
					);
					?>
				</nav>
			</div>
				<?php
			}
			?>

			<?php if ( $social_links ) : ?>
				<div class="col-lg-auto site-footer__social">
					<?php
					foreach ( $social_links as $slug => $url ) {
						$icon = cb_social_icon( $slug );

						if ( ! $icon ) {
							continue;
						}
						?>
						<a href="<?= esc_url( $url ); ?>" aria-label="<?= esc_attr( $social_labels[ $slug ] ?? $slug ); ?>" target="_blank" rel="noopener">
							<?= $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted inline SVG ?>
						</a>
						<?php
					}
					?>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<div id="footer-legal" class="container site-footer__legal">
		<div class="row">
			<div class="col">
				&copy; <?= esc_html( $current_year ); ?> andwis lifts
			</div>
			<div class="col-auto site-footer__legal-links">
				<a href="<?= esc_url( home_url( '/privacy-policy/' ) ); ?>">Privacy Policy</a>
				<a href="<?= esc_url( home_url( '/cookie-policy/' ) ); ?>">Cookies</a>
			</div>
		</div>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
