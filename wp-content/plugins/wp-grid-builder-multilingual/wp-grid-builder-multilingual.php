<?php
/**
 * WP Grid Builder Multilingual Add-on
 *
 * @package   WP Grid Builder - Multilingual
 * @author    Loïc Blascos
 * @link      https://www.wpgridbuilder.com
 * @copyright 2019-2022 Loïc Blascos
 *
 * @wordpress-plugin
 * Plugin Name:  WP Grid Builder - Multilingual
 * Plugin URI:   https://www.wpgridbuilder.com
 * Description:  Easily integrate WP Grid Builder with Polylang and WPML plugins.
 * Version:      1.1.2
 * Author:       Loïc Blascos
 * Author URI:   https://www.wpgridbuilder.com
 * License:      GPL-3.0-or-later
 * License URI:  https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:  wpgb-multilingual
 * Domain Path:  /languages
 */

use WP_Grid_Builder_Multilingual\Includes\Plugin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WPGB_MULTILINGUAL_VERSION', '1.1.2' );
define( 'WPGB_MULTILINGUAL_FILE', __FILE__ );
define( 'WPGB_MULTILINGUAL_BASE', plugin_basename( WPGB_MULTILINGUAL_FILE ) );
define( 'WPGB_MULTILINGUAL_PATH', plugin_dir_path( WPGB_MULTILINGUAL_FILE ) );
define( 'WPGB_MULTILINGUAL_URL', plugin_dir_url( WPGB_MULTILINGUAL_FILE ) );

require_once WPGB_MULTILINGUAL_PATH . 'includes/class-autoload.php';

new Plugin();
