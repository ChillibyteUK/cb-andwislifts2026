<?php
/**
 * Block template for CB Office Finder.
 *
 * A postcode field, the coverage map and the three offices nearest the
 * postcode. The lookup itself is in inc/cb-office-finder.php.
 *
 * Without JavaScript the form goes to the Contact page, where every office is
 * listed.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

$section_id = $block['anchor'] ?? $block['id'] ?? wp_unique_id( 'cb-office-finder-' );
$extra      = $block['className'] ?? '';
$uid        = sanitize_html_class( $section_id );
$label      = get_field( 'label' ) ? get_field( 'label' ) : __( 'Find your team', 'cb-andwislifts2026' );
$image      = get_field( 'image' );
$intro      = get_field( 'intro' );
$contact    = get_page_by_path( 'contact' );
$contact    = $contact ? get_permalink( $contact ) : home_url( '/' );

$GLOBALS['cb_office_finder_rendered'] = true;

// Everything the script says, so it can be translated with the rest.
$strings = array(
	'endpoint'   => rest_url( 'cb/v1/nearest-offices' ),
	'contactUrl' => $contact,
	'contact'    => __( 'Get in touch', 'cb-andwislifts2026' ),
	'mile'       => __( 'mile', 'cb-andwislifts2026' ),
	'miles'      => __( 'miles', 'cb-andwislifts2026' ),
	'searching'  => __( 'Finding your nearest teams...', 'cb-andwislifts2026' ),
	/* translators: %s: the postcode searched for. */
	'found'      => __( 'The three teams nearest %s, closest first.', 'cb-andwislifts2026' ),
	'error'      => __( 'Sorry, something went wrong. Please try again, or get in touch and we will point you to the right team.', 'cb-andwislifts2026' ),
);
?>
<section class="cb-office-finder <?= esc_attr( $extra ); ?>" id="<?= esc_attr( $section_id ); ?>" data-cb-office-finder="<?= esc_attr( wp_json_encode( $strings ) ); ?>">
	<div class="container">
		<form class="cb-office-finder__form" action="<?= esc_url( $contact ); ?>" method="get" role="search">
			<label for="<?= esc_attr( $uid ); ?>-postcode"><?= esc_html( $label ); ?></label>
			<div class="cb-office-finder__field">
				<input type="text" id="<?= esc_attr( $uid ); ?>-postcode" name="postcode" placeholder="<?php esc_attr_e( 'Enter your postcode', 'cb-andwislifts2026' ); ?>" autocomplete="postal-code" autocapitalize="characters" spellcheck="false" maxlength="8" required>
				<button class="btn btn-primary" type="submit"><?php esc_html_e( 'Search', 'cb-andwislifts2026' ); ?></button>
			</div>
		</form>

		<div class="cb-office-finder__inner">
			<?php if ( ! empty( $image['ID'] ) ) { ?>
				<div class="cb-office-finder__map">
					<?= wp_get_attachment_image( $image['ID'], 'large', false, array( 'loading' => 'lazy' ) ); ?>
				</div>
			<?php } ?>

			<div class="cb-office-finder__side">
				<?php if ( $intro ) { ?>
					<div class="cb-office-finder__intro"><?= wp_kses_post( wpautop( $intro ) ); ?></div>
				<?php } ?>

				<h2 class="cb-office-finder__title"><?php esc_html_e( 'Your nearest teams', 'cb-andwislifts2026' ); ?></h2>
				<p class="cb-office-finder__status" role="status"><?php esc_html_e( 'Enter your postcode to see the three teams closest to you.', 'cb-andwislifts2026' ); ?></p>
				<ul class="cb-office-finder__results"></ul>

				<?php if ( get_field( 'aside_heading' ) || get_field( 'aside_body' ) ) { ?>
					<div class="cb-office-finder__aside">
						<?php if ( get_field( 'aside_heading' ) ) { ?>
							<h3><?= esc_html( get_field( 'aside_heading' ) ); ?></h3>
						<?php } ?>
						<?php if ( get_field( 'aside_body' ) ) { ?>
							<p><?= esc_html( get_field( 'aside_body' ) ); ?></p>
						<?php } ?>
					</div>
				<?php } ?>
			</div>
		</div>
	</div>
</section>
