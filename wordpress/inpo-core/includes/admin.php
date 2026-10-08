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
	add_submenu_page( 'inpo-core', __( 'INPO settings', 'inpo-core' ), __( 'Settings', 'inpo-core' ), 'manage_options', 'inpo-core', 'inpo_core_settings_page' );
}

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
		$order = array( 'inpo-core', 'inpo-setup', 'edit.php?post_type=' . INPO_CORE_MEMBER, 'edit.php?post_type=' . INPO_CORE_DONOR, 'edit.php?post_type=' . INPO_CORE_MESSAGE, 'edit.php?post_type=' . INPO_CORE_SUBSCRIBER );
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
	if ( ! current_user_can( 'manage_options' ) ) {
		echo '<div class="wrap"><h1>INPO</h1><p>' . esc_html__( 'Use the menu for team, donors, messages and subscribers.', 'inpo-core' ) . '</p></div>';
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

add_action( 'add_meta_boxes', 'inpo_core_meta_boxes' );
function inpo_core_meta_boxes() {
	add_meta_box( 'inpo-post', __( 'INPO: news details', 'inpo-core' ), 'inpo_core_post_box', 'post', 'side' );
	add_meta_box( 'inpo-pub', __( 'INPO: publication details', 'inpo-core' ), 'inpo_core_pub_box', INPO_CORE_PUB, 'side' );
	add_meta_box( 'inpo-page', __( 'INPO: page head', 'inpo-core' ), 'inpo_core_page_box', 'page', 'side' );
	add_meta_box( 'inpo-donor', __( 'Donor link', 'inpo-core' ), 'inpo_core_donor_box', INPO_CORE_DONOR, 'normal' );
	add_meta_box( 'inpo-member', __( 'Role and email', 'inpo-core' ), 'inpo_core_member_box', INPO_CORE_MEMBER, 'normal' );
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
		if ( 'select' === $type ) {
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
}

function inpo_core_pub_box( $post ) {
	wp_nonce_field( 'inpo_meta', 'inpo_meta_nonce' );
	inpo_core_field( $post, 'inpo_pdf', __( 'PDF file (URL)', 'inpo-core' ), 'url', __( 'Optional. Empty = the newest PDF attached to this publication in Media.', 'inpo-core' ) );
	echo '<p><button type="button" class="button" id="inpo-pick-pdf">' . esc_html__( 'Choose from Media', 'inpo-core' ) . '</button></p>';
	inpo_core_field( $post, 'inpo_year', __( 'Year', 'inpo-core' ), 'text', __( 'Empty = the publish year.', 'inpo-core' ) );
	inpo_core_field( $post, 'inpo_partners', __( 'Partners', 'inpo-core' ), 'text', __( 'If filled in, shown instead of the year.', 'inpo-core' ) );
	inpo_core_field( $post, 'inpo_donor', __( 'Supported by', 'inpo-core' ), 'text' );
	inpo_core_field( $post, 'inpo_short', __( 'Cover word', 'inpo-core' ), 'text', __( 'E.g. OShP. Used on the "word" cover style.', 'inpo-core' ) );
	inpo_core_field(
		$post,
		'inpo_cover',
		__( 'Cover style', 'inpo-core' ),
		'select',
		__( 'Automatic rotates the style by position in the list.', 'inpo-core' ),
		array(
			''      => __( 'Automatic', 'inpo-core' ),
			'rings' => __( 'Rings (indigo)', 'inpo-core' ),
			'bars'  => __( 'Bars', 'inpo-core' ),
			'dots'  => __( 'Dots (dark)', 'inpo-core' ),
			'word'  => __( 'Word', 'inpo-core' ),
		)
	);
	?>
	<script>
	(function () {
		var b = document.getElementById('inpo-pick-pdf');
		if (!b || !window.wp || !wp.media) return;
		b.addEventListener('click', function () {
			var f = wp.media({ title: 'Choose PDF', library: { type: 'application/pdf' }, multiple: false });
			f.on('select', function () { document.getElementById('inpo-inpo_pdf').value = f.state().get('selection').first().get('url'); });
			f.open();
		});
	})();
	</script>
	<?php
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
}

function inpo_core_message_box( $post ) {
	$email = get_post_meta( $post->ID, 'inpo_email', true );
	echo '<p><strong>' . esc_html( get_post_meta( $post->ID, 'inpo_name', true ) ) . '</strong><br>';
	echo '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a><br>';
	echo esc_html( get_post_meta( $post->ID, 'inpo_topic', true ) ) . '</p>';
}

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( in_array( $hook, array( 'post.php', 'post-new.php' ), true ) && INPO_CORE_PUB === get_post_type() ) {
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
		'post'          => array( 'inpo_short_title' => 'text', 'inpo_date_label' => 'text', 'inpo_publication' => 'int', 'inpo_todo' => 'bool' ),
		INPO_CORE_PUB   => array( 'inpo_pdf' => 'url', 'inpo_year' => 'text', 'inpo_partners' => 'text', 'inpo_donor' => 'text', 'inpo_short' => 'text', 'inpo_cover' => 'text' ),
		'page'          => array( 'inpo_kicker' => 'text' ),
		INPO_CORE_DONOR  => array( 'inpo_url' => 'url' ),
		INPO_CORE_MEMBER => array( 'inpo_role' => 'text', 'inpo_email' => 'email' ),
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

	printf( $open, esc_html__( '"Si punojmë" cards', 'inpo-core' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
	for ( $i = 0; $i < 3; $i++ ) {
		$w = isset( $ways[ $i ] ) ? (array) $ways[ $i ] : array( '', '' );
		echo '<p><input type="text" name="inpo_ways[' . $i . '][0]" placeholder="' . esc_attr__( 'Title', 'inpo-core' ) . '" value="' . esc_attr( isset( $w[0] ) ? $w[0] : '' ) . '"></p>'; // phpcs:ignore WordPress.Security.EscapeOutput
		echo '<p><textarea rows="2" name="inpo_ways[' . $i . '][1]" placeholder="' . esc_attr__( 'Description', 'inpo-core' ) . '">' . esc_textarea( isset( $w[1] ) ? $w[1] : '' ) . '</textarea></p>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
	echo $close; // phpcs:ignore WordPress.Security.EscapeOutput

	printf( $open, esc_html__( 'Main activities', 'inpo-core' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
	echo '<textarea name="inpo_activities" rows="5">' . esc_textarea( implode( "\n", (array) $get( 'inpo_activities' ) ) ) . '</textarea><p class="description">' . esc_html__( 'One activity per line. Shown as a numbered list on the programme page.', 'inpo-core' ) . '</p>' . $close; // phpcs:ignore WordPress.Security.EscapeOutput

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
	foreach ( array( 'inpo_full', 'inpo_icon', 'inpo_singular' ) as $key ) {
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
