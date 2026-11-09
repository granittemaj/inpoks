<?php
/**
 * wp-admin and the login screen in INPO's design system (Quiet Indigo): Geist, indigo for actions and the current place,
 * an ink sidebar and toolbar, soft surfaces and rounded corners; the login screen with the indigo rings panel.
 * Loaded by the theme, so wp-admin returns to WordPress's own look if the theme is switched off.
 */

defined( 'ABSPATH' ) || exit;

/** Login screen. */
add_action(
	'login_enqueue_scripts',
	function () {
		wp_enqueue_style( 'inpo-login', get_template_directory_uri() . '/assets/css/login.css', array( 'login' ), INPO_THEME_VERSION );
	}
);
add_filter( 'login_headerurl', 'home_url' );
add_filter(
	'login_headertext',
	function () {
		return get_bloginfo( 'name' );
	}
);

/** wp-admin, the block editor and the toolbar. */
add_action(
	'admin_enqueue_scripts',
	function () {
		$uri = get_template_directory_uri() . '/assets/css/';
		wp_enqueue_style( 'inpo-admin-bar', $uri . 'admin-bar.css', array( 'admin-bar' ), INPO_THEME_VERSION );
		wp_enqueue_style( 'inpo-admin', $uri . 'admin.css', array( 'common', 'forms', 'colors' ), INPO_THEME_VERSION );
	},
	20
);

/** The toolbar on the public site, for people who are logged in. */
add_action(
	'wp_enqueue_scripts',
	function () {
		if ( is_admin_bar_showing() ) {
			wp_enqueue_style( 'inpo-admin-bar', get_template_directory_uri() . '/assets/css/admin-bar.css', array( 'admin-bar' ), INPO_THEME_VERSION );
		}
	},
	20
);

/** Footer line in wp-admin. */
add_filter(
	'admin_footer_text',
	function () {
		return esc_html__( 'INPO website. Help: INPO > Help: where to edit.', 'inpo' );
	}
);
