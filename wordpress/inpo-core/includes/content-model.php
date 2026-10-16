<?php
/**
 * Content model.
 *
 * News          core posts. Categories flagged "news filter" are the filters and labels.
 * Publications  CPT publikimet at /publikimet/, types in tipi_i_publikimit; filters ?lloji=, ?programi=, ?viti=.
 *               The names match the live site, where CPT UI registered them; ours runs later and wins.
 * Programmes    taxonomy inpo_program at /programet/<slug>/ (the programme page).
 * Donors        CPT inpo_donor (logo = featured image).
 * Team          CPT inpo_member (photo = featured image, role, email, group: team or board).
 * Projects      CPT inpo_project at /projektet/ (status, period, donor, partners, objectives, results).
 * Documents     CPT inpo_doc (transparency: annual, financial and audit reports...), categories in inpo_doc_cat.
 * Data          CPT inpo_dataset at /te-dhenat/ (CSV shown as an interactive chart and table).
 * Thematic fields (inpo_theme) and institutions (inpo_institution) label publications and filter the library.
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
			'taxonomies'    => array( INPO_CORE_PUB_TYPE, INPO_CORE_PROGRAM, INPO_CORE_THEME, INPO_CORE_INST ),
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

	// Thematic fields and institutions: labels and library filters only (no archives of their own).
	foreach ( array(
		INPO_CORE_THEME => array( __( 'Thematic fields', 'inpo-core' ), __( 'Thematic field', 'inpo-core' ) ),
		INPO_CORE_INST  => array( __( 'Institutions', 'inpo-core' ), __( 'Institution', 'inpo-core' ) ),
	) as $taxonomy => $names ) {
		register_taxonomy(
			$taxonomy,
			array( INPO_CORE_PUB, 'post' ),
			array(
				'labels'             => array(
					'name'          => $names[0],
					'singular_name' => $names[1],
					'menu_name'     => $names[0],
					/* translators: %s: taxonomy name */
					'add_new_item'  => sprintf( __( 'Add %s', 'inpo-core' ), strtolower( $names[1] ) ),
					/* translators: %s: taxonomy name */
					'edit_item'     => sprintf( __( 'Edit %s', 'inpo-core' ), strtolower( $names[1] ) ),
				),
				'public'             => false,
				'publicly_queryable' => false,
				'show_ui'            => true,
				'show_in_rest'       => true,
				'show_admin_column'  => true,
				'hierarchical'       => true,
				'query_var'          => false,
				'rewrite'            => false,
			)
		);
	}

	register_post_type(
		INPO_CORE_PROJECT,
		array(
			'labels'        => array(
				'name'          => __( 'Projects', 'inpo-core' ),
				'singular_name' => __( 'Project', 'inpo-core' ),
				'add_new'       => __( 'Add project', 'inpo-core' ),
				'add_new_item'  => __( 'Add new project', 'inpo-core' ),
				'edit_item'     => __( 'Edit project', 'inpo-core' ),
				'view_item'     => __( 'View project', 'inpo-core' ),
				'all_items'     => __( 'All projects', 'inpo-core' ),
				'search_items'  => __( 'Search projects', 'inpo-core' ),
				'not_found'     => __( 'No projects found.', 'inpo-core' ),
			),
			'public'        => true,
			'show_in_rest'  => true,
			'menu_icon'     => 'dashicons-portfolio',
			'menu_position' => 6,
			'has_archive'   => $live ? 'projektet' : false,
			'rewrite'       => array( 'slug' => 'projektet', 'with_front' => false ),
			'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'page-attributes' ),
			'taxonomies'    => array( INPO_CORE_PROGRAM ),
		)
	);

	register_post_type(
		INPO_CORE_DOC,
		array(
			'labels'              => array(
				'name'          => __( 'Documents', 'inpo-core' ),
				'singular_name' => __( 'Document', 'inpo-core' ),
				'add_new'       => __( 'Add document', 'inpo-core' ),
				'add_new_item'  => __( 'Add new document', 'inpo-core' ),
				'edit_item'     => __( 'Edit document', 'inpo-core' ),
				'all_items'     => __( 'All documents', 'inpo-core' ),
				'search_items'  => __( 'Search documents', 'inpo-core' ),
				'not_found'     => __( 'No documents found.', 'inpo-core' ),
			),
			'description'         => __( 'Transparency: annual, financial and audit reports, governance documents, internal policies, strategies.', 'inpo-core' ),
			'public'              => false,
			'show_ui'             => true,
			'show_in_rest'        => true,
			'menu_icon'           => 'dashicons-media-spreadsheet',
			'menu_position'       => 7,
			'supports'            => array( 'title', 'page-attributes' ),
			'exclude_from_search' => true,
		)
	);
	register_taxonomy(
		INPO_CORE_DOC_CAT,
		array( INPO_CORE_DOC ),
		array(
			'labels'            => array(
				'name'          => __( 'Document categories', 'inpo-core' ),
				'singular_name' => __( 'Document category', 'inpo-core' ),
				'menu_name'     => __( 'Categories', 'inpo-core' ),
			),
			'public'            => false,
			'show_ui'           => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'hierarchical'      => true,
			'query_var'         => false,
			'rewrite'           => false,
		)
	);

	register_post_type(
		INPO_CORE_DATASET,
		array(
			'labels'        => array(
				'name'          => __( 'Data', 'inpo-core' ),
				'singular_name' => __( 'Dataset', 'inpo-core' ),
				'add_new'       => __( 'Add dataset', 'inpo-core' ),
				'add_new_item'  => __( 'Add new dataset', 'inpo-core' ),
				'edit_item'     => __( 'Edit dataset', 'inpo-core' ),
				'view_item'     => __( 'View dataset', 'inpo-core' ),
				'all_items'     => __( 'All datasets', 'inpo-core' ),
				'not_found'     => __( 'No datasets found.', 'inpo-core' ),
			),
			'description'   => __( 'Procurement statistics, shown as interactive charts and tables.', 'inpo-core' ),
			'public'        => true,
			'show_in_rest'  => true,
			'menu_icon'     => 'dashicons-chart-bar',
			'menu_position' => 8,
			'has_archive'   => $live ? 'te-dhenat' : false,
			'rewrite'       => array( 'slug' => 'te-dhenat', 'with_front' => false ),
			'supports'      => array( 'title', 'excerpt', 'revisions', 'page-attributes' ),
			'taxonomies'    => array( INPO_CORE_PROGRAM, INPO_CORE_THEME ),
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
			'inpo_impact'      => 'boolean',
		),
		INPO_CORE_PUB    => array(
			'inpo_short'    => 'string',
			'inpo_cover'    => 'string',
			'inpo_year'     => 'string',
			'inpo_partners' => 'string',
			'inpo_donor'    => 'string',
			'inpo_pdf'      => 'string',
			'inpo_authors'  => 'string',
			// Infographics: their images (attachment IDs, in order), and their numbers drawn as charts.
			'inpo_slides'   => 'string',
			'inpo_csv'      => 'string',
			'inpo_figures'  => 'string',
			'inpo_source'   => 'string',
		),
		INPO_CORE_PROJECT => array(
			'inpo_status'      => 'string',
			'inpo_period'      => 'string',
			'inpo_donor_text'  => 'string',
			'inpo_partners'    => 'string',
			'inpo_goals'       => 'string',
			'inpo_results'     => 'string',
			'inpo_publication' => 'integer',
		),
		INPO_CORE_DOC     => array(
			'inpo_year'   => 'string',
			'inpo_pdf'    => 'string',
			'inpo_source' => 'integer',
		),
		INPO_CORE_DATASET => array(
			'inpo_csv'    => 'string',
			'inpo_chart'  => 'string',
			'inpo_unit'   => 'string',
			'inpo_source' => 'string',
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
			'inpo_group' => 'string',
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
	foreach ( array( 'inpo_order' => 'integer', 'inpo_plural' => 'string', 'inpo_singular' => 'string', 'inpo_hidden' => 'string' ) as $key => $type ) {
		register_term_meta( INPO_CORE_PUB_TYPE, $key, array( 'type' => $type, 'single' => true, 'show_in_rest' => true ) );
	}
	foreach ( array( 'inpo_chip' => 'string', 'inpo_singular' => 'string', 'inpo_order' => 'integer' ) as $key => $type ) {
		register_term_meta( 'category', $key, array( 'type' => $type, 'single' => true, 'show_in_rest' => true ) );
	}
	register_term_meta( INPO_CORE_DOC_CAT, 'inpo_order', array( 'type' => 'integer', 'single' => true, 'show_in_rest' => true ) );
	foreach ( array( 'inpo_objectives', 'inpo_results' ) as $key ) {
		register_term_meta( INPO_CORE_PROGRAM, $key, array( 'type' => 'string', 'single' => true, 'show_in_rest' => true ) );
	}
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
	// Library: 12 per page; search, type, programme, year and order from the URL (see filters.php).
	if ( $query->is_post_type_archive( INPO_CORE_PUB ) ) {
		$query->set( 'posts_per_page', 12 );
		inpo_core_filter_apply( $query, 'pub' );
	}
	// News list: 9 cards per page (one large plus a grid of eight), same filters.
	if ( $query->is_home() || $query->is_category() ) {
		$query->set( 'posts_per_page', 9 );
		inpo_core_filter_apply( $query, 'news' );
	}
	// Projects: active and completed (?statusi=aktive|perfunduara), in editor order then newest.
	if ( $query->is_post_type_archive( INPO_CORE_PROJECT ) ) {
		$query->set( 'posts_per_page', 24 );
		$query->set( 'orderby', array( 'menu_order' => 'ASC', 'date' => 'DESC' ) );
		$status = isset( $_GET['statusi'] ) ? sanitize_key( inpo_core_str( wp_unslash( $_GET['statusi'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		if ( 'aktive' === $status ) {
			$query->set( 'meta_query', array( 'relation' => 'OR', array( 'key' => 'inpo_status', 'value' => 'perfunduar', 'compare' => '!=' ), array( 'key' => 'inpo_status', 'compare' => 'NOT EXISTS' ) ) );
		} elseif ( 'perfunduara' === $status ) {
			$query->set( 'meta_query', array( array( 'key' => 'inpo_status', 'value' => 'perfunduar' ) ) );
		}
	}
	if ( $query->is_post_type_archive( INPO_CORE_DATASET ) ) {
		$query->set( 'posts_per_page', 24 );
		$query->set( 'orderby', array( 'menu_order' => 'ASC', 'date' => 'DESC' ) );
	}
	// Site search covers news and publications, like the prototype overlay.
	if ( $query->is_search() && ! $query->get( 'post_type' ) ) {
		$query->set( 'post_type', array( 'post', INPO_CORE_PUB, 'page', INPO_CORE_PROJECT, INPO_CORE_DATASET ) );
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
 * The post type's labels are English for wp-admin; visitors see the Albanian name
 * (browser tab, and Yoast's %%pt_plural%% / %%pt_single%% in titles).
 */
add_filter(
	'post_type_archive_title',
	function ( $title, $post_type ) {
		$names = array( INPO_CORE_PUB => 'Hulumtime dhe publikime', INPO_CORE_PROJECT => 'Projektet dhe ndikimi', INPO_CORE_DATASET => 'Të dhëna dhe statistika' );
		return isset( $names[ $post_type ] ) ? $names[ $post_type ] : $title;
	},
	10,
	2
);
add_filter(
	'wpseo_replacements',
	function ( $replacements ) {
		$names = array(
			INPO_CORE_PUB     => array( 'Hulumtime dhe publikime', 'Publikim' ),
			INPO_CORE_PROJECT => array( 'Projektet', 'Projekt' ),
			INPO_CORE_DATASET => array( 'Të dhëna dhe statistika', 'Të dhëna' ),
		);
		foreach ( $names as $type => $n ) {
			if ( is_post_type_archive( $type ) || is_singular( $type ) ) {
				if ( isset( $replacements['%%pt_plural%%'] ) ) {
					$replacements['%%pt_plural%%'] = $n[0];
				}
				if ( isset( $replacements['%%pt_single%%'] ) ) {
					$replacements['%%pt_single%%'] = $n[1];
				}
			}
		}
		return $replacements;
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

/**
 * A dataset as a CSV file: /te-dhenat/<slug>/?shkarko=csv (the "Shkarko CSV" link without JS).
 */
add_action( 'template_redirect', 'inpo_core_dataset_csv' );
function inpo_core_dataset_csv() {
	if ( ! is_singular( INPO_CORE_DATASET ) || empty( $_GET['shkarko'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return;
	}
	$post = get_queried_object();
	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( $post->post_name ) . '.csv"' );
	echo "\xEF\xBB\xBF" . get_post_meta( $post->ID, 'inpo_csv', true ); // phpcs:ignore WordPress.Security.EscapeOutput
	exit;
}

/**
 * Programme links without double work (INPO's brief: publications and news link to their programme
 * automatically). When a post or publication is saved without a programme, it gets one:
 * a news item from its linked publication, a publication from its thematic field
 * (Edukimi demokratik: Edukim demokratik; any other field: Qeverisje e mirë).
 */
add_action( 'save_post', 'inpo_core_auto_program', 20, 2 );
function inpo_core_auto_program( $post_id, $post ) {
	if ( wp_is_post_revision( $post_id ) || 'auto-draft' === $post->post_status || ! in_array( $post->post_type, array( 'post', INPO_CORE_PUB ), true ) ) {
		return;
	}
	if ( wp_get_object_terms( $post_id, INPO_CORE_PROGRAM, array( 'fields' => 'ids' ) ) ) {
		return;
	}
	$slug = inpo_core_guess_program( $post );
	if ( $slug && term_exists( $slug, INPO_CORE_PROGRAM ) ) {
		wp_set_object_terms( $post_id, $slug, INPO_CORE_PROGRAM );
	}
}

/**
 * The programme a post or publication belongs to, from what it is linked to.
 *
 * @return string Programme slug or ''.
 */
function inpo_core_guess_program( $post ) {
	if ( 'post' === $post->post_type ) {
		$pub = (int) get_post_meta( $post->ID, 'inpo_publication', true );
		$own = $pub ? wp_get_object_terms( $pub, INPO_CORE_PROGRAM, array( 'fields' => 'slugs' ) ) : array();
		return $own && ! is_wp_error( $own ) ? $own[0] : '';
	}
	$themes = wp_get_object_terms( $post->ID, INPO_CORE_THEME, array( 'fields' => 'slugs' ) );
	if ( ! $themes || is_wp_error( $themes ) ) {
		return '';
	}
	return in_array( 'edukimi-demokratik', $themes, true ) ? 'edukim' : 'qeverisje';
}
