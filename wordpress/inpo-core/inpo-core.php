<?php
/**
 * Plugin Name:       INPO Core
 * Plugin URI:        https://inpo-ks.org
 * Description:       Content model, blocks, forms and settings for the INPO website. Pairs with the INPO theme.
 * Version:           1.9.0
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Author:            Iniciativa për Progres
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       inpo-core
 *
 * Functionality lives here so content survives a theme switch:
 * publications, programmes, donors, contact and newsletter storage,
 * organisation settings and the dynamic blocks that render them.
 */

defined( 'ABSPATH' ) || exit;

define( 'INPO_CORE_VERSION', '1.9.0' );
define( 'INPO_CORE_FILE', __FILE__ );
define( 'INPO_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'INPO_CORE_URL', plugin_dir_url( __FILE__ ) );

require_once INPO_CORE_DIR . 'includes/settings.php';
require_once INPO_CORE_DIR . 'includes/helpers.php';
require_once INPO_CORE_DIR . 'includes/content-model.php';
require_once INPO_CORE_DIR . 'includes/filters.php';
require_once INPO_CORE_DIR . 'includes/forms.php';
require_once INPO_CORE_DIR . 'includes/rest.php';
require_once INPO_CORE_DIR . 'includes/blocks.php';
require_once INPO_CORE_DIR . 'includes/render.php';
require_once INPO_CORE_DIR . 'includes/listing-pages.php';

if ( is_admin() ) {
	require_once INPO_CORE_DIR . 'includes/admin.php';
}
require_once INPO_CORE_DIR . 'includes/setup-brief.php';
require_once INPO_CORE_DIR . 'includes/setup.php';

register_activation_hook( __FILE__, 'inpo_core_activate' );
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );

/**
 * Registers the content model before flushing so the new URLs work at once.
 */
function inpo_core_activate() {
	inpo_core_register_content_model();
	flush_rewrite_rules();
}
