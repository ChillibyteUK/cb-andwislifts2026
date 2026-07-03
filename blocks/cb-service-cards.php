<?php
/**
 * Block template for CB Service Cards.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

$section_id = $block['anchor'] ?? '';
$extra      = $block['className'] ?? '';
$heading    = get_field( 'heading' );
$intro      = get_field( 'intro' );
$cards      = get_field( 'cards' );
?>
<section class="cb-service-cards <?= esc_attr( $extra ); ?>"<?= $section_id ? ' id="' . esc_attr( $section_id ) . '"' : ''; ?>>
	<div class="container">
		<div class="cb-section-head pb-5">
			<?php if ( $heading ) { ?>
				<h2><?= esc_html( $heading ); ?></h2>
			<?php } ?>
			<?php if ( $intro ) { ?>
				<p><?= esc_html( $intro ); ?></p>
			<?php } ?>
		</div>
		<?php if ( $cards ) { ?>
			<div class="row g-3">
				<?php foreach ( $cards as $card ) { ?>
					<?php $card_link = $card['link'] ?? null; ?>
					<div class="col-lg-3 col-md-6">
						<div class="cb-service-card">
							<?php if ( ! empty( $card['number'] ) ) { ?>
								<div class="cb-service-card__num"><?= esc_html( $card['number'] ); ?></div>
							<?php } ?>
							<?php if ( ! empty( $card['title'] ) ) { ?>
								<h3><?= esc_html( $card['title'] ); ?></h3>
							<?php } ?>
							<?php if ( ! empty( $card['description'] ) ) { ?>
								<p><?= esc_html( $card['description'] ); ?></p>
							<?php } ?>
							<?php
							if ( ! empty( $card_link['url'] ) ) {
								$card_link_target = ! empty( $card_link['target'] ) ? $card_link['target'] : '_self';
								$card_link_title  = ! empty( $card_link['title'] ) ? $card_link['title'] : __( 'Learn more', 'cb-andwislifts2026' );
								?>
								<a href="<?= esc_url( $card_link['url'] ); ?>" target="<?= esc_attr( $card_link_target ); ?>"><?= esc_html( $card_link_title ); ?></a>
							<?php } ?>
						</div>
					</div>
				<?php } ?>
			</div>
		<?php } ?>
	</div>
</section>
