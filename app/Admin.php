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
	public function enqueue_scripts() {
		$min = defined( 'AUTOLOADMANAGER_DEBUG' ) && AUTOLOADMANAGER_DEBUG ? '' : '.min';
		
		wp_enqueue_style( $this->slug, plugins_url( "/assets/css/admin{$min}.css", AUTOLOADMANAGER_FILE ), '', $this->version, 'all' );
		wp_enqueue_script( $this->slug, plugins_url( "/assets/js/admin{$min}.js", AUTOLOADMANAGER_FILE ), [ 'jquery' ], $this->version, true );

	    wp_enqueue_script( "{$this->slug}-react", plugins_url( 'spa/admin/build/index.js', AUTOLOADMANAGER_FILE ), [ 'wp-element' ], '1.0.0', true );

	    $localized = [
	    	'homeurl'		=> get_bloginfo( 'url' ),
	    	'adminurl'		=> admin_url(),
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
			function(){},
			'dashicons-wordpress',
			25
		);

		// add_submenu_page(
		// 	'autoload-manager',
		// 	__( 'Help', 'autoload-manager' ),
		// 	__( 'Help', 'autoload-manager' ),
		// 	'manage_options',
		// 	'autoload-manager-help',
		// 	function() {
		// 		printf( '<div id="autoload-manager_help"><p>%s</p></div>', __( 'Loading..', 'autoload-manager' ) );
		// 	}
		// );

		// add_submenu_page(
		// 	'autoload-manager',
		// 	__( 'License', 'autoload-manager' ),
		// 	__( 'License', 'autoload-manager' ),
		// 	'manage_options',
		// 	'autoload-manager-license',
		// 	function() {
		// 		printf( '<div id="autoload-manager_license"><p>%s</p></div>', __( 'Loading..', 'autoload-manager' ) );
		// 	}
		// );
	}

	public function action_links( $links ) {
		$this->admin_url = admin_url( 'admin.php' );

		$new_links = [
			'settings'	=> sprintf( '<a href="%1$s">' . __( 'Settings', 'autoload-manager' ) . '</a>', add_query_arg( 'page', $this->slug, $this->admin_url ) )
		];
		
		return array_merge( $new_links, $links );
	}

	public function plugin_row_meta( $plugin_meta, $plugin_file ) {
		
		if ( $this->plugin['basename'] === $plugin_file ) {
			$plugin_meta['help'] = '<a href="https://help.codexpert.io/" target="_blank" class="cx-help">' . __( 'Help', 'autoload-manager' ) . '</a>';
		}

		return $plugin_meta;
	}

	public function update_cache( $post_id, $post, $update ) {
		wp_cache_delete( "alm_{$post->post_type}", 'alm' );
	}

	public function footer_text( $text ) {
		if( get_current_screen()->parent_base != $this->slug ) return $text;

		return sprintf( __( 'If you like <strong>%1$s</strong>, please <a href="%2$s" target="_blank">leave us a %3$s rating</a> on WordPress.org! It\'d motivate and inspire us to make the plugin even better!', 'autoload-manager' ), $this->name, "https://wordpress.org/support/plugin/{$this->slug}/reviews/?filter=5#new-post", '⭐⭐⭐⭐⭐' );
	}

	public function modal() {
		echo '
		<div id="autoload-manager-modal" style="display: none">
			<img id="autoload-manager-modal-loader" src="' . esc_attr( AUTOLOADMANAGER_ASSETS . '/img/loader.gif' ) . '" />
		</div>';
	}
}