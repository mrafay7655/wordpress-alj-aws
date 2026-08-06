<?php
/**
 * Helpers
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
 * Helpers
 *
 * @class WP_Grid_Builder_Multilingual\Includes\Helpers
 * @since 1.0.0
 */
trait Helpers {

	/**
	 * Get current language from Polylang or WPML
	 *
	 * @access public
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_current_language() {

		return $this->wpml_current_language() ?: $this->pll_current_language();

	}

	/**
	 * Get default language from Polylang or WPML
	 *
	 * @access public
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_default_language() {

		return $this->wpml_default_language() ?: $this->pll_default_language();

	}

	/**
	 * Get current language from WPML
	 *
	 * @access public
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function wpml_current_language() {

		global $sitepress;

		if ( empty( $sitepress ) || ! method_exists( $sitepress, 'get_current_language' ) ) {
			return '';
		}

		return $sitepress->get_current_language();

	}

	/**
	 * Get current language from Polylang
	 *
	 * @access public
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function pll_current_language() {

		if ( ! function_exists( 'pll_current_language' ) ) {
			return '';
		}

		return pll_current_language();

	}

	/**
	 * Get default language from WPML
	 *
	 * @access public
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function wpml_default_language() {

		global $sitepress;

		if ( empty( $sitepress ) || ! method_exists( $sitepress, 'get_default_language' ) ) {
			return '';
		}

		return $sitepress->get_default_language();

	}

	/**
	 * Get default language from Polylang
	 *
	 * @access public
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function pll_default_language() {

		if ( ! function_exists( 'pll_default_language' ) ) {
			return '';
		}

		return pll_default_language();

	}

	/**
	 * Get translatable custom field
	 *
	 * @access public
	 * @since 1.1.0
	 *
	 * @param array $facet Holds Facet settings.
	 * @return string
	 */
	public function get_translatable_custom_field( $facet ) {

		if ( ! isset( $facet['source'] ) ) {
			return '';
		}

		return (
			$this->is_translatable_acf_field( $facet ) ||
			$this->is_translatable_meta_box_field( $facet )
		) ? $facet['source'] : '';

	}

	/**
	 * Check if it is an ACF translatable field
	 *
	 * @access public
	 * @since 1.1.0
	 *
	 * @param array $facet Holds Facet settings.
	 * @return string
	 */
	public function is_translatable_acf_field( $facet ) {

		$field = explode( '/acf/', $facet['source'] );

		if ( empty( $field[1] ) || ! function_exists( 'get_field_object' ) ) {
			return false;
		}

		$field_names = explode( '/', $field[1] );
		$settings    = get_field_object( end( $field_names ) );

		return ! empty( $settings['type'] ) && 'true_false' === $settings['type'];

	}

	/**
	 * Check if it is a Meta Box translatable field
	 *
	 * @access public
	 * @since 1.1.0
	 *
	 * @param array $facet Holds Facet settings.
	 * @return string
	 */
	public function is_translatable_meta_box_field( $facet ) {

		$field = explode( '/meta-box/', $facet['source'] );

		if ( empty( $field[1] ) || ! function_exists( 'rwmb_get_field_settings' ) ) {
			return false;
		}

		$field_names = explode( '/', $field[1] );
		$settings    = rwmb_get_field_settings( end( $field_names ) );

		return ! empty( $settings['type'] ) && in_array( $settings['type'], [ 'checkbox', 'switch' ], true );

	}
}
