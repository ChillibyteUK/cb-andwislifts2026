<?php
/**
 * Footer template.
 *
 * The data comes from inc/cb-footer.php. The curve along the footer's top edge
 * is drawn in _footer.scss and bites into whatever section the page ends on.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

$brand        = cb_footer_brand();
$columns      = cb_footer_columns();
$social_links = cb_footer_social_links();
$legal_links  = cb_footer_legal_links();
$legal_lines  = cb_footer_legal_lines();
$contact_url  = cb_footer_contact_url();
$link_target  = $brand['link']['target'] ? sprintf( ' target="%s" rel="noopener"', esc_attr( $brand['link']['target'] ) ) : '';
$arrow        = '<svg width="6" height="9" viewBox="0 0 6 9" aria-hidden="true" focusable="false"><path d="M1.25 1.25 4.5 4.5 1.25 7.75" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/></svg>';
$caret        = '<svg width="14" height="14" viewBox="0 0 14 14" aria-hidden="true" focusable="false"><circle cx="7" cy="7" r="7" fill="currentColor"/><path d="M4.25 5.75 7 8.75l2.75-3" fill="none" stroke="var(--cb-forest)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
?>
<footer id="footer" class="site-footer">
	<div class="container site-footer__inner">

		<div class="site-footer__brand">
			<a class="site-footer__logo" href="<?= esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'andwis lifts home', 'cb-andwislifts2026' ); ?>">
				<?= cb_site_logo_svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>
			<p class="site-footer__strapline"><?= esc_html( $brand['strapline'] ); ?></p>
			<p class="site-footer__text"><?= esc_html( $brand['text'] ); ?></p>
			<a class="site-footer__group-link" href="<?= esc_url( $brand['link']['url'] ); ?>"<?= $link_target; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
				<?= esc_html( $brand['link']['title'] ); ?><?= $arrow; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>
		</div>

		<?php if ( $columns ) { ?>
			<nav class="site-footer__cols" aria-label="<?php esc_attr_e( 'Footer navigation', 'cb-andwislifts2026' ); ?>">
				<?php foreach ( $columns as $column ) { ?>
					<div class="site-footer__col">
						<?php
						// The heading on wide screens, and a button that opens the
						// list on narrow ones. CSS shows one or the other, so only
						// one is ever exposed to assistive technology.
						?>
						<h2 class="site-footer__col-title"><?= esc_html( $column['heading'] ); ?></h2>
						<button class="site-footer__col-toggle" type="button" aria-expanded="false" aria-controls="<?= esc_attr( $column['id'] ); ?>">
							<?= esc_html( $column['heading'] ); ?><?= $caret; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</button>
						<ul class="site-footer__menu" id="<?= esc_attr( $column['id'] ); ?>">
							<?php
							foreach ( $column['items'] as $item ) {
								$item_target = ! empty( $item->target ) ? sprintf( ' target="%s" rel="noopener"', esc_attr( $item->target ) ) : '';
								?>
								<li><a href="<?= esc_url( $item->url ); ?>"<?= $item_target; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?= esc_html( $item->title ); ?></a></li>
								<?php
							}
							?>
						</ul>
					</div>
				<?php } ?>
			</nav>
		<?php } ?>

		<?php if ( $social_links ) { ?>
			<ul class="site-footer__social">
				<?php foreach ( $social_links as $slug => $profile ) { ?>
					<li>
						<a href="<?= esc_url( $profile['url'] ); ?>" aria-label="<?= esc_attr( $profile['label'] ); ?>" target="_blank" rel="noopener">
							<?= cb_footer_social_icon( $slug ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted inline SVG ?>
						</a>
					</li>
				<?php } ?>
			</ul>
		<?php } ?>

		<div class="site-footer__bar">
			<?php if ( $contact_url ) { ?>
				<a class="site-footer__contact" href="<?= esc_url( $contact_url ); ?>"><?php esc_html_e( 'Contact us', 'cb-andwislifts2026' ); ?></a>
			<?php } ?>

			<?php if ( $legal_links ) { ?>
				<ul class="site-footer__legal-links">
					<?php foreach ( $legal_links as $legal_url => $legal_label ) { ?>
						<li><a href="<?= esc_url( $legal_url ); ?>"><?= esc_html( $legal_label ); ?></a></li>
					<?php } ?>
				</ul>
			<?php } ?>
		</div>

		<a class="site-footer__top" href="#top" data-cb-back-to-top>
			<span class="site-footer__top-text">
				<span><?php esc_html_e( 'You\'ve reached the ground floor', 'cb-andwislifts2026' ); ?></span>
				<strong><?php esc_html_e( 'Back to top?', 'cb-andwislifts2026' ); ?></strong>
			</span>
			<span class="site-footer__top-icon" aria-hidden="true">
				<svg width="16" height="14" viewBox="0 0 16 14" focusable="false"><path d="M8 1.5 14.5 12.5h-13z" fill="currentColor" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/></svg>
			</span>
		</a>

		<div class="site-footer__small">
			<p>&copy; <?= esc_html( gmdate( 'Y' ) ); ?> andwis lifts</p>
			<?php if ( $legal_lines ) { ?>
				<p class="site-footer__legal-details">
					<?php
					// One statement per line. Each is escaped individually and
					// joined with literal markup, which also stops a VAT number
					// wrapping mid-way.
					echo implode( '<br>', array_map( 'esc_html', $legal_lines ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
				</p>
			<?php } ?>
		</div>

	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
