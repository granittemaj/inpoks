<?php
/**
 * Legal pages (privacy, terms, cookies) and the cookie notice.
 * The texts come from LEGAL in the seed (assets/js/data.js). Once created they are ordinary Pages, edited in wp-admin;
 * the cookie notice text is in INPO > Settings.
 */

defined( 'ABSPATH' ) || exit;

/** Short labels for the footer, by page slug. */
function inpo_core_legal_labels() {
	return array(
		'politika-e-privatesise' => 'Privatësia',
		'kushtet-e-perdorimit'   => 'Kushtet e përdorimit',
		'politika-e-cookies'     => 'Cookies',
	);
}

/**
 * Block markup for a legal page: the date, then each section as a heading with paragraphs and lists.
 *
 * @param array $page { title, lead, updated, sections: [ heading, [ text | text[] ] ] }.
 */
function inpo_core_legal_content( $page ) {
	$blocks   = array();
	$blocks[] = "<!-- wp:paragraph {\"className\":\"legal-date\"} -->\n<p class=\"legal-date\">Përditësuar më " . esc_html( $page['updated'] ) . "</p>\n<!-- /wp:paragraph -->";
	foreach ( $page['sections'] as $section ) {
		$blocks[] = "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">" . esc_html( $section[0] ) . "</h2>\n<!-- /wp:heading -->";
		foreach ( $section[1] as $item ) {
			if ( is_array( $item ) ) {
				$items = '';
				foreach ( $item as $text ) {
					$items .= "<!-- wp:list-item -->\n<li>" . esc_html( $text ) . "</li>\n<!-- /wp:list-item -->";
				}
				$blocks[] = "<!-- wp:list -->\n<ul class=\"wp-block-list\">" . $items . "</ul>\n<!-- /wp:list -->";
			} else {
				$blocks[] = "<!-- wp:paragraph -->\n<p>" . esc_html( $item ) . "</p>\n<!-- /wp:paragraph -->";
			}
		}
	}
	return implode( "\n\n", $blocks );
}

/**
 * Creates the legal pages that do not exist yet and makes the privacy page WordPress's own.
 * Pages that already exist (same slug) are left as they are.
 *
 * @param int[] $created IDs of the pages made here are added (restore puts them back to draft).
 * @return int[] slug => page ID.
 */
function inpo_core_setup_legal_pages( &$created ) {
	$seed = inpo_core_seed();
	$ids  = array();
	if ( empty( $seed['LEGAL'] ) ) {
		return $ids;
	}
	foreach ( $seed['LEGAL'] as $slug => $page ) {
		$existing = get_page_by_path( $slug );
		if ( $existing ) {
			$ids[ $slug ] = $existing->ID;
			continue;
		}
		$id = wp_insert_post(
			wp_slash(
				array(
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_name'    => $slug,
					'post_title'   => $page['title'],
					'post_excerpt' => $page['lead'],
					'post_content' => inpo_core_legal_content( $page ),
					'meta_input'   => array( 'inpo_kicker' => 'Informacion ligjor' ),
				)
			)
		);
		if ( $id && ! is_wp_error( $id ) ) {
			$ids[ $slug ] = $id;
			$created[]    = $id;
		}
	}
	// WordPress's own privacy page (login screen, personal data tools), unless one is already published.
	if ( isset( $ids['politika-e-privatesise'] ) ) {
		$current = (int) get_option( 'wp_page_for_privacy_policy' );
		if ( ! $current || 'publish' !== get_post_status( $current ) ) {
			update_option( 'wp_page_for_privacy_policy', $ids['politika-e-privatesise'] );
		}
	}
	return $ids;
}

/**
 * The published legal pages for the footer: [ slug => [ label, url ] ].
 */
function inpo_core_legal_links() {
	$links = array();
	foreach ( inpo_core_legal_labels() as $slug => $label ) {
		$page = get_page_by_path( $slug );
		if ( $page && 'publish' === $page->post_status ) {
			$links[ $slug ] = array( $label, get_permalink( $page ) );
		}
	}
	return $links;
}
add_filter(
	'inpo_legal_links',
	function () {
		return inpo_core_legal_links();
	}
);
