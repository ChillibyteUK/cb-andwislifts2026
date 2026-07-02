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
	<div class="cb-wrap">
		<div class="cb-section-head">
			<?php
            if ( $heading ) :
				?>
                <h2><?= esc_html( $heading ); ?></h2><?php endif; ?>
			<?php
            if ( $intro ) :
				?>
                <p><?= esc_html( $intro ); ?></p><?php endif; ?>
		</div>
		<?php if ( $stats ) : ?>
			<div class="cb-stats__grid">
            <?php
            foreach ( $stats as $stat ) :
				?>
                <div class="cb-stats__stat">
                <?php
				if ( ! empty( $stat['value'] ) ) :
					?>
                <span class="cb-stats__value"><?= esc_html( $stat['value'] ); ?></span><?php endif; ?>
                <?php
				if ( ! empty( $stat['description'] ) ) :
					?>
    <span class="cb-stats__description"><?= esc_html( $stat['description'] ); ?></span><?php endif; ?></div><?php endforeach; ?></div>
		<?php endif; ?>
	</div>
</section>
