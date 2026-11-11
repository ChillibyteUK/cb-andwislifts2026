<?php
/**
 * Block template for CB Reasons.
 *
 * Up to four reasons in a fixed arrangement: a large photo card, a dark card,
 * a small photo card and a wide pale card. The position decides the style, so
 * editors only supply the words and, for the first and third, a photo.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

$section_id = $block['anchor'] ?? $block['id'] ?? wp_unique_id( 'cb-reasons-' );
$extra      = $block['className'] ?? '';
$heading    = get_field( 'heading' );
$intro      = get_field( 'intro' );
$items      = array_slice(
	array_filter(
		(array) get_field( 'items' ),
		static function ( $item ) {
			return is_array( $item ) && '' !== trim( (string) ( $item['title'] ?? '' ) );
		}
	),
	0,
	4
);

if ( ! $items ) {
	return;
}
?>
<section class="cb-reasons <?= esc_attr( $extra ); ?>" id="<?= esc_attr( $section_id ); ?>">
	<svg class="cb-reasons__shapes cb-reasons__shapes--left" viewBox="0 0 220 320" aria-hidden="true" focusable="false"><path d="M30 40l110 34-80 70z M150 170l60 70-90 20z M20 250l70 60H10z"/></svg>
	<svg class="cb-reasons__shapes cb-reasons__shapes--right" viewBox="0 0 260 320" aria-hidden="true" focusable="false"><circle cx="70" cy="40" r="42"/><circle cx="20" cy="170" r="44"/><circle cx="90" cy="300" r="44"/><circle cx="220" cy="350" r="44"/></svg>
	<div class="container">
		<?php if ( $heading || $intro ) { ?>
			<div class="cb-reasons__head">
				<?php if ( $heading ) { ?>
					<h2><?= cb_highlight( $heading ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h2>
				<?php } ?>
				<?php if ( $intro ) { ?>
					<p><?= esc_html( $intro ); ?></p>
				<?php } ?>
			</div>
		<?php } ?>

		<div class="cb-reasons__grid cb-reasons__grid--<?= esc_attr( count( $items ) ); ?>">
			<?php
			foreach ( array_values( $items ) as $index => $item ) {
				// Only the first and third positions are photo cards.
				$photo = in_array( $index, array( 0, 2 ), true ) && ! empty( $item['image']['ID'] ) ? (int) $item['image']['ID'] : 0;
				?>
				<article class="cb-reasons__card cb-reasons__card--<?= esc_attr( $index + 1 ); ?><?= $photo ? ' cb-reasons__card--photo' : ''; ?>">
					<?= $photo ? wp_get_attachment_image( $photo, 'large', false, array( 'loading' => 'lazy' ) ) : ''; ?>
					<div class="cb-reasons__body">
						<h3><?= esc_html( $item['title'] ); ?></h3>
						<?php if ( ! empty( $item['text'] ) ) { ?>
							<p><?= esc_html( $item['text'] ); ?></p>
						<?php } ?>
					</div>
				</article>
				<?php
			}
			?>
		</div>
	</div>
</section>
