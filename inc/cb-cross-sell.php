<?php
/**
 * Cross-sell: the two-step "which other andwis Group services?" card.
 *
 * Step one is a checklist of the group's divisions. Step two is a summary
 * tailored to what was ticked, with a route to speak to someone. It all happens
 * in place: nothing is submitted and the page does not reload until the visitor
 * follows the final button.
 *
 * It is a block (CB Cross Sell) for pages, and cb_cross_sell_render() can be
 * called from a template, which is how news articles get it.
 *
 * What is the same wherever it appears - the list of divisions, the summary
 * copy, where the final button goes - lives on Site-Wide Settings > Cross-sell.
 * What changes per placement - the heading and copy beside the card, the card's
 * question, the colours - is set on the block.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

/**
 * Query string parameter the final button carries the ticked services in.
 *
 * A Gravity Forms field with "Allow field to be populated dynamically" and this
 * parameter name picks the value up, so whoever answers the enquiry can see
 * what the visitor was interested in.
 */
const CB_CROSS_SELL_PARAM = 'interested_services';

/**
 * Split a textarea setting into trimmed, non-empty lines.
 *
 * @param mixed $value Raw field value.
 * @return string[]
 */
function cb_cross_sell_lines( $value ) {
	return array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) $value ) ), 'strlen' ) );
}

/**
 * The settings shared by every cross-sell on the site.
 *
 * Every value falls back to the phase two design's wording, so the card is
 * complete before anyone has opened the settings page.
 *
 * @return array<string, mixed>
 */
function cb_cross_sell_settings() {
	$get = static function ( $name ) {
		return function_exists( 'get_field' ) ? get_field( $name, 'option' ) : null;
	};

	$text = static function ( $name, $fallback ) use ( $get ) {
		$value = trim( (string) $get( $name ) );

		return '' !== $value ? $value : $fallback;
	};

	$divisions = cb_cross_sell_lines( $get( 'cross_sell_divisions' ) );
	$benefits  = cb_cross_sell_lines( $get( 'cross_sell_benefits' ) );
	$cta       = $get( 'cross_sell_cta' );
	$more      = $get( 'cross_sell_more' );

	if ( ! is_array( $cta ) || empty( $cta['url'] ) ) {
		$contact = get_page_by_path( 'contact' );
		$cta     = array(
			'url'    => $contact && 'publish' === $contact->post_status ? get_permalink( $contact ) : home_url( '/' ),
			'title'  => '',
			'target' => '',
		);
	}

	return array(
		'current'         => $text( 'cross_sell_current', 'andwis Lifts' ),
		'divisions'       => $divisions ? $divisions : array( 'Fire & Security', 'Environmental Services', 'Maintenance & Response', 'M&E' ),
		'summary_heading' => $text( 'cross_sell_summary_heading', __( 'One contract. One account manager.', 'cb-andwislifts2026' ) ),
		// {services} becomes the ticked list: "A", "A and B" or "A, B and C".
		'summary_text'    => $text( 'cross_sell_summary_text', __( 'Bringing {services} together with andwis Lifts under one contract could mean real efficiencies and savings for you in cost and time.', 'cb-andwislifts2026' ) ),
		'benefits'        => $benefits ? $benefits : array(
			__( 'One contract and one invoice', 'cb-andwislifts2026' ),
			__( 'One account manager across every service', 'cb-andwislifts2026' ),
			__( 'Coordinated site visits and reporting', 'cb-andwislifts2026' ),
		),
		// Optional. The design marks the quote and the stat as placeholders, so
		// they only appear once someone has written them.
		'quote'           => trim( (string) $get( 'cross_sell_quote' ) ),
		'quote_by'        => trim( (string) $get( 'cross_sell_quote_by' ) ),
		'stat'            => trim( (string) $get( 'cross_sell_stat' ) ),
		'cta'             => array(
			'url'    => (string) $cta['url'],
			'title'  => ! empty( $cta['title'] ) ? (string) $cta['title'] : __( 'Speak to one of our service agents', 'cb-andwislifts2026' ),
			'target' => ! empty( $cta['target'] ) ? (string) $cta['target'] : '',
		),
		// Optional: its destination was still to be decided in the design.
		'more'            => is_array( $more ) && ! empty( $more['url'] ) ? array(
			'url'    => (string) $more['url'],
			'title'  => ! empty( $more['title'] ) ? (string) $more['title'] : __( 'Find out more about how one contract could benefit you', 'cb-andwislifts2026' ),
			'target' => ! empty( $more['target'] ) ? (string) $more['target'] : '',
		) : null,
	);
}

/**
 * Output a cross-sell section.
 *
 * @param array $args {
 *     Optional. What this placement says; anything omitted uses the default.
 *
 *     @type string $id           Section id.
 *     @type string $class        Extra class names.
 *     @type string $style        dark | light.
 *     @type string $heading      Heading beside the card.
 *     @type string $body         Paragraph under the heading.
 *     @type string $note         Small line above the group link, e.g. "An andwis Group company."
 *     @type array  $group_link   ACF link array for the lime button.
 *     @type string $card_heading The card's question.
 *     @type string $button_label The card's button.
 * }
 * @return void
 */
function cb_cross_sell_render( $args = array() ) {
	$args = wp_parse_args(
		array_filter(
			(array) $args,
			static function ( $value ) {
				return ! ( null === $value || '' === $value || array() === $value );
			}
		),
		array(
			'id'           => wp_unique_id( 'cb-cross-sell-' ),
			'class'        => '',
			'style'        => 'dark',
			'heading'      => __( 'Looking after more than the lifts?', 'cb-andwislifts2026' ),
			'body'         => __( 'We also cover fire and security, M&E, maintenance and response, and environmental services. Tick what\'s relevant and we\'ll show how one provider could simplify things.', 'cb-andwislifts2026' ),
			'note'         => '',
			'group_link'   => array(
				'url'    => 'https://www.andwis.com/',
				'title'  => __( 'Learn more about andwis Group', 'cb-andwislifts2026' ),
				'target' => '_blank',
			),
			'card_heading' => __( 'Which other services do you need?', 'cb-andwislifts2026' ),
			'button_label' => __( 'Show me the benefits', 'cb-andwislifts2026' ),
		)
	);

	$settings = cb_cross_sell_settings();
	$style    = in_array( $args['style'], array( 'dark', 'light' ), true ) ? $args['style'] : 'dark';
	$uid      = sanitize_html_class( $args['id'] );
	$link     = is_array( $args['group_link'] ) ? $args['group_link'] : array();
	$classes  = trim( 'cb-cross-sell cb-cross-sell--' . $style . ' ' . $args['class'] );
	$tick     = '<svg class="cb-cross-sell__tick" width="14" height="11" viewBox="0 0 14 11" aria-hidden="true" focusable="false"><path d="M1.5 5.5 5.25 9.25 12.5 1.75" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"/></svg>';

	// The script needs these to write the summary.
	$data = array(
		'template' => $settings['summary_text'],
		/* translators: shown when exactly one other service is ticked. */
		'one'      => __( 'You\'ve identified 1 area of interest', 'cb-andwislifts2026' ),
		/* translators: %d: how many other services are ticked, always 2 or more. */
		'many'     => __( 'You\'ve identified %d areas of interest', 'cb-andwislifts2026' ),
		/* translators: joins the last two items of a list, as in "A, B and C". */
		'and'      => __( 'and', 'cb-andwislifts2026' ),
		'url'      => $settings['cta']['url'],
		'param'    => CB_CROSS_SELL_PARAM,
	);

	$GLOBALS['cb_cross_sell_rendered'] = true;
	?>
	<section class="<?= esc_attr( $classes ); ?>" id="<?= esc_attr( $args['id'] ); ?>" data-cb-cross-sell="<?= esc_attr( wp_json_encode( $data ) ); ?>">
		<div class="container">
			<div class="cb-cross-sell__inner">

				<div class="cb-cross-sell__copy">
					<h2><?= esc_html( $args['heading'] ); ?></h2>
					<?php if ( $args['body'] ) { ?>
						<p class="cb-cross-sell__body"><?= esc_html( $args['body'] ); ?></p>
					<?php } ?>
					<?php if ( $args['note'] ) { ?>
						<p class="cb-cross-sell__note"><?= esc_html( $args['note'] ); ?></p>
					<?php } ?>
					<?php
					if ( ! empty( $link['url'] ) ) {
						$link_target = ! empty( $link['target'] ) ? sprintf( ' target="%s" rel="noopener"', esc_attr( $link['target'] ) ) : '';
						$link_label  = ! empty( $link['title'] ) ? $link['title'] : __( 'Learn more about andwis Group', 'cb-andwislifts2026' );
						?>
						<a class="cb-cross-sell__group-link" href="<?= esc_url( $link['url'] ); ?>"<?= $link_target; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?= esc_html( $link_label ); ?></a>
						<?php
					}
					?>
				</div>

				<div class="cb-cross-sell__card">

					<?php
					// Step one. A real form pointed at the final destination, so
					// without JavaScript the button still takes the ticks there.
					?>
					<form class="cb-cross-sell__step" data-cb-cross-sell-step="choose" action="<?= esc_url( $settings['cta']['url'] ); ?>" method="get">
						<fieldset class="cb-cross-sell__fieldset">
							<legend class="cb-cross-sell__card-heading"><?= esc_html( $args['card_heading'] ); ?></legend>
							<p class="cb-cross-sell__hint"><?php esc_html_e( 'Tick all that apply.', 'cb-andwislifts2026' ); ?></p>

							<ul class="cb-cross-sell__options">
								<li>
									<?php // The division the visitor is already on: ticked, fixed, and not counted. ?>
									<label class="cb-cross-sell__option cb-cross-sell__option--current">
										<input type="checkbox" checked disabled>
										<span class="cb-cross-sell__box"><?= $tick; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
										<span class="cb-cross-sell__label"><?= esc_html( $settings['current'] ); ?></span>
										<span class="cb-cross-sell__here"><?php esc_html_e( 'You\'re here', 'cb-andwislifts2026' ); ?></span>
									</label>
								</li>
								<?php foreach ( $settings['divisions'] as $division ) { ?>
									<li>
										<label class="cb-cross-sell__option">
											<input type="checkbox" name="<?= esc_attr( CB_CROSS_SELL_PARAM ); ?>[]" value="<?= esc_attr( $division ); ?>">
											<span class="cb-cross-sell__box"><?= $tick; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
											<span class="cb-cross-sell__label"><?= esc_html( $division ); ?></span>
										</label>
									</li>
								<?php } ?>
							</ul>
						</fieldset>

						<div class="cb-cross-sell__actions">
							<button class="cb-cross-sell__button" type="submit" aria-describedby="<?= esc_attr( $uid ); ?>-helper"><?= esc_html( $args['button_label'] ); ?></button>
							<span class="cb-cross-sell__helper" id="<?= esc_attr( $uid ); ?>-helper"><?php esc_html_e( 'Tick at least one to continue', 'cb-andwislifts2026' ); ?></span>
						</div>
					</form>

					<?php // Step two. Filled in and revealed by the script. ?>
					<div class="cb-cross-sell__step" data-cb-cross-sell-step="summary" tabindex="-1" hidden>
						<button class="cb-cross-sell__back" type="button">
							<svg width="14" height="10" viewBox="0 0 14 10" aria-hidden="true" focusable="false"><path d="M5 1 1 5l4 4M1 5h12" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
							<?php esc_html_e( 'Change my answers', 'cb-andwislifts2026' ); ?>
						</button>

						<h3 class="cb-cross-sell__card-heading" data-cb-cross-sell-count></h3>
						<ul class="cb-cross-sell__pills" data-cb-cross-sell-pills></ul>

						<p class="cb-cross-sell__summary-heading"><?= esc_html( $settings['summary_heading'] ); ?></p>
						<p class="cb-cross-sell__summary-text" data-cb-cross-sell-text></p>

						<ul class="cb-cross-sell__benefits">
							<?php foreach ( $settings['benefits'] as $benefit ) { ?>
								<li><?= esc_html( $benefit ); ?></li>
							<?php } ?>
						</ul>

						<?php if ( $settings['quote'] ) { ?>
							<figure class="cb-cross-sell__quote">
								<blockquote><?= esc_html( $settings['quote'] ); ?></blockquote>
								<?php if ( $settings['quote_by'] ) { ?>
									<figcaption><?= esc_html( $settings['quote_by'] ); ?></figcaption>
								<?php } ?>
							</figure>
						<?php } ?>

						<?php if ( $settings['stat'] ) { ?>
							<p class="cb-cross-sell__stat"><?= esc_html( $settings['stat'] ); ?></p>
						<?php } ?>

						<div class="cb-cross-sell__summary-actions">
							<a class="cb-cross-sell__button" href="<?= esc_url( $settings['cta']['url'] ); ?>" data-cb-cross-sell-cta<?= $settings['cta']['target'] ? sprintf( ' target="%s" rel="noopener"', esc_attr( $settings['cta']['target'] ) ) : ''; ?>><?= esc_html( $settings['cta']['title'] ); ?></a>
							<?php if ( $settings['more'] ) { ?>
								<a class="cb-cross-sell__more" href="<?= esc_url( $settings['more']['url'] ); ?>"<?= $settings['more']['target'] ? sprintf( ' target="%s" rel="noopener"', esc_attr( $settings['more']['target'] ) ) : ''; ?>><?= esc_html( $settings['more']['title'] ); ?></a>
							<?php } ?>
						</div>
					</div>

				</div>
			</div>
		</div>
	</section>
	<?php
}

/**
 * Behaviour for every cross-sell on the page. Printed once, and only on pages
 * that have one.
 *
 * @return void
 */
function cb_cross_sell_script() {
	if ( empty( $GLOBALS['cb_cross_sell_rendered'] ) ) {
		return;
	}
	?>
	<script id="cb-cross-sell-js">
	(function () {
		function listOf(items, and) {
			if (items.length < 2) { return items.join(''); }
			return items.slice(0, -1).join(', ') + ' ' + and + ' ' + items[items.length - 1];
		}

		function track(name, services) {
			window.dataLayer = window.dataLayer || [];
			window.dataLayer.push({ event: name, cross_sell_services: services.join(', '), cross_sell_count: services.length });
		}

		Array.prototype.forEach.call(document.querySelectorAll('[data-cb-cross-sell]'), function (root) {
			var data;
			try { data = JSON.parse(root.getAttribute('data-cb-cross-sell')); } catch (e) { return; }

			var choose = root.querySelector('[data-cb-cross-sell-step="choose"]');
			var summary = root.querySelector('[data-cb-cross-sell-step="summary"]');
			if (!choose || !summary) { return; }

			var boxes = Array.prototype.slice.call(choose.querySelectorAll('input[type="checkbox"]:not(:disabled)'));
			var submit = choose.querySelector('.cb-cross-sell__button');
			var helper = choose.querySelector('.cb-cross-sell__helper');
			var count = summary.querySelector('[data-cb-cross-sell-count]');
			var pills = summary.querySelector('[data-cb-cross-sell-pills]');
			var text = summary.querySelector('[data-cb-cross-sell-text]');
			var cta = summary.querySelector('[data-cb-cross-sell-cta]');

			function ticked() {
				return boxes.filter(function (box) { return box.checked; }).map(function (box) { return box.value; });
			}

			// The button only works once another service is ticked. The hint
			// beside it explains why, and goes once it no longer applies.
			function refresh() {
				var ready = ticked().length > 0;
				submit.disabled = !ready;
				if (helper) { helper.hidden = ready; }
			}

			function show(step) {
				var forward = step === summary;
				choose.hidden = forward;
				summary.hidden = !forward;

				// Keep the top of the card in view, clear of the fixed header.
				var card = root.querySelector('.cb-cross-sell__card');
				var header = document.getElementById('wrapper-navbar');
				var limit = (header ? header.offsetHeight : 0) + 16;
				var top = card.getBoundingClientRect().top;

				if (top < limit) {
					var target = window.pageYOffset + top - limit;
					if (window.lenis && typeof window.lenis.scrollTo === 'function') {
						window.lenis.scrollTo(target, { duration: 0.6 });
					} else {
						window.scrollTo({ top: target, behavior: 'smooth' });
					}
				}

				// Move focus with the content, so the change is announced and
				// the next Tab starts inside the new step.
				(forward ? summary : choose.querySelector('input:not(:disabled)') || choose).focus({ preventScroll: true });
			}

			boxes.forEach(function (box) { box.addEventListener('change', refresh); });
			refresh();

			choose.addEventListener('submit', function (event) {
				event.preventDefault();

				var services = ticked();
				if (!services.length) { return; }

				count.textContent = services.length === 1 ? data.one : data.many.replace('%d', services.length);
				text.textContent = data.template.replace('{services}', listOf(services, data.and));

				pills.textContent = '';
				services.forEach(function (service) {
					var pill = document.createElement('li');
					pill.textContent = service;
					pills.appendChild(pill);
				});

				// Carry the ticks to wherever the button goes.
				if (cta) {
					try {
						var url = new URL(data.url, window.location.href);
						url.searchParams.set(data.param, services.join(', '));
						cta.href = url.toString();
					} catch (e) {
						cta.href = data.url;
					}
				}

				track('cross_sell_summary', services);
				show(summary);
			});

			summary.querySelector('.cb-cross-sell__back').addEventListener('click', function () {
				show(choose);
			});

			if (cta) {
				cta.addEventListener('click', function () { track('cross_sell_contact', ticked()); });
			}
		});
	})();
	</script>
	<?php
}
add_action( 'wp_footer', 'cb_cross_sell_script', 30 );
