<?php
/**
 * Block template for CB Compliance.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

$section_id     = $block['anchor'] ?? $block['id'] ?? wp_unique_id( 'cb-compliance-' );
$extra          = $block['className'] ?? '';
$heading        = get_field( 'heading' );
$body           = get_field( 'body' );
$image          = get_field( 'image' );
$logos          = get_field( 'logos' );
$image_position = get_field( 'image_position' ) ? get_field( 'image_position' ) : 'left';
?>
<section class="cb-compliance cb-compliance--image-<?= esc_attr( $image_position ); ?> <?= esc_attr( $extra ); ?>"<?= $section_id ? ' id="' . esc_attr( $section_id ) . '"' : ''; ?>>
	<?php
	if ( ! empty( $image['ID'] ) ) {
		?>
	<div class="cb-compliance__bg" style="background-image:url('<?= esc_url( wp_get_attachment_image_url( $image['ID'], 'full' ) ); ?>');" aria-hidden="true"></div>
		<?php
	}
	?>
	<div class="container">
		<div class="row align-items-center">
			<div class="<?= ! empty( $image['ID'] ) ? 'col-lg-6 cb-compliance__copy-col' : 'col-lg-8 mx-auto'; ?>">
				<?php if ( ! empty( $image['ID'] ) ) { ?>
					<div class="cb-compliance__curve" aria-hidden="true">
						<svg viewBox="0 0 66 1440" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
							<g transform="translate(0 1440) rotate(-90)">
								<path d="M1440 66C1283.48 26.17 1019.4 0 720 0S156.52 26.17 0 66h1440z" fill="currentColor"/>
							</g>
						</svg>
					</div>
				<?php } ?>
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
