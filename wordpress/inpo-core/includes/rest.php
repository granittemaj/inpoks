<?php
/**
 * REST routes under /wp-json/inpo/v1/.
 *
 * POST /contact    contact form (JSON body, same fields as the HTML form)
 * POST /subscribe  newsletter
 * GET  /search?q=  quick search for the header overlay (news first, then publications)
 */

defined( 'ABSPATH' ) || exit;

add_action( 'rest_api_init', 'inpo_core_register_routes' );
function inpo_core_register_routes() {
	register_rest_route(
		'inpo/v1',
		'/contact',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'permission_callback' => '__return_true',
			'callback'            => function ( WP_REST_Request $request ) {
				$result = inpo_core_process_contact( (array) $request->get_params() );
				$name   = $result['values']['emri'];
				return new WP_REST_Response(
					array(
						'ok'     => $result['ok'],
						'errors' => (object) $result['errors'],
						'name'   => $name,
						'email'  => $result['values']['email'],
					),
					$result['ok'] ? 200 : 422
				);
			},
		)
	);

	register_rest_route(
		'inpo/v1',
		'/subscribe',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'permission_callback' => '__return_true',
			'callback'            => function ( WP_REST_Request $request ) {
				$result = inpo_core_process_subscribe( (array) $request->get_params() );
				return new WP_REST_Response( $result, $result['ok'] ? 200 : 422 );
			},
		)
	);

	register_rest_route(
		'inpo/v1',
		'/search',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'permission_callback' => '__return_true',
			'args'                => array(
				'q' => array(
					'type'              => 'string',
					'required'          => true,
					'sanitize_callback' => 'sanitize_text_field',
				),
			),
			'callback'            => 'inpo_core_rest_search',
		)
	);
}

/**
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response
 */
function inpo_core_rest_search( WP_REST_Request $request ) {
	$q = trim( (string) $request->get_param( 'q' ) );
	if ( mb_strlen( $q ) < 2 ) {
		return new WP_REST_Response( array() );
	}
	$items = array();
	foreach ( array( 'post', INPO_CORE_PUB ) as $type ) {
		$posts = get_posts(
			array(
				'post_type'        => $type,
				's'                => $q,
				'posts_per_page'   => 8,
				'post_status'      => 'publish',
				'suppress_filters' => false,
			)
		);
		foreach ( $posts as $post ) {
			if ( 'post' === $type ) {
				$kind = inpo_core_post_category_name( $post );
			} else {
				$kind = inpo_core_type_label( inpo_core_pub_type( $post ) );
			}
			$items[] = array(
				'url'   => get_permalink( $post ),
				'title' => wp_strip_all_tags( get_the_title( $post ) ),
				'kind'  => $kind,
			);
		}
	}
	return new WP_REST_Response( array_slice( $items, 0, 8 ) );
}
