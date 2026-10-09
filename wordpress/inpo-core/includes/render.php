<?php
/**
 * Block renderers. Markup matches the approved prototype (assets/js/app.js) class for class,
 * so the theme stylesheet applies unchanged.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Wrapper attributes for the block's root element (adds custom classes from the editor).
 */
function inpo_core_wrapper( $classes, $extra = array() ) {
	return get_block_wrapper_attributes( array_merge( array( 'class' => $classes ), $extra ) );
}

/**
 * The post a block refers to: block context first, then the global post.
 *
 * @param WP_Block $block Block instance.
 * @return WP_Post|null
 */
function inpo_core_context_post( $block ) {
	$id = isset( $block->context['postId'] ) ? (int) $block->context['postId'] : 0;
	return $id ? get_post( $id ) : get_post();
}

/**
 * Editor-only hint when a template block has nothing to show yet.
 */
function inpo_core_placeholder( $text ) {
	if ( wp_is_serving_rest_request() || is_admin() ) {
		return '<div class="wrap"><p class="todo" style="margin:40px 0">' . esc_html( $text ) . '</p></div>';
	}
	return '';
}

/* ---------- Home ---------- */

function inpo_core_render_hero( $a ) {
	$cta1 = $a['cta1Url'] ? $a['cta1Url'] : inpo_core_pubs_url();
	$cta2 = $a['cta2Url'] ? $a['cta2Url'] : inpo_core_page_url( 'rreth-nesh' );
	$btns = '';
	if ( $a['cta1Text'] ) {
		$btns .= '<a class="btn btn-accent" href="' . esc_url( $cta1 ) . '">' . esc_html( $a['cta1Text'] ) . '</a>';
	}
	if ( $a['cta2Text'] ) {
		$btns .= '<a class="btn btn-line" href="' . esc_url( $cta2 ) . '">' . esc_html( $a['cta2Text'] ) . '</a>';
	}
	return '<section ' . inpo_core_wrapper( 'wrap grid12 hero' ) . '>'
		. ( $a['badge'] ? '<span class="badge"><span class="dot"></span>' . esc_html( $a['badge'] ) . '</span>' : '' )
		. '<h1 class="h-display" data-focus><span class="line"><span>' . esc_html( $a['line1'] ) . '</span></span><span class="line accent"><span>' . esc_html( $a['line2'] ) . '</span></span></h1>'
		. '<div class="hero-side"><p class="lead">' . esc_html( $a['lead'] ) . '</p><div class="hero-ctas">' . $btns . '</div></div>'
		. '</section>';
}

function inpo_core_render_bento( $a ) {
	$feat = inpo_core_find_post( $a['featuredId'], $a['featuredSlug'] );
	if ( ! $feat ) {
		$latest = get_posts( array( 'posts_per_page' => 1 ) );
		$feat   = $latest ? $latest[0] : null;
	}
	$html = '';
	if ( $feat ) {
		inpo_core_shown( $feat->ID );
		$title = $a['featuredTitle'] ? $a['featuredTitle'] : get_post_meta( $feat->ID, 'inpo_short_title', true );
		$title = $title ? $title : wp_strip_all_tags( get_the_title( $feat ) );
		$html .= '<a class="feat" href="' . esc_url( get_permalink( $feat ) ) . '">' . inpo_core_rings( 620, 4 )
			. '<div class="feat-top"><span class="pill">' . esc_html( inpo_core_post_category_name( $feat ) ) . '</span>' . inpo_core_time( $feat ) . '</div>'
			. '<div class="feat-bottom"><span class="feat-title">' . esc_html( $title ) . '</span><span class="go">' . inpo_core_icon( 'arrow' ) . '</span></div></a>';
	}
	foreach ( array( 1 => 'light', 2 => 'dark' ) as $n => $tone ) {
		$p    = 'stat' . $n;
		$post = inpo_core_find_post( $a[ $p . 'Id' ], $a[ $p . 'Slug' ] );
		if ( $post ) {
			inpo_core_shown( $post->ID );
		}
		$num   = (string) $a[ $p . 'Number' ];
		$dec   = (int) $a[ $p . 'Decimals' ];
		$shown = esc_html( $a[ $p . 'Prefix' ] . inpo_core_format_number( $num, $dec ) . $a[ $p . 'Suffix' ] );
		$data  = 'data-count="' . esc_attr( (float) $num ) . '"'
			. ( $a[ $p . 'Prefix' ] ? ' data-prefix="' . esc_attr( $a[ $p . 'Prefix' ] ) . '"' : '' )
			. ( $a[ $p . 'Suffix' ] ? ' data-suffix="' . esc_attr( $a[ $p . 'Suffix' ] ) . '"' : '' )
			. ( $dec ? ' data-dec="' . $dec . '"' : '' );
		$html .= '<a class="stat ' . $tone . '" href="' . esc_url( $post ? get_permalink( $post ) : inpo_core_news_url() ) . '">'
			. '<div class="stat-top"><span>' . esc_html( $a[ $p . 'Label' ] ) . '</span>' . ( $post ? inpo_core_time( $post ) : '' ) . '</div>'
			. '<div class="stat-body"><span class="num" ' . $data . '>' . $shown . '</span><p>' . esc_html( $a[ $p . 'Text' ] ) . '</p></div></a>';
	}
	return '<section ' . inpo_core_wrapper( 'wrap', array( 'aria-label' => $a['label'] ) ) . '><div class="bento">' . $html . '</div></section>';
}

function inpo_core_render_programs( $a ) {
	$cards = '';
	foreach ( inpo_core_programs() as $term ) {
		$icon   = get_term_meta( $term->term_id, 'inpo_icon', true );
		$cards .= '<a class="prog" href="' . esc_url( get_term_link( $term ) ) . '"><span class="prog-ico">' . inpo_core_icon( $icon ? $icon : $term->slug ) . '</span>'
			. '<div><h3>' . esc_html( $term->name ) . '</h3><p>' . esc_html( wp_strip_all_tags( $term->description ) ) . '</p><span class="link-u">' . esc_html( $a['linkLabel'] ) . ' ' . inpo_core_icon( 'arrow' ) . '</span></div></a>';
	}
	if ( ! $cards ) {
		return inpo_core_placeholder( __( 'Add programmes under Publications > Programmes.', 'inpo-core' ) );
	}
	return '<section ' . inpo_core_wrapper( 'wrap sec' ) . '><div class="grid12 sec-head"><span class="kicker">' . esc_html( $a['kicker'] ) . '</span><h2 class="h2">' . esc_html( $a['heading'] ) . '</h2></div><div class="progs">' . $cards . '</div></section>';
}

/* ---------- Publications ---------- */

/**
 * Grid of publication cards with a section header.
 */
function inpo_core_render_publications( $a, $block ) {
	$count = max( 1, (int) $a['count'] );
	$base  = array( 'post_type' => INPO_CORE_PUB, 'post_status' => 'publish', 'orderby' => array( 'menu_order' => 'ASC', 'date' => 'DESC' ) );
	$tax   = array();
	$posts = array();

	if ( 'related' === $a['mode'] ) {
		$current = inpo_core_context_post( $block );
		$exclude = ( $current && INPO_CORE_PUB === $current->post_type ) ? array( $current->ID ) : array();
		$type    = $current ? inpo_core_pub_type( $current ) : null;
		if ( $type ) {
			$posts = get_posts( $base + array( 'posts_per_page' => $count, 'post__not_in' => $exclude, 'tax_query' => array( array( 'taxonomy' => INPO_CORE_PUB_TYPE, 'terms' => $type->term_id ) ) ) );
		}
		if ( count( $posts ) < $count ) {
			$exclude = array_merge( $exclude, wp_list_pluck( $posts, 'ID' ) );
			$posts   = array_merge( $posts, get_posts( $base + array( 'posts_per_page' => $count - count( $posts ), 'post__not_in' => $exclude ) ) );
		}
	} else {
		$program = $a['program'];
		if ( 'program' === $a['mode'] || 'current' === $program ) {
			$term    = is_tax( INPO_CORE_PROGRAM ) ? get_queried_object() : null;
			$program = $term ? $term->slug : '';
		}
		if ( $program ) {
			$tax[] = array( 'taxonomy' => INPO_CORE_PROGRAM, 'field' => 'slug', 'terms' => $program );
		}
		if ( $a['type'] ) {
			$tax[] = array( 'taxonomy' => INPO_CORE_PUB_TYPE, 'field' => 'slug', 'terms' => $a['type'] );
		}
		$posts = get_posts( $base + array( 'posts_per_page' => $count, 'tax_query' => $tax ) );
	}

	if ( ! $posts ) {
		return '';
	}
	$cards = '';
	foreach ( $posts as $i => $post ) {
		$cards .= inpo_core_pub_card( $post, $i );
	}

	if ( $a['showChips'] ) {
		$chips = '<a class="chip on" href="' . esc_url( inpo_core_pubs_url() ) . '">Të gjitha</a>';
		foreach ( array_slice( inpo_core_pub_types(), 0, 4 ) as $t ) {
			$chips .= '<a class="chip" href="' . esc_url( inpo_core_pubs_url( array( 'lloji' => $t->slug ) ) ) . '">' . esc_html( inpo_core_type_plural( $t ) ) . '</a>';
		}
		$head = '<div class="pub-head"><div style="display:flex;flex-direction:column;gap:20px">'
			. ( $a['kicker'] ? '<span class="kicker">' . esc_html( $a['kicker'] ) . '</span>' : '' )
			. '<h2 class="h2">' . esc_html( $a['heading'] ) . '</h2></div><div class="chips">' . $chips . '</div></div>';
	} else {
		$link = $a['linkUrl'] ? $a['linkUrl'] : inpo_core_pubs_url( ( 'program' === $a['mode'] && is_tax( INPO_CORE_PROGRAM ) ) ? array( 'programi' => get_queried_object()->slug ) : array() );
		$head = '<div class="pub-head"><h2 class="h2">' . esc_html( $a['heading'] ) . '</h2>'
			. ( $a['linkText'] ? '<a class="btn btn-line btn-sm" href="' . esc_url( $link ) . '">' . esc_html( $a['linkText'] ) . '</a>' : '' ) . '</div>';
	}
	$grid = 3 === (int) $a['columns'] ? 'pubs three' : 'pubs';
	return '<section ' . inpo_core_wrapper( 'wrap sec' ) . '>' . $head . '<div class="' . $grid . '">' . $cards . '</div></section>';
}

/**
 * Library: search, type chips, programme select and results.
 * On the publication archive it renders the main query; elsewhere it builds the same query from the URL.
 */
function inpo_core_render_publication_library( $a ) {
	global $wp_query;
	// phpcs:disable WordPress.Security.NonceVerification
	$q     = isset( $_GET['q'] ) ? sanitize_text_field( inpo_core_str( wp_unslash( $_GET['q'] ) ) ) : '';
	$type  = isset( $_GET['lloji'] ) ? sanitize_title( inpo_core_str( wp_unslash( $_GET['lloji'] ) ) ) : '';
	$prog  = isset( $_GET['programi'] ) ? sanitize_title( inpo_core_str( wp_unslash( $_GET['programi'] ) ) ) : '';
	$year  = isset( $_GET['viti'] ) ? absint( inpo_core_str( wp_unslash( $_GET['viti'] ) ) ) : 0;
	// phpcs:enable
	if ( is_post_type_archive( INPO_CORE_PUB ) && $wp_query->is_main_query() ) {
		$query = $wp_query;
	} else {
		$tax = array();
		if ( $type ) {
			$tax[] = array( 'taxonomy' => INPO_CORE_PUB_TYPE, 'field' => 'slug', 'terms' => $type );
		}
		if ( $prog ) {
			$tax[] = array( 'taxonomy' => INPO_CORE_PROGRAM, 'field' => 'slug', 'terms' => $prog );
		}
		$query = new WP_Query(
			array(
				'post_type'      => INPO_CORE_PUB,
				'post_status'    => 'publish',
				'posts_per_page' => 12,
				's'              => $q,
				'tax_query'      => $tax,
				'year'           => $year,
				'paged'          => max( 1, (int) get_query_var( 'paged' ) ),
				'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
			)
		);
	}
	$action = inpo_core_pubs_url();

	$chips = '<button type="submit" class="chip" name="lloji" value="" aria-pressed="' . ( '' === $type ? 'true' : 'false' ) . '">Të gjitha</button>';
	foreach ( inpo_core_pub_types() as $t ) {
		$chips .= '<button type="submit" class="chip" name="lloji" value="' . esc_attr( $t->slug ) . '" aria-pressed="' . ( $t->slug === $type ? 'true' : 'false' ) . '">' . esc_html( inpo_core_type_plural( $t ) ) . '</button>';
	}
	// Programme filter only when publications actually carry a programme (on the live site they do not yet).
	$selects = '';
	if ( $prog || inpo_core_pubs_have_programs() ) {
		$options = '<option value="">Të gjitha</option>';
		foreach ( inpo_core_programs() as $t ) {
			$options .= '<option value="' . esc_attr( $t->slug ) . '"' . selected( $prog, $t->slug, false ) . '>' . esc_html( $t->name ) . '</option>';
		}
		$selects .= '<label for="pub-prog" style="font-size:14px;font-weight:500">' . esc_html( $a['programLabel'] ) . '</label><select class="sel" id="pub-prog" name="programi">' . $options . '</select>';
	}
	// Year filter, as on the old site: years that have publications, newest first.
	$years = inpo_core_pub_years();
	if ( count( $years ) > 1 ) {
		$options = '<option value="">Të gjitha</option>';
		foreach ( $years as $y ) {
			$options .= '<option value="' . esc_attr( $y ) . '"' . selected( $year, (int) $y, false ) . '>' . esc_html( $y ) . '</option>';
		}
		$selects .= '<label for="pub-year" style="font-size:14px;font-weight:500">Viti</label><select class="sel" id="pub-year" name="viti">' . $options . '</select>';
	}

	$found = (int) $query->found_posts;
	$count = 1 === $found ? '1 publikim' : $found . ' publikime';
	if ( $query->have_posts() ) {
		$cards = '';
		$start = ( max( 1, (int) $query->get( 'paged' ) ) - 1 ) * (int) $query->get( 'posts_per_page' );
		foreach ( $query->posts as $i => $post ) {
			$cards .= inpo_core_pub_card( $post, $start + $i );
		}
	} else {
		$cards = '<div class="empty"><b>Asnjë publikim për këtë kërkim.</b><span class="muted">Provoni një fjalë tjetër ose hiqni filtrat.</span><a class="btn btn-line btn-sm" href="' . esc_url( $action ) . '">Hiq filtrat</a></div>';
	}

	return '<section ' . inpo_core_wrapper( 'wrap' ) . '>'
		. '<form class="pub-filters" method="get" action="' . esc_url( $action ) . '" role="search" aria-label="Filtro publikimet" data-inpo-library>'
		. '<button type="submit" class="sr" tabindex="-1" aria-hidden="true">Kërko</button>'
		. '<input type="hidden" name="lloji" value="' . esc_attr( $type ) . '">'
		. '<div class="toolbar" style="border-bottom:0;padding-bottom:0"><div style="flex:1 1 420px;max-width:560px"><label for="q-pubs" class="sr">Kërko në publikime</label>'
		. '<div class="search-inline">' . inpo_core_icon( 'search' ) . '<input id="q-pubs" name="q" type="search" placeholder="' . esc_attr( $a['placeholder'] ) . '" value="' . esc_attr( $q ) . '"></div></div></div>'
		. '<div class="toolbar" style="margin-top:20px"><div class="chips" role="group" aria-label="Filtro sipas llojit">' . $chips . '</div>'
		. '<div class="toolbar-r"><span class="muted" style="font-size:14px" id="pub-count" aria-live="polite">' . esc_html( $count ) . '</span>'
		. $selects
		. '</div></div><noscript><p style="margin:16px 0 0"><button class="btn btn-line btn-sm" type="submit">Filtro</button></p></noscript>'
		. '</form>'
		. '<div class="results" id="pub-results"><div class="pubs three" id="pub-grid">' . $cards . '</div>' . inpo_core_pager( $query ) . '</div>'
		. '</section>';
}

function inpo_core_render_publication_detail( $a, $block ) {
	$post = inpo_core_context_post( $block );
	if ( ! $post || INPO_CORE_PUB !== $post->post_type ) {
		return inpo_core_placeholder( __( 'Publication details appear here, on a publication page.', 'inpo-core' ) );
	}
	$type     = inpo_core_pub_type( $post );
	$program  = inpo_core_pub_program( $post );
	$partners = get_post_meta( $post->ID, 'inpo_partners', true );
	$year     = inpo_core_pub_year( $post );
	$donor    = get_post_meta( $post->ID, 'inpo_donor', true );
	$pdf      = inpo_core_pub_pdf( $post );
	$archive  = inpo_core_pubs_url();
	$lead     = has_excerpt( $post ) ? wp_strip_all_tags( get_the_excerpt( $post ) ) : '';
	$hint     = ( ! $lead && current_user_can( 'edit_post', $post->ID ) )
		? '<p class="todo" style="margin:0">' . esc_html__( 'Editors only: add a summary in the Excerpt field and visitors will see it here.', 'inpo-core' ) . '</p>'
		: '';
	$size     = $pdf ? inpo_core_file_size( $pdf['size'] ) : '';
	$reader   = $pdf ? inpo_core_reader_attrs( $post, $pdf ) : '';
	$download = $pdf
		? '<button class="btn btn-accent" type="button"' . $reader . ' data-pdf-hash>' . inpo_core_icon( 'book' ) . ' Lexo online</button>'
			. '<a class="btn btn-line" href="' . esc_url( $pdf['url'] ) . '" download>' . inpo_core_icon( 'download' ) . ' Shkarko PDF' . ( $size ? ' <span class="btn-meta">' . esc_html( $size ) . '</span>' : '' ) . '</a>'
		: '<a class="btn btn-accent" href="#" data-noop data-toast="Skedari PDF do të shtohet së shpejti.">' . inpo_core_icon( 'download' ) . ' Shkarko PDF</a>';
	// The cover opens the reader too; without a server preview, the browser draws the real first page into it.
	$cover = inpo_core_cover( $post, 0 );
	if ( $pdf ) {
		$first = false === strpos( $cover, 'cv-page' ) ? ' data-first-page' : '';
		$cover = '<div class="pd-cover"' . $first . '>' . $cover . '<button class="pd-read" type="button"' . $reader . ' data-pdf-hash aria-label="' . esc_attr__( 'Lexo publikimin', 'inpo-core' ) . '"><span>' . inpo_core_icon( 'book' ) . ' Lexo online</span></button></div>';
	}

	$crumbs = '<nav class="crumbs" aria-label="Gjurma"><a href="' . esc_url( $archive ) . '">Publikimet</a>'
		. ( $type ? '<span aria-hidden="true">/</span><a href="' . esc_url( inpo_core_pubs_url( array( 'lloji' => $type->slug ) ) ) . '">' . esc_html( inpo_core_type_plural( $type ) ) . '</a>' : '' ) . '</nav>';

	$facts = '<dl class="facts"><div><dt>Lloji</dt><dd>' . esc_html( inpo_core_type_label( $type ) ) . '</dd></div>'
		. '<div><dt>Programi</dt><dd>' . esc_html( $program ? $program->name : 'INPO' ) . '</dd></div>'
		. '<div><dt>' . ( $partners ? 'Partnerë' : 'Viti' ) . '</dt><dd>' . esc_html( $partners ? $partners : ( $year ? $year : '[Viti]' ) ) . '</dd></div></dl>';

	$html = '<section class="wrap grid12 pd">' . $cover
		. '<div class="pd-info">' . $crumbs . '<h1 data-focus>' . inpo_core_title( $post ) . '</h1>' . $facts
		. ( $lead ? '<p class="lead">' . esc_html( $lead ) . '</p>' : $hint )
		. '<div class="hero-ctas">' . $download . '<button class="btn btn-line" type="button" data-copy>Kopjo linkun</button></div>'
		. ( $donor ? '<p class="muted" style="margin:0;font-size:14px">Me mbështetjen e ' . esc_html( $donor ) . '.</p>' : '' )
		. '</div></section>';

	if ( trim( $post->post_content ) ) {
		$html .= '<section class="wrap sec grid12"><div class="prose">' . inpo_core_the_content( $post ) . '</div></section>';
	}
	return '<div ' . inpo_core_wrapper( '' ) . '>' . $html . '</div>';
}

/* ---------- News ---------- */

/**
 * Rows of news. Layouts: intro (home), section (heading + button), plain (search results).
 */
function inpo_core_render_news_list( $a, $block ) {
	global $wp_query;
	$pager = '';
	if ( $a['inherit'] && ! wp_is_serving_rest_request() ) {
		$posts = $wp_query->posts;
		$pager = inpo_core_pager( $wp_query );
	} else {
		$exclude = $a['skipShown'] ? inpo_core_shown() : array();
		if ( $a['excludeCurrent'] ) {
			$current = inpo_core_context_post( $block );
			if ( $current ) {
				$exclude[] = $current->ID;
			}
		}
		$args = array(
			'posts_per_page'      => max( 1, (int) $a['count'] ),
			'post__not_in'        => $exclude,
			'ignore_sticky_posts' => true,
		);
		if ( $a['category'] ) {
			$args['category_name'] = $a['category'];
		}
		$posts = get_posts( $args );
	}

	$rows = '';
	foreach ( (array) $posts as $post ) {
		$rows .= inpo_core_news_row( $post );
	}
	if ( ! $rows ) {
		if ( 'plain' !== $a['layout'] ) {
			return '';
		}
		$rows = '<div class="empty"><b>Asnjë rezultat.</b><span class="muted">Provoni një fjalë tjetër, p.sh. Vushtrri, OShP ose stadiume.</span></div>';
	}
	$link = '<a class="btn btn-line btn-sm" href="' . esc_url( inpo_core_news_url() ) . '">' . esc_html( $a['linkText'] ) . '</a>';

	if ( 'intro' === $a['layout'] ) {
		return '<section ' . inpo_core_wrapper( 'wrap sec grid12 news-sec' ) . '><div class="news-intro">'
			. ( $a['kicker'] ? '<span class="kicker">' . esc_html( $a['kicker'] ) . '</span>' : '' )
			. '<h2 class="h2">' . esc_html( $a['heading'] ) . '</h2>'
			. ( $a['lead'] ? '<p class="lead" style="font-size:17px">' . esc_html( $a['lead'] ) . '</p>' : '' )
			. ( $a['linkText'] ? $link : '' ) . '</div><div class="news-list">' . $rows . '</div></section>';
	}
	if ( 'section' === $a['layout'] ) {
		return '<section ' . inpo_core_wrapper( 'wrap sec' ) . '><div class="pub-head"><h2 class="h2">' . esc_html( $a['heading'] ) . '</h2>' . ( $a['linkText'] ? $link : '' ) . '</div><div>' . $rows . '</div></section>';
	}
	return '<section ' . inpo_core_wrapper( 'wrap list-plain' ) . '><div>' . $rows . '</div>' . $pager . '</section>';
}

/**
 * News page: category chips, count and cards. Uses the page's own query (posts page, category archive).
 */
function inpo_core_render_news_grid( $a ) {
	global $wp_query;
	$query = $wp_query;
	if ( wp_is_serving_rest_request() || ! ( $wp_query->is_home() || $wp_query->is_archive() ) ) {
		$query = new WP_Query( array( 'posts_per_page' => 9, 'ignore_sticky_posts' => true ) );
	}
	$default = (int) get_option( 'default_category' );
	$chips   = '<a class="chip' . ( is_home() || ! is_category() ? ' on' : '' ) . '" href="' . esc_url( inpo_core_news_url() ) . '"' . ( is_home() ? ' aria-current="true"' : '' ) . '>' . esc_html( $a['allLabel'] ) . '</a>';
	$cat_args = array( 'hide_empty' => true, 'exclude' => array( $default ), 'orderby' => 'term_id' );
	if ( inpo_core_chip_category_ids() ) {
		$cat_args['include'] = inpo_core_chip_category_ids();
		unset( $cat_args['exclude'] );
	}
	foreach ( get_categories( $cat_args ) as $cat ) {
		$on     = is_category( $cat->term_id );
		$chips .= '<a class="chip' . ( $on ? ' on' : '' ) . '" href="' . esc_url( get_category_link( $cat ) ) . '"' . ( $on ? ' aria-current="true"' : '' ) . '>' . esc_html( $cat->name ) . '</a>';
	}
	$found = (int) $query->found_posts;
	$first = max( 1, (int) get_query_var( 'paged' ) ) === 1;
	$cards = '';
	foreach ( $query->posts as $i => $post ) {
		$big    = $first && 0 === $i;
		$cards .= '<a class="ncard' . ( $big ? ' big' : '' ) . '" href="' . esc_url( get_permalink( $post ) ) . '">' . ( $big ? inpo_core_rings( 560, 4 ) : '' )
			. '<div class="ncard-meta"><span class="pill">' . esc_html( inpo_core_post_category_name( $post ) ) . '</span>' . inpo_core_time( $post ) . '</div>'
			. '<div><h2>' . inpo_core_title( $post ) . '</h2><p>' . esc_html( inpo_core_excerpt( $post ) ) . '</p></div></a>';
	}
	if ( ! $cards ) {
		$cards = '<div class="empty"><b>Ende pa lajme.</b><span class="muted">Lajmet dhe reagimet publikohen këtu.</span></div>';
	}
	return '<section ' . inpo_core_wrapper( 'wrap' ) . '><div class="toolbar"><div class="chips" role="group" aria-label="Filtro sipas kategorisë">' . $chips . '</div>'
		. '<span class="muted" style="font-size:14px">' . ( 1 === $found ? '1 artikull' : $found . ' artikuj' ) . '</span></div>'
		. '<div class="news-grid">' . $cards . '</div>' . inpo_core_pager( $query ) . '</section>';
}

/**
 * Post content through the usual filters, with a guard against a block rendering itself.
 */
function inpo_core_the_content( $post ) {
	static $depth = 0;
	if ( $depth > 0 ) {
		return '';
	}
	$depth++;
	$html = apply_filters( 'the_content', str_replace( ']]>', ']]&gt;', $post->post_content ) ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	$depth--;
	return $html;
}

/**
 * The article's own photo above the text. Skipped for shared cover graphics (inpo_generic)
 * and when the same image already appears in the text.
 */
function inpo_core_article_photo( $post ) {
	$id = (int) get_post_thumbnail_id( $post );
	if ( ! $id || get_post_meta( $id, 'inpo_generic', true ) ) {
		return '';
	}
	$file = wp_basename( (string) get_attached_file( $id ) );
	$base = preg_replace( '/\.[a-z0-9]+$/i', '', $file );
	if ( $base && false !== strpos( $post->post_content, $base ) ) {
		return '';
	}
	$caption = wp_get_attachment_caption( $id );
	return '<figure class="art-photo">' . wp_get_attachment_image( $id, 'large', false, array( 'loading' => 'eager', 'sizes' => '(max-width: 980px) 100vw, 740px' ) )
		. ( $caption ? '<figcaption>' . esc_html( $caption ) . '</figcaption>' : '' ) . '</figure>';
}

function inpo_core_render_article( $a, $block ) {
	$post = inpo_core_context_post( $block );
	if ( ! $post || 'post' !== $post->post_type ) {
		return inpo_core_placeholder( __( 'The article appears here, on a news page.', 'inpo-core' ) );
	}
	$cat     = inpo_core_post_category( $post );
	$catname = inpo_core_post_category_name( $post );
	$url     = get_permalink( $post );
	$title   = wp_strip_all_tags( get_the_title( $post ) );
	$pub     = (int) get_post_meta( $post->ID, 'inpo_publication', true );
	$pub     = $pub ? get_post( $pub ) : null;

	$crumbs = '<nav class="crumbs" aria-label="Gjurma"><a href="' . esc_url( inpo_core_news_url() ) . '">Lajme</a>'
		. ( $cat ? '<span aria-hidden="true">/</span><a href="' . esc_url( get_category_link( $cat ) ) . '">' . esc_html( $cat->name ) . '</a>' : '' ) . '</nav>';

	$prose = ( has_excerpt( $post ) ? '<p class="lede">' . esc_html( wp_strip_all_tags( get_the_excerpt( $post ) ) ) . '</p>' : '' )
		. inpo_core_article_photo( $post )
		. inpo_core_the_content( $post )
		. ( get_post_meta( $post->ID, 'inpo_todo', true ) ? '<p class="todo">Teksti i plotë i reagimit, dokumentet dhe fotografitë importohen nga faqja aktuale gjatë migrimit.</p>' : '' );

	$aside = '<div class="box"><h2>Shpërndaje</h2><div class="share">'
		. '<button class="btn btn-line btn-sm" type="button" data-copy>Kopjo linkun</button>'
		. '<a class="btn btn-line btn-sm" target="_blank" rel="noopener" href="' . esc_url( 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( $url ) ) . '">Facebook</a>'
		. '<a class="btn btn-line btn-sm" target="_blank" rel="noopener" href="' . esc_url( 'https://x.com/intent/post?text=' . rawurlencode( $title ) . '&url=' . rawurlencode( $url ) ) . '">X</a>'
		. '</div></div>';
	if ( $pub && 'publish' === $pub->post_status ) {
		$aside .= '<a class="box" href="' . esc_url( get_permalink( $pub ) ) . '"><h2>Publikimi i plotë</h2><span style="font-weight:600;font-size:17px;line-height:1.35">' . inpo_core_title( $pub ) . '</span><span class="link-u">Shiko publikimin ' . inpo_core_icon( 'arrow' ) . '</span></a>';
	}
	$aside .= '<div class="box acc"><h2>Keni informata për këtë rast?</h2><p>Na dërgoni dokumente ose detaje. Ekipi ynë i monitorimit i shqyrton të gjitha.</p>'
		. '<a class="btn btn-sm" style="background:#FFFFFF;color:#0E0E1A;align-self:flex-start" href="' . esc_url( inpo_core_contact_url( array( 'tema' => 'tender' ) ) ) . '">Na shkruani</a></div>';

	return '<article ' . inpo_core_wrapper( 'wrap article' ) . '><div class="art-head">' . $crumbs . '<h1 data-focus>' . esc_html( $title ) . '</h1>'
		. '<div class="art-meta"><span class="pill">' . esc_html( $catname ) . '</span>' . inpo_core_time( $post ) . '<span>' . inpo_core_read_minutes( $post ) . ' min lexim</span></div></div>'
		. '<div class="grid12 art-body"><div class="prose">' . $prose . '</div><aside class="aside" aria-label="Shpërndaje dhe lidhje">' . $aside . '</aside></div></article>';
}

/* ---------- Programmes ---------- */

function inpo_core_render_program_page( $a ) {
	$term = is_tax( INPO_CORE_PROGRAM ) ? get_queried_object() : null;
	$all  = inpo_core_programs();
	if ( ! $term && $all ) {
		$term = $all[0]; // Editor preview.
	}
	if ( ! $term ) {
		return inpo_core_placeholder( __( 'The programme page appears here.', 'inpo-core' ) );
	}
	$icon = get_term_meta( $term->term_id, 'inpo_icon', true );
	$ways = get_term_meta( $term->term_id, 'inpo_ways', true );
	$html = '<section class="wrap page-head" style="display:block"><nav class="crumbs" aria-label="Gjurma" style="margin-bottom:22px"><span>Programet</span><span aria-hidden="true">/</span><span>' . esc_html( $term->name ) . '</span></nav>'
		. '<h1 class="h-page" data-focus>' . esc_html( $term->name ) . '</h1></section>'
		. '<section class="wrap"><div class="prog-hero">' . inpo_core_rings( 700, 4 ) . '<span class="prog-ico">' . inpo_core_icon( $icon ? $icon : $term->slug ) . '</span><p>' . esc_html( wp_strip_all_tags( $term->description ) ) . '</p></div></section>';

	$items = '';
	foreach ( (array) $ways as $way ) {
		if ( ! empty( $way[0] ) ) {
			$items .= '<div class="way"><h3>' . esc_html( $way[0] ) . '</h3><p>' . esc_html( isset( $way[1] ) ? $way[1] : '' ) . '</p></div>';
		}
	}
	if ( $items ) {
		$html .= '<section class="wrap sec"><h2 class="h2">Si punojmë</h2><div class="ways">' . $items . '</div></section>';
	}

	// The programme's main activities, as listed on the old programme page.
	$acts = array_filter( (array) get_term_meta( $term->term_id, 'inpo_activities', true ) );
	if ( $acts ) {
		$list = '';
		foreach ( array_values( $acts ) as $i => $act ) {
			$list .= '<li><span class="yr">' . sprintf( '%02d', $i + 1 ) . '</span><div><p>' . esc_html( $act ) . '</p></div></li>';
		}
		$html .= '<section class="wrap sec"><div class="grid12 sec-head"><span class="kicker">Programi</span><h2 class="h2">Aktivitetet kryesore</h2></div><ol class="timeline acts">' . $list . '</ol></section>';
	}

	$pubs = get_posts(
		array(
			'post_type'      => INPO_CORE_PUB,
			'posts_per_page' => 3,
			'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
			'tax_query'      => array( array( 'taxonomy' => INPO_CORE_PROGRAM, 'terms' => $term->term_id ) ),
		)
	);
	if ( $pubs ) {
		$cards = '';
		foreach ( $pubs as $i => $p ) {
			$cards .= inpo_core_pub_card( $p, $i );
		}
		$html .= '<section class="wrap sec"><div class="pub-head"><h2 class="h2">Publikime nga programi</h2><a class="btn btn-line btn-sm" href="' . esc_url( inpo_core_pubs_url( array( 'programi' => $term->slug ) ) ) . '">Të gjitha publikimet</a></div><div class="pubs three">' . $cards . '</div></section>';
	}

	// News from this programme; otherwise, if switched on, the latest news overall.
	$news = get_posts( array( 'posts_per_page' => 3, 'tax_query' => array( array( 'taxonomy' => INPO_CORE_PROGRAM, 'terms' => $term->term_id ) ) ) );
	$own  = (bool) $news;
	if ( ! $own && get_term_meta( $term->term_id, 'inpo_show_news', true ) ) {
		$news = get_posts( array( 'posts_per_page' => 3 ) );
	}
	if ( $news ) {
		$rows = '';
		foreach ( $news as $p ) {
			$rows .= inpo_core_news_row( $p );
		}
		$more  = $own ? add_query_arg( 'programi', $term->slug, inpo_core_news_url() ) : inpo_core_news_url();
		$html .= '<section class="wrap sec"><div class="pub-head"><h2 class="h2">' . ( $own ? 'Lajme nga programi' : 'Reagimet e fundit' ) . '</h2><a class="btn btn-line btn-sm" href="' . esc_url( $more ) . '">Të gjitha lajmet</a></div><div>' . $rows . '</div></section>';
	}

	if ( count( $all ) > 1 ) {
		$ids   = wp_list_pluck( $all, 'term_id' );
		$pos   = array_search( $term->term_id, $ids, true );
		$other = $all[ ( false === $pos ? 0 : $pos + 1 ) % count( $all ) ];
		$html .= '<section class="wrap sec"><a class="prog" href="' . esc_url( get_term_link( $other ) ) . '" style="min-height:0;flex-direction:row;align-items:center;justify-content:space-between"><div><span class="muted" style="font-size:14px">Programi tjetër</span><h3 style="margin:6px 0 0">' . esc_html( $other->name ) . '</h3></div><span class="arrow-circle">' . inpo_core_icon( 'arrow' ) . '</span></a></section>';
	}
	return '<div ' . inpo_core_wrapper( '' ) . '>' . $html . '</div>';
}

/* ---------- Shared sections ---------- */

/**
 * Staff (INPO > Team): photo, name, role, email.
 */
function inpo_core_render_team( $a ) {
	$people = get_posts(
		array(
			'post_type'      => INPO_CORE_MEMBER,
			'posts_per_page' => -1,
			'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
		)
	);
	if ( ! $people ) {
		return inpo_core_placeholder( __( 'Add people under INPO > Team.', 'inpo-core' ) );
	}
	$cards = '';
	foreach ( $people as $p ) {
		$name  = wp_strip_all_tags( get_the_title( $p ) );
		$role  = get_post_meta( $p->ID, 'inpo_role', true );
		$email = get_post_meta( $p->ID, 'inpo_email', true );
		if ( has_post_thumbnail( $p ) ) {
			$photo = get_the_post_thumbnail( $p, 'medium_large', array( 'alt' => $name, 'loading' => 'lazy', 'sizes' => '(max-width: 560px) 100vw, (max-width: 1100px) 50vw, 25vw' ) );
		} else {
			$initials = implode( '', array_map( function ( $w ) {
				return mb_substr( $w, 0, 1 );
			}, array_slice( preg_split( '/\s+/u', $name ), 0, 2 ) ) );
			$photo    = '<span class="member-initials" aria-hidden="true">' . esc_html( $initials ) . '</span>';
		}
		$cards .= '<div class="member"><div class="member-photo">' . $photo . '</div><h3>' . esc_html( $name ) . '</h3>'
			. ( $role ? '<p>' . esc_html( $role ) . '</p>' : '' )
			. ( $email ? '<a class="link-u" href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>' : '' ) . '</div>';
	}
	return '<section ' . inpo_core_wrapper( 'wrap sec' ) . '><h2 class="h2">' . esc_html( $a['heading'] ) . '</h2><div class="team">' . $cards . '</div></section>';
}

function inpo_core_render_donors( $a ) {
	$donors = get_posts(
		array(
			'post_type'      => INPO_CORE_DONOR,
			'posts_per_page' => -1,
			'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
		)
	);
	if ( ! $donors ) {
		return inpo_core_placeholder( __( 'Add donors under INPO > Donors.', 'inpo-core' ) );
	}
	$tiles = '';
	foreach ( $donors as $d ) {
		$name  = wp_strip_all_tags( get_the_title( $d ) );
		$inner = has_post_thumbnail( $d ) ? get_the_post_thumbnail( $d, 'medium', array( 'alt' => $name, 'loading' => 'lazy' ) ) : esc_html( $name );
		$url   = get_post_meta( $d->ID, 'inpo_url', true );
		$tiles .= $url
			? '<a class="donor" href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . $inner . '</a>'
			: '<div class="donor">' . $inner . '</div>';
	}
	$fine = inpo_core_settings( 'fineprint' );
	return '<section ' . inpo_core_wrapper( 'wrap sec' ) . '><span class="kicker">' . esc_html( $a['kicker'] ) . '</span><div class="donors">' . $tiles . '</div>'
		. ( $a['showFineprint'] && $fine ? '<p class="fineprint">' . esc_html( $fine ) . '</p>' : '' ) . '</section>';
}

function inpo_core_render_transparency( $a ) {
	$reports = get_posts(
		array(
			'post_type'      => INPO_CORE_PUB,
			'posts_per_page' => max( 1, (int) $a['auditCount'] ),
			'tax_query'      => array( array( 'taxonomy' => INPO_CORE_PUB_TYPE, 'field' => 'slug', 'terms' => inpo_core_audit_type_slug() ) ),
		)
	);
	$list = '';
	foreach ( $reports as $r ) {
		$list .= '<a href="' . esc_url( get_permalink( $r ) ) . '">' . inpo_core_title( $r ) . ' <span class="arrow-circle">' . inpo_core_icon( 'download' ) . '</span></a>';
	}
	$list .= '<a href="' . esc_url( inpo_core_pubs_url( array( 'lloji' => inpo_core_audit_type_slug() ) ) ) . '">' . esc_html( $a['allText'] ) . ' <span class="arrow-circle">' . inpo_core_icon( 'arrow' ) . '</span></a>';

	return '<section ' . inpo_core_wrapper( 'wrap sec' ) . '><h2 class="h2">' . esc_html( $a['heading'] ) . '</h2><div class="duo">'
		. '<div class="box" style="padding:36px"><h2 style="font-size:22px;letter-spacing:-.02em">' . esc_html( $a['auditTitle'] ) . '</h2><p class="muted" style="margin:0">' . esc_html( $a['auditText'] ) . '</p><div class="doc-list">' . $list . '</div></div>'
		. '<div class="box" style="padding:36px;background:var(--bg-soft);border-color:transparent"><h2 style="font-size:22px;letter-spacing:-.02em">' . esc_html( $a['jobsTitle'] ) . '</h2><p class="muted" style="margin:0">' . esc_html( $a['jobsText'] ) . '</p>'
		. '<button class="btn btn-ink btn-sm" style="align-self:flex-start;margin-top:auto" type="button" data-scroll="newsletter">' . esc_html( $a['jobsButton'] ) . '</button></div>'
		. '</div></section>';
}

/**
 * Honeypot field: hidden from people and assistive tech, filled by bots.
 */
function inpo_core_honeypot() {
	return '<div aria-hidden="true" style="position:absolute;left:-10000px;width:1px;height:1px;overflow:hidden"><label>Lëre bosh <input type="text" name="inpo_hp" tabindex="-1" autocomplete="off"></label></div>'
		. '<input type="hidden" name="inpo_back" value="' . esc_url( inpo_core_current_url() ) . '">';
}

/**
 * Absolute URL of the current request (works in subdirectory installs).
 */
function inpo_core_current_url() {
	$host = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : (string) wp_parse_url( home_url(), PHP_URL_HOST );
	$uri  = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/';
	return set_url_scheme( 'http://' . $host . $uri );
}

function inpo_core_render_newsletter( $a ) {
	$result = inpo_core_form_result( 'inpo_nl' );
	$check  = '<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12.5 4.5 4.5L19 7.5"/></svg>';
	if ( $result && $result['ok'] ) {
		$form = '<div class="nl-form"><div class="nl-done" role="status">' . $check . '<span>U abonuat me <b>' . esc_html( $result['email'] ) . '</b>. Buletini i parë vjen në fillim të muajit.</span></div></div>';
	} else {
		$error = $result ? $result['error'] : '';
		$form  = '<form class="nl-form" id="nl-form" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" novalidate data-inpo-newsletter>'
			. '<input type="hidden" name="action" value="inpo_subscribe">' . inpo_core_honeypot()
			. '<label for="nl-email">' . esc_html( $a['label'] ) . '</label>'
			. '<div class="nl-field"><input id="nl-email" name="email" type="email" autocomplete="email" placeholder="' . esc_attr( $a['placeholder'] ) . '" required' . ( $error ? ' aria-invalid="true" value="' . esc_attr( $result['email'] ) . '"' : '' ) . '>'
			. '<button type="submit">' . esc_html( $a['button'] ) . '</button></div>'
			. '<div class="nl-msg" id="nl-msg" role="status" aria-live="polite">' . esc_html( $error ) . '</div></form>';
	}
	return '<div ' . inpo_core_wrapper( 'wrap' ) . '><section class="nl grid12" id="newsletter" aria-labelledby="nl-h">'
		. '<svg class="rings" width="700" height="700" viewBox="0 0 700 700" aria-hidden="true"><circle cx="350" cy="350" r="120"/><circle cx="350" cy="350" r="190"/><circle cx="350" cy="350" r="260"/><circle cx="350" cy="350" r="330"/></svg>'
		. '<div class="nl-copy"><h2 id="nl-h">' . esc_html( $a['heading'] ) . '</h2><p>' . esc_html( $a['text'] ) . '</p></div>'
		. $form . '</section></div>';
}

/**
 * Office address lines for the contact card: "line 1,<br>rest, joined".
 */
function inpo_core_office_address( $address ) {
	$lines = array_values( array_filter( array_map( 'trim', preg_split( '/\R/', (string) $address ) ) ) );
	if ( ! $lines ) {
		return '';
	}
	$first = rtrim( array_shift( $lines ), ',' );
	return esc_html( $first ) . ( $lines ? ',<br>' . esc_html( implode( ', ', $lines ) ) : '' );
}

function inpo_core_render_contact( $a ) {
	$s       = inpo_core_settings();
	$topics  = inpo_core_contact_topics();
	$result  = inpo_core_form_result( 'inpo_contact' );
	$values  = $result ? $result['values'] : array();
	$errors  = $result ? $result['errors'] : array();
	$tel     = 'tel:' . preg_replace( '/[^0-9+]/', '', str_replace( '(0)', '', $s['phone'] ) );
	// phpcs:ignore WordPress.Security.NonceVerification
	$current = isset( $values['tema'] ) ? $values['tema'] : ( isset( $_GET['tema'] ) ? sanitize_key( inpo_core_str( wp_unslash( $_GET['tema'] ) ) ) : '' );
	if ( ! isset( $topics[ $current ] ) ) {
		$current = (string) key( $topics );
	}
	$val = function ( $key ) use ( $values ) {
		return isset( $values[ $key ] ) ? $values[ $key ] : '';
	};
	$err = function ( $key ) use ( $errors ) {
		return isset( $errors[ $key ] ) ? $errors[ $key ] : '';
	};
	$inv = function ( $key ) use ( $errors ) {
		return isset( $errors[ $key ] ) ? ' aria-invalid="true"' : '';
	};
	$cls = function ( $key ) use ( $errors ) {
		return isset( $errors[ $key ] ) ? 'field invalid' : 'field';
	};

	if ( $result && $result['ok'] ) {
		$main = '<div class="sent" tabindex="-1"><span class="check">' . inpo_core_icon( 'check' ) . '</span><h2>Faleminderit, ' . esc_html( $values['emri'] ) . '.</h2>'
			. '<p class="lead">Mesazhi u dërgua. Do t\'ju përgjigjemi në ' . esc_html( $values['email'] ) . '.</p>'
			. '<a class="btn btn-line" href="' . esc_url( remove_query_arg( 'inpo_contact' ) ) . '">Dërgo një mesazh tjetër</a></div>';
	} else {
		$radios = '';
		foreach ( $topics as $k => $label ) {
			$radios .= '<label><input type="radio" name="tema" value="' . esc_attr( $k ) . '"' . checked( $current, $k, false ) . '>' . esc_html( $label ) . '</label>';
		}
		$main = '<form class="form-card" id="c-form" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" novalidate data-inpo-contact>'
			. '<input type="hidden" name="action" value="inpo_contact">' . inpo_core_honeypot()
			. ( $err( 'form' ) ? '<p class="form-err" role="alert">' . esc_html( $err( 'form' ) ) . '</p>' : '<p class="form-err" role="alert" hidden></p>' )
			. '<div class="two">'
			. '<div class="' . $cls( 'emri' ) . '"><label for="c-emri">Emri</label><input id="c-emri" name="emri" autocomplete="given-name" required value="' . esc_attr( $val( 'emri' ) ) . '"' . $inv( 'emri' ) . ' aria-describedby="e-emri"><span class="err" id="e-emri">' . esc_html( $err( 'emri' ) ) . '</span></div>'
			. '<div class="field"><label for="c-mbiemri">Mbiemri <span class="opt">(opsionale)</span></label><input id="c-mbiemri" name="mbiemri" autocomplete="family-name" value="' . esc_attr( $val( 'mbiemri' ) ) . '"><span class="err"></span></div>'
			. '</div>'
			. '<div class="' . $cls( 'email' ) . '"><label for="c-email">Adresa e email-it</label><input id="c-email" name="email" type="email" autocomplete="email" placeholder="emri@shembull.com" required value="' . esc_attr( $val( 'email' ) ) . '"' . $inv( 'email' ) . ' aria-describedby="e-email"><span class="err" id="e-email">' . esc_html( $err( 'email' ) ) . '</span></div>'
			. '<fieldset class="field"><legend>Tema</legend><div class="topics">' . $radios . '</div></fieldset>'
			. '<div class="' . $cls( 'mesazhi' ) . '"><label for="c-msg">Mesazhi</label><textarea id="c-msg" name="mesazhi" rows="6" required' . $inv( 'mesazhi' ) . ' aria-describedby="e-msg">' . esc_textarea( $val( 'mesazhi' ) ) . '</textarea><span class="err" id="e-msg">' . esc_html( $err( 'mesazhi' ) ) . '</span></div>'
			. '<div class="field"><label class="consent"><input type="checkbox" id="c-ok" name="pelqim" value="1"' . checked( ! empty( $values['pelqim'] ), true, false ) . $inv( 'pelqim' ) . ' aria-describedby="e-ok">Pajtohem që të dhënat e mia të përdoren vetëm për t\'iu përgjigjur këtij mesazhi.</label><span class="err" id="e-ok">' . esc_html( $err( 'pelqim' ) ) . '</span></div>'
			. '<div class="form-foot"><span class="muted" style="font-size:14px">Ose shkruani direkt: <a class="link-u" href="mailto:' . esc_attr( $s['email'] ) . '">' . esc_html( $s['email'] ) . '</a></span><button class="btn btn-accent" type="submit">' . esc_html( $a['submit'] ) . '</button></div>'
			. '</form>';
	}

	$side = '<div class="side">'
		. '<div class="office a">' . inpo_core_rings( 360, 3 ) . '<small>' . esc_html( $s['office1'] ) . '</small><address>' . inpo_core_office_address( $s['address1'] ) . '</address>'
		. '<div class="office-foot"><a href="' . esc_attr( $tel ) . '">' . esc_html( $s['phone'] ) . '</a>' . ( $s['map1'] ? '<a class="btn btn-sm" target="_blank" rel="noopener" href="' . esc_url( $s['map1'] ) . '">Hap në hartë</a>' : '' ) . '</div></div>'
		. '<div class="office b"><small>' . esc_html( $s['office2'] ) . '</small><address>' . inpo_core_office_address( $s['address2'] ) . '</address>'
		. '<div class="office-foot"><a href="' . esc_attr( $tel ) . '">' . esc_html( $s['phone'] ) . '</a>' . ( $s['map2'] ? '<a class="btn btn-ink btn-sm" target="_blank" rel="noopener" href="' . esc_url( $s['map2'] ) . '">Hap në hartë</a>' : '' ) . '</div></div>'
		. '</div>';

	return '<section ' . inpo_core_wrapper( 'wrap grid12 contact', array( 'id' => 'kontakt' ) ) . '>' . $main . $side . '</section>';
}

/* ---------- Page head ---------- */

/**
 * Sources: custom (attributes), post (current page), archive, search, 404.
 */
function inpo_core_render_page_head( $a, $block ) {
	$kicker = $a['kicker'];
	$title  = $a['title'];
	$lead   = $a['lead'];
	$right  = '';
	$after  = '';

	switch ( $a['source'] ) {
		case 'post':
			$post = inpo_core_context_post( $block );
			if ( $post ) {
				$meta   = get_post_meta( $post->ID, 'inpo_kicker', true );
				$kicker = $meta ? $meta : $kicker;
				$title  = wp_strip_all_tags( get_the_title( $post ) );
				$lead   = has_excerpt( $post ) ? wp_strip_all_tags( get_the_excerpt( $post ) ) : $lead;
			}
			break;
		case 'archive':
			$title = wp_strip_all_tags( get_the_archive_title() );
			$desc  = wp_strip_all_tags( get_the_archive_description() );
			$lead  = $desc ? $desc : $lead;
			break;
		case 'search':
			$s      = get_search_query( false );
			$kicker = $kicker ? $kicker : 'Kërkimi';
			$title  = '' !== $s ? 'Rezultatet për “' . $s . '”' : 'Kërko në faqe';
			global $wp_query;
			$n     = (int) $wp_query->found_posts;
			$right = '<form class="search-inline" role="search" method="get" action="' . esc_url( home_url( '/' ) ) . '">' . inpo_core_icon( 'search' )
				. '<label for="q-page" class="sr">Kërko lajme dhe publikime</label><input id="q-page" type="search" name="s" value="' . esc_attr( $s ) . '" placeholder="Kërko lajme dhe publikime"></form>'
				. ( '' !== $s ? '<p class="muted" style="margin:14px 0 0;font-size:14px">' . ( 1 === $n ? '1 rezultat' : $n . ' rezultate' ) . '</p>' : '' );
			$lead  = '';
			break;
		case '404':
			$kicker = $kicker ? $kicker : 'Gabim 404';
			$title  = $title ? $title : 'Faqja nuk u gjet.';
			$lead   = $lead ? $lead : 'Linku mund të jetë i vjetër ose i shkruar gabim. Kthehuni në kryefaqe ose kërkoni atë që ju duhet.';
			$after  = '<section class="wrap"><div class="hero-ctas" style="margin-top:40px"><a class="btn btn-accent" href="' . esc_url( home_url( '/' ) ) . '">Kryefaqja</a><button class="btn btn-line" type="button" data-open-search>Kërko në faqe</button></div></section>';
			break;
	}
	if ( '' === $title ) {
		$title = get_bloginfo( 'name' );
	}
	if ( ! $right && $lead ) {
		$right = '<p class="lead">' . esc_html( $lead ) . '</p>';
	}
	$head = '<section class="wrap grid12 page-head"><div class="ph-l">' . ( $kicker ? '<span class="kicker">' . esc_html( $kicker ) . '</span>' : '' )
		. '<h1 class="h-page" data-focus>' . esc_html( $title ) . '</h1></div><div class="ph-r">' . $right . '</div></section>';
	return '<div ' . inpo_core_wrapper( '' ) . '>' . $head . $after . '</div>';
}
