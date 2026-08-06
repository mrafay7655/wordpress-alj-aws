<?php
/**
 * Uninstall
 *
 * @package   WP Grid Builder - Multilingual
 * @author    Loïc Blascos
 * @copyright 2019-2022 Loïc Blascos
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_transient( 'wpgb_multilingual_strings' );
