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
		. '<h1 class="h-display" data-focus><span class="line"><span>' . esc_html( $a['line1'] ) . '</span></span><span class="line accent"><span>' . esc_html( $a['line2'] ) . '</span></span></h1>'
		. '<div class="hero-side"><p class="lead">' . esc_html( $a['lead'] ) . '</p><div class="hero-ctas">' . $btns . '</div></div>'
		. inpo_core_hero_photo( $a )
		. inpo_core_hero_areas( $a )
		. '</section>';
}

/**
 * The hero photo: an image from Media, or the bundled photo of the report launch (February 2025).
 */
function inpo_core_hero_photo( $a ) {
	if ( empty( $a['showImage'] ) ) {
		return '';
	}
	$src = $a['image'] ? $a['image'] : INPO_CORE_URL . 'assets/photos/hero-raporti-integriteti.jpg';
	$cap = trim( (string) $a['imageCaption'] );
	return '<figure class="hero-photo"><img src="' . esc_url( $src ) . '" alt="' . esc_attr( $cap ) . '" width="960" height="1200" fetchpriority="high" decoding="async">'
		. ( $cap ? '<figcaption>' . esc_html( $cap ) . '</figcaption>' : '' ) . '</figure>';
}

/**
 * Who we are, with photos from INPO's work in the field (lines "image URL | caption"; empty = bundled photos).
 * Mirrors fieldHTML() in the prototype's app.js.
 */
function inpo_core_render_field( $a ) {
	$photos = array();
	foreach ( array_filter( array_map( 'trim', preg_split( '/\R/', (string) $a['photos'] ) ) ) as $line ) {
		$parts    = array_map( 'trim', explode( '|', $line, 2 ) );
		$photos[] = array( $parts[0], isset( $parts[1] ) ? $parts[1] : '' );
	}
	if ( ! $photos ) {
		$base = INPO_CORE_URL . 'assets/photos/';
		foreach ( array(
			'java-kunder-korrupsionit.jpg' => 'Java kundër korrupsionit, dhjetor 2023',
			'takim-prizren.jpg'            => 'Takim me nënkryetarin e Komunës së Prizrenit, tetor 2023',
			'takim-gjakove.jpg'            => 'Takim me nënkryetarin e Komunës së Gjakovës, tetor 2023',
			'tryeze-diskutimi.jpg'         => 'Tryezë diskutimi me IKD-në dhe Lëvizjen FOL, shkurt 2024',
			'raporti-mosplanifikimi.jpg'   => 'Prezantimi i raportit “(Mos)Planifikimi i Prokurimit”, nëntor 2023',
		) as $file => $cap ) {
			$photos[] = array( $base . $file, $cap );
		}
	}
	$figs = '';
	foreach ( $photos as $i => $p ) {
		$figs .= '<figure class="fp' . ( 0 === $i ? ' big' : '' ) . '"><img src="' . esc_url( $p[0] ) . '" alt="' . esc_attr( $p[1] ) . '" width="' . ( 0 === $i ? 1280 : 800 ) . '" height="' . ( 0 === $i ? 800 : 600 ) . '" loading="lazy" decoding="async">'
			. ( $p[1] ? '<figcaption>' . esc_html( $p[1] ) . '</figcaption>' : '' ) . '</figure>';
	}
	$url = $a['buttonUrl'] ? $a['buttonUrl'] : inpo_core_page_url( 'rreth-nesh' );
	return '<section ' . inpo_core_wrapper( 'wrap sec field', array( 'aria-labelledby' => 'h-field' ) ) . '><div class="grid12 field-grid"><div class="field-intro">'
		. ( $a['kicker'] ? '<span class="kicker">' . esc_html( $a['kicker'] ) . '</span>' : '' )
		. '<h2 class="h2" id="h-field">' . esc_html( $a['heading'] ) . '</h2>'
		. ( $a['text'] ? '<p class="lead">' . esc_html( $a['text'] ) . '</p>' : '' )
		. ( $a['buttonText'] ? '<a class="btn btn-line" href="' . esc_url( $url ) . '">' . esc_html( $a['buttonText'] ) . '</a>' : '' )
		. '</div><div class="field-photos">' . $figs . '</div></div></section>';
}

/**
 * Fields of action (INPO's brief: mission and fields of action on the home page).
 * Typed as lines "Label | link"; empty = the two programmes and three thematic fields.
 */
function inpo_core_hero_areas( $a ) {
	$items = array();
	foreach ( array_filter( array_map( 'trim', preg_split( '/\R/', (string) $a['areas'] ) ) ) as $line ) {
		$parts   = array_map( 'trim', explode( '|', $line, 2 ) );
		$items[] = array( $parts[0], isset( $parts[1] ) ? $parts[1] : '' );
	}
	if ( ! $items ) {
		$prog  = function ( $slug ) {
			$t = get_term_by( 'slug', $slug, INPO_CORE_PROGRAM );
			return $t ? get_term_link( $t ) : '';
		};
		$field = function ( $slug, $label ) {
			return term_exists( $slug, INPO_CORE_THEME ) ? inpo_core_pubs_url( array( 'fusha' => $slug ) ) : inpo_core_pubs_url( array( 'q' => $label ) );
		};
		$items = array(
			array( 'Qeverisja e mirë', $prog( 'qeverisje' ) ),
			array( 'Transparenca', $field( 'transparenca', 'transparenc' ) ),
			array( 'Llogaridhënia', $field( 'llogaridhenia', 'llogaridh' ) ),
			array( 'Sundimi i ligjit', $field( 'sundimi-i-ligjit', 'ligj' ) ),
			array( 'Edukimi demokratik', $prog( 'edukim' ) ),
		);
	}
	$list = '';
	foreach ( $items as $item ) {
		$url   = is_wp_error( $item[1] ) ? '' : $item[1];
		$list .= '<li>' . ( $url ? '<a href="' . esc_url( $url ) . '">' . esc_html( $item[0] ) . '</a>' : '<span>' . esc_html( $item[0] ) . '</span>' ) . '</li>';
	}
	return '<div class="areas"><span class="areas-k" id="areas-k">' . esc_html( $a['areasLabel'] ) . '</span><ul aria-labelledby="areas-k">' . $list . '</ul></div>';
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
	$head = '';
	if ( $a['heading'] ) {
		$link = $a['linkUrl'] ? $a['linkUrl'] : add_query_arg( 'kategoria', 'reagime-dhe-qendrime-publike', inpo_core_news_url() );
		$head = '<div class="bento-head"><h2 class="kicker" id="h-gjetjet">' . esc_html( $a['heading'] ) . '</h2>'
			. ( $a['linkText'] ? '<a class="link-u" href="' . esc_url( $link ) . '">' . esc_html( $a['linkText'] ) . ' ' . inpo_core_icon( 'arrow' ) . '</a>' : '' ) . '</div>';
	}
	$attr = $head ? array( 'aria-labelledby' => 'h-gjetjet' ) : array( 'aria-label' => $a['label'] );
	return '<section ' . inpo_core_wrapper( 'wrap', $attr ) . '>' . $head . '<div class="bento">' . $html . '</div></section>';
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
		} elseif ( inpo_core_hidden_type_ids() ) {
			$tax[] = array( 'taxonomy' => INPO_CORE_PUB_TYPE, 'terms' => inpo_core_hidden_type_ids(), 'operator' => 'NOT IN' );
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
		$visible = array_filter(
			inpo_core_pub_types(),
			function ( $t ) {
				return ! in_array( (int) $t->term_id, inpo_core_hidden_type_ids(), true );
			}
		);
		foreach ( array_slice( $visible, 0, 3 ) as $t ) {
			$chips .= '<a class="chip" href="' . esc_url( inpo_core_pubs_url( array( 'lloji' => $t->slug ) ) ) . '">' . esc_html( inpo_core_type_plural( $t ) ) . '</a>';
		}
		$head = '<div class="pub-head"><div class="pub-head-l">'
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
 * Library: search, programme, year, order, type chips with counts, then the results (see filters.php).
 * On the publication archive it renders the main query; elsewhere it builds the same query from the URL.
 */
function inpo_core_render_publication_library( $a ) {
	global $wp_query;
	if ( is_post_type_archive( INPO_CORE_PUB ) && $wp_query->is_main_query() && ! wp_is_serving_rest_request() ) {
		$query = $wp_query;
	} else {
		$query = new WP_Query( inpo_core_filter_query_vars( 'pub', inpo_core_filter_state( 'pub' ) ) + array( 'posts_per_page' => 12, 'paged' => max( 1, (int) get_query_var( 'paged' ) ) ) );
	}
	if ( $query->have_posts() ) {
		$cards = '';
		$start = ( max( 1, (int) $query->get( 'paged' ) ) - 1 ) * (int) $query->get( 'posts_per_page' );
		foreach ( $query->posts as $i => $post ) {
			$cards .= inpo_core_pub_card( $post, $start + $i );
		}
		$cards = '<div class="pubs three">' . $cards . '</div>';
	} else {
		$cards = inpo_core_filter_empty( 'pub' );
	}
	return '<section ' . inpo_core_wrapper( 'wrap' ) . '>'
		. inpo_core_filters_html( 'pub', (int) $query->found_posts, $a )
		. '<div class="results" id="pub-results">' . $cards . inpo_core_pager( $query ) . '</div>'
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

	// INPO's brief: full title, publication date, author or research team, programme and thematic field.
	$fact    = function ( $label, $value ) {
		return '' === $value ? '' : '<div><dt>' . esc_html( $label ) . '</dt><dd>' . $value . '</dd></div>';
	};
	$links   = function ( $taxonomy, $param ) use ( $post ) {
		$terms = wp_get_object_terms( $post->ID, $taxonomy );
		$out   = array();
		foreach ( is_wp_error( $terms ) ? array() : $terms as $t ) {
			$out[] = '<a href="' . esc_url( inpo_core_pubs_url( array( $param => $t->slug ) ) ) . '">' . esc_html( $t->name ) . '</a>';
		}
		return implode( ', ', $out );
	};
	$authors = get_post_meta( $post->ID, 'inpo_authors', true );
	$facts   = '<dl class="facts">'
		. $fact( 'Kategoria', esc_html( inpo_core_type_label( $type ) ) )
		. $fact( 'Data e publikimit', '<time datetime="' . esc_attr( get_the_date( 'Y-m-d', $post ) ) . '">' . esc_html( get_post_meta( $post->ID, 'inpo_year', true ) ? $year : get_the_date( 'd.m.Y', $post ) ) . '</time>' )
		. $fact( 'Autori ose ekipi', esc_html( $authors ) )
		. $fact( 'Programi', $program ? '<a href="' . esc_url( get_term_link( $program ) ) . '">' . esc_html( $program->name ) . '</a>' : 'INPO' )
		. $fact( 'Fusha tematike', $links( INPO_CORE_THEME, 'fusha' ) )
		. $fact( 'Institucioni', $links( INPO_CORE_INST, 'institucioni' ) )
		. $fact( 'Partnerë', esc_html( $partners ) )
		. '</dl>';

	// An infographic made of images opens on its images (the section below), not on a copy of the first one.
	$slides = inpo_core_slide_ids( $post );
	$html   = '<section class="wrap grid12 pd' . ( $slides ? ' no-cover' : '' ) . '">' . ( $slides ? '' : $cover )
		. '<div class="pd-info">' . $crumbs . '<h1 data-focus>' . inpo_core_title( $post ) . '</h1>' . $facts
		. ( $lead ? '<p class="lead">' . esc_html( $lead ) . '</p>' : $hint )
		. '<div class="hero-ctas">' . $download . '<button class="btn btn-line" type="button" data-copy>Kopjo linkun</button></div>'
		. ( $donor ? '<p class="muted" style="margin:0;font-size:14px">Me mbështetjen e ' . esc_html( $donor ) . '.</p>' : '' )
		. '</div></section>';

	if ( trim( $post->post_content ) ) {
		$html .= '<section class="wrap sec grid12"><div class="prose">' . inpo_core_the_content( $post ) . '</div></section>';
	}
	$html .= inpo_core_pub_slides( $post );
	$html .= inpo_core_pub_data( $post );
	return '<div ' . inpo_core_wrapper( '' ) . '>' . $html . '</div>';
}

/**
 * An infographic's images: a row to swipe, arrows, a counter and a full-screen view (assets/slides.js).
 * Mirrors slidesHTML() in the prototype's app.js.
 */
function inpo_core_pub_slides( $post ) {
	$ids = inpo_core_slide_ids( $post );
	if ( ! $ids ) {
		return '';
	}
	wp_enqueue_script( 'inpo-core-slides' );
	$n     = count( $ids );
	$pad   = function ( $x ) {
		return str_pad( (string) $x, 2, '0', STR_PAD_LEFT );
	};
	$arrow = function ( $d ) {
		return '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="' . $d . '"/></svg>';
	};
	$title = wp_strip_all_tags( get_the_title( $post ) );
	$items = '';
	foreach ( $ids as $i => $id ) {
		$full = wp_get_attachment_image_url( $id, 'full' );
		$alt  = trim( (string) get_post_meta( $id, '_wp_attachment_image_alt', true ) );
		$alt  = $alt ? $alt : $title . ': pamja ' . ( $i + 1 ) . ' nga ' . $n;
		$img  = wp_get_attachment_image( $id, 'large', false, array( 'alt' => $alt, 'loading' => $i < 2 ? 'eager' : 'lazy', 'decoding' => 'async', 'sizes' => '(max-width: 640px) 88vw, 620px' ) );
		$items .= '<figure class="slide" role="listitem"><a href="' . esc_url( $full ) . '" aria-label="' . esc_attr( 'Hap në ekran të plotë: pamja ' . ( $i + 1 ) . ' nga ' . $n ) . '">' . $img . '</a></figure>';
	}
	return '<section class="wrap sec slides-sec" aria-labelledby="h-slides" data-slides><div class="pub-head"><h2 class="h2" id="h-slides">Infografika</h2>'
		. '<div class="slides-nav"><span class="slides-count" aria-hidden="true">01 / ' . $pad( $n ) . '</span>'
		. '<button class="icon-btn" type="button" data-slides-go="-1" aria-label="Pamja e mëparshme">' . $arrow( 'm15 18-6-6 6-6' ) . '</button>'
		. '<button class="icon-btn" type="button" data-slides-go="1" aria-label="Pamja tjetër">' . $arrow( 'm9 18 6-6-6-6' ) . '</button></div></div>'
		. '<div class="slides" role="list">' . $items . '</div></section>';
}

/**
 * An infographic's numbers on its page: key figures, then each chart (interactive, with a table and CSV).
 * Mirrors pubData() in the prototype's app.js.
 */
function inpo_core_pub_data( $post ) {
	$tables  = inpo_core_csv_tables( (string) get_post_meta( $post->ID, 'inpo_csv', true ) );
	$figures = inpo_core_figures( inpo_core_lines( $post->ID, 'inpo_figures' ) );
	if ( ! $tables && ! $figures ) {
		return '';
	}
	$charts = '';
	foreach ( $tables as $k => $t ) {
		$charts .= '<div class="pub-chart">' . ( $t['title'] ? '<h3 class="sub-h">' . esc_html( $t['title'] ) . '</h3>' : '' )
			. inpo_core_chart_view( $t, $post->post_name . '-' . ( $k + 1 ) ) . '</div>';
	}
	$source = get_post_meta( $post->ID, 'inpo_source', true );
	return '<section class="wrap sec" aria-labelledby="h-data"><div class="grid12 sec-head"><span class="kicker">Infografika në numra</span><h2 class="h2" id="h-data">Të dhënat e infografikës</h2></div>'
		. ( $figures ? '<div class="pub-figures">' . inpo_core_impact_grid( $figures, 'plain' ) . '</div>' : '' )
		. $charts . ( $source ? '<p class="fineprint">Burimi: ' . esc_html( $source ) . '</p>' : '' ) . '</section>';
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
			. ( $a['lead'] ? '<p class="lead">' . esc_html( $a['lead'] ) . '</p>' : '' )
			. ( $a['linkText'] ? $link : '' ) . '</div><div class="news-list">' . $rows . '</div></section>';
	}
	if ( 'section' === $a['layout'] ) {
		return '<section ' . inpo_core_wrapper( 'wrap sec' ) . '><div class="pub-head"><h2 class="h2">' . esc_html( $a['heading'] ) . '</h2>' . ( $a['linkText'] ? $link : '' ) . '</div><div>' . $rows . '</div></section>';
	}
	return '<section ' . inpo_core_wrapper( 'wrap list-plain' ) . '><div>' . $rows . '</div>' . $pager . '</section>';
}

/**
 * News page: search, programme, year, order, category chips with counts, then the cards.
 * Uses the page's own query (posts page, category archive), filtered in pre_get_posts.
 */
function inpo_core_render_news_grid( $a ) {
	global $wp_query;
	$query = $wp_query;
	if ( wp_is_serving_rest_request() || ! ( $wp_query->is_home() || $wp_query->is_archive() ) ) {
		$query = new WP_Query( array( 'posts_per_page' => 9, 'ignore_sticky_posts' => true ) );
	}
	$state = inpo_core_filter_state( 'news' );
	// The first card is large on the plain list (page 1, no search or reordering).
	$plain = max( 1, (int) get_query_var( 'paged' ) ) === 1 && '' === $state['q'] && '' === $state['programi'] && ! $state['viti'] && '' === $state['rendit'];
	$cards = '';
	foreach ( $query->posts as $i => $post ) {
		$big    = $plain && 0 === $i;
		$cards .= '<a class="ncard' . ( $big ? ' big' : '' ) . '" href="' . esc_url( get_permalink( $post ) ) . '">' . ( $big ? inpo_core_rings( 560, 4 ) : '' )
			. '<div class="ncard-meta"><span class="pill">' . esc_html( inpo_core_post_category_name( $post ) ) . '</span>' . inpo_core_time( $post ) . '</div>'
			. '<div><h2>' . inpo_core_title( $post ) . '</h2><p>' . esc_html( inpo_core_excerpt( $post ) ) . '</p></div></a>';
	}
	$cards = $cards ? '<div class="news-grid">' . $cards . '</div>' : inpo_core_filter_empty( 'news' );
	return '<section ' . inpo_core_wrapper( 'wrap' ) . '>'
		. inpo_core_filters_html( 'news', (int) $query->found_posts, $a )
		. '<div class="results" id="news-results">' . $cards . inpo_core_pager( $query ) . '</div>'
		. '</section>';
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
	// INPO's brief: objectives, areas of intervention, activities, research and results, on one page.
	$head = function ( $kicker, $title ) {
		return '<div class="grid12 sec-head"><span class="kicker">' . esc_html( $kicker ) . '</span><h2 class="h2">' . esc_html( $title ) . '</h2></div>';
	};
	$by_program = array( array( 'taxonomy' => INPO_CORE_PROGRAM, 'terms' => $term->term_id ) );
	$icon       = get_term_meta( $term->term_id, 'inpo_icon', true );
	$html       = '<section class="wrap page-head" style="display:block"><nav class="crumbs" aria-label="Gjurma" style="margin-bottom:22px"><span>Programet</span><span aria-hidden="true">/</span><span>' . esc_html( $term->name ) . '</span></nav>'
		. '<h1 class="h-page" data-focus>' . esc_html( $term->name ) . '</h1></section>'
		. '<section class="wrap"><div class="prog-hero">' . inpo_core_rings( 700, 4 ) . '<span class="prog-ico">' . inpo_core_icon( $icon ? $icon : $term->slug ) . '</span><p>' . esc_html( wp_strip_all_tags( $term->description ) ) . '</p></div></section>';

	$goals = inpo_core_lines( $term->term_id, 'inpo_objectives', true );
	if ( $goals ) {
		$list = '';
		foreach ( $goals as $i => $goal ) {
			$list .= '<li><span>' . sprintf( '%02d', $i + 1 ) . '</span>' . esc_html( $goal ) . '</li>';
		}
		$html .= '<section class="wrap sec">' . $head( 'Objektivat', 'Çfarë synon programi' ) . '<ol class="goals">' . $list . '</ol></section>';
	}

	$items = '';
	foreach ( (array) get_term_meta( $term->term_id, 'inpo_ways', true ) as $way ) {
		if ( ! empty( $way[0] ) ) {
			$items .= '<div class="way"><h3>' . esc_html( $way[0] ) . '</h3><p>' . esc_html( isset( $way[1] ) ? $way[1] : '' ) . '</p></div>';
		}
	}
	if ( $items ) {
		$html .= '<section class="wrap sec">' . $head( 'Fushat e ndërhyrjes', 'Ku ndërhyjmë' ) . '<div class="ways">' . $items . '</div></section>';
	}

	$acts = array_filter( (array) get_term_meta( $term->term_id, 'inpo_activities', true ) );
	if ( $acts ) {
		$list = '';
		foreach ( array_values( $acts ) as $i => $act ) {
			$list .= '<li><span class="yr">' . sprintf( '%02d', $i + 1 ) . '</span><div><p>' . esc_html( $act ) . '</p></div></li>';
		}
		$html .= '<section class="wrap sec">' . $head( 'Aktivitetet', 'Aktivitetet kryesore' ) . '<ol class="timeline acts">' . $list . '</ol></section>';
	}

	$tax_pubs = $by_program;
	if ( inpo_core_hidden_type_ids() ) {
		$tax_pubs[] = array( 'taxonomy' => INPO_CORE_PUB_TYPE, 'terms' => inpo_core_hidden_type_ids(), 'operator' => 'NOT IN' );
	}
	$pubs = new WP_Query( array( 'post_type' => INPO_CORE_PUB, 'post_status' => 'publish', 'posts_per_page' => 3, 'orderby' => array( 'menu_order' => 'ASC', 'date' => 'DESC' ), 'tax_query' => $tax_pubs ) );
	if ( $pubs->posts ) {
		$cards = '';
		foreach ( $pubs->posts as $i => $p ) {
			$cards .= inpo_core_pub_card( $p, $i );
		}
		$html .= '<section class="wrap sec"><div class="pub-head"><div class="pub-head-l"><span class="kicker">Hulumtimet</span><h2 class="h2">Hulumtime dhe publikime nga programi</h2></div>'
			. '<a class="btn btn-line btn-sm" href="' . esc_url( inpo_core_pubs_url( array( 'programi' => $term->slug ) ) ) . '">Të gjitha (' . (int) $pubs->found_posts . ')</a></div><div class="pubs three">' . $cards . '</div></section>';
	}

	// Results: counted from what is linked to the programme, plus figures typed in (number | label).
	$news_q  = new WP_Query( array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 3, 'tax_query' => $by_program ) );
	$projs_q = new WP_Query( array( 'post_type' => INPO_CORE_PROJECT, 'post_status' => 'publish', 'posts_per_page' => 3, 'orderby' => array( 'menu_order' => 'ASC', 'date' => 'DESC' ), 'tax_query' => $by_program ) );
	$figures = array();
	foreach ( array( array( $pubs->found_posts, 'hulumtime dhe publikime' ), array( $projs_q->found_posts, 'projekte' ), array( $news_q->found_posts, 'lajme, reagime dhe aktivitete' ) ) as $f ) {
		if ( $f[0] ) {
			$figures[] = array( number_format_i18n( $f[0] ), $f[1] );
		}
	}
	$figures = array_merge( $figures, inpo_core_figures( inpo_core_lines( $term->term_id, 'inpo_results', true ) ) );
	if ( $figures ) {
		$html .= '<section class="wrap sec"><div class="impact light"><div class="impact-head"><span class="kicker">Rezultatet</span><h2 class="h2">Rezultatet e programit</h2></div>' . inpo_core_impact_grid( $figures ) . '</div></section>';
	}

	if ( $projs_q->posts ) {
		$cards = '';
		foreach ( $projs_q->posts as $p ) {
			$cards .= inpo_core_project_card( $p );
		}
		$html .= '<section class="wrap sec"><div class="pub-head"><div class="pub-head-l"><span class="kicker">Projektet</span><h2 class="h2">Projektet e programit</h2></div><a class="btn btn-line btn-sm" href="' . esc_url( inpo_core_projects_url() ) . '">Të gjitha projektet</a></div><div class="projs">' . $cards . '</div></section>';
	}

	// News from this programme; otherwise, if switched on, the latest news overall.
	$news = $news_q->posts;
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
		$html .= '<section class="wrap sec"><div class="pub-head"><h2 class="h2">' . ( $own ? 'Lajme dhe reagime nga programi' : 'Reagimet e fundit' ) . '</h2><a class="btn btn-line btn-sm" href="' . esc_url( $more ) . '">Të gjitha lajmet</a></div><div>' . $rows . '</div></section>';
	}

	if ( count( $all ) > 1 ) {
		$ids   = wp_list_pluck( $all, 'term_id' );
		$pos   = array_search( $term->term_id, $ids, true );
		$other = $all[ ( false === $pos ? 0 : $pos + 1 ) % count( $all ) ];
		$html .= '<section class="wrap sec"><a class="prog prog-next" href="' . esc_url( get_term_link( $other ) ) . '"><div><span class="muted" style="font-size:14px">Programi tjetër</span><h3 style="margin:6px 0 0">' . esc_html( $other->name ) . '</h3></div><span class="arrow-circle">' . inpo_core_icon( 'arrow' ) . '</span></a></section>';
	}
	return '<div ' . inpo_core_wrapper( '' ) . '>' . $html . '</div>';
}

/* ---------- Shared sections ---------- */

/**
 * Team and board (INPO > Team): photo, name, role, email. Members marked "Board" form their own group.
 * "wrapped" off = only the groups, for a section the page itself titles (About: #ekipi).
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
	$groups = array( 'ekipi' => '', 'bordi' => '' );
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
		$group = 'bordi' === get_post_meta( $p->ID, 'inpo_group', true ) ? 'bordi' : 'ekipi';
		// The board: names and roles in a ruled list, no photos.
		if ( 'bordi' === $group ) {
			$groups['bordi'] .= '<li' . ( preg_match( '/^Kryesues/u', (string) $role ) ? ' class="chair"' : '' ) . '><span class="board-n">' . esc_html( $name ) . '</span>' . ( $role ? '<span class="board-r">' . esc_html( $role ) . '</span>' : '' ) . '</li>';
			continue;
		}
		$groups[ $group ] .= '<div class="member"><div class="member-photo">' . $photo . '</div><h4>' . esc_html( $name ) . '</h4>'
			. ( $role ? '<p>' . esc_html( $role ) . '</p>' : '' )
			. ( $email ? '<a class="link-u" href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>' : '' ) . '</div>';
	}
	$html = '';
	foreach ( array( 'ekipi' => $a['teamLabel'], 'bordi' => $a['boardLabel'] ) as $key => $label ) {
		if ( $groups[ $key ] ) {
			$list  = 'bordi' === $key ? '<ul class="board">' . $groups[ $key ] . '</ul>' : '<div class="team">' . $groups[ $key ] . '</div>';
			$html .= ( $label ? '<h3 class="sub-h">' . esc_html( $label ) . '</h3>' : '' ) . $list;
		}
	}
	if ( ! $a['wrapped'] ) {
		return '<div ' . inpo_core_wrapper( 'team-groups' ) . '>' . $html . '</div>';
	}
	$extra = $a['anchor'] ? array( 'id' => $a['anchor'] ) : array();
	return '<section ' . inpo_core_wrapper( 'wrap sec' . ( $a['anchor'] ? ' anchor' : '' ), $extra ) . '>' . ( $a['heading'] ? '<h2 class="h2">' . esc_html( $a['heading'] ) . '</h2>' : '' ) . $html . '</section>';
}

function inpo_core_render_donors( $a ) {
	$tiles = inpo_core_donor_tiles( false );
	if ( ! $tiles ) {
		return inpo_core_placeholder( __( 'Add donors under INPO > Donors.', 'inpo-core' ) );
	}
	$fine  = inpo_core_settings( 'fineprint' );
	$extra = $a['anchor'] ? array( 'id' => $a['anchor'] ) : array();
	$head  = $a['heading']
		? '<div class="grid12 sec-head"><span class="kicker">' . esc_html( $a['kicker'] ) . '</span><h2 class="h2">' . esc_html( $a['heading'] ) . '</h2></div>'
		: '<span class="kicker">' . esc_html( $a['kicker'] ) . '</span>';
	return '<section ' . inpo_core_wrapper( 'wrap sec' . ( $a['anchor'] ? ' anchor' : '' ), $extra ) . '>' . $head . $tiles
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
		. '<div class="box" style="padding:32px"><h2 style="font-size:var(--fs-card);letter-spacing:-.02em">' . esc_html( $a['auditTitle'] ) . '</h2><p class="muted" style="margin:0">' . esc_html( $a['auditText'] ) . '</p><div class="doc-list">' . $list . '</div></div>'
		. '<div class="box" style="padding:32px;background:var(--bg-soft);border-color:transparent"><h2 style="font-size:var(--fs-card);letter-spacing:-.02em">' . esc_html( $a['jobsTitle'] ) . '</h2><p class="muted" style="margin:0">' . esc_html( $a['jobsText'] ) . '</p>'
		. '<a class="btn btn-ink btn-sm" style="align-self:flex-start;margin-top:auto" href="' . esc_url( inpo_core_contact_url() ) . '">' . esc_html( $a['jobsButton'] ) . '</a></div>'
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
	// INPO decided against the email signup (Oct 2026): the block stays registered so saved pages keep working, but shows nothing.
	if ( ! apply_filters( 'inpo_core_show_newsletter', false ) ) {
		return '';
	}
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
		. '<div class="nl-copy"><h2 id="nl-h">' . esc_html( $a['heading'] ) . '</h2><p>' . esc_html( $a['text'] ) . '</p>' . inpo_core_social_links() . '</div>'
		. $form . '</section></div>';
}

/**
 * "Na ndiqni" links on the newsletter panel (INPO's brief: subscribe and follow on social networks).
 */
function inpo_core_social_links() {
	$s     = inpo_core_settings();
	$links = '';
	foreach ( array( 'facebook' => 'Facebook', 'x' => 'X', 'instagram' => 'Instagram', 'linkedin' => 'LinkedIn', 'youtube' => 'YouTube' ) as $key => $label ) {
		if ( ! empty( $s[ $key ] ) ) {
			$links .= '<a href="' . esc_url( $s[ $key ] ) . '" target="_blank" rel="noopener">' . esc_html( $label ) . '</a>';
		}
	}
	return $links ? '<div class="nl-social"><span>Na ndiqni</span>' . $links . '</div>' : '';
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

/* ---------- INPO's brief: results and impact, projects, documents, data ---------- */

/**
 * "Në këtë faqe": links to the sections of a page.
 *
 * @param array[] $items [ id, label ].
 */
function inpo_core_subnav( $items, $label = 'Në këtë faqe', $aria = 'Seksionet e faqes' ) {
	$links = '';
	foreach ( $items as $item ) {
		$links .= '<a href="#' . esc_attr( $item[0] ) . '">' . esc_html( $item[1] ) . '</a>';
	}
	return $links ? '<nav class="subnav" aria-label="' . esc_attr( $aria ) . '"><span class="subnav-k" aria-hidden="true">' . esc_html( $label ) . '</span><span class="subnav-l">' . $links . '</span></nav>' : '';
}

/**
 * Lines "id | Label" as [ id, label ] pairs.
 */
function inpo_core_pairs( $text ) {
	$out = array();
	foreach ( array_filter( array_map( 'trim', preg_split( '/\R/', (string) $text ) ) ) as $line ) {
		$parts = array_map( 'trim', explode( '|', $line, 2 ) );
		if ( 2 === count( $parts ) ) {
			$out[] = array( sanitize_title( $parts[0] ), $parts[1] );
		}
	}
	return $out;
}

function inpo_core_render_section_links( $a ) {
	$nav = inpo_core_subnav( inpo_core_pairs( $a['items'] ), $a['label'] );
	return $nav ? '<section ' . inpo_core_wrapper( 'wrap' ) . '>' . $nav . '</section>' : inpo_core_placeholder( __( 'Add links as lines "anchor | Label", e.g. historia | Historia e organizatës.', 'inpo-core' ) );
}

/**
 * Results and impact: up to four figures. Each counts real content or shows a typed number.
 */
function inpo_core_render_impact( $a ) {
	$figures = array();
	for ( $i = 1; $i <= 4; $i++ ) {
		$value = inpo_core_impact_value( $a[ 'item' . $i . 'Source' ], $a[ 'item' . $i . 'Number' ] );
		$label = trim( $a[ 'item' . $i . 'Label' ] );
		if ( '' !== $value && '' !== $label && '0' !== $value ) {
			$figures[] = array( ctype_digit( $value ) ? number_format_i18n( (int) $value ) : $value, $label );
		}
	}
	if ( ! $figures ) {
		return inpo_core_placeholder( __( 'Results and impact: type a number or choose a source for each figure.', 'inpo-core' ) );
	}
	$light = 'light' === $a['tone'];
	$url   = $a['buttonUrl'] ? $a['buttonUrl'] : inpo_core_projects_url() . '#ndikimi';
	$btn   = $a['buttonText'] && ! is_post_type_archive( INPO_CORE_PROJECT ) ? '<a class="btn" href="' . esc_url( $url ) . '">' . esc_html( $a['buttonText'] ) . '</a>' : '';
	$extra = $a['anchor'] ? array( 'id' => $a['anchor'] ) : array();
	$hid   = $a['anchor'] ? 'h-' . $a['anchor'] : '';
	return '<section ' . inpo_core_wrapper( 'wrap sec' . ( $a['anchor'] ? ' anchor' : '' ), $extra + ( $hid && $a['heading'] ? array( 'aria-labelledby' => $hid ) : array() ) ) . '>'
		. '<div class="impact' . ( $light ? ' light' : '' ) . '">' . ( $light ? '' : inpo_core_rings( 640, 4 ) )
		. '<div class="impact-head">' . ( $a['kicker'] ? '<span class="kicker">' . esc_html( $a['kicker'] ) . '</span>' : '' )
		. ( $a['heading'] ? '<h2 class="h2"' . ( $hid ? ' id="' . esc_attr( $hid ) . '"' : '' ) . '>' . esc_html( $a['heading'] ) . '</h2>' : '' ) . '</div>'
		. inpo_core_impact_grid( $figures ) . ( $light ? '' : $btn ) . '</div></section>';
}

/**
 * Donor tiles and the grant note (shared by the donors and projects blocks).
 */
function inpo_core_donor_tiles( $fineprint = true ) {
	$donors = get_posts( array( 'post_type' => INPO_CORE_DONOR, 'posts_per_page' => -1, 'orderby' => array( 'menu_order' => 'ASC', 'title' => 'ASC' ) ) );
	$tiles  = '';
	$n      = 0;
	foreach ( $donors as $d ) {
		$name = wp_strip_all_tags( get_the_title( $d ) );
		// A name still in [brackets] is a placeholder: editors see it in wp-admin, visitors do not.
		if ( '' === $name || inpo_core_is_placeholder( $name ) ) {
			continue;
		}
		$inner  = has_post_thumbnail( $d ) ? get_the_post_thumbnail( $d, 'medium', array( 'alt' => $name, 'loading' => 'lazy' ) ) : esc_html( $name );
		$url    = get_post_meta( $d->ID, 'inpo_url', true );
		$tiles .= $url ? '<a class="donor" href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . $inner . '</a>' : '<div class="donor">' . $inner . '</div>';
		++$n;
	}
	$fine = $fineprint ? inpo_core_settings( 'fineprint' ) : '';
	return $tiles ? '<div class="donors" style="--cols:' . inpo_core_grid_cols( $n ) . '">' . $tiles . '</div>' . ( $fine ? '<p class="fineprint">' . esc_html( $fine ) . '</p>' : '' ) : '';
}

/**
 * Home: projects and partners. Active projects first, then the latest completed.
 */
function inpo_core_render_projects( $a ) {
	$count  = max( 1, (int) $a['count'] );
	$active = get_posts( array( 'post_type' => INPO_CORE_PROJECT, 'posts_per_page' => $count, 'orderby' => array( 'menu_order' => 'ASC', 'date' => 'DESC' ), 'meta_query' => array( 'relation' => 'OR', array( 'key' => 'inpo_status', 'value' => 'perfunduar', 'compare' => '!=' ), array( 'key' => 'inpo_status', 'compare' => 'NOT EXISTS' ) ) ) );
	$done   = count( $active ) < $count ? get_posts( array( 'post_type' => INPO_CORE_PROJECT, 'posts_per_page' => $count - count( $active ), 'orderby' => array( 'menu_order' => 'ASC', 'date' => 'DESC' ), 'meta_key' => 'inpo_status', 'meta_value' => 'perfunduar' ) ) : array();
	$cards  = '';
	foreach ( array_merge( $active, $done ) as $p ) {
		$cards .= inpo_core_project_card( $p );
	}
	$donors = $a['showDonors'] ? inpo_core_donor_tiles() : '';
	if ( ! $cards && ! $donors ) {
		return inpo_core_placeholder( __( 'Add projects under Projects and donors under INPO > Donors.', 'inpo-core' ) );
	}
	$head = '<div class="pub-head"><div class="pub-head-l">' . ( $a['kicker'] ? '<span class="kicker">' . esc_html( $a['kicker'] ) . '</span>' : '' ) . '<h2 class="h2">' . esc_html( $a['heading'] ) . '</h2></div>'
		. ( $cards && $a['linkText'] ? '<a class="btn btn-line btn-sm" href="' . esc_url( inpo_core_projects_url() ) . '">' . esc_html( $a['linkText'] ) . '</a>' : '' ) . '</div>';
	return '<section ' . inpo_core_wrapper( 'wrap sec' ) . '>' . $head . ( $cards ? '<div class="projs">' . $cards . '</div>' : '' )
		. ( $donors ? '<h3 class="sub-h">' . esc_html( $a['donorsHeading'] ) . '</h3>' . $donors : '' ) . '</section>';
}

/**
 * Projects archive: section links, active/completed filter with counts, the cards.
 */
function inpo_core_render_project_list( $a ) {
	global $wp_query;
	// phpcs:ignore WordPress.Security.NonceVerification
	$status = isset( $_GET['statusi'] ) ? sanitize_key( inpo_core_str( wp_unslash( $_GET['statusi'] ) ) ) : '';
	$query  = ( is_post_type_archive( INPO_CORE_PROJECT ) && $wp_query->is_main_query() && ! wp_is_serving_rest_request() ) ? $wp_query : new WP_Query( array( 'post_type' => INPO_CORE_PROJECT, 'post_status' => 'publish', 'posts_per_page' => 24 ) );
	$total  = (int) wp_count_posts( INPO_CORE_PROJECT )->publish;
	$done   = count( get_posts( array( 'post_type' => INPO_CORE_PROJECT, 'posts_per_page' => -1, 'fields' => 'ids', 'meta_key' => 'inpo_status', 'meta_value' => 'perfunduar' ) ) );
	$chip   = function ( $value, $label, $n ) use ( $status ) {
		$on = $value === $status;
		return '<a class="chip' . ( $on ? ' on' : '' ) . '" href="' . esc_url( inpo_core_projects_url( $value ? array( 'statusi' => $value ) : array() ) ) . '"' . ( $on ? ' aria-current="true"' : '' ) . '>' . esc_html( $label ) . '<span class="chip-n">' . (int) $n . '</span></a>';
	};
	$cards = '';
	foreach ( $query->posts as $p ) {
		$cards .= inpo_core_project_card( $p );
	}
	if ( ! $cards ) {
		$cards = '<div class="empty"><b>' . ( $total ? 'Asnjë projekt në këtë listë.' : 'Projektet publikohen së shpejti.' ) . '</b><span class="muted">' . ( $total ? 'Shikoni të gjitha projektet.' : 'Këtu do të gjeni projektet aktive dhe të përfunduara të INPO-s.' ) . '</span></div>';
	} else {
		$cards = '<div class="projs">' . $cards . '</div>';
	}
	return '<div ' . inpo_core_wrapper( '' ) . '>'
		. '<section class="wrap">' . inpo_core_subnav( inpo_core_pairs( $a['links'] ) ) . '</section>'
		. '<section class="wrap anchor" id="projektet" aria-labelledby="h-projektet"><h2 class="sr" id="h-projektet">Projektet</h2>'
		. '<div class="filters"><div class="chips" role="group" aria-label="Filtro sipas statusit">' . $chip( '', $a['allLabel'], $total ) . $chip( 'aktive', $a['activeLabel'], $total - $done ) . $chip( 'perfunduara', $a['doneLabel'], $done ) . '</div></div>'
		. '<div class="results">' . $cards . inpo_core_pager( $query ) . '</div></section></div>';
}

/**
 * One project: status, period, donor, partners, programme, objectives, description and results.
 */
function inpo_core_render_project_detail( $a, $block ) {
	$post = inpo_core_context_post( $block );
	if ( ! $post || INPO_CORE_PROJECT !== $post->post_type ) {
		return inpo_core_placeholder( __( 'Project details appear here, on a project page.', 'inpo-core' ) );
	}
	$active  = inpo_core_project_active( $post );
	$program = wp_get_object_terms( $post->ID, INPO_CORE_PROGRAM );
	$program = $program && ! is_wp_error( $program ) ? $program[0] : null;
	$fact    = function ( $label, $value ) {
		return '' === trim( wp_strip_all_tags( $value ) ) ? '' : '<div><dt>' . esc_html( $label ) . '</dt><dd>' . $value . '</dd></div>';
	};
	$facts   = $fact( 'Periudha e zbatimit', esc_html( get_post_meta( $post->ID, 'inpo_period', true ) ) )
		. $fact( 'Donatori', esc_html( get_post_meta( $post->ID, 'inpo_donor_text', true ) ) )
		. $fact( 'Partnerët', esc_html( get_post_meta( $post->ID, 'inpo_partners', true ) ) )
		. $fact( 'Programi', $program ? '<a href="' . esc_url( get_term_link( $program ) ) . '">' . esc_html( $program->name ) . '</a>' : '' );

	$main  = '';
	$goals = inpo_core_lines( $post->ID, 'inpo_goals' );
	if ( $goals ) {
		$list = '';
		foreach ( $goals as $i => $goal ) {
			$list .= '<li><span>' . sprintf( '%02d', $i + 1 ) . '</span>' . esc_html( $goal ) . '</li>';
		}
		$main .= '<h2 class="sub-h">Objektivat</h2><ol class="goals one">' . $list . '</ol>';
	}
	if ( trim( $post->post_content ) ) {
		$main .= '<h2 class="sub-h">Rreth projektit</h2><div class="prose">' . inpo_core_the_content( $post ) . '</div>';
	}
	$lines   = inpo_core_lines( $post->ID, 'inpo_results' );
	$figures = inpo_core_figures( $lines );
	$plain   = array_filter( $lines, function ( $l ) {
		return false === strpos( $l, '|' );
	} );
	if ( $figures || $plain ) {
		$main .= '<h2 class="sub-h">Rezultatet</h2>' . inpo_core_impact_grid( $figures, 'plain' );
		if ( $plain ) {
			$main .= '<ul class="prose results-list">' . implode( '', array_map( function ( $l ) {
				return '<li>' . esc_html( $l ) . '</li>';
			}, $plain ) ) . '</ul>';
		}
	}
	$pub = (int) get_post_meta( $post->ID, 'inpo_publication', true );
	$pub = $pub ? get_post( $pub ) : null;
	if ( $pub && 'publish' === $pub->post_status ) {
		$main .= '<h2 class="sub-h">Publikimi i projektit</h2><div class="pubs three">' . inpo_core_pub_card( $pub, 0 ) . '</div>';
	}
	if ( ! $main && current_user_can( 'edit_post', $post->ID ) ) {
		$main = '<p class="todo">' . esc_html__( 'Editors only: add objectives and results in the project details box.', 'inpo-core' ) . '</p>';
	}

	$crumbs = '<nav class="crumbs" aria-label="Gjurma"><a href="' . esc_url( inpo_core_projects_url() ) . '">Projektet dhe ndikimi</a><span aria-hidden="true">/</span>'
		. '<a href="' . esc_url( inpo_core_projects_url( array( 'statusi' => $active ? 'aktive' : 'perfunduara' ) ) ) . '">' . ( $active ? 'Projektet aktive' : 'Projektet e përfunduara' ) . '</a></nav>';
	$lead   = has_excerpt( $post ) ? '<p class="lead">' . esc_html( wp_strip_all_tags( get_the_excerpt( $post ) ) ) . '</p>' : '';
	$html   = '<section class="wrap grid12 page-head"><div class="ph-l">' . $crumbs . '<h1 class="h-headline" data-focus>' . inpo_core_title( $post ) . '</h1></div>'
		. '<div class="ph-r"><span class="pill' . ( $active ? ' on' : '' ) . '">' . ( $active ? 'Aktiv' : 'I përfunduar' ) . '</span>' . ( $lead ? '<div style="margin-top:18px">' . $lead . '</div>' : '' ) . '</div></section>'
		. '<section class="wrap grid12 proj-body">' . ( $facts ? '<dl class="facts proj-facts">' . $facts . '</dl>' : '' ) . '<div class="proj-main">' . $main . '</div></section>';

	$others = get_posts( array( 'post_type' => INPO_CORE_PROJECT, 'posts_per_page' => 3, 'post__not_in' => array( $post->ID ), 'orderby' => array( 'menu_order' => 'ASC', 'date' => 'DESC' ) ) );
	if ( $others ) {
		$html .= '<section class="wrap sec"><div class="pub-head"><h2 class="h2">Projekte të tjera</h2><a class="btn btn-line btn-sm" href="' . esc_url( inpo_core_projects_url() ) . '">Të gjitha projektet</a></div><div class="projs">'
			. implode( '', array_map( 'inpo_core_project_card', $others ) ) . '</div></section>';
	}
	return '<div ' . inpo_core_wrapper( '' ) . '>' . $html . '</div>';
}

/**
 * Concrete examples of impact: news marked as impact examples and figures from project results.
 */
function inpo_core_render_impact_cases( $a ) {
	$rows  = '';
	$posts = get_posts( array( 'posts_per_page' => max( 1, (int) $a['count'] ), 'meta_key' => 'inpo_impact', 'meta_value' => '1' ) );
	foreach ( $posts as $p ) {
		$rows .= inpo_core_news_row( $p );
	}
	foreach ( get_posts( array( 'post_type' => INPO_CORE_PROJECT, 'posts_per_page' => 12, 'orderby' => array( 'menu_order' => 'ASC', 'date' => 'DESC' ) ) ) as $p ) {
		foreach ( inpo_core_figures( inpo_core_lines( $p->ID, 'inpo_results' ) ) as $f ) {
			$rows .= '<a class="row" href="' . esc_url( get_permalink( $p ) ) . '"><div class="row-meta"><span class="case-num">' . esc_html( $f[0] ) . '</span><span>Projekt</span></div>'
				. '<span class="row-title">' . esc_html( mb_strtoupper( mb_substr( $f[1], 0, 1 ) ) . mb_substr( $f[1], 1 ) ) . '. ' . inpo_core_title( $p ) . '</span><span class="arrow-circle">' . inpo_core_icon( 'arrow' ) . '</span></a>';
		}
	}
	if ( ! $rows ) {
		return inpo_core_placeholder( __( 'Impact examples: tick "Impact example" on a news item, or add results to a project.', 'inpo-core' ) );
	}
	$extra = $a['anchor'] ? array( 'id' => $a['anchor'] ) : array();
	return '<section ' . inpo_core_wrapper( 'wrap' . ( $a['anchor'] ? ' anchor' : '' ), $extra ) . '><div class="grid12 cases"><div class="cases-intro">'
		. ( $a['kicker'] ? '<span class="kicker">' . esc_html( $a['kicker'] ) . '</span>' : '' ) . '<h3 class="sub-h">' . esc_html( $a['heading'] ) . '</h3>'
		. ( $a['text'] ? '<p class="muted">' . esc_html( $a['text'] ) . '</p>' : '' ) . '</div><div class="cases-list">' . $rows . '</div></div></section>';
}

/**
 * Document categories in display order.
 *
 * @return WP_Term[]
 */
function inpo_core_doc_cats() {
	$terms = get_terms( array( 'taxonomy' => INPO_CORE_DOC_CAT, 'hide_empty' => false ) );
	$terms = is_wp_error( $terms ) ? array() : $terms;
	usort(
		$terms,
		function ( $a, $b ) {
			$x = get_term_meta( $a->term_id, 'inpo_order', true );
			$y = get_term_meta( $b->term_id, 'inpo_order', true );
			return ( '' === $x ? 99 : (int) $x ) - ( '' === $y ? 99 : (int) $y );
		}
	);
	return $terms;
}

function inpo_core_doc_row( $doc, $cat_name ) {
	$year = get_post_meta( $doc->ID, 'inpo_year', true );
	$file = inpo_core_doc_file( $doc );
	$act  = '';
	if ( $file ) {
		wp_enqueue_script( 'inpo-core-reader' );
		$size = $file['size'] ? ' <span class="btn-meta">' . esc_html( inpo_core_file_size( $file['size'] ) ) . '</span>' : '';
		$act  = '<button class="btn btn-line btn-sm" type="button" data-pdf="' . esc_url( $file['url'] ) . '" data-pdf-title="' . esc_attr( wp_strip_all_tags( get_the_title( $doc ) ) ) . '" data-pdf-meta="' . esc_attr( $cat_name . ( $year ? ' · ' . $year : '' ) ) . '" data-pdf-link="' . esc_url( $file['url'] ) . '">' . inpo_core_icon( 'book' ) . ' Lexo</button>'
			. '<a class="btn btn-line btn-sm" href="' . esc_url( $file['url'] ) . '" download>' . inpo_core_icon( 'download' ) . ' PDF' . $size . '</a>';
	}
	$source = (int) get_post_meta( $doc->ID, 'inpo_source', true );
	if ( ! $act && $source && 'publish' === get_post_status( $source ) ) {
		// Copied from a publication without a PDF: link to the publication's page.
		$act = '<a class="btn btn-line btn-sm" href="' . esc_url( get_permalink( $source ) ) . '">Shiko ' . inpo_core_icon( 'arrow' ) . '</a>';
	}
	return '<div class="doc-row" data-year="' . esc_attr( $year ) . '"><span class="doc-yr">' . esc_html( $year ) . '</span><span class="doc-title">' . inpo_core_title( $doc ) . '</span><span class="doc-act">' . $act . '</span></div>';
}

/**
 * Transparency documents by category and year (INPO's brief, section 7).
 * Layout "grouped": every category with its documents, a year filter and links to the categories.
 * Layout "list": the documents of the chosen categories only, with a link to the Transparency page.
 */
function inpo_core_render_documents( $a ) {
	$only = array_filter( array_map( 'sanitize_title', array_map( 'trim', explode( ',', (string) $a['categories'] ) ) ) );
	$cats = array_values( array_filter( inpo_core_doc_cats(), function ( $t ) use ( $only ) {
		return ! $only || in_array( $t->slug, $only, true );
	} ) );
	if ( ! $cats ) {
		return inpo_core_placeholder( __( 'Add document categories under Documents > Categories.', 'inpo-core' ) );
	}
	// phpcs:ignore WordPress.Security.NonceVerification
	$year  = isset( $_GET['viti'] ) ? absint( inpo_core_str( wp_unslash( $_GET['viti'] ) ) ) : 0;
	$docs  = get_posts( array( 'post_type' => INPO_CORE_DOC, 'posts_per_page' => -1, 'orderby' => array( 'menu_order' => 'ASC', 'title' => 'DESC' ) ) );
	$by    = array();
	$years = array();
	foreach ( $docs as $d ) {
		$y = (string) get_post_meta( $d->ID, 'inpo_year', true );
		if ( $y ) {
			$years[ $y ] = $y;
		}
		foreach ( wp_get_object_terms( $d->ID, INPO_CORE_DOC_CAT, array( 'fields' => 'ids' ) ) as $tid ) {
			$by[ $tid ][] = array( $y, $d );
		}
	}
	krsort( $years );
	$wrap = function ( $inner, $cls = 'wrap' ) use ( $a ) {
		if ( ! $a['wrapped'] ) {
			return '<div ' . inpo_core_wrapper( 'docs' ) . '>' . $inner . '</div>';
		}
		$extra = $a['anchor'] ? array( 'id' => $a['anchor'] ) : array();
		return '<section ' . inpo_core_wrapper( $cls . ( $a['anchor'] ? ' anchor' : '' ), $extra ) . '>' . $inner . '</section>';
	};

	if ( 'list' === $a['layout'] ) {
		$rows = '';
		foreach ( $cats as $c ) {
			$list = isset( $by[ $c->term_id ] ) ? $by[ $c->term_id ] : array();
			usort( $list, function ( $x, $y ) {
				return strcmp( $y[0], $x[0] );
			} );
			foreach ( $list as $item ) {
				$rows .= inpo_core_doc_row( $item[1], $c->name );
			}
		}
		$page = get_page_by_path( 'transparenca' );
		return $wrap( ( $rows ? '<div class="doc-rows">' . $rows . '</div>' : '<p class="muted doc-empty">Dokumentet publikohen së shpejti.</p>' )
			. ( $page ? '<p class="docs-more"><a class="link-u" href="' . esc_url( get_permalink( $page ) ) . '">Të gjitha dokumentet në Transparenca ' . inpo_core_icon( 'arrow' ) . '</a></p>' : '' ) );
	}

	$groups = '';
	$links  = array();
	$soon   = array();
	foreach ( $cats as $c ) {
		$list = isset( $by[ $c->term_id ] ) ? $by[ $c->term_id ] : array();
		usort( $list, function ( $x, $y ) {
			return strcmp( $y[0], $x[0] );
		} );
		$rows  = '';
		$shown = 0;
		foreach ( $list as $item ) {
			$on     = ! $year || (int) $item[0] === $year;
			$shown += $on ? 1 : 0;
			$rows  .= str_replace( '<div class="doc-row"', '<div class="doc-row"' . ( $on ? '' : ' hidden' ), inpo_core_doc_row( $item[1], $c->name ) );
		}
		// Categories without documents go on one "coming soon" line (their anchors stay, for menu links).
		if ( ! $list ) {
			$soon[] = '<span class="anchor" id="' . esc_attr( $c->slug ) . '">' . esc_html( $c->name ) . '</span>';
			continue;
		}
		$links[] = array( $c->slug, $c->name );
		$empty   = 'Asnjë dokument për këtë vit.';
		$groups .= '<section class="docs-group anchor" id="' . esc_attr( $c->slug ) . '" aria-labelledby="h-' . esc_attr( $c->slug ) . '"><h2 class="docs-h" id="h-' . esc_attr( $c->slug ) . '">' . esc_html( $c->name ) . '<span class="chip-n">' . (int) $shown . '</span></h2>'
			. ( $rows ? '<div class="doc-rows">' . $rows . '</div>' : '' ) . '<p class="muted doc-empty"' . ( $shown ? ' hidden' : '' ) . '>' . esc_html( $empty ) . '</p></section>';
	}
	$filter = '';
	if ( $a['yearFilter'] && $years ) {
		$options = array( '' => 'Të gjitha' ) + $years;
		$filter  = '<form class="docs-year" method="get" data-inpo-docs>' . inpo_core_filter_select( 'docs-year', 'viti', 'Viti', $options, $year ? (string) $year : '' )
			. '<noscript><button class="btn btn-ink btn-sm" type="submit">Filtro</button></noscript></form>';
	}
	$top  = ( $a['showLinks'] || $filter ) ? '<div class="filters docs-filters">' . ( $a['showLinks'] && count( $links ) > 1 ? inpo_core_subnav( $links, 'Në këtë faqe', 'Kategoritë e dokumenteve' ) : '<span></span>' ) . $filter . '</div>' : '';
	$soon = $soon ? '<p class="docs-soon"><b>Së shpejti</b> ' . implode( '<span aria-hidden="true"> · </span>', $soon ) . '</p>' : '';
	return $wrap( $top . ( $groups ? $groups : '<p class="muted doc-empty">Dokumentet publikohen së shpejti.</p>' ) . $soon );
}

/**
 * A dataset's CSV as [ header, rows ]. Accepts comma, semicolon or tab separators.
 */
/**
 * Tables from CSV text. One table per block; blocks are separated by an empty line.
 * A block may start with "# Title | bar, line or table | unit". Comma, semicolon or tab separated.
 *
 * @return array[] Each: title, chart, unit, cols, rows (numbers as numbers).
 */
function inpo_core_csv_tables( $csv, $chart = 'bar', $unit = '' ) {
	$out = array();
	foreach ( preg_split( '/\R\s*\R/', trim( (string) $csv ) ) as $block ) {
		$lines = array_values( array_filter( array_map( 'trim', preg_split( '/\R/', $block ) ), 'strlen' ) );
		$t     = array( 'title' => '', 'chart' => $chart, 'unit' => $unit );
		if ( $lines && '#' === $lines[0][0] ) {
			$parts      = array_map( 'trim', explode( '|', ltrim( array_shift( $lines ), '# ' ) ) );
			$t['title'] = $parts[0];
			if ( ! empty( $parts[1] ) && in_array( $parts[1], array( 'bar', 'line', 'table' ), true ) ) {
				$t['chart'] = $parts[1];
			}
			if ( isset( $parts[2] ) ) {
				$t['unit'] = $parts[2];
			}
		}
		if ( count( $lines ) < 2 ) {
			continue;
		}
		$sep = ',';
		foreach ( array( "\t", ';' ) as $c ) {
			if ( substr_count( $lines[0], $c ) > substr_count( $lines[0], $sep ) ) {
				$sep = $c;
			}
		}
		$rows = array_map( function ( $l ) use ( $sep ) {
			return array_map( 'trim', str_getcsv( $l, $sep, '"', '\\' ) );
		}, $lines );
		$t['cols'] = array_shift( $rows );
		foreach ( $rows as &$r ) {
			foreach ( $r as $i => $v ) {
				$n = str_replace( array( ' ', ',' ), array( '', '.' ), $v );
				if ( $i && is_numeric( $n ) ) {
					$r[ $i ] = 0 + $n;
				}
			}
		}
		unset( $r );
		$t['rows'] = $rows;
		$out[]     = $t;
	}
	return $out;
}

/**
 * The chart for one table: a plain table without JS; charts.js draws the chart, a sortable table and a CSV button.
 */
function inpo_core_chart_view( $t, $file ) {
	wp_enqueue_script( 'inpo-core-charts' );
	$table = '<div class="dtable-wrap"><table class="dtable"><thead><tr>';
	foreach ( $t['cols'] as $i => $h ) {
		$table .= '<th scope="col"' . ( $i ? ' class="n"' : '' ) . '>' . esc_html( $h ) . '</th>';
	}
	$table .= '</tr></thead><tbody>';
	foreach ( $t['rows'] as $r ) {
		$table .= '<tr>';
		foreach ( $r as $i => $v ) {
			$table .= $i ? '<td class="n">' . esc_html( is_numeric( $v ) ? number_format_i18n( $v, floor( $v ) == $v ? 0 : 2 ) : $v ) . '</td>' : '<th scope="row">' . esc_html( $v ) . '</th>'; // phpcs:ignore WordPress.PHP.StrictComparisons
		}
		$table .= '</tr>';
	}
	$table .= '</tbody></table></div>';
	$data = array(
		'cols'  => $t['cols'],
		'rows'  => $t['rows'],
		'chart' => in_array( $t['chart'], array( 'bar', 'line', 'table' ), true ) ? $t['chart'] : 'bar',
		'unit'  => (string) $t['unit'],
		'title' => (string) $t['title'],
		'file'  => $file,
	);
	return '<div class="dataset-view" data-dataset="' . esc_attr( wp_json_encode( $data ) ) . '">' . $table . '</div>';
}

function inpo_core_dataset_table( $post ) {
	$csv   = trim( (string) get_post_meta( $post->ID, 'inpo_csv', true ) );
	$lines = array_values( array_filter( preg_split( '/\R/', $csv ), 'strlen' ) );
	if ( count( $lines ) < 2 ) {
		return array( array(), array() );
	}
	$sep = ',';
	foreach ( array( "\t", ';' ) as $s ) {
		if ( substr_count( $lines[0], $s ) > substr_count( $lines[0], $sep ) ) {
			$sep = $s;
		}
	}
	$rows = array_map( function ( $l ) use ( $sep ) {
		return array_map( 'trim', str_getcsv( $l, $sep, '"', '\\' ) );
	}, $lines );
	$head = array_shift( $rows );
	foreach ( $rows as &$r ) {
		foreach ( $r as $i => $v ) {
			if ( $i && is_numeric( str_replace( array( ' ', ',' ), array( '', '.' ), $v ) ) ) {
				$r[ $i ] = 0 + str_replace( array( ' ', ',' ), array( '', '.' ), $v );
			}
		}
	}
	unset( $r );
	return array( $head, $rows );
}

function inpo_core_render_dataset_list( $a ) {
	global $wp_query;
	$query = ( is_post_type_archive( INPO_CORE_DATASET ) && $wp_query->is_main_query() && ! wp_is_serving_rest_request() ) ? $wp_query : new WP_Query( array( 'post_type' => INPO_CORE_DATASET, 'post_status' => 'publish', 'posts_per_page' => 24 ) );
	$cards  = '';
	$sample = false;
	foreach ( $query->posts as $p ) {
		$sample = $sample || get_post_meta( $p->ID, '_inpo_sample', true );
		$chart  = get_post_meta( $p->ID, 'inpo_chart', true );
		$cards .= '<a class="dataset" href="' . esc_url( get_permalink( $p ) ) . '"><span class="pill">' . ( 'line' === $chart ? 'Grafik me vija' : ( 'table' === $chart ? 'Tabelë' : 'Grafik me shtylla' ) ) . '</span>'
			. '<h2>' . inpo_core_title( $p ) . '</h2>' . ( has_excerpt( $p ) ? '<p>' . esc_html( wp_strip_all_tags( get_the_excerpt( $p ) ) ) . '</p>' : '<p></p>' )
			. '<span class="link-u">Hap të dhënat ' . inpo_core_icon( 'arrow' ) . '</span></a>';
	}
	$cards = $cards ? '<div class="datasets">' . $cards . '</div>' : '<div class="empty" style="margin-top:40px"><b>Të dhënat publikohen së shpejti.</b><span class="muted">Këtu do të gjeni statistika të prokurimit publik në grafikë dhe tabela.</span></div>';
	$notice = $a['notice'] ? $a['notice'] : ( $sample ? 'Të dhëna ilustruese: grafikët këtu kanë vlera shembull, që tregojnë si funksionojnë. INPO i zëvendëson me statistikat e veta.' : '' );
	return '<section ' . inpo_core_wrapper( 'wrap' ) . '>' . ( $notice ? '<p class="notice">' . esc_html( $notice ) . '</p>' : '' ) . $cards . inpo_core_pager( $query ) . '</section>';
}

function inpo_core_render_dataset( $a, $block ) {
	$post = inpo_core_context_post( $block );
	if ( ! $post || INPO_CORE_DATASET !== $post->post_type ) {
		return inpo_core_placeholder( __( 'The dataset appears here, on a dataset page.', 'inpo-core' ) );
	}
	list( $head, $rows ) = inpo_core_dataset_table( $post );
	$crumbs = '<nav class="crumbs" aria-label="Gjurma"><a href="' . esc_url( inpo_core_pubs_url() ) . '">Hulumtime dhe publikime</a><span aria-hidden="true">/</span><a href="' . esc_url( get_post_type_archive_link( INPO_CORE_DATASET ) ) . '">Të dhëna dhe statistika</a></nav>';
	$lead   = has_excerpt( $post ) ? '<p class="lead">' . esc_html( wp_strip_all_tags( get_the_excerpt( $post ) ) ) . '</p>' : '';
	$html   = '<section class="wrap grid12 page-head"><div class="ph-l">' . $crumbs . '<h1 class="h-headline" data-focus>' . inpo_core_title( $post ) . '</h1></div><div class="ph-r">' . $lead . '</div></section>';
	if ( ! $head ) {
		return '<div ' . inpo_core_wrapper( '' ) . '>' . $html . inpo_core_placeholder( __( 'Paste the data as CSV in the dataset box: the first line holds the column names.', 'inpo-core' ) ) . '</div>';
	}
	wp_enqueue_script( 'inpo-core-charts' );
	// The table is the no-JS view; charts.js replaces it with the chart and a sortable table.
	$table = '<div class="dtable-wrap"><table class="dtable"><thead><tr>';
	foreach ( $head as $i => $h ) {
		$table .= '<th scope="col"' . ( $i ? ' class="n"' : '' ) . '>' . esc_html( $h ) . '</th>';
	}
	$table .= '</tr></thead><tbody>';
	foreach ( $rows as $r ) {
		$table .= '<tr>';
		foreach ( $r as $i => $v ) {
			$table .= $i ? '<td class="n">' . esc_html( is_numeric( $v ) ? number_format_i18n( $v, floor( $v ) == $v ? 0 : 2 ) : $v ) . '</td>' : '<th scope="row">' . esc_html( $v ) . '</th>'; // phpcs:ignore WordPress.PHP.StrictComparisons
		}
		$table .= '</tr>';
	}
	$table .= '</tbody></table></div>';
	$chart  = get_post_meta( $post->ID, 'inpo_chart', true );
	$data   = array(
		'cols'  => $head,
		'rows'  => $rows,
		'chart' => in_array( $chart, array( 'bar', 'line', 'table' ), true ) ? $chart : 'bar',
		'unit'  => (string) get_post_meta( $post->ID, 'inpo_unit', true ),
		'title' => wp_strip_all_tags( get_the_title( $post ) ),
		'file'  => $post->post_name,
	);
	$source = get_post_meta( $post->ID, 'inpo_source', true );
	$notice = get_post_meta( $post->ID, '_inpo_sample', true ) ? 'Të dhëna ilustruese: vlerat tregojnë si funksionojnë grafikët. INPO i zëvendëson me statistikat e veta.' : $a['notice'];
	$html  .= '<section class="wrap">' . ( $notice ? '<p class="notice">' . esc_html( $notice ) . '</p>' : '' )
		. '<div class="dataset-view" data-dataset="' . esc_attr( wp_json_encode( $data ) ) . '">' . $table
		. '<p><a class="btn btn-line btn-sm" href="' . esc_url( add_query_arg( 'shkarko', 'csv', get_permalink( $post ) ) ) . '" download>Shkarko CSV</a></p></div>'
		. ( $source ? '<p class="fineprint">Burimi: ' . esc_html( $source ) . '</p>' : '' ) . '</section>';
	return '<div ' . inpo_core_wrapper( '' ) . '>' . $html . '</div>';
}
