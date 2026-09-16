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

// Statutory disclosure for the footer. A UK limited company must show its
// registered name, number, place of registration and registered office on its
// website, plus the VAT number where it is registered. All of it is maintained
// on Site-Wide Settings > Legal, and anything left empty is simply omitted.
$legal_name   = trim( (string) get_field( 'registered_name', 'option' ) );
$legal_number = trim( (string) get_field( 'company_number', 'option' ) );
$legal_place  = trim( (string) get_field( 'place_of_registration', 'option' ) );
$legal_vat    = trim( (string) get_field( 'vat_number', 'option' ) );
$legal_office = trim( (string) get_field( 'registered_office', 'option' ) );

$legal_lines = array();

if ( $legal_name ) {
	$registration = $legal_name;

	if ( $legal_place ) {
		/* translators: %s: part of the UK the company is registered in. */
		$registration .= ' ' . sprintf( __( 'is registered in %s', 'cb-andwislifts2026' ), $legal_place );
	}

	if ( $legal_number ) {
		/* translators: %s: company registration number. */
		$registration .= ( $legal_place ? ', ' : ' ' ) . sprintf( __( 'company number %s', 'cb-andwislifts2026' ), $legal_number );
	}

	$legal_lines[] = $registration . '.';
} elseif ( $legal_number ) {
	/* translators: %s: company registration number. */
	$legal_lines[] = sprintf( __( 'Company number %s.', 'cb-andwislifts2026' ), $legal_number );
}

if ( $legal_office ) {
	// Entered a line per line, shown as one line.
	$office = implode( ', ', array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $legal_office ) ) ) );

	/* translators: %s: registered office address. */
	$legal_lines[] = sprintf( __( 'Registered office: %s.', 'cb-andwislifts2026' ), $office );
}

if ( $legal_vat ) {
	/* translators: %s: VAT registration number. */
	$legal_lines[] = sprintf( __( 'VAT registration number %s.', 'cb-andwislifts2026' ), $legal_vat );
}

// Legal links, in the order they should read. A page that does not exist is
// dropped rather than linked to a 404, and the permalink is looked up so a
// nested page still resolves.
$legal_pages = array();

foreach ( array(
	'privacy-policy' => __( 'Privacy Policy', 'cb-andwislifts2026' ),
	'cookie-policy'  => __( 'Cookies', 'cb-andwislifts2026' ),
	'terms-of-use'   => __( 'Terms of Use', 'cb-andwislifts2026' ),
) as $legal_slug => $legal_label ) {
	$legal_page = get_page_by_path( $legal_slug );

	if ( $legal_page && 'publish' === $legal_page->post_status ) {
		$legal_pages[ get_permalink( $legal_page ) ] = $legal_label;
	}
}

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
			<?php if ( $legal_pages ) : ?>
			<div class="col-auto site-footer__legal-links">
				<?php foreach ( $legal_pages as $legal_url => $legal_label ) : ?>
				<a href="<?= esc_url( $legal_url ); ?>"><?= esc_html( $legal_label ); ?></a>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
		</div>
		<?php if ( $legal_lines ) : ?>
		<p class="site-footer__legal-details">
			<?php
			// One statement per line. Each is escaped individually and joined with
			// literal markup, which also stops a VAT number wrapping mid-way.
			echo implode( '<br>', array_map( 'esc_html', $legal_lines ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>
		</p>
		<?php endif; ?>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
