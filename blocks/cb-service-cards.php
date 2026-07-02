<?php
/**
 * Block template for CB Service Cards.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

$section_id = $block['anchor'] ?? '';
$extra      = $block['className'] ?? '';
$kicker     = get_field( 'kicker' );
$heading    = get_field( 'heading' );
$intro      = get_field( 'intro' );
$cards      = get_field( 'cards' );
?>
<section class="cb-service-cards <?= esc_attr( $extra ); ?>"<?= $section_id ? ' id="' . esc_attr( $section_id ) . '"' : ''; ?>>
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
		<?php if ( $cards ) : ?>
			<div class="cb-service-cards__grid">
				<?php foreach ( $cards as $card ) : ?>
					<?php $card_link = $card['link'] ?? null; ?>
					<div class="cb-service-card">
						<?php
                        if ( ! empty( $card['number'] ) ) :
							?>
                            <div class="cb-service-card__num"><?= esc_html( $card['number'] ); ?></div><?php endif; ?>
						<?php
                        if ( ! empty( $card['title'] ) ) :
							?>
                            <h3><?= esc_html( $card['title'] ); ?></h3><?php endif; ?>
						<?php
                        if ( ! empty( $card['description'] ) ) :
							?>
                            <p><?= esc_html( $card['description'] ); ?></p><?php endif; ?>
						<?php
						if ( ! empty( $card_link['url'] ) ) :
							?>
                            <?php
							$card_link_target = ! empty( $card_link['target'] ) ? $card_link['target'] : '_self';
							$card_link_title  = ! empty( $card_link['title'] ) ? $card_link['title'] : __( 'Learn more', 'cb-andwislifts2026' );
							?>
                            <a href="<?= esc_url( $card_link['url'] ); ?>" target="<?= esc_attr( $card_link_target ); ?>"><?= esc_html( $card_link_title ); ?></a><?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
