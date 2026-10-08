<?php
/**
 * Content model.
 *
 * News          core posts. Categories flagged "news filter" are the filters and labels.
 * Publications  CPT publikimet at /publikimet/, types in tipi_i_publikimit; filters ?lloji=, ?programi=, ?viti=.
 *               The names match the live site, where CPT UI registered them; ours runs later and wins.
 * Programmes    taxonomy inpo_program at /programet/<slug>/ (the programme page).
 * Donors        CPT inpo_donor (logo = featured image).
 * Team          CPT inpo_member (photo = featured image, role, email).
 * Messages and subscribers are private CPTs, see forms.php.
 *
 * Everything that changes how the public site behaves (publication archive, list sizes, search,
 * redirects) waits until the INPO theme is active, so the plugin can be installed on the live
 * site first and its data prepared while the old theme still serves visitors.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'inpo_core_register_content_model', 20 );

function inpo_core_register_content_model() {
	$live = inpo_core_theme_active();

	register_post_type(
		INPO_CORE_PUB,
		array(
			'labels'        => array(
				'name'               => __( 'Publications', 'inpo-core' ),
				'singular_name'      => __( 'Publication', 'inpo-core' ),
				'menu_name'          => __( 'Publications', 'inpo-core' ),
				'add_new'            => __( 'Add publication', 'inpo-core' ),
				'add_new_item'       => __( 'Add new publication', 'inpo-core' ),
				'edit_item'          => __( 'Edit publication', 'inpo-core' ),
				'new_item'           => __( 'New publication', 'inpo-core' ),
				'view_item'          => __( 'View publication', 'inpo-core' ),
				'search_items'       => __( 'Search publications', 'inpo-core' ),
				'not_found'          => __( 'No publications found.', 'inpo-core' ),
				'not_found_in_trash' => __( 'No publications in the trash.', 'inpo-core' ),
				'all_items'          => __( 'All publications', 'inpo-core' ),
				'archives'           => __( 'Library', 'inpo-core' ),
			),
			'public'        => true,
			'show_in_rest'  => true,
			'menu_icon'     => 'dashicons-media-document',
			'menu_position' => 5,
			// With the old theme, /publikimet/ stays the old "Publikimet" page.
			'has_archive'   => $live ? 'publikimet' : false,
			'rewrite'       => array( 'slug' => 'publikimet', 'with_front' => false ),
			'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields', 'page-attributes' ),
			'taxonomies'    => array( INPO_CORE_PUB_TYPE, INPO_CORE_PROGRAM ),
		)
	);

	register_taxonomy(
		INPO_CORE_PUB_TYPE,
		array( INPO_CORE_PUB ),
		array(
			'labels'            => array(
				'name'          => __( 'Publication types', 'inpo-core' ),
				'singular_name' => __( 'Publication type', 'inpo-core' ),
				'menu_name'     => __( 'Types', 'inpo-core' ),
				'add_new_item'  => __( 'Add publication type', 'inpo-core' ),
				'edit_item'     => __( 'Edit publication type', 'inpo-core' ),
				'search_items'  => __( 'Search types', 'inpo-core' ),
			),
			'public'            => true,
			'hierarchical'      => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			// Filters the library: /publikimet/?lloji=analiza. No archive of its own.
			'query_var'         => 'lloji',
			'rewrite'           => false,
		)
	);

	register_taxonomy(
		INPO_CORE_PROGRAM,
		array( INPO_CORE_PUB, 'post' ),
		array(
			'labels'            => array(
				'name'          => __( 'Programmes', 'inpo-core' ),
				'singular_name' => __( 'Programme', 'inpo-core' ),
				'menu_name'     => __( 'Programmes', 'inpo-core' ),
				'add_new_item'  => __( 'Add programme', 'inpo-core' ),
				'edit_item'     => __( 'Edit programme', 'inpo-core' ),
				'search_items'  => __( 'Search programmes', 'inpo-core' ),
			),
			'public'            => true,
			'hierarchical'      => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'query_var'         => 'programi',
			'rewrite'           => array( 'slug' => 'programet', 'with_front' => false ),
		)
	);

	register_post_type(
		INPO_CORE_DONOR,
		array(
			'labels'              => array(
				'name'                  => __( 'Donors', 'inpo-core' ),
				'singular_name'         => __( 'Donor', 'inpo-core' ),
				'add_new'               => __( 'Add donor', 'inpo-core' ),
				'add_new_item'          => __( 'Add donor', 'inpo-core' ),
				'edit_item'             => __( 'Edit donor', 'inpo-core' ),
				'all_items'             => __( 'Donors', 'inpo-core' ),
				'featured_image'        => __( 'Logo', 'inpo-core' ),
				'set_featured_image'    => __( 'Set logo', 'inpo-core' ),
				'remove_featured_image' => __( 'Remove logo', 'inpo-core' ),
			),
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => 'inpo-core',
			'show_in_rest'        => true,
			'supports'            => array( 'title', 'thumbnail', 'page-attributes' ),
			'exclude_from_search' => true,
		)
	);

	register_post_type(
		INPO_CORE_MEMBER,
		array(
			'labels'              => array(
				'name'                  => __( 'Team', 'inpo-core' ),
				'singular_name'         => __( 'Team member', 'inpo-core' ),
				'add_new'               => __( 'Add team member', 'inpo-core' ),
				'add_new_item'          => __( 'Add team member', 'inpo-core' ),
				'edit_item'             => __( 'Edit team member', 'inpo-core' ),
				'all_items'             => __( 'Team', 'inpo-core' ),
				'featured_image'        => __( 'Photo', 'inpo-core' ),
				'set_featured_image'    => __( 'Set photo', 'inpo-core' ),
				'remove_featured_image' => __( 'Remove photo', 'inpo-core' ),
			),
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => 'inpo-core',
			'show_in_rest'        => true,
			'supports'            => array( 'title', 'thumbnail', 'page-attributes' ),
			'exclude_from_search' => true,
		)
	);

	add_post_type_support( 'page', 'excerpt' );

	$auth = function () {
		return current_user_can( 'edit_posts' );
	};
	$meta = array(
		'post'           => array(
			'inpo_short_title' => 'string',
			'inpo_date_label'  => 'string',
			'inpo_todo'        => 'boolean',
			'inpo_publication' => 'integer',
		),
		INPO_CORE_PUB    => array(
			'inpo_short'    => 'string',
			'inpo_cover'    => 'string',
			'inpo_year'     => 'string',
			'inpo_partners' => 'string',
			'inpo_donor'    => 'string',
			'inpo_pdf'      => 'string',
		),
		'page'           => array(
			'inpo_kicker' => 'string',
		),
		INPO_CORE_DONOR  => array(
			'inpo_url' => 'string',
		),
		INPO_CORE_MEMBER => array(
			'inpo_role'  => 'string',
			'inpo_email' => 'string',
		),
	);
	foreach ( $meta as $post_type => $keys ) {
		foreach ( $keys as $key => $type ) {
			register_post_meta(
				$post_type,
				$key,
				array(
					'type'          => $type,
					'single'        => true,
					'show_in_rest'  => true,
					'auth_callback' => $auth,
				)
			);
		}
	}
	foreach ( array( 'inpo_order' => 'integer', 'inpo_plural' => 'string', 'inpo_singular' => 'string' ) as $key => $type ) {
		register_term_meta( INPO_CORE_PUB_TYPE, $key, array( 'type' => $type, 'single' => true, 'show_in_rest' => true ) );
	}
	register_term_meta( 'category', 'inpo_chip', array( 'type' => 'string', 'single' => true, 'show_in_rest' => true ) );
	register_term_meta(
		INPO_CORE_PROGRAM,
		'inpo_activities',
		array( 'type' => 'array', 'single' => true, 'show_in_rest' => array( 'schema' => array( 'type' => 'array', 'items' => array( 'type' => 'string' ) ) ) )
	);
	foreach ( array( 'inpo_order' => 'integer', 'inpo_full' => 'string', 'inpo_icon' => 'string', 'inpo_ways' => 'array', 'inpo_show_news' => 'boolean' ) as $key => $type ) {
		register_term_meta(
			INPO_CORE_PROGRAM,
			$key,
			array(
				'type'         => $type,
				'single'       => true,
				'show_in_rest' => 'array' === $type ? array( 'schema' => array( 'type' => 'array', 'items' => array( 'type' => 'array', 'items' => array( 'type' => 'string' ) ) ) ) : true,
			)
		);
	}
}

/**
 * When single posts live under the news page (/lajme/%postname%/),
 * /lajme/page/2/ and /lajme/feed/ would be read as post slugs. These rules win first.
 */
add_action( 'init', 'inpo_core_news_rewrites', 20 );
function inpo_core_news_rewrites() {
	$page_id = (int) get_option( 'page_for_posts' );
	if ( ! $page_id ) {
		return;
	}
	$path = get_page_uri( $page_id );
	if ( ! $path ) {
		return;
	}
	$structure = (string) get_option( 'permalink_structure' );
	if ( 0 !== strpos( ltrim( $structure, '/' ), $path . '/' ) ) {
		return;
	}
	$q = preg_quote( $path, '#' );
	add_rewrite_rule( '^' . $q . '/page/?([0-9]{1,})/?$', 'index.php?pagename=' . $path . '&paged=$matches[1]', 'top' );
	add_rewrite_rule( '^' . $q . '/(feed|rdf|rss|rss2|atom)/?$', 'index.php?feed=$matches[1]', 'top' );
	add_rewrite_rule( '^' . $q . '/feed/(feed|rdf|rss|rss2|atom)/?$', 'index.php?feed=$matches[1]', 'top' );
}

/**
 * Main query tweaks for the INPO theme's pages.
 */
add_action( 'pre_get_posts', 'inpo_core_pre_get_posts' );
function inpo_core_pre_get_posts( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! inpo_core_theme_active() ) {
		return;
	}
	// Library: 12 per page, newest first, free-text filter in ?q= (keeps the archive template).
	if ( $query->is_post_type_archive( INPO_CORE_PUB ) ) {
		$query->set( 'posts_per_page', 12 );
		$query->set( 'orderby', array( 'menu_order' => 'ASC', 'date' => 'DESC' ) );
		$q = isset( $_GET['q'] ) ? sanitize_text_field( inpo_core_str( wp_unslash( $_GET['q'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		if ( '' !== $q ) {
			$query->set( 's', $q );
		}
		// ?viti=2023 (not WordPress's own ?year=, which redirects to the date archive).
		$viti = isset( $_GET['viti'] ) ? absint( inpo_core_str( wp_unslash( $_GET['viti'] ) ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
		if ( $viti ) {
			$query->set( 'year', $viti );
		}
	}
	// News list: 9 cards per page (one large plus a grid of eight).
	if ( $query->is_home() || $query->is_category() ) {
		$query->set( 'posts_per_page', 9 );
	}
	// Site search covers news and publications, like the prototype overlay.
	if ( $query->is_search() && ! $query->get( 'post_type' ) ) {
		$query->set( 'post_type', array( 'post', INPO_CORE_PUB, 'page' ) );
	}
}

/**
 * Archive titles without the "Category:" prefix (INPO theme only).
 */
add_filter(
	'get_the_archive_title_prefix',
	function ( $prefix ) {
		return inpo_core_theme_active() ? '' : $prefix;
	}
);

/**
 * Old URLs that have a new home (set when the INPO theme goes live): old programme pages, the empty
 * Video page and the "publications in the programme" page. Stored as
 * array( 'pages' => array( page_id => url ), 'paths' => array( '/old/path/' => url ) ).
 */
add_action( 'template_redirect', 'inpo_core_legacy_redirects', 1 );
function inpo_core_legacy_redirects() {
	$map = get_option( 'inpo_core_redirects' );
	if ( ! is_array( $map ) || ! inpo_core_theme_active() ) {
		return;
	}
	$target = '';
	if ( is_page() && ! empty( $map['pages'][ get_queried_object_id() ] ) ) {
		$target = $map['pages'][ get_queried_object_id() ];
	} elseif ( is_404() && ! empty( $map['paths'] ) ) {
		$path   = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_parse_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_PATH ) : '';
		$path   = trailingslashit( rawurldecode( $path ) );
		$target = isset( $map['paths'][ $path ] ) ? $map['paths'][ $path ] : '';
	}
	if ( $target ) {
		wp_safe_redirect( $target, 301 );
		exit;
	}
}
