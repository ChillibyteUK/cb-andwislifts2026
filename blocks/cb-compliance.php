<?php
/**
 * Block template for CB Compliance.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

$section_id     = $block['anchor'] ?? '';
$extra          = $block['className'] ?? '';
$kicker         = get_field( 'kicker' );
$heading        = get_field( 'heading' );
$body           = get_field( 'body' );
$image          = get_field( 'image' );
$logos          = get_field( 'logos' );
$image_position = get_field( 'image_position' );
if ( ! $image_position ) {
	$image_position = 'left';
}
?>
<section class="cb-compliance cb-compliance--image-<?= esc_attr( $image_position ); ?> <?= esc_attr( $extra ); ?>"<?= $section_id ? ' id="' . esc_attr( $section_id ) . '"' : ''; ?>>
	<div class="cb-wrap cb-compliance__grid">
		<?php
        if ( ! empty( $image['ID'] ) ) :
			?>
            <div class="cb-compliance__media"><?= wp_get_attachment_image( $image['ID'], 'large' ); ?></div><?php endif; ?>
		<div class="cb-compliance__copy">
			<?php
            if ( $kicker ) :
				?>
                <p class="cb-kicker"><?= esc_html( $kicker ); ?></p><?php endif; ?>
			<?php
            if ( $heading ) :
				?>
                <h2><?= esc_html( $heading ); ?></h2><?php endif; ?>
			<?php
            if ( $body ) :
				?>
                <div class="cb-prose"><?= wp_kses_post( wpautop( $body ) ); ?></div><?php endif; ?>
			<?php
            if ( $logos ) :
				?>
                <div class="cb-compliance__logos">
                <?php
				foreach ( $logos as $logo ) :
					?>
                <div class="cb-compliance__logo">
								<?php
								if ( ! empty( $logo['logo']['ID'] ) ) :
									?>
									<?= wp_get_attachment_image( $logo['logo']['ID'], 'medium' ); ?>
									<?php
				elseif ( ! empty( $logo['fallback_label'] ) ) :
					?>
					<?= esc_html( $logo['fallback_label'] ); ?><?php endif; ?></div><?php endforeach; ?></div><?php endif; ?>
		</div>
	</div>
</section>
