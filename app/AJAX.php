<?php
namespace Codexpert\AutoloaManager\App;

use Codexpert\Plugin\Base;

/**
 * if accessed directly, exit.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @package Plugin
 * @subpackage AJAX
 * @author Codexpert <hi@codexpert.io>
 */
class AJAX extends Base {

	public $plugin;
	
	public $slug;

	public $name;

	public $version;

	/**
	 * Constructor function
	 */
	public function __construct() {
		$this->plugin	= AUTOLOADMANAGER;
		$this->slug		= $this->plugin['TextDomain'];
		$this->name		= $this->plugin['Name'];
		$this->version	= $this->plugin['Version'];
	}

	public function some_callback() {
		
		$response = [
			'status'	=> 0,
			'message'	=> __( 'Unauthorized', 'autoload-manager' ),
		];

		if( ! wp_verify_nonce( $_POST['_wpnonce'] ) ) {
			wp_send_json_success( $response );
		}
	}

	public function toggle_autoload_option() {
	    // check_ajax_referer('autoload_option_nonce', 'nonce');

	    if (!current_user_can('manage_options')) {
	        wp_send_json_error('Permission denied');
	    }

	    $option_name = isset($_POST['option_name']) ? sanitize_text_field($_POST['option_name']) : '';
	    $autoload_value = isset($_POST['autoload']) ? sanitize_text_field($_POST['autoload']) : '';

	    if (empty($option_name) || ($autoload_value !== 'yes' && $autoload_value !== 'no')) {
	        wp_send_json_error('Invalid data');
	    }

	    global $wpdb;
	    $updated = $wpdb->update(
	        $wpdb->options,
	        ['autoload' => $autoload_value],
	        ['option_name' => $option_name]
	    );

	    if ($updated !== false) {
	        // Clear the old transient
	        delete_transient('autoload_options_list');
	        
	        // Query new data and update the transient with new values
	        $options_data = $wpdb->get_results("SELECT option_name, autoload FROM {$wpdb->options}");
	        set_transient('autoload_options_list', $options_data, 12 * HOUR_IN_SECONDS);

	        wp_send_json_success('Autoload value updated');
	    } else {
	        wp_send_json_error('Failed to update');
	    }
	}

}