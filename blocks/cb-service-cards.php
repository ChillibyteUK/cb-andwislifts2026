<?php
/**
 * Block template for CB Service Cards.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

$section_id = $block['anchor'] ?? $block['id'] ?? wp_unique_id( 'cb-service-cards-' );
$extra      = $block['className'] ?? '';
$heading    = get_field( 'heading' );
$intro      = get_field( 'intro' );
$source     = get_field( 'source' ) ? get_field( 'source' ) : 'manual';

if ( 'manual' === $source ) {
	$cards = get_field( 'cards' );
} else {
	$cards = cb_get_cpt_cards( $source, (int) get_field( 'limit' ), get_field( 'selected_posts' ) );
}

// Column width follows the card count so the last row fills as evenly as it can:
// multiples of four go four-up, multiples of three go three-up, one or two take
// halves, and anything else falls back to four-up with the remainder centred.
//
// Five cards deliberately fall through to three-up (3 + 2 centred) rather than a
// single 20% row - at 20% the cards get tall and narrow, which read poorly in
// review.
$card_count = is_array( $cards ) ? count( $cards ) : 0;

if ( $card_count > 0 && $card_count <= 2 ) {
	$col_class = 'col-lg-6';
} elseif ( 0 === $card_count % 4 ) {
	$col_class = 'col-lg-3 col-md-6';
} elseif ( 0 === $card_count % 3 || 5 === $card_count ) {
	$col_class = 'col-lg-4 col-md-6';
} else {
	$col_class = 'col-lg-3 col-md-6';
}
?>
<section class="cb-service-cards <?= esc_attr( $extra ); ?>" id="<?= esc_attr( $section_id ); ?>">
	<div class="container">
		<div class="cb-section-head pb-5 cb-gsap-fade">
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
		if ( $cards ) {
			?>
		<div class="row g-3 justify-content-center">
			<?php
			foreach ( $cards as $card ) {
				$card_link = $card['link'] ?? null;
				$card_icon = $card['icon'] ?? null;
				?>
			<div class="<?= esc_attr( $col_class ); ?>">
				<div class="cb-service-card" style="opacity:0;visibility:hidden;transform:translate3d(0,20px,0);">
					<?php
					if ( ! empty( $card_icon['ID'] ) ) {
						?>
					<div class="cb-service-card__icon"><?= wp_get_attachment_image( $card_icon['ID'], 'thumbnail' ); ?></div>
						<?php
					}
					if ( ! empty( $card['title'] ) ) {
						?>
					<h3><?= esc_html( $card['title'] ); ?></h3>
						<?php
					}
					if ( ! empty( $card['description'] ) ) {
						?>
					<p><?= esc_html( $card['description'] ); ?></p>
						<?php
					}
					if ( ! empty( $card_link['url'] ) ) {
						$card_link_target = ! empty( $card_link['target'] ) ? $card_link['target'] : '_self';
						$card_link_title  = ! empty( $card_link['title'] ) ? $card_link['title'] : __( 'Learn more', 'cb-andwislifts2026' );
						?>
					<a href="<?= esc_url( $card_link['url'] ); ?>" target="<?= esc_attr( $card_link_target ); ?>"><?= esc_html( $card_link_title ); ?></a>
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
if ( $cards ) {
	add_action(
		'wp_footer',
		function () use ( $section_id ) {
			?>
<script>
document.addEventListener('DOMContentLoaded', function () {
	var block = document.getElementById(<?= wp_json_encode( $section_id ); ?>);
	if (!block) return;

	var cards = block.querySelectorAll('.cb-service-card');
	if (!cards.length) return;

	if (!window.gsap) {
		cards.forEach(function (card) {
			card.style.opacity = '1';
			card.style.visibility = 'visible';
			card.style.transform = 'none';
		});
		return;
	}

	if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
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

	var timeline = window.gsap.timeline(timelineOptions);

	timeline.to(cards, {
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
