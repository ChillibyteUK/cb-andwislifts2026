<?php
/**
 * Block template for CB Pill Strip.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

$section_id       = $block['anchor'] ?? $block['id'] ?? '';
$extra            = $block['className'] ?? '';
$label            = get_field( 'label' );
$pills            = get_field( 'pills' );

// Pills suit short labels. Anything longer - a list of standards, say - wraps
// into a ragged centred block, so the columns layout renders the same rows as a
// two-column list instead.
$layout = get_field( 'layout' ) ? get_field( 'layout' ) : 'pills';
?>
<div class="cb-pill-strip cb-pill-strip--<?= esc_attr( $layout ); ?> <?= esc_attr( $extra ); ?>"<?= $section_id ? ' id="' . esc_attr( $section_id ) . '"' : ''; ?>>
	<div class="container">
		<?php
        if ( $label ) {
			?>
		<div class="cb-pill-strip__label"><?= esc_html( $label ); ?></div>
			<?php
		}
        if ( $pills && 'columns' === $layout ) {
			?>
		<ul class="cb-pill-strip__list">
			<?php
			foreach ( $pills as $pill ) {
				if ( empty( $pill['text'] ) ) {
					continue;
				}

				$text = $pill['text'];
				$code = '';

				// Rows are written "EN81-21 - New lifts in existing buildings".
				// Pulling the reference out lets it carry the weight, with the
				// description beside it. Anything not in that shape renders whole.
				if ( false !== strpos( $text, ' - ' ) ) {
					list( $first, $rest ) = explode( ' - ', $text, 2 );

					if ( strlen( $first ) <= 32 ) {
						$code = $first;
						$text = $rest;
					}
				}
				?>
			<li>
				<?php if ( $code ) { ?>
				<span class="cb-pill-strip__code"><?= esc_html( $code ); ?></span>
				<?php } ?>
				<span class="cb-pill-strip__text"><?= esc_html( $text ); ?></span>
			</li>
				<?php
			}
			?>
		</ul>
			<?php
        } elseif ( $pills ) {
			?>
		<div class="cb-pill-strip__pills">
            <?php
			foreach ( $pills as $pill ) {
				if ( ! empty( $pill['text'] ) ) {
					?>
			<span><?= esc_html( $pill['text'] ); ?></span>
					<?php
				}
			}
			?>
		</div>
        	<?php
        }
		?>
	</div>
</div>
