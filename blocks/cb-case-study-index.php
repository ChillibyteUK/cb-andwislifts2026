<?php
/**
 * Block template for CB Case Study Index.
 *
 * Lists published case studies as cards, with filter chips for sector and
 * service built from the relationship fields actually in use. Filtering is
 * client-side - every card is already in the DOM.
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

// Collect the filters actually in use so we never offer an empty one.
$filters = array();
$cards   = array();

foreach ( $query->posts as $item ) {
	$terms = array();

	foreach ( array( 'related_sectors', 'related_services' ) as $field ) {
		foreach ( (array) get_field( $field, $item->ID ) as $related_id ) {
			$label = get_the_title( $related_id );
			if ( ! $label ) {
				continue;
			}
			$slug             = sanitize_title( $label );
			$filters[ $slug ] = $label;
			$terms[]          = $slug;
		}
	}

	$cards[] = array(
		'post'  => $item,
		'terms' => $terms,
	);
}

asort( $filters );
?>
<section class="cb-case-study-index <?= esc_attr( $extra ); ?>" id="<?= esc_attr( $section_id ); ?>">
	<div class="container">
		<div class="cb-section-head pb-5">
			<?php
			if ( $heading ) {
				?>
			<h2><?= esc_html( $heading ); ?></h2>
				<?php
			}
			?>
		</div>
		<?php
		if ( ! $limit && count( $filters ) > 1 ) {
			?>
		<div class="cb-case-study-index__filter" role="group" aria-label="Filter case studies by sector or service">
			<button type="button" class="cb-case-study-index__chip is-active" data-filter="all">All</button>
			<?php
			foreach ( $filters as $slug => $label ) {
				?>
			<button type="button" class="cb-case-study-index__chip" data-filter="<?= esc_attr( $slug ); ?>"><?= esc_html( $label ); ?></button>
				<?php
			}
			?>
		</div>
			<?php
		}
		if ( $cards ) {
			?>
		<div class="row g-3">
			<?php
			foreach ( $cards as $card ) {
				$item    = $card['post'];
				$summary = get_field( 'card_summary', $item->ID );
				$image   = get_post_thumbnail_id( $item->ID );
				?>
			<div class="col-lg-4 col-md-6 cb-case-study-index__col" data-terms="<?= esc_attr( implode( ' ', $card['terms'] ) ); ?>">
				<a class="cb-case-study-card h-100" href="<?= esc_url( get_permalink( $item ) ); ?>">
					<?php
					if ( $image ) {
						?>
					<div class="cb-case-study-card__media"><?= wp_get_attachment_image( $image, 'medium_large' ); ?></div>
						<?php
					}
					?>
					<div class="cb-case-study-card__body">
						<h3><?= esc_html( get_the_title( $item ) ); ?></h3>
						<?php
						if ( $summary ) {
							?>
						<p><?= esc_html( wp_trim_words( $summary, 26 ) ); ?></p>
							<?php
						}
						?>
						<span class="cb-case-study-card__cta">Read the case study</span>
					</div>
				</a>
			</div>
				<?php
			}
			?>
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
if ( $cards && ! $limit && count( $filters ) > 1 ) {
	add_action(
		'wp_footer',
		function () use ( $section_id ) {
			?>
<script>
document.addEventListener('DOMContentLoaded', function () {
	var block = document.getElementById(<?= wp_json_encode( $section_id ); ?>);
	if (!block) return;

	var chips = block.querySelectorAll('.cb-case-study-index__chip');
	var cols = block.querySelectorAll('.cb-case-study-index__col');

	chips.forEach(function (chip) {
		chip.addEventListener('click', function () {
			var filter = chip.getAttribute('data-filter');
			chips.forEach(function (c) { c.classList.remove('is-active'); });
			chip.classList.add('is-active');

			cols.forEach(function (col) {
				var terms = (col.getAttribute('data-terms') || '').split(' ');
				col.hidden = !(filter === 'all' || terms.indexOf(filter) !== -1);
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
