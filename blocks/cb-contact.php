<?php
/**
 * Block template for CB Contact.
 *
 * The body of the Contact page, under a light CB Hero:
 *
 * - Two large action cards that straddle the hero's curve. A card with a form
 *   of its own swaps that form in below, in place of the general one, so a
 *   visitor asking for a quote gets the quote fields without leaving the page.
 * - "Other ways to reach us": a row of pills. A pill with a link goes there.
 *   Any other pill, when pressed, shows its email address inside itself and
 *   its questions underneath; pressing another replaces them.
 * - A white card with the enquiry form on the left and, on the right, the head
 *   office, the opening hours and every regional office, each opening in place.
 *
 * Nothing is selected to begin with: the general form shows, and no answers,
 * so nobody reads through content that is not for them.
 *
 * The offices come from Site-wide Settings, the one list the site keeps. The
 * head office address, phone and email are the ones under General there.
 *
 * Everything is in the page without JavaScript: the chips' switching is added
 * by the script.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

$section_id = $block['anchor'] ?? $block['id'] ?? wp_unique_id( 'cb-contact-' );
$extra      = $block['className'] ?? '';
$uid        = sanitize_html_class( $section_id );
$form_id    = (int) get_field( 'form_id' );
$form_note  = trim( (string) get_field( 'form_note' ) );
$finder     = get_field( 'finder_link' );
$hours      = get_field( 'hours_text' );
$offices    = function_exists( 'cb_offices_shared' ) ? cb_offices_shared() : array();

// A link to a page on this site that is still a draft would be a dead end, so
// it is left out until the page is published.
$usable = static function ( $link ) {
	$url = is_array( $link ) ? trim( (string) ( $link['url'] ?? '' ) ) : '';

	if ( '' === $url ) {
		return false;
	}

	$path = wp_parse_url( $url, PHP_URL_PATH );
	$host = wp_parse_url( $url, PHP_URL_HOST );

	if ( ! $path || ( $host && wp_parse_url( home_url(), PHP_URL_HOST ) !== $host ) ) {
		return true;
	}

	$page = get_page_by_path( trim( $path, '/' ) );

	return ! $page || 'publish' === $page->post_status;
};

$actions = array_filter(
	(array) get_field( 'actions' ),
	static function ( $action ) {
		return is_array( $action ) && ! empty( $action['heading'] );
	}
);

// A way to reach us is shown if it leads somewhere: to a page, or to answers.
$ways = array();

foreach ( (array) get_field( 'ways' ) as $way ) {
	if ( ! is_array( $way ) || empty( $way['label'] ) ) {
		continue;
	}

	$way['questions'] = array_filter(
		(array) ( $way['questions'] ?? array() ),
		static function ( $row ) {
			return is_array( $row ) && ! empty( $row['question'] ) && ! empty( $row['answer'] );
		}
	);

	$way['email'] = sanitize_email( (string) ( $way['email'] ?? '' ) );

	if ( ! empty( $way['link']['url'] ) ) {
		if ( $usable( $way['link'] ) ) {
			$way['questions'] = array();
			$way['email']     = '';
			$ways[]           = $way;
		}
	} elseif ( $way['questions'] || $way['email'] ) {
		$ways[] = $way;
	}
}

// The forms: the general one, then one for each action card that has its own.
$forms = array(
	'general' => array(
		'id'      => $form_id,
		'heading' => get_field( 'form_heading' ) ? get_field( 'form_heading' ) : __( 'General enquiry', 'cb-andwislifts2026' ),
		'intro'   => (string) get_field( 'form_intro' ),
	),
);

foreach ( array_values( $actions ) as $index => $action ) {
	if ( ! empty( $action['form_id'] ) ) {
		$forms[ 'action-' . $index ] = array(
			'id'      => (int) $action['form_id'],
			'heading' => ! empty( $action['form_heading'] ) ? $action['form_heading'] : $action['heading'],
			'intro'   => ! empty( $action['form_intro'] ) ? $action['form_intro'] : (string) ( $action['text'] ?? '' ),
		);
	}
}

// The head office, as entered under General in Site-wide Settings.
// The address field hands back its line breaks as markup, so take it raw.
$head_address = trim( wp_strip_all_tags( (string) get_field( 'contact_address', 'option', false ) ) );
$head_phone   = trim( (string) get_field( 'contact_phone', 'option' ) );
$head_email   = trim( (string) get_field( 'contact_email', 'option' ) );
?>
<section class="cb-contact <?= esc_attr( $extra ); ?>" id="<?= esc_attr( $section_id ); ?>">
	<div class="container">
		<?php if ( $actions ) { ?>
		<div class="cb-contact__actions">
			<?php
			foreach ( array_values( $actions ) as $index => $action ) {
				$has_form = isset( $forms[ 'action-' . $index ] );
				$has_link = $usable( $action['link'] ?? null );
				?>
			<div class="cb-contact__action cb-contact__action--<?= 'bright' === ( $action['style'] ?? '' ) ? 'bright' : 'dark'; ?>">
				<h2><?= esc_html( $action['heading'] ); ?></h2>
				<?php if ( ! empty( $action['text'] ) ) { ?>
				<p><?= esc_html( $action['text'] ); ?></p>
				<?php } ?>
				<?php
				// With a form of its own the button swaps that form in. Its link is
				// where it goes without JavaScript, or the form below failing that.
				if ( $has_form || $has_link ) {
					?>
				<a class="btn btn-primary" href="<?= esc_url( $has_link ? $action['link']['url'] : '#general-enquiry' ); ?>"<?= $has_form ? ' data-cb-contact-form="' . esc_attr( 'action-' . $index ) . '"' : ''; ?><?= $has_link ? cb_link_target( $action['link'] ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?= esc_html( ! empty( $action['link']['title'] ) ? $action['link']['title'] : $action['heading'] ); ?></a>
				<?php } ?>
			</div>
			<?php } ?>
		</div>
		<?php } ?>

		<?php if ( $ways ) { ?>
		<div class="cb-contact__ways" data-cb-contact-ways>
			<h2><?= esc_html( get_field( 'ways_heading' ) ? get_field( 'ways_heading' ) : __( 'Other ways to reach us', 'cb-andwislifts2026' ) ); ?></h2>
			<div class="cb-contact__chips">
				<?php foreach ( $ways as $index => $way ) { ?>
				<div class="cb-contact__pill">
					<?php if ( ! empty( $way['link']['url'] ) ) { ?>
					<a class="cb-contact__chip" href="<?= esc_url( $way['link']['url'] ); ?>"<?= cb_link_target( $way['link'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?= esc_html( $way['label'] ); ?></a>
					<?php } else { ?>
					<a class="cb-contact__chip" href="<?= $way['questions'] ? '#' . esc_attr( $uid . '-way-' . $index ) : esc_url( 'mailto:' . $way['email'] ); ?>" data-cb-contact-chip="<?= esc_attr( $uid . '-way-' . $index ); ?>"><?= esc_html( $way['label'] ); ?></a>
						<?php if ( $way['email'] ) { ?>
					<a class="cb-contact__chip-email" href="<?= esc_url( 'mailto:' . $way['email'] ); ?>"><?= esc_html( $way['email'] ); ?></a>
						<?php } ?>
					<?php } ?>
				</div>
				<?php } ?>
			</div>
			<?php
			foreach ( $ways as $index => $way ) {
				if ( ! $way['questions'] ) {
					continue;
				}
				?>
			<div class="cb-contact__panel" id="<?= esc_attr( $uid . '-way-' . $index ); ?>">
				<h3><?= esc_html( ! empty( $way['faq_heading'] ) ? $way['faq_heading'] : $way['label'] ); ?></h3>
				<?php
				$first = true;

				foreach ( $way['questions'] as $row ) {
					?>
				<details class="cb-contact__faq" name="<?= esc_attr( $uid . '-way-' . $index ); ?>"<?= $first ? ' open' : ''; ?>>
					<summary><?= esc_html( $row['question'] ); ?></summary>
					<div class="cb-contact__answer"><?= wp_kses_post( wpautop( $row['answer'] ) ); ?></div>
				</details>
					<?php
					$first = false;
				}
				?>
			</div>
				<?php
			}
			?>
		</div>
		<?php } ?>

		<div class="cb-contact__card">
			<div class="cb-contact__form" id="general-enquiry" data-cb-contact-forms>
				<?php foreach ( $forms as $form_key => $form ) { ?>
				<div class="cb-contact__form-panel" data-cb-form="<?= esc_attr( $form_key ); ?>"<?= 'general' === $form_key ? '' : ' hidden'; ?>>
					<h2 tabindex="-1"><?= esc_html( $form['heading'] ); ?></h2>
					<?php if ( $form['intro'] ) { ?>
					<p class="cb-contact__lead"><?= esc_html( $form['intro'] ); ?></p>
					<?php } ?>
					<div class="cb-contact__form-wrap">
						<?php
						// In the editor, show a summary rather than the live form. Gravity
						// Forms emits its own inline scripts, and a <script> inside a block's
						// preview output breaks that preview in the editor.
						if ( ! empty( $is_preview ) ) {
							$form_object = $form['id'] && class_exists( 'GFAPI' ) ? GFAPI::get_form( $form['id'] ) : null;
							?>
						<p class="cb-form__placeholder"><?= esc_html( $form_object ? sprintf( /* translators: %s: Gravity Forms form title. */ __( 'Form: %s', 'cb-andwislifts2026' ), $form_object['title'] ) : __( 'Choose a form in the block settings.', 'cb-andwislifts2026' ) ); ?></p>
							<?php
						} elseif ( $form['id'] && function_exists( 'gravity_form' ) ) {
							gravity_form( $form['id'], false, false, false, null, true );
						}
						?>
					</div>
					<?php if ( 'general' !== $form_key ) { ?>
					<p class="cb-contact__note"><button type="button" class="cb-contact__reset" data-cb-contact-form="general"><?php esc_html_e( 'Send a general enquiry instead', 'cb-andwislifts2026' ); ?></button></p>
					<?php } elseif ( $form_note ) { ?>
					<p class="cb-contact__note"><?= wp_kses_post( make_clickable( esc_html( $form_note ) ) ); ?></p>
					<?php } ?>
				</div>
				<?php } ?>
			</div>

			<div class="cb-contact__offices">
				<div class="cb-contact__offices-intro">
				<h2><?= esc_html( get_field( 'offices_heading' ) ? get_field( 'offices_heading' ) : __( 'Our offices', 'cb-andwislifts2026' ) ); ?></h2>
				<?php if ( $head_address || $head_phone || $head_email ) { ?>
				<address class="cb-contact__head-office">
					<?php if ( $head_address ) { ?>
					<span><?= esc_html( implode( ', ', array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $head_address ) ) ) ) ); ?></span>
					<?php } ?>
					<?php if ( $head_phone ) { ?>
					<span><?php esc_html_e( 'Tel:', 'cb-andwislifts2026' ); ?> <a href="tel:<?= esc_attr( preg_replace( '/[^0-9+]/', '', $head_phone ) ); ?>"><?= esc_html( $head_phone ); ?></a></span>
					<?php } ?>
					<?php if ( $head_email ) { ?>
					<span><?php esc_html_e( 'Email:', 'cb-andwislifts2026' ); ?> <a href="mailto:<?= esc_attr( $head_email ); ?>"><?= esc_html( $head_email ); ?></a></span>
					<?php } ?>
				</address>
				<?php } ?>
				<?php if ( get_field( 'offices_text' ) ) { ?>
				<p class="cb-contact__lead"><?= esc_html( get_field( 'offices_text' ) ); ?></p>
				<?php } ?>
				<?php if ( $usable( $finder ) ) { ?>
				<p class="cb-contact__finder"><a href="<?= esc_url( $finder['url'] ); ?>"<?= cb_link_target( $finder ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?= esc_html( $finder['title'] ); ?></a></p>
				<?php } ?>
				</div>

				<?php if ( $hours ) { ?>
				<div class="cb-contact__hours">
					<?php if ( get_field( 'hours_heading' ) ) { ?>
					<h3><?= esc_html( get_field( 'hours_heading' ) ); ?></h3>
					<?php } ?>
					<?= wp_kses_post( $hours ); ?>
				</div>
				<?php } ?>

				<?php if ( $offices ) { ?>
				<div class="cb-contact__offices-list">
				<h2 class="cb-contact__list-heading"><?= esc_html( get_field( 'list_heading' ) ? get_field( 'list_heading' ) : __( 'All regional offices', 'cb-andwislifts2026' ) ); ?></h2>
				<div class="cb-contact__office-list">
					<?php
					foreach ( $offices as $office ) {
						$name      = trim( (string) ( $office['name'] ?? '' ) );
						$locations = cb_offices_locations( $office );

						if ( '' === $name ) {
							continue;
						}

						// A label only helps where it tells two addresses apart.
						$labelled = count( array_unique( wp_list_pluck( $locations, 'label' ) ) ) > 1;
						?>
					<details class="cb-contact__office">
						<summary><?= esc_html( $name ); ?></summary>
						<div class="cb-contact__office-body">
							<?php foreach ( $locations as $location ) { ?>
							<p>
								<?php if ( $labelled && $location['label'] ) { ?>
								<strong>(<?= esc_html( $location['label'] ); ?>)</strong><br>
								<?php } ?>
								<?php if ( $location['address'] ) { ?>
								<?= nl2br( esc_html( $location['address'] ) ); ?><br>
								<?php } ?>
								<?php if ( $location['phone'] ) { ?>
								<?php esc_html_e( 'Tel:', 'cb-andwislifts2026' ); ?> <a href="tel:<?= esc_attr( preg_replace( '/[^0-9+]/', '', $location['phone'] ) ); ?>"><?= esc_html( $location['phone'] ); ?></a>
								<?php } ?>
							</p>
							<?php } ?>
							<?php
							foreach ( (array) ( $office['contacts'] ?? array() ) as $contact ) {
								if ( empty( $contact['email'] ) ) {
									continue;
								}
								?>
							<p><?= esc_html( trim( ( $contact['role'] ?? '' ) . ' ' . ( $contact['name'] ?? '' ) ) ); ?> <a href="mailto:<?= esc_attr( $contact['email'] ); ?>"><?= esc_html( $contact['email'] ); ?></a></p>
								<?php
							}
							?>
						</div>
					</details>
						<?php
					}
					?>
				</div>
				</div>
				<?php } ?>
			</div>
		</div>
	</div>
</section>
<?php
// Emitted on wp_footer rather than inline, matching the other blocks in this
// theme - an inline <script> in a block's output breaks its editor preview.
add_action(
	'wp_footer',
	function () use ( $section_id ) {
		?>
<script>
document.addEventListener('DOMContentLoaded', function () {
	var block = document.getElementById(<?= wp_json_encode( $section_id ); ?>);
	if (!block) return;

	var each = function (list, fn) { Array.prototype.forEach.call(list, fn); };
	var chips = block.querySelectorAll('[data-cb-contact-chip]');
	var emails = block.querySelectorAll('.cb-contact__chip-email');
	var panels = block.querySelectorAll('.cb-contact__panel');
	var forms = block.querySelectorAll('.cb-contact__form-panel');

	// One pill at a time: its email inside it, its answers underneath.
	// Pressing the open one closes it. Nothing is open to begin with.
	function choose(chip) {
		var id = chip ? chip.getAttribute('data-cb-contact-chip') : '';

		each(chips, function (other) {
			var on = other === chip;
			var email = other.parentNode.querySelector('.cb-contact__chip-email');

			other.classList.toggle('is-active', on);
			other.parentNode.classList.toggle('is-active', on);
			other.setAttribute('aria-expanded', on ? 'true' : 'false');
			if (email) { email.hidden = !on; }
		});
		each(panels, function (panel) { panel.hidden = panel.id !== id; });
	}

	each(chips, function (chip) {
		chip.setAttribute('role', 'button');
		chip.addEventListener('click', function (event) {
			event.preventDefault();
			choose(chip.classList.contains('is-active') ? null : chip);
		});
	});

	if (chips.length) { choose(null); }

	// The top cards swap the form: quote fields, expert fields, or back to the
	// general enquiry. Any open answers close, and the form comes into view.
	each(block.querySelectorAll('[data-cb-contact-form]'), function (trigger) {
		trigger.addEventListener('click', function (event) {
			var key = trigger.getAttribute('data-cb-contact-form');
			var shown = null;

			each(forms, function (panel) {
				panel.hidden = panel.getAttribute('data-cb-form') !== key;
				if (!panel.hidden) { shown = panel; }
			});

			if (!shown) return;

			event.preventDefault();
			choose(null);

			var heading = shown.querySelector('h2');
			var top = shown.getBoundingClientRect().top + window.pageYOffset - 110;

			if (window.lenis && window.lenis.scrollTo) { window.lenis.scrollTo(top); } else { window.scrollTo({ top: top, behavior: 'smooth' }); }
			if (heading) { heading.focus({ preventScroll: true }); }
		});
	});
});
</script>
		<?php
	},
	9999
);
