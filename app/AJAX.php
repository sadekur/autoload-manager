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
		if ( !isset( $_REQUEST[ 'nonce' ], $_REQUEST[ 'option_id' ], $_REQUEST[ 'autoload' ] ) || !wp_verify_nonce( $_REQUEST[ 'nonce' ] ) || !current_user_can( 'manage_options' ) ) {
			wp_die( 'No permission' );
		}
		$option_id 	= intval( $_REQUEST[ 'option_id' ] );
		$autoload 	= sanitize_text_field($_REQUEST[ 'autoload' ]);
		global $wpdb;
		$wpdb->update(
			$wpdb->options,
			[ 'autoload' => $autoload ],
			[ 'option_id' => $option_id ]
		);
		$autoload_options = get_transient( 'autoload_options_status' );
		if ( false === $autoload_options ) {
			$autoload_options = [];
		}
		$autoload_options[ $option_id ] = $autoload;
		set_transient( 'autoload_options_status', $autoload_options, 12 * HOUR_IN_SECONDS );
	
		wp_send_json_success( 'Autoload updated successfully' );
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


	public function load_options_data() {
		global $wpdb;
		$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
		$items_per_page = 50; // Number of items per page
		$offset = ($page - 1) * $items_per_page;
	
		$options = $wpdb->get_results($wpdb->prepare("SELECT option_id, option_name, autoload FROM {$wpdb->options} LIMIT %d, %d", $offset, $items_per_page));
		$total_items = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->options}");
		$total_pages = ceil($total_items / $items_per_page);
	
		ob_start();
		foreach ($options as $option) {
			$checked = ($option->autoload === 'yes' || $option->autoload === 'on') ? 'checked' : '';
			$statusClass = ($option->autoload === 'yes' || $option->autoload === 'on') ? 'status-on' : 'status-off';
			echo '<tr class="' . $statusClass . '">';
			echo '<td><input type="checkbox" class="row-select" data-option-id="' . esc_attr($option->option_id) . '"></td>';
			echo '<td>' . esc_html($option->option_id) . '</td>';
			echo '<td>' . esc_html($option->option_name) . '</td>';
			echo '<td>' . esc_html($option->autoload) . '</td>';
			echo '<td>
				<label class="switch">
					<input type="checkbox" class="autoload-manager-checkbox" data-option-id="' . esc_attr($option->option_id) . '" name="switches[' . esc_html($option->option_id) . ']" value="1" ' . $checked . '>
					<span class="slider round"></span>
				</label>
				</td>';
			echo '</tr>';
		}
		$table_content = ob_get_clean();
	
		wp_send_json_success([
			'table_content' => $table_content,
			'pagination' => [
				'total_pages' => $total_pages,
				'current_page' => $page
			]
		]);
	}
	
}