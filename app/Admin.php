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
        $options = $wpdb->get_results("SELECT option_id, option_name, option_value, autoload FROM {$wpdb->options}");

        echo '<div class="wrap"><h1>' . esc_html(get_admin_page_title()) . '</h1>';
        echo '<table class="wp-list-table widefat fixed striped">';
			echo '<thead>
				<tr>
					<th>Option ID</th>
					<th>Option Name</th>
					<th>Autoload Status</th>
					<th>Action</th>
				</tr>
			</thead>';
        echo '<tbody>';

        foreach ($options as $option) {
            $checked = ($option->autoload === 'yes' || $option->autoload === 'on') ? 'checked' : '';

            echo '<tr>';
            echo '<td>' . esc_html($option->option_id) . '</td>';
            echo '<td>' . esc_html($option->option_name) . '</td>';
            echo '<td>' . esc_html($option->autoload) . '</td>';
            echo '<td><label class="switch"><input type="checkbox" ' . $checked . ' onchange="toggleAutoload(' . esc_attr($option->option_id) . ', this.checked)"><span class="slider round"></span></label></td>';
            echo '</tr>';
        }

        echo '</tbody></table></div>';

        $this->add_toggle_script();
    }

    private function add_toggle_script() {
        ?>
        <script type="text/javascript">
        function toggleAutoload(optionId, checked) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', ajaxurl, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (this.status === 200) {
                    console.log('Autoload updated!');
                    // Optionally, refresh the page to reflect the change immediately
                    location.reload();
                }
            };
            xhr.send('action=toggle_autoload&option_id=' + optionId + '&autoload=' + (checked ? 'yes' : 'no'));
        }
        </script>
        <?php
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
