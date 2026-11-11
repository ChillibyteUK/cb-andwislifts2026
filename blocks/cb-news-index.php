<?php
/**
 * Block template for CB News Index.
 *
 * Articles as cards. With no limit set it is the news hub's full listing,
 * with a row of category chips, each with its count. With a limit it is a
 * teaser row of the latest. Filtering is client-side - every card is already
 * in the DOM - so the page still lists everything with no JavaScript.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

$section_id = $block['anchor'] ?? $block['id'] ?? wp_unique_id( 'cb-news-index-' );
$extra      = $block['className'] ?? '';
$heading    = get_field( 'heading' );
$intro      = get_field( 'intro' );
$limit      = (int) get_field( 'limit' );
$view_all   = get_field( 'view_all' );
$footnote   = trim( (string) get_field( 'footnote' ) );

$query = new WP_Query(
	array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => $limit > 0 ? $limit : -1,
		'orderby'        => 'date',
		'order'          => 'DESC',
		'no_found_rows'  => true,
	)
);

// The chips are the categories actually in use, so none is ever empty.
$chips = array();
$cards = array();

foreach ( $query->posts as $item ) {
	$slugs = array();

	foreach ( cb_news_categories( $item->ID ) as $category ) {
		if ( ! isset( $chips[ $category->slug ] ) ) {
			$chips[ $category->slug ] = array(
				'label' => $category->name,
				'count' => 0,
			);
		}

		++$chips[ $category->slug ]['count'];
		$slugs[] = $category->slug;
	}

	$cards[] = array(
		'post'  => $item,
		'slugs' => $slugs,
	);
}

uasort(
	$chips,
	static function ( $a, $b ) {
		return strcasecmp( $a['label'], $b['label'] );
	}
);

$filterable = ! $limit && count( $chips ) > 1;
?>
<section class="cb-news-index <?= esc_attr( $extra ); ?>" id="<?= esc_attr( $section_id ); ?>">
	<div class="container">
		<?php if ( $heading || $intro ) { ?>
		<div class="cb-section-head pb-5">
			<?php if ( $heading ) { ?>
			<h2><?= esc_html( $heading ); ?></h2>
			<?php } ?>
			<?php if ( $intro ) { ?>
			<p><?= esc_html( $intro ); ?></p>
			<?php } ?>
		</div>
		<?php } ?>
		<?php if ( $filterable ) { ?>
		<div class="cb-news-index__chips" role="group" aria-label="<?php esc_attr_e( 'Filter news by topic', 'cb-andwislifts2026' ); ?>">
			<button type="button" class="cb-news-index__chip is-active" data-filter="" aria-pressed="true"><?php esc_html_e( 'All', 'cb-andwislifts2026' ); ?></button>
			<?php foreach ( $chips as $slug => $chip ) { ?>
			<button type="button" class="cb-news-index__chip" data-filter="<?= esc_attr( $slug ); ?>" aria-pressed="false"><?= esc_html( $chip['label'] ); ?> (<?= (int) $chip['count']; ?>)</button>
			<?php } ?>
		</div>
		<?php } ?>
		<?php if ( $cards ) { ?>
		<div class="cb-case-study-grid cb-news-index__grid">
			<?php foreach ( $cards as $card ) { ?>
			<div class="cb-news-index__col" data-categories="<?= esc_attr( implode( ' ', $card['slugs'] ) ); ?>">
				<?php cb_news_card( $card['post'] ); ?>
			</div>
			<?php } ?>
		</div>
		<?php } else { ?>
		<p class="cb-news-index__empty"><?php esc_html_e( 'News is on its way. Check back soon.', 'cb-andwislifts2026' ); ?></p>
		<?php } ?>
		<?php if ( ! empty( $view_all['url'] ) ) { ?>
		<p class="cb-news-index__more">
			<a class="btn btn-ghost" href="<?= esc_url( $view_all['url'] ); ?>"><?= esc_html( $view_all['title'] ? $view_all['title'] : __( 'View all news', 'cb-andwislifts2026' ) ); ?></a>
		</p>
		<?php } ?>
		<?php if ( $footnote ) { ?>
		<p class="cb-news-index__footnote"><?= wp_kses_post( make_clickable( esc_html( $footnote ) ) ); ?></p>
		<?php } ?>
	</div>
</section>
<?php
// Emitted on wp_footer rather than inline, matching the other blocks in this
// theme - an inline <script> in a block's output breaks its editor preview.
if ( $filterable ) {
	add_action(
		'wp_footer',
		function () use ( $section_id ) {
			?>
<script>
document.addEventListener('DOMContentLoaded', function () {
	var block = document.getElementById(<?= wp_json_encode( $section_id ); ?>);
	if (!block) return;

	var each = function (list, fn) { Array.prototype.forEach.call(list, fn); };
	var chips = block.querySelectorAll('.cb-news-index__chip');
	var cols = block.querySelectorAll('.cb-news-index__col');
	var grid = block.querySelector('.cb-news-index__grid');
	var still = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	var timer;

	function swap(filter) {
		each(cols, function (col) {
			var terms = (col.getAttribute('data-categories') || '').split(' ');
			col.hidden = !!filter && terms.indexOf(filter) === -1;
		});
	}

	// The cards fade out, change while they cannot be seen, then fade back in.
	// The time here matches the fade out in the stylesheet.
	each(chips, function (chip) {
		chip.addEventListener('click', function () {
			var filter = chip.getAttribute('data-filter');

			each(chips, function (other) {
				other.classList.toggle('is-active', other === chip);
				other.setAttribute('aria-pressed', other === chip ? 'true' : 'false');
			});

			window.clearTimeout(timer);

			if (still) {
				swap(filter);
				return;
			}

			grid.classList.add('is-changing');
			timer = window.setTimeout(function () {
				swap(filter);
				grid.classList.remove('is-changing');
			}, 180);
		});
	});
});
</script>
			<?php
		},
		9999
	);
}
