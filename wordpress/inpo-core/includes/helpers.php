<?php
/**
 * Markup helpers shared by the block renderers.
 * They mirror the prototype helpers in assets/js/app.js: rings(), cover(), pubCard(), newsRow().
 */

defined( 'ABSPATH' ) || exit;

// Same names as the live inpo-ks.org (registered there by CPT UI), so existing publications,
// their types and WPML settings carry over when this plugin is activated on that site.
const INPO_CORE_PUB = 'publikimet';
const INPO_CORE_PUB_TYPE = 'tipi_i_publikimit';
const INPO_CORE_PROGRAM = 'inpo_program';
const INPO_CORE_DONOR = 'inpo_donor';
const INPO_CORE_MEMBER = 'inpo_member';
// Added for INPO's brief (Oct 2026): projects, transparency documents, data, thematic fields, institutions.
const INPO_CORE_PROJECT = 'inpo_project';
const INPO_CORE_DOC = 'inpo_doc';
const INPO_CORE_DOC_CAT = 'inpo_doc_cat';
const INPO_CORE_DATASET = 'inpo_dataset';
const INPO_CORE_THEME = 'inpo_theme';
const INPO_CORE_INST = 'inpo_institution';

/**
 * Is the INPO theme active (or being live-previewed)? Until it is, the plugin only stores data
 * and leaves the public site exactly as the current theme renders it.
 */
function inpo_core_theme_active() {
	return (bool) apply_filters( 'inpo_core_theme_active', 'inpo' === get_template() );
}

/**
 * Inline SVG icons. All decorative, so aria-hidden.
 *
 * @param string $name Icon key.
 * @return string
 */
function inpo_core_icon( $name ) {
	$icons = array(
		'arrow'     => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17 17 7"/><path d="M8 7h9v9"/></svg>',
		'download'  => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4v11"/><path d="m7 10 5 5 5-5"/><path d="M5 20h14"/></svg>',
		'search'    => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>',
		'check'     => '<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12.5 4.5 4.5L19 7.5"/></svg>',
		'qeverisje' => '<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="5" r="2.5"/><circle cx="5" cy="18" r="2.5"/><circle cx="12" cy="18" r="2.5"/><circle cx="19" cy="18" r="2.5"/><path d="M12 7.5v8"/><path d="M12 11c-4 0-7 1.5-7 4.5"/><path d="M12 11c4 0 7 1.5 7 4.5"/></svg>',
		'edukim'    => '<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m2 9 10-5 10 5-10 5-10-5Z"/><path d="M6 11v5c0 1.5 2.7 3 6 3s6-1.5 6-3v-5"/><path d="M22 9v6"/></svg>',
		'book'      => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 5.5C4.5 4 8 4 12 6c4-2 7.5-2 10-.5V19c-2.5-1.5-6-1.5-10 .5-4-2-7.5-2-10-.5Z"/><path d="M12 6v13.5"/></svg>',
		'dokument'  => '<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3H6v18h12V7l-4-4Z"/><path d="M14 3v4h4"/><path d="M9 13h6M9 17h6"/></svg>',
	);
	return isset( $icons[ $name ] ) ? $icons[ $name ] : $icons['dokument'];
}

/**
 * Icon choices for programmes (admin select).
 */
function inpo_core_program_icons() {
	return array(
		'qeverisje' => __( 'Network (governance)', 'inpo-core' ),
		'edukim'    => __( 'Graduation cap (education)', 'inpo-core' ),
		'dokument'  => __( 'Document', 'inpo-core' ),
	);
}

/**
 * Concentric rings, the signature motif. Same maths as rings() in the prototype.
 *
 * @param int $size Pixel size.
 * @param int $n    Number of rings.
 * @return string
 */
function inpo_core_rings( $size, $n ) {
	$c    = $size / 2;
	$step = ( $c - 8 ) / $n;
	$out  = '';
	for ( $i = 1; $i <= $n; $i++ ) {
		$r    = (int) round( $step * $i + ( 1 === $i ? $step * 0.4 : 0 ) );
		$out .= '<circle cx="' . $c . '" cy="' . $c . '" r="' . $r . '"/>';
	}
	return '<svg class="rings" width="' . $size . '" height="' . $size . '" viewBox="0 0 ' . $size . ' ' . $size . '" aria-hidden="true">' . $out . '</svg>';
}

/**
 * Permalink of a page by its path, or home when missing.
 *
 * @param string $path Page path.
 * @return string
 */
function inpo_core_page_url( $path ) {
	$page = get_page_by_path( $path );
	return $page ? get_permalink( $page ) : home_url( '/' );
}

/**
 * URL of the news list (posts page).
 */
function inpo_core_news_url() {
	$id = (int) get_option( 'page_for_posts' );
	return $id ? get_permalink( $id ) : home_url( '/' );
}

/**
 * URL of the publication library, with optional filters.
 *
 * @param array $args Query args, e.g. array( 'lloji' => 'analiza' ).
 * @return string
 */
function inpo_core_pubs_url( $args = array() ) {
	$url = get_post_type_archive_link( INPO_CORE_PUB );
	$url = $url ? $url : home_url( '/' );
	return $args ? add_query_arg( array_map( 'rawurlencode', $args ), $url ) : $url;
}

/**
 * Contact page URL.
 */
function inpo_core_contact_url( $args = array() ) {
	$url = inpo_core_page_url( 'kontakti' );
	return $args ? add_query_arg( $args, $url ) : $url;
}

/**
 * First real category of a post (skips "Uncategorized").
 *
 * @param int|WP_Post $post Post.
 * @return WP_Term|null
 */
function inpo_core_post_category( $post ) {
	$cats    = get_the_category( is_object( $post ) ? $post->ID : $post );
	$default = (int) get_option( 'default_category' );
	$chips   = inpo_core_chip_category_ids();
	$first   = null;
	$best    = null;
	foreach ( $cats as $cat ) {
		if ( $cat->term_id === $default ) {
			continue;
		}
		// Of several filter categories, the most specific (fewest posts) labels the post:
		// a public reaction is "Reagim publik" even when it is also listed under Aktivitete.
		if ( $chips && in_array( $cat->term_id, $chips, true ) && ( ! $best || $cat->count < $best->count ) ) {
			$best = $cat;
		}
		$first = $first ? $first : $cat;
	}
	if ( $best ) {
		return $best;
	}
	// With filter categories chosen, other (legacy) categories are not shown as labels.
	return $chips ? null : $first;
}

/**
 * Categories chosen as news filters (term meta inpo_chip). Empty = all categories qualify.
 * On the live site the old categories mix topics, programmes and layout flags ("PerSllajder"),
 * so only a clean set is offered as filters and labels.
 *
 * @return int[]
 */
function inpo_core_chip_category_ids() {
	static $ids = null;
	if ( null === $ids ) {
		$ids = array_map( 'intval', get_terms( array( 'taxonomy' => 'category', 'hide_empty' => false, 'fields' => 'ids', 'meta_key' => 'inpo_chip', 'meta_value' => '1' ) ) );
	}
	return $ids;
}

/**
 * Category label, or "Lajm" when the post has no category.
 */
function inpo_core_post_category_name( $post ) {
	$cat = inpo_core_post_category( $post );
	if ( ! $cat ) {
		return 'Lajm';
	}
	// Card label: the singular form when set (Reagim publik), else the category name.
	$one = get_term_meta( $cat->term_id, 'inpo_singular', true );
	return $one ? $one : $cat->name;
}

/**
 * Display date in the prototype format (18.06.2025).
 * An editor can override it, e.g. "Maj 2025" when the exact day is unknown.
 *
 * @param WP_Post $post Post.
 * @return string
 */
function inpo_core_date_label( $post ) {
	$label = get_post_meta( $post->ID, 'inpo_date_label', true );
	return $label ? $label : get_the_date( 'd.m.Y', $post );
}

/**
 * <time> element for a post.
 */
function inpo_core_time( $post ) {
	return '<time datetime="' . esc_attr( get_the_date( 'Y-m-d', $post ) ) . '">' . esc_html( inpo_core_date_label( $post ) ) . '</time>';
}

/**
 * Plain title for use inside our own markup.
 */
function inpo_core_title( $post ) {
	return esc_html( wp_strip_all_tags( get_the_title( $post ) ) );
}

/**
 * Excerpt without auto-generated "read more" noise.
 */
function inpo_core_excerpt( $post ) {
	if ( has_excerpt( $post ) ) {
		return wp_strip_all_tags( get_the_excerpt( $post ) );
	}
	return wp_trim_words( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ), 28, '…' );
}

/**
 * Publication type term (first one).
 */
function inpo_core_pub_type( $post ) {
	$terms = get_the_terms( $post, INPO_CORE_PUB_TYPE );
	return ( $terms && ! is_wp_error( $terms ) ) ? $terms[0] : null;
}

/**
 * Programme term of a publication (first one).
 */
function inpo_core_pub_program( $post ) {
	$terms = get_the_terms( $post, INPO_CORE_PROGRAM );
	return ( $terms && ! is_wp_error( $terms ) ) ? $terms[0] : null;
}

/**
 * Publication types ordered by their "order" meta.
 *
 * @return WP_Term[]
 */
function inpo_core_pub_types() {
	$terms = get_terms(
		array(
			'taxonomy'   => INPO_CORE_PUB_TYPE,
			'hide_empty' => true, // No filter that leads to an empty list.
			'meta_key'   => 'inpo_order',
			'orderby'    => 'meta_value_num',
			'order'      => 'ASC',
		)
	);
	if ( is_wp_error( $terms ) || ! $terms ) {
		$terms = get_terms( array( 'taxonomy' => INPO_CORE_PUB_TYPE, 'hide_empty' => true ) );
	}
	return is_wp_error( $terms ) ? array() : $terms;
}

/**
 * Programmes ordered by their "order" meta.
 *
 * @return WP_Term[]
 */
function inpo_core_programs() {
	$terms = get_terms(
		array(
			'taxonomy'   => INPO_CORE_PROGRAM,
			'hide_empty' => false,
			'meta_key'   => 'inpo_order',
			'orderby'    => 'meta_value_num',
			'order'      => 'ASC',
		)
	);
	if ( is_wp_error( $terms ) || ! $terms ) {
		$terms = get_terms( array( 'taxonomy' => INPO_CORE_PROGRAM, 'hide_empty' => false ) );
	}
	return is_wp_error( $terms ) ? array() : $terms;
}

/**
 * Type names are plural ("Analiza"), as on the live site; they label the filter chips.
 */
function inpo_core_type_plural( $term ) {
	$plural = get_term_meta( $term->term_id, 'inpo_plural', true );
	return $plural ? $plural : $term->name;
}

/**
 * Singular label for one publication ("Analizë"): term meta inpo_singular, else the name.
 */
function inpo_core_type_label( $term ) {
	if ( ! $term ) {
		return 'Publikim';
	}
	$singular = get_term_meta( $term->term_id, 'inpo_singular', true );
	return $singular ? $singular : $term->name;
}

/**
 * Publication year: the "year" field, else the year it was published.
 */
function inpo_core_pub_year( $post ) {
	$year = get_post_meta( $post->ID, 'inpo_year', true );
	return $year ? $year : get_the_date( 'Y', $post );
}

/**
 * The publication's PDF: the "PDF" field, else the newest PDF attached to it in Media.
 *
 * @return array|null { url: string, size: int|null }
 */
function inpo_core_pub_pdf( $post ) {
	$url  = get_post_meta( $post->ID, 'inpo_pdf', true );
	$file = '';
	$id   = 0;
	if ( $url ) {
		$id   = attachment_url_to_postid( $url );
		$file = $id ? get_attached_file( $id ) : '';
	} else {
		$pdfs = get_children(
			array(
				'post_parent'    => $post->ID,
				'post_type'      => 'attachment',
				'post_mime_type' => 'application/pdf',
				'orderby'        => 'date',
				'order'          => 'DESC',
				'numberposts'    => 1,
			)
		);
		if ( $pdfs ) {
			$pdf  = reset( $pdfs );
			$id   = $pdf->ID;
			$url  = wp_get_attachment_url( $pdf->ID );
			$file = get_attached_file( $pdf->ID );
		}
	}
	if ( ! $url ) {
		return null;
	}
	return array( 'url' => $url, 'id' => (int) $id, 'size' => ( $file && file_exists( $file ) ) ? (int) filesize( $file ) : null );
}

/**
 * "2,4 MB" style sizes, Albanian number format.
 */
function inpo_core_file_size( $bytes ) {
	if ( ! $bytes ) {
		return '';
	}
	return $bytes >= 1048576
		? number_format( $bytes / 1048576, 1, ',', '.' ) . ' MB'
		: max( 1, (int) round( $bytes / 1024 ) ) . ' KB';
}

/**
 * Slug of the "reports about INPO" type (audit reports). The live site uses raporte-per-inpo.
 */
function inpo_core_audit_type_slug() {
	return term_exists( 'raporte-per-inpo', INPO_CORE_PUB_TYPE ) ? 'raporte-per-inpo' : 'inpo';
}

/**
 * A picture of the publication for its cover: the featured image, else the first-page preview
 * WordPress creates for an uploaded PDF (when the server can render PDFs).
 *
 * @return array|null { url, width, height }
 */
function inpo_core_pub_preview( $post ) {
	$id = (int) get_post_thumbnail_id( $post );
	if ( ! $id ) {
		$pdf = inpo_core_pub_pdf( $post );
		$id  = $pdf && $pdf['id'] ? $pdf['id'] : 0;
	}
	if ( ! $id ) {
		return null;
	}
	$src = wp_get_attachment_image_src( $id, 'medium_large' );
	if ( ! $src || empty( $src[0] ) || preg_match( '#/wp-includes/images/media/#', $src[0] ) ) {
		return null;
	}
	return array( 'url' => $src[0], 'width' => (int) $src[1], 'height' => (int) $src[2] );
}

/**
 * Publication cover: a document with a folded corner and its title typeset like a report.
 * Shows the real first page when a preview exists. Decorative: the title is also in the card or the h1.
 *
 * @param WP_Post $post  Publication.
 * @param int     $index Position in the list (rotates the style when none is set).
 * @return string
 */
/**
 * The table an infographic's cover is drawn from: the chart with the most rows (not a table-only one).
 */
function inpo_core_cover_table( $post ) {
	$csv = (string) get_post_meta( $post->ID, 'inpo_csv', true );
	if ( '' === trim( $csv ) || ! function_exists( 'inpo_core_csv_tables' ) ) {
		return null;
	}
	$best = null;
	foreach ( inpo_core_csv_tables( $csv ) as $t ) {
		if ( 'table' !== $t['chart'] && ( ! $best || count( $t['rows'] ) > count( $best['rows'] ) ) ) {
			$best = $t;
		}
	}
	return $best;
}

function inpo_core_cover( $post, $index = 0 ) {
	$variants = array( 'rings', 'bars', 'dots', 'word' );
	$variant  = get_post_meta( $post->ID, 'inpo_cover', true );
	if ( ! in_array( $variant, $variants, true ) ) {
		$variant = $variants[ absint( $index ) % 4 ];
	}
	$preview = inpo_core_pub_preview( $post );
	$art     = '';
	$data    = inpo_core_cover_table( $post );
	if ( $data ) {
		// An infographic with data: its chart with the most bars, drawn small on the cover.
		$variant = 'bars';
		$vals    = array();
		foreach ( array_slice( $data['rows'], 0, 8 ) as $r ) {
			$vals[] = isset( $r[1] ) && is_numeric( $r[1] ) ? (float) $r[1] : 0;
		}
		$max = max( 1, max( $vals ) );
		$art = '<div class="bars data">';
		foreach ( $vals as $v ) {
			$art .= '<i style="height:' . max( 6, (int) round( $v / $max * 100 ) ) . '%"' . ( $v === $max ? ' class="on"' : '' ) . '></i>';
		}
		$art .= '</div>';
	} elseif ( $preview ) {
		$variant = 'page';
		$art     = '<img class="cover-img" src="' . esc_url( $preview['url'] ) . '" alt="" loading="lazy" decoding="async"' . ( $preview['width'] ? ' width="' . $preview['width'] . '" height="' . $preview['height'] . '"' : '' ) . '>';
	} elseif ( 'rings' === $variant ) {
		$art = inpo_core_rings( 260, 3 );
	} elseif ( 'bars' === $variant ) {
		$art = '<div class="bars"><i style="height:40%"></i><i style="height:66%"></i><i style="height:52%"></i><i style="height:100%"></i></div>';
	} elseif ( 'dots' === $variant ) {
		$on  = array( 2, 7, 10, 15 );
		$art = '<div class="dots">';
		for ( $k = 0; $k < 18; $k++ ) {
			$art .= in_array( $k, $on, true ) ? '<i class="on"></i>' : '<i></i>';
		}
		$art .= '</div>';
	} else {
		$word = get_post_meta( $post->ID, 'inpo_short', true );
		if ( ! $word ) {
			$word = mb_strtoupper( implode( '', array_map( function ( $w ) {
				return mb_substr( $w, 0, 1 );
			}, array_slice( preg_split( '/\s+/', wp_strip_all_tags( get_the_title( $post ) ) ), 0, 3 ) ) ) );
		}
		$art = '<span class="word">' . esc_html( $word ) . '</span>';
	}
	$type = inpo_core_pub_type( $post );
	return '<div class="cover cv-' . $variant . '" aria-hidden="true">' . $art
		. '<div class="cover-top"><span class="pill">' . esc_html( inpo_core_type_label( $type ) ) . '</span><span class="cover-year">' . esc_html( inpo_core_pub_year( $post ) ) . '</span></div>'
		. '<span class="cover-title">' . inpo_core_title( $post ) . '</span>'
		. '<div class="cover-foot"><span class="cover-mark">' . esc_html( inpo_core_settings( 'short_name' ) ) . '</span></div></div>';
}

/**
 * Data attributes that open a PDF in the reader (assets/reader.js).
 */
function inpo_core_reader_attrs( $post, $pdf ) {
	wp_enqueue_script( 'inpo-core-reader' );
	$type = inpo_core_pub_type( $post );
	return ' data-pdf="' . esc_url( $pdf['url'] ) . '" data-pdf-title="' . esc_attr( wp_strip_all_tags( get_the_title( $post ) ) ) . '"'
		. ' data-pdf-meta="' . esc_attr( inpo_core_type_label( $type ) . ' · ' . inpo_core_pub_year( $post ) ) . '"'
		. ' data-pdf-link="' . esc_url( get_permalink( $post ) ) . '"';
}

/**
 * Publication card: cover, title and, when there is a PDF, a "Lexo" button that opens the reader.
 */
function inpo_core_pub_card( $post, $index = 0 ) {
	$url   = get_permalink( $post );
	$pdf   = inpo_core_pub_pdf( $post );
	$read  = $pdf
		? '<button type="button" class="pub-read"' . inpo_core_reader_attrs( $post, $pdf ) . ' aria-label="' . esc_attr( 'Lexo: ' . wp_strip_all_tags( get_the_title( $post ) ) ) . '">' . inpo_core_icon( 'book' ) . ' Lexo</button>'
		: '';
	return '<div class="pub"><a class="pub-cover" href="' . esc_url( $url ) . '" tabindex="-1" aria-hidden="true">' . inpo_core_cover( $post, $index ) . '</a>'
		. '<a class="pub-title" href="' . esc_url( $url ) . '">' . inpo_core_title( $post ) . '</a>' . $read . '</div>';
}

/**
 * News row (date, category, title, arrow). Also used for publications in search results.
 */
function inpo_core_news_row( $post ) {
	if ( INPO_CORE_PUB === $post->post_type ) {
		$type  = inpo_core_pub_type( $post );
		$meta  = '<time>' . esc_html( inpo_core_pub_year( $post ) ) . '</time><span>' . esc_html( inpo_core_type_label( $type ) ) . '</span>';
	} elseif ( INPO_CORE_PROJECT === $post->post_type ) {
		$meta = '<time>' . esc_html( get_post_meta( $post->ID, 'inpo_period', true ) ) . '</time><span>Projekt</span>';
	} elseif ( INPO_CORE_DATASET === $post->post_type ) {
		$meta = '<time>' . esc_html( get_the_date( 'Y', $post ) ) . '</time><span>Të dhëna</span>';
	} elseif ( 'page' === $post->post_type ) {
		$meta = '<time>Faqe</time><span>' . esc_html( get_bloginfo( 'name' ) ) . '</span>';
	} else {
		$meta = inpo_core_time( $post ) . '<span>' . esc_html( inpo_core_post_category_name( $post ) ) . '</span>';
	}
	return '<a class="row" href="' . esc_url( get_permalink( $post ) ) . '"><div class="row-meta">' . $meta . '</div><span class="row-title">' . inpo_core_title( $post ) . '</span><span class="arrow-circle">' . inpo_core_icon( 'arrow' ) . '</span></a>';
}

/**
 * Remembers posts already shown on this page, so later lists can skip them
 * (the home page news list skips the stories in the bento).
 *
 * @param int|null $id Post ID to add.
 * @return int[]
 */
function inpo_core_shown( $id = null ) {
	static $ids = array();
	if ( $id ) {
		$ids[] = (int) $id;
	}
	return $ids;
}

/**
 * Reading time in minutes. Migration placeholders count as a typical full text.
 */
function inpo_core_read_minutes( $post ) {
	$text  = trim( wp_strip_all_tags( $post->post_content . ' ' . $post->post_excerpt ) );
	$words = '' === $text ? 0 : count( preg_split( '/\s+/u', $text ) );
	if ( get_post_meta( $post->ID, 'inpo_todo', true ) ) {
		$words += 180;
	}
	return max( 2, (int) round( $words / 200 ) );
}

/**
 * Numbered pagination styled as chips. Works for the main query or a custom one.
 *
 * @param WP_Query|null $query Query, defaults to the main query.
 * @return string
 */
function inpo_core_pager( $query = null ) {
	global $wp_query;
	$query = $query ? $query : $wp_query;
	if ( $query->max_num_pages < 2 ) {
		return '';
	}
	$links = paginate_links(
		array(
			'total'     => $query->max_num_pages,
			'current'   => max( 1, (int) get_query_var( 'paged' ) ),
			'type'      => 'array',
			'prev_text' => 'Prapa',
			'next_text' => 'Para',
			'mid_size'  => 1,
		)
	);
	if ( ! $links ) {
		return '';
	}
	return '<nav class="pager" aria-label="Faqet">' . implode( '', $links ) . '</nav>';
}

/**
 * Formats a stat number like the prototype count-up (4.98 -> 4,98).
 */
function inpo_core_format_number( $value, $decimals ) {
	return number_format( (float) $value, max( 0, (int) $decimals ), ',', '.' );
}

/**
 * Finds a post by ID or slug (block attributes accept either, patterns use slugs).
 *
 * @param int    $id        Post ID.
 * @param string $slug      Post slug.
 * @param string $post_type Post type.
 * @return WP_Post|null
 */
function inpo_core_find_post( $id, $slug = '', $post_type = 'post' ) {
	if ( $id ) {
		$post = get_post( (int) $id );
		if ( $post && 'publish' === $post->post_status ) {
			return $post;
		}
	}
	if ( $slug ) {
		$found = get_posts(
			array(
				'name'           => sanitize_title( $slug ),
				'post_type'      => $post_type,
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);
		if ( $found ) {
			return $found[0];
		}
	}
	return null;
}

/* ---------- Thematic fields, institutions ---------- */

/**
 * Terms of a taxonomy that are in use, ordered by name: slug => name.
 */
function inpo_core_terms_in_use( $taxonomy ) {
	$terms = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => true, 'orderby' => 'name' ) );
	$out   = array();
	foreach ( is_wp_error( $terms ) ? array() : $terms as $t ) {
		$out[ $t->slug ] = $t->name;
	}
	return $out;
}

/**
 * Publication types hidden from the library (transparency reports and job calls live elsewhere).
 *
 * @return int[] Term IDs.
 */
function inpo_core_hidden_type_ids() {
	static $ids = null;
	if ( null === $ids ) {
		$ids = array_map( 'intval', get_terms( array( 'taxonomy' => INPO_CORE_PUB_TYPE, 'hide_empty' => false, 'fields' => 'ids', 'meta_key' => 'inpo_hidden', 'meta_value' => '1' ) ) );
	}
	return $ids;
}

/* ---------- Projects ---------- */

function inpo_core_projects_url( $args = array() ) {
	$url = get_post_type_archive_link( INPO_CORE_PROJECT );
	$url = $url ? $url : home_url( '/projektet/' );
	return $args ? add_query_arg( array_map( 'rawurlencode', $args ), $url ) : $url;
}

function inpo_core_project_active( $post ) {
	return 'perfunduar' !== get_post_meta( $post->ID, 'inpo_status', true );
}

/**
 * Lines stored as an array meta (objectives, results), without empties.
 *
 * @return string[]
 */
function inpo_core_lines( $id, $key, $term = false ) {
	$v = $term ? get_term_meta( $id, $key, true ) : get_post_meta( $id, $key, true );
	if ( is_string( $v ) ) {
		$v = preg_split( '/\R/', $v );
	}
	return array_values( array_filter( array_map( 'trim', (array) $v ), 'strlen' ) );
}

/**
 * Figures from lines "number | label" (e.g. "160 | aktivitete prokurimi të monitoruara").
 *
 * @return array[] [ number, label ]
 */
function inpo_core_figures( $lines ) {
	$out = array();
	foreach ( (array) $lines as $line ) {
		$parts = array_map( 'trim', explode( '|', $line, 2 ) );
		if ( 2 === count( $parts ) && '' !== $parts[0] ) {
			$out[] = $parts;
		}
	}
	return $out;
}

/**
 * Figures as a definition list (number big, label small), on any background.
 */
function inpo_core_impact_grid( $figures, $class = '' ) {
	$html = '';
	foreach ( $figures as $f ) {
		$html .= '<div><dt>' . esc_html( $f[1] ) . '</dt><dd>' . esc_html( $f[0] ) . '</dd></div>';
	}
	return $html ? '<dl class="impact-grid' . ( $class ? ' ' . esc_attr( $class ) : '' ) . '">' . $html . '</dl>' : '';
}

function inpo_core_project_card( $post ) {
	$active   = inpo_core_project_active( $post );
	$period   = get_post_meta( $post->ID, 'inpo_period', true );
	$donor    = get_post_meta( $post->ID, 'inpo_donor_text', true );
	$partners = get_post_meta( $post->ID, 'inpo_partners', true );
	$meta     = '';
	// Cards leave out values still in [brackets]; the project page lists every field.
	$period = inpo_core_is_placeholder( $period ) ? '' : $period;
	if ( $donor && ! inpo_core_is_placeholder( $donor ) ) {
		$meta .= '<div><dt>Donatori</dt><dd>' . esc_html( $donor ) . '</dd></div>';
	}
	if ( $partners && ! inpo_core_is_placeholder( $partners ) ) {
		$meta .= '<div><dt>Partnerët</dt><dd>' . esc_html( $partners ) . '</dd></div>';
	}
	return '<a class="proj" href="' . esc_url( get_permalink( $post ) ) . '"><div class="proj-top"><span class="pill' . ( $active ? ' on' : '' ) . '">' . ( $active ? 'Aktiv' : 'I përfunduar' ) . '</span>'
		. ( $period ? '<span class="proj-period">' . esc_html( $period ) . '</span>' : '' ) . '</div>'
		. '<h3>' . inpo_core_title( $post ) . '</h3>' . ( $meta ? '<dl class="proj-meta">' . $meta . '</dl>' : '' )
		. '<span class="link-u">Shiko projektin ' . inpo_core_icon( 'arrow' ) . '</span></a>';
}

/* ---------- Results and impact ---------- */

/**
 * Value of an impact figure. Sources count real content, so the numbers stay current.
 *
 * @param string $source manual, reagime, publikime, monitorime, projekte, vite.
 * @param string $manual Number typed by an editor.
 */
function inpo_core_impact_value( $source, $manual ) {
	switch ( $source ) {
		case 'reagime':
			$cat = get_term_by( 'slug', 'reagime-dhe-qendrime-publike', 'category' );
			return $cat ? (string) $cat->count : '';
		case 'publikime':
			$q = new WP_Query( array( 'post_type' => INPO_CORE_PUB, 'post_status' => 'publish', 'posts_per_page' => 1, 'fields' => 'ids', 'tax_query' => inpo_core_hidden_type_ids() ? array( array( 'taxonomy' => INPO_CORE_PUB_TYPE, 'terms' => inpo_core_hidden_type_ids(), 'operator' => 'NOT IN' ) ) : array() ) );
			return (string) $q->found_posts;
		case 'monitorime':
			$t = get_term_by( 'slug', 'raporte', INPO_CORE_PUB_TYPE );
			return $t ? (string) $t->count : '';
		case 'projekte':
			$c = wp_count_posts( INPO_CORE_PROJECT );
			return isset( $c->publish ) ? (string) $c->publish : '';
		case 'vite':
			return (string) ( (int) wp_date( 'Y' ) - 2005 );
	}
	return trim( (string) $manual );
}

/* ---------- Transparency documents ---------- */

/**
 * File of a document: its own PDF field, else the PDF of the publication it was copied from.
 *
 * @return array|null { url, size }
 */
function inpo_core_doc_file( $post ) {
	$url = get_post_meta( $post->ID, 'inpo_pdf', true );
	if ( $url ) {
		$id = attachment_url_to_postid( $url );
		return array( 'url' => $url, 'size' => $id ? (int) @filesize( get_attached_file( $id ) ) : 0 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	}
	$source = (int) get_post_meta( $post->ID, 'inpo_source', true );
	$source = $source ? get_post( $source ) : null;
	if ( $source ) {
		$pdf = inpo_core_pub_pdf( $source );
		return $pdf ? array( 'url' => $pdf['url'], 'size' => $pdf['size'] ) : null;
	}
	return null;
}

/**
 * True for a value that is still a placeholder, written in [square brackets].
 */
function inpo_core_is_placeholder( $value ) {
	$value = trim( wp_strip_all_tags( (string) $value ) );
	return '' !== $value && '[' === $value[0];
}

/**
 * Columns for a grid of $n tiles (5, 4 or 3) that leave the fewest empty cells in the last row.
 */
function inpo_core_grid_cols( $n ) {
	$best = 5;
	$gap  = PHP_INT_MAX;
	foreach ( array( 5, 4, 3 ) as $cols ) {
		$empty = ( $cols - $n % $cols ) % $cols;
		if ( $n >= $cols && $empty < $gap ) {
			$best = $cols;
			$gap  = $empty;
		}
	}
	return $n < 3 ? max( 1, $n ) : $best;
}
