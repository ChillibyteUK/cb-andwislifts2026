<?php
/**
 * Block template for CB Service Floors.
 *
 * The services as numbered floors: a column of tabs counting down like a lift
 * indicator, and a card for whichever is selected. As the section scrolls
 * through the window the selection steps from one floor to the next, as if
 * the page were a lift passing them; a click picks one directly.
 *
 * Plain tabs underneath: every panel is in the page, and without JavaScript
 * they are all shown, one after another.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

$section_id = $block['anchor'] ?? $block['id'] ?? wp_unique_id( 'cb-service-floors-' );
$extra      = $block['className'] ?? '';
$heading    = get_field( 'heading' );
$intro      = get_field( 'intro' );
$limit      = (int) get_field( 'limit' );
$uid        = sanitize_html_class( $section_id );

$services = get_posts(
	array(
		'post_type'      => 'service',
		'post_status'    => 'publish',
		'posts_per_page' => $limit > 0 ? $limit : 4,
		'orderby'        => array(
			'menu_order' => 'ASC',
			'title'      => 'ASC',
		),
		'no_found_rows'  => true,
	)
);

if ( ! $services ) {
	return;
}

$total = count( $services );
?>
<section class="cb-service-floors <?= esc_attr( $extra ); ?>" id="<?= esc_attr( $section_id ); ?>" data-cb-service-floors>
	<div class="container">
		<?php if ( $heading || $intro ) { ?>
			<div class="cb-service-floors__head">
				<?php if ( $heading ) { ?>
					<h2><?= cb_highlight( $heading ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h2>
				<?php } ?>
				<?php if ( $intro ) { ?>
					<p><?= esc_html( $intro ); ?></p>
				<?php } ?>
			</div>
		<?php } ?>

		<div class="cb-service-floors__inner">
			<div class="cb-service-floors__tabs" role="tablist" aria-label="<?php esc_attr_e( 'Services', 'cb-andwislifts2026' ); ?>">
				<?php
				foreach ( $services as $index => $service ) {
					// Counting down: the first service is the top floor.
					$floor = $total - $index;
					?>
					<button class="cb-service-floors__tab<?= 0 === $index ? ' is-active' : ''; ?>" type="button" role="tab" id="<?= esc_attr( $uid . '-tab-' . $index ); ?>" aria-controls="<?= esc_attr( $uid . '-panel-' . $index ); ?>" aria-selected="<?= 0 === $index ? 'true' : 'false'; ?>"<?= 0 === $index ? '' : ' tabindex="-1"'; ?>>
						<span class="cb-service-floors__number" aria-hidden="true"><?= esc_html( sprintf( '%02d', $floor ) ); ?></span>
						<span class="cb-service-floors__pill">
							<span class="cb-service-floors__label"><?= esc_html( get_the_title( $service ) ); ?></span>
							<span class="cb-service-floors__icon" aria-hidden="true"><svg width="14" height="14" viewBox="0 0 14 14" focusable="false"><path d="M7 1.5 13 12H1z" fill="currentColor"/></svg></span>
						</span>
					</button>
					<?php
				}
				?>
			</div>

			<div class="cb-service-floors__panels">
				<?php
				foreach ( $services as $index => $service ) {
					$summary = function_exists( 'get_field' ) ? (string) get_field( 'card_summary', $service->ID ) : '';
					$photo   = function_exists( 'get_field' ) ? get_field( 'card_photo', $service->ID ) : null;
					$photo   = ! empty( $photo['ID'] ) ? (int) $photo['ID'] : (int) get_post_thumbnail_id( $service->ID );
					?>
					<div class="cb-service-floors__panel<?= 0 === $index ? ' is-active' : ''; ?>" role="tabpanel" id="<?= esc_attr( $uid . '-panel-' . $index ); ?>" aria-labelledby="<?= esc_attr( $uid . '-tab-' . $index ); ?>">
						<div class="cb-service-floors__text">
							<h3 class="cb-service-floors__panel-title"><?= esc_html( get_the_title( $service ) ); ?></h3>
							<?php if ( $summary ) { ?>
								<p><?= esc_html( $summary ); ?></p>
							<?php } ?>
						</div>
						<div class="cb-service-floors__media<?= $photo ? '' : ' cb-service-floors__media--empty'; ?>">
							<?php
							if ( $photo ) {
								echo wp_get_attachment_image( $photo, 'large', false, array( 'loading' => 'lazy' ) );
							}
							?>
							<a class="btn btn-primary cb-service-floors__button" href="<?= esc_url( get_permalink( $service ) ); ?>">
								<?php esc_html_e( 'Learn more', 'cb-andwislifts2026' ); ?><span class="visually-hidden"> <?= esc_html( sprintf( /* translators: %s: service name. */ __( 'about %s', 'cb-andwislifts2026' ), get_the_title( $service ) ) ); ?></span>
							</a>
						</div>
					</div>
					<?php
				}
				?>
			</div>
		</div>
	</div>
	<svg class="cb-service-floors__shapes" viewBox="0 0 420 260" aria-hidden="true" focusable="false"><path d="M150 20l70 42-70 42z M262 92l72 34-60 52z M120 150l62 18-44 58z M330 190l80 30-62 40z M70 250l30-42 34 42z"/></svg>
</section>
<?php
add_action( 'wp_footer', 'cb_service_floors_script', 30 );

if ( ! function_exists( 'cb_service_floors_script' ) ) {
	/**
	 * Behaviour for CB Service Floors, printed once however many are on the page.
	 *
	 * @return void
	 */
	function cb_service_floors_script() {
		static $done = false;

		if ( $done ) {
			return;
		}

		$done = true;
		?>
		<script id="cb-service-floors-js">
		(function () {
			var calm = window.matchMedia('(prefers-reduced-motion: reduce)');

			Array.prototype.forEach.call(document.querySelectorAll('[data-cb-service-floors]'), function (root) {
				var tabs = Array.prototype.slice.call(root.querySelectorAll('[role="tab"]'));
				var panels = Array.prototype.slice.call(root.querySelectorAll('[role="tabpanel"]'));
				if (tabs.length < 2) { return; }

				var current = 0;
				// Once someone picks a floor themselves, scrolling stops choosing
				// for them until the section has left the window.
				var manual = false;

				root.classList.add('cb-service-floors--js');

				function select(index) {
					if (index === current) { return; }
					current = index;
					tabs.forEach(function (tab, i) {
						var on = i === index;
						tab.classList.toggle('is-active', on);
						tab.setAttribute('aria-selected', on ? 'true' : 'false');
						if (on) { tab.removeAttribute('tabindex'); } else { tab.setAttribute('tabindex', '-1'); }
					});
					panels.forEach(function (panel, i) { panel.classList.toggle('is-active', i === index); });
				}

				tabs.forEach(function (tab, index) {
					tab.addEventListener('click', function () { manual = true; select(index); });

					tab.addEventListener('keydown', function (event) {
						var next = null;
						if (event.key === 'ArrowDown' || event.key === 'ArrowRight') { next = (index + 1) % tabs.length; }
						if (event.key === 'ArrowUp' || event.key === 'ArrowLeft') { next = (index - 1 + tabs.length) % tabs.length; }
						if (event.key === 'Home') { next = 0; }
						if (event.key === 'End') { next = tabs.length - 1; }
						if (next === null) { return; }
						event.preventDefault();
						manual = true;
						select(next);
						tabs[next].focus();
					});
				});

				var ticking = false;

				// Divide the section's journey through the middle of the window
				// into one band per floor.
				function update() {
					ticking = false;

					var box = root.getBoundingClientRect();
					var view = window.innerHeight;

					if (box.bottom < 0 || box.top > view) { manual = false; return; }
					if (manual || calm.matches) { return; }

					var start = view * 0.75;
					var end = view * 0.25 - box.height;
					var progress = (start - box.top) / (start - end);
					var index = Math.floor(Math.max(0, Math.min(0.999, progress)) * tabs.length);

					select(index);
				}

				function onScroll() {
					if (!ticking) { ticking = true; window.requestAnimationFrame(update); }
				}

				window.addEventListener('scroll', onScroll, { passive: true });
				window.addEventListener('resize', onScroll);
				onScroll();
			});
		})();
		</script>
		<?php
	}
}
