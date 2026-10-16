<?php
/**
 * Admin UI (English): the INPO menu (settings, team, donors, messages, subscribers, setup),
 * meta boxes for posts, publications, pages, donors and team members, and programme fields.
 * Classic meta boxes keep the plugin free of a build step; they show in the block editor too.
 * The public site's copy stays Albanian.
 */

defined( 'ABSPATH' ) || exit;

/* ---------- Menu and settings ---------- */

add_action( 'admin_menu', 'inpo_core_admin_menu' );
function inpo_core_admin_menu() {
	add_menu_page( 'INPO', 'INPO', 'edit_posts', 'inpo-core', 'inpo_core_settings_page', 'dashicons-building', 3 );
	// Editors (not only administrators) keep the organisation details up to date.
	add_submenu_page( 'inpo-core', __( 'INPO settings', 'inpo-core' ), __( 'Settings', 'inpo-core' ), 'edit_pages', 'inpo-core', 'inpo_core_settings_page' );
	add_submenu_page( 'inpo-core', __( 'Where to edit what', 'inpo-core' ), __( 'Help: where to edit', 'inpo-core' ), 'edit_posts', 'inpo-help', 'inpo_core_help_page' );
}
add_filter(
	'option_page_capability_inpo_core',
	function () {
		return 'edit_pages';
	}
);

/**
 * INPO submenu in a fixed order: Settings, Setup, Team, Donors, Messages, Subscribers.
 */
add_action(
	'admin_menu',
	function () {
		global $submenu;
		if ( empty( $submenu['inpo-core'] ) ) {
			return;
		}
		$order = array( 'inpo-help', 'inpo-core', 'inpo-setup', 'edit.php?post_type=' . INPO_CORE_MEMBER, 'edit.php?post_type=' . INPO_CORE_DONOR, 'edit.php?post_type=' . INPO_CORE_MESSAGE, 'edit.php?post_type=' . INPO_CORE_SUBSCRIBER );
		usort(
			$submenu['inpo-core'],
			function ( $a, $b ) use ( $order ) {
				$x = array_search( $a[2], $order, true );
				$y = array_search( $b[2], $order, true );
				return ( false === $x ? 99 : $x ) - ( false === $y ? 99 : $y );
			}
		);
	},
	999
);

function inpo_core_setting_fields() {
	return array(
		__( 'Organisation', 'inpo-core' )        => array(
			'short_name' => array( __( 'Short name (logo and footer wordmark)', 'inpo-core' ), 'text' ),
			'name_long'  => array( __( 'Full name', 'inpo-core' ), 'text' ),
			'about'      => array( __( 'Footer description', 'inpo-core' ), 'textarea' ),
			'email'      => array( __( 'Public email', 'inpo-core' ), 'email' ),
			'phone'      => array( __( 'Phone', 'inpo-core' ), 'text' ),
			'audit_url'  => array( __( '"Raportet e auditimit" link (footer)', 'inpo-core' ), 'url' ),
		),
		__( 'Offices', 'inpo-core' )             => array(
			'office1'  => array( __( 'Office 1: title', 'inpo-core' ), 'text' ),
			'address1' => array( __( 'Office 1: address (one line per line)', 'inpo-core' ), 'textarea' ),
			'map1'     => array( __( 'Office 1: map link', 'inpo-core' ), 'url' ),
			'office2'  => array( __( 'Office 2: title', 'inpo-core' ), 'text' ),
			'address2' => array( __( 'Office 2: address', 'inpo-core' ), 'textarea' ),
			'map2'     => array( __( 'Office 2: map link', 'inpo-core' ), 'url' ),
		),
		__( 'Social networks', 'inpo-core' )     => array(
			'facebook' => array( 'Facebook', 'url' ),
			'x'        => array( 'X', 'url' ),
			'youtube'  => array( 'YouTube', 'url' ),
			'instagram' => array( 'Instagram', 'url' ),
			'linkedin'  => array( 'LinkedIn', 'url' ),
		),
		__( 'Donors and forms', 'inpo-core' )    => array(
			'fineprint' => array( __( 'Grant note under the donors', 'inpo-core' ), 'textarea' ),
			'recipient' => array( __( 'Email that receives contact messages', 'inpo-core' ), 'email' ),
		),
	);
}

add_action( 'admin_init', 'inpo_core_register_settings' );
function inpo_core_register_settings() {
	register_setting(
		'inpo_core',
		INPO_CORE_OPTION,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'inpo_core_sanitize_settings',
		)
	);
}

function inpo_core_sanitize_settings( $input ) {
	$clean = array();
	foreach ( inpo_core_setting_fields() as $fields ) {
		foreach ( $fields as $key => $field ) {
			// options.php has already unslashed the input.
			$value = isset( $input[ $key ] ) ? inpo_core_str( $input[ $key ] ) : '';
			switch ( $field[1] ) {
				case 'url':
					$clean[ $key ] = esc_url_raw( $value );
					break;
				case 'email':
					$clean[ $key ] = sanitize_email( $value );
					break;
				case 'textarea':
					$clean[ $key ] = sanitize_textarea_field( $value );
					break;
				default:
					$clean[ $key ] = sanitize_text_field( $value );
			}
		}
	}
	return $clean;
}

function inpo_core_settings_page() {
	if ( ! current_user_can( 'edit_pages' ) ) {
		inpo_core_help_page();
		return;
	}
	$saved    = get_option( INPO_CORE_OPTION, array() );
	$defaults = inpo_core_default_settings();
	echo '<div class="wrap"><h1>' . esc_html__( 'INPO settings', 'inpo-core' ) . '</h1><p>' . esc_html__( 'Shown in the site footer, on the contact page and under the donors. Empty fields use the defaults shown in grey (the facts from inpo-ks.org). Text here appears on the public site, so write it in Albanian.', 'inpo-core' ) . '</p>';
	echo '<form method="post" action="options.php">';
	settings_fields( 'inpo_core' );
	foreach ( inpo_core_setting_fields() as $section => $fields ) {
		echo '<h2>' . esc_html( $section ) . '</h2><table class="form-table" role="presentation">';
		foreach ( $fields as $key => $field ) {
			$id    = 'inpo-' . $key;
			$name  = INPO_CORE_OPTION . '[' . $key . ']';
			$value = isset( $saved[ $key ] ) ? $saved[ $key ] : '';
			$ph    = isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';
			echo '<tr><th scope="row"><label for="' . esc_attr( $id ) . '">' . esc_html( $field[0] ) . '</label></th><td>';
			if ( 'textarea' === $field[1] ) {
				echo '<textarea class="large-text" rows="3" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" placeholder="' . esc_attr( $ph ) . '">' . esc_textarea( $value ) . '</textarea>';
			} else {
				echo '<input class="regular-text" type="' . esc_attr( 'email' === $field[1] ? 'email' : ( 'url' === $field[1] ? 'url' : 'text' ) ) . '" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" placeholder="' . esc_attr( $ph ) . '">';
			}
			echo '</td></tr>';
		}
		echo '</table>';
	}
	submit_button( __( 'Save settings', 'inpo-core' ) );
	echo '</form></div>';
}

/* ---------- Meta boxes ---------- */

/* ---------- Simple publishing screens ---------- */

/**
 * Structured content (publications, projects, documents, data, team, donors) is edited on the simple
 * classic screen: the title, then INPO's fields in the main column, then the text. News and pages keep
 * the block editor.
 */
function inpo_core_form_types() {
	return array( INPO_CORE_PUB, INPO_CORE_PROJECT, INPO_CORE_DOC, INPO_CORE_DATASET, INPO_CORE_MEMBER, INPO_CORE_DONOR );
}
add_filter(
	'use_block_editor_for_post_type',
	function ( $use, $type ) {
		return in_array( $type, inpo_core_form_types(), true ) ? false : $use;
	},
	10,
	2
);
// INPO's fields right under the title, before the text editor.
add_action(
	'edit_form_after_title',
	function ( $post ) {
		do_meta_boxes( get_current_screen(), 'inpo_main', $post );
	}
);
add_action(
	'admin_head',
	function () {
		$screen = get_current_screen();
		if ( ! $screen || 'post' !== $screen->base || ! in_array( $screen->post_type, inpo_core_form_types(), true ) ) {
			return;
		}
		echo '<style>
			#inpo_main-sortables{margin:16px 0 20px}
			#inpo_main-sortables .postbox{border-radius:8px}
			#inpo_main-sortables .postbox-header h2{font-size:15px}
			.inpo-form-intro{font-size:14px;margin:4px 0 12px}
			.inpo-step{border-top:1px solid #f0f0f1;padding:14px 0 6px}
			.inpo-step h3{display:flex;align-items:center;gap:10px;margin:0 0 6px;font-size:15px}
			.inpo-step h3 span{display:inline-flex;align-items:center;justify-content:center;width:26px;height:26px;border-radius:999px;background:#3D3B8E;color:#fff;font-size:13px}
			.inpo-step h3 em{font-weight:400;color:#646970;font-size:13px}
			.inpo-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:0 24px}
			.inpo-more summary{cursor:pointer;font-weight:600;padding:4px 0}
			#inpo-pick-pdf,#inpo-slides-pick{min-height:36px}
		</style>';
	}
);

add_action( 'add_meta_boxes', 'inpo_core_meta_boxes' );
function inpo_core_meta_boxes() {
	add_meta_box( 'inpo-post', __( 'INPO: news details', 'inpo-core' ), 'inpo_core_post_box', 'post', 'side' );
	add_meta_box( 'inpo-pub', __( 'Publication: file, images and details', 'inpo-core' ), 'inpo_core_pub_box', INPO_CORE_PUB, 'inpo_main', 'high' );
	add_meta_box( 'inpo-page', __( 'INPO: page head', 'inpo-core' ), 'inpo_core_page_box', 'page', 'side' );
	add_meta_box( 'inpo-donor', __( 'Donor link', 'inpo-core' ), 'inpo_core_donor_box', INPO_CORE_DONOR, 'inpo_main', 'high' );
	add_meta_box( 'inpo-member', __( 'Role, email and group', 'inpo-core' ), 'inpo_core_member_box', INPO_CORE_MEMBER, 'inpo_main', 'high' );
	add_meta_box( 'inpo-project', __( 'INPO: project details', 'inpo-core' ), 'inpo_core_project_box', INPO_CORE_PROJECT, 'inpo_main', 'high' );
	add_meta_box( 'inpo-doc', __( 'Document file and year', 'inpo-core' ), 'inpo_core_doc_box', INPO_CORE_DOC, 'inpo_main', 'high' );
	add_meta_box( 'inpo-dataset', __( 'Data and chart', 'inpo-core' ), 'inpo_core_dataset_box', INPO_CORE_DATASET, 'inpo_main', 'high' );
	add_meta_box( 'inpo-message', __( 'Sender', 'inpo-core' ), 'inpo_core_message_box', INPO_CORE_MESSAGE, 'side' );
}

function inpo_core_field( $post, $key, $label, $type = 'text', $help = '', $options = array() ) {
	$value = get_post_meta( $post->ID, $key, true );
	$id    = 'inpo-' . $key;
	echo '<p>';
	if ( 'checkbox' === $type ) {
		echo '<label><input type="checkbox" name="' . esc_attr( $key ) . '" value="1"' . checked( (bool) $value, true, false ) . '> ' . esc_html( $label ) . '</label>';
	} else {
		echo '<label for="' . esc_attr( $id ) . '"><strong>' . esc_html( $label ) . '</strong></label><br>';
		if ( 'textarea' === $type ) {
			echo '<textarea class="widefat" rows="' . ( isset( $options['rows'] ) ? (int) $options['rows'] : 4 ) . '" id="' . esc_attr( $id ) . '" name="' . esc_attr( $key ) . '"' . ( ! empty( $options['mono'] ) ? ' style="font-family:monospace"' : '' ) . '>' . esc_textarea( $value ) . '</textarea>';
		} elseif ( 'select' === $type ) {
			echo '<select class="widefat" id="' . esc_attr( $id ) . '" name="' . esc_attr( $key ) . '">';
			foreach ( $options as $k => $l ) {
				echo '<option value="' . esc_attr( $k ) . '"' . selected( (string) $value, (string) $k, false ) . '>' . esc_html( $l ) . '</option>';
			}
			echo '</select>';
		} else {
			echo '<input class="widefat" type="' . esc_attr( $type ) . '" id="' . esc_attr( $id ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '">';
		}
	}
	if ( $help ) {
		echo '<br><span class="description">' . esc_html( $help ) . '</span>';
	}
	echo '</p>';
}

function inpo_core_post_box( $post ) {
	wp_nonce_field( 'inpo_meta', 'inpo_meta_nonce' );
	$pubs    = get_posts( array( 'post_type' => INPO_CORE_PUB, 'posts_per_page' => 200, 'orderby' => 'title', 'order' => 'ASC' ) );
	$options = array( '' => __( 'None', 'inpo-core' ) );
	foreach ( $pubs as $p ) {
		$options[ $p->ID ] = wp_strip_all_tags( get_the_title( $p ) );
	}
	inpo_core_field( $post, 'inpo_short_title', __( 'Short title', 'inpo-core' ), 'text', __( 'Used on the large card on the home page.', 'inpo-core' ) );
	inpo_core_field( $post, 'inpo_date_label', __( 'Displayed date', 'inpo-core' ), 'text', __( 'Empty = the publish date. E.g. "Maj 2025" when the exact day is unknown.', 'inpo-core' ) );
	inpo_core_field( $post, 'inpo_publication', __( 'Related publication', 'inpo-core' ), 'select', __( 'Shown in the article sidebar.', 'inpo-core' ), $options );
	inpo_core_field( $post, 'inpo_todo', __( 'Show the "full text imported during migration" note', 'inpo-core' ), 'checkbox' );
	inpo_core_field( $post, 'inpo_impact', __( 'Impact example: an institution acted after INPO (shown under Projects > Results and impact)', 'inpo-core' ), 'checkbox' );
	echo '<p class="description">' . esc_html__( 'Programme: when left empty, the programme of the related publication is used.', 'inpo-core' ) . '</p>';
}

function inpo_core_pub_box( $post ) {
	wp_nonce_field( 'inpo_meta', 'inpo_meta_nonce' );
	echo '<div class="inpo-form">';
	echo '<p class="inpo-form-intro">' . esc_html__( 'Fill in the steps below, choose the category, programme and thematic field in the boxes on the right, then press Publish.', 'inpo-core' ) . '</p>';

	echo '<section class="inpo-step"><h3><span>1</span>' . esc_html__( 'The PDF file', 'inpo-core' ) . '</h3>';
	echo '<p class="description">' . esc_html__( 'The report as a PDF. Visitors read it online or download it. Leave empty for an infographic made of images.', 'inpo-core' ) . '</p>';
	inpo_core_field( $post, 'inpo_pdf', __( 'PDF file', 'inpo-core' ), 'url', __( 'Empty = the newest PDF attached to this publication in Media.', 'inpo-core' ) );
	echo '<p><button type="button" class="button button-primary" id="inpo-pick-pdf">' . esc_html__( 'Choose PDF from Media', 'inpo-core' ) . '</button></p></section>';

	echo '<section class="inpo-step"><h3><span>2</span>' . esc_html__( 'Infographic images', 'inpo-core' ) . ' <em>' . esc_html__( 'only for infographics', 'inpo-core' ) . '</em></h3>';
	inpo_core_slides_field( $post, false );
	echo '</section>';

	echo '<section class="inpo-step"><h3><span>3</span>' . esc_html__( 'Details shown on the page', 'inpo-core' ) . '</h3><div class="inpo-grid">';
	inpo_core_field( $post, 'inpo_authors', __( 'Author or research team', 'inpo-core' ), 'text', __( 'E.g. the names of the researchers, or "Ekipi hulumtues i INPO-s".', 'inpo-core' ) );
	inpo_core_field( $post, 'inpo_year', __( 'Year', 'inpo-core' ), 'text', __( 'Empty = the publish date is shown.', 'inpo-core' ) );
	inpo_core_field( $post, 'inpo_partners', __( 'Partners', 'inpo-core' ), 'text', __( 'E.g. IKD, Lëvizja FOL.', 'inpo-core' ) );
	inpo_core_field( $post, 'inpo_donor', __( 'Supported by', 'inpo-core' ), 'text', __( 'E.g. Rockefeller Brothers Fund.', 'inpo-core' ) );
	echo '</div><p class="description">' . esc_html__( 'The short summary under the title is the text in the editor above (or the Excerpt).', 'inpo-core' ) . '</p></section>';

	echo '<details class="inpo-step inpo-more"><summary>' . esc_html__( 'More options: the look of the cover', 'inpo-core' ) . '</summary><div class="inpo-grid">';
	inpo_core_field(
		$post,
		'inpo_cover',
		__( 'Cover style', 'inpo-core' ),
		'select',
		__( 'Used when there is no PDF preview or infographic image. Automatic rotates the style.', 'inpo-core' ),
		array(
			''      => __( 'Automatic', 'inpo-core' ),
			'rings' => __( 'Rings (indigo)', 'inpo-core' ),
			'bars'  => __( 'Bars', 'inpo-core' ),
			'dots'  => __( 'Dots (dark)', 'inpo-core' ),
			'word'  => __( 'Word', 'inpo-core' ),
		)
	);
	inpo_core_field( $post, 'inpo_short', __( 'Cover word', 'inpo-core' ), 'text', __( 'E.g. OShP. Used on the "word" cover style.', 'inpo-core' ) );
	echo '</div></details>';

	echo '<details class="inpo-step inpo-more"' . ( get_post_meta( $post->ID, 'inpo_csv', true ) ? ' open' : '' ) . '><summary>' . esc_html__( 'More options: figures and interactive charts', 'inpo-core' ) . '</summary>';
	echo '<p class="description">' . esc_html__( 'Optional. An infographic\'s numbers, shown as key figures and interactive charts (with a table and CSV download).', 'inpo-core' ) . '</p>';
	inpo_core_field( $post, 'inpo_figures', __( 'Key figures', 'inpo-core' ), 'textarea', __( 'One per line: number | label. E.g. 192 | kontrata me procedurë të negociuar.', 'inpo-core' ), array( 'rows' => 4 ) );
	inpo_core_field( $post, 'inpo_csv', __( 'Charts (CSV)', 'inpo-core' ), 'textarea', __( 'One chart per block, blocks separated by an empty line. First line of a block: # Title | bar, line or table | unit. Then the column names, then one row per category.', 'inpo-core' ), array( 'rows' => 10, 'mono' => true ) );
	inpo_core_field( $post, 'inpo_source', __( 'Source of the data', 'inpo-core' ), 'text' );
	echo '</details></div>';
	inpo_core_pdf_picker();
}

function inpo_core_page_box( $post ) {
	wp_nonce_field( 'inpo_meta', 'inpo_meta_nonce' );
	inpo_core_field( $post, 'inpo_kicker', __( 'Kicker above the title', 'inpo-core' ), 'text', __( 'The small indigo text above the page title. The intro text comes from the Excerpt.', 'inpo-core' ) );
}

function inpo_core_donor_box( $post ) {
	wp_nonce_field( 'inpo_meta', 'inpo_meta_nonce' );
	inpo_core_field( $post, 'inpo_url', __( 'Donor website', 'inpo-core' ), 'url', __( 'Optional. Set the logo as the featured image; without a logo the name is shown.', 'inpo-core' ) );
}

function inpo_core_member_box( $post ) {
	wp_nonce_field( 'inpo_meta', 'inpo_meta_nonce' );
	inpo_core_field( $post, 'inpo_role', __( 'Role (Albanian)', 'inpo-core' ), 'text', __( 'E.g. Drejtore Ekzekutive. The photo is the featured image; "Order" sets the position.', 'inpo-core' ) );
	inpo_core_field( $post, 'inpo_email', __( 'Email', 'inpo-core' ), 'email', __( 'Optional. Shown as a link on the About page.', 'inpo-core' ) );
	inpo_core_field( $post, 'inpo_group', __( 'Group', 'inpo-core' ), 'select', __( 'The About page shows the team and the board separately.', 'inpo-core' ), array( '' => __( 'Team', 'inpo-core' ), 'bordi' => __( 'Board', 'inpo-core' ) ) );
}

function inpo_core_project_box( $post ) {
	wp_nonce_field( 'inpo_meta', 'inpo_meta_nonce' );
	echo '<p class="description">' . esc_html__( 'Public text, in Albanian. The title is the project name; the Excerpt is a short summary; the main text describes the project. Set the programme in the sidebar.', 'inpo-core' ) . '</p>';
	inpo_core_field( $post, 'inpo_status', __( 'Status', 'inpo-core' ), 'select', '', array( '' => __( 'Active', 'inpo-core' ), 'perfunduar' => __( 'Completed', 'inpo-core' ) ) );
	inpo_core_field( $post, 'inpo_period', __( 'Implementation period', 'inpo-core' ), 'text', __( 'E.g. Janar 2024 - Dhjetor 2025.', 'inpo-core' ) );
	inpo_core_field( $post, 'inpo_donor_text', __( 'Donor', 'inpo-core' ), 'text' );
	inpo_core_field( $post, 'inpo_partners', __( 'Partners', 'inpo-core' ), 'text' );
	inpo_core_field( $post, 'inpo_goals', __( 'Objectives', 'inpo-core' ), 'textarea', __( 'One per line.', 'inpo-core' ) );
	inpo_core_field( $post, 'inpo_results', __( 'Measurable results', 'inpo-core' ), 'textarea', __( 'One per line. "160 | aktivitete prokurimi të monitoruara" shows as a big figure; a line without "|" shows as a sentence.', 'inpo-core' ) );
	$pubs    = get_posts( array( 'post_type' => INPO_CORE_PUB, 'posts_per_page' => 300, 'orderby' => 'title', 'order' => 'ASC' ) );
	$options = array( '' => __( 'None', 'inpo-core' ) );
	foreach ( $pubs as $p ) {
		$options[ $p->ID ] = wp_strip_all_tags( get_the_title( $p ) );
	}
	inpo_core_field( $post, 'inpo_publication', __( 'Publication of the project', 'inpo-core' ), 'select', '', $options );
}

function inpo_core_doc_box( $post ) {
	wp_nonce_field( 'inpo_meta', 'inpo_meta_nonce' );
	echo '<p class="description">' . esc_html__( 'Set the category in the sidebar (Raportet vjetore, Raportet financiare, Raportet e auditimit...). The Transparency page lists documents by category and year.', 'inpo-core' ) . '</p>';
	inpo_core_field( $post, 'inpo_year', __( 'Year', 'inpo-core' ), 'text', __( 'The year the document covers, e.g. 2024.', 'inpo-core' ) );
	inpo_core_field( $post, 'inpo_pdf', __( 'PDF file (URL)', 'inpo-core' ), 'url' );
	echo '<p><button type="button" class="button" id="inpo-pick-pdf">' . esc_html__( 'Choose from Media', 'inpo-core' ) . '</button></p>';
	inpo_core_pdf_picker();
}

function inpo_core_dataset_box( $post ) {
	wp_nonce_field( 'inpo_meta', 'inpo_meta_nonce' );
	echo '<p class="description">' . esc_html__( 'Paste the data from a spreadsheet: first line = column names, first column = categories (years, quarters, municipalities), other columns = numbers. Comma, semicolon or tab separated. The Excerpt is shown under the title.', 'inpo-core' ) . '</p>';
	inpo_core_field( $post, 'inpo_csv', __( 'Data (CSV)', 'inpo-core' ), 'textarea', '', array( 'rows' => 10, 'mono' => true ) );
	inpo_core_field( $post, 'inpo_chart', __( 'Chart', 'inpo-core' ), 'select', '', array( 'bar' => __( 'Bars', 'inpo-core' ), 'line' => __( 'Lines', 'inpo-core' ), 'table' => __( 'Table only', 'inpo-core' ) ) );
	inpo_core_field( $post, 'inpo_unit', __( 'Unit', 'inpo-core' ), 'text', __( 'E.g. kontrata, tenderë, euro. Read out with each value.', 'inpo-core' ) );
	inpo_core_field( $post, 'inpo_source', __( 'Source', 'inpo-core' ), 'text', __( 'E.g. e-Prokurimi, KRPP; monitorimi i INPO-s.', 'inpo-core' ) );
}

/**
 * Infographic images: chosen from Media in the order they are shown; stored as attachment IDs.
 */
function inpo_core_slides_field( $post, $heading = true ) {
	$ids    = array_filter( array_map( 'absint', explode( ',', (string) get_post_meta( $post->ID, 'inpo_slides', true ) ) ) );
	$thumbs = '';
	foreach ( $ids as $id ) {
		$thumbs .= wp_get_attachment_image( $id, 'thumbnail', false, array( 'style' => 'width:88px;height:88px;object-fit:cover;border-radius:8px;border:1px solid #dcdcde' ) );
	}
	if ( $heading ) {
		echo '<h4 style="margin:24px 0 4px">' . esc_html__( 'Infographic images', 'inpo-core' ) . '</h4>';
	}
	echo '<p class="description">' . esc_html__( 'For an infographic made of images (01/06, 02/06...): choose them in order. The page shows them as a row to swipe with a full-screen view; the first image is the cover.', 'inpo-core' ) . '</p>';
	echo '<input type="hidden" name="inpo_slides" id="inpo-slides" value="' . esc_attr( implode( ',', $ids ) ) . '">';
	echo '<div id="inpo-slides-preview" style="display:flex;flex-wrap:wrap;gap:6px;margin:8px 0">' . $thumbs . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
	echo '<p><button type="button" class="button button-primary" id="inpo-slides-pick">' . esc_html__( 'Choose images from Media', 'inpo-core' ) . '</button> ';
	echo '<button type="button" class="button-link button-link-delete" id="inpo-slides-clear"' . ( $ids ? '' : ' hidden' ) . '>' . esc_html__( 'Remove all', 'inpo-core' ) . '</button></p>';
	?>
	<script>
	(function () {
		var pick = document.getElementById('inpo-slides-pick'), clear = document.getElementById('inpo-slides-clear');
		var field = document.getElementById('inpo-slides'), preview = document.getElementById('inpo-slides-preview');
		if (!pick) return;
		// wp.media loads after the meta boxes in the block editor: check on click, not on load.
		pick.addEventListener('click', function () {
			if (!window.wp || !wp.media) return;
			var frame = wp.media({ title: <?php echo wp_json_encode( __( 'Infographic images, in order', 'inpo-core' ) ); ?>, library: { type: 'image' }, multiple: 'add', button: { text: <?php echo wp_json_encode( __( 'Use these images', 'inpo-core' ) ); ?> } });
			frame.on('open', function () {
				frame.content.mode('browse');
				var sel = frame.state().get('selection');
				field.value.split(',').filter(Boolean).forEach(function (id) { var a = wp.media.attachment(id); a.fetch(); sel.add(a); });
			});
			frame.on('select', function () {
				var list = frame.state().get('selection').toJSON();
				field.value = list.map(function (m) { return m.id; }).join(',');
				preview.innerHTML = '';
				list.forEach(function (m) {
					var img = document.createElement('img');
					img.src = (m.sizes && m.sizes.thumbnail ? m.sizes.thumbnail.url : m.url);
					img.style.cssText = 'width:88px;height:88px;object-fit:cover;border-radius:8px;border:1px solid #dcdcde';
					preview.appendChild(img);
				});
				clear.hidden = !list.length;
			});
			frame.open();
		});
		clear.addEventListener('click', function () { field.value = ''; preview.innerHTML = ''; clear.hidden = true; });
	})();
	</script>
	<?php
}

/**
 * "Choose from Media" for a PDF field (publications and documents).
 */
function inpo_core_pdf_picker() {
	?>
	<script>
	(function () {
		var b = document.getElementById('inpo-pick-pdf');
		if (!b) return;
		b.addEventListener('click', function () {
			if (!window.wp || !wp.media) return;
			var f = wp.media({ title: 'Choose PDF', library: { type: 'application/pdf' }, multiple: false });
			f.on('select', function () { document.getElementById('inpo-inpo_pdf').value = f.state().get('selection').first().get('url'); });
			f.open();
		});
	})();
	</script>
	<?php
}

function inpo_core_message_box( $post ) {
	$email = get_post_meta( $post->ID, 'inpo_email', true );
	echo '<p><strong>' . esc_html( get_post_meta( $post->ID, 'inpo_name', true ) ) . '</strong><br>';
	echo '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a><br>';
	echo esc_html( get_post_meta( $post->ID, 'inpo_topic', true ) ) . '</p>';
}

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( in_array( $hook, array( 'post.php', 'post-new.php' ), true ) && in_array( get_post_type(), array( INPO_CORE_PUB, INPO_CORE_DOC ), true ) ) {
		wp_enqueue_media();
	}
} );

add_action( 'save_post', 'inpo_core_save_meta', 10, 2 );
function inpo_core_save_meta( $post_id, $post ) {
	if ( ! isset( $_POST['inpo_meta_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['inpo_meta_nonce'] ), 'inpo_meta' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$keys = array(
		'post'          => array( 'inpo_short_title' => 'text', 'inpo_date_label' => 'text', 'inpo_publication' => 'int', 'inpo_todo' => 'bool', 'inpo_impact' => 'bool' ),
		INPO_CORE_PUB   => array( 'inpo_pdf' => 'url', 'inpo_year' => 'text', 'inpo_partners' => 'text', 'inpo_donor' => 'text', 'inpo_short' => 'text', 'inpo_cover' => 'text', 'inpo_authors' => 'text', 'inpo_figures' => 'textarea', 'inpo_csv' => 'textarea', 'inpo_source' => 'text', 'inpo_slides' => 'ids' ),
		INPO_CORE_PROJECT => array( 'inpo_status' => 'text', 'inpo_period' => 'text', 'inpo_donor_text' => 'text', 'inpo_partners' => 'text', 'inpo_goals' => 'textarea', 'inpo_results' => 'textarea', 'inpo_publication' => 'int' ),
		INPO_CORE_DOC     => array( 'inpo_year' => 'text', 'inpo_pdf' => 'url' ),
		INPO_CORE_DATASET => array( 'inpo_csv' => 'textarea', 'inpo_chart' => 'text', 'inpo_unit' => 'text', 'inpo_source' => 'text' ),
		'page'          => array( 'inpo_kicker' => 'text' ),
		INPO_CORE_DONOR  => array( 'inpo_url' => 'url' ),
		INPO_CORE_MEMBER => array( 'inpo_role' => 'text', 'inpo_email' => 'email', 'inpo_group' => 'text' ),
	);
	if ( empty( $keys[ $post->post_type ] ) ) {
		return;
	}
	foreach ( $keys[ $post->post_type ] as $key => $type ) {
		$raw = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		if ( 'bool' === $type ) {
			$value = $raw ? true : '';
		} elseif ( 'int' === $type ) {
			$value = absint( $raw );
		} elseif ( 'url' === $type ) {
			$value = esc_url_raw( $raw );
		} elseif ( 'email' === $type ) {
			$value = sanitize_email( $raw );
		} elseif ( 'ids' === $type ) {
			$value = implode( ',', array_filter( array_map( 'absint', explode( ',', (string) $raw ) ) ) );
		} elseif ( 'textarea' === $type ) {
			$value = trim( sanitize_textarea_field( $raw ) );
		} else {
			$value = sanitize_text_field( $raw );
		}
		if ( '' === $value || 0 === $value ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, $value );
		}
	}
}

/* ---------- Taxonomy fields ---------- */

add_action( INPO_CORE_PROGRAM . '_edit_form_fields', 'inpo_core_program_fields' );
add_action( INPO_CORE_PROGRAM . '_add_form_fields', 'inpo_core_program_fields' );
function inpo_core_program_fields( $term ) {
	$is_edit = is_object( $term );
	$get     = function ( $key ) use ( $term, $is_edit ) {
		return $is_edit ? get_term_meta( $term->term_id, $key, true ) : '';
	};
	$ways    = (array) $get( 'inpo_ways' );
	$open    = $is_edit ? '<tr class="form-field"><th scope="row">%s</th><td>' : '<div class="form-field"><label>%s</label>';
	$close   = $is_edit ? '</td></tr>' : '</div>';
	wp_nonce_field( 'inpo_term', 'inpo_term_nonce' );

	printf( $open, esc_html__( 'Full name', 'inpo-core' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
	echo '<input type="text" name="inpo_full" value="' . esc_attr( $get( 'inpo_full' ) ) . '"><p class="description">' . esc_html__( 'E.g. Programi për Qeverisje të Mirë. Used in the browser title.', 'inpo-core' ) . '</p>' . $close; // phpcs:ignore WordPress.Security.EscapeOutput

	printf( $open, esc_html__( 'Icon', 'inpo-core' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
	echo '<select name="inpo_icon">';
	foreach ( inpo_core_program_icons() as $k => $l ) {
		echo '<option value="' . esc_attr( $k ) . '"' . selected( $get( 'inpo_icon' ), $k, false ) . '>' . esc_html( $l ) . '</option>';
	}
	echo '</select>' . $close; // phpcs:ignore WordPress.Security.EscapeOutput

	printf( $open, esc_html__( 'Objectives', 'inpo-core' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
	echo '<textarea name="inpo_objectives" rows="4">' . esc_textarea( implode( "\n", inpo_core_lines( $is_edit ? $term->term_id : 0, 'inpo_objectives', true ) ) ) . '</textarea><p class="description">' . esc_html__( 'One per line. Shown first on the programme page.', 'inpo-core' ) . '</p>' . $close; // phpcs:ignore WordPress.Security.EscapeOutput

	printf( $open, esc_html__( 'Areas of intervention (cards)', 'inpo-core' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
	for ( $i = 0; $i < 3; $i++ ) {
		$w = isset( $ways[ $i ] ) ? (array) $ways[ $i ] : array( '', '' );
		echo '<p><input type="text" name="inpo_ways[' . $i . '][0]" placeholder="' . esc_attr__( 'Title', 'inpo-core' ) . '" value="' . esc_attr( isset( $w[0] ) ? $w[0] : '' ) . '"></p>'; // phpcs:ignore WordPress.Security.EscapeOutput
		echo '<p><textarea rows="2" name="inpo_ways[' . $i . '][1]" placeholder="' . esc_attr__( 'Description', 'inpo-core' ) . '">' . esc_textarea( isset( $w[1] ) ? $w[1] : '' ) . '</textarea></p>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
	echo $close; // phpcs:ignore WordPress.Security.EscapeOutput

	printf( $open, esc_html__( 'Main activities', 'inpo-core' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
	echo '<textarea name="inpo_activities" rows="5">' . esc_textarea( implode( "\n", (array) $get( 'inpo_activities' ) ) ) . '</textarea><p class="description">' . esc_html__( 'One activity per line. Shown as a numbered list on the programme page.', 'inpo-core' ) . '</p>' . $close; // phpcs:ignore WordPress.Security.EscapeOutput

	printf( $open, esc_html__( 'Results', 'inpo-core' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
	echo '<textarea name="inpo_results" rows="4">' . esc_textarea( implode( "\n", inpo_core_lines( $is_edit ? $term->term_id : 0, 'inpo_results', true ) ) ) . '</textarea><p class="description">' . esc_html__( 'One per line: number | label, e.g. 160 | aktivitete prokurimi të monitoruara. Shown with the counts of the programme\'s publications, projects and news.', 'inpo-core' ) . '</p>' . $close; // phpcs:ignore WordPress.Security.EscapeOutput

	printf( $open, esc_html__( 'Order', 'inpo-core' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
	echo '<input type="number" name="inpo_order" value="' . esc_attr( $get( 'inpo_order' ) ) . '" style="width:6em">' . $close; // phpcs:ignore WordPress.Security.EscapeOutput

	printf( $open, esc_html__( 'Latest news', 'inpo-core' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
	echo '<label><input type="checkbox" name="inpo_show_news" value="1"' . checked( (bool) $get( 'inpo_show_news' ), true, false ) . '> ' . esc_html__( 'When the programme has no news of its own, show the three latest news', 'inpo-core' ) . '</label>' . $close; // phpcs:ignore WordPress.Security.EscapeOutput
}

add_action( INPO_CORE_PUB_TYPE . '_edit_form_fields', 'inpo_core_type_fields' );
add_action( INPO_CORE_PUB_TYPE . '_add_form_fields', 'inpo_core_type_fields' );
function inpo_core_type_fields( $term ) {
	$is_edit = is_object( $term );
	$singular = $is_edit ? get_term_meta( $term->term_id, 'inpo_singular', true ) : '';
	$order    = $is_edit ? get_term_meta( $term->term_id, 'inpo_order', true ) : '';
	wp_nonce_field( 'inpo_term', 'inpo_term_nonce' );
	if ( $is_edit ) {
		echo '<tr class="form-field"><th scope="row">' . esc_html__( 'Singular label', 'inpo-core' ) . '</th><td><input type="text" name="inpo_singular" value="' . esc_attr( $singular ) . '"><p class="description">' . esc_html__( 'The label on one publication, e.g. Analizë. The name (plural) labels the filter.', 'inpo-core' ) . '</p></td></tr>';
		echo '<tr class="form-field"><th scope="row">' . esc_html__( 'Order', 'inpo-core' ) . '</th><td><input type="number" name="inpo_order" value="' . esc_attr( $order ) . '" style="width:6em"></td></tr>';
		echo '<tr class="form-field"><th scope="row">' . esc_html__( 'Filter label', 'inpo-core' ) . '</th><td><input type="text" name="inpo_plural" value="' . esc_attr( get_term_meta( $term->term_id, 'inpo_plural', true ) ) . '"><p class="description">' . esc_html__( 'The label on the library filter and in the menu, e.g. Raporte të monitorimit. Empty = the name. Changing this does not change the old site.', 'inpo-core' ) . '</p></td></tr>';
		echo '<tr class="form-field"><th scope="row">' . esc_html__( 'Library', 'inpo-core' ) . '</th><td><label><input type="checkbox" name="inpo_hidden" value="1"' . checked( '1', get_term_meta( $term->term_id, 'inpo_hidden', true ), false ) . '> ' . esc_html__( 'Hide from the publication library (e.g. reports about INPO, which are on the Transparency page)', 'inpo-core' ) . '</label></td></tr>';
	} else {
		echo '<div class="form-field"><label>' . esc_html__( 'Singular label', 'inpo-core' ) . '</label><input type="text" name="inpo_singular"><p>' . esc_html__( 'The label on one publication, e.g. Analizë.', 'inpo-core' ) . '</p></div>';
		echo '<div class="form-field"><label>' . esc_html__( 'Order', 'inpo-core' ) . '</label><input type="number" name="inpo_order" style="width:6em"></div>';
	}
}

add_action( 'created_' . INPO_CORE_PROGRAM, 'inpo_core_save_term' );
add_action( 'edited_' . INPO_CORE_PROGRAM, 'inpo_core_save_term' );
add_action( 'created_' . INPO_CORE_PUB_TYPE, 'inpo_core_save_term' );
add_action( 'edited_' . INPO_CORE_PUB_TYPE, 'inpo_core_save_term' );
function inpo_core_save_term( $term_id ) {
	if ( ! isset( $_POST['inpo_term_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['inpo_term_nonce'] ), 'inpo_term' ) || ! current_user_can( 'manage_categories' ) ) {
		return;
	}
	// phpcs:disable WordPress.Security.ValidatedSanitizedInput
	foreach ( array( 'inpo_full', 'inpo_icon', 'inpo_singular', 'inpo_plural' ) as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			update_term_meta( $term_id, $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
		}
	}
	if ( isset( $_POST['inpo_order'] ) ) {
		update_term_meta( $term_id, 'inpo_order', (int) $_POST['inpo_order'] );
	}
	if ( isset( $_POST['inpo_ways'] ) && is_array( $_POST['inpo_ways'] ) ) {
		$ways = array();
		foreach ( wp_unslash( $_POST['inpo_ways'] ) as $w ) {
			$title = sanitize_text_field( isset( $w[0] ) ? $w[0] : '' );
			if ( '' !== $title ) {
				$ways[] = array( $title, sanitize_textarea_field( isset( $w[1] ) ? $w[1] : '' ) );
			}
		}
		update_term_meta( $term_id, 'inpo_ways', $ways );
	}
	if ( isset( $_POST['inpo_icon'] ) ) {
		update_term_meta( $term_id, 'inpo_show_news', ! empty( $_POST['inpo_show_news'] ) );
	}
	if ( isset( $_POST['inpo_plural'] ) ) {
		update_term_meta( $term_id, 'inpo_hidden', empty( $_POST['inpo_hidden'] ) ? '' : '1' );
	}
	foreach ( array( 'inpo_objectives', 'inpo_results' ) as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			update_term_meta( $term_id, $key, trim( sanitize_textarea_field( inpo_core_str( wp_unslash( $_POST[ $key ] ) ) ) ) );
		}
	}
	if ( isset( $_POST['inpo_activities'] ) ) {
		$lines = array_filter( array_map( 'sanitize_text_field', preg_split( '/\R/', inpo_core_str( wp_unslash( $_POST['inpo_activities'] ) ) ) ) );
		update_term_meta( $term_id, 'inpo_activities', array_values( $lines ) );
	}
	// phpcs:enable
}

/* ---------- Category: use as a news filter ---------- */

add_action( 'category_edit_form_fields', function ( $term ) {
	wp_nonce_field( 'inpo_term', 'inpo_term_nonce' );
	echo '<tr class="form-field"><th scope="row">' . esc_html__( 'News filter', 'inpo-core' ) . '</th><td><label><input type="checkbox" name="inpo_chip" value="1"' . checked( '1', get_term_meta( $term->term_id, 'inpo_chip', true ), false ) . '> ' . esc_html__( 'Show as a filter on the news page and as the label on news cards', 'inpo-core' ) . '</label>'
		. '<p class="description">' . esc_html__( 'Once at least one category is ticked, only ticked categories are shown as filters. Other categories stay, they are just not offered as filters.', 'inpo-core' ) . '</p></td></tr>';
	echo '<tr class="form-field"><th scope="row">' . esc_html__( 'Card label', 'inpo-core' ) . '</th><td><input type="text" name="inpo_singular" value="' . esc_attr( get_term_meta( $term->term_id, 'inpo_singular', true ) ) . '"><p class="description">' . esc_html__( 'Singular, shown on news cards, e.g. Reagim publik. Empty = the name.', 'inpo-core' ) . '</p></td></tr>';
	echo '<tr class="form-field"><th scope="row">' . esc_html__( 'Filter order', 'inpo-core' ) . '</th><td><input type="number" name="inpo_order" value="' . esc_attr( get_term_meta( $term->term_id, 'inpo_order', true ) ) . '" style="width:6em"></td></tr>';
} );
add_action( 'edited_category', function ( $term_id ) {
	if ( ! isset( $_POST['inpo_term_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['inpo_term_nonce'] ), 'inpo_term' ) || ! current_user_can( 'manage_categories' ) ) {
		return;
	}
	if ( empty( $_POST['inpo_chip'] ) ) {
		delete_term_meta( $term_id, 'inpo_chip' );
	} else {
		update_term_meta( $term_id, 'inpo_chip', '1' );
	}
	update_term_meta( $term_id, 'inpo_singular', sanitize_text_field( inpo_core_str( wp_unslash( isset( $_POST['inpo_singular'] ) ? $_POST['inpo_singular'] : '' ) ) ) );
	if ( isset( $_POST['inpo_order'] ) && '' !== $_POST['inpo_order'] ) {
		update_term_meta( $term_id, 'inpo_order', (int) $_POST['inpo_order'] );
	}
} );

/* ---------- Messages and subscribers lists ---------- */

add_filter( 'manage_' . INPO_CORE_MESSAGE . '_posts_columns', function ( $cols ) {
	return array( 'cb' => $cols['cb'], 'title' => __( 'Message', 'inpo-core' ), 'inpo_email' => __( 'Email', 'inpo-core' ), 'inpo_topic' => __( 'Topic', 'inpo-core' ), 'date' => __( 'Date', 'inpo-core' ) );
} );
add_filter( 'manage_' . INPO_CORE_SUBSCRIBER . '_posts_columns', function ( $cols ) {
	return array( 'cb' => $cols['cb'], 'title' => __( 'Email', 'inpo-core' ), 'date' => __( 'Date', 'inpo-core' ) );
} );
add_action( 'manage_' . INPO_CORE_MESSAGE . '_posts_custom_column', function ( $col, $id ) {
	if ( in_array( $col, array( 'inpo_email', 'inpo_topic' ), true ) ) {
		echo esc_html( get_post_meta( $id, $col, true ) );
	}
}, 10, 2 );

add_filter( 'views_edit-' . INPO_CORE_SUBSCRIBER, function ( $views ) {
	$url = wp_nonce_url( admin_url( 'admin-post.php?action=inpo_export_subscribers' ), 'inpo_export' );
	$views['inpo_export'] = '<a href="' . esc_url( $url ) . '"><strong>' . esc_html__( 'Export CSV', 'inpo-core' ) . '</strong></a>';
	return $views;
} );

add_action( 'admin_post_inpo_export_subscribers', 'inpo_core_export_subscribers' );
function inpo_core_export_subscribers() {
	if ( ! current_user_can( 'edit_others_posts' ) || ! check_admin_referer( 'inpo_export' ) ) {
		wp_die( esc_html__( 'You are not allowed to do this.', 'inpo-core' ) );
	}
	$ids = get_posts( array( 'post_type' => INPO_CORE_SUBSCRIBER, 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids' ) );
	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="inpo-subscribers-' . gmdate( 'Y-m-d' ) . '.csv"' );
	$out = fopen( 'php://output', 'w' );
	fputcsv( $out, array( 'email', 'date' ), ',', '"', '\\' );
	foreach ( $ids as $id ) {
		$email = (string) get_post_meta( $id, 'inpo_email', true );
		// Spreadsheet apps run cells that start with = + - @ as formulas.
		$email = preg_match( '/^[=+\-@]/', $email ) ? "'" . $email : $email;
		fputcsv( $out, array( $email, get_post_field( 'post_date', $id ) ), ',', '"', '\\' );
	}
	fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	exit;
}

/* ---------- Help: where to edit what ---------- */

/**
 * One screen that tells editors where each part of the site is edited. Links open the right screen.
 */
function inpo_core_help_page() {
	$link  = function ( $url, $text ) {
		return '<a href="' . esc_url( $url ) . '">' . esc_html( $text ) . '</a>';
	};
	$page  = function ( $path, $text ) use ( $link ) {
		$p = get_page_by_path( $path );
		return $p ? $link( get_edit_post_link( $p->ID, 'url' ), $text ) : esc_html( $text );
	};
	$list  = function ( $kind, $text ) use ( $link ) {
		$p = function_exists( 'inpo_core_listing_page' ) ? inpo_core_listing_page( $kind ) : null;
		return $p ? $link( get_edit_post_link( $p->ID, 'url' ), $text ) : esc_html( $text );
	};
	$front = (int) get_option( 'page_on_front' );
	$rows  = array(
		__( 'Pages', 'inpo-core' )                => array(
			array( __( 'Home page: headline, photo, key findings, "Kush jemi" photos, figures, sections', 'inpo-core' ), $front ? $link( get_edit_post_link( $front, 'url' ), __( 'Pages > Home', 'inpo-core' ) ) : __( 'Pages > Home', 'inpo-core' ), __( 'Click a section, then change it in the sidebar on the right (Settings). Photos: "Choose photo from Media".', 'inpo-core' ) ),
			array( __( 'About: history, mission, vision, values, team, documents', 'inpo-core' ), $page( 'rreth-nesh', __( 'Pages > Rreth nesh', 'inpo-core' ) ), __( 'Text blocks are edited in place. The team and board come from INPO > Team.', 'inpo-core' ) ),
			array( __( 'Contact and Transparency pages', 'inpo-core' ), $page( 'kontakti', __( 'Pages > Kontakti', 'inpo-core' ) ) . ', ' . $page( 'transparenca', __( 'Pages > Transparenca', 'inpo-core' ) ), __( 'Addresses and phone come from INPO > Settings.', 'inpo-core' ) ),
			array( __( 'Heading and intro of the lists (Publications, Projects, Data, News), and sections under them', 'inpo-core' ), $list( 'publikimet', __( 'Publications', 'inpo-core' ) ) . ', ' . $list( 'projektet', __( 'Projects', 'inpo-core' ) ) . ', ' . $list( 'te-dhenat', __( 'Data', 'inpo-core' ) ) . ', ' . $list( 'lajme', __( 'News', 'inpo-core' ) ), __( 'Pages marked "… list". Title = heading, Excerpt = intro, Kicker = small label. Blocks you add appear under the list.', 'inpo-core' ) ),
			array( __( 'A new page', 'inpo-core' ), $link( admin_url( 'post-new.php?post_type=page' ), __( 'Pages > Add New', 'inpo-core' ) ), __( 'Use the INPO blocks (category "INPO") or the patterns, then add the page to the menu.', 'inpo-core' ) ),
		),
		__( 'Content', 'inpo-core' )              => array(
			array( __( 'News, reactions, press releases', 'inpo-core' ), $link( admin_url( 'edit.php' ), __( 'Posts', 'inpo-core' ) ), __( 'Pick the section in Categories. "Impact example" in the INPO box shows the story under results.', 'inpo-core' ) ),
			array( __( 'Publications, infographics and their charts', 'inpo-core' ), $link( admin_url( 'edit.php?post_type=' . INPO_CORE_PUB ), __( 'Publications', 'inpo-core' ) ), __( 'PDF, authors, year in the Publication box; type, programme, thematic field and institution in the sidebar. An infographic: "Choose images from Media" under Infographic images (in order); optional figures and charts under "Data".', 'inpo-core' ) ),
			array( __( 'Projects', 'inpo-core' ), $link( admin_url( 'edit.php?post_type=' . INPO_CORE_PROJECT ), __( 'Projects', 'inpo-core' ) ), __( 'Status, period, donor, partners, objectives and results in the Project box.', 'inpo-core' ) ),
			array( __( 'Transparency documents (reports, audits, policies)', 'inpo-core' ), $link( admin_url( 'edit.php?post_type=' . INPO_CORE_DOC ), __( 'Documents', 'inpo-core' ) ), __( 'Category in the sidebar, year and PDF in the Document box.', 'inpo-core' ) ),
			array( __( 'Data and statistics (charts)', 'inpo-core' ), $link( admin_url( 'edit.php?post_type=' . INPO_CORE_DATASET ), __( 'Data', 'inpo-core' ) ), __( 'Paste the table from a spreadsheet into "Data (CSV)".', 'inpo-core' ) ),
			array( __( 'Programmes: description, objectives, focus areas, activities, results', 'inpo-core' ), $link( admin_url( 'edit-tags.php?taxonomy=' . INPO_CORE_PROGRAM . '&post_type=' . INPO_CORE_PUB ), __( 'Publications > Programmes', 'inpo-core' ) ), __( 'Click a programme to edit its fields.', 'inpo-core' ) ),
			array( __( 'Team and board (photos, roles, emails)', 'inpo-core' ), $link( admin_url( 'edit.php?post_type=' . INPO_CORE_MEMBER ), __( 'INPO > Team', 'inpo-core' ) ), __( 'Photo = Featured image. "Group" chooses team or board. Order = Page Attributes > Order.', 'inpo-core' ) ),
			array( __( 'Donors and partners (logos)', 'inpo-core' ), $link( admin_url( 'edit.php?post_type=' . INPO_CORE_DONOR ), __( 'INPO > Donors', 'inpo-core' ) ), __( 'Logo = Featured image.', 'inpo-core' ) ),
		),
		__( 'Whole site', 'inpo-core' )           => array(
			array( __( 'Main menu and its sub-items', 'inpo-core' ), $link( admin_url( 'nav-menus.php' ), __( 'Appearance > Menus', 'inpo-core' ) ), __( 'A short label for the bar goes in "Title Attribute".', 'inpo-core' ) ),
			array( __( 'Footer, addresses, phone, email, social links, grant note', 'inpo-core' ), $link( admin_url( 'admin.php?page=inpo-core' ), __( 'INPO > Settings', 'inpo-core' ) ), '' ),
			array( __( 'Logo and browser icon', 'inpo-core' ), $link( admin_url( 'site-editor.php' ), __( 'Appearance > Editor > Site Identity', 'inpo-core' ) ), __( 'Leave empty to use INPO\'s logo and icon that come with the theme.', 'inpo-core' ) ),
			array( __( 'Messages from the contact form', 'inpo-core' ), $link( admin_url( 'edit.php?post_type=' . INPO_CORE_MESSAGE ), __( 'INPO > Messages', 'inpo-core' ) ), '' ),
		),
	);
	echo '<div class="wrap"><h1>' . esc_html__( 'Where to edit what', 'inpo-core' ) . '</h1>';
	echo '<p>' . esc_html__( 'Everything on the website is edited here in wp-admin. No code is needed.', 'inpo-core' ) . '</p>';
	foreach ( $rows as $group => $items ) {
		echo '<h2>' . esc_html( $group ) . '</h2><table class="widefat striped" style="max-width:1100px"><thead><tr><th style="width:34%">' . esc_html__( 'To change', 'inpo-core' ) . '</th><th style="width:26%">' . esc_html__( 'Go to', 'inpo-core' ) . '</th><th>' . esc_html__( 'How', 'inpo-core' ) . '</th></tr></thead><tbody>';
		foreach ( $items as $r ) {
			echo '<tr><td>' . esc_html( $r[0] ) . '</td><td>' . wp_kses_post( $r[1] ) . '</td><td>' . esc_html( $r[2] ) . '</td></tr>';
		}
		echo '</tbody></table>';
	}
	echo '</div>';
}


/* ---------- Dashboard: publish on the website ---------- */

/**
 * The publication type for infographics (its slug differs between sites).
 */
function inpo_core_infographic_type() {
	foreach ( (array) get_terms( array( 'taxonomy' => INPO_CORE_PUB_TYPE, 'hide_empty' => false ) ) as $t ) {
		if ( $t instanceof WP_Term && ( 'infografika' === $t->slug || preg_match( '/infografik/iu', $t->name ) ) ) {
			return $t;
		}
	}
	return null;
}

// "Add infographic" opens a new publication with the infographic type already ticked.
add_filter(
	'wp_terms_checklist_args',
	function ( $args, $post_id ) {
		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_GET['inpo_type'] ) && 'infografika' === $_GET['inpo_type'] && isset( $args['taxonomy'] ) && INPO_CORE_PUB_TYPE === $args['taxonomy'] && ! wp_get_object_terms( $post_id, INPO_CORE_PUB_TYPE, array( 'fields' => 'ids' ) ) ) {
			$term = inpo_core_infographic_type();
			if ( $term ) {
				$args['selected_cats'] = array( $term->term_id );
			}
		}
		return $args;
	},
	10,
	2
);

add_action(
	'wp_dashboard_setup',
	function () {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}
		wp_add_dashboard_widget( 'inpo_publish', __( 'Publish on the INPO website', 'inpo-core' ), 'inpo_core_publish_widget' );
		// First in the left column.
		global $wp_meta_boxes;
		$normal = $wp_meta_boxes['dashboard']['normal']['core'];
		$mine   = array( 'inpo_publish' => $normal['inpo_publish'] );
		unset( $normal['inpo_publish'] );
		$wp_meta_boxes['dashboard']['normal']['core'] = array_merge( $mine, $normal ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
	}
);

function inpo_core_publish_widget() {
	$items = array(
		array( admin_url( 'post-new.php' ), __( 'News or reaction', 'inpo-core' ), 'dashicons-megaphone' ),
		array( admin_url( 'post-new.php?post_type=' . INPO_CORE_PUB ), __( 'Publication (PDF)', 'inpo-core' ), 'dashicons-media-document' ),
		array( admin_url( 'post-new.php?post_type=' . INPO_CORE_PUB . '&inpo_type=infografika' ), __( 'Infographic (images)', 'inpo-core' ), 'dashicons-format-gallery' ),
		array( admin_url( 'post-new.php?post_type=' . INPO_CORE_PROJECT ), __( 'Project', 'inpo-core' ), 'dashicons-portfolio' ),
		array( admin_url( 'post-new.php?post_type=' . INPO_CORE_DOC ), __( 'Transparency document', 'inpo-core' ), 'dashicons-media-spreadsheet' ),
		array( admin_url( 'post-new.php?post_type=' . INPO_CORE_DATASET ), __( 'Data and chart', 'inpo-core' ), 'dashicons-chart-bar' ),
	);
	echo '<style>.inpo-pub-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:8px;margin:4px 0 12px}.inpo-pub-grid a{display:flex;flex-direction:column;align-items:flex-start;gap:8px;padding:14px;border:1px solid #dcdcde;border-radius:8px;text-decoration:none;color:#1d2327;font-weight:600}.inpo-pub-grid a:hover,.inpo-pub-grid a:focus{border-color:#3D3B8E;color:#3D3B8E}.inpo-pub-grid .dashicons{color:#3D3B8E;font-size:24px;width:24px;height:24px}</style>';
	echo '<p style="margin-top:0">' . esc_html__( 'What do you want to add?', 'inpo-core' ) . '</p><div class="inpo-pub-grid">';
	foreach ( $items as $it ) {
		echo '<a href="' . esc_url( $it[0] ) . '"><span class="dashicons ' . esc_attr( $it[2] ) . '" aria-hidden="true"></span>' . esc_html( $it[1] ) . '</a>';
	}
	echo '</div><p style="margin-bottom:0"><a href="' . esc_url( admin_url( 'admin.php?page=inpo-help' ) ) . '">' . esc_html__( 'Where is everything else edited? See Help.', 'inpo-core' ) . '</a></p>';
}
