<?php
/**
 * Filters for the news list (Lajme) and the publication library (Publikimet).
 *
 * One engine for both: search (?q=), one facet as chips with counts (?kategoria= or ?lloji=),
 * programme (?programi=), thematic field (?fusha=) and institution (?institucioni=) for publications,
 * year (?viti=) and order (?rendit=). The same state builds the page's
 * main query (pre_get_posts), the chip counts and the active-filter tags, so they always agree.
 * Works without JS (plain GET form); assets/view.js makes it instant.
 */

defined( 'ABSPATH' ) || exit;

/**
 * The two filterable lists.
 */
function inpo_core_filter_kinds() {
	return array(
		'pub'  => array(
			'post_type' => INPO_CORE_PUB,
			'facet'     => 'lloji',
			'taxonomy'  => INPO_CORE_PUB_TYPE,
			'label'     => 'Kërko në publikime',
			'chips'     => 'Filtro sipas kategorisë',
			'one'       => 'publikim',
			'many'      => 'publikime',
			'none'      => 'Asnjë publikim nuk përputhet me këtë kërkim.',
		),
		'news' => array(
			'post_type' => 'post',
			'facet'     => 'kategoria',
			'taxonomy'  => 'category',
			'label'     => 'Kërko në lajme',
			'chips'     => 'Filtro sipas kategorisë',
			'one'       => 'artikull',
			'many'      => 'artikuj',
			'none'      => 'Asnjë lajm nuk përputhet me këtë kërkim.',
		),
	);
}

/**
 * Order options. The first is the default and never appears in the URL.
 */
function inpo_core_filter_sorts() {
	return array(
		''           => 'Më të rejat',
		'te-vjetrat' => 'Më të vjetrat',
		'titulli'    => 'Sipas titullit',
	);
}

/**
 * The facet terms offered as chips, in display order: slug => label.
 */
function inpo_core_filter_facets( $kind ) {
	$out = array();
	if ( 'pub' === $kind ) {
		$hidden = inpo_core_hidden_type_ids();
		foreach ( inpo_core_pub_types() as $t ) {
			if ( ! in_array( (int) $t->term_id, $hidden, true ) ) {
				$out[ $t->slug ] = inpo_core_type_plural( $t );
			}
		}
		return $out;
	}
	// All sections are listed here; the chips leave out the ones without posts (inpo_core_filter_empty_facets).
	$args = array( 'taxonomy' => 'category', 'hide_empty' => ! inpo_core_chip_category_ids(), 'exclude' => array( (int) get_option( 'default_category' ) ), 'orderby' => 'name' );
	if ( inpo_core_chip_category_ids() ) {
		$args['include'] = inpo_core_chip_category_ids();
		unset( $args['exclude'] );
	}
	$terms = get_terms( $args );
	$terms = is_wp_error( $terms ) ? array() : $terms;
	usort(
		$terms,
		function ( $a, $b ) {
			$x = get_term_meta( $a->term_id, 'inpo_order', true );
			$y = get_term_meta( $b->term_id, 'inpo_order', true );
			return ( '' === $x ? 99 : (int) $x ) - ( '' === $y ? 99 : (int) $y );
		}
	);
	foreach ( $terms as $t ) {
		$out[ $t->slug ] = $t->name;
	}
	return $out;
}

/**
 * Sanitised filter state from the URL. On a category archive the category is the facet.
 */
function inpo_core_filter_state( $kind ) {
	static $cache = array();
	if ( isset( $cache[ $kind ] ) ) {
		return $cache[ $kind ];
	}
	$k   = inpo_core_filter_kinds()[ $kind ];
	$get = function ( $name ) {
		return isset( $_GET[ $name ] ) ? inpo_core_str( wp_unslash( $_GET[ $name ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	};
	$sort  = sanitize_key( $get( 'rendit' ) );
	$state = array(
		'q'        => trim( sanitize_text_field( $get( 'q' ) ) ),
		'facet'    => sanitize_title( $get( $k['facet'] ) ),
		'programi' => sanitize_title( $get( 'programi' ) ),
		'fusha'    => 'pub' === $kind ? sanitize_title( $get( 'fusha' ) ) : '',
		'institucioni' => 'pub' === $kind ? sanitize_title( $get( 'institucioni' ) ) : '',
		'viti'     => absint( $get( 'viti' ) ),
		'rendit'   => array_key_exists( $sort, inpo_core_filter_sorts() ) ? $sort : '',
	);
	if ( 'news' === $kind && '' === $state['facet'] && is_category() ) {
		$state['facet'] = get_queried_object()->slug;
	}
	$cache[ $kind ] = $state;
	return $state;
}

/**
 * True when anything narrows or reorders the list.
 */
function inpo_core_filter_active( $state ) {
	return '' !== $state['q'] || '' !== $state['facet'] || '' !== $state['programi'] || ! empty( $state['fusha'] ) || ! empty( $state['institucioni'] ) || $state['viti'] || '' !== $state['rendit'];
}

/**
 * Query vars for a state. $facet = false leaves the facet out (used for the chip counts).
 */
function inpo_core_filter_query_vars( $kind, $state, $facet = true ) {
	$k    = inpo_core_filter_kinds()[ $kind ];
	$vars = array( 'post_type' => $k['post_type'], 'post_status' => 'publish', 'tax_query' => array() );
	if ( '' !== $state['q'] ) {
		$vars['s'] = $state['q'];
	}
	if ( $state['viti'] ) {
		$vars['year'] = $state['viti'];
	}
	if ( '' !== $state['programi'] ) {
		$vars['tax_query'][] = array( 'taxonomy' => INPO_CORE_PROGRAM, 'field' => 'slug', 'terms' => $state['programi'] );
	}
	if ( ! empty( $state['fusha'] ) ) {
		$vars['tax_query'][] = array( 'taxonomy' => INPO_CORE_THEME, 'field' => 'slug', 'terms' => $state['fusha'] );
	}
	if ( ! empty( $state['institucioni'] ) ) {
		$vars['tax_query'][] = array( 'taxonomy' => INPO_CORE_INST, 'field' => 'slug', 'terms' => $state['institucioni'] );
	}
	if ( $facet && '' !== $state['facet'] ) {
		$vars['tax_query'][] = array( 'taxonomy' => $k['taxonomy'], 'field' => 'slug', 'terms' => $state['facet'], 'include_children' => false );
	} elseif ( 'pub' === $kind && inpo_core_hidden_type_ids() ) {
		// Transparency reports and job calls have their own pages; an explicit ?lloji= still shows them.
		$vars['tax_query'][] = array( 'taxonomy' => INPO_CORE_PUB_TYPE, 'terms' => inpo_core_hidden_type_ids(), 'operator' => 'NOT IN' );
	}
	if ( 'te-vjetrat' === $state['rendit'] ) {
		$vars['orderby'] = array( 'date' => 'ASC', 'ID' => 'ASC' );
	} elseif ( 'titulli' === $state['rendit'] ) {
		$vars['orderby'] = array( 'title' => 'ASC' );
	} else {
		$vars['orderby'] = 'pub' === $kind ? array( 'menu_order' => 'ASC', 'date' => 'DESC' ) : array( 'date' => 'DESC' );
	}
	return $vars;
}

/**
 * Applies the state to the page's main query (publication archive, news page, category archive).
 * The facet and programme are set as an explicit tax query; WordPress's own taxonomy query vars
 * (?lloji=, ?programi=) are cleared so the archive keeps its template.
 */
function inpo_core_filter_apply( $query, $kind ) {
	$state = inpo_core_filter_state( $kind );
	$vars  = inpo_core_filter_query_vars( $kind, $state, ! ( 'news' === $kind && $query->is_category() ) );
	foreach ( array( 's', 'year', 'orderby' ) as $key ) {
		if ( isset( $vars[ $key ] ) ) {
			$query->set( $key, $vars[ $key ] );
		}
	}
	if ( $vars['tax_query'] ) {
		$query->set( 'tax_query', $vars['tax_query'] );
	}
	$query->set( 'lloji', '' );
	$query->set( 'programi', '' );
}

/**
 * Chip counts under the other active filters: array( '' => total, slug => n ).
 * Two queries (ids, then their terms). Cached until content changes, except for free-text searches.
 */
function inpo_core_filter_counts( $kind, $state ) {
	$k    = inpo_core_filter_kinds()[ $kind ];
	$vars = inpo_core_filter_query_vars( $kind, $state, false );
	unset( $vars['orderby'] );
	$key = '';
	if ( '' === $state['q'] ) {
		$key = 'inpo_fc_' . md5( $kind . wp_json_encode( $vars ) . get_option( 'inpo_core_filter_version', 0 ) . apply_filters( 'wpml_current_language', '' ) );
		$hit = get_transient( $key );
		if ( is_array( $hit ) ) {
			return $hit;
		}
	}
	$ids = ( new WP_Query( $vars + array( 'fields' => 'ids', 'posts_per_page' => -1, 'no_found_rows' => true, 'update_post_meta_cache' => false ) ) )->posts;
	$out = array( '' => count( $ids ) );
	if ( $ids ) {
		$terms = wp_get_object_terms( $ids, $k['taxonomy'], array( 'fields' => 'all_with_object_id' ) );
		foreach ( is_wp_error( $terms ) ? array() : $terms as $t ) {
			$out[ $t->slug ] = isset( $out[ $t->slug ] ) ? $out[ $t->slug ] + 1 : 1;
		}
	}
	if ( $key ) {
		set_transient( $key, $out, 12 * HOUR_IN_SECONDS );
	}
	return $out;
}

/**
 * Content changed: older cached counts no longer apply.
 */
function inpo_core_filter_bump() {
	update_option( 'inpo_core_filter_version', time(), false );
}
add_action( 'save_post', 'inpo_core_filter_bump' );
add_action( 'deleted_post', 'inpo_core_filter_bump' );
add_action( 'set_object_terms', 'inpo_core_filter_bump' );
add_action( 'edited_term', 'inpo_core_filter_bump' );

/**
 * Years that have content, newest first.
 */
function inpo_core_filter_years( $kind ) {
	global $wpdb;
	$type = inpo_core_filter_kinds()[ $kind ]['post_type'];
	return array_map( 'intval', $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT YEAR(post_date) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish' ORDER BY 1 DESC", $type ) ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
}

/**
 * True when some content of this kind carries a programme (the select is hidden otherwise).
 */
function inpo_core_filter_has_programs( $kind ) {
	return (bool) get_posts(
		array(
			'post_type'      => inpo_core_filter_kinds()[ $kind ]['post_type'],
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'tax_query'      => array( array( 'taxonomy' => INPO_CORE_PROGRAM, 'operator' => 'EXISTS' ) ),
		)
	);
}

/**
 * The list's own URL.
 */
function inpo_core_filter_base( $kind ) {
	return 'pub' === $kind ? inpo_core_pubs_url() : inpo_core_news_url();
}

/**
 * URL for a state, with parameters in a fixed order and defaults left out.
 */
function inpo_core_filter_url( $kind, $state ) {
	$k    = inpo_core_filter_kinds()[ $kind ];
	$args = array(
		'q'          => $state['q'],
		$k['facet']  => $state['facet'],
		'programi'   => $state['programi'],
		'fusha'      => isset( $state['fusha'] ) ? $state['fusha'] : '',
		'institucioni' => isset( $state['institucioni'] ) ? $state['institucioni'] : '',
		'viti'       => $state['viti'] ? (string) $state['viti'] : '',
		'rendit'     => $state['rendit'],
	);
	$args = array_filter( $args, 'strlen' );
	return $args ? add_query_arg( array_map( 'rawurlencode', $args ), inpo_core_filter_base( $kind ) ) : inpo_core_filter_base( $kind );
}

/**
 * A pill-shaped select: visible key and value; the native select covers the pill.
 */
function inpo_core_filter_select( $id, $name, $label, $options, $value ) {
	$value   = (string) $value;
	$current = isset( $options[ $value ] ) ? $options[ $value ] : reset( $options );
	$html    = '';
	foreach ( $options as $v => $l ) {
		$html .= '<option value="' . esc_attr( $v ) . '"' . selected( $value, (string) $v, false ) . '>' . esc_html( $l ) . '</option>';
	}
	return '<div class="sel-pill' . ( '' !== $value && '0' !== $value ? ' is-set' : '' ) . '"><label class="sel-k" for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label>'
		. '<span class="sel-v" aria-hidden="true">' . esc_html( $current ) . '</span><select id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '">' . $html . '</select></div>';
}

/**
 * The filter form, the live count and the active filters. Returns the HTML that goes above the results.
 *
 * @param string $kind  'pub' or 'news'.
 * @param int    $found Number of results for the current state.
 * @param array  $opts  placeholder, programLabel, allLabel.
 */
function inpo_core_filters_html( $kind, $found, $opts = array() ) {
	$k      = inpo_core_filter_kinds()[ $kind ];
	$state  = inpo_core_filter_state( $kind );
	$facets = inpo_core_filter_facets( $kind );
	$counts = inpo_core_filter_counts( $kind, $state );
	$opts   = wp_parse_args( $opts, array( 'placeholder' => '', 'programLabel' => 'Programi', 'allLabel' => 'Të gjitha' ) );

	// Selects: programme (when content has one), year (when there is more than one), order.
	$selects = '';
	if ( '' !== $state['programi'] || inpo_core_filter_has_programs( $kind ) ) {
		$options = array( '' => 'Të gjitha' );
		foreach ( inpo_core_programs() as $t ) {
			$options[ $t->slug ] = $t->name;
		}
		$selects .= inpo_core_filter_select( $kind . '-prog', 'programi', $opts['programLabel'], $options, $state['programi'] );
	}
	// Publications: thematic field and institution, as in INPO's brief (once publications carry them).
	if ( 'pub' === $kind ) {
		foreach ( array( 'fusha' => array( INPO_CORE_THEME, 'Fusha tematike' ), 'institucioni' => array( INPO_CORE_INST, 'Institucioni' ) ) as $name => $def ) {
			$used = inpo_core_terms_in_use( $def[0] );
			if ( $used || '' !== $state[ $name ] ) {
				$selects .= inpo_core_filter_select( $kind . '-' . $name, $name, $def[1], array( '' => 'Të gjitha' ) + $used, $state[ $name ] );
			}
		}
	}
	$years = inpo_core_filter_years( $kind );
	if ( count( $years ) > 1 ) {
		$options = array( '' => 'Të gjitha' );
		foreach ( $years as $y ) {
			$options[ (string) $y ] = (string) $y;
		}
		$selects .= inpo_core_filter_select( $kind . '-year', 'viti', 'Viti', $options, $state['viti'] ? (string) $state['viti'] : '' );
	}
	// Chips: submit buttons, so they work without JS. The hidden field carries the facet for the other controls.
	// Categories without any content stay out of the way (the chosen one always shows).
	$chips = '';
	$empty = inpo_core_filter_empty_facets( $kind );
	foreach ( array( '' => $opts['allLabel'] ) + $facets as $slug => $label ) {
		if ( '' !== (string) $slug && (string) $slug !== $state['facet'] && in_array( (string) $slug, $empty, true ) ) {
			continue;
		}
		$n      = isset( $counts[ $slug ] ) ? $counts[ $slug ] : 0;
		$chips .= '<button type="submit" class="chip" name="' . esc_attr( $k['facet'] ) . '" value="' . esc_attr( $slug ) . '" aria-pressed="' . ( (string) $slug === $state['facet'] ? 'true' : 'false' ) . '">'
			. esc_html( $label ) . '<span class="chip-n">' . (int) $n . '</span></button>';
	}

	$form = '<form class="filters" method="get" action="' . esc_url( inpo_core_filter_base( $kind ) ) . '" role="search" aria-label="' . esc_attr( $k['label'] ) . '" data-inpo-filter="' . esc_attr( $kind ) . '">'
		// First submit button = the one Enter uses, so Enter in the search field keeps the chosen chip.
		. '<button type="submit" class="sr" tabindex="-1" aria-hidden="true">Kërko</button>'
		. '<input type="hidden" name="' . esc_attr( $k['facet'] ) . '" value="' . esc_attr( $state['facet'] ) . '">'
		. '<div class="filters-top"><div class="search-inline">' . inpo_core_icon( 'search' ) . '<label class="sr" for="' . esc_attr( $kind ) . '-q">' . esc_html( $k['label'] ) . '</label>'
		. '<input id="' . esc_attr( $kind ) . '-q" name="q" type="search" placeholder="' . esc_attr( $opts['placeholder'] ) . '" value="' . esc_attr( $state['q'] ) . '" autocomplete="off"></div>'
		. ( '' !== $selects ? '<div class="filters-sel">' . $selects . '</div>' : '' ) . '</div>'
		. '<div class="chips" role="group" aria-label="' . esc_attr( $k['chips'] ) . '">' . $chips . '</div>'
		. '<noscript><p class="filters-ns"><button class="btn btn-ink btn-sm" type="submit">Filtro</button></p></noscript>'
		// The count, the active filters and the order, in one line above the results.
		. '<div class="res-bar"><p class="res-count" id="' . esc_attr( $kind ) . '-count" role="status">' . esc_html( 1 === $found ? '1 ' . $k['one'] : $found . ' ' . $k['many'] ) . '</p>'
		. '<div class="res-active" id="' . esc_attr( $kind ) . '-active">' . inpo_core_filter_tags( $kind, $state, $facets ) . '</div>'
		. '<div class="res-sort">' . inpo_core_filter_select( $kind . '-sort', 'rendit', 'Rendit', inpo_core_filter_sorts(), $state['rendit'] ) . '</div></div>'
		. '</form>';

	return $form;
}

/**
 * Facet terms with no published content of this kind (hidden from the chips).
 */
function inpo_core_filter_empty_facets( $kind ) {
	$taxonomy = 'pub' === $kind ? INPO_CORE_PUB_TYPE : 'category';
	$empty    = array();
	foreach ( array_keys( inpo_core_filter_facets( $kind ) ) as $slug ) {
		$term = get_term_by( 'slug', $slug, $taxonomy );
		if ( ! $term || ! (int) $term->count ) {
			$empty[] = (string) $slug;
		}
	}
	return $empty;
}

/**
 * Removable tags for the active filters, as links (no JS needed).
 */
function inpo_core_filter_tags( $kind, $state, $facets ) {
	$x    = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>';
	$tags = array();
	if ( '' !== $state['q'] ) {
		$tags['q'] = '“' . $state['q'] . '”';
	}
	if ( '' !== $state['facet'] ) {
		$tags['facet'] = isset( $facets[ $state['facet'] ] ) ? $facets[ $state['facet'] ] : $state['facet'];
	}
	if ( '' !== $state['programi'] ) {
		$term             = get_term_by( 'slug', $state['programi'], INPO_CORE_PROGRAM );
		$tags['programi'] = $term ? $term->name : $state['programi'];
	}
	foreach ( array( 'fusha' => INPO_CORE_THEME, 'institucioni' => INPO_CORE_INST ) as $key => $taxonomy ) {
		if ( ! empty( $state[ $key ] ) ) {
			$term         = get_term_by( 'slug', $state[ $key ], $taxonomy );
			$tags[ $key ] = $term ? $term->name : $state[ $key ];
		}
	}
	if ( $state['viti'] ) {
		$tags['viti'] = (string) $state['viti'];
	}
	$html = '';
	foreach ( $tags as $key => $label ) {
		$without         = $state;
		$without[ $key ] = 'viti' === $key ? 0 : '';
		$html           .= '<a class="tag" href="' . esc_url( inpo_core_filter_url( $kind, $without ) ) . '" aria-label="' . esc_attr( 'Hiq filtrin: ' . $label ) . '">' . esc_html( $label ) . $x . '</a>';
	}
	if ( count( $tags ) > 1 ) {
		$html .= '<a class="res-clear" href="' . esc_url( inpo_core_filter_base( $kind ) ) . '">Hiq të gjitha</a>';
	}
	return $html;
}

/**
 * Empty result: say so, and offer the two ways out.
 */
function inpo_core_filter_empty( $kind ) {
	$k     = inpo_core_filter_kinds()[ $kind ];
	$state = inpo_core_filter_state( $kind );
	$wide  = '';
	if ( '' !== $state['q'] && ( '' !== $state['facet'] || '' !== $state['programi'] || ! empty( $state['fusha'] ) || ! empty( $state['institucioni'] ) || $state['viti'] ) ) {
		$wide = '<a class="btn btn-line btn-sm" href="' . esc_url( inpo_core_filter_url( $kind, array( 'q' => $state['q'], 'facet' => '', 'programi' => '', 'fusha' => '', 'institucioni' => '', 'viti' => 0, 'rendit' => '' ) ) ) . '">' . esc_html( 'Kërko “' . $state['q'] . '” kudo' ) . '</a>';
	}
	if ( ! inpo_core_filter_active( $state ) ) {
		return '<div class="empty"><b>' . ( 'pub' === $kind ? 'Ende pa publikime.' : 'Ende pa lajme.' ) . '</b><span class="muted">' . ( 'pub' === $kind ? 'Publikimet shfaqen këtu.' : 'Lajmet dhe reagimet publikohen këtu.' ) . '</span></div>';
	}
	return '<div class="empty"><b>' . esc_html( $k['none'] ) . '</b><span class="muted">Provoni një fjalë tjetër ose hiqni disa filtra.</span>'
		. '<div class="hero-ctas">' . $wide . '<a class="btn btn-ink btn-sm" href="' . esc_url( inpo_core_filter_base( $kind ) ) . '">Hiq filtrat</a></div></div>';
}

/**
 * Filtered views are for visitors, not search engines: noindex, follow (core and Yoast).
 */
function inpo_core_filter_is_filtered_view() {
	if ( ! inpo_core_theme_active() ) {
		return false;
	}
	if ( is_post_type_archive( INPO_CORE_PUB ) ) {
		return inpo_core_filter_active( inpo_core_filter_state( 'pub' ) );
	}
	if ( is_home() || is_category() ) {
		$state = inpo_core_filter_state( 'news' );
		if ( is_category() ) {
			$state['facet'] = '';
		}
		return inpo_core_filter_active( $state );
	}
	return false;
}
add_filter(
	'wp_robots',
	function ( $robots ) {
		if ( inpo_core_filter_is_filtered_view() ) {
			$robots['noindex'] = true;
			$robots['follow']  = true;
			unset( $robots['max-image-preview'] );
		}
		return $robots;
	}
);
add_filter(
	'wpseo_robots',
	function ( $robots ) {
		return inpo_core_filter_is_filtered_view() ? 'noindex, follow' : $robots;
	}
);
