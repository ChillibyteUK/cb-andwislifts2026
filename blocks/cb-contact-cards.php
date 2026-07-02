<?php
/**
 * Block template for CB Contact Cards.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

$section_id = $block['anchor'] ?? '';
$extra      = $block['className'] ?? '';
$kicker     = get_field( 'kicker' );
$heading    = get_field( 'heading' );
$intro      = get_field( 'intro' );
$note       = get_field( 'note' );
$cards      = get_field( 'contact_cards', 'option' );
?>
<section class="cb-contact-cards <?= esc_attr( $extra ); ?>"<?= $section_id ? ' id="' . esc_attr( $section_id ) . '"' : ''; ?>>
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
			<div class="cb-contact-cards__grid">
				<?php foreach ( $cards as $card ) : ?>
					<?php $url = $card['link_url'] ?? ''; ?>
					<?php $card_url = $url ? $url : '#'; ?>
					<a class="cb-contact-card" href="<?= esc_url( $card_url ); ?>">
						<div class="cb-contact-card__icon">
                        <?php
                        if ( ! empty( $card['icon']['ID'] ) ) :
							?>
                            <?= wp_get_attachment_image( $card['icon']['ID'], 'thumbnail' ); ?>
                            <?php
else :
	?>
                            <span aria-hidden="true">@</span><?php endif; ?></div>
						<?php
                        if ( ! empty( $card['title'] ) ) :
							?>
                            <h3><?= esc_html( $card['title'] ); ?></h3><?php endif; ?>
						<?php
                        if ( ! empty( $card['description'] ) ) :
							?>
                            <p><?= esc_html( $card['description'] ); ?></p><?php endif; ?>
						<?php
                        if ( ! empty( $card['value'] ) ) :
							?>
                            <div class="cb-contact-card__value"><?= esc_html( $card['value'] ); ?></div><?php endif; ?>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
		<?php
        if ( $note ) :
			?>
            <p class="cb-contact-cards__note"><?= esc_html( $note ); ?></p><?php endif; ?>
	</div>
</section>
