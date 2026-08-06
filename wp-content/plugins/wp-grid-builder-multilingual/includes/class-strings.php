<?php
/**
 * Strings
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
 * Handle translatebale strings
 *
 * @class WP_Grid_Builder_Multilingual\Includes\Strings
 * @since 1.0.0
 */
final class Strings {

	/**
	 * Holds strings to translate
	 *
	 * @since 1.0.0
	 * @var array
	 */
	public $strings = [];

	/**
	 * Holds registered strings
	 *
	 * @since 1.0.0
	 * @var array
	 */
	public $registry = [];

	/**
	 * Constructor
	 *
	 * @access public
	 * @since 1.0.0
	 */
	public function __construct() {

		add_action( 'admin_init', [ $this, 'register_strings' ] );

		// Clear transient on save.
		add_action( 'wp_grid_builder/save/grid', [ $this, 'unregister_strings' ] );
		add_action( 'wp_grid_builder/save/card', [ $this, 'unregister_strings' ] );
		add_action( 'wp_grid_builder/save/facet', [ $this, 'unregister_strings' ] );

		// Clear transient on bulk actions.
		foreach ( [ 'grids', 'cards', 'facets' ] as $type ) {

			add_action( 'wp_grid_builder/delete/' . $type, [ $this, 'unregister_strings' ] );
			add_action( 'wp_grid_builder/import/' . $type, [ $this, 'unregister_strings' ] );
			add_action( 'wp_grid_builder/duplicate/' . $type, [ $this, 'unregister_strings' ] );

		}

	}

	/**
	 * Register translatable strings
	 *
	 * @access public
	 * @since 1.0.0
	 */
	public function register_strings() {

		$this->get_registry();
		$this->process_strings();

		array_map( [ $this, 'register_string' ], $this->strings );

	}

	/**
	 * Register translatable string
	 *
	 * @access public
	 * @since 1.0.0
	 *
	 * @param array $string Hols string arguments.
	 */
	public function register_string( $string = [] ) {

		do_action( 'wp_grid_builder_i18n/register_string', $string['value'], $string['domain'], $string['multiline'] );

	}

	/**
	 * Unregister translatable strings
	 *
	 * @access public
	 * @since 1.0.0
	 */
	public function unregister_strings() {

		$old_strings = get_transient( 'wpgb_multilingual_strings' );
		delete_transient( 'wpgb_multilingual_strings' );

		// Register new strings.
		$this->register_strings();

		// If no change occured.
		if ( $this->strings === $old_strings || empty( $old_strings ) ) {
			return;
		}

		array_map( [ $this, 'unregister_string' ], $old_strings );

	}

	/**
	 * Unregister translatable string
	 *
	 * @access public
	 * @since 1.0.0
	 *
	 * @param array $old_string Hols old string arguments.
	 */
	public function unregister_string( $old_string = [] ) {

		// If string not anymore registered.
		if ( ! in_array( $old_string, $this->strings, true ) ) {
			do_action( 'wp_grid_builder_i18n/unregister_string', $old_string['value'], $old_string['domain'] );
		}

	}

	/**
	 * Get registered strings to translate
	 *
	 * @access public
	 * @since 1.0.0
	 */
	public function get_registry() {

		$this->registry = [
			'grid'  => apply_filters( 'wp_grid_builder_i18n/grid/register_strings', [] ),
			'card'  => apply_filters( 'wp_grid_builder_i18n/card/register_strings', [] ),
			'facet' => apply_filters( 'wp_grid_builder_i18n/facet/register_strings', [] ),
		];

	}

	/**
	 * Query all settings to get translatable strings
	 *
	 * @access public
	 * @since 1.0.0
	 */
	public function query_settings() {

		global $wpdb;

		return (array) $wpdb->get_results(
			"SELECT settings as strings, context
			from (
				select settings, 'facet' as context from {$wpdb->prefix}wpgb_facets
				union all
				select settings, 'grid' as context from {$wpdb->prefix}wpgb_grids
				union all
				select layout, 'card' as context from {$wpdb->prefix}wpgb_cards
			) a"
		);

	}

	/**
	 * Process strings to translate from settings
	 *
	 * @access public
	 * @since 1.0.0
	 */
	public function process_strings() {

		$this->strings = get_transient( 'wpgb_multilingual_strings' );

		if ( ! empty( $this->strings ) ) {
			return;
		}

		$this->strings = [];

		foreach ( $this->query_settings() as $args ) {

			$strings = json_decode( $args->strings, true );
			$this->filter_strings( $strings, $args->context );

		}

		set_transient( 'wpgb_multilingual_strings', $this->strings );

	}

	/**
	 * Recursively filter strings.
	 *
	 * @access public
	 * @since 1.0.0
	 *
	 * @param array  $strings Hols translatable strings.
	 * @param string $context String context.
	 */
	public function filter_strings( $strings, $context ) {

		foreach ( $strings as $slug => $string ) {

			if ( is_array( $string ) ) {
				$this->filter_strings( $string, $context );
			} else {
				$this->filter_string( $context, $slug, $string );
			}
		}

	}

	/**
	 * Filter translatable string
	 *
	 * @access public
	 * @since 1.0.0
	 *
	 * @param string $context String context.
	 * @param string $slug    String slug.
	 * @param string $value   String value.
	 */
	public function filter_string( $context = '', $slug = '', $value = '' ) {

		// If it's not a valid string.
		if ( ! is_scalar( $value ) || empty( trim( $value ) ) ) {
			return;
		}

		// If it's not a translatable string.
		if ( ! isset( $this->registry[ $context ][ $slug ] ) ) {
			return;
		}

		// We merge default registry args to string args.
		$string = wp_parse_args(
			$this->registry[ $context ][ $slug ],
			[
				'domain'    => 'Gridbuilder ᵂᴾ',
				'context'   => $context,
				'value'     => $value,
				'slug'      => $slug,
				'multiline' => false,
			]
		);

		// Skip, if the string is already registered.
		if ( in_array( $string, $this->strings, true ) ) {
			return;
		}

		$this->strings[] = $string;

	}
}
