<?php
/**
 * Block template for CB Stats.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

$section_id = $block['anchor'] ?? '';
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
                <?php
				if ( ! empty( $stat['value'] ) ) {
					?>
                <span class="cb-stats__value"><?= esc_html( $stat['value'] ); ?></span>
                	<?php
				}
				if ( ! empty( $stat['description'] ) ) {
					?>
    			<span class="cb-stats__description"><?= esc_html( $stat['description'] ); ?></span>
                	<?php
				}
				?>
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
