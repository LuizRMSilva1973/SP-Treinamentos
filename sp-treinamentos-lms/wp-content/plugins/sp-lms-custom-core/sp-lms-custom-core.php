<?php
/**
 * Plugin Name: SP LMS Custom Core
 * Plugin URI:  https://sptreinamentos.com.br
 * Description: Core functionality for SP Treinamentos LMS (Watchtime, Daily Limits, Certificates).
 * Version:     1.0.0
 * Author:      Paulo Roberto Bispo
 * Text Domain: sp-lms-core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

define( 'SP_LMS_CORE_PATH', plugin_dir_path( __FILE__ ) );
define( 'SP_LMS_CORE_URL', plugin_dir_url( __FILE__ ) );
define( 'SP_LMS_CORE_VERSION', '1.0.0' );

// Include Core Classes
require_once SP_LMS_CORE_PATH . 'includes/class-db-manager.php';
require_once SP_LMS_CORE_PATH . 'includes/class-watchtime-tracker.php';
require_once SP_LMS_CORE_PATH . 'includes/class-certificate-rules.php';
require_once SP_LMS_CORE_PATH . 'includes/class-id-card-generator.php';

/**
 * Main Plugin Class
 */
class SP_LMS_Custom_Core {

    public function __construct() {
        // Initialize Modules
        new SP_LMS_DB_Manager();
        new SP_LMS_Watchtime_Tracker();
        new SP_LMS_Certificate_Rules();
        new SP_LMS_ID_Card_Generator();

        // Register Activation Hook
        register_activation_hook( __FILE__, array( 'SP_LMS_DB_Manager', 'create_tables' ) );
        
        // Handle Multisite New Blog Creation
        add_action( 'wp_initialize_site', array( 'SP_LMS_DB_Manager', 'on_new_blog' ), 10, 2 );
    }
}

// Initialize Plugin
new SP_LMS_Custom_Core();
