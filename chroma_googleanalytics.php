<?php

/**
* Plugin Name:       Chroma Google Analytics
* Description:       Simple Google Analytics tracking integration
* Version:           1.3
* Author:            ChromaDot
* Author URI:        http://chromadot.com/
* License:           GPL-2.0+
* License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
* Text Domain:       chroma_googleanalytics
*/


// Delete chroma_googleanalytics option for testing
// delete_option("chroma_googleanalytics");

// var_dump funtion for debugging
if (!function_exists('dumper')) {
    function dumper($mixed = null) {
        echo '<pre class="var-dump">';
        var_dump($mixed);
        echo '</pre>';
        return null;
    }
}

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// The code that runs during plugin activation.
function activate_chroma_googleanalytics() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-chroma_googleanalytics-activator.php';
    chroma_googleanalytics_Activator::activate();
}

// The code that runs during plugin deactivation.
function deactivate_chroma_googleanalytics() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-chroma_googleanalytics-deactivator.php';
    chroma_googleanalytics_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_chroma_googleanalytics' );
register_deactivation_hook( __FILE__, 'deactivate_chroma_googleanalytics' );

// The core plugin class that is used to define internationalization,
// admin-specific hooks, and public-facing site hooks.
require plugin_dir_path( __FILE__ ) . 'includes/class-chroma_googleanalytics.php';


// Begins execution of the plugin.
function run_chroma_googleanalytics() {
    $plugin = new chroma_googleanalytics();
    $plugin->run();
}
add_action('plugins_loaded','run_chroma_googleanalytics');
