<?php
/**
 * Block template for CB Case Study Showcase.
 *
 * The latest case studies as tall photo columns. One is open at a time,
 * showing its title, summary and tags; hovering or focusing another opens
 * that one instead. On small screens they become a row to swipe through,
 * all open.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

$section_id = $block['anchor'] ?? $block['id'] ?? wp_unique_id( 'cb-case-study-showcase-' );
$extra      = $block['className'] ?? '';
$heading    = get_field( 'heading' );
$view_all   = get_field( 'view_all' );
$limit      = (int) get_field( 'limit' );

$studies = get_posts(
	array(
		'post_type'      => 'case_study',
		'post_status'    => 'publish',
		'posts_per_page' => $limit > 0 ? $limit : 4,
		'orderby'        => array(
			'menu_order' => 'ASC',
			'date'       => 'DESC',
		),
		'meta_key'       => '_thumbnail_id', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- a column with no photo would be an empty box.
		'no_found_rows'  => true,
	)
);

if ( ! $studies ) {
	return;
}
?>
<section class="cb-showcase <?= esc_attr( $extra ); ?>" id="<?= esc_attr( $section_id ); ?>" data-cb-showcase>
	<div class="container">
		<?php if ( $heading ) { ?>
			<h2 class="cb-showcase__heading"><?= cb_highlight( $heading ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h2>
		<?php } ?>

		<ul class="cb-showcase__list">
			<?php
			foreach ( $studies as $index => $study ) {
				$get     = static function ( $name ) use ( $study ) {
					return function_exists( 'get_field' ) ? get_field( $name, $study->ID ) : null;
				};
				$summary = trim( wp_strip_all_tags( (string) $get( 'card_summary' ) ) );
				$client  = trim( (string) $get( 'client_name' ) );
				$tags    = array();

				foreach ( array( 'related_sectors', 'related_services' ) as $field ) {
					foreach ( (array) $get( $field ) as $related_id ) {
						$label = $related_id ? get_the_title( $related_id ) : '';

						if ( $label ) {
							$tags[] = $label;
						}
					}
				}
				?>
				<li class="cb-showcase__item<?= 0 === $index ? ' is-active' : ''; ?>">
					<a class="cb-showcase__link" href="<?= esc_url( get_permalink( $study ) ); ?>">
						<?= get_the_post_thumbnail( $study, 'large', array( 'loading' => 'lazy' ) ); ?>
						<span class="cb-showcase__content">
							<span class="cb-showcase__title"><?= esc_html( get_the_title( $study ) ); ?></span>
							<?php if ( $client ) { ?>
								<span class="cb-showcase__meta"><?= esc_html( $client ); ?></span>
							<?php } ?>
							<?php if ( $summary ) { ?>
								<span class="cb-showcase__text"><?= esc_html( wp_trim_words( $summary, 24, '...' ) ); ?></span>
							<?php } ?>
							<span class="cb-showcase__foot">
								<?php if ( $tags ) { ?>
									<span class="cb-showcase__tags">
										<?php foreach ( array_slice( array_unique( $tags ), 0, 3 ) as $tag ) { ?>
											<span><?= esc_html( $tag ); ?></span>
										<?php } ?>
									</span>
								<?php } ?>
								<span class="cb-showcase__more"><?php esc_html_e( 'Read more', 'cb-andwislifts2026' ); ?> <svg width="6" height="9" viewBox="0 0 6 9" aria-hidden="true" focusable="false"><path d="M1.25 1.25 4.5 4.5 1.25 7.75" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
							</span>
						</span>
					</a>
				</li>
				<?php
			}
			?>
		</ul>

		<?php if ( ! empty( $view_all['url'] ) ) { ?>
			<div class="cb-showcase__actions">
				<a class="btn btn-ghost" href="<?= esc_url( $view_all['url'] ); ?>"<?= cb_link_target( $view_all ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?= esc_html( $view_all['title'] ); ?></a>
			</div>
		<?php } ?>
	</div>
</section>
<?php
add_action( 'wp_footer', 'cb_showcase_script', 30 );

if ( ! function_exists( 'cb_showcase_script' ) ) {
	/**
	 * Open the hovered or focused column. Printed once.
	 *
	 * @return void
	 */
	function cb_showcase_script() {
		static $done = false;

		if ( $done ) {
			return;
		}

		$done = true;
		?>
		<script id="cb-showcase-js">
		(function () {
			Array.prototype.forEach.call(document.querySelectorAll('[data-cb-showcase]'), function (root) {
				var items = Array.prototype.slice.call(root.querySelectorAll('.cb-showcase__item'));

				items.forEach(function (item) {
					function open() {
						items.forEach(function (other) { other.classList.toggle('is-active', other === item); });
					}

					item.addEventListener('mouseenter', open);
					item.addEventListener('focusin', open);
				});
			});
		})();
		</script>
		<?php
	}
}
