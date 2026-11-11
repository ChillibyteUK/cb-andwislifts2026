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

// Icon is the original behaviour and stays the default, so blocks built before
// this option existed are untouched. The other three treat the same field as a
// photograph rather than a small mark.
$media_style = get_field( 'media_style' ) ? get_field( 'media_style' ) : 'icon';

// The phase two styles have no card around them: "photo" is a picture, a
// title, a line and a button; "plain" is the same, centred, under a small
// mark; "tile" is a picture with just the name and the button beside it.
$is_open = in_array( $media_style, array( 'photo', 'plain', 'tile' ), true );

// An optional closing card that prompts instead of describing a service.
$promo     = array(
	'heading' => get_field( 'promo_heading' ),
	'text'    => get_field( 'promo_text' ),
	'cta'     => get_field( 'promo_cta' ),
);
$has_promo = ! empty( $promo['heading'] );

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
$card_count = ( is_array( $cards ) ? count( $cards ) : 0 ) + ( $has_promo ? 1 : 0 );

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
<section class="cb-service-cards cb-service-cards--<?= esc_attr( $media_style ); ?> <?= esc_attr( $extra ); ?>" id="<?= esc_attr( $section_id ); ?>">
	<div class="container">
		<?php
		// No heading and no intro: no head, so it cannot hold the cards down.
		if ( $heading || $intro ) {
			?>
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
		}
		?>
		<?php
		if ( $cards ) {
			?>
		<div class="row g-3 justify-content-center">
			<?php
			foreach ( $cards as $card ) {
				$card_link = $card['link'] ?? null;
				$card_icon = $card['icon'] ?? null;

				if ( in_array( $media_style, array( 'photo', 'tile' ), true ) && ! empty( $card['photo']['ID'] ) ) {
					$card_icon = $card['photo'];
				}
				?>
			<div class="<?= esc_attr( $col_class ); ?>">
				<div class="cb-service-card cb-service-card--<?= esc_attr( $media_style ); ?>" style="opacity:0;visibility:hidden;transform:translate3d(0,20px,0);">
					<?php
					if ( ! empty( $card_icon['ID'] ) ) {
						// An icon is a small mark, so the thumbnail crop suits it. A
						// photograph needs a real image to cover the card.
						$card_media_size = 'icon' === $media_style ? 'thumbnail' : ( 'plain' === $media_style ? 'medium' : 'medium_large' );
						?>
					<div class="cb-service-card__media"><?= wp_get_attachment_image( $card_icon['ID'], $card_media_size, false, array( 'loading' => 'lazy' ) ); ?></div>
						<?php
					}
					?>
					<div class="cb-service-card__body">
						<?php
						if ( ! empty( $card['title'] ) ) {
							?>
						<h3><?= 'plain' === $media_style ? cb_split_title( $card['title'] ) : esc_html( $card['title'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h3>
							<?php
						}
						if ( ! empty( $card['description'] ) && 'tile' !== $media_style ) {
							?>
						<p><?= esc_html( $card['description'] ); ?></p>
							<?php
						}
						if ( ! empty( $card_link['url'] ) ) {
							$card_link_target = ! empty( $card_link['target'] ) ? $card_link['target'] : '_self';
							$card_link_title  = ! empty( $card_link['title'] ) ? $card_link['title'] : __( 'Learn more', 'cb-andwislifts2026' );
							?>
						<a class="<?= $is_open ? 'btn btn-ghost cb-service-card__button' : 'cb-service-card__link'; ?>" href="<?= esc_url( $card_link['url'] ); ?>" target="<?= esc_attr( $card_link_target ); ?>"><?= esc_html( $card_link_title ); ?></a>
							<?php
						}
						?>
					</div>
				</div>
			</div>
				<?php
			}

			if ( $has_promo ) {
				?>
			<div class="<?= esc_attr( $col_class ); ?>">
				<div class="cb-service-card--promo">
					<h3><?= esc_html( $promo['heading'] ); ?></h3>
					<?php if ( $promo['text'] ) { ?>
						<p><?= esc_html( $promo['text'] ); ?></p>
					<?php } ?>
					<?php if ( ! empty( $promo['cta']['url'] ) ) { ?>
						<a class="btn btn-ghost" href="<?= esc_url( $promo['cta']['url'] ); ?>"<?= cb_link_target( $promo['cta'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?= esc_html( $promo['cta']['title'] ); ?></a>
					<?php } ?>
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
