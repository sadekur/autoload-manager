<?php
namespace Codexpert\AutoloaManager\App;

use Codexpert\Plugin\Base;
use Codexpert\Plugin\Metabox;

/**
 * if accessed directly, exit.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @package Plugin
 * @subpackage Admin
 * @author Codexpert <hi@codexpert.io>
 */
class Admin extends Base {

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

	/**
	 * Internationalization
	 */
	public function i18n() {
		load_plugin_textdomain( 'autoload-manager', false, AUTOLOADMANAGER_DIR . '/languages/' );
	}

	/**
	 * Enqueue JavaScripts and stylesheets
	 */
	public function enqueue_scripts($hook) {
	// 	 if ($hook !== 'toplevel_page_autoload-options-list') {
    //     return;
    // }
		$min = defined( 'AUTOLOADMANAGER_DEBUG' ) && AUTOLOADMANAGER_DEBUG ? '' : '.min';
		
		wp_enqueue_style( $this->slug, plugins_url( "/assets/css/admin{$min}.css", AUTOLOADMANAGER_FILE ), '', $this->version, 'all' );
		wp_enqueue_script( $this->slug, plugins_url( "/assets/js/admin{$min}.js", AUTOLOADMANAGER_FILE ), [ 'jquery' ], $this->version, true );


	    $localized = [
	    	'homeurl'		=> get_bloginfo( 'url' ),
	    	'ajaxurl' 		=> admin_url('admin-ajax.php'),
	    	'asseturl'		=> AUTOLOADMANAGER_ASSETS,
	    	'ajaxurl'		=> admin_url( 'admin-ajax.php' ),
	    	'_wpnonce'		=> wp_create_nonce(),
	    	'api_base'		=> get_rest_url(),
	    	'rest_nonce'	=> wp_create_nonce( 'wp_rest' ),
	    ];
	    
	    wp_localize_script( $this->slug, 'AUTOLOADMANAGER', apply_filters( "{$this->slug}-localized", $localized ) );
	}

	public function admin_menu() {

		add_menu_page(
			__( 'Autoload Manager', 'autoload-manager' ),
			__( 'Autoload Manager', 'autoload-manager' ),
			'manage_options',
			'autoload-manager',
			 [$this, 'options_page'],
			'dashicons-wordpress',
			25
		);
	}

	public function options_page() {
		global $wpdb;
		// Load the initial 50 items directly
		$options = $wpdb->get_results("SELECT option_id, option_name, autoload FROM {$wpdb->options} LIMIT 50");
	
		echo '<div class="wrap"><h1>' . esc_html(get_admin_page_title()) . '</h1>';
		echo '<div>
			<button class="button" id="bulk-on">Bulk On</button>
			<button class="button" id="bulk-off">Bulk Off</button>
			<button class="button" id="filter-all">All</button>
			<button class="button" id="filter-on">On</button>
			<button class="button" id="filter-off">Off</button>
		</div>';
		echo '<table class="wp-list-table widefat fixed striped" id="autoloadOptionsTable">';
		echo '<thead>
			<tr>
				<th><input type="checkbox" id="select-all" /></th>
				<th>Option ID</th>
				<th>Option Name</th>
				<th>Autoload Status</th>
				<th>Action</th>
			</tr>
		</thead>';
		echo '<tbody>';
		foreach ($options as $option) {
			$checked = ($option->autoload === 'yes' || $option->autoload === 'on') ? 'checked' : '';
			$statusClass = ($option->autoload === 'yes' || $option->autoload === 'on') ? 'status-on' : 'status-off';
			echo '<tr class="' . $statusClass . '">';
			echo '<td><input type="checkbox" class="row-select" data-option-id="' . esc_attr($option->option_id) . '"></td>';
			echo '<td>' . esc_html($option->option_id) . '</td>';
			echo '<td>' . esc_html($option->option_name) . '</td>';
			echo '<td class="autoload-status">' . esc_html($option->autoload) . '</td>';
			echo '<td>
				<label class="switch">
					<input type="checkbox" class="autoload-manager-checkbox" data-option-id="' . esc_attr($option->option_id) . '" name="switches[' . esc_html($option->option_id) . ']" value="1" ' . $checked . '>
					<span class="slider round"></span>
				</label>
				</td>';
			echo '</tr>';
		}
		echo '</tbody></table>';
	
		// Pagination controls
		echo '<div id="pagination-controls" class="tablenav">
			<div class="tablenav-pages">
				<button class="button" id="prev-page">Previous</button>
				<button class="button" id="next-page">Next</button>
			</div>
		</div>';
		echo '</div>';
	}

	public function action_links( $links ) {
		$this->admin_url = admin_url( 'admin.php' );

		$new_links = [
			'settings'	=> sprintf( '<a href="%1$s">' . __( 'Settings', 'autoload-manager' ) . '</a>', add_query_arg( 'page', $this->slug, $this->admin_url ) )
		];
		
		return array_merge( $new_links, $links );
	}
	public function modal() {
		echo '
		<div id="autoload-manager-modal" style="display: none">
			<img id="autoload-manager-modal-loader" src="' . esc_attr( AUTOLOADMANAGER_ASSETS . '/img/loader.gif' ) . '" />
		</div>';
	}
}
