<?php
/**
 * Footer data and behaviour.
 *
 * footer.php is the markup. Everything it needs to work out first lives here,
 * so the template stays readable.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

/**
 * The brand block's copy, from Site-Wide Settings > Footer.
 *
 * Each field falls back to the phase two design's wording when left empty, so
 * the footer is complete before anyone has visited the settings page.
 *
 * @return array{strapline: string, text: string, link: array{url: string, title: string, target: string}}
 */
function cb_footer_brand() {
	$get = static function ( $name ) {
		return function_exists( 'get_field' ) ? get_field( $name, 'option' ) : null;
	};

	$strapline = trim( (string) $get( 'footer_strapline' ) );
	$text      = trim( (string) $get( 'footer_text' ) );
	$link      = $get( 'footer_link' );

	if ( ! is_array( $link ) || empty( $link['url'] ) ) {
		$link = array(
			'url'    => 'https://www.andwis.com/',
			'title'  => __( 'Discover the andwis group', 'cb-andwislifts2026' ),
			'target' => '_blank',
		);
	}

	return array(
		'strapline' => '' !== $strapline ? $strapline : __( 'An andwis Group company.', 'cb-andwislifts2026' ),
		'text'      => '' !== $text ? $text : __( 'We are experts in technical and compliance services for the built environment, to protect people, places and our planet.', 'cb-andwislifts2026' ),
		'link'      => array(
			'url'    => (string) $link['url'],
			'title'  => ! empty( $link['title'] ) ? (string) $link['title'] : __( 'Discover the andwis group', 'cb-andwislifts2026' ),
			'target' => ! empty( $link['target'] ) ? (string) $link['target'] : '',
		),
	);
}

/**
 * The footer's link columns: one per footer menu location that has a menu.
 *
 * The column heading is the menu's own name, so "About andwis lifts",
 * "Services" and "Sectors" are simply what the three menus are called in
 * Appearance > Menus.
 *
 * @return array<int, array{id: string, heading: string, items: object[]}>
 */
function cb_footer_columns() {
	$locations = get_nav_menu_locations();
	$columns   = array();

	foreach ( array( 'footer_menu', 'footer_menu_2', 'footer_menu_3' ) as $location ) {
		$menu = ! empty( $locations[ $location ] ) ? wp_get_nav_menu_object( $locations[ $location ] ) : false;

		if ( ! $menu ) {
			continue;
		}

		$items = wp_get_nav_menu_items( $menu->term_id, array( 'update_post_term_cache' => false ) );
		$items = array_values(
			array_filter(
				(array) $items,
				static function ( $item ) {
					// Columns are flat lists. Anything nested is ignored.
					return ! (int) $item->menu_item_parent;
				}
			)
		);

		if ( ! $items ) {
			continue;
		}

		$columns[] = array(
			'id'      => 'footer-col-' . $location,
			'heading' => $menu->name,
			'items'   => $items,
		);
	}

	return $columns;
}

/**
 * Social profiles to show, keyed by network slug.
 *
 * URLs live in the "social" group on Site-Wide Settings. Empty ones are
 * dropped, and so is any network cb_footer_social_icon() has no glyph for -
 * a wrong logo is worse than none.
 *
 * @return array<string, array{url: string, label: string}>
 */
function cb_footer_social_links() {
	$social = function_exists( 'get_field' ) ? (array) get_field( 'social', 'option' ) : array();
	$all    = array(
		'linkedin'  => array( $social['linkedin_url'] ?? '', 'LinkedIn' ),
		'youtube'   => array( $social['youtube_url'] ?? '', 'YouTube' ),
		'facebook'  => array( $social['facebook_url'] ?? '', 'Facebook' ),
		'instagram' => array( $social['instagram_url'] ?? '', 'Instagram' ),
		'x'         => array( $social['twitter_url'] ?? '', 'X' ),
		'pinterest' => array( $social['pinterest_url'] ?? '', 'Pinterest' ),
	);
	$links  = array();

	foreach ( $all as $slug => $entry ) {
		if ( '' !== trim( (string) $entry[0] ) && cb_footer_social_icon( $slug ) ) {
			$links[ $slug ] = array(
				'url'   => (string) $entry[0],
				'label' => $entry[1],
			);
		}
	}

	return $links;
}

/**
 * Glyph for a social network, drawn to sit inside the footer's white discs.
 *
 * LinkedIn gets its own: the shared icon is the logo's rounded square with the
 * letters knocked out, which on a disc reads as a dark box. The design shows
 * the letters alone.
 *
 * @param string $slug Network slug.
 * @return string Inline SVG, or an empty string.
 */
function cb_footer_social_icon( $slug ) {
	if ( 'linkedin' === $slug ) {
		return '<svg viewBox="0 0 36 36" aria-hidden="true" focusable="false"><path d="M10.68 30.68H5.34V13.49h5.34v17.19zM8.01 11.15A3.1 3.1 0 1 1 8 4.96a3.1 3.1 0 0 1 0 6.2zM30.68 30.68h-5.34v-8.36c0-1.99-.03-4.55-2.78-4.55-2.77 0-3.2 2.17-3.2 4.41v8.5h-5.32V13.49h5.11v2.35h.07a5.6 5.6 0 0 1 5.05-2.78c5.4 0 6.4 3.56 6.4 8.19v9.43z"/></svg>';
	}

	return function_exists( 'cb_social_icon' ) ? (string) cb_social_icon( $slug ) : '';
}

/**
 * Legal links for the footer, as URL => label, in reading order.
 *
 * Only published pages are linked - a page that is missing or still a draft is
 * dropped rather than sent to a 404. Each entry lists the slugs it may live
 * under, first match wins.
 *
 * @return array<string, string>
 */
function cb_footer_legal_links() {
	$wanted = array(
		array( array( 'privacy-policy' ), __( 'Privacy policy', 'cb-andwislifts2026' ) ),
		array( array( 'cookie-policy', 'cookies' ), __( 'Cookies', 'cb-andwislifts2026' ) ),
		array( array( 'terms-and-conditions', 'terms-of-use' ), __( 'Terms and conditions', 'cb-andwislifts2026' ) ),
		array( array( 'sitemap' ), __( 'Sitemap', 'cb-andwislifts2026' ) ),
	);
	$links  = array();

	foreach ( $wanted as $entry ) {
		foreach ( $entry[0] as $slug ) {
			$page = get_page_by_path( $slug );

			if ( $page && 'publish' === $page->post_status ) {
				$links[ get_permalink( $page ) ] = $entry[1];
				break;
			}
		}
	}

	return $links;
}

/**
 * Statutory company disclosure, one statement per line.
 *
 * A UK limited company must show its registered name, number, place of
 * registration and registered office on its website, plus the VAT number where
 * it is registered. All of it is maintained on Site-Wide Settings > Legal, and
 * anything left empty is simply omitted.
 *
 * The phase two design does not draw these lines. They stay because the law
 * requires them, set small beneath the back-to-top control.
 *
 * @return string[]
 */
function cb_footer_legal_lines() {
	$get = static function ( $name ) {
		return function_exists( 'get_field' ) ? trim( (string) get_field( $name, 'option' ) ) : '';
	};

	$name   = $get( 'registered_name' );
	$number = $get( 'company_number' );
	$place  = $get( 'place_of_registration' );
	$vat    = $get( 'vat_number' );
	$office = $get( 'registered_office' );
	$lines  = array();

	if ( $name ) {
		$registration = $name;

		if ( $place ) {
			/* translators: %s: part of the UK the company is registered in. */
			$registration .= ' ' . sprintf( __( 'is registered in %s', 'cb-andwislifts2026' ), $place );
		}

		if ( $number ) {
			/* translators: %s: company registration number. */
			$registration .= ( $place ? ', ' : ' ' ) . sprintf( __( 'company number %s', 'cb-andwislifts2026' ), $number );
		}

		$lines[] = $registration . '.';
	} elseif ( $number ) {
		/* translators: %s: company registration number. */
		$lines[] = sprintf( __( 'Company number %s.', 'cb-andwislifts2026' ), $number );
	}

	if ( $office ) {
		// Entered a line per line, shown as one line.
		$office = implode( ', ', array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $office ) ) ) );

		/* translators: %s: registered office address. */
		$lines[] = sprintf( __( 'Registered office: %s.', 'cb-andwislifts2026' ), $office );
	}

	if ( $vat ) {
		/* translators: %s: VAT registration number. */
		$lines[] = sprintf( __( 'VAT registration number %s.', 'cb-andwislifts2026' ), $vat );
	}

	return $lines;
}

/**
 * Where the footer's "Contact us" goes.
 *
 * @return string URL, or an empty string when there is no published contact page.
 */
function cb_footer_contact_url() {
	$page = get_page_by_path( 'contact' );

	return $page && 'publish' === $page->post_status ? (string) get_permalink( $page ) : '';
}

/**
 * Behaviour for the footer: the small-screen column accordion and back to top.
 *
 * Printed on wp_footer like the blocks' scripts. Both features degrade without
 * it: the columns stay open, because the class that lets CSS collapse them is
 * only added here, and "Back to top" is a plain link to #top.
 *
 * @return void
 */
function cb_footer_script() {
	?>
	<script id="cb-footer-js">
	(function () {
		var footer = document.getElementById('footer');
		if (!footer) { return; }

		footer.classList.add('site-footer--js');

		Array.prototype.forEach.call(footer.querySelectorAll('.site-footer__col-toggle'), function (toggle) {
			toggle.addEventListener('click', function () {
				var open = toggle.getAttribute('aria-expanded') !== 'true';
				toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
				toggle.closest('.site-footer__col').classList.toggle('is-open', open);
			});
		});

		var top = footer.querySelector('[data-cb-back-to-top]');
		if (!top) { return; }

		top.addEventListener('click', function (event) {
			event.preventDefault();

			var calm = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

			if (!calm && window.lenis && typeof window.lenis.scrollTo === 'function') {
				window.lenis.scrollTo(0, { duration: 1 });
			} else {
				window.scrollTo({ top: 0, behavior: calm ? 'auto' : 'smooth' });
			}

			// Send keyboard focus up with the page, or the next Tab would land
			// straight back in the footer.
			var home = document.querySelector('#wrapper-navbar .navbar-brand');
			if (home) { home.focus({ preventScroll: true }); }
		});
	})();
	</script>
	<?php
}
add_action( 'wp_footer', 'cb_footer_script', 30 );
