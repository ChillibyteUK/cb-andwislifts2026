<?php
/**
 * Block template for CB Image Text Checklist.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

$section_id       = $block['anchor'] ?? '';
$extra            = $block['className'] ?? '';
$kicker           = get_field( 'kicker' );
$heading          = get_field( 'heading' );
$body             = get_field( 'body' );
$list_label       = get_field( 'list_label' );
$items            = get_field( 'checklist' );
$image            = get_field( 'image' );
$image_tag        = get_field( 'image_tag' );
$image_position   = get_field( 'image_position' );
$background_style = get_field( 'background_style' );
if ( ! $image_position ) {
	$image_position = 'right';
}
if ( ! $background_style ) {
	$background_style = 'white';
}
?>
<section class="cb-image-text-checklist cb-image-text-checklist--<?= esc_attr( $background_style ); ?> cb-image-text-checklist--image-<?= esc_attr( $image_position ); ?> <?= esc_attr( $extra ); ?>"<?= $section_id ? ' id="' . esc_attr( $section_id ) . '"' : ''; ?>>
	<div class="cb-wrap cb-image-text-checklist__grid">
		<div class="cb-image-text-checklist__copy">
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
            if ( $list_label ) :
				?>
                <p class="cb-small-label"><?= esc_html( $list_label ); ?></p><?php endif; ?>
			<?php if ( $items ) : ?>
				<ul class="cb-check-list">
					<?php foreach ( $items as $item ) : ?>
						<?php
                        if ( ! empty( $item['text'] ) ) :
							?>
                            <li><span class="cb-check" aria-hidden="true">&#10003;</span><?= esc_html( $item['text'] ); ?></li><?php endif; ?>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
		<?php if ( ! empty( $image['ID'] ) || $image_tag ) : ?>
			<div class="cb-image-text-checklist__media">
				<?php
                if ( ! empty( $image['ID'] ) ) :
					?>
                    <?= wp_get_attachment_image( $image['ID'], 'large' ); ?><?php endif; ?>
				<?php
                if ( $image_tag ) :
					?>
                    <span class="cb-image-text-checklist__tag"><?= esc_html( $image_tag ); ?></span><?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
