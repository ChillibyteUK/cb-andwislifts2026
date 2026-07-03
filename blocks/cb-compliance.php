<?php
/**
 * Block template for CB Compliance.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

$section_id     = $block['anchor'] ?? '';
$extra          = $block['className'] ?? '';
$heading        = get_field( 'heading' );
$body           = get_field( 'body' );
$image          = get_field( 'image' );
$logos          = get_field( 'logos' );
$image_position = get_field( 'image_position' ) ? get_field( 'image_position' ) : 'left';
?>
<section class="cb-compliance cb-compliance--image-<?= esc_attr( $image_position ); ?> <?= esc_attr( $extra ); ?>"<?= $section_id ? ' id="' . esc_attr( $section_id ) . '"' : ''; ?>>
	<div class="container">
		<div class="row align-items-center">
			<?php
			if ( ! empty( $image['ID'] ) ) {
				?>
			<div class="col-lg-6 cb-compliance__media-col <?= 'right' === $image_position ? 'order-lg-last' : ''; ?>">
				<div class="cb-compliance__media"><?= wp_get_attachment_image( $image['ID'], 'large' ); ?></div>
			</div>
				<?php
			}
			?>
			<div class="<?= ! empty( $image['ID'] ) ? 'col-lg-6' : 'col-lg-8 mx-auto'; ?> <?= 'right' === $image_position ? 'order-lg-first' : ''; ?>">
				<div class="cb-compliance__copy">
					<?php
					if ( $heading ) {
						?>
					<h2><?= esc_html( $heading ); ?></h2>
						<?php
					}
					if ( $body ) {
						?>
					<div class="cb-prose"><?= wp_kses_post( wpautop( $body ) ); ?></div>
						<?php
					}
					if ( $logos ) {
						?>
					<div class="cb-compliance__logos">
						<?php
						foreach ( $logos as $logo ) {
							?>
						<div class="cb-compliance__logo">
							<?php
							if ( ! empty( $logo['logo']['ID'] ) ) {
								echo wp_get_attachment_image( $logo['logo']['ID'], 'medium' );
							} elseif ( ! empty( $logo['fallback_label'] ) ) {
								echo esc_html( $logo['fallback_label'] );
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
			</div>
		</div>
	</div>
</section>
