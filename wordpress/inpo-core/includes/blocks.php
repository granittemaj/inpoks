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
	// Data: interactive chart and sortable table (same file as the prototype's assets/js/charts.js).
	// Infographic images: a row to swipe and a full-screen view (same file as the prototype's assets/js/slides.js).
	wp_register_script( 'inpo-core-slides', INPO_CORE_URL . 'assets/slides.js', array(), INPO_CORE_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );
	wp_register_script( 'inpo-core-charts', INPO_CORE_URL . 'assets/charts.js', array(), INPO_CORE_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );
	// PDF reader. pdf.js (Mozilla, Apache-2.0) is bundled and only downloaded when a reader opens.
	wp_register_script( 'inpo-core-reader', INPO_CORE_URL . 'assets/reader.js', array(), INPO_CORE_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );
	wp_add_inline_script(
		'inpo-core-reader',
		'window.inpoReader=' . wp_json_encode(
			array(
				'lib'    => INPO_CORE_URL . 'assets/vendor/pdfjs/pdf.min.js?ver=3.11.174',
				'worker' => INPO_CORE_URL . 'assets/vendor/pdfjs/pdf.worker.min.js?ver=3.11.174',
			)
		) . ';',
		'before'
	);

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
