<?php
/**
 * WP Grid Builder
 *
 * @package   WP Grid Builder
 * @author    Loïc Blascos
 * @link      https://www.wpgridbuilder.com
 * @copyright 2019-2024 Loïc Blascos
 *
 * @wordpress-plugin
 * Plugin Name:       WP Grid Builder
 * Plugin URI:        https://www.wpgridbuilder.com
 * Description:       Build advanced grid layouts with real time faceted search for your eCommerce, blog, portfolio, and more...
 * Version:           2.3.1
 * Requires at least: 6.2
 * Requires PHP:      7.0
 * Author:            Loïc Blascos
 * Author URI:        https://www.wpgridbuilder.com
 * License:           GPL-3.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       wp-grid-builder
 * Domain Path:       /languages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WPGB_VERSION', '2.3.1' );
define( 'WPGB_SLUG', 'wpgb' );
define( 'WPGB_NAME', 'WP Grid Builder' );
define( 'WPGB_FILE', __FILE__ );
define( 'WPGB_BASE', plugin_basename( WPGB_FILE ) );
define( 'WPGB_PATH', plugin_dir_path( WPGB_FILE ) );
define( 'WPGB_URL', plugin_dir_url( WPGB_FILE ) );


add_filter('pre_http_request', function($pre, $args, $url) {
    if(strpos($url, 'wpgridbuilder.com') !== false && strpos($url, 'edd_action=') !== false) {
        $license_data = [
            'success' => true,
            'license' => 'valid',
            'item_name' => 'WP Grid Builder',
            'license_limit' => 999,
            'site_count' => 1,
            'expires' => 'lifetime',
            'activations_left' => 'unlimited',
            'license_key' => 'WEADOWN000000005603B1EBE59708542',
            'license_type' => 'lifetime',
            'is_local' => false,
            'addons' => [
                'bricks' => true,
                'pods' => true,
                'beaver-builder' => true,
                'meta-box' => true,
                'oxygen' => true,
                'elementor' => true,
                'multilingual' => true,
                'caching' => true,
                'map-facet' => true,
                'learndash' => true
            ]
        ];
        
        update_option(WPGB_SLUG . '_plugin_info', $license_data);
        
        return [
            'body' => json_encode($license_data),
            'response' => ['code' => 200, 'message' => 'OK'],
            'cookies' => [],
            'headers' => []
        ];
    }
    return $pre;
}, 10, 3);

require_once WPGB_PATH . '/includes/class-autoload.php';

/**
 * Instantiate the plugin
 *
 * @since 1.0.0
 * @return \WP_Grid_Builder\Includes\Plugin Plugin instance
 */
function wp_grid_builder() {

        $license_data = [
        'success' => true,
        'license' => 'valid',
        'item_name' => 'WP Grid Builder',
        'license_limit' => 999,
        'site_count' => 1,
        'expires' => 'lifetime',
        'activations_left' => 'unlimited',
        'license_key' => 'WEADOWN000000005603B1EBE59708542',
        'license_type' => 'lifetime',
        'is_local' => false,
        'addons' => [
            'bricks' => true,
            'pods' => true,
            'beaver-builder' => true,
            'meta-box' => true,
            'oxygen' => true,
            'elementor' => true,
            'multilingual' => true,
            'caching' => true,
            'map-facet' => true,
            'learndash' => true
        ]
    ];
    update_option(WPGB_SLUG . '_plugin_info', $license_data);

	return \WP_Grid_Builder\Includes\Plugin::get_instance();

}

wp_grid_builder();
