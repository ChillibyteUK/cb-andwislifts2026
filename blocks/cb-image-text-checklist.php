<?php
/**
 * Block template for CB Image Text Checklist.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

$section_id     = $block['anchor'] ?? '';
$extra          = $block['className'] ?? '';
$heading        = get_field( 'heading' );
$body           = get_field( 'body' );
$list_label     = get_field( 'list_label' );
$items          = get_field( 'checklist' );
$image          = get_field( 'image' );
$image_position = get_field( 'image_position' ) ? get_field( 'image_position' ) : 'right';
?>
<section class="cb-image-text-checklist cb-image-text-checklist--white <?= esc_attr( $extra ); ?>"<?= $section_id ? ' id="' . esc_attr( $section_id ) . '"' : ''; ?>>
	<div class="container">
		<div class="row gy-5 align-items-center">
			<div class="col-lg-6 <?= 'left' === $image_position ? 'order-lg-last' : ''; ?>">
				<div class="cb-image-text-checklist__copy">
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
					if ( $list_label ) {
						?>
					<p class="cb-small-label"><?= esc_html( $list_label ); ?></p>
						<?php
					}
					if ( $items ) {
						?>
						<ul class="cb-check-list">
							<?php
							foreach ( $items as $item ) {
								if ( ! empty( $item['text'] ) ) {
									?>
							<li><span class="cb-check" aria-hidden="true">&#10003;</span><?= esc_html( $item['text'] ); ?></li>
									<?php
								}
							}
							?>
						</ul>
						<?php
					}
					?>
				</div>
			</div>
			<?php
			if ( ! empty( $image['ID'] ) ) {
				?>
			<div class="col-lg-5 offset-lg-1 <?= 'left' === $image_position ? 'order-lg-first' : ''; ?>">
				<div class="cb-image-text-checklist__media">
					<?php
					if ( ! empty( $image['ID'] ) ) {
						echo wp_get_attachment_image( $image['ID'], 'large' );
					}
					?>
				</div>
			</div>
				<?php
			}
			?>
		</div>
	</div>
</section>
