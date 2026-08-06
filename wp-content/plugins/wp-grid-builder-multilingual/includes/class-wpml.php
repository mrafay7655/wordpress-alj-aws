<?php
/**
 * WPML
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
 * Add WPML support
 *
 * @class WP_Grid_Builder_Multilingual\Includes\WPML
 * @since 1.0.0
 */
final class WPML extends Translate {

	/**
	 * Constructor
	 *
	 * @access public
	 * @since 1.2.0
	 */
	public function __construct() {

		parent::__construct();

		// Handle strings translation.
		add_action( 'wp_grid_builder_i18n/switch_language', [ $this, 'switch_language' ] );
		add_action( 'wp_grid_builder_i18n/register_string', [ $this, 'register_string' ], 10, 2 );
		add_action( 'wp_grid_builder_i18n/unregister_string', [ $this, 'unregister_string' ], 10, 2 );
		add_filter( 'wp_grid_builder_i18n/translate_string', [ $this, 'translate_string' ], 10, 5 );

		// Prevent issue with lang parameter set from WP Grid Builder.
		add_filter( 'wp_grid_builder/async/get_endpoint', [ $this, 'unset_lang_parameter' ] );
		add_filter( 'wp_grid_builder/admin/preview_action', [ $this, 'unset_lang_parameter' ] );

	}

	/**
	 * Register plugin string
	 *
	 * @access public
	 * @since 1.0.0
	 *
	 * @param string $string Holds string arguments.
	 * @param string $domain Domain name.
	 */
	public function register_string( $string, $domain ) {

		do_action( 'wpml_register_single_string', $domain, $string, $string );

	}

	/**
	 * Unregister plugin string
	 *
	 * @access public
	 * @since 1.0.0
	 *
	 * @param string $string Holds string arguments.
	 * @param string $domain Domain name.
	 */
	public function unregister_string( $string, $domain ) {

		if ( ! function_exists( 'icl_unregister_string' ) ) {
			return;
		}

		icl_unregister_string( $domain, $string );

	}

	/**
	 * Translate plugin string
	 *
	 * @access public
	 * @since 1.0.0
	 *
	 * @param string $string  String value to translate.
	 * @param string $slug    String slug unique name.
	 * @param string $context String context (grid/card/facet).
	 * @param string $domain  Domain name (Gridbuilder ᵂᴾ).
	 * @param string $lang    Current language to translate.
	 * @return string
	 */
	public function translate_string( $string, $slug, $context, $domain, $lang ) {

		return apply_filters( 'wpml_translate_single_string', $string, $domain, $string, $lang );

	}

	/**
	 * Switch language
	 *
	 * @access public
	 * @since 1.0.0
	 *
	 * @param string $lang Language to switch to.
	 */
	public function switch_language( $lang ) {

		global $sitepress;

		if ( ! method_exists( $sitepress, 'switch_lang' ) ) {
			return;
		}

		$sitepress->switch_lang( $lang );

	}

	/**
	 * Prevent WPML to redirect POST requests when filtering with facets and when viewing grids in preview mode.
	 * If it's the default language and that WPML sets the lang in query string it will redirect POST requests.
	 *
	 * @access public
	 * @since 1.0.1
	 *
	 * @param string $endpoint Async/preview endpoint url.
	 * @return string
	 */
	public function unset_lang_parameter( $endpoint ) {

		$current_lang = $this->get_current_language();
		$default_lang = $this->get_default_language();
		$query_string = 3 === (int) apply_filters( 'wpml_setting', 0, 'language_negotiation_type' );

		// We remove lang from query args to prevent any issue with default language.
		if ( $query_string && $current_lang === $default_lang ) {
			$endpoint = remove_query_arg( 'lang', $endpoint );
		}

		return $endpoint;

	}

	/**
	 * Get the language of the post from its ID
	 *
	 * @access public
	 * @since 1.1.0
	 *
	 * @param integer $post_id Post ID.
	 * @return string
	 */
	public function get_post_language( $post_id ) {

		global $sitepress;

		$info = wpml_get_language_information( null, $post_id );

		if ( ! is_wp_error( $info ) && ! empty( $info['language_code'] ) ) {
			return $info['language_code'];
		}

		return $this->get_default_language();

	}

	/**
	 * Get terms translations in all languages
	 *
	 * @access public
	 * @since 1.0.6
	 *
	 * @param array  $term_ids Term IDs from default language to translate.
	 * @param string $taxonomy Taxonomy name.
	 * @param string $lang     Translated language.
	 * @return array Translated terms
	 */
	public function get_terms_translations( $term_ids, $taxonomy, $lang ) {

		$_term_ids = [];
		$languages = (array) ( $lang ?: array_keys( icl_get_languages() ) );

		foreach ( (array) $term_ids as $term_id ) {

			$_term_ids = array_merge(
				$_term_ids,
				array_map(
					function( $language ) use ( $term_id, $taxonomy ) {
						return icl_object_id( $term_id, $taxonomy, false, $language );
					},
					$languages
				)
			);

		}

		return $_term_ids;

	}
}
