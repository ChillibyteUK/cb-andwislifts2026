<?php
/**
 * Block template for CB Resources.
 *
 * The body of the Resources page: three cards down the left and a column of
 * short call-to-action cards on the right.
 *
 * - Technical information: topics in columns, by group. A topic is a download
 *   if it has a file, a link if it has one, and otherwise opens in place to
 *   show its description.
 * - Product technical data: one chip per product, and that product's files
 *   as tiles. A tile with no file yet is shown as on request.
 * - Downloads and certificates: documents by group. The first group runs the
 *   full width and the rest sit two across. A group folds away after a set
 *   number of rows. The whole row downloads its file.
 *
 * On a phone the first two cards start closed behind "Click to expand".
 * Each download is pushed to the dataLayer as `resource_download`, with the
 * document's title.
 *
 * Everything is in the page without JavaScript: the chips, the folds and the
 * phone expanders are added by the script.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

$section_id = $block['anchor'] ?? $block['id'] ?? wp_unique_id( 'cb-resources-' );
$extra      = $block['className'] ?? '';
$uid        = sanitize_html_class( $section_id );
$note       = trim( (string) get_field( 'note' ) );
$side_cards = array_filter(
	(array) get_field( 'side_cards' ),
	static function ( $card ) {
		return is_array( $card ) && ( ! empty( $card['heading'] ) || ! empty( $card['text'] ) );
	}
);

// What a file is, as a reader would name it: PDF, DWG.
$extension = static function ( $file ) {
	$name = is_array( $file ) ? ( $file['filename'] ?? ( $file['url'] ?? '' ) ) : '';

	return $name ? strtoupper( pathinfo( (string) wp_parse_url( $name, PHP_URL_PATH ), PATHINFO_EXTENSION ) ) : '';
};

$download_icon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4v11M7.5 11 12 15.5 16.5 11M5 20h14"></path></svg>';

// ---- Technical information.
$topic_groups = array();
$topic_files  = false;

foreach ( (array) get_field( 'topics' ) as $topic ) {
	if ( ! is_array( $topic ) || empty( $topic['title'] ) ) {
		continue;
	}

	$topic_group                    = trim( (string) ( $topic['group'] ?? '' ) );
	$topic_groups[ $topic_group ][] = $topic;
	$topic_files                    = $topic_files || ! empty( $topic['file']['url'] );
}

// ---- Product technical data.
$products = array();

foreach ( (array) get_field( 'products' ) as $product ) {
	if ( ! is_array( $product ) || empty( $product['name'] ) ) {
		continue;
	}

	$product['files'] = array_filter(
		(array) ( $product['files'] ?? array() ),
		static function ( $row ) {
			return is_array( $row ) && ! empty( $row['label'] );
		}
	);

	if ( $product['files'] ) {
		$products[] = $product;
	}
}

// ---- Downloads and certificates.
$doc_groups = array();

foreach ( (array) get_field( 'documents' ) as $document ) {
	if ( ! is_array( $document ) || empty( $document['title'] ) ) {
		continue;
	}

	$doc_group = trim( (string) ( $document['group'] ?? '' ) );

	$doc_groups[ '' === $doc_group ? __( 'Documents', 'cb-andwislifts2026' ) : $doc_group ][] = $document;
}

$fold = (int) get_field( 'downloads_show' );
$fold = $fold > 0 ? $fold : 4;

$print_document = static function ( $document, $extra_row ) use ( $extension, $download_icon ) {
	$file = $document['file'] ?? null;
	$ext  = $extension( $file );
	$meta = array_filter(
		array(
			$ext,
			// A note in the editor's own words, or failing that when the file was last changed.
			trim( (string) ( $document['note'] ?? '' ) ) ? trim( (string) $document['note'] ) : ( ! empty( $file['modified'] ) ? sprintf( /* translators: %s: month and year. */ __( 'updated %s', 'cb-andwislifts2026' ), date_i18n( 'M Y', strtotime( $file['modified'] ) ) ) : '' ),
		)
	);

	// The whole row downloads the file, and says what it is: "ISO 9001
	// certificate (PDF, updated Jan 2026)".
	$has_file = ! empty( $file['url'] );
	$tag      = $has_file ? 'a' : 'div';
	$label    = $meta ? sprintf( '%s (%s)', $document['title'], implode( ', ', $meta ) ) : $document['title'];
	?>
	<li class="cb-resources__doc"<?= $extra_row ? ' data-cb-resources-extra' : ''; ?>>
		<<?= $tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- "a" or "div". ?> class="cb-resources__doc-row"<?php if ( $has_file ) { ?> href="<?= esc_url( $file['url'] ); ?>" download aria-label="<?= esc_attr( $label ); ?>" data-cb-download="<?= esc_attr( $document['title'] ); ?>" data-cb-download-type="<?= esc_attr( $ext ); ?>"<?php } ?>>
			<span class="cb-resources__doc-icon" aria-hidden="true"><?= esc_html( $ext ? $ext : 'DOC' ); ?></span>
			<span class="cb-resources__doc-text">
				<span class="cb-resources__doc-title"><?= esc_html( $document['title'] ); ?></span>
				<?php if ( $meta ) { ?>
				<span class="cb-resources__doc-meta"><?= esc_html( implode( ' · ', $meta ) ); ?></span>
				<?php } ?>
			</span>
			<?php if ( $has_file ) { ?>
			<span class="cb-resources__doc-link" aria-hidden="true"><?= $download_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed markup above. ?></span>
			<?php } else { ?>
			<span class="cb-resources__doc-pending"><?php esc_html_e( 'On request', 'cb-andwislifts2026' ); ?></span>
			<?php } ?>
		</<?= $tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	</li>
	<?php
};

if ( ! $topic_groups && ! $products && ! $doc_groups && ! $side_cards ) {
	return;
}
?>
<section class="cb-resources <?= esc_attr( $extra ); ?>" id="<?= esc_attr( $section_id ); ?>">
	<div class="container">
		<div class="cb-resources__row">
			<div class="cb-resources__main">
				<?php if ( $topic_groups ) { ?>
				<div class="cb-resources__card cb-resources__card--outline" id="technical-information">
					<div class="cb-resources__card-head">
						<h2><?= esc_html( get_field( 'tech_heading' ) ? get_field( 'tech_heading' ) : __( 'Technical information', 'cb-andwislifts2026' ) ); ?></h2>
						<?php if ( $topic_files ) { ?>
						<span class="cb-resources__badge cb-resources__badge--wide"><?php esc_html_e( 'Click to download', 'cb-andwislifts2026' ); ?></span>
						<?php } ?>
						<button type="button" class="cb-resources__badge cb-resources__expand" data-cb-resources-expand aria-expanded="true" aria-controls="<?= esc_attr( $uid . '-tech' ); ?>" data-open="<?php esc_attr_e( 'Click to expand', 'cb-andwislifts2026' ); ?>" data-close="<?php esc_attr_e( 'Close', 'cb-andwislifts2026' ); ?>" hidden><?php esc_html_e( 'Click to expand', 'cb-andwislifts2026' ); ?></button>
					</div>
					<?php if ( get_field( 'tech_intro' ) ) { ?>
					<p class="cb-resources__card-intro"><?= esc_html( get_field( 'tech_intro' ) ); ?></p>
					<?php } ?>
					<div class="cb-resources__topics" id="<?= esc_attr( $uid . '-tech' ); ?>">
						<?php foreach ( $topic_groups as $topic_group => $topics ) { ?>
						<div class="cb-resources__topic-group">
							<?php if ( '' !== $topic_group ) { ?>
							<h3><?= esc_html( $topic_group ); ?></h3>
							<?php } ?>
							<ul>
								<?php foreach ( $topics as $topic ) { ?>
								<li>
									<?php if ( ! empty( $topic['file']['url'] ) ) { ?>
									<a href="<?= esc_url( $topic['file']['url'] ); ?>" download data-cb-download="<?= esc_attr( $topic['title'] ); ?>" data-cb-download-type="<?= esc_attr( $extension( $topic['file'] ) ); ?>"><?= esc_html( $topic['title'] ); ?></a>
									<?php } elseif ( ! empty( $topic['link']['url'] ) ) { ?>
									<a href="<?= esc_url( $topic['link']['url'] ); ?>"<?= cb_link_target( $topic['link'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?= esc_html( $topic['title'] ); ?></a>
									<?php } elseif ( ! empty( $topic['description'] ) ) { ?>
									<details>
										<summary><?= esc_html( $topic['title'] ); ?></summary>
										<p><?= esc_html( $topic['description'] ); ?></p>
									</details>
									<?php } else { ?>
										<?= esc_html( $topic['title'] ); ?>
									<?php } ?>
								</li>
								<?php } ?>
							</ul>
						</div>
						<?php } ?>
					</div>
				</div>
				<?php } ?>

				<?php if ( $products ) { ?>
				<div class="cb-resources__card cb-resources__card--outline" id="product-data" data-cb-resources-products>
					<div class="cb-resources__card-head">
						<h2><?= esc_html( get_field( 'product_heading' ) ? get_field( 'product_heading' ) : __( 'Product technical data', 'cb-andwislifts2026' ) ); ?></h2>
						<button type="button" class="cb-resources__badge cb-resources__expand" data-cb-resources-expand aria-expanded="true" aria-controls="<?= esc_attr( $uid . '-products' ); ?>" data-open="<?php esc_attr_e( 'Click to expand', 'cb-andwislifts2026' ); ?>" data-close="<?php esc_attr_e( 'Close', 'cb-andwislifts2026' ); ?>" hidden><?php esc_html_e( 'Click to expand', 'cb-andwislifts2026' ); ?></button>
					</div>
					<div class="cb-resources__card-body" id="<?= esc_attr( $uid . '-products' ); ?>">
					<?php if ( count( $products ) > 1 ) { ?>
					<div class="cb-resources__chips" role="group" aria-label="<?php esc_attr_e( 'Choose a product', 'cb-andwislifts2026' ); ?>" hidden>
						<?php foreach ( $products as $index => $product ) { ?>
						<button type="button" class="cb-resources__chip<?= $index ? '' : ' is-active'; ?>" aria-pressed="<?= $index ? 'false' : 'true'; ?>" aria-controls="<?= esc_attr( $uid . '-product-' . $index ); ?>"><?= esc_html( $product['name'] ); ?></button>
						<?php } ?>
					</div>
					<?php } ?>
					<?php foreach ( $products as $index => $product ) { ?>
					<div class="cb-resources__product" id="<?= esc_attr( $uid . '-product-' . $index ); ?>">
						<h3><?= esc_html( $product['name'] ); ?></h3>
						<ul class="cb-resources__tiles">
							<?php
							foreach ( $product['files'] as $row ) {
								$ext = $extension( $row['file'] ?? null );
								?>
							<li>
								<?php if ( ! empty( $row['file']['url'] ) ) { ?>
								<a class="cb-resources__tile" href="<?= esc_url( $row['file']['url'] ); ?>" download data-cb-download="<?= esc_attr( $product['name'] . ' ' . $row['label'] ); ?>" data-cb-download-type="<?= esc_attr( $ext ); ?>">
									<span><?= esc_html( $row['label'] ); ?></span>
									<?php if ( $ext ) { ?>
									<small><?= esc_html( $ext ); ?></small>
									<?php } ?>
								</a>
								<?php } else { ?>
								<span class="cb-resources__tile cb-resources__tile--pending">
									<span><?= esc_html( $row['label'] ); ?></span>
									<small><?php esc_html_e( 'On request', 'cb-andwislifts2026' ); ?></small>
								</span>
								<?php } ?>
							</li>
							<?php } ?>
						</ul>
					</div>
					<?php } ?>
					</div>
				</div>
				<?php } ?>

				<?php if ( $doc_groups ) { ?>
				<div class="cb-resources__card cb-resources__card--tint" id="downloads" data-cb-resources-downloads>
					<div class="cb-resources__card-head">
						<h2><?= esc_html( get_field( 'downloads_heading' ) ? get_field( 'downloads_heading' ) : __( 'Downloads and certificates', 'cb-andwislifts2026' ) ); ?></h2>
						<span class="cb-resources__badge"><?php esc_html_e( 'Click to download', 'cb-andwislifts2026' ); ?></span>
					</div>
					<?php if ( get_field( 'downloads_intro' ) ) { ?>
					<p class="cb-resources__card-intro"><?= esc_html( get_field( 'downloads_intro' ) ); ?></p>
					<?php } ?>
					<?php
					$group_index = 0;

					foreach ( $doc_groups as $doc_group => $documents ) {
						$is_first = 0 === $group_index;

						// The groups after the first share a row, two across.
						if ( 1 === $group_index ) {
							echo '<div class="cb-resources__doc-columns">';
						}
						?>
					<div class="cb-resources__doc-group">
						<h3><?= esc_html( $doc_group ); ?> (<?= count( $documents ); ?>)</h3>
						<ul class="cb-resources__docs">
							<?php
							foreach ( $documents as $row_index => $document ) {
								$print_document( $document, $row_index >= $fold );
							}
							?>
						</ul>
						<?php if ( count( $documents ) > $fold ) { ?>
						<button type="button" class="cb-resources__more" data-cb-resources-more data-more="<?= esc_attr( sprintf( /* translators: %d: number of documents. */ __( 'Show all %d', 'cb-andwislifts2026' ), count( $documents ) ) ); ?>" data-less="<?php esc_attr_e( 'Show less', 'cb-andwislifts2026' ); ?>" aria-expanded="true" hidden>
							<span><?php esc_html_e( 'Show less', 'cb-andwislifts2026' ); ?></span>
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="m8.5 13.5 3.5-3.5 3.5 3.5"></path></svg>
						</button>
						<?php } ?>
					</div>
						<?php
						++$group_index;
					}

					if ( $group_index > 1 ) {
						echo '</div>';
					}
					?>
				</div>
				<?php } ?>

				<?php if ( $note ) { ?>
				<p class="cb-resources__note"><?= wp_kses_post( make_clickable( esc_html( $note ) ) ); ?></p>
				<?php } ?>
			</div>

			<?php if ( $side_cards ) { ?>
			<aside class="cb-resources__side">
				<?php foreach ( $side_cards as $card ) { ?>
				<div class="cb-resources__side-card<?= 'highlight' === ( $card['style'] ?? '' ) ? ' cb-resources__side-card--highlight' : ''; ?>">
					<?php if ( ! empty( $card['heading'] ) ) { ?>
					<h2><?= esc_html( $card['heading'] ); ?></h2>
					<?php } ?>
					<?php if ( ! empty( $card['text'] ) ) { ?>
					<p><?= esc_html( $card['text'] ); ?></p>
					<?php } ?>
					<?php if ( ! empty( $card['link']['url'] ) ) { ?>
					<a class="btn btn-primary" href="<?= esc_url( $card['link']['url'] ); ?>"<?= cb_link_target( $card['link'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?= esc_html( $card['link']['title'] ); ?></a>
					<?php } ?>
				</div>
				<?php } ?>
			</aside>
			<?php } ?>
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

	// Products: one chip per product, showing that product's files.
	var products = block.querySelector('[data-cb-resources-products]');
	var chips = products ? products.querySelectorAll('.cb-resources__chip') : [];

	if (chips.length) {
		var panels = products.querySelectorAll('.cb-resources__product');

		function choose(chip) {
			each(chips, function (other) {
				other.classList.toggle('is-active', other === chip);
				other.setAttribute('aria-pressed', other === chip ? 'true' : 'false');
			});
			each(panels, function (panel) {
				panel.hidden = panel.id !== chip.getAttribute('aria-controls');
			});
		}

		products.classList.add('is-enhanced');
		products.querySelector('.cb-resources__chips').hidden = false;
		each(chips, function (chip) {
			chip.addEventListener('click', function () { choose(chip); });
		});
		choose(chips[0]);
	}

	// Downloads: a group folds away after its first few rows.
	each(block.querySelectorAll('[data-cb-resources-more]'), function (more) {
		var extras = more.parentNode.querySelectorAll('[data-cb-resources-extra]');
		var label = more.querySelector('span');

		function fold(open) {
			each(extras, function (row) { row.hidden = !open; });
			more.setAttribute('aria-expanded', open ? 'true' : 'false');
			label.textContent = more.getAttribute(open ? 'data-less' : 'data-more');
		}

		more.hidden = false;
		more.addEventListener('click', function () {
			fold(more.getAttribute('aria-expanded') !== 'true');
		});
		fold(false);
	});

	// On a phone the first two cards start closed behind "Click to expand".
	var phone = window.matchMedia('(max-width: 767.98px)');
	var expanders = block.querySelectorAll('[data-cb-resources-expand]');

	function setCard(button, open) {
		var body = document.getElementById(button.getAttribute('aria-controls'));
		if (body) { body.hidden = !open; }
		button.setAttribute('aria-expanded', open ? 'true' : 'false');
		button.textContent = button.getAttribute(open ? 'data-close' : 'data-open');
	}

	function arrange() {
		each(expanders, function (button) {
			button.hidden = !phone.matches;
			setCard(button, !phone.matches);
		});
	}

	each(expanders, function (button) {
		button.addEventListener('click', function () {
			setCard(button, button.getAttribute('aria-expanded') !== 'true');
		});
	});

	if (expanders.length) {
		arrange();
		if (phone.addEventListener) { phone.addEventListener('change', arrange); }
	}

	// Each download is recorded by its title.
	block.addEventListener('click', function (event) {
		var link = event.target.closest ? event.target.closest('[data-cb-download]') : null;
		if (!link) return;

		window.dataLayer = window.dataLayer || [];
		window.dataLayer.push({
			event: 'resource_download',
			resource_title: link.getAttribute('data-cb-download'),
			resource_type: link.getAttribute('data-cb-download-type') || ''
		});
	});
});
</script>
		<?php
	},
	9999
);
