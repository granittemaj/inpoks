<?php
/**
 * Organisation settings: contact details, offices, socials and form recipients.
 * Defaults are the known public facts about INPO. Edit them in Settings > INPO.
 */

defined( 'ABSPATH' ) || exit;

const INPO_CORE_OPTION = 'inpo_core_settings';

/**
 * Default values. Only facts published on inpo-ks.org belong here.
 */
function inpo_core_default_settings() {
	return array(
		'short_name' => 'INPO',
		'name_long'  => 'Iniciativa për Progres',
		'about'      => 'Iniciativa për Progres promovon demokratizimin e qeverisjes lokale përmes aktivizmit qytetar.',
		'email'      => 'info@inpo-ks.org',
		'phone'      => '+383 (0) 49 354 000',
		'office1'    => 'Zyra në Prishtinë',
		'address1'   => "Rr. “Qamil Hoxha”, H32,\nnr. 09, kati IV\n10000 Prishtinë",
		'map1'       => 'https://www.google.com/maps/search/?api=1&query=Qamil+Hoxha+Prishtin%C3%AB',
		'office2'    => 'Zyra në Ferizaj',
		'address2'   => "Rr. “Latif Hasani”, pn\n70000 Ferizaj",
		'map2'       => 'https://www.google.com/maps/search/?api=1&query=Latif+Hasani+Ferizaj',
		'facebook'   => 'https://www.facebook.com/OrganizataINPO/',
		'x'          => 'https://x.com/organizatainpo',
		'youtube'    => 'https://www.youtube.com/channel/UC5FqAZlDj2B05tV4HSiLWOg',
		'audit_url'  => '',
		'fineprint'  => 'Ky grant është financuar nga projekti Promovimi i Shoqërisë Demokratike (DSP), i financuar nga Zyra Zvicerane për Bashkëpunim në Kosovë (SCO-K) dhe Ministria e Punëve të Jashtme e Danimarkës (DANIDA) dhe menaxhuar nga Fondacioni Kosovar për Shoqëri Civile (KCSF).',
		'recipient'  => '',
	);
}

/**
 * Returns the merged settings, or a single key.
 *
 * @param string|null $key Optional key.
 * @return mixed
 */
function inpo_core_settings( $key = null ) {
	static $cache = null;
	if ( null === $cache ) {
		$saved = get_option( INPO_CORE_OPTION, array() );
		$cache = array_merge( inpo_core_default_settings(), is_array( $saved ) ? array_filter( $saved, 'strlen' ) : array() );
		if ( '' === $cache['audit_url'] ) {
			$cache['audit_url'] = inpo_core_page_url( 'rreth-nesh' );
		}
		if ( '' === $cache['recipient'] ) {
			$cache['recipient'] = get_option( 'admin_email' );
		}
	}
	if ( null === $key ) {
		return $cache;
	}
	return isset( $cache[ $key ] ) ? $cache[ $key ] : '';
}

/**
 * Hands the organisation details to the INPO theme (header, footer).
 */
add_filter(
	'inpo_org_info',
	function ( $org ) {
		return array_merge( (array) $org, inpo_core_settings() );
	}
);

/**
 * Tells the theme the newsletter block exists, so it can show "Abonohu".
 */
add_filter( 'inpo_has_newsletter', '__return_true' );
