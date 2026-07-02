<?php
/**
 * Block template for CB Pill Strip.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

$section_id = $block['anchor'] ?? '';
$extra      = $block['className'] ?? '';
$label      = get_field( 'label' );
$pills      = get_field( 'pills' );
?>
<div class="cb-pill-strip <?= esc_attr( $extra ); ?>"<?= $section_id ? ' id="' . esc_attr( $section_id ) . '"' : ''; ?>>
	<div class="cb-wrap">
		<?php
        if ( $label ) :
			?>
            <div class="cb-pill-strip__label"><?= esc_html( $label ); ?></div><?php endif; ?>
		<?php
        if ( $pills ) :
			?>
            <div class="cb-pill-strip__pills">
            <?php
			foreach ( $pills as $pill ) :
				?>
						<?php
						if ( ! empty( $pill['text'] ) ) :
							?>
    <span><?= esc_html( $pill['text'] ); ?></span><?php endif; ?><?php endforeach; ?></div><?php endif; ?>
	</div>
</div>
