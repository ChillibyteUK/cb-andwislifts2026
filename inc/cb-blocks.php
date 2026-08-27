<?php
/**
 * File responsible for registering custom ACF blocks and modifying core block arguments.
 *
 * @package cb-andwislifts2026
 */

/**
 * Registers custom ACF blocks.
 *
 * This function checks if the ACF plugin is active and registers custom blocks
 * for use in the WordPress block editor. Each block has its own name, title,
 * category, icon, render template, and supports various features.
 */
function acf_blocks() {
    if ( function_exists( 'acf_register_block_type' ) ) {

		// INSERT NEW BLOCKS HERE.

		acf_register_block_type(
			array(
				'name'            => 'cb_cta',
				'title'           => __( 'CB CTA' ),
				'category'        => 'layout',
				'icon'            => 'megaphone',
				'render_template' => 'blocks/cb-cta.php',
				'api_version'     => 3,
				'supports'        => array(
					'anchor'    => true,
					'className' => true,
					'align'     => true,

				),
			)
		);

		acf_register_block_type(
			array(
				'name'            => 'cb_news_index',
				'title'           => __( 'CB News Index' ),
				'category'        => 'layout',
				'icon'            => 'admin-post',
				'render_template' => 'blocks/cb-news-index.php',
				'api_version'     => 3,
				'supports'        => array(
					'anchor'    => true,
					'className' => true,
					'align'     => true,

				),
			)
		);

		acf_register_block_type(
			array(
				'name'            => 'cb_emergency',
				'title'           => __( 'CB Emergency' ),
				'category'        => 'layout',
				'icon'            => 'phone',
				'render_template' => 'blocks/cb-emergency.php',
				'api_version'     => 3,
				'supports'        => array(
					'anchor'    => true,
					'className' => true,
					'align'     => true,

				),
			)
		);

		acf_register_block_type(
			array(
				'name'            => 'cb_case_study_index',
				'title'           => __( 'CB Case Study Index' ),
				'category'        => 'layout',
				'icon'            => 'portfolio',
				'render_template' => 'blocks/cb-case-study-index.php',
				'api_version'     => 3,
				'supports'        => array(
					'anchor'    => true,
					'className' => true,
					'align'     => true,

				),
			)
		);

		acf_register_block_type(
			array(
				'name'            => 'cb_downloads',
				'title'           => __( 'CB Downloads' ),
				'category'        => 'layout',
				'icon'            => 'download',
				'render_template' => 'blocks/cb-downloads.php',
				'api_version'     => 3,
				'supports'        => array(
					'anchor'    => true,
					'className' => true,
					'align'     => true,

				),
			)
		);

		acf_register_block_type(
			array(
				'name'            => 'cb_faqs',
				'title'           => __( 'CB FAQs' ),
				'category'        => 'layout',
				'icon'            => 'editor-help',
				'render_template' => 'blocks/cb-faqs.php',
				'api_version'     => 3,
				'supports'        => array(
					'anchor'    => true,
					'className' => true,
					'align'     => true,

				),
			)
		);

		acf_register_block_type(
			array(
				'name'            => 'cb_video',
				'title'           => __( 'CB Video' ),
				'category'        => 'layout',
				'icon'            => 'video-alt3',
				'render_template' => 'blocks/cb-video.php',
				'api_version'     => 3,
				'supports'        => array(
					'anchor'    => true,
					'className' => true,
					'align'     => true,

				),
			)
		);

		acf_register_block_type(
			array(
				'name'            => 'cb_vacancy_index',
				'title'           => __( 'CB Vacancy Index' ),
				'category'        => 'layout',
				'icon'            => 'groups',
				'render_template' => 'blocks/cb-vacancy-index.php',
				'api_version'     => 3,
				'supports'        => array(
					'anchor'    => true,
					'className' => true,
					'align'     => true,

				),
			)
		);

		acf_register_block_type(
			array(
				'name'            => 'cb_form',
				'title'           => __( 'CB Form' ),
				'category'        => 'layout',
				'icon'            => 'feedback',
				'render_template' => 'blocks/cb-form.php',
				'api_version'     => 3,
				'supports'        => array(
					'anchor'    => true,
					'className' => true,
					'align'     => true,

				),
			)
		);

		acf_register_block_type(
			array(
				'name'            => 'cb_pill_strip',
				'title'           => __( 'CB Pill Strip' ),
				'category'        => 'layout',
				'icon'            => 'cover-image',
				'render_template' => 'blocks/cb-pill-strip.php',
				'api_version'     => 3,
				'supports'        => array(
					'anchor'    => true,
					'className' => true,
					'align'     => true,

				),
			)
		);

		acf_register_block_type(
			array(
				'name'            => 'cb_contact_cards',
				'title'           => __( 'CB Contact Cards' ),
				'category'        => 'layout',
				'icon'            => 'cover-image',
				'render_template' => 'blocks/cb-contact-cards.php',
				'api_version'     => 3,
				'supports'        => array(
					'anchor'    => true,
					'className' => true,
					'align'     => true,

				),
			)
		);

		acf_register_block_type(
			array(
				'name'            => 'cb_customer_grid',
				'title'           => __( 'CB Customer Grid' ),
				'category'        => 'layout',
				'icon'            => 'cover-image',
				'render_template' => 'blocks/cb-customer-grid.php',
				'api_version'     => 3,
				'supports'        => array(
					'anchor'    => true,
					'className' => true,
					'align'     => true,

				),
			)
		);

		acf_register_block_type(
			array(
				'name'            => 'cb_sectors',
				'title'           => __( 'CB Sectors' ),
				'category'        => 'layout',
				'icon'            => 'cover-image',
				'render_template' => 'blocks/cb-sectors.php',
				'api_version'     => 3,
				'supports'        => array(
					'anchor'    => true,
					'className' => true,
					'align'     => true,

				),
			)
		);

		acf_register_block_type(
			array(
				'name'            => 'cb_stats',
				'title'           => __( 'CB Stats' ),
				'category'        => 'layout',
				'icon'            => 'cover-image',
				'render_template' => 'blocks/cb-stats.php',
				'api_version'     => 3,
				'supports'        => array(
					'anchor'    => true,
					'className' => true,
					'align'     => true,

				),
			)
		);

		acf_register_block_type(
			array(
				'name'            => 'cb_logo_flow',
				'title'           => __( 'CB Logo Flow' ),
				'category'        => 'layout',
				'icon'            => 'cover-image',
				'render_template' => 'blocks/cb-logo-flow.php',
				'api_version'     => 3,
				'supports'        => array(
					'anchor'    => true,
					'className' => true,
					'align'     => true,

				),
			)
		);

		acf_register_block_type(
			array(
				'name'            => 'cb_compliance',
				'title'           => __( 'CB Compliance' ),
				'category'        => 'layout',
				'icon'            => 'cover-image',
				'render_template' => 'blocks/cb-compliance.php',
				'api_version'     => 3,
				'supports'        => array(
					'anchor'    => true,
					'className' => true,
					'align'     => true,

				),
			)
		);

		acf_register_block_type(
			array(
				'name'            => 'cb_feature_accordion',
				'title'           => __( 'CB Feature Accordion' ),
				'category'        => 'layout',
				'icon'            => 'cover-image',
				'render_template' => 'blocks/cb-feature-accordion.php',
				'api_version'     => 3,
				'supports'        => array(
					'anchor'    => true,
					'className' => true,
					'align'     => true,

				),
			)
		);

		acf_register_block_type(
			array(
				'name'            => 'cb_service_cards',
				'title'           => __( 'CB Service Cards' ),
				'category'        => 'layout',
				'icon'            => 'cover-image',
				'render_template' => 'blocks/cb-service-cards.php',
				'api_version'     => 3,
				'supports'        => array(
					'anchor'    => true,
					'className' => true,
					'align'     => true,

				),
			)
		);

		acf_register_block_type(
			array(
				'name'            => 'cb_image_text_checklist',
				'title'           => __( 'CB Image Text Checklist' ),
				'category'        => 'layout',
				'icon'            => 'cover-image',
				'render_template' => 'blocks/cb-image-text-checklist.php',
				'api_version'     => 3,
				'supports'        => array(
					'anchor'    => true,
					'className' => true,
					'align'     => true,

				),
			)
		);

		acf_register_block_type(
			array(
				'name'            => 'cb_hero',
				'title'           => __( 'CB Hero' ),
				'category'        => 'layout',
				'icon'            => 'cover-image',
				'render_template' => 'blocks/cb-hero.php',
				'api_version'     => 3,
				'supports'        => array(
					'anchor'    => true,
					'className' => true,
					'align'     => true,

				),
			)
		);

    }
}
add_action( 'acf/init', 'acf_blocks' );

/**
 * Opt every ACF block into ACF Blocks v3.
 *
 * WordPress 7.1 forces the block editor into an iframe, and only ACF Blocks v3
 * is iframe-compatible. On v1/v2 the fields fail to initialise inside the
 * iframe - WYSIWYG fields throw during quicktags setup and take the whole block
 * form down with them.
 *
 * v3 removes the edit/preview mode concept: blocks always render their preview,
 * and fields are edited in the sidebar or the larger pop-out panel behind the
 * pencil icon in the block toolbar.
 *
 * @param integer $version The default ACF block version.
 * @param array   $block   The block settings.
 * @return integer
 */
function cb_acf_block_version( $version, $block ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
	return 3;
}
add_filter( 'acf/blocks/default_block_version', 'cb_acf_block_version', 10, 2 );


/**
 * Populate the CB Form block's form picker from the active Gravity Forms.
 *
 * Keeps the choices in sync with whatever forms exist rather than hardcoding
 * IDs in the field group JSON.
 *
 * @param array $field The ACF field array.
 * @return array
 */
function cb_form_id_choices( $field ) {
	$field['choices'] = array();

	if ( ! class_exists( 'GFAPI' ) ) {
		return $field;
	}

	foreach ( GFAPI::get_forms() as $form ) {
		$field['choices'][ $form['id'] ] = $form['title'];
	}

	return $field;
}
add_filter( 'acf/load_field/key=field_cb_form_form_id', 'cb_form_id_choices' );

// Auto-sync ACF field groups from acf-json folder.
add_filter(
	'acf/settings/save_json',
	function ( $path ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
		return get_stylesheet_directory() . '/acf-json';
	}
);

add_filter(
	'acf/settings/load_json',
	function ( $paths ) {
		unset( $paths[0] );
		$paths[] = get_stylesheet_directory() . '/acf-json';
		return $paths;
	}
);

/**
 * Modifies the arguments for specific core block types.
 *
 * @param array  $args The block type arguments.
 * @param string $name The block type name.
 * @return array Modified block type arguments.
 */
function core_block_type_args( $args, $name ) {

	if ( 'core/paragraph' === $name ) {
		$args['render_callback'] = 'modify_core_add_container';
	}
	if ( 'core/heading' === $name ) {
		$args['render_callback'] = 'modify_core_add_container';
	}
	if ( 'core/list' === $name ) {
		$args['render_callback'] = 'modify_core_add_container';
	}
	if ( 'core/separator' === $name ) {
		$args['render_callback'] = 'modify_core_add_container';
	}

    return $args;
}
add_filter( 'register_block_type_args', 'core_block_type_args', 10, 3 );

/**
 * Helper function to detect if footer.php is being rendered.
 *
 * @return bool True if footer.php is being rendered, false otherwise.
 */
function is_footer_rendering() {
    $backtrace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_debug_backtrace
    foreach ( $backtrace as $trace ) {
        if ( isset( $trace['file'] ) && basename( $trace['file'] ) === 'footer.php' ) {
            return true;
        }
    }
    return false;
}

/**
 * Adds a container div around the block content unless footer.php is being rendered.
 *
 * @param array  $attributes The block attributes.
 * @param string $content    The block content.
 * @return string The modified block content wrapped in a container div.
 */
function modify_core_add_container( $attributes, $content ) {
    if ( is_footer_rendering() ) {
        return $content;
    }

    ob_start();
    ?>
    <div class="container">
        <?= wp_kses_post( $content ); ?>
    </div>
	<?php
	$content = ob_get_clean();
    return $content;
}
