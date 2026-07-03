<?php
/**
 * Block template for CB Customer Grid.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

$section_id = $block['anchor'] ?? '';
$extra      = $block['className'] ?? '';
$heading    = get_field( 'heading' );
$intro      = get_field( 'intro' );
$logos      = get_field( 'logos' ) ? get_field( 'logos' ) : array();
?>
<section class="cb-customer-grid <?= esc_attr( $extra ); ?>"<?= $section_id ? ' id="' . esc_attr( $section_id ) . '"' : ''; ?>>
	<div class="container">
		<div class="cb-section-head text-center">
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
	</div>
	<?php
    if ( $logos ) {
        ?>
    <div class="cb-customer-grid__marquee pb-5" data-marquee>
        <div data-marquee-track>
            <?php
            foreach ( $logos as $logo_id ) {
                ?>
            <div class="logo-cell">
                <figure>
                    <?= wp_get_attachment_image( $logo_id, 'large' ); ?>
                </figure>
            </div>
                <?php
            }
            ?>
        </div>
    </div>
	    <?php
    }
    ?>
</section>

<?php if ( $logos ) : ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof gsap === 'undefined') return;
    var prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    document.querySelectorAll('.cb-customer-grid [data-marquee]').forEach(function (el) {
        var track = el.querySelector('[data-marquee-track]');
        if (!track || track.dataset.ready === '1') return;
        track.dataset.ready = '1';

        track.innerHTML = track.innerHTML + track.innerHTML;

        if (prefersReduced) return;

        var speed = parseFloat(el.dataset.marqueeSpeed) || 80;
        var tween;

        function start() {
            var distance = track.scrollWidth / 2;
            if (!distance) return;
            if (tween) tween.kill();
            gsap.set(track, { x: 0 });
            tween = gsap.to(track, {
                x: -distance,
                duration: distance / speed,
                ease: 'none',
                repeat: -1
            });
        }

        start();

        var resizeTimer;
        window.addEventListener('resize', function () {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(start, 150);
        });

        el.addEventListener('mouseenter', function () {
            if (tween) gsap.to(tween, { timeScale: 0, duration: 0.3 });
        });
        el.addEventListener('mouseleave', function () {
            if (tween) gsap.to(tween, { timeScale: 1, duration: 0.3 });
        });
    });
});
</script>
<?php endif; ?>
<?php
