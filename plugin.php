<?php
/**
 * Register the theater-production-subsites module to altis
 *
 * @package           figuren-theater/theater-production-subsites
 * @author            figuren.theater
 * @copyright         2023 figuren.theater
 * @license           GPL-3.0+
 *
 * @wordpress-plugin
 * Plugin Name:       figuren.theater | theater_production_subsites
 * Plugin URI:        https://github.com/figuren-theater/theater-production-subsites
 * Description:       ... like the figuren.theater WordPress Multisite network.
 * Version:           0.1.0-alpha
 * Requires at least: 6.0
 * Requires PHP:      7.1
 * Author:            figuren.theater
 * Author URI:        https://figuren.theater
 * Text Domain:       figurentheater
 * Domain Path:       /languages
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Update URI:        https://github.com/figuren-theater/theater-production-subsites
 */

namespace Figuren_Theater\Production_Subsites;

const DIRECTORY = __DIR__;


/**
 * REMOVE
 *
 * @todo Add composer autoloading strategy
 */
require_once DIRECTORY . '/inc/block-loading/namespace.php'; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingCustomConstant
require_once DIRECTORY . '/inc/pattern-loading/namespace.php'; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingCustomConstant
require_once DIRECTORY . '/inc/registration/namespace.php'; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingCustomConstant
require_once DIRECTORY . '/inc/namespace.php'; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingCustomConstant

add_action( 'init', __NAMESPACE__ . '\\register', 0 );
