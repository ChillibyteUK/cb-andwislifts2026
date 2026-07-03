<?php
/**
 * Block template for CB Logo Flow.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

$section_id   = $block['anchor'] ?? $block['id'] ?? wp_unique_id( 'cb-logo-flow-' );
$extra        = $block['className'] ?? '';
$heading      = get_field( 'heading' );
$intro        = get_field( 'intro' );
$legacy_logos = get_field( 'legacy_logos' );
$result_logo  = get_field( 'result_logo' );
$has_flow     = $legacy_logos || ! empty( $result_logo['ID'] );
?>
<section class="cb-logo-flow <?= esc_attr( $extra ); ?>" id="<?= esc_attr( $section_id ); ?>">
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
		if ( $has_flow ) {
			?>
		<div class="cb-logo-flow__flow">
			<?php
			if ( $legacy_logos ) {
				foreach ( $legacy_logos as $index => $legacy ) {
					?>
			<div class="cb-logo-flow__legacy">
					<?php
					if ( ! empty( $legacy['logo']['ID'] ) ) {
						?>
						<?= wp_get_attachment_image( $legacy['logo']['ID'], 'medium' ); ?>
						<?php
					} elseif ( ! empty( $legacy['fallback_label'] ) ) {
						?>
						<?= esc_html( $legacy['fallback_label'] ); ?>
						<?php
					}
					?>
			</div>
					<?php
				}
			}
			if ( ! empty( $result_logo['ID'] ) ) {
				?>
			<svg class="cb-logo-flow__arrow" aria-hidden="true" viewBox="0 0 20 20" width="1em" height="1em" fill="currentColor"><polygon points="4,2 4,18 18,10"/></svg><div class="cb-logo-flow__result"><?= wp_get_attachment_image( $result_logo['ID'], 'medium' ); ?></div>
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
if ( $has_flow ) {
	add_action(
		'wp_footer',
		function () use ( $section_id ) {
			?>
<script>
document.addEventListener('DOMContentLoaded', function () {
	if (!window.gsap) {
		return;
	}

	if (window.ScrollTrigger) {
		window.gsap.registerPlugin(window.ScrollTrigger);
	}

	var block = document.getElementById(<?= wp_json_encode( $section_id ); ?>);
	if (!block) {
		return;
	}

	var legacyLogos = block.querySelectorAll('.cb-logo-flow__legacy');
	var resultLogo = block.querySelector('.cb-logo-flow__result');
	if (!legacyLogos.length && !resultLogo) {
		return;
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

	if (legacyLogos.length) {
		timeline.from(legacyLogos, {
			autoAlpha: 0,
			y: 24,
			duration: 0.55,
			stagger: 0.55
		});
	}

	if (resultLogo) {
		timeline.from(
			resultLogo,
			{
				autoAlpha: 0,
				scale: 0.7,
				y: 12,
				duration: 0.9,
				ease: 'bounce.out'
			},
			legacyLogos.length ? '>' : 0
		);
	}
});
</script>
			<?php
		},
		9999
	);
}
