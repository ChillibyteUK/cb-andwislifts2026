<?php
/**
 * Block template for CB Form.
 *
 * Renders a Gravity Form inside the theme's section/container wrapper so it
 * picks up the standard page width and vertical rhythm.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

$section_id = $block['anchor'] ?? $block['id'] ?? wp_unique_id( 'cb-form-' );
$extra      = $block['className'] ?? '';
$heading    = get_field( 'heading' );
$intro      = get_field( 'intro' );
$form_id    = get_field( 'form_id' );
?>
<section class="cb-form <?= esc_attr( $extra ); ?>" id="<?= esc_attr( $section_id ); ?>">
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
		<div class="cb-form__wrap">
			<?php
			// In the editor, show a summary rather than the live form. Gravity
			// Forms emits its own inline scripts, and a <script> inside a block's
			// preview output breaks that preview in the editor.
			if ( ! empty( $is_preview ) ) {
				$form_title = '';

				if ( $form_id && class_exists( 'GFAPI' ) ) {
					$form_object = GFAPI::get_form( (int) $form_id );
					$form_title  = $form_object ? $form_object['title'] : '';
				}
				?>
			<p class="cb-form__placeholder">
				<?php
				if ( $form_title ) {
					printf(
						/* translators: %s: Gravity Forms form title. */
						esc_html__( 'Form: %s', 'cb-andwislifts2026' ),
						esc_html( $form_title )
					);
				} else {
					esc_html_e( 'Choose a form in the block settings.', 'cb-andwislifts2026' );
				}
				?>
			</p>
				<?php
			} elseif ( $form_id && function_exists( 'gravity_form' ) ) {
				gravity_form( (int) $form_id, false, false, false, null, true );
			}
			?>
		</div>
	</div>
</section>
