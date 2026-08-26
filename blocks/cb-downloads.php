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
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

$section_id = $block['anchor'] ?? $block['id'] ?? wp_unique_id( 'cb-downloads-' );
$extra      = $block['className'] ?? '';
$heading    = get_field( 'heading' );
$intro      = get_field( 'intro' );
$documents  = get_field( 'documents' );
$note       = get_field( 'note' );

$groups = array();

if ( $documents ) {
	foreach ( $documents as $document ) {
		if ( empty( $document['title'] ) ) {
			continue;
		}
		$group_name = trim( (string) ( $document['group'] ?? '' ) );
		if ( '' === $group_name ) {
			$group_name = __( 'Documents', 'cb-andwislifts2026' );
		}
		$groups[ $group_name ][] = $document;
	}
}
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
		foreach ( $groups as $group_name => $rows ) {
			?>
		<div class="cb-downloads__group">
			<h3><?= esc_html( $group_name ); ?></h3>
			<ul class="cb-downloads__list">
				<?php
				foreach ( $rows as $row ) {
					$file    = $row['file'] ?? null;
					$version = trim( (string) ( $row['version'] ?? '' ) );
					$review  = trim( (string) ( $row['review_date'] ?? '' ) );
					$meta    = array();

					if ( $version ) {
						$meta[] = 'Version ' . $version;
					}
					if ( $review ) {
						$meta[] = 'Reviewed ' . date_i18n( 'j M Y', strtotime( $review ) );
					}
					?>
				<li class="cb-downloads__item">
					<div class="cb-downloads__detail">
						<span class="cb-downloads__title"><?= esc_html( $row['title'] ); ?></span>
						<?php
						if ( $meta ) {
							?>
						<span class="cb-downloads__meta"><?= esc_html( implode( ' &middot; ', $meta ) ); ?></span>
							<?php
						}
						?>
					</div>
					<?php
					if ( ! empty( $file['url'] ) ) {
						$size = ! empty( $file['filesize'] ) ? ' (' . size_format( $file['filesize'] ) . ')' : '';
						?>
					<a class="cb-downloads__link" href="<?= esc_url( $file['url'] ); ?>" download>
						<?= esc_html__( 'Download', 'cb-andwislifts2026' ); ?><?= esc_html( $size ); ?>
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
		if ( $note ) {
			?>
		<p class="cb-downloads__note"><?= wp_kses_post( $note ); ?></p>
			<?php
		}
		?>
	</div>
</section>
