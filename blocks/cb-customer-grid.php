<?php
/**
 * Block template for CB Customer Grid.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

$section_id = $block['anchor'] ?? '';
$extra      = $block['className'] ?? '';
$kicker     = get_field( 'kicker' );
$heading    = get_field( 'heading' );
$intro      = get_field( 'intro' );
$logos      = get_field( 'logos' ) ?: array();
$note       = get_field( 'note' );

// Distribute logos into 5 columns.
$columns = array( array(), array(), array(), array(), array() );
foreach ( $logos as $index => $logo_id ) {
	$columns[ $index % 5 ][] = $logo_id;
}
?>
<section class="cb-customer-grid logo-slider-block <?= esc_attr( $extra ); ?>"<?= $section_id ? ' id="' . esc_attr( $section_id ) . '"' : ''; ?>>
	<div class="cb-wrap">
		<div class="cb-section-head">
			<?php if ( $kicker ) : ?>
				<p class="cb-kicker"><?= esc_html( $kicker ); ?></p>
			<?php endif; ?>
			<?php if ( $heading ) : ?>
				<h2><?= esc_html( $heading ); ?></h2>
			<?php endif; ?>
			<?php if ( $intro ) : ?>
				<p><?= esc_html( $intro ); ?></p>
			<?php endif; ?>
		</div>
		<?php if ( $logos ) : ?>
			<div class="cb-customer-grid__sliders" aria-label="Customer logos">
				<?php foreach ( $columns as $col_index => $col_logos ) : ?>
					<?php if ( $col_logos ) : ?>
						<div class="client-logos<?= $col_index >= 3 ? ' mobile-hidden' : ''; ?>">
							<div class="logo-slider" data-logo-slider-index="<?= esc_attr( $col_index ); ?>">
								<?php foreach ( $col_logos as $logo_id ) : ?>
									<div class="logo-cell">
										<figure>
											<?= wp_get_attachment_image( $logo_id, 'large' ); ?>
										</figure>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
		<?php if ( $note ) : ?>
			<p class="cb-customer-grid__note"><?= esc_html( $note ); ?></p>
		<?php endif; ?>
	</div>
</section>
