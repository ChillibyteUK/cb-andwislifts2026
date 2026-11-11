<?php
/**
 * Block template for CB Video.
 *
 * One video, optionally with a white card of text beside it, or several in a
 * row of two with a title and a line under each. Takes any oEmbed-supported
 * URL (YouTube, Vimeo) or an uploaded MP4.
 *
 * A video with a poster image is not loaded until its play button is
 * pressed: the picture stands in for it, which keeps the page light and
 * shows the mint play button the design draws.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

$section_id = $block['anchor'] ?? $block['id'] ?? wp_unique_id( 'cb-video-' );
$extra      = $block['className'] ?? '';
$heading    = get_field( 'heading' );
$intro      = get_field( 'intro' );
$aside      = get_field( 'aside' );
$background = 'green' === get_field( 'background' ) ? 'green' : 'paper';

// The list of videos, or failing that the single one from before the list
// existed, so blocks already on pages keep working.
$items = array();

foreach ( (array) get_field( 'videos' ) as $row ) {
	if ( is_array( $row ) && ( ! empty( $row['video_url'] ) || ! empty( $row['video_file']['url'] ) || ! empty( $row['poster']['ID'] ) ) ) {
		$items[] = $row;
	}
}

if ( ! $items && ( get_field( 'video_url' ) || get_field( 'video_file' ) || get_field( 'poster' ) ) ) {
	$items[] = array(
		'video_url'  => get_field( 'video_url' ),
		'video_file' => get_field( 'video_file' ),
		'poster'     => get_field( 'poster' ),
		'title'      => '',
		'text'       => get_field( 'caption' ),
	);
}

if ( ! $items && ! $heading ) {
	return;
}

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

$print_frame = static function ( $item ) use ( $allowed_embed ) {
	$embed  = ! empty( $item['video_url'] ) ? wp_oembed_get( $item['video_url'] ) : '';
	$poster = ! empty( $item['poster']['ID'] ) ? (int) $item['poster']['ID'] : 0;
	$title  = trim( (string) ( $item['title'] ?? '' ) );

	if ( $embed && $poster ) {
		$GLOBALS['cb_video_facade'] = true;
		?>
		<div class="cb-video__frame cb-video__frame--facade" data-cb-video>
			<?= wp_get_attachment_image( $poster, 'large', false, array( 'loading' => 'lazy' ) ); ?>
			<button type="button" class="cb-video__play">
				<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5.5v13l11-6.5z" fill="currentColor"/></svg>
				<span class="visually-hidden"><?= esc_html( $title ? sprintf( /* translators: %s: video title. */ __( 'Play video: %s', 'cb-andwislifts2026' ), $title ) : __( 'Play video', 'cb-andwislifts2026' ) ); ?></span>
			</button>
			<template><?= wp_kses( $embed, $allowed_embed ); ?></template>
		</div>
		<?php
	} elseif ( $embed ) {
		?>
		<div class="cb-video__frame"><?= wp_kses( $embed, $allowed_embed ); ?></div>
		<?php
	} elseif ( ! empty( $item['video_file']['url'] ) ) {
		?>
		<div class="cb-video__frame">
			<video controls playsinline preload="metadata"<?= $poster ? ' poster="' . esc_url( wp_get_attachment_image_url( $poster, 'full' ) ) . '"' : ''; ?>>
				<source src="<?= esc_url( $item['video_file']['url'] ); ?>" type="<?= esc_attr( $item['video_file']['mime_type'] ); ?>">
			</video>
		</div>
		<?php
	} elseif ( $poster ) {
		// No video supplied yet - show the poster so the section still reads.
		?>
		<div class="cb-video__frame cb-video__frame--poster"><?= wp_get_attachment_image( $poster, 'large' ); ?></div>
		<?php
	} elseif ( ! empty( $item['video_url'] ) ) {
		// An address that cannot be played (removed, private or mistyped). Hold
		// the space and say so, in place of leaving a hole in the layout.
		?>
		<div class="cb-video__frame cb-video__frame--pending"><span><?php esc_html_e( 'Video to follow', 'cb-andwislifts2026' ); ?></span></div>
		<?php
	}
};

$classes = array( 'cb-video', 'cb-video--' . $background );

if ( $aside && 1 === count( $items ) ) {
	$classes[] = 'cb-video--has-aside';
}

if ( $extra ) {
	$classes[] = $extra;
}
?>
<section class="<?= esc_attr( implode( ' ', $classes ) ); ?>" id="<?= esc_attr( $section_id ); ?>">
	<div class="container">
		<?php if ( $heading || $intro ) { ?>
		<div class="cb-video__head">
			<?php if ( $heading ) { ?>
			<h2><?= esc_html( $heading ); ?></h2>
			<?php } ?>
			<?php if ( $intro ) { ?>
			<p><?= esc_html( $intro ); ?></p>
			<?php } ?>
		</div>
		<?php } ?>
		<?php if ( 1 === count( $items ) ) { ?>
		<div class="cb-video__row">
			<div class="cb-video__main">
				<?php $print_frame( $items[0] ); ?>
				<?php if ( ! empty( $items[0]['text'] ) ) { ?>
				<p class="cb-video__caption"><?= esc_html( $items[0]['text'] ); ?></p>
				<?php } ?>
			</div>
			<?php if ( $aside ) { ?>
			<div class="cb-video__aside"><?= cb_highlight_body( wp_kses_post( $aside ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
			<?php } ?>
		</div>
		<?php } elseif ( $items ) { ?>
		<div class="cb-video__grid">
			<?php foreach ( $items as $item ) { ?>
			<div class="cb-video__item">
				<?php $print_frame( $item ); ?>
				<?php if ( ! empty( $item['title'] ) ) { ?>
				<h3><?= esc_html( $item['title'] ); ?></h3>
				<?php } ?>
				<?php if ( ! empty( $item['text'] ) ) { ?>
				<p><?= esc_html( $item['text'] ); ?></p>
				<?php } ?>
			</div>
			<?php } ?>
		</div>
		<?php } ?>
	</div>
</section>
