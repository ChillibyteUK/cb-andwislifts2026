<?php
/**
 * Block template for CB Contact Cards.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

$section_id = $block['anchor'] ?? '';
$extra      = $block['className'] ?? '';
$heading    = get_field( 'heading' );
$intro      = get_field( 'intro' );
$note       = get_field( 'note' );
$cards      = get_field( 'contact_cards', 'option' );
?>
<section class="cb-contact-cards <?= esc_attr( $extra ); ?>"<?= $section_id ? ' id="' . esc_attr( $section_id ) . '"' : ''; ?>>
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
		<?php
		if ( $cards ) {
			?>
		<div class="row g-3">
			<?php
			foreach ( $cards as $card ) {
				$url = $card['link_url'] ?? '#';
				?>
			<div class="col-lg-4">
				<a class="cb-contact-card" href="<?= esc_url( $url ); ?>">
					<div class="cb-contact-card__icon">
						<?php
						if ( ! empty( $card['icon']['ID'] ) ) {
							echo wp_get_attachment_image( $card['icon']['ID'], 'thumbnail' );
						} else {
							?>
							<span aria-hidden="true">@</span>
							<?php
						}
						?>
					</div>
					<?php
					if ( ! empty( $card['title'] ) ) {
						?>
					<h3><?= esc_html( $card['title'] ); ?></h3>
						<?php
					}
					if ( ! empty( $card['description'] ) ) {
						?>
					<p><?= esc_html( $card['description'] ); ?></p>
						<?php
					}
					if ( ! empty( $card['value'] ) ) {
						?>
					<div class="cb-contact-card__value"><?= esc_html( $card['value'] ); ?></div>
						<?php
					}
					?>
				</a>
			</div>
				<?php
			}
			?>
		</div>
			<?php
		}
		if ( $note ) {
			?>
		<p class="cb-contact-cards__note"><?= esc_html( $note ); ?></p>
			<?php
		}
		?>
	</div>
</section>
