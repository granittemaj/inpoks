<?php
/**
 * Setup for the live site, in two phases, plus a demo install for empty sites.
 *
 * 1. Prepare  (INPO > Setup, or wp inpo prepare)
 *    Safe while the old theme is still active: visitors see no change. Adds what the new design
 *    needs: publication type labels, programmes with activities, clean news filters, programme
 *    links on news, team and donors (copied from the old "Anëtarët" / "Donatorët" entries).
 *    Editors can then review and edit all of it in wp-admin before launch.
 *
 * 2. Go live  (automatic when the INPO theme is activated, or wp inpo golive)
 *    Gives Ballina, Rreth nesh and Kontakti their new content, makes "Lajme" the news page,
 *    creates the main menu and redirects old pages. Everything it changes is backed up first;
 *    switching back to another theme restores it (wp inpo restore).
 *
 * Permalinks are never touched on an existing site.
 *
 * Demo install  (wp inpo setup): imports the prototype content into an empty site.
 */

defined( 'ABSPATH' ) || exit;

if ( is_admin() ) {
	add_action( 'admin_menu', function () {
		add_submenu_page( 'inpo-core', __( 'INPO setup', 'inpo-core' ), __( 'Setup', 'inpo-core' ), 'manage_options', 'inpo-setup', 'inpo_core_setup_page' );
	}, 20 );
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	foreach ( array(
		'prepare' => 'inpo_core_prepare_site',
		'golive'  => 'inpo_core_go_live',
		'restore' => 'inpo_core_restore',
		'covers'  => 'inpo_core_make_previews_all',
		'samples' => 'inpo_core_add_sample_data',
		'upgrade' => 'inpo_core_upgrade_structure',
		'setup'   => 'inpo_core_run_setup',
	) as $command => $callback ) {
		WP_CLI::add_command(
			'inpo ' . $command,
			function () use ( $callback ) {
				foreach ( call_user_func( $callback ) as $line ) {
					WP_CLI::log( $line );
				}
				WP_CLI::success( 'Done.' );
			}
		);
	}
}

/**
 * Does the site already have real content (the live inpo-ks.org)?
 */
function inpo_core_site_has_content() {
	$posts = wp_count_posts( 'post' );
	$pubs  = wp_count_posts( INPO_CORE_PUB );
	return ( isset( $posts->publish ) && $posts->publish > 20 ) || ( isset( $pubs->publish ) && $pubs->publish > 0 );
}

/* ---------- Automatic go-live and restore on theme switch ---------- */

add_action(
	'switch_theme',
	function ( $new_name, $new_theme ) {
		if ( 'inpo' === $new_theme->get_template() ) {
			// Run on the next admin request, once the INPO theme's patterns are registered.
			if ( inpo_core_site_has_content() && ! get_option( 'inpo_core_live' ) ) {
				update_option( 'inpo_core_pending_go_live', 1 );
			}
		} elseif ( get_option( 'inpo_core_live' ) ) {
			set_transient( 'inpo_core_notice', array( 'restore', inpo_core_restore() ), HOUR_IN_SECONDS );
		}
	},
	10,
	2
);

add_action(
	'admin_init',
	function () {
		if ( get_option( 'inpo_core_pending_go_live' ) && inpo_core_theme_active() && current_user_can( 'manage_options' ) ) {
			delete_option( 'inpo_core_pending_go_live' );
			set_transient( 'inpo_core_notice', array( 'live', inpo_core_go_live() ), HOUR_IN_SECONDS );
		}
	}
);

add_action(
	'admin_notices',
	function () {
		$notice = get_transient( 'inpo_core_notice' );
		if ( ! $notice || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		delete_transient( 'inpo_core_notice' );
		$titles = array(
			'live'    => __( 'INPO is live. The new theme now shows your content:', 'inpo-core' ),
			'upgrade' => __( 'INPO updated: the site now has the structure from INPO\'s brief.', 'inpo-core' ),
			'restore' => __( 'INPO theme deactivated. The previous page content and settings were restored:', 'inpo-core' ),
		);
		$title  = isset( $titles[ $notice[0] ] ) ? $titles[ $notice[0] ] : $titles['restore'];
		echo '<div class="notice notice-success is-dismissible"><p><strong>' . esc_html( $title ) . '</strong><br>' . implode( '<br>', array_map( 'esc_html', (array) $notice[1] ) ) . '</p></div>';
	}
);

/* ---------- Admin page ---------- */

function inpo_core_setup_page() {
	$log = array();
	if ( current_user_can( 'manage_options' ) && isset( $_POST['inpo_action'] ) && check_admin_referer( 'inpo_setup' ) ) {
		$action = sanitize_key( wp_unslash( $_POST['inpo_action'] ) );
		if ( 'prepare' === $action ) {
			$log = inpo_core_prepare_site();
		} elseif ( 'golive' === $action && inpo_core_theme_active() ) {
			$log = inpo_core_go_live();
		} elseif ( 'restore' === $action ) {
			$log = inpo_core_restore();
		} elseif ( 'covers' === $action ) {
			$log = inpo_core_make_previews( 10 );
		} elseif ( 'upgrade' === $action ) {
			$log = inpo_core_upgrade_structure();
		} elseif ( 'menu' === $action ) {
			$log = array( inpo_core_setup_menu( inpo_core_menu_pages(), true ) );
		} elseif ( 'samples' === $action ) {
			$log = inpo_core_add_sample_data();
		} elseif ( 'samples-off' === $action ) {
			$log = inpo_core_remove_sample_data();
		} elseif ( 'demo' === $action && ! inpo_core_site_has_content() ) {
			$log = inpo_core_run_setup();
		}
	}
	$existing = inpo_core_site_has_content();
	$prepared = get_option( 'inpo_core_prepared' );
	$live     = get_option( 'inpo_core_live' );
	$theme    = inpo_core_theme_active();
	$date     = function ( $t ) {
		return wp_date( get_option( 'date_format' ) . ' H:i', (int) $t );
	};
	$button   = function ( $action, $label, $primary = false ) {
		echo '<button type="submit" name="inpo_action" value="' . esc_attr( $action ) . '" class="button' . ( $primary ? ' button-primary' : '' ) . '">' . esc_html( $label ) . '</button> ';
	};

	echo '<div class="wrap"><h1>' . esc_html__( 'INPO setup', 'inpo-core' ) . '</h1>';
	if ( $log ) {
		echo '<div class="notice notice-success"><p>' . implode( '<br>', array_map( 'esc_html', $log ) ) . '</p></div>';
	}
	echo '<form method="post">';
	wp_nonce_field( 'inpo_setup' );

	if ( $existing ) {
		echo '<div class="card" style="max-width:820px"><h2>' . esc_html__( 'Step 1. Prepare the data (now, with the current theme)', 'inpo-core' ) . '</h2>';
		echo '<p>' . esc_html__( 'Safe on the live site: visitors see no change until the INPO theme is activated. News, publications, PDFs and media stay exactly as they are. This adds:', 'inpo-core' ) . '</p><ul style="list-style:disc;padding-left:20px">'
			. '<li>' . esc_html__( 'the publication categories from INPO\'s brief (Raporte të monitorimit, Hulumtime dhe studime, Analiza të politikave publike, Analiza ligjore, Infografika, Rekomandime për politika publike) as labels on the existing types, plus the two new ones;', 'inpo-core' ) . '</li>'
			. '<li>' . esc_html__( 'thematic fields and institutions on publications, read from their titles, for the library filters;', 'inpo-core' ) . '</li>'
			. '<li>' . esc_html__( 'the five news sections (Aktivitete, Komunikata për media, Reagime dhe qëndrime publike, Opinione dhe artikuj, INPO në media), filled from the old categories, tags and titles;', 'inpo-core' ) . '</li>'
			. '<li>' . esc_html__( 'the programmes with their objectives and main activities, and links from news and publications to them;', 'inpo-core' ) . '</li>'
			. '<li>' . esc_html__( 'Transparency documents: the audit reports and the work report, copied from "Raporte për INPO" (same PDFs);', 'inpo-core' ) . '</li>'
			. '<li>' . esc_html__( 'projects known from the site, as drafts; Team and Donors, copied from the old "Anëtarët" and "Donatorët" entries.', 'inpo-core' ) . '</li></ul>'
			. '<p>' . esc_html__( 'Keyword sorting runs once and only adds labels; old categories and names stay as they are. Review everything afterwards: Posts > Categories, Publications > Types, Thematic fields and Institutions, Projects, Documents, INPO > Team.', 'inpo-core' ) . '</p>';
		if ( $prepared ) {
			/* translators: %s: date */
			echo '<p><strong>' . esc_html( sprintf( __( 'Prepared on %s. Safe to run again: existing items are kept.', 'inpo-core' ), $date( $prepared ) ) ) . '</strong></p>';
		}
		$button( 'prepare', $prepared ? __( 'Run again', 'inpo-core' ) : __( 'Prepare the data', 'inpo-core' ), ! $prepared );
		echo '</div>';

		echo '<div class="card" style="max-width:820px"><h2>' . esc_html__( 'Step 2. Go live (automatic)', 'inpo-core' ) . '</h2>';
		echo '<p>' . esc_html__( 'Happens by itself when you activate the INPO theme under Appearance > Themes: Ballina, Rreth nesh and Kontakti get their new content, the Transparenca page is created, Lajme becomes the news page, projects from facts are published, the main menu is built with the eight sections of INPO\'s brief and old programme pages redirect to the new ones. Permalinks are not changed.', 'inpo-core' ) . '</p>';
		echo '<p>' . esc_html__( 'Everything it changes is backed up first. Switching back to the old theme restores the old page content and settings automatically.', 'inpo-core' ) . '</p>';
		if ( $live ) {
			/* translators: %s: date */
			echo '<p><strong>' . esc_html( sprintf( __( 'Live since %s.', 'inpo-core' ), $date( $live ) ) ) . '</strong></p>';
			$button( 'restore', __( 'Restore the old pages and settings', 'inpo-core' ) );
		} elseif ( $theme ) {
			$button( 'golive', __( 'Go live now', 'inpo-core' ), true );
		} else {
			echo '<p><em>' . esc_html__( 'Waiting for the INPO theme to be activated.', 'inpo-core' ) . '</em></p>';
		}
		echo '</div>';
	} else {
		echo '<div class="card" style="max-width:820px"><h2>' . esc_html__( 'Demo install (empty site)', 'inpo-core' ) . '</h2>';
		echo '<p>' . esc_html__( 'For an empty site, e.g. staging: imports the prototype content, creates the pages and menu, and sets permalinks to /lajme/<slug>/.', 'inpo-core' ) . '</p>';
		$button( 'demo', __( 'Import demo content', 'inpo-core' ), true );
		echo '</div>';
	}
	// Update to the brief's structure (sites set up with 1.x).
	if ( inpo_core_site_mode() ) {
		echo '<div class="card" style="max-width:820px"><h2>' . esc_html__( 'Structure from INPO\'s brief', 'inpo-core' ) . '</h2>';
		echo '<p>' . esc_html__( 'Runs by itself after an update. It adds the news sections, publication categories, thematic fields, institutions, Transparency documents, board and projects, gives Ballina and Rreth nesh their new layout (only when they were not edited since), creates the Transparency page and rebuilds the menu (only when it is still the one INPO made). Safe to run again.', 'inpo-core' ) . '</p>';
		$button( 'upgrade', __( 'Apply the new structure', 'inpo-core' ) );
		$button( 'menu', __( 'Rebuild the main menu', 'inpo-core' ) );
		echo '<p class="description">' . esc_html__( '"Rebuild the main menu" makes a fresh menu with the eight sections and keeps the current one under Appearance > Menus.', 'inpo-core' ) . '</p>';
		echo '</div>';
	}

	// Sample procurement data, for demos on staging.
	$samples = get_posts( array( 'post_type' => INPO_CORE_DATASET, 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids', 'meta_key' => '_inpo_sample', 'meta_value' => '1' ) );
	echo '<div class="card" style="max-width:820px"><h2>' . esc_html__( 'Sample procurement data', 'inpo-core' ) . '</h2>';
	echo '<p>' . esc_html__( 'Six datasets with made-up values (contract values, negotiated contracts, cancelled tenders, complaints, bids per tender, procedures) to show the interactive charts under Data. Each is labelled "Të dhëna ilustruese" on the site. Remove them before real statistics go public.', 'inpo-core' ) . '</p>';
	if ( $samples ) {
		/* translators: %d: number of datasets */
		echo '<p><strong>' . esc_html( sprintf( __( 'Sample datasets on this site: %d', 'inpo-core' ), count( $samples ) ) ) . '</strong></p>';
		$button( 'samples-off', __( 'Remove the sample datasets', 'inpo-core' ) );
	} else {
		$button( 'samples', __( 'Add sample datasets', 'inpo-core' ) );
	}
	echo '</div>';

	// PDF covers.
	$missing = inpo_core_pdfs_without_preview();
	echo '<div class="card" style="max-width:820px"><h2>' . esc_html__( 'PDF covers', 'inpo-core' ) . '</h2>';
	echo '<p>' . esc_html__( 'Publication cards show the real first page when WordPress has a preview image of the PDF. Without one they use the designed INPO cover, and a publication page still draws the first page in the browser.', 'inpo-core' ) . '</p>';
	if ( ! wp_image_editor_supports( array( 'mime_type' => 'application/pdf' ) ) ) {
		echo '<p><em>' . esc_html__( 'This server cannot render PDFs (it needs Imagick with Ghostscript), so previews cannot be made here. Ask the host to enable it, or keep the designed covers.', 'inpo-core' ) . '</em></p>';
	} elseif ( $missing ) {
		/* translators: %d: number of PDFs */
		echo '<p>' . esc_html( sprintf( __( '%d publication PDFs have no preview yet. Each click processes 10.', 'inpo-core' ), count( $missing ) ) ) . '</p>';
		$button( 'covers', __( 'Make PDF covers', 'inpo-core' ), true );
	} else {
		echo '<p><strong>' . esc_html__( 'Every publication PDF has a preview.', 'inpo-core' ) . '</strong></p>';
	}
	echo '</div>';
	echo '</form></div>';
}

/**
 * IDs of PDFs attached to publications that have no preview image yet.
 *
 * @return int[]
 */
function inpo_core_pdfs_without_preview() {
	$ids = get_posts(
		array(
			'post_type'       => 'attachment',
			'post_mime_type'  => 'application/pdf',
			'post_status'     => 'inherit',
			'posts_per_page'  => -1,
			'fields'          => 'ids',
			'post_parent__in' => get_posts( array( 'post_type' => INPO_CORE_PUB, 'posts_per_page' => -1, 'fields' => 'ids', 'post_status' => 'any' ) ) ?: array( 0 ),
		)
	);
	return array_values(
		array_filter(
			$ids,
			function ( $id ) {
				$meta = wp_get_attachment_metadata( $id );
				return empty( $meta['sizes'] );
			}
		)
	);
}

/**
 * Creates first-page previews for up to $limit publication PDFs (WordPress's own PDF thumbnails).
 *
 * @return string[] Log lines.
 */
function inpo_core_make_previews( $limit = 10 ) {
	if ( ! wp_image_editor_supports( array( 'mime_type' => 'application/pdf' ) ) ) {
		return array( __( 'This server cannot render PDFs.', 'inpo-core' ) );
	}
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$done = 0;
	$fail = 0;
	foreach ( array_slice( inpo_core_pdfs_without_preview(), 0, $limit ) as $id ) {
		$file = get_attached_file( $id );
		$meta = $file && file_exists( $file ) ? wp_generate_attachment_metadata( $id, $file ) : array();
		if ( ! empty( $meta['sizes'] ) ) {
			wp_update_attachment_metadata( $id, $meta );
			$done++;
		} else {
			$fail++;
		}
	}
	/* translators: 1: made, 2: failed, 3: left */
	return array( sprintf( __( 'PDF covers made: %1$d, failed: %2$d, still to do: %3$d', 'inpo-core' ), $done, $fail, count( inpo_core_pdfs_without_preview() ) ) );
}

function inpo_core_make_previews_all() {
	$log = array();
	do {
		$before = count( inpo_core_pdfs_without_preview() );
		$log    = inpo_core_make_previews( 10 );
		$after  = count( inpo_core_pdfs_without_preview() );
	} while ( $after && $after < $before );
	return $log;
}

/* ---------- Shared data (facts from inpo-ks.org) ---------- */

/**
 * Main activities per programme, from the old programme pages.
 */
function inpo_core_program_activities() {
	return array(
		'qeverisje' => array(
			'Monitorimi i vazhdueshëm i funksionalitetit të Kuvendit Komunal, Komisionive Komunale, Drejtorive Komunale dhe Zyreve të Kryetarit të Komunës',
			'Takime të vazhdueshme me qytetarë dhe avokim i kërkesave të qytetarëve kundrejt institucioneve lokale ekzekutive dhe legjislative',
			'Hartimi i hulumtimeve, analizave lidhur me transparencës, llogaridhënien dhe sundimin e ligjit në Komunë',
			'Performancat rrugore dhe fushatat sensibilizuese',
		),
		'edukim'    => array(
			'Themelimi i klubeve të debatit dhe organizimi i garave të formateve të strukturuara të debatit',
			'Organizimi i trajnimeve mbi fushën e edukimit demokratik dhe funksionalitetit të shoqërisë civile',
			'Rritja e përfshirjes së të rinjve në zhvillimin e arsimit profesional',
			'Krijimi i hapësirës për të rinj për të zhvilluar ide/aktivitete në kuadër të promovimit të një shoqërie të hapur',
		),
	);
}

/**
 * Donors shown on the old home page: name, link, logo path inside uploads.
 * Used when the old "Donatorët" entries cannot be read. One logo has no name on the old site,
 * so it keeps a placeholder for the team to fill in.
 */
function inpo_core_known_donors() {
	return array(
		array( 'Ambasada e SHBA-së', 'https://xk.usembassy.gov/', '2023/03/Untitled-design-5.png' ),
		array( 'INL · Departamenti i Shtetit', 'https://xk.usembassy.gov/', '2023/03/63cf19861985e723a908a062_dos_inl_seal.png' ),
		array( 'Rockefeller Brothers Fund', 'http://www.rbf.org/programs/western-balkans', '2017/03/rockefeller-brothers-fund-logo-vector.png' ),
		array( 'KCSF', 'http://www.kcsfoundation.org/', '2017/03/Logo_KCSF.png' ),
		array( 'USAID', 'https://www.usaid.gov/kosovo', '2017/03/logo-2.png' ),
		array( 'ATRC', 'http://advocacy-center.org/', '2017/03/logo.jpg' ),
		array( 'YIHR', '', '2017/02/o-nama-yihr-logo-540x395-1.jpg' ),
		array( 'Balkan Trust for Democracy', '', '2017/02/donation-2.png' ),
		array( '[Emri i donatorit]', '', '2017/02/fb-logo.png' ),
		array( 'DANIDA', '', '' ),
		array( 'Zyra Zvicerane për Bashkëpunim', '', '' ),
	);
}

/**
 * Staff shown on the old About page: name, role, email, photo path inside uploads.
 * Used when the old "Anëtarët" entries cannot be read.
 */
function inpo_core_known_team() {
	return array(
		array( 'Albulena Nrecaj', 'Drejtore Ekzekutive', 'albulena.nrecaj@inpo-ks.org', '2017/07/81988036_3224758080874887_2546643430155485184_n.jpg' ),
		array( 'Merita Bunjaku Sylaj', 'Menaxhere e Financave dhe Administratës', 'merita@inpo-ks.org', '2024/10/962fd0e5-4558-4a2c-b9c5-2f9d42774990.jpg' ),
		array( 'Elza Tullari Celina', 'Monitoruese', 'elza@inpo-ks.org', '2024/10/Untitled-design-9.png' ),
		array( 'Arbër Kabashi', 'Monitorues', 'arber@inpo-ks.org', '2023/10/DSC_4328-1-scaled-e1696854058731.jpg' ),
	);
}

/**
 * Block markup for a page: the theme's pattern when registered, otherwise a plain fallback.
 */
function inpo_core_pattern_content( $name, $fallback ) {
	$registry = WP_Block_Patterns_Registry::get_instance();
	if ( $registry->is_registered( $name ) ) {
		$pattern = $registry->get_registered( $name );
		return $pattern['content'];
	}
	return $fallback;
}

function inpo_core_home_content() {
	return inpo_core_pattern_content( 'inpo/page-home', "<!-- wp:inpo/hero /-->\n\n<!-- wp:inpo/bento /-->\n\n<!-- wp:inpo/field /-->\n\n<!-- wp:inpo/publications /-->\n\n<!-- wp:inpo/impact /-->\n\n<!-- wp:inpo/programs /-->\n\n<!-- wp:inpo/news-list /-->\n\n<!-- wp:inpo/projects /-->" );
}

function inpo_core_about_content() {
	return inpo_core_pattern_content( 'inpo/page-about', "<!-- wp:inpo/team /-->\n\n<!-- wp:inpo/documents {\"layout\":\"list\",\"categories\":\"strategjite-dhe-dokumentet-organizative\"} /-->" );
}

/**
 * Page layouts made by an earlier version (1.x) and not edited since, which go-live may replace.
 * Markers: the 1.x home had the programmes but no figures; the 1.x About had the old transparency box.
 */
function inpo_core_layout_outdated( $page, $kind ) {
	$c = $page->post_content;
	if ( 'home' === $kind ) {
		return false !== strpos( $c, '<!-- wp:inpo/programs' ) && false === strpos( $c, '<!-- wp:inpo/impact' );
	}
	return false !== strpos( $c, '<!-- wp:inpo/transparency' ) && false === strpos( $c, 'id="historia"' );
}

/**
 * Programmes, with their description, "how we work" cards and main activities.
 *
 * @return int[] slug => term ID.
 */
function inpo_core_setup_programs() {
	$data = inpo_core_seed();
	$acts = inpo_core_program_activities();
	$ids  = array();
	$i    = 0;
	foreach ( $data['PROGS'] as $slug => $p ) {
		$term = get_term_by( 'slug', $slug, INPO_CORE_PROGRAM );
		if ( ! $term ) {
			$new = wp_insert_term( $p['name'], INPO_CORE_PROGRAM, array( 'slug' => $slug, 'description' => $p['desc'] ) );
			if ( is_wp_error( $new ) ) {
				continue;
			}
			$term = get_term( $new['term_id'], INPO_CORE_PROGRAM );
			update_term_meta( $term->term_id, 'inpo_full', $p['full'] );
			update_term_meta( $term->term_id, 'inpo_icon', $slug );
			update_term_meta( $term->term_id, 'inpo_ways', $p['ways'] );
			update_term_meta( $term->term_id, 'inpo_order', $i );
			update_term_meta( $term->term_id, 'inpo_show_news', 'qeverisje' === $slug );
			if ( ! empty( $p['goals'] ) ) {
				update_term_meta( $term->term_id, 'inpo_objectives', implode( "\n", $p['goals'] ) );
			}
		}
		if ( isset( $acts[ $slug ] ) && ! get_term_meta( $term->term_id, 'inpo_activities', true ) ) {
			update_term_meta( $term->term_id, 'inpo_activities', $acts[ $slug ] );
		}
		$ids[ $slug ] = (int) $term->term_id;
		$i++;
	}
	return $ids;
}

/**
 * Attachment ID for a file path inside uploads (e.g. 2017/03/logo.jpg), independent of the domain.
 * Also accepts a resized file name (logo-263x284.jpg) and finds the original.
 */
function inpo_core_attachment_by_path( $path ) {
	global $wpdb;
	if ( ! $path ) {
		return 0;
	}
	foreach ( array_unique( array( $path, preg_replace( '/-\d+x\d+(\.\w+)$/', '$1', $path ) ) ) as $candidate ) {
		$id = (int) $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value = %s LIMIT 1", $candidate ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		if ( $id ) {
			return $id;
		}
	}
	return 0;
}

/**
 * A post type registered by the old site (CPT UI), found by its label, e.g. "Donatorët".
 */
function inpo_core_legacy_post_type( $pattern ) {
	$ours = array( INPO_CORE_DONOR, INPO_CORE_MEMBER, INPO_CORE_PUB, INPO_CORE_MESSAGE, INPO_CORE_SUBSCRIBER );
	foreach ( get_post_types( array( '_builtin' => false ), 'objects' ) as $type ) {
		if ( ! in_array( $type->name, $ours, true ) && preg_match( $pattern, $type->name . ' ' . $type->label ) ) {
			return $type->name;
		}
	}
	return '';
}

/**
 * Text values stored on a post: its custom fields (ACF or plain) plus excerpt and content.
 *
 * @return string[]
 */
function inpo_core_post_strings( $post ) {
	$out = array();
	foreach ( get_post_meta( $post->ID ) as $key => $values ) {
		if ( '_' === $key[0] ) {
			continue;
		}
		foreach ( $values as $v ) {
			if ( is_string( $v ) && '' !== trim( $v ) && ! is_serialized( $v ) ) {
				$out[] = trim( wp_strip_all_tags( $v ) );
			}
		}
	}
	foreach ( array( $post->post_excerpt, $post->post_content ) as $v ) {
		$v = trim( wp_strip_all_tags( $v ) );
		if ( '' !== $v ) {
			$out[] = $v;
		}
	}
	return $out;
}

function inpo_core_find_or_create( $post_type, $title, $order ) {
	$found = get_posts( array( 'post_type' => $post_type, 'title' => $title, 'post_status' => 'any', 'posts_per_page' => 1 ) );
	if ( $found ) {
		return array( $found[0]->ID, false );
	}
	$id = wp_insert_post( wp_slash( array( 'post_type' => $post_type, 'post_status' => 'publish', 'post_title' => $title, 'menu_order' => $order ) ) );
	return array( ( $id && ! is_wp_error( $id ) ) ? $id : 0, true );
}

/**
 * Donors: from the old "Donatorët" entries when they exist, otherwise from the old home page.
 */
function inpo_core_setup_donors() {
	$legacy = inpo_core_legacy_post_type( '/donator|donor|sponsor/iu' );
	$rows   = array();
	if ( $legacy ) {
		foreach ( get_posts( array( 'post_type' => $legacy, 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => array( 'menu_order' => 'ASC', 'date' => 'ASC' ) ) ) as $p ) {
			$url = '';
			foreach ( inpo_core_post_strings( $p ) as $v ) {
				if ( preg_match( '#^https?://\S+$#', $v ) && false === strpos( $v, 'google.' ) ) {
					$url = $v;
					break;
				}
			}
			$rows[] = array( wp_strip_all_tags( get_the_title( $p ) ), $url, (int) get_post_thumbnail_id( $p ) );
		}
	}
	$source = $rows ? $legacy : 'home page';
	if ( ! $rows ) {
		foreach ( inpo_core_known_donors() as $d ) {
			$rows[] = array( $d[0], $d[1], inpo_core_attachment_by_path( $d[2] ) );
		}
	}
	$count = 0;
	foreach ( $rows as $i => $d ) {
		list( $id, $new ) = inpo_core_find_or_create( INPO_CORE_DONOR, $d[0], $i );
		if ( ! $id ) {
			continue;
		}
		$count += $new ? 1 : 0;
		if ( $d[1] && ! get_post_meta( $id, 'inpo_url', true ) ) {
			update_post_meta( $id, 'inpo_url', esc_url_raw( $d[1] ) );
		}
		if ( $d[2] && ! has_post_thumbnail( $id ) ) {
			set_post_thumbnail( $id, $d[2] );
		}
	}
	// Donors the old media library has no logo for get the bundled one.
	inpo_core_attach_donor_logos();
	/* translators: 1: number added, 2: source */
	return sprintf( __( 'Donors added: %1$d (from %2$s)', 'inpo-core' ), $count, $source );
}

/**
 * Team: from the old "Anëtarët" entries when they exist, otherwise from the old About page.
 */
function inpo_core_setup_team() {
	$legacy = inpo_core_legacy_post_type( '/an[eë]tar|staf|member|team|ekip/iu' );
	$rows   = array();
	if ( $legacy ) {
		foreach ( get_posts( array( 'post_type' => $legacy, 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => array( 'menu_order' => 'ASC', 'date' => 'ASC' ) ) ) as $p ) {
			$email = '';
			$role  = '';
			foreach ( inpo_core_post_strings( $p ) as $v ) {
				if ( ! $email && is_email( $v ) ) {
					$email = $v;
				} elseif ( ! $role && mb_strlen( $v ) <= 80 && ! preg_match( '#^(https?://|\d+$)#', $v ) && false === strpos( $v, '@' ) ) {
					$role = $v;
				}
			}
			$rows[] = array( wp_strip_all_tags( get_the_title( $p ) ), $role, $email, (int) get_post_thumbnail_id( $p ) );
		}
	}
	$source = $rows ? $legacy : 'About page';
	if ( ! $rows ) {
		foreach ( inpo_core_known_team() as $m ) {
			$rows[] = array( $m[0], $m[1], $m[2], inpo_core_attachment_by_path( $m[3] ) );
		}
	}
	$count = 0;
	foreach ( $rows as $i => $m ) {
		list( $id, $new ) = inpo_core_find_or_create( INPO_CORE_MEMBER, $m[0], $i );
		if ( ! $id ) {
			continue;
		}
		$count += $new ? 1 : 0;
		if ( $m[1] && ! get_post_meta( $id, 'inpo_role', true ) ) {
			update_post_meta( $id, 'inpo_role', sanitize_text_field( $m[1] ) );
		}
		if ( $m[2] && ! get_post_meta( $id, 'inpo_email', true ) ) {
			update_post_meta( $id, 'inpo_email', sanitize_email( $m[2] ) );
		}
		if ( $m[3] && ! has_post_thumbnail( $id ) ) {
			set_post_thumbnail( $id, $m[3] );
		}
	}
	// Members the old media library has no photo for get the bundled one.
	inpo_core_attach_team_photos();
	/* translators: 1: number added, 2: source */
	return sprintf( __( 'Team members added: %1$d (from %2$s)', 'inpo-core' ), $count, $source );
}

/* ---------- 1. Prepare (safe with the old theme) ---------- */

/**
 * @return string[] Log lines.
 */
function inpo_core_prepare_site() {
	$log = array();
	wp_defer_term_counting( true );

	$log[]    = inpo_core_setup_types( false );
	$programs = inpo_core_setup_programs();
	/* translators: %s: programme slugs */
	$log[] = sprintf( __( 'Programmes: %s', 'inpo-core' ), implode( ', ', array_keys( $programs ) ) );

	// INPO's brief: the five news sections (old categories stay; posts are added, never removed).
	$log = array_merge( $log, inpo_core_setup_news_sections() );

	// Programmes on news, from the old programme categories (append only).
	$by_program = array(
		'qeverisje' => array( 'aktivitete-qeverisje-te-mire', 'aktivitetet-qeverisja-e-mire', 'programi-per-qeverisje-te-mire-lokale' ),
		'edukim'    => array( 'aktivitete-edukimi-demokratik', 'programi-per-edukim-demokratik' ),
	);
	foreach ( $by_program as $slug => $cats ) {
		if ( empty( $programs[ $slug ] ) ) {
			continue;
		}
		$ids = inpo_core_posts_in_categories( $cats );
		foreach ( $ids as $id ) {
			wp_set_object_terms( $id, $programs[ $slug ], INPO_CORE_PROGRAM, true );
		}
		/* translators: 1: programme, 2: number of posts */
		$log[] = sprintf( __( 'Programme "%1$s": %2$d posts linked', 'inpo-core' ), $slug, count( $ids ) );
	}
	wp_defer_term_counting( false );

	// Cover graphics reused on many posts are not shown as article photos.
	global $wpdb;
	$generic = $wpdb->get_col( "SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_thumbnail_id' GROUP BY meta_value HAVING COUNT(*) >= 3" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	foreach ( $generic as $att ) {
		update_post_meta( (int) $att, 'inpo_generic', 1 );
	}
	/* translators: %d: number of images */
	$log[] = sprintf( __( 'Shared cover images (not shown as article photos): %d', 'inpo-core' ), count( $generic ) );

	$log[] = inpo_core_setup_donors();
	/* translators: %d: number of pages */
	$log[] = sprintf( __( 'List pages made under Pages (heading and intro of publications, projects, data): %d', 'inpo-core' ), inpo_core_setup_listing_pages() );
	$log[] = inpo_core_setup_team();
	$log[] = inpo_core_setup_board();

	// INPO's brief: publication categories, fields and institutions, transparency documents, projects.
	$log   = array_merge( $log, inpo_core_sort_publications() );
	$log[] = inpo_core_setup_documents();
	$log   = array_merge( $log, inpo_core_setup_brief_content() );

	update_option( 'inpo_core_prepared', time() );
	return $log;
}

/**
 * IDs of posts (any status, all languages) in any of the given category slugs.
 *
 * @param string[] $slugs Category slugs.
 * @return int[]
 */
function inpo_core_posts_in_categories( $slugs ) {
	$terms = array();
	foreach ( $slugs as $slug ) {
		$t = get_term_by( 'slug', $slug, 'category' );
		if ( $t ) {
			$terms[] = (int) $t->term_id;
		}
	}
	if ( ! $terms ) {
		return array();
	}
	return get_posts(
		array(
			'post_type'        => 'post',
			'post_status'      => array( 'publish', 'draft', 'pending', 'future', 'private' ),
			'posts_per_page'   => -1,
			'fields'           => 'ids',
			'category__in'     => $terms,
			'suppress_filters' => true,
		)
	);
}

/* ---------- 2. Go live (on INPO theme activation) and restore ---------- */

/**
 * @return string[] Log lines.
 */
function inpo_core_go_live() {
	$log = array();
	if ( ! get_option( 'inpo_core_prepared' ) ) {
		$log = inpo_core_prepare_site();
	}
	$backup = array(
		'options' => array(
			'show_on_front'  => get_option( 'show_on_front' ),
			'page_on_front'  => get_option( 'page_on_front' ),
			'page_for_posts' => get_option( 'page_for_posts' ),
		),
		'pages'   => array(),
		'created' => array(),
	);
	$keep = function ( $page ) use ( &$backup ) {
		$backup['pages'][ $page->ID ] = array(
			'post_content' => $page->post_content,
			'post_title'   => $page->post_title,
			'post_excerpt' => $page->post_excerpt,
			'template'     => get_post_meta( $page->ID, '_wp_page_template', true ),
			'kicker'       => get_post_meta( $page->ID, 'inpo_kicker', true ),
		);
	};
	$programs = array();
	foreach ( inpo_core_programs() as $t ) {
		$programs[ $t->slug ] = $t;
	}

	$page_ids = array();
	$front    = (int) get_option( 'page_on_front' );
	$front    = $front ? get_post( $front ) : get_page_by_path( 'ballina' );
	if ( $front ) {
		$keep( $front );
		inpo_core_adopt_page( $front, inpo_core_home_content(), array( 'template' => 'page-landing', 'replace' => inpo_core_layout_outdated( $front, 'home' ) ) );
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $front->ID );
		/* translators: %s: page title */
		$log[] = sprintf( __( 'Home page: %s', 'inpo-core' ), get_the_title( $front ) );
	}
	$news = get_page_by_path( 'lajme' );
	if ( $news ) {
		$keep( $news );
		update_option( 'page_for_posts', $news->ID );
		update_post_meta( $news->ID, '_wp_page_template', 'default' );
		$page_ids['lajme'] = $news->ID;
		$log[]             = __( 'News page: /lajme/', 'inpo-core' );
	}
	$about = get_page_by_path( 'rreth-nesh' );
	if ( $about ) {
		$keep( $about );
		inpo_core_adopt_page(
			$about,
			inpo_core_about_content(),
			array(
				'kicker'  => 'Rreth nesh',
				'excerpt' => 'Organizatë joqeveritare dhe jofitimprurëse, e themeluar në vitin 2005. Politikat publike janë fokusi i punës sonë.',
				'replace' => inpo_core_layout_outdated( $about, 'about' ),
			)
		);
		$page_ids['rreth-nesh'] = $about->ID;
		$log[]                  = __( 'About page: new layout with team and values', 'inpo-core' );
	}
	$contact = get_page_by_path( 'kontakti' );
	if ( $contact ) {
		$keep( $contact );
		inpo_core_adopt_page(
			$contact,
			'<!-- wp:inpo/contact /-->',
			array(
				'title'   => 'Le të flasim.',
				'kicker'  => 'Kontakti',
				'excerpt' => 'Keni informata për një tender, pyetje për raportet tona ose një ide për bashkëpunim? Na shkruani.',
			)
		);
		$page_ids['kontakti'] = $contact->ID;
		$log[]                = __( 'Contact page: new contact form', 'inpo-core' );
	}
	$transparency = inpo_core_transparency_page( $backup['created'] );
	if ( $transparency ) {
		$page_ids['transparenca'] = $transparency;
		$log[]                    = __( 'Transparency page: documents by category and year', 'inpo-core' );
	}
	/* translators: %d: number of projects */
	$log[] = sprintf( __( 'Projects published: %d', 'inpo-core' ), inpo_core_publish_seeded_projects() );
	update_option( 'inpo_core_live_backup', $backup, false );

	// Old pages whose role the new design covers: send visitors (and search engines) on.
	$redirects = array( 'pages' => array(), 'paths' => array() );
	$targets   = array(
		'programi-per-qeverisje-te-mire'  => isset( $programs['qeverisje'] ) ? get_term_link( $programs['qeverisje'] ) : '',
		'programi-per-edukim-demoktratik' => isset( $programs['edukim'] ) ? get_term_link( $programs['edukim'] ) : '',
		'video'                           => $news ? get_permalink( $news ) : '',
		'publikimet'                      => inpo_core_pubs_url(),
	);
	foreach ( $targets as $slug => $url ) {
		$page = get_page_by_path( $slug );
		if ( $page && $url && ! is_wp_error( $url ) ) {
			$redirects['pages'][ $page->ID ] = $url;
		}
	}
	// A child of the old "Publikimet" page now sits under the publication archive URL.
	$redirects['paths']['/publikimet/publikimet-ne-programin-e-qeverisjes-se-mire/'] = inpo_core_pubs_url();
	update_option( 'inpo_core_redirects', $redirects, false );
	/* translators: %d: number of redirects */
	$log[] = sprintf( __( 'Redirects from old pages: %d', 'inpo-core' ), count( $redirects['pages'] ) + count( $redirects['paths'] ) );

	if ( isset( $page_ids['lajme'], $page_ids['rreth-nesh'], $page_ids['kontakti'] ) ) {
		$log[] = inpo_core_setup_menu( $page_ids );
	}

	// New URLs (programmes, publication archive): rebuild rewrite rules on the next request.
	update_option( 'inpo_core_flush_rewrites', 1 );
	update_option( 'inpo_core_live', time() );
	inpo_core_setup_listing_pages();
	return $log;
}

/**
 * Puts back what go-live changed: page content, titles, templates and reading settings.
 *
 * @return string[] Log lines.
 */
function inpo_core_restore() {
	$backup = get_option( 'inpo_core_live_backup' );
	$log    = array();
	if ( is_array( $backup ) ) {
		foreach ( (array) $backup['options'] as $key => $value ) {
			update_option( $key, $value );
		}
		foreach ( (array) $backup['pages'] as $id => $p ) {
			wp_update_post(
				wp_slash(
					array(
						'ID'           => $id,
						'post_content' => $p['post_content'],
						'post_title'   => $p['post_title'],
						'post_excerpt' => $p['post_excerpt'],
					)
				)
			);
			update_post_meta( $id, '_wp_page_template', $p['template'] ? $p['template'] : 'default' );
			if ( '' === $p['kicker'] ) {
				delete_post_meta( $id, 'inpo_kicker' );
			}
		}
		foreach ( isset( $backup['created'] ) ? (array) $backup['created'] : array() as $id ) {
			wp_update_post( array( 'ID' => $id, 'post_status' => 'draft' ) );
		}
		/* translators: %d: number of pages */
		$log[] = sprintf( __( 'Pages restored: %d', 'inpo-core' ), count( (array) $backup['pages'] ) );
		$log[] = __( 'Reading settings restored (home page and news page).', 'inpo-core' );
	}
	delete_option( 'inpo_core_live_backup' );
	delete_option( 'inpo_core_redirects' );
	delete_option( 'inpo_core_live' );
	update_option( 'inpo_core_flush_rewrites', 1 );
	$log[] = __( 'Redirects removed. Team, donors, programmes and filters stay prepared for the next launch.', 'inpo-core' );
	return $log;
}

/**
 * Gives an existing page its new content, once. The old content stays in the revisions and in the backup.
 *
 * @param WP_Post $page    Page.
 * @param string  $content New block content.
 * @param array   $extra   template, title, kicker, excerpt, replace (an unedited earlier INPO layout).
 */
function inpo_core_adopt_page( $page, $content, $extra = array() ) {
	if ( false === strpos( $page->post_content, '<!-- wp:inpo/' ) || ! empty( $extra['replace'] ) ) {
		wp_save_post_revision( $page->ID );
		$update = array( 'ID' => $page->ID, 'post_content' => $content );
		if ( ! empty( $extra['title'] ) ) {
			$update['post_title'] = $extra['title'];
		}
		if ( ! empty( $extra['excerpt'] ) && ! has_excerpt( $page ) ) {
			$update['post_excerpt'] = $extra['excerpt'];
		}
		wp_update_post( wp_slash( $update ) );
	}
	update_post_meta( $page->ID, '_wp_page_template', isset( $extra['template'] ) ? $extra['template'] : 'default' );
	if ( ! empty( $extra['kicker'] ) && ! get_post_meta( $page->ID, 'inpo_kicker', true ) ) {
		update_post_meta( $page->ID, 'inpo_kicker', $extra['kicker'] );
	}
}

/* ---------- Demo install (empty site) ---------- */

/**
 * Reads the seed file.
 */
function inpo_core_seed() {
	$json = file_get_contents( INPO_CORE_DIR . 'seed/content.json' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	return json_decode( $json, true );
}

/**
 * @return string[] Log lines.
 */
function inpo_core_run_setup() {
	$data = inpo_core_seed();
	$log  = array();

	// News sections (INPO's brief). Prototype keys => category slugs.
	inpo_core_setup_news_sections();
	update_option( 'inpo_core_sorted_v2', time() ); // Demo content arrives sorted; no keyword sorting.
	$cat_ids = array();
	foreach ( array( 'aktivitete' => 'aktivitete', 'komunikata' => 'komunikata-per-media', 'reagime' => 'reagime-dhe-qendrime-publike', 'opinione' => 'opinione-dhe-artikuj', 'media' => 'inpo-ne-media' ) as $key => $slug ) {
		$term            = get_term_by( 'slug', $slug, 'category' );
		$cat_ids[ $key ] = $term ? (int) $term->term_id : (int) get_option( 'default_category' );
	}
	/* translators: %d: number */
	$log[] = sprintf( __( 'Categories: %d', 'inpo-core' ), count( $cat_ids ) );
	$themes = inpo_core_ensure_terms( INPO_CORE_THEME, inpo_core_theme_definitions() );
	$insts  = inpo_core_ensure_terms( INPO_CORE_INST, inpo_core_institution_definitions() );
	foreach ( $data['INSTS'] as $slug => $name ) {
		if ( ! isset( $insts[ $slug ] ) ) {
			$insts = $insts + inpo_core_ensure_terms( INPO_CORE_INST, array( $slug => array( $name ) ) );
		}
	}
	$theme_slug = array( 'prokurimi' => 'prokurimi-publik', 'transparenca' => 'transparenca', 'llogaridhenia' => 'llogaridhenia', 'integriteti' => 'integriteti-dhe-antikorrupsioni', 'ligji' => 'sundimi-i-ligjit', 'lokale' => 'qeverisja-lokale', 'edukimi' => 'edukimi-demokratik' );

	$log[] = inpo_core_setup_types( true );
	$programs = inpo_core_setup_programs();
	/* translators: %d: number */
	$log[]    = sprintf( __( 'Programmes: %d', 'inpo-core' ), count( $programs ) );

	// Publications, in prototype order. Types use the live site's slugs.
	$type_slug = array( 'monitorim' => 'raporte', 'hulumtime' => 'hulumtime', 'politika' => 'analiza', 'ligjore' => 'analiza-ligjore', 'infografika' => 'publikime', 'rekomandime' => 'rekomandime' );
	$pub_ids   = array();
	$variants  = array( 'rings', 'bars', 'dots', 'word' );
	$now       = time();
	foreach ( $data['PUBS'] as $i => $p ) {
		$existing = get_page_by_path( $p['id'], OBJECT, INPO_CORE_PUB );
		if ( $existing ) {
			$pub_ids[ $p['id'] ] = $existing->ID;
			continue;
		}
		$meta = array( 'inpo_short' => $p['short'], 'inpo_cover' => $variants[ $i % 4 ] );
		foreach ( array( 'year' => 'inpo_year', 'partners' => 'inpo_partners', 'donor' => 'inpo_donor' ) as $k => $m ) {
			if ( ! empty( $p[ $k ] ) ) {
				$meta[ $m ] = $p[ $k ];
			}
		}
		$id = wp_insert_post(
			wp_slash(
				array(
					'post_type'   => INPO_CORE_PUB,
					'post_status' => 'publish',
					'post_title'  => $p['title'],
					'post_name'   => $p['id'],
					'menu_order'  => $i,
					'post_date'   => ! empty( $p['date'] ) ? implode( '-', array_reverse( explode( '.', $p['date'] ) ) ) . ' 09:00:00' : wp_date( 'Y-m-d H:i:s', $now - $i * MINUTE_IN_SECONDS ),
					'meta_input'  => $meta,
				)
			)
		);
		if ( $id && ! is_wp_error( $id ) ) {
			$pub_ids[ $p['id'] ] = $id;
			wp_set_object_terms( $id, isset( $type_slug[ $p['type'] ] ) ? $type_slug[ $p['type'] ] : $p['type'], INPO_CORE_PUB_TYPE );
			if ( $p['prog'] ) {
				wp_set_object_terms( $id, $p['prog'], INPO_CORE_PROGRAM );
			}
			if ( ! empty( $p['theme'] ) && isset( $theme_slug[ $p['theme'] ], $themes[ $theme_slug[ $p['theme'] ] ] ) ) {
				wp_set_object_terms( $id, $themes[ $theme_slug[ $p['theme'] ] ], INPO_CORE_THEME );
			}
			if ( ! empty( $p['inst'] ) && isset( $insts[ $p['inst'] ] ) ) {
				wp_set_object_terms( $id, $insts[ $p['inst'] ], INPO_CORE_INST );
			}
		}
	}
	/* translators: %d: number */
	$log[] = sprintf( __( 'Publications: %d', 'inpo-core' ), count( $pub_ids ) );
	/* translators: %d: number */
	$log[] = sprintf( __( 'Infographics with charts: %d', 'inpo-core' ), inpo_core_setup_infographics() );

	// News, under the same slugs as on inpo-ks.org (the home page blocks refer to them).
	$real_slug = array(
		'pastrimi'            => 'ankesa-e-krm-pastrimi-perjashtohet-pas-shqyrtimit-inpo-kerkon-rishikim-te-vendimit-te-pakuptimte-te-oshp-se',
		'mkrs-procesverbal'   => 'pas-reagimit-te-inpo-se-ndaj-tenderit-56-milionesh-mkrs-se-publikon-procesverbalin-e-hapjes-se-tendereve-por-jo-edhe-vendimin-per-fitues-in-it-e-kontrates',
		'mkrs-125'            => 'tenderi-56-milionesh-per-stadiume-i-mkrs-se-126-dite-pa-vendim-pa-transparence-pa-pergjegjesi',
		'prishtina-mbeturina' => 'inpo-ngre-shqetesime-per-mungese-transparence-dhe-ligjshmerie-ne-prokurimin-per-menaxhimin-e-mbeturinave-nga-komuna-e-prishtines',
		'vushtrri'            => 'inpo-kerkon-anulimin-dhe-rishikimin-e-tenderit-4-98-milione-e-te-komunes-se-vushtrrise-per-shkak-te-kritereve-diskriminuese',
	);
	$count = 0;
	foreach ( $data['NEWS'] as $n ) {
		$slug = isset( $real_slug[ $n['id'] ] ) ? $real_slug[ $n['id'] ] : $n['id'];
		if ( get_page_by_path( $slug, OBJECT, 'post' ) ) {
			continue;
		}
		$content = '';
		foreach ( $n['body'] as $para ) {
			$content .= "<!-- wp:paragraph -->\n<p>" . esc_html( $para ) . "</p>\n<!-- /wp:paragraph -->\n\n";
		}
		$meta = array();
		if ( ! empty( $n['todo'] ) ) {
			$meta['inpo_todo'] = true;
		}
		if ( ! empty( $n['short'] ) ) {
			$meta['inpo_short_title'] = $n['short'];
		}
		// Dates the prototype shows as text (e.g. "Maj 2025") stay as text.
		if ( ! preg_match( '/^\d{2}\.\d{2}\.\d{4}$/', $n['date'] ) ) {
			$meta['inpo_date_label'] = $n['date'];
		}
		if ( ! empty( $n['pub'] ) && isset( $pub_ids[ $n['pub'] ] ) ) {
			$meta['inpo_publication'] = $pub_ids[ $n['pub'] ];
		}
		if ( in_array( $n['id'], (array) $data['CASES'], true ) ) {
			$meta['inpo_impact'] = true;
		}
		$id = wp_insert_post(
			wp_slash(
				array(
					'post_type'     => 'post',
					'post_status'   => 'publish',
					'post_title'    => $n['title'],
					'post_name'     => $slug,
					'post_excerpt'  => $n['excerpt'],
					'post_content'  => trim( $content ),
					'post_date'     => $n['iso'] . ' 09:00:00',
					'post_category' => array( $cat_ids[ $n['cat'] ] ),
					'meta_input'    => $meta,
				)
			)
		);
		if ( $id && ! is_wp_error( $id ) ) {
			$count++;
		}
	}
	/* translators: %d: number */
	$log[] = sprintf( __( 'News added: %d', 'inpo-core' ), $count );

	$log[] = inpo_core_setup_donors();
	/* translators: %d: number of pages */
	$log[] = sprintf( __( 'List pages made under Pages (heading and intro of publications, projects, data): %d', 'inpo-core' ), inpo_core_setup_listing_pages() );
	$log[] = inpo_core_setup_team();
	$log[] = inpo_core_setup_board();
	$log = array_merge( $log, inpo_core_demo_brief( $data, $pub_ids ) );

	// Pages.
	$pages = array(
		'kryefaqja'  => array(
			'title'    => 'Kryefaqja',
			'template' => 'page-landing',
			'content'  => inpo_core_home_content(),
		),
		'lajme'      => array( 'title' => 'Lajme', 'content' => '' ),
		'rreth-nesh' => array(
			'title'   => 'Rreth nesh',
			'kicker'  => 'Rreth nesh',
			'excerpt' => 'Organizatë joqeveritare dhe jofitimprurëse, e themeluar në vitin 2005. Politikat publike janë fokusi i punës sonë.',
			'content' => inpo_core_about_content(),
		),
		'kontakti'   => array(
			'title'   => 'Le të flasim.',
			'kicker'  => 'Kontakti',
			'excerpt' => 'Keni informata për një tender, pyetje për raportet tona ose një ide për bashkëpunim? Na shkruani.',
			'content' => '<!-- wp:inpo/contact /-->',
		),
	);
	$page_ids = array();
	foreach ( $pages as $slug => $p ) {
		$existing = get_page_by_path( $slug );
		if ( $existing ) {
			$page_ids[ $slug ] = $existing->ID;
			continue;
		}
		$args = array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => $p['title'],
			'post_name'    => $slug,
			'post_content' => $p['content'],
			'post_excerpt' => isset( $p['excerpt'] ) ? $p['excerpt'] : '',
			'meta_input'   => isset( $p['kicker'] ) ? array( 'inpo_kicker' => $p['kicker'] ) : array(),
		);
		if ( isset( $p['template'] ) ) {
			$args['page_template'] = $p['template'];
		}
		$page_ids[ $slug ] = wp_insert_post( wp_slash( $args ) );
	}
	$created = array();
	$page_ids['transparenca'] = inpo_core_transparency_page( $created );
	/* translators: %s: page slugs */
	$log[] = sprintf( __( 'Pages: %s', 'inpo-core' ), implode( ', ', array_keys( $page_ids ) ) );

	// WordPress's sample post and page, if untouched, would show up in the news list and search.
	foreach ( array( array( 'hello-world', 'post' ), array( 'sample-page', 'page' ) ) as $sample ) {
		$p = get_page_by_path( $sample[0], OBJECT, $sample[1] );
		if ( $p && $p->post_date === $p->post_modified ) {
			wp_trash_post( $p->ID );
			/* translators: %s: slug */
			$log[] = sprintf( __( 'Moved to trash: %s', 'inpo-core' ), $sample[0] );
		}
	}

	// Reading settings, site identity and permalinks. Choices already made by an editor are kept.
	if ( 'page' !== get_option( 'show_on_front' ) || ! get_option( 'page_on_front' ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $page_ids['kryefaqja'] );
	}
	if ( ! get_option( 'page_for_posts' ) ) {
		update_option( 'page_for_posts', $page_ids['lajme'] );
	}
	if ( in_array( get_option( 'blogname' ), array( '', 'My WordPress Website', 'My WordPress Site', 'WordPress' ), true ) ) {
		update_option( 'blogname', 'INPO' );
	}
	if ( in_array( get_option( 'blogdescription' ), array( '', 'Just another WordPress site' ), true ) ) {
		update_option( 'blogdescription', 'Iniciativa për Progres' );
	}
	// Albanian site language, so <html lang="sq"> and core strings match the copy.
	if ( '' === (string) get_option( 'WPLANG' ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/translation-install.php';
		if ( wp_can_install_language_pack() ) {
			wp_download_language_pack( 'sq' );
		}
		update_option( 'WPLANG', 'sq' );
		$log[] = __( 'Site language: Albanian', 'inpo-core' );
	}
	global $wp_rewrite;
	// First run: apply the INPO URLs. Later runs keep whatever an editor has set since.
	$first_run = ! get_option( 'inpo_core_setup_done' );
	if ( $first_run || '' === (string) get_option( 'permalink_structure' ) ) {
		$wp_rewrite->set_permalink_structure( '/lajme/%postname%/' );
		$wp_rewrite->set_category_base( 'lajme/kategoria' );
		$log[] = __( 'Permalinks: /lajme/%postname%/, categories under /lajme/kategoria/', 'inpo-core' );
	} else {
		/* translators: %s: permalink structure */
		$log[] = sprintf( __( 'Permalinks already set, left unchanged: %s', 'inpo-core' ), get_option( 'permalink_structure' ) );
	}

	$log[] = inpo_core_setup_menu( $page_ids );

	// Taxonomy URLs were registered with the old bases during this request,
	// so let the next request rebuild the rules instead of flushing stale ones now.
	// A hard flush on the next request also writes .htaccess on Apache.
	delete_option( 'rewrite_rules' );
	update_option( 'inpo_core_flush_rewrites', 1 );
	update_option( 'inpo_core_setup_done', time() );
	// Again at the end: the Posts page (news list) is assigned above.
	inpo_core_setup_listing_pages();
	return $log;
}

/**
 * Finishes a setup's URL changes once the content model is registered with the new bases.
 */
add_action( 'init', 'inpo_core_deferred_flush', 99 );
function inpo_core_deferred_flush() {
	if ( get_option( 'inpo_core_flush_rewrites' ) ) {
		delete_option( 'inpo_core_flush_rewrites' );
		flush_rewrite_rules( true );
	}
}

/**
 * Does the header menu (or "Kryesore") already have the sections of INPO's brief?
 */
function inpo_core_menu_is_current( $menu = null ) {
	if ( ! $menu ) {
		$locations = (array) get_theme_mod( 'nav_menu_locations', array() );
		$menu      = ! empty( $locations['primary'] ) ? wp_get_nav_menu_object( (int) $locations['primary'] ) : wp_get_nav_menu_object( 'Kryesore' );
	}
	if ( ! $menu ) {
		return false;
	}
	$titles = wp_list_pluck( (array) wp_get_nav_menu_items( $menu->term_id ), 'title' );
	return (bool) array_intersect( $titles, array( 'Hulumtime dhe publikime', 'Lajme dhe qëndrime', 'Projektet dhe ndikimi', 'Transparenca' ) );
}

/**
 * Creates the "Kryesore" menu with the structure from INPO's brief and assigns it to the header.
 * A menu without the brief's sections is kept, renamed "Kryesore (e vjetër)", and a new one is made;
 * a menu that already has them is left as it is (unless $force).
 */
function inpo_core_setup_menu( $page_ids, $force = false ) {
	$menu = wp_get_nav_menu_object( 'Kryesore' );
	// The menu in the header may have another name: judge that one.
	$locations = (array) get_theme_mod( 'nav_menu_locations', array() );
	$current   = ! empty( $locations['primary'] ) ? wp_get_nav_menu_object( (int) $locations['primary'] ) : false;
	$menu      = $current ? $current : $menu;
	if ( $menu && ! $force && inpo_core_menu_is_current( $menu ) ) {
		$locations['primary'] = $menu->term_id;
		set_theme_mod( 'nav_menu_locations', $locations );
		return __( 'Main menu already has the new structure and is assigned to the header', 'inpo-core' );
	}
	// The old menu stays under Appearance > Menus (renamed when it was called "Kryesore").
	if ( $menu && 'Kryesore' === $menu->name ) {
		wp_update_nav_menu_object( $menu->term_id, array( 'menu-name' => 'Kryesore (e vjetër ' . wp_date( 'Y-m-d H.i' ) . ')' ) );
	}
	$menu_id = 0;
	$error   = '';
	foreach ( array( 'Kryesore', 'Kryesore (INPO)', 'Kryesore (INPO ' . wp_date( 'Y-m-d H.i.s' ) . ')' ) as $name ) {
		$taken = wp_get_nav_menu_object( $name );
		if ( $taken ) {
			if ( ! wp_get_nav_menu_items( $taken->term_id ) ) {
				$menu_id = $taken->term_id; // An empty menu with this name: use it.
				break;
			}
			continue;
		}
		$res = wp_create_nav_menu( $name );
		if ( ! is_wp_error( $res ) ) {
			$menu_id = (int) $res;
			break;
		}
		$error = $res->get_error_message();
	}
	if ( ! $menu_id ) {
		/* translators: %s: WordPress error */
		return sprintf( __( 'The menu could not be created: %s', 'inpo-core' ), $error ? $error : __( 'unknown error', 'inpo-core' ) );
	}
	inpo_core_build_menu( $menu_id, $page_ids );
	$locations['primary'] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );
	return $menu
		/* translators: %s: old menu name */
		? sprintf( __( 'Main menu rebuilt with the eight sections and assigned to the header. The previous menu is kept under Appearance > Menus (%s).', 'inpo-core' ), wp_get_nav_menu_object( $menu->term_id )->name )
		: __( 'Main menu created with the eight sections and assigned to the header', 'inpo-core' );
}

/**
 * Page IDs the menu links to.
 */
function inpo_core_menu_pages() {
	$id = function ( $slug ) {
		$p = get_page_by_path( $slug );
		return $p ? $p->ID : 0;
	};
	return array( 'lajme' => (int) get_option( 'page_for_posts' ), 'rreth-nesh' => $id( 'rreth-nesh' ), 'kontakti' => $id( 'kontakti' ), 'transparenca' => $id( 'transparenca' ) );
}

/**
 * The eight sections and their sub-items, as in INPO's brief.
 */
function inpo_core_build_menu( $menu_id, $page_ids ) {
	$add    = function ( $args ) use ( $menu_id ) {
		return wp_update_nav_menu_item( $menu_id, 0, array_merge( array( 'menu-item-status' => 'publish' ), $args ) );
	};
	$custom = function ( $title, $url, $parent = 0 ) use ( $add ) {
		return $add( array( 'menu-item-title' => $title, 'menu-item-type' => 'custom', 'menu-item-url' => $url, 'menu-item-parent-id' => $parent ) );
	};
	$page   = function ( $title, $id, $parent = 0 ) use ( $add ) {
		return $add( array( 'menu-item-title' => $title, 'menu-item-type' => 'post_type', 'menu-item-object' => 'page', 'menu-item-object-id' => $id, 'menu-item-parent-id' => $parent ) );
	};

	$custom( 'Kryefaqja', home_url( '/' ) );

	$about  = get_permalink( $page_ids['rreth-nesh'] );
	$parent = $page( 'Rreth nesh', $page_ids['rreth-nesh'] );
	foreach ( array( 'historia' => 'Historia e organizatës', 'misioni' => 'Misioni, vizioni dhe vlerat', 'ekipi' => 'Ekipi dhe bordi', 'dokumentet' => 'Strategjitë dhe dokumentet organizative' ) as $anchor => $title ) {
		$custom( $title, $about . '#' . $anchor, $parent );
	}

	$programs = inpo_core_programs();
	$parent   = $custom( 'Programet', $programs ? get_term_link( $programs[0] ) : home_url( '/' ) );
	foreach ( $programs as $t ) {
		$add( array( 'menu-item-title' => $t->name, 'menu-item-type' => 'taxonomy', 'menu-item-object' => INPO_CORE_PROGRAM, 'menu-item-object-id' => $t->term_id, 'menu-item-parent-id' => $parent ) );
	}

	$parent = $custom( 'Hulumtime dhe publikime', inpo_core_pubs_url() );
	foreach ( inpo_core_type_definitions() as $slug => $def ) {
		if ( $def[2] < 6 && term_exists( $slug, INPO_CORE_PUB_TYPE ) ) {
			$custom( $def[0], inpo_core_pubs_url( array( 'lloji' => $slug ) ), $parent );
		}
	}
	$data = get_post_type_archive_link( INPO_CORE_DATASET );
	$custom( 'Të dhëna dhe statistika', $data ? $data : home_url( '/te-dhenat/' ), $parent );

	$news   = get_permalink( $page_ids['lajme'] );
	$parent = $page( 'Lajme dhe qëndrime', $page_ids['lajme'] );
	foreach ( inpo_core_news_sections() as $slug => $def ) {
		if ( term_exists( $slug, 'category' ) ) {
			$custom( $def[0], add_query_arg( 'kategoria', $slug, $news ), $parent );
		}
	}

	$projects = inpo_core_projects_url();
	$parent   = $custom( 'Projektet dhe ndikimi', $projects );
	$custom( 'Projektet aktive', add_query_arg( 'statusi', 'aktive', $projects ), $parent );
	$custom( 'Projektet e përfunduara', add_query_arg( 'statusi', 'perfunduara', $projects ), $parent );
	$custom( 'Partnerët dhe donatorët', $projects . '#partneret', $parent );
	$custom( 'Rezultatet dhe ndikimi institucional', $projects . '#ndikimi', $parent );

	if ( ! empty( $page_ids['transparenca'] ) ) {
		$url    = get_permalink( $page_ids['transparenca'] );
		$parent = $page( 'Transparenca', $page_ids['transparenca'] );
		foreach ( array_slice( inpo_core_doc_definitions(), 0, 5, true ) as $slug => $def ) {
			$custom( $def[0], $url . '#' . $slug, $parent );
		}
	}

	$page( 'Kontakti', $page_ids['kontakti'] );
	update_option( 'inpo_core_menu_v', 2 );
}

/**
 * Demo content for the brief's new sections: projects, documents, datasets (illustrative values).
 *
 * @return string[] Log lines.
 */
function inpo_core_demo_brief( $data, $pub_ids ) {
	$log = array();
	if ( ! get_posts( array( 'post_type' => INPO_CORE_PROJECT, 'post_status' => 'any', 'posts_per_page' => 1, 'fields' => 'ids' ) ) ) {
		foreach ( (array) $data['PROJECTS'] as $i => $p ) {
			$results = array();
			foreach ( (array) $p['results'] as $r ) {
				$results[] = $r[0] . ' | ' . $r[1];
			}
			$meta = array(
				'inpo_status'     => 'aktiv' === $p['status'] ? '' : 'perfunduar',
				'inpo_period'     => $p['period'],
				'inpo_donor_text' => $p['donor'],
				'inpo_partners'   => $p['partners'],
				'inpo_goals'      => implode( "\n", (array) $p['goals'] ),
				'inpo_results'    => implode( "\n", $results ),
			);
			if ( ! empty( $p['pub'] ) && isset( $pub_ids[ $p['pub'] ] ) ) {
				$meta['inpo_publication'] = $pub_ids[ $p['pub'] ];
			}
			$id = wp_insert_post( wp_slash( array( 'post_type' => INPO_CORE_PROJECT, 'post_status' => 'publish', 'post_title' => $p['title'], 'post_name' => $p['id'], 'menu_order' => $i, 'meta_input' => array_filter( $meta, 'strlen' ) ) ) );
			if ( $id && ! is_wp_error( $id ) ) {
				wp_set_object_terms( $id, $p['prog'], INPO_CORE_PROGRAM );
			}
		}
		/* translators: %d: number */
		$log[] = sprintf( __( 'Projects: %d', 'inpo-core' ), count( (array) $data['PROJECTS'] ) );
	}
	$log[]   = inpo_core_setup_documents();
	$doc_cat = array( 'vjetore' => 'raportet-vjetore', 'financiare' => 'raportet-financiare', 'auditimi' => 'raportet-e-auditimit', 'qeverisja' => 'dokumentet-e-qeverisjes-organizative', 'politikat' => 'politikat-dhe-procedurat-e-brendshme', 'strategjite' => 'strategjite-dhe-dokumentet-organizative' );
	foreach ( (array) $data['DOCS'] as $i => $d ) {
		list( $id, $new ) = inpo_core_find_or_create( INPO_CORE_DOC, $d['title'], $i );
		if ( $id && $new ) {
			update_post_meta( $id, 'inpo_year', $d['year'] );
			wp_set_object_terms( $id, $doc_cat[ $d['cat'] ], INPO_CORE_DOC_CAT );
		}
	}
	$log = array_merge( $log, inpo_core_add_sample_data() );
	return $log;
}
