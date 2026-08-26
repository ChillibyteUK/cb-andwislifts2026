<?php
/**
 * Block template for CB Video.
 *
 * Neither this theme nor cb-turnpower2025 had a reusable video block - Turnpower's
 * home hero hardcodes a <video> src - so this is new. Takes any oEmbed-supported
 * URL (YouTube, Vimeo) or an uploaded MP4 and renders it in a 16:9 wrapper.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

$section_id = $block['anchor'] ?? $block['id'] ?? wp_unique_id( 'cb-video-' );
$extra      = $block['className'] ?? '';
$heading    = get_field( 'heading' );
$intro      = get_field( 'intro' );
$video_url  = get_field( 'video_url' );
$video_file = get_field( 'video_file' );
$poster     = get_field( 'poster' );
$caption    = get_field( 'caption' );

$embed = $video_url ? wp_oembed_get( $video_url ) : '';

// wp_kses_post() strips <iframe> outright - it is not in $allowedposttags - which
// would silently blank the embed. oEmbed markup comes from WordPress's own
// whitelisted provider list, so allow the iframe explicitly instead.
$allowed_embed = array(
	'iframe' => array(
		'src'             => true,
		'width'           => true,
		'height'          => true,
		'title'           => true,
		'frameborder'     => true,
		'allow'           => true,
		'allowfullscreen' => true,
		'loading'         => true,
		'referrerpolicy'  => true,
		'class'           => true,
		'style'           => true,
	),
);
?>
<section class="cb-video <?= esc_attr( $extra ); ?>" id="<?= esc_attr( $section_id ); ?>">
	<div class="container">
		<div class="cb-section-head pb-5 cb-gsap-fade">
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
		if ( $embed ) {
			?>
		<div class="cb-video__frame"><?= wp_kses( $embed, $allowed_embed ); ?></div>
			<?php
		} elseif ( ! empty( $video_file['url'] ) ) {
			?>
		<div class="cb-video__frame">
			<video controls playsinline<?= ! empty( $poster['ID'] ) ? ' poster="' . esc_url( wp_get_attachment_image_url( $poster['ID'], 'full' ) ) . '"' : ''; ?>>
				<source src="<?= esc_url( $video_file['url'] ); ?>" type="<?= esc_attr( $video_file['mime_type'] ); ?>">
			</video>
		</div>
			<?php
		} elseif ( ! empty( $poster['ID'] ) ) {
			// No video supplied yet - show the poster so the section still reads.
			?>
		<div class="cb-video__frame cb-video__frame--poster"><?= wp_get_attachment_image( $poster['ID'], 'large' ); ?></div>
			<?php
		}
		if ( $caption ) {
			?>
		<p class="cb-video__caption"><?= esc_html( $caption ); ?></p>
			<?php
		}
		?>
	</div>
</section>
