<?php
/**
 * Primary navigation: the header menu and its dropdown panels.
 *
 * The menu is still managed in Appearance > Menus (the "Primary Nav" location),
 * two levels deep. What a top-level item's dropdown looks like is chosen with a
 * CSS class on that menu item:
 *
 *   nav-split  Green link list plus a "Case study examples" column (Services).
 *   nav-bar    Full-width green bar of links (Sectors and Coverage).
 *   nav-dark   Dark green panel (About). The default when no class is set.
 *   nav-mint   Mint panel (Contact).
 *   button     Not a dropdown: renders the item as the mint CTA pill.
 *
 * A panel's heading is the menu item's Title Attribute when one is set, and its
 * navigation label otherwise, so "Services" can open a panel headed "By service".
 *
 * A top-level item with children renders as a button that opens its panel, not
 * as a link - the panel heading carries the link to the parent page instead.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

/**
 * How many case studies the Services panel shows per service.
 */
const CB_NAV_CASE_STUDY_LIMIT = 2;

/**
 * Transient holding the case study IDs shown in the Services panel.
 */
const CB_NAV_CASE_STUDY_CACHE = 'cb_nav_case_studies_v1';

/**
 * The primary menu as a tree of top-level items, each with a `cb_children` list.
 *
 * @return array<int, object>
 */
function cb_nav_get_tree() {
	$locations = get_nav_menu_locations();
	$menu_id   = isset( $locations['primary_nav'] ) ? (int) $locations['primary_nav'] : 0;

	if ( ! $menu_id ) {
		return array();
	}

	$items = wp_get_nav_menu_items( $menu_id, array( 'update_post_term_cache' => false ) );

	if ( empty( $items ) ) {
		return array();
	}

	// Adds current / current_item_ancestor, the same pass wp_nav_menu() makes.
	_wp_menu_item_classes_by_context( $items );

	$tree     = array();
	$children = array();

	foreach ( $items as $item ) {
		$parent = (int) $item->menu_item_parent;

		if ( $parent ) {
			$children[ $parent ][] = $item;
		} else {
			$tree[ (int) $item->ID ] = $item;
		}
	}

	foreach ( $tree as $id => $item ) {
		$item->cb_children = isset( $children[ $id ] ) ? $children[ $id ] : array();
	}

	return array_values( $tree );
}

/**
 * Which panel style a top-level menu item asks for.
 *
 * @param object $item Menu item.
 * @return string split | bar | dark | mint.
 */
function cb_nav_panel_style( $item ) {
	$styles = array( 'split', 'bar', 'dark', 'mint' );

	foreach ( (array) $item->classes as $class ) {
		if ( 0 === strpos( (string) $class, 'nav-' ) && in_array( substr( $class, 4 ), $styles, true ) ) {
			return substr( $class, 4 );
		}
	}

	return 'dark';
}

/**
 * Whether a menu item carries a given CSS class.
 *
 * @param object $item       Menu item.
 * @param string $class_name Class to look for.
 * @return boolean
 */
function cb_nav_item_has_class( $item, $class_name ) {
	return in_array( $class_name, (array) $item->classes, true );
}

/**
 * Whether a menu item is the current page or an ancestor of it.
 *
 * @param object $item Menu item.
 * @return boolean
 */
function cb_nav_item_is_active( $item ) {
	return ! empty( $item->current ) || ! empty( $item->current_item_ancestor ) || ! empty( $item->current_item_parent );
}

/**
 * Extra attributes for a menu item's link: target, rel and aria-current.
 *
 * @param object $item Menu item.
 * @return string Leading-space-prefixed attribute string, already escaped.
 */
function cb_nav_link_attrs( $item ) {
	$attrs = '';

	if ( ! empty( $item->target ) ) {
		$rel    = '_blank' === $item->target ? 'noopener' : '';
		$rel    = trim( $rel . ' ' . (string) $item->xfn );
		$attrs .= ' target="' . esc_attr( $item->target ) . '"';
		$attrs .= $rel ? ' rel="' . esc_attr( $rel ) . '"' : '';
	} elseif ( ! empty( $item->xfn ) ) {
		$attrs .= ' rel="' . esc_attr( $item->xfn ) . '"';
	}

	if ( ! empty( $item->current ) ) {
		$attrs .= ' aria-current="page"';
	}

	return $attrs;
}

/**
 * Inline SVG icons used by the navigation.
 *
 * @param string $name chevron-down | chevron-right | document | menu.
 * @return string
 */
function cb_nav_icon( $name ) {
	switch ( $name ) {
		case 'chevron-down':
			return '<svg class="cb-nav__chevron" width="18" height="18" viewBox="0 0 18 18" aria-hidden="true" focusable="false"><path d="M3 6.25 9 12.25l6-6" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';

		case 'chevron-right':
			return '<svg class="cb-nav__arrow" width="10" height="14" viewBox="0 0 10 14" aria-hidden="true" focusable="false"><path d="M2 2l6 5-6 5" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"/></svg>';

		case 'document':
			return '<svg class="cb-nav__case-icon" width="16" height="20" viewBox="0 0 16 20" aria-hidden="true" focusable="false"><path d="M1.75 1.75h8.5l4 4v12.5H1.75z M10.25 1.75v4h4" fill="none" stroke="currentColor" stroke-width="1.25" stroke-linejoin="round"/><path d="M4.5 9.5h7M4.5 12.25h7M4.5 15h7" fill="none" stroke="currentColor" stroke-width="1.25" stroke-linecap="round"/></svg>';

		case 'menu':
			return '<svg width="28" height="20" viewBox="0 0 28 20" aria-hidden="true" focusable="false"><path d="M1.5 2h25M1.5 10h25M1.5 18h25" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>';
	}

	return '';
}

/**
 * URL of the case studies hub.
 *
 * @return string
 */
function cb_nav_case_studies_url() {
	$page = get_page_by_path( 'case-studies' );
	$url  = $page && 'publish' === $page->post_status ? get_permalink( $page ) : get_post_type_archive_link( 'case_study' );

	/**
	 * Filters the "See all case studies" destination used by the navigation.
	 *
	 * @param string $url Case studies hub URL.
	 */
	return (string) apply_filters( 'cb_nav_case_studies_url', (string) $url );
}

/**
 * Case study IDs for the Services panel, keyed by service ID plus a `latest` key.
 *
 * Each service gets its most recent related case studies. `latest` is the
 * fallback shown for a service with none, and for links that are not services.
 *
 * Cached, because it is one meta query per service on every page of the site.
 *
 * @return array<string|int, int[]>
 */
function cb_nav_case_study_map() {
	$map = get_transient( CB_NAV_CASE_STUDY_CACHE );

	if ( is_array( $map ) ) {
		return $map;
	}

	$base = array(
		'post_type'              => 'case_study',
		'post_status'            => 'publish',
		'posts_per_page'         => CB_NAV_CASE_STUDY_LIMIT,
		// ID breaks ties: imported case studies share a publish date.
		'orderby'                => array(
			'date' => 'DESC',
			'ID'   => 'DESC',
		),
		'fields'                 => 'ids',
		'no_found_rows'          => true,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
		'suppress_filters'       => false,
	);

	$map = array(
		'latest' => array_map( 'intval', get_posts( $base ) ),
	);

	$services = get_posts(
		array(
			'post_type'      => 'service',
			'post_status'    => 'publish',
			'posts_per_page' => 50,
			'fields'         => 'ids',
			'no_found_rows'  => true,
		)
	);

	foreach ( $services as $service_id ) {
		$args = $base;

		// ACF stores a relationship as a serialised array of IDs, hence the
		// quoted LIKE - the same lookup the sector pages use.
		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		$args['meta_query'] = array(
			array(
				'key'     => 'related_services',
				'value'   => '"' . (int) $service_id . '"',
				'compare' => 'LIKE',
			),
		);

		$found = array_map( 'intval', get_posts( $args ) );

		if ( $found ) {
			$map[ (int) $service_id ] = $found;
		}
	}

	set_transient( CB_NAV_CASE_STUDY_CACHE, $map, 12 * HOUR_IN_SECONDS );

	return $map;
}

/**
 * Drop the cached Services panel case studies.
 *
 * @return void
 */
function cb_nav_flush_case_study_map() {
	delete_transient( CB_NAV_CASE_STUDY_CACHE );
}
add_action( 'save_post_case_study', 'cb_nav_flush_case_study_map' );
add_action( 'save_post_service', 'cb_nav_flush_case_study_map' );
add_action( 'deleted_post', 'cb_nav_flush_case_study_map' );
add_action( 'trashed_post', 'cb_nav_flush_case_study_map' );
add_action( 'untrashed_post', 'cb_nav_flush_case_study_map' );
// ACF writes the relationship after save_post has fired.
add_action( 'acf/save_post', 'cb_nav_flush_case_study_map', 20 );

/**
 * Output a panel heading, linked when the parent item has somewhere to go.
 *
 * @param string $label Heading text.
 * @param string $url   Destination, or an empty string / "#" for none.
 * @return void
 */
function cb_nav_render_heading( $label, $url ) {
	$has_link = $url && '#' !== $url;
	?>
	<p class="cb-nav__heading">
		<?php if ( $has_link ) { ?>
			<a href="<?= esc_url( $url ); ?>"><?= esc_html( $label ); ?><?= cb_nav_icon( 'chevron-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
		<?php } else { ?>
			<?= esc_html( $label ); ?>
		<?php } ?>
	</p>
	<?php
}

/**
 * Output the "Case study examples" column of a split panel.
 *
 * One group of case studies per distinct set, so services sharing the fallback
 * share a group. The script in cb_nav_footer_script() swaps the visible group as
 * the visitor moves over the service links.
 *
 * @param object[] $children The panel's menu items.
 * @return void
 */
function cb_nav_render_featured( $children ) {
	$map = cb_nav_case_study_map();

	if ( empty( $map['latest'] ) ) {
		return;
	}

	$groups = array();

	foreach ( $children as $child ) {
		$service_id = 'service' === $child->object ? (int) $child->object_id : 0;

		if ( $service_id && ! empty( $map[ $service_id ] ) ) {
			$groups[ 'service-' . $service_id ] = array(
				'label' => $child->title,
				'ids'   => $map[ $service_id ],
			);
		}
	}

	// The fallback group goes last, and first only when nothing else exists.
	$groups['latest'] = array(
		'label' => __( 'Recent projects', 'cb-andwislifts2026' ),
		'ids'   => $map['latest'],
	);

	$hub     = cb_nav_case_studies_url();
	$default = (string) array_key_first( $groups );
	$first   = reset( $children );

	// Open on the first link's own group, which is "latest" if it has none.
	if ( $first && 'service' === $first->object && ! isset( $groups[ 'service-' . (int) $first->object_id ] ) ) {
		$default = 'latest';
	}
	?>
	<div class="cb-nav__col cb-nav__col--featured">
		<?php cb_nav_render_heading( __( 'Case study examples', 'cb-andwislifts2026' ), $hub ); ?>

		<?php foreach ( $groups as $key => $group ) { ?>
			<div class="cb-nav__group<?= $key === $default ? ' is-active' : ''; ?>" data-cb-nav-group-panel="<?= esc_attr( $key ); ?>">
				<p class="cb-nav__group-label"><?= esc_html( $group['label'] ); ?></p>
				<ul class="cb-nav__cases">
					<?php
					foreach ( $group['ids'] as $case_id ) {
						// One supporting line under the title: the client when there
						// is one, otherwise the card summary, cut to a line by CSS.
						$client = function_exists( 'get_field' ) ? trim( (string) get_field( 'client_name', $case_id ) ) : '';

						if ( '' === $client && function_exists( 'get_field' ) ) {
							$client = trim( wp_strip_all_tags( (string) get_field( 'card_summary', $case_id ) ) );
						}
						?>
						<li>
							<a class="cb-nav__case" href="<?= esc_url( get_permalink( $case_id ) ); ?>">
								<?= cb_nav_icon( 'document' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								<span class="cb-nav__case-text">
									<span class="cb-nav__case-title"><?= esc_html( get_the_title( $case_id ) ); ?></span>
									<?php if ( '' !== $client ) { ?>
										<span class="cb-nav__case-meta"><?= esc_html( $client ); ?></span>
									<?php } ?>
								</span>
							</a>
						</li>
						<?php
					}
					?>
				</ul>
			</div>
		<?php } ?>

		<?php if ( $hub ) { ?>
			<a class="cb-nav__more" href="<?= esc_url( $hub ); ?>"><?= esc_html__( 'See all case studies', 'cb-andwislifts2026' ); ?><?= cb_nav_icon( 'chevron-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
		<?php } ?>
	</div>
	<?php
}

/**
 * Output one top-level item that opens a panel.
 *
 * @param object $item Top-level menu item with `cb_children`.
 * @return void
 */
function cb_nav_render_panel_item( $item ) {
	$style    = cb_nav_panel_style( $item );
	$panel_id = 'cb-nav-panel-' . (int) $item->ID;
	$heading  = '' !== trim( (string) $item->attr_title ) ? $item->attr_title : $item->title;
	$map      = 'split' === $style ? cb_nav_case_study_map() : array();
	$classes  = 'cb-nav__item cb-nav__item--panel cb-nav__item--' . $style . ( cb_nav_item_is_active( $item ) ? ' is-current' : '' );
	?>
	<li class="<?= esc_attr( $classes ); ?>" data-cb-nav-item>
		<button class="cb-nav__link cb-nav__toggle" type="button" aria-expanded="false" aria-controls="<?= esc_attr( $panel_id ); ?>">
			<span class="cb-nav__label" data-text="<?= esc_attr( $item->title ); ?>"><?= esc_html( $item->title ); ?></span>
			<?= cb_nav_icon( 'chevron-down' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</button>

		<div class="cb-nav__panel cb-nav__panel--<?= esc_attr( $style ); ?>" id="<?= esc_attr( $panel_id ); ?>">
			<div class="cb-nav__panel-inner">
				<?php if ( 'bar' === $style ) { ?>
					<ul class="cb-nav__bar-list">
						<?php foreach ( $item->cb_children as $child ) { ?>
							<li><a href="<?= esc_url( $child->url ); ?>"<?= cb_nav_link_attrs( $child ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?= esc_html( $child->title ); ?></a></li>
						<?php } ?>
					</ul>
				<?php } else { ?>
					<div class="cb-nav__col cb-nav__col--list">
						<?php cb_nav_render_heading( $heading, $item->url ); ?>
						<ul class="cb-nav__list">
							<?php
							foreach ( $item->cb_children as $child ) {
								$group = '';

								if ( 'split' === $style ) {
									$service_id = 'service' === $child->object ? (int) $child->object_id : 0;
									$group      = $service_id && ! empty( $map[ $service_id ] ) ? 'service-' . $service_id : 'latest';
								}
								?>
								<li><a href="<?= esc_url( $child->url ); ?>"<?= $group ? ' data-cb-nav-group="' . esc_attr( $group ) . '"' : ''; ?><?= cb_nav_link_attrs( $child ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?= esc_html( $child->title ); ?></a></li>
								<?php
							}
							?>
						</ul>
					</div>
					<?php
					if ( 'split' === $style ) {
						cb_nav_render_featured( $item->cb_children );
					}
				}
				?>
			</div>
		</div>
	</li>
	<?php
}

/**
 * Output the primary navigation list.
 *
 * @param object[]|null $tree Optional menu tree in the shape cb_nav_get_tree()
 *                            returns. Defaults to the Primary Nav menu.
 * @return void
 */
function cb_render_primary_nav( $tree = null ) {
	$tree = null === $tree ? cb_nav_get_tree() : $tree;

	if ( empty( $tree ) ) {
		return;
	}
	?>
	<ul class="cb-nav navbar-nav" id="cb-nav">
		<?php
		foreach ( $tree as $item ) {
			if ( cb_nav_item_has_class( $item, 'button' ) ) {
				?>
				<li class="cb-nav__item cb-nav__item--cta">
					<a class="cb-nav__cta" href="<?= esc_url( $item->url ); ?>"<?= cb_nav_link_attrs( $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?= esc_html( $item->title ); ?></a>
				</li>
				<?php
				continue;
			}

			if ( ! empty( $item->cb_children ) ) {
				cb_nav_render_panel_item( $item );
				continue;
			}
			?>
			<li class="cb-nav__item<?= cb_nav_item_is_active( $item ) ? ' is-current' : ''; ?>">
				<a class="cb-nav__link" href="<?= esc_url( $item->url ); ?>"<?= cb_nav_link_attrs( $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
					<span class="cb-nav__label" data-text="<?= esc_attr( $item->title ); ?>"><?= esc_html( $item->title ); ?></span>
				</a>
			</li>
			<?php
		}
		?>
	</ul>
	<?php
}

/**
 * Behaviour for the navigation panels.
 *
 * Printed in the footer rather than bundled, in line with how the blocks ship
 * their scripts, so the nav does not depend on a JS build step.
 *
 * - Pointer devices that can hover open a panel on hover; a click keeps it open.
 * - Touch and keyboard toggle it with the button. Escape closes and returns focus.
 * - Below the desktop breakpoint the same buttons work as an accordion inside
 *   the Bootstrap collapse.
 *
 * @return void
 */
function cb_nav_footer_script() {
	?>
	<script id="cb-nav-js">
	(function () {
		var nav = document.getElementById('cb-nav');
		if (!nav) { return; }

		var header = document.getElementById('wrapper-navbar');
		var navbar = nav.closest('.navbar');
		var items = Array.prototype.slice.call(nav.querySelectorAll('[data-cb-nav-item]'));
		var desktop = window.matchMedia('(min-width: 1200px)');
		var canHover = window.matchMedia('(hover: hover) and (pointer: fine)');
		var closeTimer = null;

		function button(item) { return item.querySelector('.cb-nav__toggle'); }
		function panel(item) { return item.querySelector('.cb-nav__panel'); }

		// Keep a panel inside the page container, and line a bar's links up
		// under the item that opened it.
		function place(item) {
			var p = panel(item);
			p.style.left = '';
			p.style.removeProperty('--cb-nav-bar-offset');

			if (!desktop.matches || !navbar) { return; }

			var itemBox = item.getBoundingClientRect();

			if (item.classList.contains('cb-nav__item--bar')) {
				p.style.setProperty('--cb-nav-bar-offset', Math.max(itemBox.left - navbar.getBoundingClientRect().left, 0) + 'px');
				return;
			}

			var container = nav.closest('.container') || navbar;
			var box = container.getBoundingClientRect();
			var style = window.getComputedStyle(container);
			var right = box.right - parseFloat(style.paddingRight || 0);
			var left = box.left + parseFloat(style.paddingLeft || 0);
			var shift = Math.min(0, right - (itemBox.left + p.offsetWidth));

			// Never push it past the container's left edge either.
			shift = Math.max(shift, left - itemBox.left);
			p.style.left = shift + 'px';
		}

		function close(item) {
			item.classList.remove('is-open');
			button(item).setAttribute('aria-expanded', 'false');
			if (header && !nav.querySelector('.is-open')) { header.classList.remove('has-open-panel'); }
		}

		function closeAll(except) {
			items.forEach(function (item) { if (item !== except) { close(item); } });
		}

		function open(item) {
			window.clearTimeout(closeTimer);
			closeAll(item);
			place(item);
			item.classList.add('is-open');
			button(item).setAttribute('aria-expanded', 'true');
			if (header) { header.classList.add('has-open-panel'); }
		}

		items.forEach(function (item) {
			button(item).addEventListener('click', function (event) {
				var byPointer = event.detail !== 0;

				// A mouse click on a panel that hover already opened should not shut it.
				if (desktop.matches && canHover.matches && byPointer) {
					open(item);
					return;
				}

				if (item.classList.contains('is-open')) { close(item); } else { open(item); }
			});

			item.addEventListener('mouseenter', function () {
				if (desktop.matches && canHover.matches) { open(item); }
			});

			item.addEventListener('mouseleave', function () {
				if (!desktop.matches || !canHover.matches) { return; }
				window.clearTimeout(closeTimer);
				closeTimer = window.setTimeout(function () { close(item); }, 180);
			});
		});

		// Swap the case studies shown beside the service links.
		function showGroup(link) {
			var key = link.getAttribute('data-cb-nav-group');
			var host = link.closest('.cb-nav__panel');
			if (!key || !host) { return; }

			Array.prototype.forEach.call(host.querySelectorAll('[data-cb-nav-group-panel]'), function (group) {
				group.classList.toggle('is-active', group.getAttribute('data-cb-nav-group-panel') === key);
			});
		}

		['mouseover', 'focusin'].forEach(function (type) {
			nav.addEventListener(type, function (event) {
				var link = event.target.closest ? event.target.closest('[data-cb-nav-group]') : null;
				if (link) { showGroup(link); }
			});
		});

		document.addEventListener('keydown', function (event) {
			if (event.key !== 'Escape') { return; }
			var current = nav.querySelector('.cb-nav__item.is-open');
			if (!current) { return; }
			close(current);
			button(current).focus();
		});

		// Desktop only: on small screens the open section stays put while the
		// visitor scrolls or taps around the drawer.
		document.addEventListener('click', function (event) {
			if (desktop.matches && !nav.contains(event.target)) { closeAll(); }
		});

		document.addEventListener('focusin', function (event) {
			if (!desktop.matches) { return; }
			var current = nav.querySelector('.cb-nav__item.is-open');
			if (current && !current.contains(event.target)) { close(current); }
		});

		function reset() {
			closeAll();
			items.forEach(function (item) {
				panel(item).style.left = '';
				panel(item).style.removeProperty('--cb-nav-bar-offset');
			});
		}

		if (desktop.addEventListener) {
			desktop.addEventListener('change', reset);
		} else if (desktop.addListener) {
			desktop.addListener(reset);
		}

		window.addEventListener('resize', function () {
			var current = nav.querySelector('.cb-nav__item.is-open');
			if (current) { place(current); }
		});

		var drawer = document.getElementById('primary-navbar');
		if (drawer) {
			drawer.addEventListener('hidden.bs.collapse', function () { closeAll(); });
		}
	})();
	</script>
	<?php
}
add_action( 'wp_footer', 'cb_nav_footer_script', 30 );
