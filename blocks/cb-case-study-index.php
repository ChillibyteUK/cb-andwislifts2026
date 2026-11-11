<?php
/**
 * Block template for CB Case Study Index.
 *
 * Lists published case studies as cards. With no limit set it is the hub's
 * full listing: a "Browse by" switch between sector and service, a row of
 * chips for whichever is chosen, each with its count, and a line saying how
 * many are showing. Filtering is client-side - every card is already in the
 * DOM - so it is instant and the page still lists everything with no
 * JavaScript.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

$section_id = $block['anchor'] ?? $block['id'] ?? wp_unique_id( 'cb-case-study-index-' );
$extra      = $block['className'] ?? '';
$heading    = get_field( 'heading' );
$empty_msg  = get_field( 'no_results_message' ) ? get_field( 'no_results_message' ) : 'Case studies are on their way. Get in touch and we will talk you through relevant work.';
$limit      = (int) get_field( 'limit' );
$view_all   = get_field( 'view_all' );

$query = new WP_Query(
	array(
		'post_type'      => 'case_study',
		'post_status'    => 'publish',
		'posts_per_page' => $limit > 0 ? $limit : -1,
		'orderby'        => array(
			'menu_order' => 'ASC',
			'date'       => 'DESC',
		),
		'no_found_rows'  => true,
	)
);

// The filters are built from the tags actually in use, so none is ever empty.
$groups = array(
	'sector'  => array(
		'label' => __( 'Sector', 'cb-andwislifts2026' ),
		'terms' => array(),
	),
	'service' => array(
		'label' => __( 'Service', 'cb-andwislifts2026' ),
		'terms' => array(),
	),
);
$cards  = array();

foreach ( $query->posts as $item ) {
	$slugs = array(
		'sector'  => array(),
		'service' => array(),
	);

	foreach ( cb_case_study_tags( $item->ID ) as $case_tag ) {
		if ( ! isset( $groups[ $case_tag['type'] ]['terms'][ $case_tag['slug'] ] ) ) {
			$groups[ $case_tag['type'] ]['terms'][ $case_tag['slug'] ] = array(
				'label' => $case_tag['label'],
				'count' => 0,
			);
		}

		++$groups[ $case_tag['type'] ]['terms'][ $case_tag['slug'] ]['count'];
		$slugs[ $case_tag['type'] ][] = $case_tag['slug'];
	}

	$cards[] = array(
		'post'  => $item,
		'slugs' => $slugs,
	);
}

// A way of browsing with only one choice in it is not worth offering.
foreach ( $groups as $group_key => $group ) {
	if ( count( $group['terms'] ) < 2 ) {
		unset( $groups[ $group_key ] );
		continue;
	}

	uasort(
		$groups[ $group_key ]['terms'],
		static function ( $a, $b ) {
			return strcasecmp( $a['label'], $b['label'] );
		}
	);
}

$filterable = ! $limit && $groups && $cards;
$total      = count( $cards );
/* translators: 1: case studies showing, 2: case studies in all. */
$count_text = __( 'Showing %1$s of %2$s case studies', 'cb-andwislifts2026' );
?>
<section class="cb-case-study-index <?= esc_attr( $extra ); ?>" id="<?= esc_attr( $section_id ); ?>">
	<div class="container">
		<?php if ( $heading ) { ?>
		<div class="cb-section-head pb-5">
			<h2><?= esc_html( $heading ); ?></h2>
		</div>
		<?php } ?>
		<?php
		if ( $filterable ) {
			$first = true;
			?>
		<div class="cb-case-study-index__browse">
			<?php if ( count( $groups ) > 1 ) { ?>
			<div class="cb-case-study-index__toggle">
				<span id="<?= esc_attr( $section_id ); ?>-browse"><?php esc_html_e( 'Browse by', 'cb-andwislifts2026' ); ?></span>
				<div class="cb-case-study-index__switch" role="group" aria-labelledby="<?= esc_attr( $section_id ); ?>-browse">
					<?php foreach ( $groups as $group_key => $group ) { ?>
					<button type="button" class="<?= $first ? 'is-active' : ''; ?>" data-group="<?= esc_attr( $group_key ); ?>" aria-pressed="<?= $first ? 'true' : 'false'; ?>"><?= esc_html( $group['label'] ); ?></button>
						<?php
						$first = false;
					}
					?>
				</div>
			</div>
				<?php
			}

			$first = true;

			foreach ( $groups as $group_key => $group ) {
				?>
			<div class="cb-case-study-index__chips" data-group="<?= esc_attr( $group_key ); ?>" role="group" aria-label="<?= esc_attr( $group['label'] ); ?>"<?= $first ? '' : ' hidden'; ?>>
				<button type="button" class="cb-case-study-index__chip is-active" data-filter="" aria-pressed="true"><?php esc_html_e( 'All', 'cb-andwislifts2026' ); ?> (<?= (int) $total; ?>)</button>
				<?php foreach ( $group['terms'] as $slug => $term ) { ?>
				<button type="button" class="cb-case-study-index__chip" data-filter="<?= esc_attr( $slug ); ?>" aria-pressed="false"><?= esc_html( $term['label'] ); ?> (<?= (int) $term['count']; ?>)</button>
				<?php } ?>
			</div>
				<?php
				$first = false;
			}
			?>
			<p class="cb-case-study-index__count" role="status"><?= esc_html( sprintf( $count_text, $total, $total ) ); ?></p>
		</div>
			<?php
		}

		if ( $cards ) {
			?>
		<div class="cb-case-study-grid">
			<?php foreach ( $cards as $card ) { ?>
			<div class="cb-case-study-index__col" data-sector="<?= esc_attr( implode( ' ', $card['slugs']['sector'] ) ); ?>" data-service="<?= esc_attr( implode( ' ', $card['slugs']['service'] ) ); ?>">
				<?php cb_case_study_card( $card['post'] ); ?>
			</div>
			<?php } ?>
		</div>
			<?php
			if ( ! empty( $view_all['url'] ) ) {
				?>
		<p class="cb-case-study-index__more">
			<a class="cb-case-study-card__cta" href="<?= esc_url( $view_all['url'] ); ?>"><?= esc_html( $view_all['title'] ? $view_all['title'] : 'View all' ); ?></a>
		</p>
				<?php
			}
		} else {
			?>
		<p class="cb-case-study-index__empty"><?= esc_html( $empty_msg ); ?></p>
			<?php
		}
		?>
	</div>
</section>
<?php
// Emitted on wp_footer rather than inline, matching the other blocks in this
// theme - an inline <script> in a block's output breaks its editor preview.
if ( $filterable ) {
	add_action(
		'wp_footer',
		function () use ( $section_id, $count_text ) {
			?>
<script>
document.addEventListener('DOMContentLoaded', function () {
	var block = document.getElementById(<?= wp_json_encode( $section_id ); ?>);
	if (!block) return;

	var each = function (list, fn) { Array.prototype.forEach.call(list, fn); };
	var switches = block.querySelectorAll('.cb-case-study-index__switch button');
	var rows = block.querySelectorAll('.cb-case-study-index__chips');
	var cols = block.querySelectorAll('.cb-case-study-index__col');
	var count = block.querySelector('.cb-case-study-index__count');
	var text = <?= wp_json_encode( $count_text ); ?>;
	var group = rows[0].getAttribute('data-group');
	var grid = block.querySelector('.cb-case-study-grid');
	var still = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	var timer;

	function swap(filter) {
		var shown = 0;

		each(cols, function (col) {
			var terms = (col.getAttribute('data-' + group) || '').split(' ');
			var match = !filter || terms.indexOf(filter) !== -1;
			col.hidden = !match;
			if (match) shown++;
		});

		count.textContent = text.replace('%1$s', shown).replace('%2$s', cols.length);
	}

	// The cards fade out, change while they cannot be seen, then fade back in.
	// The time here matches the fade out in the stylesheet.
	function apply(filter) {
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
	}

	function choose(row, chip) {
		each(row.querySelectorAll('.cb-case-study-index__chip'), function (other) {
			other.classList.toggle('is-active', other === chip);
			other.setAttribute('aria-pressed', other === chip ? 'true' : 'false');
		});
		apply(chip.getAttribute('data-filter'));
	}

	each(rows, function (row) {
		each(row.querySelectorAll('.cb-case-study-index__chip'), function (chip) {
			chip.addEventListener('click', function () { choose(row, chip); });
		});
	});

	// Switching how to browse starts again from "All".
	each(switches, function (button) {
		button.addEventListener('click', function () {
			group = button.getAttribute('data-group');

			each(switches, function (other) {
				other.classList.toggle('is-active', other === button);
				other.setAttribute('aria-pressed', other === button ? 'true' : 'false');
			});

			each(rows, function (row) {
				row.hidden = row.getAttribute('data-group') !== group;
				if (!row.hidden) choose(row, row.querySelector('.cb-case-study-index__chip'));
			});
		});
	});
});
</script>
			<?php
		},
		9999
	);
}
