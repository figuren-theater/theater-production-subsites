<?php
/**
 * Register the theater-production-subsites module to altis
 *
 * @package           figuren-theater/theater-production-subsites
 * @author            figuren.theater
 * @copyright         2024 figuren.theater
 * @license           GPL-3.0+
 *
 * @wordpress-plugin
 * Plugin Name:       Theater Production Subsites
 * Plugin URI:        https://github.com/figuren-theater/theater-production-subsites
 * Description:       Allows to create sub-sites of productions within the figuren.theater WordPress Multisite network.
 * Version:           0.1.1-alpha
 * Requires at least: 6.0
 * Requires PHP:      7.1
 * Author:            figuren.theater
 * Author URI:        https://figuren.theater
 * Text Domain:       theater-production-subsites
 * Domain Path:       /languages
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Update URI:        https://github.com/figuren-theater/theater-production-subsites
 */

namespace Figuren_Theater\Production_Subsites;

const DIRECTORY  = __DIR__;
const PT_SUPPORT = 'hierachical-sub-post-type';
const SUB_SUFFIX = '_sub';



/**
 * REMOVE
 *
 * @todo https://github.com/figuren-theater/theater-production-subsites/issues/5 Add composer autoloading strategy
 */
require_once DIRECTORY . '/inc/admin-ui/namespace.php'; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingCustomConstant
require_once DIRECTORY . '/inc/block-loading/namespace.php'; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingCustomConstant
require_once DIRECTORY . '/inc/pattern-loading/namespace.php'; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingCustomConstant
require_once DIRECTORY . '/inc/registration/namespace.php'; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingCustomConstant
require_once DIRECTORY . '/inc/urls/namespace.php'; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingCustomConstant
require_once DIRECTORY . '/inc/namespace.php'; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingCustomConstant

add_action( 'plugins_loaded', __NAMESPACE__ . '\\register' ); // 'plugins_loaded' (or earlier) is needed for the "Theater for WordPress" integration to work.
