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
$cards      = get_field( 'cards' );
?>
<section class="cb-service-cards <?= esc_attr( $extra ); ?>" id="<?= esc_attr( $section_id ); ?>">
	<div class="container">
		<div class="cb-section-head pb-5 cb-gsap-fade">
			<?php if ( $heading ) { ?>
				<h2><?= esc_html( $heading ); ?></h2>
			<?php } ?>
			<?php if ( $intro ) { ?>
				<p><?= esc_html( $intro ); ?></p>
			<?php } ?>
		</div>
		<?php if ( $cards ) { ?>
			<div class="row g-3">
				<?php foreach ( $cards as $card ) { ?>
					<?php $card_link = $card['link'] ?? null; ?>
					<?php $card_icon = $card['icon'] ?? null; ?>
					<div class="col-lg-3 col-md-6">
						<div class="cb-service-card" style="opacity:0;visibility:hidden;transform:translate3d(0,20px,0);">
							<?php if ( ! empty( $card_icon['ID'] ) ) { ?>
								<div class="cb-service-card__icon"><?= wp_get_attachment_image( $card_icon['ID'], 'thumbnail' ); ?></div>
							<?php } ?>
							<?php if ( ! empty( $card['title'] ) ) { ?>
								<h3><?= esc_html( $card['title'] ); ?></h3>
							<?php } ?>
							<?php if ( ! empty( $card['description'] ) ) { ?>
								<p><?= esc_html( $card['description'] ); ?></p>
							<?php } ?>
							<?php
							if ( ! empty( $card_link['url'] ) ) {
								$card_link_target = ! empty( $card_link['target'] ) ? $card_link['target'] : '_self';
								$card_link_title  = ! empty( $card_link['title'] ) ? $card_link['title'] : __( 'Learn more', 'cb-andwislifts2026' );
								?>
								<a href="<?= esc_url( $card_link['url'] ); ?>" target="<?= esc_attr( $card_link_target ); ?>"><?= esc_html( $card_link_title ); ?></a>
							<?php } ?>
						</div>
					</div>
				<?php } ?>
			</div>
		<?php } ?>
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
