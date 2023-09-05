<?php

/**
 * Plugin Name:       Schools Manager
 * Plugin URI:        https://github.com/helsingborg-stad/api-schools-manager
 * Description:       Creates a api that may be used to manage schools
 * Version: 0.1.7
 * Author:            Thor Brink @ Helsingborg Stad
 * Author URI:        https://github.com/helsingborg-stad
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       api-schools-manager
 * Domain Path:       /languages
 */

// Protect agains direct file access
if (!defined('WPINC')) {
    die;
}

define('SCHOOLS_MANAGER_PATH', plugin_dir_path(__FILE__));
define('SCHOOLS_MANAGER_URL', plugins_url('', __FILE__));
define('SCHOOLS_MANAGER_TEMPLATE_PATH', SCHOOLS_MANAGER_PATH . 'templates/');
const ASM_TEXT_DOMAIN = 'api-schools-manager';

load_plugin_textdomain(ASM_TEXT_DOMAIN, false, dirname(plugin_basename(__FILE__)) . '/languages');

require_once SCHOOLS_MANAGER_PATH . 'Public.php';

// Register the autoloader
if (file_exists(SCHOOLS_MANAGER_PATH . 'vendor/autoload.php')) {
    require SCHOOLS_MANAGER_PATH . '/vendor/autoload.php';
}

// Acf auto import and export
add_action('acf/init', function () {
    $acfExportManager = new \AcfExportManager\AcfExportManager();
    $acfExportManager->setTextdomain('api-schools-manager');
    $acfExportManager->setExportFolder(SCHOOLS_MANAGER_PATH . 'source/php/AcfFields/');
    $acfExportManager->autoExport(array());
    $acfExportManager->import();
});

// Start application
new SchoolsManager\App();
