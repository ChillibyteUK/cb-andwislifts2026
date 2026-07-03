<?php
/**
 * Block template for CB Feature Accordion.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

$section_id = $block['anchor'] ?? $block['id'] ?? wp_unique_id( 'cb-feature-accordion-' );
$extra      = $block['className'] ?? '';
$image      = get_field( 'image' );
$heading    = get_field( 'heading' );
$intro      = get_field( 'intro' );
$items      = get_field( 'accordion_items' );
?>
<section class="cb-feature-accordion <?= esc_attr( $extra ); ?>"<?= $section_id ? ' id="' . esc_attr( $section_id ) . '"' : ''; ?>>
	<?php
	if ( ! empty( $image['ID'] ) ) {
		?>
	<div class="cb-feature-accordion__bg" style="background-image:url('<?= esc_url( wp_get_attachment_image_url( $image['ID'], 'full' ) ); ?>');" aria-hidden="true"></div>
		<?php
	}
	?>
	<div class="container">
		<div class="row gx-5 align-items-start">
			<div class="col-lg-6 offset-lg-6 cb-feature-accordion__copy-col">
				<div class="cb-feature-accordion__curve" aria-hidden="true">
					<svg viewBox="0 0 66 1440" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
						<g transform="translate(0 1440) rotate(-90)">
							<path d="M1440 66C1283.48 26.17 1019.4 0 720 0S156.52 26.17 0 66h1440z" fill="currentColor"/>
						</g>
					</svg>
				</div>
				<div class="cb-feature-accordion__copy">
				<?php
				if ( $heading ) {
					?>
				<h2><?= esc_html( $heading ); ?></h2>
					<?php
				}
				if ( $intro ) {
					?>
				<div class="cb-prose"><?= wp_kses_post( wpautop( $intro ) ); ?></div>
					<?php
				}
				if ( $items ) {
					$acc_idx = 0;
					?>
				<div class="cb-feature-accordion__items" id="<?= esc_attr( $section_id ); ?>-items">
					<?php
					foreach ( $items as $item ) {
						++$acc_idx;
						$item_id  = 'cb-acc-' . ( $section_id ? $section_id : 'cb-feature' ) . '-item-' . $acc_idx;
						$expanded = ! empty( $item['open_by_default'] );
						?>
					<div class="cb-feature-accordion__item">
						<button class="cb-feature-accordion__toggle<?= $expanded ? '' : ' collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#<?= esc_attr( $item_id ); ?>" aria-expanded="<?= $expanded ? 'true' : 'false'; ?>">
							<?= esc_html( $item['title'] ?? '' ); ?>
							<span class="cb-feature-accordion__icon">+</span>
						</button>
						<div id="<?= esc_attr( $item_id ); ?>" class="collapse<?= $expanded ? ' show' : ''; ?>" data-bs-parent="#<?= esc_attr( $section_id ); ?>-items">
							<div class="cb-feature-accordion__body">
								<?= wp_kses_post( wpautop( $item['body'] ?? '' ) ); ?>
								<?php
								if ( ! empty( $item['included_items'] ) ) {
									?>
								<ul>
									<?php
									foreach ( $item['included_items'] as $included ) {
										if ( ! empty( $included['text'] ) ) {
											?>
									<li><?= esc_html( $included['text'] ); ?></li>
											<?php
										}
									}
									?>
								</ul>
									<?php
								}
								?>
							</div>
						</div>
					</div>
						<?php
					}
					?>
				</div>
					<?php
				}
				?>
				</div>
			</div>
			</div>
	</div>
</section>
