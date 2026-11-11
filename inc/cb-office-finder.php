<?php
/**
 * Office finder: the nearest andwis lifts offices to a postcode.
 *
 * The CB Office Finder block asks for a postcode and lists the three closest
 * offices with their distance. It always answers with the nearest three and
 * never with "none in range": seven offices cover the whole country, so any
 * radius would leave some visitors with an empty result.
 *
 * The lookup runs on this site's server, not in the visitor's browser. The
 * browser asks our own REST route; the route turns the postcode into a
 * location with postcodes.io (free, open UK postcode data, no account) and
 * does the distance sums. So the visitor's postcode and IP address are never
 * sent to a third party from their device, and every answer is cached.
 *
 * Offices are the ones in Site-wide Settings: the one list the site keeps.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

/**
 * How many offices an answer holds.
 */
const CB_OFFICE_FINDER_LIMIT = 3;

/**
 * A UK postcode, or just its outward half ("LS1"), in any spacing or case.
 */
const CB_OFFICE_FINDER_PATTERN = '/^([A-Z]{1,2}\d[A-Z\d]?)\s*(\d[A-Z]{2})?$/';

/**
 * Tidy a postcode into "LS1 4AP" or "LS1" form.
 *
 * @param string $postcode Whatever was typed.
 * @return string The tidied postcode, or an empty string if it is not one.
 */
function cb_office_finder_normalise( $postcode ) {
	$postcode = strtoupper( trim( preg_replace( '/\s+/', ' ', (string) $postcode ) ) );

	if ( ! preg_match( CB_OFFICE_FINDER_PATTERN, $postcode, $parts ) ) {
		return '';
	}

	return isset( $parts[2] ) && '' !== $parts[2] ? $parts[1] . ' ' . $parts[2] : $parts[1];
}

/**
 * Where a postcode is, as latitude and longitude.
 *
 * Cached for 30 days when found. A miss is remembered for an hour, so a typo
 * or an outage is not asked about again on every request.
 *
 * @param string $postcode A tidied postcode from cb_office_finder_normalise().
 * @return float[]|null [ latitude, longitude ], or null if it is not known.
 */
function cb_office_finder_locate( $postcode ) {
	if ( '' === $postcode ) {
		return null;
	}

	$key    = 'cb_pc_' . md5( $postcode );
	$cached = get_transient( $key );

	if ( is_array( $cached ) ) {
		return $cached ? $cached : null;
	}

	// A full postcode and an outward code live at different addresses.
	$path     = false !== strpos( $postcode, ' ' ) ? 'postcodes/' : 'outcodes/';
	$response = wp_remote_get( 'https://api.postcodes.io/' . $path . rawurlencode( $postcode ), array( 'timeout' => 6 ) );
	$body     = is_wp_error( $response ) ? null : json_decode( wp_remote_retrieve_body( $response ), true );

	// A postcode that has been retired still has a place on the map, and
	// people go on using them for years.
	$result = is_array( $body ) ? ( $body['result'] ?? $body['terminated'] ?? null ) : null;
	$result = isset( $result['latitude'], $result['longitude'] ) ? $result : null;

	if ( ! $result ) {
		set_transient( $key, array(), HOUR_IN_SECONDS );

		return null;
	}

	$place = array( (float) $result['latitude'], (float) $result['longitude'] );

	set_transient( $key, $place, 30 * DAY_IN_SECONDS );

	return $place;
}

/**
 * The Contact page's CB Offices block, where the offices used to be kept.
 *
 * @return array|null The parsed block, or null if there is none.
 */
function cb_offices_shared_block() {
	$page = get_page_by_path( 'contact' );

	if ( ! $page ) {
		return null;
	}

	foreach ( parse_blocks( $page->post_content ) as $block ) {
		// The first offices block there that holds its own list.
		if ( 'acf/cb-offices' === $block['blockName'] && empty( $block['attrs']['data']['use_shared'] ) ) {
			return $block;
		}
	}

	return null;
}

/**
 * The site's offices, as the Offices repeater returns them.
 *
 * They are kept in Site-wide Settings. Before that list existed they were
 * entered on the Contact page's CB Offices block, which is still read if the
 * settings list is empty.
 *
 * @return array[]
 */
function cb_offices_shared() {
	$offices = function_exists( 'get_field' ) ? get_field( 'offices', 'option' ) : null;

	if ( is_array( $offices ) && $offices ) {
		return $offices;
	}

	$block = cb_offices_shared_block();

	if ( ! $block || ! function_exists( 'acf_setup_meta' ) ) {
		return array();
	}

	// Lets get_field() read another block's saved values.
	acf_setup_meta( $block['attrs']['data'] ?? array(), 'cb_offices_shared' );
	$offices = get_field( 'offices', 'cb_offices_shared' );
	acf_reset_meta( 'cb_offices_shared' );

	return is_array( $offices ) ? $offices : array();
}

/**
 * An office's locations, each with a label, address and phone.
 *
 * A region can hold several offices - a service site and a projects site,
 * say - each with its own address and number. Where Locations is empty the
 * older single address and phone are used instead, so rows entered before
 * that field existed keep working.
 *
 * @param array $office One row of the Offices repeater.
 * @return array<int, array{label: string, address: string, phone: string}>
 */
function cb_offices_locations( $office ) {
	$locations = array();

	foreach ( (array) ( $office['locations'] ?? array() ) as $location ) {
		$address = trim( (string) ( $location['address'] ?? '' ) );
		$phone   = trim( (string) ( $location['phone'] ?? '' ) );

		if ( '' !== $address || '' !== $phone ) {
			$locations[] = array(
				'label'   => trim( (string) ( $location['label'] ?? '' ) ),
				'address' => $address,
				'phone'   => $phone,
			);
		}
	}

	$address = trim( (string) ( $office['address'] ?? '' ) );
	$phone   = trim( (string) ( $office['phone'] ?? '' ) );

	if ( ! $locations && ( '' !== $address || '' !== $phone ) ) {
		$locations[] = array(
			'label'   => '',
			'address' => $address,
			'phone'   => $phone,
		);
	}

	return $locations;
}

/**
 * CB Offices, plain list: on a phone each office folds down to its name and
 * opens when pressed. Printed once. Wider than a phone, and without
 * JavaScript, every office is simply shown.
 *
 * @return void
 */
function cb_offices_list_script() {
	static $printed = false;

	if ( $printed ) {
		return;
	}

	$printed = true;
	?>
<script>
(function () {
	var phone = window.matchMedia('(max-width: 575.98px)');
	var each = function (list, fn) { Array.prototype.forEach.call(list, fn); };

	each(document.querySelectorAll('.cb-offices--list'), function (root) {
		var entries = root.querySelectorAll('.cb-offices__entry');

		function arrange() {
			root.classList.toggle('is-accordion', phone.matches);

			each(entries, function (entry) {
				var name = entry.querySelector('h3');
				var body = entry.querySelector('.cb-offices__entry-body');
				if (!name || !body) return;

				if (phone.matches) {
					var open = entry.classList.contains('is-open');
					name.setAttribute('role', 'button');
					name.setAttribute('tabindex', '0');
					name.setAttribute('aria-expanded', open ? 'true' : 'false');
					body.hidden = !open;
				} else {
					name.removeAttribute('role');
					name.removeAttribute('tabindex');
					name.removeAttribute('aria-expanded');
					body.hidden = false;
				}
			});
		}

		each(entries, function (entry) {
			var name = entry.querySelector('h3');
			if (!name) return;

			function toggle() {
				if (!phone.matches) return;
				entry.classList.toggle('is-open');
				arrange();
			}

			name.addEventListener('click', toggle);
			name.addEventListener('keydown', function (event) {
				if (event.key === 'Enter' || event.key === ' ') {
					event.preventDefault();
					toggle();
				}
			});
		});

		arrange();
		if (phone.addEventListener) { phone.addEventListener('change', arrange); }
	});
})();
</script>
	<?php
}

/**
 * Every office location on the Contact page, flattened to one row each.
 *
 * An office may list several locations, or just a single address from before
 * locations existed; both are read.
 *
 * @return array<int, array{office: string, label: string, address: string, phone: string, postcode: string}>
 */
function cb_office_finder_locations() {
	$rows = array();

	foreach ( cb_offices_shared() as $office ) {
		foreach ( cb_offices_locations( $office ) as $place ) {
			$address = implode( ', ', array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $place['address'] ) ) ) );

			// The postcode is the last thing in the address.
			if ( ! preg_match( '/([A-Z]{1,2}\d[A-Z\d]?\s*\d[A-Z]{2})\s*$/i', $address, $found ) ) {
				continue;
			}

			$rows[] = array(
				'office'   => trim( (string) ( $office['name'] ?? '' ) ),
				'label'    => $place['label'],
				'address'  => $address,
				'phone'    => $place['phone'],
				'postcode' => cb_office_finder_normalise( $found[1] ),
			);
		}
	}

	return $rows;
}

/**
 * Miles between two points, as the crow flies.
 *
 * @param float[] $from [ latitude, longitude ].
 * @param float[] $to   [ latitude, longitude ].
 * @return float
 */
function cb_office_finder_miles( $from, $to ) {
	$lat = deg2rad( $to[0] - $from[0] );
	$lng = deg2rad( $to[1] - $from[1] );
	$a   = sin( $lat / 2 ) ** 2 + cos( deg2rad( $from[0] ) ) * cos( deg2rad( $to[0] ) ) * sin( $lng / 2 ) ** 2;

	return 3958.8 * 2 * atan2( sqrt( $a ), sqrt( 1 - $a ) );
}

/**
 * The offices nearest a postcode, closest first.
 *
 * @param string $postcode A tidied postcode.
 * @return array[]|null Up to CB_OFFICE_FINDER_LIMIT offices, each with `miles`; null if the postcode is unknown.
 */
function cb_office_finder_nearest( $postcode ) {
	$from = cb_office_finder_locate( $postcode );

	if ( ! $from ) {
		return null;
	}

	$offices = array();

	foreach ( cb_office_finder_locations() as $office ) {
		$to = cb_office_finder_locate( $office['postcode'] );

		if ( $to ) {
			$miles           = cb_office_finder_miles( $from, $to );
			$office['miles'] = $miles < 10 ? round( $miles, 1 ) : round( $miles );
			$offices[]       = $office;
		}
	}

	usort(
		$offices,
		static function ( $a, $b ) {
			return $a['miles'] <=> $b['miles'];
		}
	);

	return array_slice( $offices, 0, CB_OFFICE_FINDER_LIMIT );
}

/**
 * GET /wp-json/cb/v1/nearest-offices?postcode=LS1 4AP
 *
 * @return void
 */
function cb_office_finder_routes() {
	register_rest_route(
		'cb/v1',
		'/nearest-offices',
		array(
			'methods'             => 'GET',
			'permission_callback' => '__return_true',
			'args'                => array(
				'postcode' => array(
					'required'          => true,
					'sanitize_callback' => 'cb_office_finder_normalise',
				),
			),
			'callback'            => static function ( WP_REST_Request $request ) {
				$postcode = (string) $request->get_param( 'postcode' );
				$offices  = '' !== $postcode ? cb_office_finder_nearest( $postcode ) : null;

				if ( null === $offices ) {
					return new WP_Error( 'cb_postcode_unknown', __( 'We could not find that postcode. Please check it and try again.', 'cb-andwislifts2026' ), array( 'status' => 404 ) );
				}

				return rest_ensure_response(
					array(
						'postcode' => $postcode,
						'offices'  => $offices,
					)
				);
			},
		)
	);
}
add_action( 'rest_api_init', 'cb_office_finder_routes' );

/**
 * Behaviour for the CB Office Finder block. Printed once, where it is used.
 *
 * @return void
 */
function cb_office_finder_script() {
	if ( empty( $GLOBALS['cb_office_finder_rendered'] ) ) {
		return;
	}
	?>
	<script id="cb-office-finder-js">
	(function () {
		Array.prototype.forEach.call(document.querySelectorAll('[data-cb-office-finder]'), function (root) {
			var form = root.querySelector('form');
			var input = root.querySelector('input[name="postcode"]');
			var status = root.querySelector('.cb-office-finder__status');
			var list = root.querySelector('.cb-office-finder__results');
			var text = JSON.parse(root.getAttribute('data-cb-office-finder'));

			function el(tag, className, content) {
				var node = document.createElement(tag);
				if (className) { node.className = className; }
				if (content) { node.textContent = content; }
				return node;
			}

			function show(offices, postcode) {
				list.textContent = '';

				offices.forEach(function (office) {
					var item = el('li', 'cb-office-finder__result');
					var head = el('div', 'cb-office-finder__result-head');

					head.appendChild(el('h3', '', office.office));
					head.appendChild(el('span', 'cb-office-finder__miles', office.miles + ' ' + (office.miles === 1 ? text.mile : text.miles)));
					item.appendChild(head);
					item.appendChild(el('p', 'cb-office-finder__address', office.address));

					var actions = el('div', 'cb-office-finder__actions');
					var contact = el('a', 'btn btn-primary', text.contact);
					contact.href = text.contactUrl;
					actions.appendChild(contact);

					if (office.phone) {
						var phone = el('a', 'cb-office-finder__phone', office.phone);
						phone.href = 'tel:' + office.phone.replace(/[^\d+]/g, '');
						actions.appendChild(phone);
					}

					item.appendChild(actions);
					list.appendChild(item);
				});

				status.textContent = text.found.replace('%s', postcode);
				root.classList.add('has-results');
			}

			function fail(message) {
				list.textContent = '';
				root.classList.remove('has-results');
				status.textContent = message || text.error;
			}

			form.addEventListener('submit', function (event) {
				event.preventDefault();

				var postcode = input.value.trim();
				if (!postcode) { return; }

				root.classList.add('is-loading');
				status.textContent = text.searching;

				fetch(text.endpoint + (text.endpoint.indexOf('?') === -1 ? '?' : '&') + 'postcode=' + encodeURIComponent(postcode), { headers: { Accept: 'application/json' } })
					.then(function (response) {
						return response.json().then(function (body) { return { ok: response.ok, body: body }; });
					})
					.then(function (result) {
						if (!result.ok || !result.body.offices || !result.body.offices.length) {
							fail(result.body && result.body.message);
							return;
						}

						show(result.body.offices, result.body.postcode);
					})
					.catch(function () {
						fail();
					})
					.then(function () {
						root.classList.remove('is-loading');
					});
			});
		});
	})();
	</script>
	<?php
}
add_action( 'wp_footer', 'cb_office_finder_script', 30 );
