<?php
/**
 * Translate
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
 * Handle strings translation
 *
 * @class WP_Grid_Builder_Multilingual\Includes\Translate
 * @since 1.0.0
 */
class Translate {

	use Helpers;

	/**
	 * Constructor
	 *
	 * @access public
	 * @since 1.0.0
	 */
	public function __construct() {

		add_filter( 'wp_grid_builder/grid/settings', [ $this, 'translate_settings' ], PHP_INT_MAX );
		add_filter( 'wp_grid_builder/card/settings', [ $this, 'translate_settings' ], PHP_INT_MAX );
		add_filter( 'wp_grid_builder/facet/settings', [ $this, 'translate_settings' ], PHP_INT_MAX );
		add_filter( 'wp_grid_builder/grid/query_args', [ $this, 'prepare_tax_query' ], PHP_INT_MAX );
		add_filter( 'wp_grid_builder/indexer/term_query_args', [ $this, 'translate_terms' ], PHP_INT_MAX, 2 );
		add_filter( 'wp_grid_builder/facet/choices', [ $this, 'translate_facet_strings' ], 0, 2 );
		add_filter( 'wp_grid_builder/facet/choices', [ $this, 'translate_acf_choices' ], 0, 2 );

	}

	/**
	 * Get registry from context (grid/card/facet)
	 *
	 * @access public
	 * @since 1.0.0
	 *
	 * @param string $context Context of the registry.
	 * @return array
	 */
	public function get_registry( $context ) {

		return apply_filters( 'wp_grid_builder_i18n/' . $context . '/register_strings', [] );

	}

	/**
	 * Translate strings recursively
	 *
	 * @access public
	 * @since 1.0.0
	 *
	 * @param array  $strings  Holds strings to translate.
	 * @param string $context  String context.
	 * @param array  $registry Holds registered string to translate.
	 * @param string $lang     Language code.
	 * @return array
	 */
	public function translate_strings( $strings, $context, $registry, $lang ) {

		foreach ( $strings as $key => $string ) {

			if ( is_array( $string ) ) {
				$strings[ $key ] = $this->translate_strings( $string, $context, $registry, $lang );
			} elseif ( isset( $registry[ $key ] ) ) {

				// Set a default domain if missing.
				$domain = ! empty( $registry[ $key ]['domain'] ) ? $registry[ $key ]['domain'] : 'Gridbuilder ᵂᴾ';
				// Apply filters to translate from Polylang or WPML.
				$new_string = apply_filters( 'wp_grid_builder_i18n/translate_string', $string, $key, $context, $domain, $lang );

				if ( $new_string !== $string ) {
					$strings[ $key ] = $new_string;
				}
			}
		}

		return $strings;

	}

	/**
	 * Translate grid/card/facet settings
	 *
	 * @access public
	 * @since 1.0.0
	 *
	 * @param array $settings Holds grid settings.
	 * @return array
	 */
	public function translate_settings( $settings ) {

		$context  = explode( '/', current_filter() )[1];
		$registry = $this->get_registry( $context );
		$language = $this->get_current_language();

		return $this->translate_strings( $settings, $context, $registry, $language );

	}

	/**
	 * Prepare tax_query (convert term taxonomy ids)
	 * WPML and Polylang do not handle translation if taxonomy parameter is missing in tax_query...
	 * However, taxonomy parameter is optional in WP_Tax_QUery when field=term_taxonomy_id
	 *
	 * @access public
	 * @since 1.0.0
	 *
	 * @param array $query_args Holds query arguments.
	 * @return array
	 */
	public function prepare_tax_query( $query_args ) {

		$current = $this->get_current_language();
		$default = $this->get_default_language();

		if ( empty( $query_args['tax_query'] ) ) {
			return $query_args;
		}

		unset( $query_args['lang'] );

		// Do not translate in this case.
		if ( $current === $default ) {
			return $query_args;
		}

		$query_args['tax_query'] = array_map(
			[ $this, 'convert_tax_query_clauses' ],
			(array) $query_args['tax_query']
		);

		return $query_args;

	}

	/**
	 * Recursively process each clause of tax_query
	 *
	 * @access public
	 * @since 1.0.0
	 *
	 * @param array $clauses Holds tax_query clauses.
	 * @return array
	 */
	public function convert_tax_query_clauses( $clauses ) {

		if ( ! is_array( $clauses ) ) {
			return $clauses;
		}

		if ( ! isset( $clauses['terms'] ) ) {
			return array_map( __METHOD__, $clauses );
		}

		if ( isset( $clauses['field'] ) && ! in_array( $clauses['field'], [ 'term_id', 'term_taxonomy_id' ], true ) ) {
			return $clauses;
		}

		return $this->convert_tax_query_clause( $clauses );

	}

	/**
	 * Convert tax_query clause to add missing taxonomy parameter
	 *
	 * @access public
	 * @since 1.0.0
	 *
	 * @param array $clause Holds tax_query clause.
	 * @return array
	 */
	public function convert_tax_query_clause( $clause ) {

		$clauses = [];

		foreach ( $this->get_term_terms( $clause['terms'] ) as $term ) {

			$clauses[] = wp_parse_args(
				[
					'field'    => 'term_id',
					'terms'    => $this->get_terms_translations( $term->term_id, $term->taxonomy, $this->get_current_language() ),
					'taxonomy' => $term->taxonomy,
				],
				$clause
			);
		}

		if ( ! empty( $clause['relation'] ) ) {
			$clauses['relation'] = $clause['relation'];
		}

		return $clauses;

	}

	/**
	 * Query terms from term taxonomy ids.
	 *
	 * @access public
	 * @since 1.0.0
	 *
	 * @param array $term_taxonomy_ids Holds term taxonomy ids.
	 * @return array
	 */
	public function get_term_terms( $term_taxonomy_ids ) {

		$terms = get_terms(
			[
				'term_taxonomy_id' => $term_taxonomy_ids,
				'lang'             => $this->get_default_language(),
			]
		);

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return [];
		}

		return $terms;

	}

	/**
	 * Translate taxonomy terms
	 *
	 * @access public
	 * @since 1.0.6
	 *
	 * @param array   $query_args Holds WP_Term_Query arguments.
	 * @param integer $object_id Current object ID to translate.
	 * @return array
	 */
	public function translate_terms( $query_args, $object_id ) {

		$clauses = [];

		if ( ! empty( $query_args['include'] ) ) {
			$clauses[] = 'include';
		} elseif ( ! empty( $query_args['exclude'] ) ) {
			$clauses[] = 'exclude';
		}

		if ( ! empty( $query_args['parent'] ) ) {
			$clauses[] = 'parent';
		}

		if ( empty( $clauses ) ) {
			return $query_args;
		}

		$lang = $this->get_post_language( $object_id );

		if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
			$this->switch_language( $lang );
		}

		foreach ( $clauses as $clause ) {

			$term_translation_ids  = $this->get_terms_translations( (array) $query_args[ $clause ], $query_args['taxonomy'], $lang );
			$query_args[ $clause ] = 'parent' === $clause ? current( $term_translation_ids ) : $term_translation_ids;

		}

		return $query_args;

	}

	/**
	 * Translate strings from facet choices
	 *
	 * @access public
	 * @since 1.1.0
	 *
	 * @param array $choices Holds Facet choices.
	 * @param array $facet   Holds Facet settings.
	 * @return array
	 */
	public function translate_facet_strings( $choices, $facet ) {

		$sources = [
			'features'      => 'post_meta/_wpgb_featured_product',
			'stock_status'  => 'post_meta/_stock_status',
			'on_sale'       => 'post_meta/_on_sale',
			'post_type'     => 'post_field/post_type',
			'user_roles'    => 'user_field/roles',
			'term_taxonomy' => 'term_field/taxonomy',
			'custom_field'  => $this->get_translatable_custom_field( $facet ),
		];

		if ( empty( $facet['source'] ) || ! in_array( $facet['source'], $sources, true ) ) {
			return $choices;
		}

		// phpcs:disable WordPress.WP.I18n.TextDomainMismatch
		return array_map(
			function( $choice ) use ( $facet, $sources ) {

				switch ( $facet['source'] ) {
					case $sources['features']:
						if ( '1' === $choice->facet_value ) {
							$choice->facet_name = __( 'Featured', 'wp-grid-builder' );
						}
						break;
					case $sources['stock_status']:
						$choice->facet_name = $choice->facet_value ? __( 'In Stock', 'wp-grid-builder' ) : __( 'Out of Stock', 'wp-grid-builder' );
						break;
					case $sources['on_sale']:
						if ( '1' === $choice->facet_value ) {
							$choice->facet_name = __( 'On Sale', 'wp-grid-builder' );
						}
						break;
					case $sources['post_type']:
						$type = get_post_type_object( $choice->facet_value );

						if ( isset( $type->labels->name ) ) {
							$choice->facet_name = $type->labels->name;
						}
						break;
					case $sources['user_roles']:
						$roles = wp_roles();
						$roles = $roles->get_names();
						$roles = array_map( 'translate_user_role', $roles );

						if ( isset( $roles[ $choice->facet_value ] ) ) {
							$choice->facet_name = $roles[ $choice->facet_value ];
						}
						break;
					case $sources['term_taxonomy']:
						$taxonomy = get_taxonomy( $choice->facet_value );

						if ( ! empty( $taxonomy->label ) ) {
							$choice->facet_name = $taxonomy->label;
						}
						break;
					case $sources['custom_field']:
						if ( in_array( $choice->facet_value, [ '1', 'Yes', 'Oui' ], true ) ) {
							$choice->facet_name = __( 'Yes', 'wp-grid-builder' );
						} elseif ( in_array( $choice->facet_value, [ '0', 'No', 'Non' ], true ) ) {
							$choice->facet_name = __( 'No', 'wp-grid-builder' );
						}
						break;
				}

				return $choice;

			},
			$choices
		);
		// phpcs:enable WordPress.WP.I18n.TextDomainMismatch

	}

	/**
	 * Translate ACF choices
	 *
	 * @access public
	 * @since 1.1.1
	 *
	 * @param array $choices Holds Facet choices.
	 * @param array $facet   Holds Facet settings.
	 * @return array
	 */
	public function translate_acf_choices( $choices, $facet ) {

		$field = explode( '/acf/', $facet['source'] );

		if ( empty( $field[1] ) || ! function_exists( 'get_field_object' ) ) {
			return $choices;
		}

		$field_names = explode( '/', $field[1] );
		$field_name  = end( $field_names );

		if ( 'field_' !== substr( $field_name, 0, 6 ) ) {
			return $choices;
		}

		// Get object from field key with untranslated choices.
		$acf_field = get_field_object( $field_name );

		if ( empty( $acf_field ) ) {
			return $choices;
		}

		// Get object from field name with translated choices.
		$acf_field = get_field_object( $acf_field['name'] );

		if ( empty( $acf_field ) || empty( $acf_field['choices'] ) ) {
			return $choices;
		}

		$acf_choices = $acf_field['choices'];

		return array_map(
			function( $choice ) use ( $acf_choices ) {

				if ( ! empty( $acf_choices[ $choice->facet_value ] ) ) {
					$choice->facet_name = $acf_choices[ $choice->facet_value ];
				}

				return $choice;

			},
			$choices
		);
	}
}
