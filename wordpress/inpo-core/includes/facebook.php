<?php
/**
 * Facebook posts on the old site.
 *
 * Four infographics were published on inpo-ks.org as a news post holding only a Facebook box. At go-live they
 * become Infografika publications: their images (from the old media library or bundled in seed/infographics/,
 * taken from INPO's own Facebook posts), INPO's text from the post, the original date and slug. The old news
 * address redirects. Switching back to the old theme turns them back into the news posts they were.
 *
 * Any other Facebook box in an article shows as a link, so the site loads no Facebook scripts.
 *
 * @package inpo-core
 */

defined( 'ABSPATH' ) || exit;

/**
 * The infographics: key => Facebook post (found in the old post's content), images, title, lead and text.
 * Texts are INPO's own, from the Facebook posts. "slides" is the number of bundled images (0: the images are
 * already attached to the old post). "related": a news post on the same subject that links to it.
 */
function inpo_core_facebook_infographics() {
	return array(
		'trendi'     => array(
			'fb'     => 'pfbid0UJXXNryLVA5d7MeU8Yc81X5oZkUX4w8DMtAVddwyQURdfBSJGmRsDkuQxShpLuYxl',
			'slides' => 0,
			'title'  => 'Trendi i anulimit të tenderëve 2024–2026',
			'lead'   => 'Kur një tender anulohet, nuk kemi të bëjmë vetëm me një procedurë të mbyllur. Shpesh pas tij mbetet një investim që vonohet, një shërbim që pret apo një proces që duhet të nisë sërish nga fillimi.',
			'text'   => array(
				'Prandaj, anulimet janë një tregues i rëndësishëm për të kuptuar sa mirë po planifikohet dhe po funksionon prokurimi publik në praktikë.',
				'Vetëm në periudhën janar–shtator 2026, 20.6% e procedurave të iniciuara janë anuluar, ndërsa vlera e aktiviteteve të anuluara arrin në 129.3 milionë euro.',
				'Veçanërisht domethënëse janë kontratat për punë: në nëntë muaj janë anuluar 140 aktivitete, me vlerë rreth 49 milionë euro.',
				'Anulimet, vetvetiu, nuk nënkuptojnë domosdoshmërisht parregullsi apo shkelje ligjore. Por kur ato shfaqen në përmasa të larta, duhet të shihen më nga afër: sa mirë planifikohen tenderët, sa funksionon konkurrenca, sa realiste janë kërkesat e autoriteteve kontraktuese dhe sa efikas është prokurimi në kthimin e buxhetit në rezultate konkrete.',
			),
		),
		'boshlleqet' => array(
			'fb'      => 'pfbid0RcFJaqwqzNsQ8mMjfekf25fw2y9dQB2YLMAq5sZBpKYotkpnEknh83nfnQRnb7yzl',
			'slides'  => 7,
			'title'   => 'Boshllëqet e transparencës në procedurat e negociuara',
			'lead'    => 'INPO publikoi raportin “Boshllëqet e transparencës në procedurat e negociuara”, i cili trajton sfidat e transparencës, arsyetimit dhe mbikëqyrjes në përdorimin e këtyre procedurave.',
			'text'    => array(
				'Përmes këtyre infografikave, sjellim në mënyrë të përmbledhur gjetjet kryesore të raportit dhe çështjet që kërkojnë më shumë vëmendje për të forcuar transparencën dhe llogaridhënien në prokurimin publik.',
			),
			'related' => '2011325626215625',
		),
		'temf'       => array(
			'fb'     => 'pfbid0255xwvkC2FpsJNfJkryCZzYNpZKg3VMGnmhk1ifKsANg7HCUAfc95iiQ4Tk6kqoVAl',
			'slides' => 6,
			'title'  => 'TEMF në prokurimin publik',
			'lead'   => 'Ligji për Prokurimin Publik parasheh dy kritere për dhënien e kontratës: çmimin më të ulët dhe tenderin ekonomikisht më të favorshëm (TEMF).',
			'text'   => array(
				'Për dallim nga çmimi më i ulët, TEMF u mundëson autoriteteve kontraktuese që, krahas çmimit, të vlerësojnë edhe elemente të tjera relevante, si cilësia, kostot e operimit dhe mirëmbajtjes, karakteristikat funksionale e teknike dhe faktorë të tjerë që lidhen me objektin e kontratës.',
				'Rritja e përdorimit të TEMF nuk është vetëm çështje e praktikës së prokurimit. Qeveria e Kosovës e ka përfshirë përdorimin e këtij kriteri në Agjendën e Reformave, në kuadër të reformave për menaxhimin e financave publike. Synimi është rritja e përdorimit të raportit më të mirë çmim–cilësi (TEMF) në së paku 10% të kontratave me vlerë të madhe deri në vitin 2027.',
				'## Por çfarë tregojnë të dhënat për vitin 2025?',
				'Bazuar në monitorimin e INPO-s, gjatë vitit 2025 kriteri TEMF është përdorur në rreth 12% të kontratave të lidhura. Në total janë identifikuar 244 kontrata, të realizuara nga 56 autoritete kontraktuese.',
				'Në shikim të parë, rritja e përdorimit të TEMF është zhvillim pozitiv. Megjithatë, numri i kontratave ku përdoret TEMF nuk është i mjaftueshëm për të matur efikasitetin e prokurimit publik. Çështja kryesore është se si përdoret ky kriter dhe nëse ai realisht rezulton në përzgjedhjen e ofertës që siguron raportin më të mirë ndërmjet çmimit dhe cilësisë.',
				'Nga 244 kontratat e identifikuara, 116 janë kontrata të konsulencës individuale, ndërsa vetëm 128 kontrata u përkasin kategorive të tjera të prokurimit. Kjo tregon se një pjesë e konsiderueshme e përdorimit të TEMF është e përqendruar në një kategori specifike dhe, rrjedhimisht, vetëm rritja numerike e përdorimit të kriterit nuk dëshmon domosdoshmërisht një ndryshim të gjerë të praktikës së prokurimit.',
				'128 kontratat e analizuara kanë vlerë të përgjithshme prej 114.89 milionë euro. Prej tyre, 57 janë kontrata me vlerë të madhe, 58 me vlerë të mesme dhe 13 me vlerë të ulët. Megjithatë, mbi 106.9 milionë euro, apo pjesa dërrmuese e vlerës së përgjithshme, është e përqendruar në kontratat me vlerë të madhe. Kjo e bën veçanërisht të rëndësishme analizën e mënyrës se si është përdorur TEMF pikërisht në kontratat ku ekspozimi i parasë publike është më i lartë.',
				'Edhe sipas llojit të kontratës vërehet një përqendrim i theksuar. Nga 128 kontratat, 62 janë kontrata për punë, me vlerë prej 98.3 milionë euro, ndërsa 49 kontrata për shërbime arrijnë vlerën prej rreth 12.95 milionë euro. Pra, pjesa më e madhe e vlerës së kontraktuar përmes TEMF është e përqendruar në kontratat për punë.',
				'Në aspektin e procedurave, 98 nga 128 kontratat janë realizuar përmes procedurës së hapur, me vlerë mbi 111.3 milionë euro. Dominimi i procedurës së hapur është pozitiv nga perspektiva e konkurrencës formale, por procedura e hapur, vetvetiu, nuk garanton vlerë për para. Rëndësi vendimtare ka mënyra se si janë përcaktuar dhe peshuar kriteret e cilësisë dhe nëse ato kanë ndikuar realisht në përzgjedhjen e ofertës fituese.',
				'Monitorimi i INPO-s ka evidentuar pikërisht këtë sfidë: në një pjesë të rasteve të analizuara, TEMF vazhdon të përdoret më shumë formalisht sesa substancialisht. Kriteret shpesh reduktohen në çmim dhe një numër të kufizuar elementesh teknike, ndërsa elemente të rëndësishme për vlerën për para – si cilësia, performanca, kostot gjatë ciklit jetësor, mirëmbajtja apo aspekte të tjera relevante – mbeten të kufizuara.',
				'Prandaj, 12% nuk duhet lexuar automatikisht si 12% progres në efikasitetin e prokurimit.',
				'Rritja e përdorimit të TEMF është e nevojshme, por progresi real duhet të matet edhe përmes cilësisë së kritereve të përdorura, peshës reale që u jepet faktorëve joçmimorë dhe rezultateve që kontratat prodhojnë për paranë publike.',
				'Sfida për institucionet nuk është vetëm të rrisin numrin e procedurave me TEMF, por ta shndërrojnë TEMF nga një kriter formal në një instrument real për vlerë për para, cilësi dhe efikasitet në prokurimin publik.',
			),
		),
		'plani'      => array(
			'fb'     => 'pfbid02rr6gEBWnRazKWid7rHrr4FayUwKZQHT6LYcfWxkJwe9FCfHbDdRhcTV2pGFdgSunl',
			'slides' => 5,
			'title'  => 'Performanca e Qeverisë në realizimin e planeve të prokurimit gjatë gjashtëmujorit të parë të vitit 2026',
			'lead'   => 'Monitorimi i INPO-s në 18 ministri dhe Zyrën e Kryeministrit tregon një hendek të theksuar ndërmjet planifikimit dhe zbatimit.',
			'text'   => array(
				'- A janë iniciuar me kohë aktivitetet e planifikuara?',
				'- Sa prej mbi 600 milionë eurove të planifikuara kanë kaluar realisht në proces prokurimi?',
				'- Sa prej tyre lidhen me punë dhe investime kapitale?',
				'- Dhe sa aktivitete janë iniciuar pa qenë fare pjesë e planeve të prokurimit?',
				'## Gjetjet kryesore',
				'- Nga 1,128 aktivitete të planifikuara, me vlerë 600.57 milionë euro, deri më 30 qershor 2026 ishin iniciuar vetëm 170 aktivitete, në vlerë 31.99 milionë euro.',
				'- Pra, vetëm 5.33% e vlerës së planifikuar kishte kaluar në fazën e inicimit.',
				'- Nga 170 aktivitetet e iniciuara, vetëm 9 ishin për punë/investime kapitale.',
				'- Ndërkohë, 59 aktivitete, në vlerë 27.84 milionë euro, janë iniciuar edhe pse nuk ishin të përfshira në Planet e Prokurimit.',
				'A po shërbejnë Planet e Prokurimit si instrument real për planifikimin dhe menaxhimin e parasë publike, apo po mbesin vetëm në plan?',
			),
		),
	);
}

/**
 * Block markup from text lines: "## " a heading, "- " a list item (consecutive ones form one list), else a paragraph.
 *
 * @param string[] $lines Lines.
 * @return string
 */
function inpo_core_text_blocks( $lines ) {
	$out  = array();
	$list = array();
	$flush = function () use ( &$list, &$out ) {
		if ( $list ) {
			$items = '';
			foreach ( $list as $li ) {
				$items .= "<!-- wp:list-item -->\n<li>" . esc_html( $li ) . "</li>\n<!-- /wp:list-item -->\n";
			}
			$out[] = "<!-- wp:list -->\n<ul class=\"wp-block-list\">\n" . $items . "</ul>\n<!-- /wp:list -->";
			$list  = array();
		}
	};
	foreach ( $lines as $line ) {
		if ( 0 === strpos( $line, '- ' ) ) {
			$list[] = substr( $line, 2 );
			continue;
		}
		$flush();
		if ( 0 === strpos( $line, '## ' ) ) {
			$out[] = "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">" . esc_html( substr( $line, 3 ) ) . "</h2>\n<!-- /wp:heading -->";
		} else {
			$out[] = "<!-- wp:paragraph -->\n<p>" . esc_html( $line ) . "</p>\n<!-- /wp:paragraph -->";
		}
	}
	$flush();
	return implode( "\n\n", $out );
}

/**
 * The old news post whose content holds a Facebook address (the Albanian one first).
 */
function inpo_core_post_with( $needle, $type = 'post' ) {
	global $wpdb;
	return (int) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_status IN ('publish','future','draft','pending','private') AND post_content LIKE %s ORDER BY post_status = 'publish' DESC, ID ASC LIMIT 1",
			$type,
			'%' . $wpdb->esc_like( $needle ) . '%'
		)
	);
}

/**
 * The publication type "Infografika" (created if missing).
 */
function inpo_core_infographic_term() {
	$term = get_term_by( 'slug', 'infografika', INPO_CORE_PUB_TYPE );
	if ( ! $term ) {
		foreach ( (array) get_terms( array( 'taxonomy' => INPO_CORE_PUB_TYPE, 'hide_empty' => false ) ) as $t ) {
			if ( $t instanceof WP_Term && preg_match( '/infografik/iu', $t->name ) ) {
				return $t;
			}
		}
		$new  = wp_insert_term( 'Infografika', INPO_CORE_PUB_TYPE, array( 'slug' => 'infografika' ) );
		$term = is_wp_error( $new ) ? null : get_term( $new['term_id'], INPO_CORE_PUB_TYPE );
	}
	return $term;
}

/**
 * Images of an infographic: the bundled ones (added to the media library once) or those attached to the old post.
 *
 * @return int[] Attachment IDs in order.
 */
function inpo_core_infographic_images( $key, $def, $post_id ) {
	$ids = array();
	if ( ! $def['slides'] ) {
		$kids = get_children( array( 'post_parent' => $post_id, 'post_type' => 'attachment', 'post_mime_type' => 'image', 'numberposts' => -1 ) );
		uasort(
			$kids,
			function ( $a, $b ) {
				return strnatcmp( wp_basename( (string) get_attached_file( $a->ID ) ), wp_basename( (string) get_attached_file( $b->ID ) ) );
			}
		);
		return array_map( 'intval', array_keys( $kids ) );
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	for ( $i = 1; $i <= $def['slides']; $i++ ) {
		$file  = sprintf( '%s-%02d.jpg', $key, $i );
		$found = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'meta_key' => '_inpo_seed', 'meta_value' => $file, 'fields' => 'ids', 'posts_per_page' => 1 ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
		if ( $found ) {
			$ids[] = (int) $found[0];
			continue;
		}
		$src = INPO_CORE_DIR . 'seed/infographics/' . $file;
		$tmp = wp_tempnam( $file );
		if ( ! file_exists( $src ) || ! $tmp || ! copy( $src, $tmp ) ) {
			continue;
		}
		$alt = $def['title'] . ': pamja ' . $i . ' nga ' . $def['slides'];
		$id  = media_handle_sideload( array( 'name' => 'inpo-infografika-' . $file, 'tmp_name' => $tmp ), $post_id, $alt );
		if ( is_wp_error( $id ) ) {
			wp_delete_file( $tmp );
			continue;
		}
		update_post_meta( $id, '_inpo_seed', $file );
		update_post_meta( $id, '_wp_attachment_image_alt', $alt );
		$ids[] = (int) $id;
	}
	return count( $ids ) === (int) $def['slides'] ? $ids : array();
}

/**
 * Turns the Facebook infographic posts into Infografika publications. Records what it changed in $backup
 * (for inpo_core_restore) and the old addresses in $paths (old path => new URL). Safe to run again.
 *
 * @param array $backup Go-live backup, by reference.
 * @param array $paths  Redirects, by reference.
 * @return string[] Titles converted.
 */
function inpo_core_convert_facebook_infographics( &$backup, &$paths ) {
	$term = inpo_core_infographic_term();
	if ( ! $term ) {
		return array();
	}
	@set_time_limit( 300 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	$themes = inpo_core_ensure_terms( INPO_CORE_THEME, inpo_core_theme_definitions() );
	$insts  = inpo_core_ensure_terms( INPO_CORE_INST, inpo_core_institution_definitions() );
	$done   = array();
	foreach ( inpo_core_facebook_infographics() as $key => $def ) {
		$id   = inpo_core_post_with( $def['fb'] );
		$post = $id ? get_post( $id ) : null;
		if ( ! $post ) {
			continue;
		}
		$slides = inpo_core_infographic_images( $key, $def, $id );
		if ( ! $slides ) {
			continue;
		}
		$old_url = get_permalink( $post );
		$backup['converted'][ $id ] = array(
			'post_type'    => $post->post_type,
			'post_title'   => $post->post_title,
			'post_content' => $post->post_content,
			'post_excerpt' => $post->post_excerpt,
			'thumbnail'    => (int) get_post_thumbnail_id( $post ),
			'post_name'    => $post->post_name,
		);
		$update = array(
			'ID'           => $id,
			'post_type'    => INPO_CORE_PUB,
			'post_title'   => $def['title'],
			'post_excerpt' => $def['lead'],
			'post_content' => inpo_core_text_blocks( $def['text'] ),
		);
		// A slug made of styled letters (%f0%9d...) gets a readable one; the old address redirects.
		if ( false !== strpos( $post->post_name, '%' ) ) {
			$update['post_name'] = sanitize_title( $def['title'] );
		}
		wp_update_post( wp_slash( $update ) );
		clean_post_cache( $id );
		wp_set_object_terms( $id, (int) $term->term_id, INPO_CORE_PUB_TYPE );
		update_post_meta( $id, 'inpo_slides', implode( ',', $slides ) );
		set_post_thumbnail( $id, $slides[0] );
		inpo_core_label_by_title( $id, $themes, $insts );

		$new_url = get_permalink( $id );
		foreach ( array_unique( array( (string) wp_parse_url( $old_url, PHP_URL_PATH ), '/' . $post->post_name . '/' ) ) as $path ) {
			if ( $path && '/' !== $path ) {
				$paths[ trailingslashit( rawurldecode( $path ) ) ] = $new_url;
			}
		}
		// The news post on the same subject (here a video) links to the infographic in its sidebar.
		if ( ! empty( $def['related'] ) ) {
			$rel = inpo_core_post_with( $def['related'] );
			if ( $rel && ! get_post_meta( $rel, 'inpo_publication', true ) ) {
				update_post_meta( $rel, 'inpo_publication', $id );
				$backup['related'][] = $rel;
			}
		}
		$done[] = $def['title'];
	}
	return $done;
}

/**
 * Puts the converted posts back as the news posts they were (inpo_core_restore).
 *
 * @return int Posts restored.
 */
function inpo_core_unconvert_facebook_infographics( $backup ) {
	$n = 0;
	foreach ( isset( $backup['converted'] ) ? (array) $backup['converted'] : array() as $id => $p ) {
		$back = array( 'ID' => $id, 'post_type' => $p['post_type'], 'post_title' => $p['post_title'], 'post_content' => $p['post_content'], 'post_excerpt' => $p['post_excerpt'] );
		if ( ! empty( $p['post_name'] ) ) {
			$back['post_name'] = $p['post_name'];
		}
		wp_update_post( wp_slash( $back ) );
		clean_post_cache( $id );
		wp_delete_object_term_relationships( $id, INPO_CORE_PUB_TYPE );
		delete_post_meta( $id, 'inpo_slides' );
		if ( $p['thumbnail'] ) {
			set_post_thumbnail( $id, $p['thumbnail'] );
		} else {
			delete_post_thumbnail( $id );
		}
		++$n;
	}
	foreach ( isset( $backup['related'] ) ? (array) $backup['related'] : array() as $id ) {
		delete_post_meta( $id, 'inpo_publication' );
	}
	return $n;
}

/**
 * INPO's own videos that the old site showed as a Facebook box: Facebook video number => bundled file
 * (seed/videos/, H.264 with sound, so every browser plays it) and its poster frame.
 */
function inpo_core_facebook_videos() {
	return array(
		'2011325626215625' => array( 'file' => 'boshlleqet-reel.mp4', 'poster' => 'boshlleqet-reel.jpg', 'title' => 'Boshllëqet e transparencës në procedurat e negociuara' ),
	);
}

/**
 * A bundled file in the media library (added once; found again by its _inpo_seed name).
 */
function inpo_core_seed_attachment( $dir, $file, $parent, $title ) {
	$found = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'meta_key' => '_inpo_seed', 'meta_value' => $file, 'fields' => 'ids', 'posts_per_page' => 1 ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
	if ( $found ) {
		return (int) $found[0];
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$src = INPO_CORE_DIR . $dir . $file;
	$tmp = wp_tempnam( $file );
	if ( ! file_exists( $src ) || ! $tmp || ! copy( $src, $tmp ) ) {
		return 0;
	}
	$id = media_handle_sideload( array( 'name' => 'inpo-' . $file, 'tmp_name' => $tmp ), $parent, $title );
	if ( is_wp_error( $id ) ) {
		wp_delete_file( $tmp );
		return 0;
	}
	update_post_meta( $id, '_inpo_seed', $file );
	return (int) $id;
}

/**
 * Replaces the Facebook video box in a post with the site's own player. Records the old content in $backup.
 *
 * @return int Posts changed.
 */
function inpo_core_convert_facebook_videos( &$backup ) {
	@set_time_limit( 300 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	$n = 0;
	foreach ( inpo_core_facebook_videos() as $fb => $def ) {
		$id   = inpo_core_post_with( $fb );
		$post = $id ? get_post( $id ) : null;
		if ( ! $post || false === strpos( $post->post_content, 'facebook.com/plugins/video.php' ) ) {
			continue;
		}
		$video  = inpo_core_seed_attachment( 'seed/videos/', $def['file'], $id, $def['title'] );
		$poster = inpo_core_seed_attachment( 'seed/videos/', $def['poster'], $id, $def['title'] );
		if ( ! $video ) {
			continue;
		}
		$url    = wp_get_attachment_url( $video );
		$poster = $poster ? wp_get_attachment_url( $poster ) : '';
		$block  = '<!-- wp:video {"id":' . $video . '} -->' . "\n" . '<figure class="wp-block-video"><video controls playsinline preload="metadata" src="' . esc_url( $url ) . '"' . ( $poster ? ' poster="' . esc_url( $poster ) . '"' : '' ) . '></video></figure>' . "\n" . '<!-- /wp:video -->';
		$new    = preg_replace( '#(?:<div[^>]*>\s*)?<iframe[^>]+facebook\.com/plugins/video\.php[^>]*>\s*</iframe>(?:\s*</div>)?#i', $block, $post->post_content, 1 );
		if ( ! $new || $new === $post->post_content ) {
			continue;
		}
		$backup['videos'][ $id ] = $post->post_content;
		wp_update_post( wp_slash( array( 'ID' => $id, 'post_content' => $new ) ) );
		if ( $poster && ! has_post_thumbnail( $id ) ) {
			set_post_thumbnail( $id, inpo_core_seed_attachment( 'seed/videos/', $def['poster'], $id, $def['title'] ) );
			$backup['video_thumbs'][] = $id;
		}
		++$n;
	}
	return $n;
}

/**
 * Puts the Facebook boxes back (inpo_core_restore).
 */
function inpo_core_unconvert_facebook_videos( $backup ) {
	foreach ( isset( $backup['videos'] ) ? (array) $backup['videos'] : array() as $id => $content ) {
		wp_update_post( wp_slash( array( 'ID' => $id, 'post_content' => $content ) ) );
	}
	foreach ( isset( $backup['video_thumbs'] ) ? (array) $backup['video_thumbs'] : array() as $id ) {
		delete_post_thumbnail( $id );
	}
	return isset( $backup['videos'] ) ? count( (array) $backup['videos'] ) : 0;
}

/* ---------- Video players: the browser's own, full width ---------- */

// [video] shortcodes (older posts): the browser's player instead of MediaElement's, so it matches the site.
add_filter(
	'wp_video_shortcode_library',
	function ( $library ) {
		return inpo_core_theme_active() && ! is_admin() ? 'none' : $library;
	}
);

// YouTube: full width at 16:9, from youtube-nocookie.com (no cookies until the visitor plays).
add_filter(
	'embed_oembed_html',
	function ( $html, $url ) {
		if ( is_admin() || ! inpo_core_theme_active() || ! preg_match( '#youtu\.?be#', (string) $url ) ) {
			return $html;
		}
		$html = str_replace( array( 'https://www.youtube.com/embed/', 'https://youtube.com/embed/' ), 'https://www.youtube-nocookie.com/embed/', $html );
		$html = preg_replace( '/\s(width|height)="\d+"/', '', $html );
		return '<div class="video-embed">' . $html . '</div>';
	},
	10,
	2
);

// A site that went live with an earlier version: convert on the next wp-admin page (the images take a moment).
add_action(
	'admin_init',
	function () {
		if ( ! get_option( 'inpo_core_needs_facebook' ) || ! current_user_can( 'manage_options' ) || ! get_option( 'inpo_core_live' ) || ! inpo_core_theme_active() ) {
			return;
		}
		delete_option( 'inpo_core_needs_facebook' );
		$backup = get_option( 'inpo_core_live_backup' );
		$backup = is_array( $backup ) ? $backup : array();
		$paths  = array();
		$done   = inpo_core_convert_facebook_infographics( $backup, $paths );
		$videos = inpo_core_convert_facebook_videos( $backup );
		if ( ! $done && ! $videos ) {
			return;
		}
		update_option( 'inpo_core_live_backup', $backup, false );
		$map          = get_option( 'inpo_core_redirects' );
		$map          = is_array( $map ) ? $map : array( 'pages' => array(), 'paths' => array() );
		$map['paths'] = array_merge( isset( $map['paths'] ) ? (array) $map['paths'] : array(), $paths );
		update_option( 'inpo_core_redirects', $map, false );
		$lines = array();
		if ( $done ) {
			/* translators: %s: titles */
			$lines[] = sprintf( __( 'Facebook infographics now under Publications > Infografika: %s', 'inpo-core' ), implode( '; ', $done ) );
		}
		if ( $videos ) {
			/* translators: %d: number of videos */
			$lines[] = sprintf( __( 'Facebook videos now play on the site: %d', 'inpo-core' ), $videos );
		}
		set_transient( 'inpo_core_notice', array( 'upgrade', $lines ), HOUR_IN_SECONDS );
	}
);

/**
 * A Facebook box in an article (post or video) becomes a link: no Facebook scripts or trackers on the site.
 */
function inpo_core_facebook_links( $html ) {
	if ( false === strpos( $html, 'facebook.com/plugins/' ) || ! inpo_core_theme_active() || is_admin() ) {
		return $html;
	}
	return preg_replace_callback(
		'#(?:<div[^>]*>\s*)?<iframe[^>]+src="https://www\.facebook\.com/plugins/(post|video)\.php\?([^"]*)"[^>]*>\s*</iframe>(?:\s*</div>)?#i',
		function ( $m ) {
			parse_str( html_entity_decode( $m[2] ), $q );
			$url  = isset( $q['href'] ) ? (string) $q['href'] : '';
			$host = (string) wp_parse_url( $url, PHP_URL_HOST );
			if ( ! preg_match( '/(^|\.)facebook\.com$/', $host ) ) {
				return '';
			}
			$label = 'video' === $m[1] ? 'Shiko videon në Facebook' : 'Shiko postimin në Facebook';
			return '<p class="fb-link"><a class="btn btn-line" href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . esc_html( $label ) . ' ' . inpo_core_icon( 'arrow' ) . '</a></p>';
		},
		$html
	);
}
add_filter( 'the_content', 'inpo_core_facebook_links', 9 );
