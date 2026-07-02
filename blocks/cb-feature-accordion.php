<?php
/**
 * Block template for CB Feature Accordion.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

$section_id       = $block['anchor'] ?? '';
$extra            = $block['className'] ?? '';
$image            = get_field( 'image' );
$heading          = get_field( 'heading' );
$intro            = get_field( 'intro' );
$items            = get_field( 'accordion_items' );
$background_style = get_field( 'background_style' );
if ( ! $background_style ) {
	$background_style = 'tint';
}
?>
<section class="cb-feature-accordion cb-feature-accordion--<?= esc_attr( $background_style ); ?> <?= esc_attr( $extra ); ?>"<?= $section_id ? ' id="' . esc_attr( $section_id ) . '"' : ''; ?>>
	<div class="cb-wrap">
		<div class="cb-feature-accordion__grid">
			<?php
            if ( ! empty( $image['ID'] ) ) :
				?>
                <div class="cb-feature-accordion__media"><?= wp_get_attachment_image( $image['ID'], 'large' ); ?></div><?php endif; ?>
			<div class="cb-feature-accordion__copy">
				<?php
                if ( $heading ) :
					?>
                    <h3><?= esc_html( $heading ); ?></h3><?php endif; ?>
				<?php
                if ( $intro ) :
					?>
                    <div class="cb-prose"><?= wp_kses_post( wpautop( $intro ) ); ?></div><?php endif; ?>
				<?php if ( $items ) : ?>
					<div class="cb-accordion">
						<?php foreach ( $items as $item ) : ?>
							<details<?= ! empty( $item['open_by_default'] ) ? ' open' : ''; ?>>
								<summary><?= esc_html( $item['title'] ?? '' ); ?> <span class="cb-accordion__plus">+</span></summary>
								<div class="cb-accordion__panel">
									<?= wp_kses_post( wpautop( $item['body'] ?? '' ) ); ?>
									<?php if ( ! empty( $item['included_items'] ) ) : ?>
										<ul>
                                        <?php
                                        foreach ( $item['included_items'] as $included ) :
											?>
                                            <?php
											if ( ! empty( $included['text'] ) ) :
												?>
                                            <li><?= esc_html( $included['text'] ); ?></li><?php endif; ?><?php endforeach; ?></ul>
									<?php endif; ?>
								</div>
							</details>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
