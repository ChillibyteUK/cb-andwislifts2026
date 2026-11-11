<?php
/**
 * What a case study and a news article share: the green hero with its
 * pictures, and the way the body is laid out.
 *
 * @package cb-andwislifts2026
 */

defined( 'ABSPATH' ) || exit;

/**
 * A post's pictures for the top of its page: the featured image, then any
 * added under More images.
 *
 * @param integer $post_id The post ID.
 * @return integer[] Attachment IDs, without repeats.
 */
function cb_story_images( $post_id ) {
	$ids = array_merge( array( get_post_thumbnail_id( $post_id ) ), (array) get_field( 'gallery', $post_id, false ) );

	return array_values( array_unique( array_filter( array_map( 'intval', $ids ) ) ) );
}

/**
 * Print the green hero: tags, an optional kicker, the title, an optional
 * intro beside it, and the pictures across its bottom edge.
 *
 * @param array $args {
 *     What to show.
 *
 *     @type string    $tags    Ready-made tags markup.
 *     @type string    $meta    A short line beside the tags, such as a date.
 *     @type string    $kicker  Small green line above the title.
 *     @type string    $title   The heading.
 *     @type string    $intro   A sentence or two beside the title.
 *     @type integer[] $images  Attachment IDs. More than one makes a carousel.
 *     @type string    $variant Extra class, "article" for the smaller title.
 * }
 * @return void
 */
function cb_story_hero( $args ) {
	$args = wp_parse_args(
		$args,
		array(
			'tags'    => '',
			'meta'    => '',
			'kicker'  => '',
			'title'   => '',
			'intro'   => '',
			'images'  => array(),
			'variant' => '',
		)
	);

	$classes = array( 'cb-case-hero' );

	if ( ! $args['images'] ) {
		$classes[] = 'cb-case-hero--no-media';
	}

	if ( ! $args['intro'] ) {
		$classes[] = 'cb-case-hero--no-intro';
	}

	if ( $args['variant'] ) {
		$classes[] = 'cb-case-hero--' . $args['variant'];
	}

	if ( count( $args['images'] ) > 1 ) {
		$GLOBALS['cb_story_gallery'] = true;
	}
	?>
	<section class="<?= esc_attr( implode( ' ', $classes ) ); ?>">
		<div class="container">
			<?php if ( $args['tags'] || $args['meta'] ) { ?>
			<div class="cb-case-hero__top">
				<?= $args['tags']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built and escaped by the caller. ?>
				<?php if ( $args['meta'] ) { ?>
				<span class="cb-case-hero__meta"><?= esc_html( $args['meta'] ); ?></span>
				<?php } ?>
			</div>
			<?php } ?>
			<div class="cb-case-hero__inner">
				<div class="cb-case-hero__title">
					<?php if ( $args['kicker'] ) { ?>
					<p class="cb-case-hero__kicker"><?= esc_html( $args['kicker'] ); ?></p>
					<?php } ?>
					<h1><?= esc_html( $args['title'] ); ?></h1>
				</div>
				<?php if ( $args['intro'] ) { ?>
				<p class="cb-case-hero__intro"><?= nl2br( esc_html( $args['intro'] ) ); ?></p>
				<?php } ?>
			</div>
			<?php if ( $args['images'] ) { ?>
			<div class="cb-case-hero__media" data-cb-case-gallery>
				<div class="swiper cb-case-gallery">
					<div class="swiper-wrapper">
						<?php foreach ( $args['images'] as $index => $image_id ) { ?>
						<div class="swiper-slide">
							<?=
							wp_get_attachment_image(
								$image_id,
								'full',
								false,
								array(
									'loading' => $index ? 'lazy' : 'eager',
									'sizes'   => '(min-width: 1245px) 1209px, 100vw',
								)
							);
							?>
						</div>
						<?php } ?>
					</div>
				</div>
				<?php if ( count( $args['images'] ) > 1 ) { ?>
				<div class="cb-case-gallery__dots"></div>
				<?php } ?>
			</div>
			<?php } ?>
		</div>
	</section>
	<?php
}

/**
 * The hero's pictures only become a carousel when there is more than one.
 *
 * @return void
 */
function cb_story_gallery_script() {
	if ( empty( $GLOBALS['cb_story_gallery'] ) ) {
		return;
	}
	?>
<script>
document.addEventListener('DOMContentLoaded', function () {
	var root = document.querySelector('[data-cb-case-gallery]');
	if (!root || typeof Swiper === 'undefined') return;

	new Swiper(root.querySelector('.swiper'), {
		loop: true,
		speed: 600,
		spaceBetween: 24,
		grabCursor: true,
		keyboard: { enabled: true },
		pagination: { el: root.querySelector('.cb-case-gallery__dots'), clickable: true }
	});
});
</script>
	<?php
}
add_action( 'wp_footer', 'cb_story_gallery_script', 9999 );

/**
 * Print a post's body.
 *
 * The theme's own sections (CB Text Media and the rest) run the full width
 * and bring their own container. Anything else - paragraphs, headings, lists -
 * is gathered into a text column, and a picture on its own is given the full
 * content width. So an author can mix plain writing with sections freely.
 *
 * @param WP_Post|null $post The post. Defaults to the current one.
 * @return void
 */
function cb_story_content( $post = null ) {
	$post = get_post( $post );

	if ( ! $post || '' === trim( $post->post_content ) ) {
		return;
	}

	$runs = array();

	foreach ( parse_blocks( $post->post_content ) as $block ) {
		$markup = serialize_block( $block );

		if ( '' === trim( $markup ) ) {
			continue;
		}

		$name = (string) $block['blockName'];
		$type = 0 === strpos( $name, 'acf/' ) ? 'section' : ( 'core/image' === $name ? 'figure' : 'prose' );
		$last = count( $runs ) - 1;

		// Neighbouring pieces of plain writing share one column.
		if ( 'prose' === $type && $last >= 0 && 'prose' === $runs[ $last ]['type'] ) {
			$runs[ $last ]['markup'] .= $markup;
			continue;
		}

		$runs[] = array(
			'type'   => $type,
			'markup' => $markup,
		);
	}

	echo '<div class="cb-story">';

	foreach ( $runs as $run ) {
		// Rendered block output, already filtered by WordPress. Do NOT pass this
		// through wp_kses_post(): it strips <script> tags but keeps the script body.
		$html = apply_filters( 'the_content', $run['markup'] ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

		if ( 'section' === $run['type'] ) {
			echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			continue;
		}

		// The inner div holds the width: the theme wraps each paragraph, heading
		// and list in a container of its own, which would otherwise centre it.
		printf( '<div class="container cb-story__%s"><div class="cb-story__inner">%s</div></div>', esc_attr( $run['type'] ), $html ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	echo '</div>';
}

/**
 * Print the mint closing band.
 *
 * @param string $heading The heading.
 * @param string $body    The line under it.
 * @param string $label   The button's words.
 * @param string $path    Where it goes, as a path on this site.
 * @return void
 */
function cb_story_closing_band( $heading, $body, $label, $path ) {
	echo do_blocks( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- rendered block markup.
		'<!-- wp:acf/cb-cta ' . wp_json_encode(
			array(
				'name' => 'acf/cb-cta',
				'data' => array(
					'heading'  => $heading,
					'_heading' => 'field_cb_cta_heading',
					'body'     => $body,
					'_body'    => 'field_cb_cta_body',
					'cta'      => array(
						'title'  => $label,
						'url'    => home_url( $path ),
						'target' => '',
					),
					'_cta'     => 'field_cb_cta_link',
					'style'    => 'mint',
					'_style'   => 'field_cb_cta_style',
					'layout'   => 'centred',
					'_layout'  => 'field_cb_cta_layout',
				),
				'mode' => 'preview',
			),
			JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_APOS
		) . ' /-->'
	);
}
