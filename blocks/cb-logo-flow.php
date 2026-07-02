<?php
/**
 * Block template for CB Logo Flow.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

$section_id   = $block['anchor'] ?? '';
$extra        = $block['className'] ?? '';
$kicker       = get_field( 'kicker' );
$heading      = get_field( 'heading' );
$intro        = get_field( 'intro' );
$legacy_logos = get_field( 'legacy_logos' );
$result_logo  = get_field( 'result_logo' );
?>
<section class="cb-logo-flow <?= esc_attr( $extra ); ?>"<?= $section_id ? ' id="' . esc_attr( $section_id ) . '"' : ''; ?>>
	<div class="cb-wrap">
		<div class="cb-section-head">
			<?php
            if ( $kicker ) :
				?>
                <p class="cb-kicker"><?= esc_html( $kicker ); ?></p><?php endif; ?>
			<?php
            if ( $heading ) :
				?>
                <h2><?= esc_html( $heading ); ?></h2><?php endif; ?>
			<?php
            if ( $intro ) :
				?>
                <p><?= esc_html( $intro ); ?></p><?php endif; ?>
		</div>
		<?php if ( $legacy_logos || ! empty( $result_logo['ID'] ) ) : ?>
			<div class="cb-logo-flow__flow">
				<?php if ( $legacy_logos ) : ?>
					<?php foreach ( $legacy_logos as $index => $legacy ) : ?>
						<?php
                        if ( $index > 0 ) :
							?>
                            <span class="cb-logo-flow__plus">+</span><?php endif; ?>
						<div class="cb-logo-flow__legacy">
                        <?php
                        if ( ! empty( $legacy['logo']['ID'] ) ) :
							?>
                            <?= wp_get_attachment_image( $legacy['logo']['ID'], 'medium' ); ?>
                            <?php
elseif ( ! empty( $legacy['fallback_label'] ) ) :
	?>
                            <?= esc_html( $legacy['fallback_label'] ); ?><?php endif; ?></div>
					<?php endforeach; ?>
				<?php endif; ?>
				<?php if ( ! empty( $result_logo['ID'] ) ) : ?>
					<span class="cb-logo-flow__arrow" aria-hidden="true">→</span><div class="cb-logo-flow__result"><?= wp_get_attachment_image( $result_logo['ID'], 'medium' ); ?></div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
