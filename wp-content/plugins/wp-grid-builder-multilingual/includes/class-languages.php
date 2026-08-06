<?php
/**
 * Languages
 *
 * @package   WP Grid Builder - Multilingual
 * @author    Loïc Blascos
 * @copyright 2019-2022 Loïc Blascos
 */

namespace WP_Grid_Builder_Multilingual\Includes;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle languages
 *
 * @class WP_Grid_Builder_Multilingual\Includes\Languages
 * @since 1.0.0
 */
final class Languages {

	use Helpers;

	/**
	 * Constructor
	 *
	 * @access public
	 * @since 1.0.0
	 */
	public function __construct() {

		add_filter( 'wp_grid_builder/admin/load_page', [ $this, 'switch_language' ] );
		add_filter( 'wp_grid_builder/admin/localize_script', [ $this, 'localize_language' ] );
		add_filter( 'wp_grid_builder/admin/preview_action', [ $this, 'set_endpoint_language' ] );
		add_filter( 'wp_grid_builder/async/get_endpoint', [ $this, 'set_endpoint_language' ] );

	}

	/**
	 * Switch language to default in plugin panels
	 * So whatever the admin language all grids/facets/cards use the default language
	 *
	 * @access public
	 * @since 1.0.0
	 *
	 * @param string $page_path Page path to include in panel.
	 * @return string
	 */
	public function switch_language( $page_path ) {

		$language = $this->get_default_language();

		if ( ! empty( $language ) ) {
			do_action( 'wp_grid_builder_i18n/switch_language', $language );
		}

		return $page_path;

	}

	/**
	 * Localize language to query default language with admin-ajax
	 *
	 * @access public
	 * @since 1.0.0
	 *
	 * @param array $data Holds data to localize.
	 * @return array
	 */
	public function localize_language( $data ) {

		$language = $this->get_default_language();

		if ( ! empty( $language ) && isset( $data['lang'] ) ) {
			$data['lang'] = $language;
		}

		return $data;

	}

	/**
	 * Add language parameter in async and preview endpoints
	 *
	 * @access public
	 * @since 1.0.0
	 *
	 * @param string $endpoint Async endpoint url.
	 * @return string
	 */
	public function set_endpoint_language( $endpoint ) {

		$language = $this->get_current_language();

		if ( ! empty( $language ) ) {

			$endpoint = remove_query_arg( 'lang', $endpoint );
			$endpoint = add_query_arg( 'lang', $language, $endpoint );

		}

		return $endpoint;

	}
}
