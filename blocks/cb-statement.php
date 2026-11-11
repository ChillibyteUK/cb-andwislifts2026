<?php
/**
 * Block template for CB Statement.
 *
 * One short statement set inside a large ring, with the lift motif on its
 * edge and a supporting paragraph beside it.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

$section_id = $block['anchor'] ?? $block['id'] ?? wp_unique_id( 'cb-statement-' );
$extra      = $block['className'] ?? '';
$heading    = get_field( 'heading' );
$body       = get_field( 'body' );
$background = 'paper' === get_field( 'background' ) ? 'paper' : 'tint';

if ( ! $heading ) {
	return;
}
?>
<section class="cb-statement cb-statement--<?= esc_attr( $background ); ?> <?= esc_attr( $extra ); ?>" id="<?= esc_attr( $section_id ); ?>">
	<div class="container">
		<div class="cb-statement__inner">
			<div class="cb-statement__ring">
				<h2><?= esc_html( $heading ); ?></h2>
				<?php // Decorative: a lift call button ringed by dots that slowly turn. ?>
				<svg class="cb-statement__motif" viewBox="0 0 200 200" aria-hidden="true" focusable="false">
					<g class="cb-statement__dots">
						<?php for ( $i = 0; $i < 8; $i++ ) { ?>
							<circle cx="100" cy="16" r="16" transform="rotate(<?= esc_attr( $i * 45 ); ?> 100 100)"/>
						<?php } ?>
					</g>
					<circle class="cb-statement__hub" cx="100" cy="100" r="60"/>
					<path class="cb-statement__arrows" d="M100 62l22 30H78z M100 138l-22-30h44z"/>
				</svg>
			</div>
			<?php if ( $body ) { ?>
				<p class="cb-statement__body"><?= esc_html( $body ); ?></p>
			<?php } ?>
		</div>
	</div>
</section>
