<?php
/**
 * INPO theme setup.
 *
 * The theme owns presentation: tokens and CSS, templates, the header and footer blocks,
 * page patterns and the UI script (menu, search, theme toggle, home intro).
 * Content blocks, publications and forms come from the INPO Core plugin.
 */

defined( 'ABSPATH' ) || exit;

define( 'INPO_THEME_VERSION', wp_get_theme( get_template() )->get( 'Version' ) );

require_once get_template_directory() . '/inc/chrome.php';

add_action( 'after_setup_theme', 'inpo_theme_setup' );
function inpo_theme_setup() {
	load_theme_textdomain( 'inpo', get_template_directory() . '/languages' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'custom-logo', array( 'flex-width' => true, 'flex-height' => true ) );
	add_editor_style( 'assets/css/inpo.css' );
	register_nav_menus( array( 'primary' => __( 'Main menu (header and mobile menu)', 'inpo' ) ) );
	remove_theme_support( 'core-block-patterns' );
}

add_action( 'wp_enqueue_scripts', 'inpo_theme_assets' );
function inpo_theme_assets() {
	$uri = get_template_directory_uri();
	wp_enqueue_style( 'inpo', $uri . '/assets/css/inpo.css', array(), INPO_THEME_VERSION );
	wp_enqueue_script( 'inpo', $uri . '/assets/js/theme.js', array(), INPO_THEME_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );
	wp_add_inline_script(
		'inpo',
		'window.inpoTheme=' . wp_json_encode(
			array(
				'search'     => esc_url_raw( rest_url( 'inpo/v1/search' ) ),
				'coreSearch' => esc_url_raw( rest_url( 'wp/v2/search' ) ),
				'hasPlugin'  => function_exists( 'inpo_core_settings' ),
				'home'       => esc_url_raw( home_url( '/' ) ),
			)
		) . ';',
		'before'
	);
}

/**
 * Before paint: enable JS-only styles and apply the saved colour theme without a flash.
 * Also preloads the one font file.
 */
add_action( 'wp_head', 'inpo_theme_head', 1 );
function inpo_theme_head() {
	echo '<meta name="theme-color" content="#3D3B8E">' . "\n";
	echo '<link rel="preload" href="' . esc_url( get_template_directory_uri() . '/assets/fonts/Geist-Variable.woff2' ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
	echo "<script>(function(){var d=document.documentElement;d.classList.add('js');try{var t=localStorage.getItem('inpo-theme');if(t)d.dataset.theme=t;}catch(e){}})();</script>\n";
	if ( ! has_site_icon() ) {
		echo '<link rel="icon" href="' . esc_url( get_template_directory_uri() . '/assets/favicon.svg' ) . '" type="image/svg+xml">' . "\n";
	}
}

/**
 * The header renders its own skip link ("Kalo te përmbajtja"), so drop the core one.
 */
add_action(
	'init',
	function () {
		remove_action( 'wp_enqueue_scripts', 'wp_enqueue_block_template_skip_link' );
		remove_action( 'wp_footer', 'the_block_template_skip_link' );
	}
);

/**
 * Header and footer are dynamic blocks, so menus and organisation details stay live.
 */
add_action( 'init', 'inpo_theme_blocks' );
function inpo_theme_blocks() {
	wp_register_script(
		'inpo-theme-editor',
		get_template_directory_uri() . '/assets/js/editor.js',
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render' ),
		INPO_THEME_VERSION,
		true
	);
	register_block_type( get_template_directory() . '/blocks/site-header', array( 'render_callback' => 'inpo_theme_render_header' ) );
	register_block_type( get_template_directory() . '/blocks/site-footer', array( 'render_callback' => 'inpo_theme_render_footer' ) );

	register_block_style( 'core/button', array( 'name' => 'ink', 'label' => __( 'Dark', 'inpo' ) ) );
	register_block_style( 'core/button', array( 'name' => 'line', 'label' => __( 'Outline', 'inpo' ) ) );
	register_block_pattern_category( 'inpo', array( 'label' => 'INPO' ) );
}

/**
 * Reminds admins to activate the companion plugin.
 */
add_action(
	'admin_notices',
	function () {
		if ( function_exists( 'inpo_core_settings' ) || ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		echo '<div class="notice notice-warning"><p><strong>INPO:</strong> ' . esc_html__( 'Please activate the INPO Core plugin. It provides the publications, programmes, forms and blocks this theme uses.', 'inpo' ) . '</p></div>';
	}
);

/**
 * Titles read "Lajme · INPO", like the prototype.
 */
add_filter(
	'document_title_separator',
	function () {
		return '·';
	}
);
