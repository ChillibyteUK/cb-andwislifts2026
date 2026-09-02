<?php
/**
 * Block template for CB Downloads.
 *
 * A flat repeater of documents that the template groups by the row's Group
 * field, preserving first-appearance order. Flat rather than nested repeaters so
 * editors can reorder and regroup a document without rebuilding the structure.
 *
 * Rows without a file still render, marked as available on request - the brief's
 * document set is maintained on a review cycle and files arrive over time.
 *
 * With filters switched on the block gains a search box and two selects, topic
 * and file type. Filtering is client-side: every document is already in the DOM,
 * which keeps it instant and leaves the page working with no JavaScript.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

$section_id  = $block['anchor'] ?? $block['id'] ?? wp_unique_id( 'cb-downloads-' );
$extra       = $block['className'] ?? '';
$heading     = get_field( 'heading' );
$intro       = get_field( 'intro' );
$documents   = get_field( 'documents' );
$note        = get_field( 'note' );
$has_filters = null === get_field( 'show_filters' ) ? true : (bool) get_field( 'show_filters' );

$groups = array();
$types  = array();
$total  = 0;

if ( $documents ) {
	foreach ( $documents as $document ) {
		if ( empty( $document['title'] ) ) {
			continue;
		}

		$group_name = trim( (string) ( $document['group'] ?? '' ) );

		if ( '' === $group_name ) {
			$group_name = __( 'Documents', 'cb-andwislifts2026' );
		}

		// File type drives both the badge and the second filter. Taken from the
		// filename rather than the mime type, which is what a reader recognises.
		$file      = $document['file'] ?? null;
		$file_name = $file['filename'] ?? ( $file['url'] ?? '' );
		$extension = $file_name ? strtoupper( pathinfo( wp_parse_url( $file_name, PHP_URL_PATH ), PATHINFO_EXTENSION ) ) : '';

		if ( $extension ) {
			$types[ $extension ] = $extension;
		}

		$document['cb_group']     = $group_name;
		$document['cb_extension'] = $extension;

		$groups[ $group_name ][] = $document;
		++$total;
	}
}

ksort( $types );
$group_names = array_keys( $groups );
?>
<section class="cb-downloads <?= esc_attr( $extra ); ?>" id="<?= esc_attr( $section_id ); ?>">
	<div class="container">
		<div class="cb-section-head pb-5">
			<?php
			if ( $heading ) {
				?>
			<h2><?= esc_html( $heading ); ?></h2>
				<?php
			}
			if ( $intro ) {
				?>
			<p><?= esc_html( $intro ); ?></p>
				<?php
			}
			?>
		</div>
		<?php
		if ( $has_filters && $total ) {
			$search_id = $section_id . '-search';
			$group_id  = $section_id . '-group';
			$type_id   = $section_id . '-type';
			?>
		<div class="cb-downloads__filters">
			<div class="cb-downloads__field cb-downloads__field--search">
				<label for="<?= esc_attr( $search_id ); ?>"><?= esc_html__( 'Search', 'cb-andwislifts2026' ); ?></label>
				<input type="search" id="<?= esc_attr( $search_id ); ?>" data-cb-downloads-search placeholder="<?= esc_attr__( 'Search documents', 'cb-andwislifts2026' ); ?>" autocomplete="off">
			</div>
			<?php if ( count( $group_names ) > 1 ) { ?>
			<div class="cb-downloads__field">
				<label for="<?= esc_attr( $group_id ); ?>"><?= esc_html__( 'Topic', 'cb-andwislifts2026' ); ?></label>
				<select id="<?= esc_attr( $group_id ); ?>" data-cb-downloads-group>
					<option value=""><?= esc_html__( 'All topics', 'cb-andwislifts2026' ); ?></option>
					<?php foreach ( $group_names as $group_name ) { ?>
					<option value="<?= esc_attr( $group_name ); ?>"><?= esc_html( $group_name ); ?></option>
					<?php } ?>
				</select>
			</div>
			<?php } ?>
			<?php if ( count( $types ) > 1 ) { ?>
			<div class="cb-downloads__field">
				<label for="<?= esc_attr( $type_id ); ?>"><?= esc_html__( 'File type', 'cb-andwislifts2026' ); ?></label>
				<select id="<?= esc_attr( $type_id ); ?>" data-cb-downloads-type>
					<option value=""><?= esc_html__( 'All file types', 'cb-andwislifts2026' ); ?></option>
					<?php foreach ( $types as $type ) { ?>
					<option value="<?= esc_attr( $type ); ?>"><?= esc_html( $type ); ?></option>
					<?php } ?>
				</select>
			</div>
			<?php } ?>
			<button type="button" class="cb-downloads__clear" data-cb-downloads-clear hidden><?= esc_html__( 'Clear', 'cb-andwislifts2026' ); ?></button>
		</div>
		<p class="cb-downloads__count" data-cb-downloads-count role="status" aria-live="polite"></p>
			<?php
		}

		foreach ( $groups as $group_name => $rows ) {
			?>
		<div class="cb-downloads__group" data-cb-downloads-group-name="<?= esc_attr( $group_name ); ?>">
			<h3><?= esc_html( $group_name ); ?></h3>
			<ul class="cb-downloads__list">
				<?php
				foreach ( $rows as $row ) {
					$file    = $row['file'] ?? null;
					$version = trim( (string) ( $row['version'] ?? '' ) );
					$review  = trim( (string) ( $row['review_date'] ?? '' ) );
					$meta    = array();

					if ( $row['cb_extension'] ) {
						$meta[] = $row['cb_extension'];
					}
					if ( ! empty( $file['filesize'] ) ) {
						$meta[] = size_format( $file['filesize'] );
					}
					if ( $version ) {
						$meta[] = 'Version ' . $version;
					}
					if ( $review ) {
						$meta[] = 'Reviewed ' . date_i18n( 'j M Y', strtotime( $review ) );
					}
					?>
				<li class="cb-downloads__item" data-cb-downloads-item data-type="<?= esc_attr( $row['cb_extension'] ); ?>">
					<span class="cb-downloads__icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"></path><path d="M14 3v5h5"></path></svg>
						<?php if ( $row['cb_extension'] ) { ?>
						<span class="cb-downloads__ext"><?= esc_html( $row['cb_extension'] ); ?></span>
						<?php } ?>
					</span>
					<div class="cb-downloads__detail">
						<span class="cb-downloads__title"><?= esc_html( $row['title'] ); ?></span>
						<?php
						if ( $meta ) {
							?>
						<span class="cb-downloads__meta"><?= esc_html( implode( ' · ', $meta ) ); ?></span>
							<?php
						}
						?>
					</div>
					<?php
					if ( ! empty( $file['url'] ) ) {
						?>
					<a class="cb-downloads__link" href="<?= esc_url( $file['url'] ); ?>" download>
						<span><?= esc_html__( 'Download', 'cb-andwislifts2026' ); ?></span>
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v12M7 11l5 5 5-5M5 21h14"></path></svg>
					</a>
						<?php
					} else {
						?>
					<span class="cb-downloads__pending"><?= esc_html__( 'Available on request', 'cb-andwislifts2026' ); ?></span>
						<?php
					}
					?>
				</li>
					<?php
				}
				?>
			</ul>
		</div>
			<?php
		}

		if ( $has_filters && $total ) {
			?>
		<p class="cb-downloads__empty" data-cb-downloads-empty hidden><?= esc_html__( 'No documents match that search. Try a different term or clear the filters.', 'cb-andwislifts2026' ); ?></p>
			<?php
		}

		if ( $note ) {
			?>
		<p class="cb-downloads__note"><?= wp_kses_post( $note ); ?></p>
			<?php
		}
		?>
	</div>
</section>

<?php
// Emitted on wp_footer rather than inline. An inline <script> inside an ACF
// block's output breaks its preview in the editor - the other blocks in this
// theme already use this pattern.
if ( $has_filters && $total ) {
	add_action(
		'wp_footer',
		function () use ( $section_id, $total ) {
			?>
<script>
document.addEventListener('DOMContentLoaded', function () {
	var block = document.getElementById(<?= wp_json_encode( $section_id ); ?>);
	if (!block) return;

	var search = block.querySelector('[data-cb-downloads-search]');
	var group  = block.querySelector('[data-cb-downloads-group]');
	var type   = block.querySelector('[data-cb-downloads-type]');
	var clear  = block.querySelector('[data-cb-downloads-clear]');
	var count  = block.querySelector('[data-cb-downloads-count]');
	var empty  = block.querySelector('[data-cb-downloads-empty]');
	var groups = Array.prototype.slice.call(block.querySelectorAll('[data-cb-downloads-group-name]'));
	var total  = <?= (int) $total; ?>;

	// Cache the searchable text once rather than reading the DOM on every keystroke.
	var items = Array.prototype.slice.call(block.querySelectorAll('[data-cb-downloads-item]')).map(function (el) {
		return {
			el: el,
			text: (el.textContent || '').toLowerCase(),
			type: el.getAttribute('data-type') || '',
			group: el.closest('[data-cb-downloads-group-name]')
		};
	});

	function apply() {
		var term = (search && search.value || '').trim().toLowerCase();
		var wantGroup = group && group.value || '';
		var wantType = type && type.value || '';
		var shown = 0;

		items.forEach(function (item) {
			var groupName = item.group ? item.group.getAttribute('data-cb-downloads-group-name') : '';
			var match = (!term || item.text.indexOf(term) !== -1) &&
				(!wantGroup || groupName === wantGroup) &&
				(!wantType || item.type === wantType);

			item.el.hidden = !match;
			if (match) shown++;
		});

		// A heading with nothing under it reads as an error, so hide the group too.
		groups.forEach(function (el) {
			el.hidden = !el.querySelector('[data-cb-downloads-item]:not([hidden])');
		});

		var filtering = !!(term || wantGroup || wantType);
		if (clear) clear.hidden = !filtering;
		if (empty) empty.hidden = shown !== 0;
		if (count) {
			count.textContent = filtering
				? shown + ' of ' + total + ' documents'
				: '';
		}
	}

	var timer;
	function debounced() {
		window.clearTimeout(timer);
		timer = window.setTimeout(apply, 120);
	}

	if (search) search.addEventListener('input', debounced);
	if (group) group.addEventListener('change', apply);
	if (type) type.addEventListener('change', apply);
	if (clear) {
		clear.addEventListener('click', function () {
			if (search) search.value = '';
			if (group) group.value = '';
			if (type) type.value = '';
			apply();
			if (search) search.focus();
		});
	}

	apply();
});
</script>
			<?php
		},
		9999
	);
}
