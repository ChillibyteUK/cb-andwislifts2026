<?php
/**
 * Block template for CB Pill Strip.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

$section_id       = $block['anchor'] ?? $block['id'] ?? '';
$extra            = $block['className'] ?? '';
$label            = get_field( 'label' );
$pills      = get_field( 'pills' );
?>
<div class="cb-pill-strip <?= esc_attr( $extra ); ?>"<?= $section_id ? ' id="' . esc_attr( $section_id ) . '"' : ''; ?>>
	<div class="container">
		<?php
        if ( $label ) {
			?>
		<div class="cb-pill-strip__label"><?= esc_html( $label ); ?></div>
			<?php
		}
        if ( $pills ) {
			?>
		<div class="cb-pill-strip__pills">
            <?php
			foreach ( $pills as $pill ) {
				if ( ! empty( $pill['text'] ) ) {
					?>
			<span><?= esc_html( $pill['text'] ); ?></span>
					<?php
				}
			}
			?>
		</div>
        	<?php
        }
		?>
	</div>
</div>
