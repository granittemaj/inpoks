<?php
/**
 * Contact form and newsletter.
 *
 * Both forms work without JavaScript (POST to admin-post.php, redirect back)
 * and are enhanced with fetch() to the REST routes in rest.php.
 * Submissions are stored as private posts, so nothing is lost if mail fails.
 */

defined( 'ABSPATH' ) || exit;

const INPO_CORE_MESSAGE = 'inpo_message';
const INPO_CORE_SUBSCRIBER = 'inpo_subscriber';

add_action( 'init', 'inpo_core_register_form_types' );
function inpo_core_register_form_types() {
	$common = array(
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => 'inpo-core',
		'show_in_rest'        => false,
		'exclude_from_search' => true,
		'capability_type'     => 'post',
		'map_meta_cap'        => true,
		'capabilities'        => array( 'create_posts' => 'do_not_allow' ),
	);
	register_post_type(
		INPO_CORE_MESSAGE,
		array_merge(
			$common,
			array(
				'labels'   => array(
					'name'          => __( 'Messages', 'inpo-core' ),
					'singular_name' => __( 'Message', 'inpo-core' ),
					'edit_item'     => __( 'Message', 'inpo-core' ),
					'all_items'     => __( 'Messages', 'inpo-core' ),
					'not_found'     => __( 'No messages yet.', 'inpo-core' ),
				),
				'supports' => array( 'title', 'editor' ),
			)
		)
	);
	register_post_type(
		INPO_CORE_SUBSCRIBER,
		array_merge(
			$common,
			array(
				'labels'   => array(
					'name'          => __( 'Subscribers', 'inpo-core' ),
					'singular_name' => __( 'Subscriber', 'inpo-core' ),
					'edit_item'     => __( 'Subscriber', 'inpo-core' ),
					'all_items'     => __( 'Subscribers', 'inpo-core' ),
					'not_found'     => __( 'No subscribers yet.', 'inpo-core' ),
				),
				'supports' => array( 'title' ),
			)
		)
	);
}

/**
 * Request values as plain strings. Arrays (e.g. ?email[]=x) become '' instead of warnings.
 */
function inpo_core_str( $value ) {
	return is_string( $value ) || is_numeric( $value ) ? (string) $value : '';
}

/**
 * Contact topics. Filter "inpo_core_contact_topics" to change them.
 */
function inpo_core_contact_topics() {
	return apply_filters(
		'inpo_core_contact_topics',
		array(
			'tender'      => 'Informatë për tender',
			'publikime'   => 'Publikimet',
			'media'       => 'Media',
			'bashkepunim' => 'Bashkëpunim',
		)
	);
}

/**
 * Same rule as the prototype, plus WordPress's own check.
 */
function inpo_core_email_valid( $email ) {
	return (bool) preg_match( '/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/', $email ) && is_email( $email );
}

/**
 * Simple per-IP rate limit. The IP is hashed, never stored.
 *
 * @param string $bucket Name of the limit.
 * @param int    $max    Allowed hits per window.
 * @param int    $window Seconds.
 * @return bool True when the limit is exceeded.
 */
function inpo_core_rate_limited( $bucket, $max, $window ) {
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	/**
	 * Behind Cloudflare or a reverse proxy every visitor shares REMOTE_ADDR.
	 * Return the real client IP here (e.g. from CF-Connecting-IP) so the limit applies per visitor.
	 */
	$ip  = (string) apply_filters( 'inpo_core_client_ip', $ip );
	$key = 'inpo_rl_' . $bucket . '_' . substr( hash_hmac( 'sha256', $ip, wp_salt( 'nonce' ) ), 0, 20 );
	$hit = (int) get_transient( $key );
	if ( $hit >= $max ) {
		return true;
	}
	set_transient( $key, $hit + 1, $window );
	return false;
}

/**
 * Validates and stores a contact message.
 *
 * @param array $data Raw fields: emri, mbiemri, email, tema, mesazhi, pelqim, website (honeypot).
 * @return array { ok: bool, errors: array, values: array }
 */
function inpo_core_process_contact( $data ) {
	$topics = inpo_core_contact_topics();
	$v      = array(
		'emri'    => sanitize_text_field( inpo_core_str( isset( $data['emri'] ) ? $data['emri'] : '' ) ),
		'mbiemri' => sanitize_text_field( inpo_core_str( isset( $data['mbiemri'] ) ? $data['mbiemri'] : '' ) ),
		'email'   => sanitize_email( inpo_core_str( isset( $data['email'] ) ? $data['email'] : '' ) ),
		'tema'    => sanitize_key( inpo_core_str( isset( $data['tema'] ) ? $data['tema'] : '' ) ),
		'mesazhi' => sanitize_textarea_field( inpo_core_str( isset( $data['mesazhi'] ) ? $data['mesazhi'] : '' ) ),
		'pelqim'  => ! empty( $data['pelqim'] ),
	);
	if ( ! isset( $topics[ $v['tema'] ] ) ) {
		$v['tema'] = (string) key( $topics );
	}

	// Bots fill the hidden field. Pretend success, store nothing.
	if ( ! empty( $data['inpo_hp'] ) ) {
		return array( 'ok' => true, 'errors' => array(), 'values' => $v );
	}

	$errors = array();
	if ( '' === trim( $v['emri'] ) ) {
		$errors['emri'] = 'Shkruani emrin.';
	}
	if ( ! inpo_core_email_valid( $v['email'] ) ) {
		$errors['email'] = 'Shkruani një adresë email të vlefshme, p.sh. emri@shembull.com.';
	}
	if ( mb_strlen( trim( $v['mesazhi'] ) ) < 10 ) {
		$errors['mesazhi'] = 'Shkruani mesazhin, të paktën 10 shkronja.';
	}
	if ( ! $v['pelqim'] ) {
		$errors['pelqim'] = 'Pranoni kushtin për të dërguar mesazhin.';
	}
	if ( ! $errors && inpo_core_rate_limited( 'contact', 5, 10 * MINUTE_IN_SECONDS ) ) {
		$errors['form'] = 'Keni dërguar disa mesazhe radhazi. Provoni përsëri pas pak minutash.';
	}
	if ( $errors ) {
		return array( 'ok' => false, 'errors' => $errors, 'values' => $v );
	}

	$name  = trim( $v['emri'] . ' ' . $v['mbiemri'] );
	$topic = $topics[ $v['tema'] ];
	$id    = wp_insert_post(
		wp_slash( array(
			'post_type'    => INPO_CORE_MESSAGE,
			'post_status'  => 'private',
			'post_title'   => $name . ' · ' . $topic,
			'post_content' => $v['mesazhi'],
			'meta_input'   => array(
				'inpo_email' => strtolower( $v['email'] ),
				'inpo_topic' => $topic,
				'inpo_name'  => $name,
			),
		) ),
		true
	);
	if ( is_wp_error( $id ) ) {
		return array( 'ok' => false, 'errors' => array( 'form' => 'Mesazhi nuk u ruajt. Provoni përsëri ose shkruani te ' . inpo_core_settings( 'email' ) . '.' ), 'values' => $v );
	}

	/* translators: 1: name, 2: email, 3: topic */
	$body = sprintf( __( "Name: %1\$s\nEmail: %2\$s\nTopic: %3\$s", 'inpo-core' ), $name, $v['email'], $topic ) . "\n\n{$v['mesazhi']}\n\n" . admin_url( 'post.php?post=' . $id . '&action=edit' );
	wp_mail(
		inpo_core_settings( 'recipient' ),
		/* translators: %s: topic */
		sprintf( __( '[INPO] New contact message: %s', 'inpo-core' ), $topic ),
		$body,
		array( 'Reply-To: ' . $name . ' <' . $v['email'] . '>' )
	);
	do_action( 'inpo_core_contact_received', $id, $v );

	return array( 'ok' => true, 'errors' => array(), 'values' => $v );
}

/**
 * Stores a newsletter subscriber. Duplicates are accepted silently.
 *
 * @param array $data Raw fields: email, website (honeypot).
 * @return array { ok: bool, error: string, email: string }
 */
function inpo_core_process_subscribe( $data ) {
	$email = sanitize_email( inpo_core_str( isset( $data['email'] ) ? $data['email'] : '' ) );
	if ( ! empty( $data['inpo_hp'] ) ) {
		return array( 'ok' => true, 'error' => '', 'email' => $email );
	}
	if ( ! inpo_core_email_valid( $email ) ) {
		return array( 'ok' => false, 'error' => 'Shkruani një adresë email të vlefshme, p.sh. emri@shembull.com.', 'email' => $email );
	}
	if ( inpo_core_rate_limited( 'subscribe', 5, 10 * MINUTE_IN_SECONDS ) ) {
		return array( 'ok' => false, 'error' => 'Shumë kërkesa radhazi. Provoni përsëri pas pak minutash.', 'email' => $email );
	}
	$email    = strtolower( $email );
	$existing = get_posts(
		array(
			'post_type'      => INPO_CORE_SUBSCRIBER,
			'post_status'    => 'any',
			'meta_key'       => 'inpo_email',
			'meta_value'     => $email,
			'posts_per_page' => 1,
			'fields'         => 'ids',
		)
	);
	if ( ! $existing ) {
		$id = wp_insert_post(
			array(
				'post_type'   => INPO_CORE_SUBSCRIBER,
				'post_status' => 'private',
				'post_title'  => $email,
				'meta_input'  => array( 'inpo_email' => $email ),
			)
		);
		if ( $id && ! is_wp_error( $id ) ) {
			/**
			 * Hook for mailing-list sync (Brevo, Mailchimp...).
			 *
			 * @param string $email Subscriber email.
			 * @param int    $id    Subscriber post ID.
			 */
			do_action( 'inpo_core_newsletter_subscribed', $email, $id );
		}
	}
	return array( 'ok' => true, 'error' => '', 'email' => $email );
}

/* ---------- No-JS fallback: admin-post.php handlers ---------- */

add_action( 'admin_post_nopriv_inpo_contact', 'inpo_core_handle_contact_post' );
add_action( 'admin_post_inpo_contact', 'inpo_core_handle_contact_post' );
function inpo_core_handle_contact_post() {
	$result = inpo_core_process_contact( wp_unslash( $_POST ) ); // phpcs:ignore WordPress.Security.NonceVerification
	inpo_core_redirect_with_result( 'inpo_contact', $result, 'kontakt' );
}

add_action( 'admin_post_nopriv_inpo_subscribe', 'inpo_core_handle_subscribe_post' );
add_action( 'admin_post_inpo_subscribe', 'inpo_core_handle_subscribe_post' );
function inpo_core_handle_subscribe_post() {
	$result = inpo_core_process_subscribe( wp_unslash( $_POST ) ); // phpcs:ignore WordPress.Security.NonceVerification
	inpo_core_redirect_with_result( 'inpo_nl', $result, 'newsletter' );
}

/**
 * Keeps the result server-side for 10 minutes and passes only a random token in the URL,
 * so no personal data ends up in links or logs.
 */
function inpo_core_redirect_with_result( $param, $result, $anchor ) {
	$token = wp_generate_password( 16, false );
	set_transient( 'inpo_form_' . $token, $result, 10 * MINUTE_IN_SECONDS );
	// The form sends its own page URL, so the result shows even when the browser sends no Referer.
	$back = isset( $_POST['inpo_back'] ) ? wp_validate_redirect( esc_url_raw( inpo_core_str( wp_unslash( $_POST['inpo_back'] ) ) ), '' ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	$back = $back ? $back : wp_get_referer();
	$back = $back ? remove_query_arg( array( 'inpo_contact', 'inpo_nl' ), $back ) : home_url( '/' );
	$back = preg_replace( '/#.*$/', '', $back );
	wp_safe_redirect( add_query_arg( $param, $token, $back ) . '#' . $anchor );
	exit;
}

/**
 * Reads a no-JS form result for the current request.
 *
 * @param string $param inpo_contact or inpo_nl.
 * @return array|null
 */
function inpo_core_form_result( $param ) {
	$token = isset( $_GET[ $param ] ) ? inpo_core_str( wp_unslash( $_GET[ $param ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	$token = preg_replace( '/[^A-Za-z0-9]/', '', $token );
	if ( '' === $token ) {
		return null;
	}
	$result = get_transient( 'inpo_form_' . $token );
	return is_array( $result ) ? $result : null;
}

/* ---------- Privacy tools (Tools > Export / Erase Personal Data) ---------- */

add_filter(
	'wp_privacy_personal_data_exporters',
	function ( $exporters ) {
		$exporters['inpo-core'] = array(
			'exporter_friendly_name' => __( 'INPO: messages and newsletter', 'inpo-core' ),
			'callback'               => 'inpo_core_privacy_export',
		);
		return $exporters;
	}
);
add_filter(
	'wp_privacy_personal_data_erasers',
	function ( $erasers ) {
		$erasers['inpo-core'] = array(
			'eraser_friendly_name' => __( 'INPO: messages and newsletter', 'inpo-core' ),
			'callback'             => 'inpo_core_privacy_erase',
		);
		return $erasers;
	}
);

function inpo_core_privacy_posts( $email ) {
	return get_posts(
		array(
			'post_type'      => array( INPO_CORE_MESSAGE, INPO_CORE_SUBSCRIBER ),
			'post_status'    => array( 'any', 'trash' ),
			'meta_key'       => 'inpo_email',
			'meta_value'     => strtolower( $email ),
			'posts_per_page' => -1,
		)
	);
}

function inpo_core_privacy_export( $email ) {
	$items = array();
	foreach ( inpo_core_privacy_posts( $email ) as $post ) {
		$items[] = array(
			'group_id'    => $post->post_type,
			'group_label' => INPO_CORE_MESSAGE === $post->post_type ? __( 'Contact messages', 'inpo-core' ) : __( 'Newsletter', 'inpo-core' ),
			'item_id'     => $post->post_type . '-' . $post->ID,
			'data'        => array(
				array( 'name' => __( 'Date', 'inpo-core' ), 'value' => $post->post_date ),
				array( 'name' => __( 'Title', 'inpo-core' ), 'value' => $post->post_title ),
				array( 'name' => __( 'Content', 'inpo-core' ), 'value' => $post->post_content ),
			),
		);
	}
	return array( 'data' => $items, 'done' => true );
}

function inpo_core_privacy_erase( $email ) {
	$removed = false;
	foreach ( inpo_core_privacy_posts( $email ) as $post ) {
		$removed = (bool) wp_delete_post( $post->ID, true ) || $removed;
	}
	return array( 'items_removed' => $removed, 'items_retained' => false, 'messages' => array(), 'done' => true );
}
