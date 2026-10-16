<?php
/**
 * INPO's brief (October 2026): the structure the team asked for, mapped onto the live content.
 *
 * Prepare (safe with the old theme) adds, without deleting or renaming anything:
 * - the five news sections as categories, filled from the old categories, tags and titles;
 * - the six publication categories (labels on the existing types, plus Analiza ligjore and
 *   Rekomandime për politika publike), with publications sorted into the new ones by title;
 * - thematic fields and institutions on publications, from their titles;
 * - transparency documents (audit reports, the work report), copied from "Raporte për INPO";
 * - programme objectives, impact examples and projects (as drafts) from facts on inpo-ks.org.
 * Keyword sorting runs once; editors can correct everything afterwards in wp-admin.
 */

defined( 'ABSPATH' ) || exit;

/* ---------- Definitions ---------- */

/**
 * News sections: slug => [ name, card label, order, old category slugs, title pattern, tag slugs ].
 */
function inpo_core_news_sections() {
	return array(
		'aktivitete'                   => array( 'Aktivitete', 'Aktivitet', 0, array( 'aktivitete-shkolla-e-aktivizmit', 'aktivitete-edukimi-demokratik', 'aktivitete-qeverisje-te-mire', 'aktivitetet-qeverisja-e-mire' ), '', array() ),
		'komunikata-per-media'         => array( 'Komunikata për media', 'Komunikatë për media', 1, array( 'publikime' ), '/^(INPO\s+)?(publikon|publikoi|publikuan|lanson)|publikohet|INPO,[^:]*publikuan|komunikat/iu', array() ),
		'reagime-dhe-qendrime-publike' => array( 'Reagime dhe qëndrime publike', 'Reagim publik', 2, array( 'reagim-publik', 'prokurimi-i-hapur' ), '/INPO\s+(kërkon|reagon|ngre|shpreh|bën thirrje|i bën thirrje|dënon|kundërshton|paralajmëron|kritikon)|reagim|qëndrim|kërkon anulimin|kërkon rishikim/iu', array( 'reagim', 'reagim-publik' ) ),
		'opinione-dhe-artikuj'         => array( 'Opinione dhe artikuj', 'Opinion', 3, array(), '/^opinion|kolumn|op-ed/iu', array() ),
		'inpo-ne-media'                => array( 'INPO në media', 'INPO në media', 4, array(), '/në media/iu', array( 'media' ) ),
	);
}

/**
 * Publication categories: slug => [ filter label, card label, order, hidden from the library ].
 * The live site's slugs: infographics are "publikime", monitoring reports "raporte".
 */
function inpo_core_type_definitions() {
	return array(
		'raporte'          => array( 'Raporte të monitorimit', 'Raport monitorimi', 0, false ),
		'hulumtime'        => array( 'Hulumtime dhe studime', 'Hulumtim', 1, false ),
		'analiza'          => array( 'Analiza të politikave publike', 'Analizë e politikave publike', 2, false ),
		'analiza-ligjore'  => array( 'Analiza ligjore', 'Analizë ligjore', 3, false ),
		'publikime'        => array( 'Infografika', 'Infografikë', 4, false ),
		'rekomandime'      => array( 'Rekomandime për politika publike', 'Rekomandim për politika publike', 5, false ),
		'buletine'         => array( 'Buletine', 'Buletin', 6, false ),
		'raporte-per-inpo' => array( 'Raporte për INPO', 'Raport për INPO', 7, true ),
		'mundesi-punesimi' => array( 'Mundësi punësimi', 'Mundësi punësimi', 8, true ),
	);
}

/**
 * New publication categories and the titles that belong to them.
 */
function inpo_core_type_rules() {
	return array(
		'analiza-ligjore' => '/ligj|jurisprudenc|kushtetu|gjykat/iu',
		'rekomandime'     => '/rekomandim|udhëzues|doracak|hapa për/iu',
	);
}

/**
 * Thematic fields: slug => [ name, title pattern ].
 */
function inpo_core_theme_definitions() {
	return array(
		'prokurimi-publik'                => array( 'Prokurimi publik', '/prokurim|tender|kontrat|autoritet(et|i) kontraktues/iu' ),
		'transparenca'                    => array( 'Transparenca dhe qasja në informata', '/transparenc|qasje|informim|informat/iu' ),
		'llogaridhenia'                   => array( 'Llogaridhënia', '/llogaridh|premtim|kënaqshm|performanc|vlerësu/iu' ),
		'integriteti-dhe-antikorrupsioni' => array( 'Integriteti dhe antikorrupsioni', '/korrup|integritet/iu' ),
		'sundimi-i-ligjit'                => array( 'Sundimi i ligjit', '/ligj|jurisprudenc|gjykat|kushtetu/iu' ),
		'qeverisja-lokale'                => array( 'Qeverisja lokale', '/komun|kuvend|këshill|lokal/iu' ),
		'edukimi-demokratik'              => array( 'Edukimi demokratik', '/arsim|rini|të rinj|aktiviz|kurrikul|debat|edukim/iu' ),
	);
}

/**
 * Institutions named in publication titles: slug => [ name, title pattern ].
 */
function inpo_core_institution_definitions() {
	return array(
		'oshp'       => array( 'Organi Shqyrtues i Prokurimit (OShP)', '/\bOSH?P\b|organi shqyrtues/iu' ),
		'krpp'       => array( 'Komisioni Rregullativ i Prokurimit Publik (KRPP)', '/\bKRPP\b/u' ),
		'aip'        => array( 'Agjencia për Informim dhe Privatësi (AIP)', '/agjencia për informim|\bAIP\b/iu' ),
		'qeveria'    => array( 'Qeveria e Kosovës', '/\bqeveri(a|së|se)\b/iu' ),
		'ministrite' => array( 'Ministritë', '/ministri/iu' ),
		'mkrs'       => array( 'Ministria e Kulturës, Rinisë dhe Sportit (MKRS)', '/\bMKRS\b/u' ),
		'fsk'        => array( 'Forca e Sigurisë së Kosovës (FSK)', '/\bFSK\b/u' ),
		'kuvendi'    => array( 'Kuvendet komunale', '/kuvend/iu' ),
		'komunat'    => array( 'Komunat', '/komun/iu' ),
		'zka'        => array( 'Zyra Kombëtare e Auditimit', '/auditor/iu' ),
	);
}

/**
 * Transparency document categories: slug => [ name, order ]. Slugs are the section anchors.
 */
function inpo_core_doc_definitions() {
	return array(
		'raportet-vjetore'                        => array( 'Raportet vjetore', 0 ),
		'raportet-financiare'                     => array( 'Raportet financiare', 1 ),
		'raportet-e-auditimit'                    => array( 'Raportet e auditimit', 2 ),
		'dokumentet-e-qeverisjes-organizative'    => array( 'Dokumentet e qeverisjes organizative', 3 ),
		'politikat-dhe-procedurat-e-brendshme'    => array( 'Politikat dhe procedurat e brendshme', 4 ),
		'strategjite-dhe-dokumentet-organizative' => array( 'Strategjitë dhe dokumentet organizative', 5 ),
	);
}

/**
 * Programme objectives, from the programme intros on inpo-ks.org.
 */
function inpo_core_program_objectives() {
	return array(
		'qeverisje' => array( 'Qeverisje e përgjegjshme në institucionet publike', 'Përfshirja e qytetarëve në vendimmarrje', 'Transparencë, llogaridhënie dhe sundim i ligjit' ),
		'edukim'    => array( 'Të rinj aktivë në jetën publike', 'Promovimi i një shoqërie të hapur', 'Edukim për demokracinë dhe shoqërinë civile' ),
	);
}

/**
 * Projects known from inpo-ks.org, created as drafts for the team to complete.
 * [ title, status, period, donor, partners, objectives, results, publication title pattern, publish at go-live ]
 */
function inpo_core_known_projects() {
	return array(
		array( 'Integriteti i kandidatëve, sundimi i ligjit dhe qeverisja e mirë', 'perfunduar', '', 'Rockefeller Brothers Fund', 'Instituti i Kosovës për Drejtësi (IKD), Lëvizja FOL', '', '', '/integriteti i kandidat/iu', true ),
		array( 'Anulimi i prokurimit: domosdoshmëri apo keqmenaxhim?', 'perfunduar', '2023', 'Byroja Ndërkombëtare për Çështje të Narkotikëve dhe Zbatimit të Ligjit (INL)', '', 'Analiza e tenderëve të anuluar në Kosovë', '', '/anulimi i prokurimit/iu', true ),
		array( 'Monitorimi i prokurimit në komunat e Kosovës', 'perfunduar', '2019', '', 'Instituti i Kosovës për Drejtësi (IKD)', 'Monitorimi i aktiviteteve të prokurimit në komunat e Kosovës', '160 | aktivitete prokurimi të monitoruara', '', true ),
		array( 'Grant nga programi Promovimi i Shoqërisë Demokratike (DSP)', '', '', 'Zyra Zvicerane për Bashkëpunim në Kosovë (SCO-K) dhe Ministria e Punëve të Jashtme e Danimarkës (DANIDA), menaxhuar nga KCSF', '', '', '', '', false ),
	);
}

/* ---------- Steps ---------- */

/**
 * Terms of a taxonomy from definitions (slug => [ name, ... ]); existing terms are kept as they are.
 *
 * @return int[] slug => term ID.
 */
function inpo_core_ensure_terms( $taxonomy, $defs, $order_index = null ) {
	$ids = array();
	foreach ( $defs as $slug => $def ) {
		$term = get_term_by( 'slug', $slug, $taxonomy );
		if ( ! $term ) {
			$new  = wp_insert_term( $def[0], $taxonomy, array( 'slug' => $slug ) );
			$term = is_wp_error( $new ) ? null : get_term( $new['term_id'], $taxonomy );
			if ( $term && null !== $order_index ) {
				update_term_meta( $term->term_id, 'inpo_order', $def[ $order_index ] );
			}
		}
		if ( $term ) {
			$ids[ $slug ] = (int) $term->term_id;
		}
	}
	return $ids;
}

/**
 * IDs of posts (any status, all languages) matching a query; WPML filters off.
 */
function inpo_core_all_ids( $args ) {
	return get_posts( array_merge( array( 'post_status' => array( 'publish', 'draft', 'pending', 'future', 'private' ), 'posts_per_page' => -1, 'fields' => 'ids', 'suppress_filters' => true ), $args ) );
}

/**
 * Publication categories: their labels on the existing types, the two new ones, hidden ones.
 * Labels are term meta, read by the new theme only, so the old site keeps its names.
 */
function inpo_core_setup_types( $create ) {
	$upgrade = (int) get_option( 'inpo_core_types_v' ) < 2;
	$done    = 0;
	foreach ( inpo_core_type_definitions() as $slug => $def ) {
		$term = get_term_by( 'slug', $slug, INPO_CORE_PUB_TYPE );
		$new  = ! $term && ( $create || array_key_exists( $slug, inpo_core_type_rules() ) );
		if ( $new ) {
			$res  = wp_insert_term( $def[0], INPO_CORE_PUB_TYPE, array( 'slug' => $slug ) );
			$term = is_wp_error( $res ) ? null : get_term( $res['term_id'], INPO_CORE_PUB_TYPE );
		}
		if ( ! $term ) {
			continue;
		}
		// Once per version: the brief's labels and order. Later edits in wp-admin are kept.
		if ( $upgrade || $new || '' === get_term_meta( $term->term_id, 'inpo_singular', true ) ) {
			update_term_meta( $term->term_id, 'inpo_plural', $def[0] );
			update_term_meta( $term->term_id, 'inpo_singular', $def[1] );
			update_term_meta( $term->term_id, 'inpo_order', $def[2] );
			update_term_meta( $term->term_id, 'inpo_hidden', $def[3] ? '1' : '' );
		}
		$done++;
	}
	update_option( 'inpo_core_types_v', 2 );
	/* translators: %d: number of types */
	return sprintf( __( 'Publication categories set up (labels for the new theme only): %d', 'inpo-core' ), $done );
}

/**
 * The five news sections, filled from the old categories, tags and titles (append only).
 *
 * @return string[] Log lines.
 */
function inpo_core_setup_news_sections() {
	$log    = array();
	$sorted = (bool) get_option( 'inpo_core_sorted_v2' );
	$new    = array();
	foreach ( inpo_core_news_sections() as $slug => $def ) {
		$term = get_term_by( 'slug', $slug, 'category' );
		if ( ! $term ) {
			$res  = wp_insert_term( $def[0], 'category', array( 'slug' => $slug ) );
			$term = is_wp_error( $res ) ? null : get_term( $res['term_id'], 'category' );
		}
		if ( ! $term ) {
			continue;
		}
		$new[] = (int) $term->term_id;
		update_term_meta( $term->term_id, 'inpo_chip', '1' );
		if ( '' === get_term_meta( $term->term_id, 'inpo_singular', true ) ) {
			update_term_meta( $term->term_id, 'inpo_singular', $def[1] );
		}
		if ( '' === get_term_meta( $term->term_id, 'inpo_order', true ) ) {
			update_term_meta( $term->term_id, 'inpo_order', $def[2] );
		}
		$ids = $def[3] ? inpo_core_posts_in_categories( $def[3] ) : array();
		if ( ! $sorted ) {
			if ( $def[5] ) {
				$ids = array_merge( $ids, inpo_core_all_ids( array( 'post_type' => 'post', 'tag_slug__in' => $def[5] ) ) );
			}
			if ( $def[4] ) {
				foreach ( inpo_core_all_ids( array( 'post_type' => 'post' ) ) as $id ) {
					if ( preg_match( $def[4], wp_strip_all_tags( get_the_title( $id ) ) ) ) {
						$ids[] = $id;
					}
				}
			}
		}
		$ids = array_unique( array_map( 'intval', $ids ) );
		foreach ( $ids as $id ) {
			wp_set_object_terms( $id, (int) $term->term_id, 'category', true );
		}
		/* translators: 1: section, 2: number of posts */
		$log[] = sprintf( __( 'News section "%1$s": %2$d posts added', 'inpo-core' ), $def[0], count( $ids ) );
	}
	// Only the five sections are offered as filters now (the earlier "Publikime" filter is retired).
	foreach ( inpo_core_chip_category_ids() as $id ) {
		if ( ! in_array( (int) $id, $new, true ) ) {
			delete_term_meta( $id, 'inpo_chip' );
		}
	}
	// Impact examples: an institution acted after INPO.
	if ( ! $sorted ) {
		$n = 0;
		foreach ( inpo_core_all_ids( array( 'post_type' => 'post' ) ) as $id ) {
			if ( preg_match( '/pas reagimit|me sukses|si rezultat i|pas kërkesës së INPO|pas ankesës së INPO/iu', wp_strip_all_tags( get_the_title( $id ) ) ) ) {
				update_post_meta( $id, 'inpo_impact', true );
				$n++;
			}
		}
		/* translators: %d: number of posts */
		$log[] = sprintf( __( 'Impact examples marked: %d', 'inpo-core' ), $n );
	}
	return $log;
}

/**
 * Publications: new categories, thematic fields, institutions and programmes, from their titles.
 * Runs once; afterwards editors correct and add by hand.
 *
 * @return string[] Log lines.
 */
function inpo_core_sort_publications() {
	$themes = inpo_core_ensure_terms( INPO_CORE_THEME, inpo_core_theme_definitions() );
	$insts  = inpo_core_ensure_terms( INPO_CORE_INST, inpo_core_institution_definitions() );
	if ( get_option( 'inpo_core_sorted_v2' ) ) {
		return array( __( 'Publications were already sorted by keyword; nothing changed (edit them by hand).', 'inpo-core' ) );
	}
	$count = array( 'types' => 0, 'themes' => 0, 'insts' => 0, 'programs' => 0 );
	foreach ( inpo_core_all_ids( array( 'post_type' => INPO_CORE_PUB ) ) as $id ) {
		$title = wp_strip_all_tags( get_the_title( $id ) );
		foreach ( inpo_core_type_rules() as $slug => $re ) {
			if ( preg_match( $re, $title ) ) {
				wp_set_object_terms( $id, $slug, INPO_CORE_PUB_TYPE, true );
				$count['types']++;
			}
		}
		foreach ( inpo_core_theme_definitions() as $slug => $def ) {
			if ( isset( $themes[ $slug ] ) && preg_match( $def[1], $title ) ) {
				wp_set_object_terms( $id, $themes[ $slug ], INPO_CORE_THEME, true );
				$count['themes']++;
			}
		}
		foreach ( inpo_core_institution_definitions() as $slug => $def ) {
			if ( isset( $insts[ $slug ] ) && preg_match( $def[1], $title ) ) {
				wp_set_object_terms( $id, $insts[ $slug ], INPO_CORE_INST, true );
				$count['insts']++;
			}
		}
		if ( ! wp_get_object_terms( $id, INPO_CORE_PROGRAM, array( 'fields' => 'ids' ) ) ) {
			$slug = inpo_core_guess_program( get_post( $id ) );
			if ( $slug && term_exists( $slug, INPO_CORE_PROGRAM ) ) {
				wp_set_object_terms( $id, $slug, INPO_CORE_PROGRAM );
				$count['programs']++;
			}
		}
	}
	update_option( 'inpo_core_sorted_v2', time() );
	return array(
		/* translators: %d: number */
		sprintf( __( 'Publications added to Analiza ligjore or Rekomandime by title: %d', 'inpo-core' ), $count['types'] ),
		/* translators: %d: number */
		sprintf( __( 'Thematic fields set by title: %d', 'inpo-core' ), $count['themes'] ),
		/* translators: %d: number */
		sprintf( __( 'Institutions set by title: %d', 'inpo-core' ), $count['insts'] ),
		/* translators: %d: number */
		sprintf( __( 'Publications linked to a programme: %d', 'inpo-core' ), $count['programs'] ),
	);
}

/**
 * Transparency documents: categories, and the reports about INPO copied from the publications
 * (the same PDF, not a copy of the file).
 */
function inpo_core_setup_documents() {
	$cats = inpo_core_ensure_terms( INPO_CORE_DOC_CAT, inpo_core_doc_definitions(), 1 );
	$pubs = inpo_core_all_ids( array( 'post_type' => INPO_CORE_PUB, 'tax_query' => array( array( 'taxonomy' => INPO_CORE_PUB_TYPE, 'field' => 'slug', 'terms' => 'raporte-per-inpo' ) ) ) );
	foreach ( inpo_core_all_ids( array( 'post_type' => INPO_CORE_PUB ) ) as $id ) {
		if ( preg_match( '/raporti i punës|raport(i)? vjetor/iu', wp_strip_all_tags( get_the_title( $id ) ) ) ) {
			$pubs[] = $id;
		}
	}
	$added = 0;
	foreach ( array_unique( $pubs ) as $pub ) {
		if ( get_posts( array( 'post_type' => INPO_CORE_DOC, 'post_status' => 'any', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_key' => 'inpo_source', 'meta_value' => $pub ) ) ) {
			continue;
		}
		$title = wp_strip_all_tags( get_the_title( $pub ) );
		$cat   = preg_match( '/auditim/iu', $title ) ? 'raportet-e-auditimit' : ( preg_match( '/financ/iu', $title ) ? 'raportet-financiare' : ( preg_match( '/punës|vjetor/iu', $title ) ? 'raportet-vjetore' : 'dokumentet-e-qeverisjes-organizative' ) );
		$year  = preg_match( '/\b(19|20)\d{2}\b/', $title, $m ) ? $m[0] : get_the_date( 'Y', $pub );
		$id    = wp_insert_post( wp_slash( array( 'post_type' => INPO_CORE_DOC, 'post_status' => 'publish', 'post_title' => $title, 'meta_input' => array( 'inpo_year' => $year, 'inpo_source' => $pub ) ) ) );
		if ( $id && ! is_wp_error( $id ) && isset( $cats[ $cat ] ) ) {
			wp_set_object_terms( $id, $cats[ $cat ], INPO_CORE_DOC_CAT );
			$added++;
		}
	}
	/* translators: %d: number of documents */
	return sprintf( __( 'Transparency documents copied from "Raporte për INPO": %d', 'inpo-core' ), $added );
}

/**
 * Programme objectives (when empty), and projects known from the site as drafts.
 *
 * @return string[] Log lines.
 */
function inpo_core_setup_objectives() {
	foreach ( inpo_core_program_objectives() as $slug => $goals ) {
		$term = get_term_by( 'slug', $slug, INPO_CORE_PROGRAM );
		if ( $term && ! inpo_core_lines( $term->term_id, 'inpo_objectives', true ) ) {
			update_term_meta( $term->term_id, 'inpo_objectives', implode( "\n", $goals ) );
		}
	}
}

function inpo_core_setup_brief_content() {
	$log = array();
	inpo_core_setup_objectives();
	$made = 0;
	if ( ! get_posts( array( 'post_type' => INPO_CORE_PROJECT, 'post_status' => 'any', 'posts_per_page' => 1, 'fields' => 'ids' ) ) ) {
		foreach ( inpo_core_known_projects() as $i => $p ) {
			$meta = array( 'inpo_status' => $p[1], 'inpo_period' => $p[2], 'inpo_donor_text' => $p[3], 'inpo_partners' => $p[4], 'inpo_goals' => $p[5], 'inpo_results' => $p[6], '_inpo_seeded' => $p[8] ? 'publish' : 'draft' );
			if ( $p[7] ) {
				foreach ( inpo_core_all_ids( array( 'post_type' => INPO_CORE_PUB ) ) as $pub ) {
					if ( preg_match( $p[7], wp_strip_all_tags( get_the_title( $pub ) ) ) ) {
						$meta['inpo_publication'] = $pub;
						break;
					}
				}
			}
			$id = wp_insert_post( wp_slash( array( 'post_type' => INPO_CORE_PROJECT, 'post_status' => 'draft', 'post_title' => $p[0], 'menu_order' => $i, 'meta_input' => array_filter( $meta, 'strlen' ) ) ) );
			if ( $id && ! is_wp_error( $id ) ) {
				wp_set_object_terms( $id, 'qeverisje', INPO_CORE_PROGRAM );
				$made++;
			}
		}
	}
	/* translators: %d: number of projects */
	$log[] = sprintf( __( 'Projects added as drafts (facts from inpo-ks.org; complete and publish them under Projects): %d', 'inpo-core' ), $made );
	/* translators: %d: number */
	$log[] = sprintf( __( 'Infographics with charts (numbers from INPO\'s infographics): %d', 'inpo-core' ), inpo_core_setup_infographics() );
	return $log;
}

/**
 * Go live: projects created from known facts are published (the one with a placeholder name stays a draft).
 */
function inpo_core_publish_seeded_projects() {
	$n = 0;
	foreach ( get_posts( array( 'post_type' => INPO_CORE_PROJECT, 'post_status' => 'draft', 'posts_per_page' => -1, 'meta_key' => '_inpo_seeded', 'meta_value' => 'publish' ) ) as $p ) {
		wp_update_post( array( 'ID' => $p->ID, 'post_status' => 'publish' ) );
		delete_post_meta( $p->ID, '_inpo_seeded' );
		$n++;
	}
	return $n;
}

/**
 * The Transparency page (documents by category and year), created at go-live when missing.
 *
 * @return int Page ID (0 when it could not be made).
 */
function inpo_core_transparency_page( &$created ) {
	$page = get_page_by_path( 'transparenca' );
	if ( $page ) {
		if ( 'publish' !== $page->post_status ) {
			wp_update_post( array( 'ID' => $page->ID, 'post_status' => 'publish' ) );
		}
		// Made by an earlier go-live: a later restore unpublishes it again.
		if ( get_post_meta( $page->ID, '_inpo_created', true ) ) {
			$created[] = $page->ID;
		}
		return $page->ID;
	}
	$id = wp_insert_post(
		wp_slash(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => 'Transparenca',
				'post_name'    => 'transparenca',
				'post_excerpt' => 'Raportet tona vjetore, financiare dhe të auditimit, si dhe dokumentet që rregullojnë punën e organizatës, sipas kategorisë dhe vitit.',
				'post_content' => inpo_core_pattern_content( 'inpo/page-transparency', '<!-- wp:inpo/documents /-->' ),
				'meta_input'   => array( 'inpo_kicker' => 'Transparenca', '_inpo_created' => 1 ),
			)
		)
	);
	if ( $id && ! is_wp_error( $id ) ) {
		$created[] = $id;
		return $id;
	}
	return 0;
}

/* ---------- Board ---------- */

/**
 * The board as listed on inpo-ks.org/rreth-nesh ("Anëtarët"): name, role.
 */
function inpo_core_known_board() {
	return array(
		array( 'Miranda Gagica', 'Kryesuese e Kuvendit të Anëtarëve' ),
		array( 'Albulena Nrecaj', 'Anëtare' ),
		array( 'Arbër Kadriu', 'Anëtar' ),
		array( 'Emine Behluli', 'Anëtare' ),
		array( 'Lirika Demiri', 'Anëtare' ),
		array( 'Valentina Abazi', 'Anëtare' ),
		array( 'Donika Lamaxhema', 'Anëtare' ),
		array( 'Kushtrim Palushi', 'Anëtar' ),
		array( 'Samir Reka', 'Anëtar' ),
	);
}

/**
 * Board members under INPO > Team (group "Board"). A person can be in the team and on the board.
 */
function inpo_core_setup_board() {
	$board = get_posts( array( 'post_type' => INPO_CORE_MEMBER, 'post_status' => 'any', 'posts_per_page' => -1, 'meta_key' => 'inpo_group', 'meta_value' => 'bordi' ) );
	$have  = array_map( function ( $p ) {
		return wp_strip_all_tags( $p->post_title );
	}, $board );
	$added = 0;
	foreach ( inpo_core_known_board() as $i => $m ) {
		if ( in_array( $m[0], $have, true ) ) {
			continue;
		}
		$id = wp_insert_post( wp_slash( array( 'post_type' => INPO_CORE_MEMBER, 'post_status' => 'publish', 'post_title' => $m[0], 'menu_order' => 100 + $i, 'meta_input' => array( 'inpo_role' => $m[1], 'inpo_group' => 'bordi' ) ) ) );
		$added += ( $id && ! is_wp_error( $id ) ) ? 1 : 0;
	}
	/* translators: %d: number of people */
	return sprintf( __( 'Board members added (from the About page): %d', 'inpo-core' ), $added );
}

/* ---------- Sample procurement data ---------- */

/**
 * Adds the sample datasets (made-up values, marked as illustrative on the site), e.g. for a demo on staging.
 *
 * @return string[] Log lines.
 */
function inpo_core_add_sample_data() {
	$data  = inpo_core_seed();
	$added = 0;
	foreach ( (array) $data['DATASETS'] as $i => $d ) {
		if ( get_page_by_path( $d['id'], OBJECT, INPO_CORE_DATASET ) ) {
			continue;
		}
		$csv = array( implode( ',', $d['cols'] ) );
		foreach ( $d['rows'] as $r ) {
			$csv[] = implode( ',', $r );
		}
		$id = wp_insert_post(
			wp_slash(
				array(
					'post_type'    => INPO_CORE_DATASET,
					'post_status'  => 'publish',
					'post_title'   => $d['title'],
					'post_name'    => $d['id'],
					'post_excerpt' => $d['lead'],
					'menu_order'   => 100 + $i,
					'meta_input'   => array( 'inpo_csv' => implode( "\n", $csv ), 'inpo_chart' => $d['chart'], 'inpo_unit' => $d['unit'], '_inpo_sample' => 1 ),
				)
			)
		);
		$added += ( $id && ! is_wp_error( $id ) ) ? 1 : 0;
	}
	/* translators: %d: number of datasets */
	return array( sprintf( __( 'Sample datasets added (illustrative values, labelled on the site): %d', 'inpo-core' ), $added ) );
}

/**
 * Deletes the sample datasets (only those added by the button or the demo install).
 *
 * @return string[] Log lines.
 */
function inpo_core_remove_sample_data() {
	$ids = get_posts( array( 'post_type' => INPO_CORE_DATASET, 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids', 'meta_key' => '_inpo_sample', 'meta_value' => '1' ) );
	foreach ( $ids as $id ) {
		wp_delete_post( $id, true );
	}
	/* translators: %d: number of datasets */
	return array( sprintf( __( 'Sample datasets removed: %d', 'inpo-core' ), count( $ids ) ) );
}


/* ---------- Upgrading a site set up with an earlier version ---------- */

/**
 * How this site was set up: live (went live with the INPO theme), demo (demo import),
 * prepared (Prepare ran, old theme still active) or '' (nothing yet).
 */
function inpo_core_site_mode() {
	if ( get_option( 'inpo_core_live' ) ) {
		return 'live';
	}
	if ( get_option( 'inpo_core_setup_done' ) ) {
		return 'demo';
	}
	return get_option( 'inpo_core_prepared' ) ? 'prepared' : '';
}

/**
 * Brings a site set up with 1.x to the structure of INPO's brief: sections, categories, fields,
 * institutions, documents, board, projects, the new home and About layouts (when not edited since),
 * the Transparency page and the menu (when it is still the one INPO made). Safe to run again.
 *
 * @return string[] Log lines.
 */
function inpo_core_upgrade_structure() {
	$mode = inpo_core_site_mode();
	if ( ! $mode ) {
		return array( __( 'Nothing to update yet: run the setup first.', 'inpo-core' ) );
	}
	$log = array();
	wp_defer_term_counting( true );
	$log[] = inpo_core_setup_types( 'demo' === $mode );
	inpo_core_setup_programs();
	inpo_core_setup_objectives();
	$log = array_merge( $log, inpo_core_setup_news_sections() );
	wp_defer_term_counting( false );
	$log   = array_merge( $log, inpo_core_sort_publications() );
	$log[] = inpo_core_setup_board();
	if ( 'demo' === $mode ) {
		$data    = inpo_core_seed();
		$pub_ids = array();
		foreach ( (array) $data['PUBS'] as $p ) {
			$found = get_page_by_path( $p['id'], OBJECT, INPO_CORE_PUB );
			if ( $found ) {
				$pub_ids[ $p['id'] ] = $found->ID;
			}
		}
		$log = array_merge( $log, inpo_core_demo_brief( $data, $pub_ids ) );
	} else {
		$log[] = inpo_core_setup_documents();
		$log   = array_merge( $log, inpo_core_setup_brief_content() );
		if ( 'live' === $mode ) {
			/* translators: %d: number of projects */
			$log[] = sprintf( __( 'Projects published: %d', 'inpo-core' ), inpo_core_publish_seeded_projects() );
		}
	}

	if ( 'prepared' !== $mode && inpo_core_theme_active() ) {
		$front = get_post( (int) get_option( 'page_on_front' ) );
		if ( $front && inpo_core_layout_outdated( $front, 'home' ) ) {
			inpo_core_adopt_page( $front, inpo_core_home_content(), array( 'template' => 'page-landing', 'replace' => true ) );
			$log[] = __( 'Home page: new layout (fields of action, results and impact, projects)', 'inpo-core' );
		}
		$about = get_page_by_path( 'rreth-nesh' );
		if ( $about && inpo_core_layout_outdated( $about, 'about' ) ) {
			inpo_core_adopt_page( $about, inpo_core_about_content(), array( 'replace' => true ) );
			$log[] = __( 'About page: new layout (history, mission, team and board, documents)', 'inpo-core' );
		}
		$created      = array();
		$transparency = inpo_core_transparency_page( $created );
		if ( $created && 'live' === $mode ) {
			$backup = get_option( 'inpo_core_live_backup' );
			if ( is_array( $backup ) ) {
				$backup['created'] = array_unique( array_merge( isset( $backup['created'] ) ? (array) $backup['created'] : array(), $created ) );
				update_option( 'inpo_core_live_backup', $backup, false );
			}
		}
		if ( $transparency ) {
			$log[] = __( 'Transparency page ready', 'inpo-core' );
		}
		$page_ids = inpo_core_menu_pages();
		if ( $page_ids['lajme'] && $page_ids['rreth-nesh'] && $page_ids['kontakti'] ) {
			$log[] = inpo_core_setup_menu( $page_ids );
		}
	}
	update_option( 'inpo_core_flush_rewrites', 1 );
	update_option( 'inpo_core_structure_v', 2 );
	return $log;
}

/**
 * After an update of the plugin: refresh the URL rules at once (new archives such as /projektet/),
 * and apply the new structure on the next wp-admin page an administrator opens.
 */
/* ---------- Infographic data and donor logos ---------- */

/**
 * The live site's slugs for the infographics that carry data in the seed (seed id => live slug).
 */
function inpo_core_infographic_slugs() {
	return array(
		'tm4'        => 'infografike-kontratat-e-negociuara-pa-publikim-te-njoftimit-per-kontrate-ne-nivel-vendi-tetor-dhjetor-2024-tm4',
		'projektet'  => 'performanca-e-komunave-ne-perfshirjen-e-projekteve-ekzekutive-ne-aktivitetet-e-prokurimit-per-pune-gjate-vitit-2023',
		'ministrite' => 'performanca-e-ministrive-ne-realizimin-e-planit-te-prokurimit-per-tenderet-per-pune-per-periudhen-janar-shtator-2024',
		'tm3'        => 'kontratat-sipas-procedures-se-negociuar-pa-publikim-te-njoftimit-per-kontrate-korrik-shtator-tm3-2024-ne-nivel-vendi',
	);
}

/**
 * Seed data of an infographic as publication meta: key figures, chart blocks (see inpo_core_csv_tables), source.
 */
function inpo_core_infographic_meta( $d ) {
	$blocks = array();
	foreach ( (array) $d['charts'] as $c ) {
		$lines = array( '# ' . $c['title'] . ' | ' . $c['chart'] . ' | ' . $c['unit'], implode( ',', $c['cols'] ) );
		foreach ( $c['rows'] as $r ) {
			$lines[] = implode( ',', array_map( 'strval', $r ) );
		}
		$blocks[] = implode( "\n", $lines );
	}
	$figures = array();
	foreach ( (array) ( isset( $d['figures'] ) ? $d['figures'] : array() ) as $f ) {
		$figures[] = $f[0] . ' | ' . $f[1];
	}
	return array(
		'inpo_csv'     => implode( "\n\n", $blocks ),
		'inpo_figures' => implode( "\n", $figures ),
		'inpo_source'  => isset( $d['source'] ) ? $d['source'] : '',
	);
}

/**
 * Adds the infographics' numbers to their publications (demo slug or live slug), unless an editor already did.
 *
 * @return int Publications updated.
 */
function inpo_core_setup_infographics() {
	$seed  = inpo_core_seed();
	$live  = inpo_core_infographic_slugs();
	$count = 0;
	foreach ( (array) $seed['PUBS'] as $p ) {
		if ( empty( $p['data'] ) ) {
			continue;
		}
		$post = get_page_by_path( $p['id'], OBJECT, INPO_CORE_PUB );
		if ( ! $post && isset( $live[ $p['id'] ] ) ) {
			$post = get_page_by_path( $live[ $p['id'] ], OBJECT, INPO_CORE_PUB );
		}
		if ( ! $post || '' !== trim( (string) get_post_meta( $post->ID, 'inpo_csv', true ) ) ) {
			continue;
		}
		foreach ( inpo_core_infographic_meta( $p['data'] ) as $key => $value ) {
			update_post_meta( $post->ID, $key, wp_slash( $value ) );
		}
		++$count;
	}
	return $count;
}

/**
 * Donor logos bundled with the plugin (seed/donors/), for sites without the old site's media library.
 */
function inpo_core_donor_logo_files() {
	return array(
		'Ambasada e SHBA-së'           => 'us-embassy.png',
		'INL · Departamenti i Shtetit' => 'inl.png',
		'Rockefeller Brothers Fund'    => 'rbf.png',
		'KCSF'                         => 'kcsf.png',
		'USAID'                        => 'usaid.png',
		'ATRC'                         => 'atrc.png',
		'YIHR'                         => 'yihr.png',
		'Balkan Trust for Democracy'   => 'btd.png',
	);
}

/**
 * Gives donors without a logo the bundled one (copied into the media library once).
 *
 * @return int Logos added.
 */
function inpo_core_attach_donor_logos() {
	return inpo_core_attach_bundled_images( INPO_CORE_DONOR, inpo_core_donor_logo_files(), 'seed/donors/', 'inpo-donor-' );
}

/**
 * Team photos bundled with the plugin (seed/team/), from inpo-ks.org.
 */
function inpo_core_team_photo_files() {
	return array(
		'Albulena Nrecaj'      => 'albulena-nrecaj.jpg',
		'Merita Bunjaku Sylaj' => 'merita-bunjaku-sylaj.jpg',
		'Elza Tullari Celina'  => 'elza-tullari-celina.jpg',
		'Arbër Kabashi'        => 'arber-kabashi.jpg',
	);
}

/**
 * Gives team members (not the board) without a photo the bundled one.
 *
 * @return int Photos added.
 */
function inpo_core_attach_team_photos() {
	return inpo_core_attach_bundled_images( INPO_CORE_MEMBER, inpo_core_team_photo_files(), 'seed/team/', 'inpo-team-', function ( $post ) {
		return 'bordi' !== get_post_meta( $post->ID, 'inpo_group', true );
	} );
}

/**
 * Sets a bundled image (copied into the media library once) as the featured image of posts that have none,
 * matched by title.
 *
 * @param string        $type   Post type.
 * @param array         $files  Title => file name.
 * @param string        $dir    Folder inside the plugin.
 * @param string        $prefix File name prefix in the media library.
 * @param callable|null $want   Optional extra check per post.
 * @return int Images added.
 */
function inpo_core_attach_bundled_images( $type, $files, $dir, $prefix, $want = null ) {
	$count = 0;
	foreach ( get_posts( array( 'post_type' => $type, 'post_status' => 'any', 'posts_per_page' => -1 ) ) as $p ) {
		$name = wp_strip_all_tags( get_the_title( $p ) );
		if ( has_post_thumbnail( $p ) || ! isset( $files[ $name ] ) || ( $want && ! $want( $p ) ) ) {
			continue;
		}
		$src = INPO_CORE_DIR . $dir . $files[ $name ];
		if ( ! file_exists( $src ) ) {
			continue;
		}
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$tmp = wp_tempnam( $files[ $name ] );
		if ( ! $tmp || ! copy( $src, $tmp ) ) {
			continue;
		}
		$id = media_handle_sideload( array( 'name' => $prefix . $files[ $name ], 'tmp_name' => $tmp ), $p->ID, $name );
		if ( is_wp_error( $id ) ) {
			wp_delete_file( $tmp );
			continue;
		}
		update_post_meta( $id, '_wp_attachment_image_alt', $name );
		set_post_thumbnail( $p->ID, $id );
		++$count;
	}
	return $count;
}

/**
 * Small content fixes for sites set up by an earlier version. Each one only touches
 * content that is still exactly as an earlier version created it.
 */
function inpo_core_tidy_content() {
	// 1.4.4: the DSP project no longer starts with a placeholder.
	$old = get_posts(
		array(
			'post_type'      => INPO_CORE_PROJECT,
			'post_status'    => 'any',
			'title'          => '[Emri i projektit], grant nga programi Promovimi i Shoqërisë Demokratike (DSP)',
			'posts_per_page' => 5,
		)
	);
	foreach ( $old as $p ) {
		wp_update_post( array( 'ID' => $p->ID, 'post_title' => 'Grant nga programi Promovimi i Shoqërisë Demokratike (DSP)' ) );
	}
	// 1.7.0: the lists (publications, projects, data, news) take their heading, intro and extra sections from a Page.
	if ( inpo_core_site_mode() ) {
		inpo_core_setup_listing_pages();
	}
	// 1.6.0: the home page gets "Kush jemi" with photos from the field, after the key findings.
	$front = get_post( (int) get_option( 'page_on_front' ) );
	if ( $front && inpo_core_site_mode() && false === strpos( $front->post_content, '<!-- wp:inpo/field' ) && preg_match( '#<!-- wp:inpo/bento[^>]*/-->#', $front->post_content, $m ) ) {
		$content = str_replace( $m[0], $m[0] . "\n\n<!-- wp:inpo/field /-->", $front->post_content );
		wp_update_post( wp_slash( array( 'ID' => $front->ID, 'post_content' => $content ) ) );
	}
	// 1.5.5: team photos from inpo-ks.org for members without one.
	if ( inpo_core_site_mode() ) {
		inpo_core_attach_team_photos();
		// The executive director first, as in the prototype.
		foreach ( array_keys( inpo_core_team_photo_files() ) as $order => $name ) {
			// A name can be on the team and the board: only the team entry moves.
			$m = get_posts(
				array(
					'post_type'      => INPO_CORE_MEMBER,
					'post_status'    => 'any',
					'title'          => $name,
					'posts_per_page' => 1,
					'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery
						'relation' => 'OR',
						array( 'key' => 'inpo_group', 'compare' => 'NOT EXISTS' ),
						array( 'key' => 'inpo_group', 'value' => 'bordi', 'compare' => '!=' ),
					),
				)
			);
			if ( $m && (int) $m[0]->menu_order !== $order ) {
				wp_update_post( array( 'ID' => $m[0]->ID, 'menu_order' => $order ) );
			}
		}
	}
	// 1.5.4: no email signup. The About page's jobs box pointed to it: it now points to the contact page.
	$about = get_page_by_path( 'rreth-nesh' );
	if ( $about && false !== strpos( $about->post_content, '#newsletter' ) ) {
		$content = str_replace(
			array( ' Abonohuni për të marrë njoftim kur hapet një pozitë e re.', 'href="#newsletter">Merr njoftime</a>' ),
			array( '', 'href="' . esc_url( inpo_core_contact_url() ) . '">Na kontaktoni</a>' ),
			$about->post_content
		);
		wp_update_post( wp_slash( array( 'ID' => $about->ID, 'post_content' => $content ) ) );
	}
	// 1.5.1: infographics carry their numbers (charts on the page); donors get their logos.
	if ( inpo_core_site_mode() ) {
		inpo_core_setup_infographics();
		inpo_core_attach_donor_logos();
	}
}

add_action(
	'init',
	function () {
		if ( get_option( 'inpo_core_version' ) === INPO_CORE_VERSION ) {
			return;
		}
		update_option( 'inpo_core_version', INPO_CORE_VERSION );
		flush_rewrite_rules();
		inpo_core_tidy_content();
		// Not on the brief's structure yet (or the menu was left behind): update on the next wp-admin page.
		if ( inpo_core_site_mode() && ( (int) get_option( 'inpo_core_structure_v' ) < 2 || ( inpo_core_theme_active() && ! inpo_core_menu_is_current() ) ) ) {
			update_option( 'inpo_core_needs_upgrade', 1 );
		}
	},
	99
);
add_action(
	'admin_init',
	function () {
		if ( get_option( 'inpo_core_needs_upgrade' ) && current_user_can( 'manage_options' ) ) {
			delete_option( 'inpo_core_needs_upgrade' );
			set_transient( 'inpo_core_notice', array( 'upgrade', inpo_core_upgrade_structure() ), HOUR_IN_SECONDS );
		}
	}
);
