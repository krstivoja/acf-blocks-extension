<?php
/*
 * Plugin Name:       ACF Blocks Extension
 * Plugin URI:        https://example.com/plugins/the-basics/
 * Description:       Handle the basics with this plugin.
 * Version:           1.10.3
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            John Smith
 * Author URI:        https://author.example.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://example.com/my-plugin/
 * Text Domain:       acf-blocks-extension
 * Domain Path:       /languages
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Hook into plugins_loaded to ensure all plugins are loaded
add_action('plugins_loaded', 'initialize_acf_block_location_extension');

function initialize_acf_block_location_extension()
{
    // Include the plugin.php file if not already included
    if (!function_exists('is_plugin_active')) {
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');
    }

    // Check if ACF or ACF PRO is active
    if (is_plugin_active('advanced-custom-fields/acf.php') || is_plugin_active('advanced-custom-fields-pro/acf.php')) {
        // Define the custom location class
        class ACF_Location_Block extends ACF_Location
        {

            // Constructor
            public function __construct()
            {
                $this->name     = 'block';
                $this->label    = __('Block', 'acf-block-location');
                $this->category = 'forms'; // Change as needed, e.g., 'post', 'page', etc.
            }

            // Match logic for the location rule
            public function match($rule, $screen, $field_group)
            {
                // Define match logic
                // Example: return true if the current screen is a specific block type
                return true;
            }

            // Returns an array of possible values for this rule type
            // public function get_values( $rule ) {
            //     // Define values for this location rule
            //     return [
            //         'block_type_1' => 'Block Type 1',
            //         'block_type_2' => 'Block Type 2',
            //         // ... Add more block types as needed
            //     ];
            // }

            // Returns an array of possible values for this rule type
            public function get_values($rule)
            {
                $all_blocks = WP_Block_Type_Registry::get_instance()->get_all_registered();
                $specific_domain_blocks = [];

                // Specify the domain you want to filter by
                $specific_domain = 'acf/';

                foreach ($all_blocks as $block_name => $block) {
                    if (strpos($block_name, $specific_domain) === 0) {
                        // Add the block to the array if it matches the specific domain
                        $specific_domain_blocks[$block_name] = $block->title;
                    }
                }

                return $specific_domain_blocks;
            }


            // Ensures this method is static if it's static in the parent class
            public static function get_operators($rule)
            {
                // Define available operators
                return [
                    '==' => __("is equal to", 'acf-block-location'),
                    '!=' => __("is not equal to", 'acf-block-location')
                ];
            }
        }

        // Register the custom location class with ACF
        add_action('init', 'register_acf_location_block');
    } else {
        // ACF is not active, handle this case (e.g., show an admin notice)
        add_action('admin_notices', 'acf_block_location_admin_notice');
    }
}

function register_acf_location_block()
{
    acf_register_location_type('ACF_Location_Block');
}

function acf_block_location_admin_notice()
{
?>
    <div class="notice notice-warning">
        <p><?php _e('ACF Block Location Extension requires Advanced Custom Fields or ACF PRO to be active.', 'acf-block-location'); ?></p>
    </div>
<?php
}
