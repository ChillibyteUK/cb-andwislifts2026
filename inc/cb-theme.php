<?php
/**
 * LC Theme Functions
 *
 * This file contains theme-specific functions and customizations for the LC Harrier 2025 theme.
 *
 * @package cb-andwislifts2026
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

require_once CB_THEME_DIR . '/inc/cb-utility.php';
require_once CB_THEME_DIR . '/inc/cb-acf-theme-palette.php';
// Disabled until custom post types are needed: require_once CB_THEME_DIR . '/inc/cb-posttypes.php'.
// Disabled until custom taxonomies are needed: require_once CB_THEME_DIR . '/inc/cb-taxonomies.php'.

require_once CB_THEME_DIR . '/inc/cb-blocks.php';

/**
 * Editor styles: opt-in so WP loads editor.css in the block editor.
 * With theme.json present, this just adds your custom CSS on top (variables, helpers).
 */
add_action(
    'after_setup_theme',
    function () {
        add_theme_support( 'editor-styles' );
        add_editor_style( 'css/custom-editor-style.min.css' );
        add_theme_support( 'align-wide' );
    },
    5
);

/**
 * Neutralise legacy palette/font-size support (if parent/Understrap adds them).
 * theme.json is authoritative, but some themes still register supports in PHP.
 * Remove them AFTER the parent has added them (high priority).
 */
add_action(
    'after_setup_theme',
    function () {
        remove_theme_support( 'editor-color-palette' );
        remove_theme_support( 'editor-gradient-presets' );
        remove_theme_support( 'editor-font-sizes' );
    },
    99
);

/**
 * (Optional) Ensure custom colours *aren’t* forcibly disabled by parent.
 * If Understrap disables custom colours, this re-enables them so theme.json works fully.
 */
add_filter( 'should_load_separate_core_block_assets', '__return_true' ); // performance nicety.

/**
 * Removes specific page templates from the available templates list.
 *
 * @param array $page_templates The list of page templates.
 * @return array The modified list of page templates.
 */
function child_theme_remove_page_template( $page_templates ) {
    unset(
        $page_templates['page-templates/blank.php'],
        $page_templates['page-templates/empty.php'],
        $page_templates['page-templates/left-sidebarpage.php'],
        $page_templates['page-templates/right-sidebarpage.php'],
        $page_templates['page-templates/both-sidebarspage.php']
    );
    return $page_templates;
}
add_filter( 'theme_page_templates', 'child_theme_remove_page_template' );

/**
 * Removes support for specific post formats in the theme.
 */
function remove_understrap_post_formats() {
    remove_theme_support( 'post-formats', array( 'aside', 'image', 'video', 'quote', 'link' ) );
}
add_action( 'after_setup_theme', 'remove_understrap_post_formats', 11 );


if ( function_exists( 'acf_add_options_page' ) ) {
    acf_add_options_page(
        array(
            'page_title' => 'Site-Wide Settings',
            'menu_title' => 'Site-Wide Settings',
            'menu_slug'  => 'theme-general-settings',
            'capability' => 'edit_posts',
        )
    );
}

/**
 * Initializes widgets, menus, and theme supports.
 *
 * This function registers navigation menus, unregisters sidebars and menus,
 * and adds theme support for custom editor color palettes.
 */
function widgets_init() {

    register_nav_menus(
		array(
			'primary_nav' => 'Primary Nav',
			'footer_menu' => 'Footer Nav',
		)
	);

    unregister_sidebar( 'hero' );
    unregister_sidebar( 'herocanvas' );
    unregister_sidebar( 'statichero' );
    unregister_sidebar( 'left-sidebar' );
    unregister_sidebar( 'right-sidebar' );
    unregister_sidebar( 'footerfull' );
    unregister_nav_menu( 'primary' );

    add_theme_support( 'disable-custom-colors' );
}
add_action( 'widgets_init', 'widgets_init', 11 );

/**
 * Enqueues theme-specific scripts and styles.
 *
 * This function deregisters jQuery and disables certain styles and scripts
 * that are commented out for potential use in the theme.
 */
function hub_theme_enqueue() {
    $the_theme = wp_get_theme();

	// phpcs:disable
    // wp_enqueue_script('lightbox-scripts', get_stylesheet_directory_uri() . '/js/lightbox-plus-jquery.min.js', array(), $the_theme->get('Version'), true);
    // wp_enqueue_script('parallax', get_stylesheet_directory_uri() . '/js/parallax.min.js', array('jquery'), null, true);
    // wp_enqueue_style( 'splide-stylesheet', 'https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.3/dist/css/splide.min.css', array(), null );
    // wp_enqueue_script( 'splide-scripts', 'https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.3/dist/js/splide.min.js', array(), null, true );
    // wp_enqueue_style('lightbox-stylesheet', get_stylesheet_directory_uri() . '/css/lightbox.min.css', array(), $the_theme->get('Version'));
    // wp_enqueue_script('lightbox-scripts', get_stylesheet_directory_uri() . '/js/lightbox.min.js', array(), $the_theme->get('Version'), true);
    // wp_enqueue_style( 'glightbox-style', 'https://cdnjs.cloudflare.com/ajax/libs/glightbox/3.3.1/css/glightbox.min.css', array(), $the_theme->get( 'Version' ) );
    // wp_enqueue_script( 'glightbox', 'https://cdnjs.cloudflare.com/ajax/libs/glightbox/3.3.1/js/glightbox.min.js', array(), $the_theme->get( 'Version' ), true );
	// wp_enqueue_style( 'tom-select', 'https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.min.css', array(), '2.3.1' );
    // wp_enqueue_script( 'tom-select', 'https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js', array(), '2.3.1', true );
    // wp_enqueue_style( 'aos-style', 'https://unpkg.com/aos@2.3.1/dist/aos.css', array() ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
    // wp_enqueue_script( 'aos', 'https://unpkg.com/aos@2.3.1/dist/aos.js', array(), null, true ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
    // wp_deregister_script( 'jquery' ); // needed by gravity forms
    // phpcs:enable

    wp_enqueue_style( 'swiper', 'https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css', array(), null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
    wp_enqueue_script( 'swiper', 'https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js', array(), null, true ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
	wp_enqueue_style( 'flickity', 'https://cdn.jsdelivr.net/npm/flickity@3/dist/flickity.min.css', array(), '3.0.0' );
	wp_enqueue_script( 'flickity', 'https://cdn.jsdelivr.net/npm/flickity@3/dist/flickity.pkgd.min.js', array(), '3.0.0', true );
	wp_enqueue_script( 'lenis', 'https://unpkg.com/lenis@1.3.11/dist/lenis.min.js', array(), null, true ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
	wp_enqueue_style( 'lenis-style', 'https://unpkg.com/lenis@1.3.11/dist/lenis.css', array() ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
	wp_enqueue_script( 'gsap', 'https://cdn.jsdelivr.net/npm/gsap@3.12.7/dist/gsap.min.js', array(), '3.12.7', true );
    wp_enqueue_script( 'gsap-scrolltrigger', 'https://cdn.jsdelivr.net/npm/gsap@3.12.7/dist/ScrollTrigger.min.js', array( 'gsap' ), '3.12.7', true );
}
add_action( 'wp_enqueue_scripts', 'hub_theme_enqueue' );

// Performance: Remove WordPress global styles and SVG filters (WP 6.0+).
// This prevents FOUC by removing unnecessary inline styles in the head.
remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters' );


add_action(
    'admin_head',
    function () {
        echo '<style>
        .block-editor-page #wpwrap {
        overflow-y: auto !important;
        }
   </style>';
    }
);

add_action(
	'wp_footer',
	function () {
		?>
<script>
document.addEventListener('DOMContentLoaded', function () {
	if (typeof Lenis === 'undefined') return;
	const lenis = new Lenis({
		smooth: true,
		lerp: 0.1
	});
	window.lenis = lenis;
	function raf(time) {
		lenis.raf(time);
		requestAnimationFrame(raf);
	}
	requestAnimationFrame(raf);
});
</script>
		<?php
	}
);


/**
 * Add defer attribute to scripts for better performance.
 *
 * @param string $tag    The script tag HTML.
 * @param string $handle The script handle.
 * @return string Modified script tag.
 */
function add_defer_to_scripts( $tag, $handle ) {
    // Defer child theme scripts.
    if ( 'child-theme-scripts' === $handle ) {
        return str_replace( ' src', ' defer src', $tag );
    }

	// phpcs:disable
    // Defer jQuery when it's loaded (for Gravity Forms pages).
    // Note: This may break some plugins if they expect jQuery immediately.
    // if ( in_array( $handle, array( 'jquery-core', 'jquery-migrate' ), true ) ) {
    //     return str_replace( ' src', ' defer src', $tag );
    // }
	// phpcs:enable

    return $tag;
}
add_filter( 'script_loader_tag', 'add_defer_to_scripts', 10, 2 );

/**
 * Remove id attributes from nav menu links to prevent duplicate IDs.
 * This is necessary when using the same menu in both desktop and mobile (offcanvas) views.
 *
 * @param array $atts HTML attributes applied to the anchor element.
 * @return array Modified attributes without id.
 */
function remove_nav_menu_item_id( $atts ) {
    unset( $atts['id'] );
    return $atts;
}
add_filter( 'nav_menu_link_attributes', 'remove_nav_menu_item_id' );



add_filter(
	'gform_submit_button',
	function ( $button, $form ) {
    	return '<button class="gform_button btn btn-primary w-100 justify-content-center" id="gform_submit_button_' . $form['id'] . '">' .
			esc_html( $form['button']['text'] ) .
			'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"></path></svg></button>';
	},
	10,
	2
);


add_action(
	'after_setup_theme',
	function () {
		remove_filter( 'tiny_mce_before_init', 'understrap_tiny_mce_before_init' );
	},
	20
);

add_filter( 'tiny_mce_before_init', 'cb_tiny_mce_before_init', 20 );

/**
 * Limit TinyMCE style formats to child theme options.
 *
 * @param array $settings TinyMCE settings array.
 * @return array
 */
function cb_tiny_mce_before_init( $settings ) {
	$settings['style_formats']       = wp_json_encode(
		array(
			array(
				'title'   => 'Standfirst',
				'block'   => 'p',
				'classes' => 'font-lede',
			),
		)
	);
	$settings['style_formats_merge'] = false;

	return $settings;
}
