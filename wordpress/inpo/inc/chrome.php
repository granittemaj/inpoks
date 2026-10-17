<?php
/**
 * Site chrome: header (logo, the sections with dropdowns, language, theme, search), mobile menu,
 * and footer. Markup matches the prototype's index.html and buildChrome() in app.js.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Organisation details. INPO Core fills these from Settings > INPO via the "inpo_org_info" filter;
 * the values below are the published facts, used when the plugin is inactive.
 */
function inpo_theme_org() {
	static $org = null;
	if ( null === $org ) {
		$org = apply_filters(
			'inpo_org_info',
			array(
				'short_name' => 'INPO',
				'name_long' => 'Iniciativa për Progres',
				'about'     => 'Iniciativa për Progres promovon demokratizimin e qeverisjes lokale përmes aktivizmit qytetar.',
				'email'     => 'info@inpo-ks.org',
				'phone'     => '+383 (0) 49 354 000',
				'office1'   => 'Zyra në Prishtinë',
				'address1'  => "Rr. “Qamil Hoxha”, H32,\nnr. 09, kati IV\n10000 Prishtinë",
				'office2'   => 'Zyra në Ferizaj',
				'address2'  => "Rr. “Latif Hasani”, pn\n70000 Ferizaj",
				'facebook'  => 'https://www.facebook.com/OrganizataINPO/',
				'x'         => 'https://x.com/organizatainpo',
				'youtube'   => 'https://www.youtube.com/channel/UC5FqAZlDj2B05tV4HSiLWOg',
				'audit_url' => home_url( '/' ),
			)
		);
	}
	return $org;
}

/**
 * Is this URL the current page or one of its parents (so /lajme/ is current on an article)?
 */
function inpo_theme_is_current( $url ) {
	$path = wp_parse_url( $url, PHP_URL_PATH );
	$path = trailingslashit( $path ? $path : '/' );
	// Sections whose items do not live under the section's URL (e.g. articles at /slug/ on the live site).
	$section = inpo_theme_section_url();
	if ( $section && trailingslashit( (string) wp_parse_url( $section, PHP_URL_PATH ) ) === $path ) {
		return true;
	}
	$req  = isset( $_SERVER['REQUEST_URI'] ) ? wp_parse_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_PATH ) : '/';
	$req  = trailingslashit( $req ? $req : '/' );
	$home = trailingslashit( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ) );
	if ( $path === $home ) {
		return $req === $home;
	}
	return 0 === strpos( $req, $path );
}

/**
 * URL of the section the current view belongs to: the news page for articles, categories and tags,
 * the library for publications.
 */
function inpo_theme_section_url() {
	static $url = null;
	if ( null === $url ) {
		$url = '';
		if ( is_singular( 'post' ) || is_category() || is_tag() || is_date() || is_author() ) {
			$news = (int) get_option( 'page_for_posts' );
			$url  = $news ? get_permalink( $news ) : '';
		} elseif ( post_type_exists( 'publikimet' ) && ( is_singular( array( 'publikimet', 'inpo_dataset' ) ) || is_post_type_archive( array( 'publikimet', 'inpo_dataset' ) ) ) ) {
			// Data sits under "Hulumtime dhe publikime".
			$url = get_post_type_archive_link( 'publikimet' );
		} elseif ( post_type_exists( 'inpo_project' ) && is_singular( 'inpo_project' ) ) {
			$url = get_post_type_archive_link( 'inpo_project' );
		}
	}
	return $url;
}

/**
 * Menu as a tree: [ title, url, desc, target, children[] ].
 * Uses the "primary" location, or a sensible default before a menu exists.
 */
function inpo_theme_menu() {
	$locations = get_nav_menu_locations();
	$items     = ! empty( $locations['primary'] ) ? wp_get_nav_menu_items( $locations['primary'] ) : array();
	$tree      = array();
	if ( $items ) {
		$by_id = array();
		foreach ( $items as $item ) {
			$by_id[ $item->ID ] = array(
				'title'    => $item->title,
				'url'      => $item->url,
				'desc'     => $item->description,
				'short'    => $item->attr_title,
				'target'   => $item->target,
				'parent'   => (int) $item->menu_item_parent,
				'children' => array(),
			);
		}
		foreach ( $by_id as $id => $node ) {
			if ( $node['parent'] && isset( $by_id[ $node['parent'] ] ) ) {
				$by_id[ $node['parent'] ]['children'][] = $node;
			}
		}
		foreach ( $by_id as $node ) {
			if ( ! $node['parent'] ) {
				$tree[] = $node;
			}
		}
		return $tree;
	}

	$node = function ( $title, $url, $desc = '', $children = array() ) {
		return array( 'title' => $title, 'url' => $url, 'desc' => $desc, 'short' => '', 'target' => '', 'children' => $children );
	};
	$page = function ( $slug ) {
		$p = get_page_by_path( $slug );
		return $p ? get_permalink( $p ) : '';
	};
	$tree[] = $node( 'Kryefaqja', home_url( '/' ) );
	if ( $page( 'rreth-nesh' ) ) {
		$tree[] = $node( 'Rreth nesh', $page( 'rreth-nesh' ) );
	}
	if ( taxonomy_exists( 'inpo_program' ) ) {
		$terms = get_terms( array( 'taxonomy' => 'inpo_program', 'hide_empty' => false ) );
		if ( $terms && ! is_wp_error( $terms ) ) {
			$kids = array();
			foreach ( $terms as $t ) {
				$kids[] = $node( $t->name, get_term_link( $t ) );
			}
			$tree[] = $node( 'Programet', $kids[0]['url'], '', $kids );
		}
	}
	if ( post_type_exists( 'publikimet' ) && get_post_type_archive_link( 'publikimet' ) ) {
		$tree[] = $node( 'Hulumtime dhe publikime', get_post_type_archive_link( 'publikimet' ) );
	}
	if ( get_option( 'page_for_posts' ) ) {
		$tree[] = $node( 'Lajme dhe qëndrime', get_permalink( get_option( 'page_for_posts' ) ) );
	}
	if ( post_type_exists( 'inpo_project' ) && get_post_type_archive_link( 'inpo_project' ) ) {
		$tree[] = $node( 'Projektet dhe ndikimi', get_post_type_archive_link( 'inpo_project' ) );
	}
	foreach ( array( 'transparenca' => 'Transparenca', 'kontakti' => 'Kontakti' ) as $slug => $title ) {
		if ( $page( $slug ) ) {
			$tree[] = $node( $title, $page( $slug ) );
		}
	}
	return $tree;
}

/**
 * Language switch. Uses Polylang when active; otherwise SQ plus an EN placeholder.
 */
function inpo_theme_languages() {
	// WPML (used on inpo-ks.org).
	$wpml = apply_filters( 'wpml_active_languages', null, array( 'skip_missing' => 0, 'orderby' => 'id' ) );
	if ( is_array( $wpml ) && $wpml ) {
		$out = '';
		foreach ( $wpml as $l ) {
			$out .= '<a href="' . esc_url( $l['url'] ) . '" lang="' . esc_attr( $l['code'] ) . '" hreflang="' . esc_attr( $l['code'] ) . '"' . ( ! empty( $l['active'] ) ? ' aria-current="true"' : '' ) . '>' . esc_html( strtoupper( $l['code'] ) ) . '</a>';
		}
		return '<div class="lang" role="group" aria-label="Gjuha">' . $out . '</div>';
	}
	if ( function_exists( 'pll_the_languages' ) ) {
		$langs = pll_the_languages( array( 'raw' => 1, 'hide_if_empty' => 0 ) );
		$out   = '';
		foreach ( (array) $langs as $l ) {
			$out .= '<a href="' . esc_url( $l['url'] ) . '" lang="' . esc_attr( $l['locale'] ) . '"' . ( $l['current_lang'] ? ' aria-current="true"' : '' ) . '>' . esc_html( strtoupper( $l['slug'] ) ) . '</a>';
		}
		if ( $out ) {
			return '<div class="lang" role="group" aria-label="Gjuha">' . $out . '</div>';
		}
	}
	return '<div class="lang" role="group" aria-label="Gjuha"><a href="#" aria-current="true" data-noop>SQ</a><a href="#" lang="en" data-noop data-toast="Versioni në anglisht vjen me migrimin e përmbajtjes.">EN</a></div>';
}

function inpo_theme_icon( $name ) {
	$icons = array(
		'chevron' => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>',
		'moon'    => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8Z"/></svg>',
		'search'  => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>',
		'menu'    => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M5 9h14"/><path d="M5 15h14"/></svg>',
		'close'   => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>',
	);
	return $icons[ $name ];
}

/**
 * Wordmark or the uploaded logo.
 */
function inpo_theme_logo( $label = true ) {
	// Short name plus full name from the organisation details, independent of the site title
	// (on inpo-ks.org the title is the full name and the tagline is "INPO").
	$org  = inpo_theme_org();
	$name = ! empty( $org['short_name'] ) ? $org['short_name'] : get_bloginfo( 'name' );
	$desc = ! empty( $org['name_long'] ) ? $org['name_long'] : get_bloginfo( 'description' );
	$aria = $label ? ' aria-label="' . esc_attr( $name . ( $desc ? ', ' . $desc : '' ) . ', kryefaqja' ) . '"' : '';
	$logo = get_theme_mod( 'custom_logo' );
	if ( $logo ) {
		$img = wp_get_attachment_image( $logo, 'medium', false, array( 'alt' => $label ? '' : $name ) );
		return '<a class="logo" href="' . esc_url( home_url( '/' ) ) . '"' . $aria . '>' . $img . '</a>';
	}
	// INPO's logo, bundled with the theme. A Site Logo set in the editor replaces it.
	if ( file_exists( get_theme_file_path( 'assets/img/logo.png' ) ) && apply_filters( 'inpo_theme_bundled_logo', true ) ) {
		return '<a class="logo" href="' . esc_url( home_url( '/' ) ) . '"' . $aria . '><img src="' . esc_url( get_theme_file_uri( 'assets/img/logo.png' ) ) . '?v=' . esc_attr( INPO_THEME_VERSION ) . '" alt="' . ( $label ? '' : esc_attr( $name . ( $desc ? ', ' . $desc : '' ) ) ) . '" width="122" height="50"></a>';
	}
	return '<a class="logo" href="' . esc_url( home_url( '/' ) ) . '"' . $aria . '><b>' . esc_html( $name ) . '</b>' . ( $desc ? '<span>' . esc_html( $desc ) . '</span>' : '' ) . '</a>';
}

/**
 * Is this exact URL (path and query) the current request? For sub-items such as /publikimet/?lloji=x.
 */
function inpo_theme_is_exact( $url ) {
	if ( false !== strpos( $url, '#' ) ) {
		return false;
	}
	$want = wp_parse_url( $url );
	$req  = isset( $_SERVER['REQUEST_URI'] ) ? wp_parse_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) : array();
	$path = function ( $p ) {
		return trailingslashit( isset( $p['path'] ) ? $p['path'] : '/' );
	};
	return $path( $want ) === $path( $req ) && ( isset( $want['query'] ) ? $want['query'] : '' ) === ( isset( $req['query'] ) ? $req['query'] : '' );
}

function inpo_theme_link( $node, $attrs = '' ) {
	$target = $node['target'] ? ' target="' . esc_attr( $node['target'] ) . '" rel="noopener"' : '';
	// Sub-items with a query or an anchor are current only on that exact view.
	$exact  = false !== strpos( $node['url'], '?' ) || false !== strpos( $node['url'], '#' );
	$cur    = ( $exact ? inpo_theme_is_exact( $node['url'] ) : inpo_theme_is_current( $node['url'] ) ) ? ' aria-current="page"' : '';
	return '<a href="' . esc_url( $node['url'] ) . '"' . $attrs . $target . $cur . '>';
}

/**
 * Label in the desktop bar: the menu item's "Title Attribute" when set, else a short form of
 * the brief's long section names (the full names stay in the dropdowns, mobile menu and pages).
 */
function inpo_theme_short_label( $node ) {
	if ( ! empty( $node['short'] ) ) {
		return $node['short'];
	}
	$short = array(
		'Hulumtime dhe publikime' => 'Publikime',
		'Lajme dhe qëndrime'      => 'Lajme',
		'Projektet dhe ndikimi'   => 'Projektet',
	);
	return isset( $short[ $node['title'] ] ) ? $short[ $node['title'] ] : $node['title'];
}

function inpo_theme_render_header() {
	$menu           = inpo_theme_menu();
	$nav            = '';
	$mobile         = '';
	$home = untrailingslashit( home_url( '/' ) );
	foreach ( $menu as $i => $item ) {
		// The logo is the link home: "Kryefaqja" stays in the mobile menu only.
		if ( ! $item['children'] && untrailingslashit( $item['url'] ) === $home ) {
			$mobile .= inpo_theme_link( $item ) . esc_html( $item['title'] ) . '</a>';
			continue;
		}
		if ( $item['children'] ) {
			// A section with its own page (not just its first item) gets an overview link first.
			$overview = untrailingslashit( strtok( $item['url'], '#' ) ) !== untrailingslashit( strtok( $item['children'][0]['url'], '#' ) );
			$current  = inpo_theme_is_current( $item['url'] ) && $overview;
			$links    = $overview ? '<a class="dd-all" href="' . esc_url( $item['url'] ) . '">' . esc_html( $item['title'] ) . '<small>Përmbledhje</small></a>' : '';
			$sub      = $overview ? '<a href="' . esc_url( $item['url'] ) . '">' . esc_html( $item['title'] ) . ': përmbledhje</a>' : '';
			foreach ( $item['children'] as $child ) {
				$current = $current || inpo_theme_is_current( $child['url'] );
				$links  .= inpo_theme_link( $child ) . ( $child['desc'] ? '<b>' . esc_html( $child['title'] ) . '</b><small>' . esc_html( $child['desc'] ) . '</small>' : esc_html( $child['title'] ) ) . '</a>';
				$sub    .= inpo_theme_link( $child ) . esc_html( $child['title'] ) . '</a>';
			}
			$id      = 'dd-menu-' . $i;
			$nav    .= '<div class="dd' . ( $current ? ' current' : '' ) . '"><button type="button" aria-expanded="false" aria-controls="' . $id . '">' . esc_html( inpo_theme_short_label( $item ) ) . ' ' . inpo_theme_icon( 'chevron' ) . '</button><div class="dd-menu" id="' . $id . '">' . $links . '</div></div>';
			$mobile .= '<details class="menu-group"' . ( $current ? ' open' : '' ) . '><summary>' . esc_html( $item['title'] ) . ' ' . inpo_theme_icon( 'chevron' ) . '</summary><div class="menu-sub">' . $sub . '</div></details>';
		} else {
			$nav    .= inpo_theme_link( $item ) . esc_html( inpo_theme_short_label( $item ) ) . '</a>';
			$mobile .= inpo_theme_link( $item ) . esc_html( $item['title'] ) . '</a>';
		}

	}
	$langs     = inpo_theme_languages();

	$html  = '<a class="skip" href="#main">Kalo te përmbajtja</a>';
	$html .= '<header class="site-header" id="hdr"><div class="wrap hdr">' . inpo_theme_logo()
		. '<nav class="nav" aria-label="Kryesore">' . $nav . '</nav>'
		. '<div class="actions">' . $langs
		. '<button class="icon-btn" type="button" id="theme" aria-label="Ndrysho temën e ngjyrave">' . inpo_theme_icon( 'moon' ) . '</button>'
		. '<button class="icon-btn" type="button" id="open-search" aria-label="Kërko në faqe" aria-haspopup="dialog">' . inpo_theme_icon( 'search' ) . '</button>'
		. '<button class="icon-btn menu-btn" type="button" id="open-menu" aria-label="Hap menunë" aria-expanded="false" aria-haspopup="dialog">' . inpo_theme_icon( 'menu' ) . '</button>'
		. '</div></div></header>';

	$html .= '<dialog id="menu" aria-label="Menuja"><div class="menu-panel"><div class="menu-top">' . inpo_theme_logo( false )
		. '<button class="icon-btn" type="button" data-close aria-label="Mbyll menunë">' . inpo_theme_icon( 'close' ) . '</button></div>'
		. '<nav class="menu-links" aria-label="Menuja mobile">' . $mobile . '</nav>'
		. '<div class="menu-foot">' . $langs . '</div></div></dialog>';

	$html .= '<dialog id="search" aria-label="Kërko"><div class="search-panel"><form class="search-field" role="search" method="get" action="' . esc_url( home_url( '/' ) ) . '">'
		. str_replace( 'width="18" height="18"', 'width="22" height="22"', inpo_theme_icon( 'search' ) )
		. '<label for="q-global" class="sr">Kërko lajme dhe publikime</label><input id="q-global" name="s" type="search" placeholder="Kërko lajme dhe publikime" autocomplete="off"><kbd>Esc</kbd></form>'
		. '<div class="search-res" id="search-res" role="region" aria-live="polite" aria-label="Rezultatet"></div></div></dialog>';

	// Back to top: appears after scrolling down a screen (theme.js).
	$html .= '<button class="to-top" type="button" id="to-top" data-top aria-label="Kthehu lart"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 19V5"/><path d="m5 12 7-7 7 7"/></svg></button>';
	$html .= '<div class="toast" id="toast" role="status" aria-live="polite"></div>';
	return $html;
}

function inpo_theme_render_footer() {
	$o       = inpo_theme_org();
	$tel     = 'tel:' . preg_replace( '/[^0-9+]/', '', str_replace( '(0)', '', $o['phone'] ) );
	$socials = '';
	foreach ( array( 'facebook' => 'Facebook', 'x' => 'X', 'youtube' => 'YouTube' ) as $key => $label ) {
		if ( ! empty( $o[ $key ] ) ) {
			$socials .= '<a href="' . esc_url( $o[ $key ] ) . '" target="_blank" rel="noopener">' . esc_html( $label ) . '</a>';
		}
	}
	$office = function ( $title, $address, $cls ) {
		return '<div class="' . $cls . '"><h3>' . esc_html( $title ) . '</h3>' . nl2br( esc_html( trim( $address ) ), false ) . '</div>';
	};
	return '<footer class="foot"><div class="wrap"><div class="grid12 foot-cols">'
		. '<div class="foot-about"><p>' . esc_html( $o['about'] ) . '</p><div class="socials">' . $socials . '</div></div>'
		. $office( $o['office1'], $o['address1'], 'foot-c1' )
		. $office( $o['office2'], $o['address2'], 'foot-c2' )
		. '<div class="foot-c3"><h3>Kontakt</h3><a href="mailto:' . esc_attr( $o['email'] ) . '">' . esc_html( $o['email'] ) . '</a><a href="' . esc_attr( $tel ) . '">' . esc_html( $o['phone'] ) . '</a><a href="' . esc_url( $o['audit_url'] ) . '">Raportet e auditimit</a></div>'
		. '</div><div class="foot-bottom"><small>© ' . esc_html( wp_date( 'Y' ) ) . ' ' . esc_html( $o['name_long'] ) . '. Të gjitha të drejtat e rezervuara.</small><small class="foot-credit">Developed by <a href="https://www.papingu.com" target="_blank" rel="noopener">papingu</a></small></div></div></footer>';
}
