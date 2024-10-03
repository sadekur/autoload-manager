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
		if (!wp_verify_nonce($_REQUEST['nonce']) || !current_user_can('manage_options')) {
			wp_die('No permission');
		}
		$option_id = intval($_REQUEST['option_id']);
		$autoload = sanitize_text_field($_REQUEST['autoload']);
		global $wpdb;
		$wpdb->update(
			$wpdb->options,
			['autoload' => $autoload],
			['option_id' => $option_id]
		);
		set_transient('autoload_option_' . $option_id, $autoload, 12 * HOUR_IN_SECONDS);
		wp_send_json_success('Autoload updated successfully');
	}

	public function toggle_bulk_autoload_option() {
		if (!wp_verify_nonce($_REQUEST['nonce']) || !current_user_can('manage_options')) {
			wp_die('No permission');
		}
		$option_ids = $_POST['option_ids'];
		$bulk_status = sanitize_text_field($_POST['autoload']);
		global $wpdb;

		foreach ($option_ids as $option_id) {
			$wpdb->update(
				$wpdb->options,
				['autoload' => $bulk_status],
				['option_id' => intval($option_id)]
			);
		}
		set_transient('autoload_bulk_option_' . $option_id, $bulk_status, 12 * HOUR_IN_SECONDS);

		wp_send_json_success('Autoload updated successfully');
	}
}