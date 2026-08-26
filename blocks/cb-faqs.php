<?php
/**
 * Block template for CB FAQs.
 *
 * Bootstrap accordion with optional category filtering. Every question is also
 * registered with cb_collect_faq(), which emits one aggregated FAQPage JSON-LD
 * for the whole page from inc/cb-faq-schema.php.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

$section_id = $block['anchor'] ?? $block['id'] ?? wp_unique_id( 'cb-faqs-' );
$extra      = $block['className'] ?? '';
$heading    = get_field( 'heading' );
$intro      = get_field( 'intro' );
$show_filter = get_field( 'show_filter' );
$items      = get_field( 'faq_items' );

// Collect the categories actually in use, so the filter never offers an empty one.
$categories = array();

if ( $items ) {
	foreach ( $items as $item ) {
		$category = trim( (string) ( $item['category'] ?? '' ) );
		if ( '' !== $category ) {
			$categories[ sanitize_title( $category ) ] = $category;
		}
	}
}
?>
<section class="cb-faqs <?= esc_attr( $extra ); ?>" id="<?= esc_attr( $section_id ); ?>">
	<div class="container">
		<div class="cb-section-head pb-5">
			<?php
			if ( $heading ) {
				?>
			<h2><?= esc_html( $heading ); ?></h2>
				<?php
			}
			if ( $intro ) {
				?>
			<p><?= esc_html( $intro ); ?></p>
				<?php
			}
			?>
		</div>
		<?php
		if ( $show_filter && count( $categories ) > 1 ) {
			?>
		<div class="cb-faqs__filter" role="group" aria-label="Filter questions by topic">
			<button type="button" class="cb-faqs__chip is-active" data-filter="all">All</button>
			<?php
			foreach ( $categories as $slug => $label ) {
				?>
			<button type="button" class="cb-faqs__chip" data-filter="<?= esc_attr( $slug ); ?>"><?= esc_html( $label ); ?></button>
				<?php
			}
			?>
		</div>
			<?php
		}
		if ( $items ) {
			?>
		<div class="accordion cb-faqs__list" id="<?= esc_attr( $section_id ); ?>-accordion">
			<?php
			$counter = 0;

			foreach ( $items as $item ) {
				$question = $item['question'] ?? '';
				$answer   = $item['answer'] ?? '';

				if ( ! $question ) {
					continue;
				}

				cb_collect_faq( $question, $answer );

				$category  = trim( (string) ( $item['category'] ?? '' ) );
				$cat_slug  = $category ? sanitize_title( $category ) : '';
				$item_id   = $section_id . '-' . $counter;
				?>
			<div class="accordion-item cb-faqs__item"<?= $cat_slug ? ' data-category="' . esc_attr( $cat_slug ) . '"' : ''; ?>>
				<h3 class="accordion-header">
					<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#<?= esc_attr( $item_id ); ?>" aria-expanded="false" aria-controls="<?= esc_attr( $item_id ); ?>">
						<?= esc_html( $question ); ?>
					</button>
				</h3>
				<div id="<?= esc_attr( $item_id ); ?>" class="accordion-collapse collapse" data-bs-parent="#<?= esc_attr( $section_id ); ?>-accordion">
					<div class="accordion-body"><?= wp_kses_post( wpautop( $answer ) ); ?></div>
				</div>
			</div>
				<?php
				++$counter;
			}
			?>
		</div>
			<?php
		}
		?>
	</div>
</section>
<?php
if ( $items && $show_filter && count( $categories ) > 1 ) {
	add_action(
		'wp_footer',
		function () use ( $section_id ) {
			?>
<script>
document.addEventListener('DOMContentLoaded', function () {
	var block = document.getElementById(<?= wp_json_encode( $section_id ); ?>);
	if (!block) return;

	var chips = block.querySelectorAll('.cb-faqs__chip');
	var items = block.querySelectorAll('.cb-faqs__item');

	chips.forEach(function (chip) {
		chip.addEventListener('click', function () {
			var filter = chip.getAttribute('data-filter');

			chips.forEach(function (c) { c.classList.remove('is-active'); });
			chip.classList.add('is-active');

			items.forEach(function (item) {
				var show = filter === 'all' || item.getAttribute('data-category') === filter;
				item.hidden = !show;

				// Close anything being hidden so the accordion cannot keep an
				// open panel in a filtered-out item.
				if (!show) {
					var panel = item.querySelector('.accordion-collapse');
					var button = item.querySelector('.accordion-button');
					if (panel) panel.classList.remove('show');
					if (button) {
						button.classList.add('collapsed');
						button.setAttribute('aria-expanded', 'false');
					}
				}
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
