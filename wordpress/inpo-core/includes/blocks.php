<?php
/**
 * Block registration. Every block is dynamic: block.json holds the attributes,
 * render.php holds the markup (inpo/foo-bar -> inpo_core_render_foo_bar()).
 * No build step: the editor UI is plain JS in assets/editor.js.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'inpo_core_register_blocks' );
function inpo_core_register_blocks() {
	wp_register_script(
		'inpo-core-editor',
		INPO_CORE_URL . 'assets/editor.js',
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render', 'wp-data', 'wp-core-data' ),
		INPO_CORE_VERSION,
		true
	);
	wp_register_script( 'inpo-core-view', INPO_CORE_URL . 'assets/view.js', array(), INPO_CORE_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );
	// Public endpoints, so no nonce: keeps full-page caching safe.
	wp_add_inline_script(
		'inpo-core-view',
		'window.inpoCore=' . wp_json_encode( array( 'rest' => esc_url_raw( rest_url( 'inpo/v1/' ) ) ) ) . ';',
		'before'
	);

	foreach ( glob( INPO_CORE_DIR . 'blocks/*/block.json' ) as $file ) {
		$slug     = basename( dirname( $file ) );
		$callback = 'inpo_core_render_' . str_replace( '-', '_', $slug );
		register_block_type(
			dirname( $file ),
			array(
				'render_callback' => function ( $attributes, $content, $block ) use ( $callback ) {
					return call_user_func( $callback, $attributes, $block );
				},
			)
		);
	}
}

add_filter(
	'block_categories_all',
	function ( $categories ) {
		array_unshift( $categories, array( 'slug' => 'inpo', 'title' => 'INPO', 'icon' => null ) );
		return $categories;
	}
);
