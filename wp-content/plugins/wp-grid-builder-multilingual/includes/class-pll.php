<?php
/**
 * Polylang
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
 * Add Polylang support
 *
 * @class WP_Grid_Builder_Multilingual\Includes\PLL
 * @since 1.0.0
 */
final class PLL extends Translate {

	/**
	 * Constructor
	 *
	 * @access public
	 * @since 1.0.0
	 */
	public function __construct() {

		parent::__construct();

		// Handle strings translation.
		add_action( 'wp_grid_builder_i18n/switch_language', [ $this, 'switch_language' ] );
		add_action( 'wp_grid_builder_i18n/register_string', [ $this, 'register_string' ], 10, 3 );
		add_filter( 'wp_grid_builder_i18n/translate_string', [ $this, 'translate_string' ], 10, 5 );

		// Handle Polylang context.
		add_filter( 'pll_context', [ $this, 'change_context' ] );
		add_filter( 'pll_is_ajax_on_front', [ $this, 'is_ajax_on_front' ] );

	}

	/**
	 * Register plugin string
	 *
	 * @access public
	 * @since 1.0.0
	 *
	 * @param string  $string    Holds string arguments.
	 * @param string  $domain    Domain name.
	 * @param boolean $multiline Multiline support.
	 */
	public function register_string( $string, $domain, $multiline ) {

		if ( ! function_exists( 'pll_register_string' ) ) {
			return;
		}

		pll_register_string( $string, $string, $domain, $multiline );

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

		if ( ! function_exists( 'pll_translate_string' ) ) {
			return $string;
		}

		return pll_translate_string( $string, $lang );

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

		if ( ! function_exists( 'pll_languages_list' ) ) {
			return;
		}

		if ( in_array( $lang, pll_languages_list(), true ) ) {
			PLL()->curlang = PLL()->model->get_language( $lang );
		}

	}

	/**
	 * Change Polylang class to force auto translate in preview mode
	 *
	 * @access public
	 * @since 1.0.0
	 *
	 * @param string $class Class name to init.
	 * @return string
	 */
	public function change_context( $class ) {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( is_admin() && ! empty( $_GET['wpgb-preview'] ) && class_exists( 'PLL_Frontend' ) ) {

			// We hook when Polylang is init to instantiate PLL_Frontend_Auto_Translate class.
			add_action( 'pll_init', [ $this, 'auto_translate' ] );
			// Load frontend class to call auto_translate method later.
			$class = 'PLL_Frontend';

		}

		return $class;

	}

	/**
	 * Init PLL_Frontend_Auto_Translate class of Polylang
	 * Polylang only init this class on template_redirect hook which is not the case in preview mode
	 *
	 * @access public
	 * @since 1.0.0
	 *
	 * @param object $polylang Hold Polylang class.
	 */
	public function auto_translate( $polylang ) {

		if ( method_exists( $polylang, 'auto_translate' ) ) {
			$polylang->auto_translate();
		}

	}

	/**
	 * Check if it's a frontend ajax request from the plugin
	 *
	 * @access public
	 * @since 1.0.0
	 *
	 * @param boolean $is_ajax Is async request or not.
	 * @return boolean
	 */
	public function is_ajax_on_front( $is_ajax ) {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_GET['wpgb-ajax'] ) ) {

			// We set DOING_AJAX constant to force Polylang to translate async queries.
			// ! defined( 'DOING_AJAX' ) && define( 'DOING_AJAX', true );
			// We override is_ajax value of Polylang.
			$is_ajax = true;

		}

		return $is_ajax;

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

		return pll_get_post_language( $post_id );

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

		foreach ( (array) $term_ids as $term_id ) {

			$translations = pll_get_term_translations( $term_id );

			if ( ! empty( $lang ) ) {
				$translations = isset( $translations[ $lang ] ) ? $translations[ $lang ] : [];
			}

			$_term_ids = array_merge( $_term_ids, (array) $translations );

		}

		return array_values( $_term_ids );

	}
}
