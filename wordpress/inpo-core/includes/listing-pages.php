<?php
/**
 * Listing pages: the lists (publications, projects, data, news) take their heading, intro and extra
 * sections from an ordinary Page, so editors change them under Pages like any other page.
 *
 * - Publications, Projects, Data: a private Page per list, made by the plugin (private, so it has no
 *   public URL of its own; "View" opens the list). Kept in the option inpo_core_listing_pages.
 * - News: the Posts page from Settings > Reading.
 * Without a page, the list falls back to the texts in the theme template.
 *
 * @package inpo-core
 */

defined( 'ABSPATH' ) || exit;

/**
 * The lists that have a page: kind => title, kicker, intro, default blocks under the list.
 */
function inpo_core_listing_kinds() {
	return array(
		'publikimet' => array(
			'label'   => __( 'Publications list', 'inpo-core' ),
			'title'   => 'Hulumtime dhe publikime',
			'kicker'  => 'Hulumtime dhe publikime',
			'lead'    => 'Raporte monitorimi, hulumtime, analiza të politikave publike dhe analiza ligjore, infografika dhe rekomandime për institucionet. Të gjitha falas, në PDF.',
			'content' => '',
		),
		'projektet'  => array(
			'label'   => __( 'Projects list', 'inpo-core' ),
			'title'   => 'Projektet dhe ndikimi',
			'kicker'  => 'Projektet dhe ndikimi',
			'lead'    => 'Projektet që zbatojmë me partnerët dhe donatorët tanë, dhe ndryshimet që kanë sjellë në institucione.',
			'content' => "<!-- wp:inpo/impact {\"kicker\":\"Rezultatet dhe ndikimi institucional\",\"anchor\":\"ndikimi\",\"buttonText\":\"\"} /-->\n\n<!-- wp:inpo/impact-cases /-->\n\n<!-- wp:inpo/donors {\"kicker\":\"Partnerët dhe donatorët\",\"heading\":\"Me kë punojmë.\",\"anchor\":\"partneret\"} /-->",
		),
		'te-dhenat'  => array(
			'label'   => __( 'Data list', 'inpo-core' ),
			'title'   => 'Të dhëna dhe statistika',
			'kicker'  => 'Hulumtime dhe publikime',
			'lead'    => 'Statistika të prokurimit publik nga monitorimi ynë, në grafikë interaktivë dhe tabela që mund t’i shkarkoni.',
			'content' => '',
		),
		'lajme'      => array(
			'label'   => __( 'News list', 'inpo-core' ),
			'title'   => 'Lajme dhe qëndrime',
			'kicker'  => 'Lajme dhe qëndrime',
			'lead'    => 'Aktivitetet, komunikatat, reagimet dhe qëndrimet tona publike për tenderët, vendimet dhe institucionet që monitorojmë.',
			'content' => '',
		),
	);
}

/**
 * The page behind a list, or null.
 */
function inpo_core_listing_page( $kind ) {
	if ( 'lajme' === $kind ) {
		$id = (int) get_option( 'page_for_posts' );
	} else {
		$ids = (array) get_option( 'inpo_core_listing_pages', array() );
		$id  = isset( $ids[ $kind ] ) ? (int) $ids[ $kind ] : 0;
	}
	$page = $id ? get_post( $id ) : null;
	return $page && 'page' === $page->post_type && 'trash' !== $page->post_status ? $page : null;
}

/**
 * Which list a page belongs to ('' for an ordinary page).
 */
function inpo_core_listing_kind_of( $page_id ) {
	foreach ( array_keys( inpo_core_listing_kinds() ) as $kind ) {
		$page = inpo_core_listing_page( $kind );
		if ( $page && (int) $page->ID === (int) $page_id ) {
			return $kind;
		}
	}
	return '';
}

/**
 * Public URL of a list.
 */
function inpo_core_listing_url( $kind ) {
	switch ( $kind ) {
		case 'publikimet':
			return inpo_core_pubs_url();
		case 'projektet':
			return inpo_core_projects_url();
		case 'te-dhenat':
			$link = get_post_type_archive_link( INPO_CORE_DATASET );
			return $link ? $link : home_url( '/te-dhenat/' );
		default:
			return inpo_core_news_url();
	}
}

/**
 * Makes the private pages for publications, projects and data (once). The news list uses the Posts page.
 *
 * @return int Pages made.
 */
function inpo_core_setup_listing_pages() {
	$ids  = (array) get_option( 'inpo_core_listing_pages', array() );
	$made = 0;
	foreach ( inpo_core_listing_kinds() as $kind => $k ) {
		if ( 'lajme' === $kind || inpo_core_listing_page( $kind ) ) {
			continue;
		}
		$id = wp_insert_post(
			wp_slash(
				array(
					'post_type'    => 'page',
					'post_status'  => 'private',
					'post_title'   => $k['title'],
					'post_name'    => 'faqja-' . $kind,
					'post_excerpt' => $k['lead'],
					'post_content' => $k['content'],
					'meta_input'   => array( 'inpo_kicker' => $k['kicker'], '_inpo_listing' => $kind ),
				)
			)
		);
		if ( $id && ! is_wp_error( $id ) ) {
			$ids[ $kind ] = (int) $id;
			++$made;
		}
	}
	update_option( 'inpo_core_listing_pages', $ids, false );
	// News: the Posts page. Without content of its own it becomes a list page (heading, intro, sections under the list).
	$news = inpo_core_listing_page( 'lajme' );
	if ( $news && ! get_post_meta( $news->ID, '_inpo_listing', true ) && '' === trim( $news->post_content ) ) {
		update_post_meta( $news->ID, '_inpo_listing', 'lajme' );
		$k = inpo_core_listing_kinds()['lajme'];
		if ( ! has_excerpt( $news ) ) {
			wp_update_post( wp_slash( array( 'ID' => $news->ID, 'post_excerpt' => $k['lead'] ) ) );
		}
		if ( ! get_post_meta( $news->ID, 'inpo_kicker', true ) ) {
			update_post_meta( $news->ID, 'inpo_kicker', $k['kicker'] );
		}
	}
	return $made;
}

/**
 * Block inpo/listing: the head of a list (part "head") or the sections an editor added under it (part "content").
 */
function inpo_core_render_listing( $a, $block ) {
	$kinds = inpo_core_listing_kinds();
	$kind  = isset( $kinds[ $a['for'] ] ) ? $a['for'] : 'publikimet';
	$k     = $kinds[ $kind ];
	$page  = inpo_core_listing_page( $kind );

	if ( 'content' === $a['part'] ) {
		// The Posts page may hold old content from before the redesign: it is used only once marked as a list page.
		if ( $page && 'lajme' === $kind && 'lajme' !== get_post_meta( $page->ID, '_inpo_listing', true ) ) {
			$page = null;
		}
		$content = $page ? $page->post_content : $k['content'];
		return trim( $content ) ? do_blocks( $content ) : '';
	}

	$head = array(
		'source' => 'custom',
		'kicker' => $a['kicker'] ? $a['kicker'] : $k['kicker'],
		'title'  => $a['title'] ? $a['title'] : $k['title'],
		'lead'   => $a['lead'] ? $a['lead'] : $k['lead'],
	);
	if ( $page ) {
		$kicker         = get_post_meta( $page->ID, 'inpo_kicker', true );
		$head['kicker'] = $kicker ? $kicker : $head['kicker'];
		// The raw title: get_the_title() would add "Private:" to the private list pages.
		$head['title']  = wp_strip_all_tags( $page->post_title );
		$head['lead']   = has_excerpt( $page ) ? trim( wp_strip_all_tags( get_the_excerpt( $page ) ) ) : $head['lead'];
	}
	return inpo_core_render_page_head( $head, $block );
}

/* ---------- wp-admin: make the listing pages easy to recognise ---------- */

// "Publications list" next to the page in Pages, like WordPress's own "Posts Page".
add_filter(
	'display_post_states',
	function ( $states, $post ) {
		$kind = 'page' === $post->post_type ? inpo_core_listing_kind_of( $post->ID ) : '';
		if ( $kind && 'lajme' !== $kind ) {
			$states['inpo_listing'] = inpo_core_listing_kinds()[ $kind ]['label'];
		}
		return $states;
	},
	10,
	2
);

// "View page" opens the list itself.
add_filter(
	'page_link',
	function ( $link, $post_id ) {
		$ids  = (array) get_option( 'inpo_core_listing_pages', array() );
		$kind = array_search( (int) $post_id, array_map( 'intval', $ids ), true );
		return false !== $kind ? inpo_core_listing_url( $kind ) : $link;
	},
	10,
	2
);

// A note in the editor sidebar: what each field of this page does.
add_action(
	'add_meta_boxes_page',
	function ( $post ) {
		$kind = inpo_core_listing_kind_of( $post->ID );
		if ( ! $kind ) {
			return;
		}
		add_meta_box(
			'inpo-listing',
			__( 'This page is a list', 'inpo-core' ),
			function () use ( $kind ) {
				echo '<p>' . esc_html__( 'Title = the heading of the list. Excerpt = the intro text under it. "Kicker above the title" = the small label above.', 'inpo-core' ) . '</p>';
				echo '<p>' . esc_html__( 'The list itself (with its filters) is added automatically. Blocks you add here appear under the list.', 'inpo-core' ) . '</p>';
				echo '<p><a href="' . esc_url( inpo_core_listing_url( $kind ) ) . '" target="_blank" rel="noopener">' . esc_html__( 'Open the list', 'inpo-core' ) . '</a></p>';
			},
			'page',
			'side',
			'high'
		);
	}
);
