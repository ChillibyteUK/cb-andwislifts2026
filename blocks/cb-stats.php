<?php
/**
 * Block template for CB Stats.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

$section_id = $block['anchor'] ?? $block['id'] ?? wp_unique_id( 'cb-stats-' );
$extra      = $block['className'] ?? '';
$heading    = get_field( 'heading' );
$intro      = get_field( 'intro' );
$stats      = get_field( 'stats' );
?>
<section class="cb-stats <?= esc_attr( $extra ); ?>"<?= $section_id ? ' id="' . esc_attr( $section_id ) . '"' : ''; ?>>
	<div class="container">
		<div class="cb-section-head pb-4">
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
		if ( $stats ) {
			?>
		<div class="cb-stats__grid justify-content-center row g-4">
            <?php
            foreach ( $stats as $stat ) {
				?>
			<div class="cb-stats__stat col-md-6 cb-col-lg-5">
                <div class="cb-stats__card" style="opacity:0;visibility:hidden;transform:translate3d(0,20px,0);">
                <?php
				if ( ! empty( $stat['description'] ) ) {
					?>
					<span class="cb-stats__description"><?= esc_html( $stat['description'] ); ?></span>
					<?php
				}
				if ( ! empty( $stat['value'] ) ) {
					?>
					<span class="cb-stats__value"><?= esc_html( $stat['value'] ); ?></span>
                  	<?php
				}
				?>
				</div>
			</div>
				<?php
			}
			?>
		</div>
			<?php
		}
		?>
	</div>
</section>
<?php
if ( $stats ) {
	add_action(
		'wp_footer',
		function () use ( $section_id ) {
			?>
<script>
document.addEventListener('DOMContentLoaded', function () {
	var block = document.getElementById(<?= wp_json_encode( $section_id ); ?>);
	if (!block) return;

	var cards = block.querySelectorAll('.cb-stats__card');
	if (!cards.length) return;

	if (!window.gsap || (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches)) {
		cards.forEach(function (card) {
			card.style.opacity = '1';
			card.style.visibility = 'visible';
			card.style.transform = 'none';
		});
		return;
	}

	if (window.ScrollTrigger) {
		window.gsap.registerPlugin(window.ScrollTrigger);
	}

	var timelineOptions = {
		defaults: {
			ease: 'power2.out'
		}
	};

	if (window.ScrollTrigger) {
		timelineOptions.scrollTrigger = {
			trigger: block,
			start: 'top 75%',
			once: true
		};
	}

	window.gsap.timeline(timelineOptions).to(cards, {
		opacity: 1,
		visibility: 'visible',
		y: 0,
		duration: 0.55,
		stagger: 0.55,
		clearProps: 'opacity,visibility,transform'
	});
});
</script>
			<?php
		},
		9999
	);
}
