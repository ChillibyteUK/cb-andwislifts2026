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

    }
}
add_action( 'acf/init', 'acf_blocks' );

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
