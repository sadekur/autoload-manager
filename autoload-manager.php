<?php
/**
 * Plugin Name: Autoload Manager
 * Description: Hello
 * Plugin URI: https://codexpert.io
 * Author: Codexpert, Inc
 * Author URI: https://codexpert.io
 * Version: 0.9
 * Requires at least: 5.0
 * Tested up to: 6.3
 * Requires PHP: 7.4
 * Text Domain: autoload-manager
 * Domain Path: /languages
 *
 * AutoloaManager is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * AutoloaManager is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 */

namespace Codexpert\AutoloaManager;

use Codexpert\Plugin\Widget;
use Codexpert\Plugin\Notice;
use Pluggable\Plugin\License;
use Pluggable\Marketing\Survey;
use Pluggable\Marketing\Feature;
use Pluggable\Marketing\Deactivator;

/**
 * if accessed directly, exit.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main class for the plugin
 * @package Plugin
 * @author Codexpert <hi@codexpert.io>
 */
final class Plugin {
	
	/**
	 * The Plugin
	 * 
	 * @access private
	 */
	private $plugin;
	
	/**
	 * Plugin instance
	 * 
	 * @access private
	 * 
	 * @var Plugin
	 */
	private static $_instance;

	/**
	 * The constructor method
	 * 
	 * @access private
	 * 
	 * @since 0.9
	 */
	private function __construct() {
		
		/**
		 * Includes required files
		 */
		$this->include();

		/**
		 * Defines contants
		 */
		$this->define();

		/**
		 * Runs actual hooks
		 */
		$this->hook();

		/**
		 * Plugin is loaded
		 */
		do_action( 'autoload-manager_loaded' );
	}

	/**
	 * Includes files
	 * 
	 * @access private
	 * 
	 * @uses composer
	 * @uses psr-4
	 */
	private function include() {
		require_once( dirname( __FILE__ ) . '/inc/functions.php' );
		require_once( dirname( __FILE__ ) . '/vendor/autoload.php' );
	}

	/**
	 * Define variables and constants
	 * 
	 * @access private
	 * 
	 * @uses get_plugin_data
	 * @uses plugin_basename
	 */
	private function define() {

		/**
		 * Define some constants
		 * 
		 * @since 0.9
		 */
		define( 'AUTOLOADMANAGER_FILE', __FILE__ );
		define( 'AUTOLOADMANAGER_DIR', dirname( AUTOLOADMANAGER_FILE ) );
		define( 'AUTOLOADMANAGER_ASSETS', plugins_url( 'assets', AUTOLOADMANAGER_FILE ) );
		define( 'AUTOLOADMANAGER_DEBUG', apply_filters( 'autoload-manager_debug', true ) );

		/**
		 * The plugin data
		 * 
		 * @since 0.9
		 * @var $plugin
		 */
		$this->plugin					= get_plugin_data( AUTOLOADMANAGER_FILE );
		$this->plugin['basename']		= plugin_basename( AUTOLOADMANAGER_FILE );
		$this->plugin['file']			= AUTOLOADMANAGER_FILE;
		$this->plugin['doc_id']			= 1960;
		$this->plugin['server']			= 'https://my.pluggable.io';
		$this->plugin['icon']			= AUTOLOADMANAGER_ASSETS . '/img/icon.png';
		$this->plugin['depends']		= [ 'woocommerce/woocommerce.php' => __( 'WooCommerce', 'autoload-manager' ) ];
		
		/**
		 * The license
		 */
		$this->plugin['license']		= new License( AUTOLOADMANAGER_FILE );

		/**
		 * Set plugin data instance
		 */
		define( 'AUTOLOADMANAGER', apply_filters( 'autoload-manager_instance', $this->plugin ) );
	}

	/**
	 * Hooks
	 * 
	 * @access private
	 * 
	 * Executes main plugin features
	 *
	 * To add an action, use $instance->action()
	 * To apply a filter, use $instance->filter()
	 * To register a shortcode, use $instance->register()
	 * To add a hook for logged in users, use $instance->priv()
	 * To add a hook for non-logged in users, use $instance->nopriv()
	 * 
	 * @return void
	 */
	private function hook() {

		if( is_admin() ) :

			/**
			 * The installer
			 */
			$installer = new App\Installer();
			$installer->activate( 'install' );
			$installer->deactivate( 'uninstall' );
			$installer->action( 'admin_footer', 'update' );

			/**
			 * Admin facing hooks
			 */
			$admin = new App\Admin();
			$admin->action( 'admin_footer', 'modal' );
			$admin->action( 'plugins_loaded', 'i18n' );
			$admin->action( 'admin_menu', 'admin_menu' );
			$admin->action( 'admin_enqueue_scripts', 'enqueue_scripts' );
			$admin->filter( "plugin_action_links_{$this->plugin['basename']}", 'action_links' );


			/**
			 * Settings related hooks
			 */
			$settings = new App\Settings();
			// $settings->action( 'plugins_loaded', 'init_menu' );


			/**
			 * Renders different notices
			 * 
			 * @package Codexpert\Plugin
			 * 
			 * @author Codexpert <hi@codexpert.io>
			 */
			$notice = new Notice( $this->plugin );

			/**
			 * Asks to participate in a survey
			 * 
			 * @package Pluggable\Marketing
			 * 
			 * @author Pluggable <hi@pluggable.io>
			 */
			$survey = new Survey( AUTOLOADMANAGER_FILE );

			/**
			 * Shows a popup window asking why a user is deactivating the plugin
			 * 
			 * @package Pluggable\Marketing
			 * 
			 * @author Pluggable <hi@pluggable.io>
			 */
			$deactivator = new Deactivator( AUTOLOADMANAGER_FILE );

			/**
			 * Alters featured plugins
			 * 
			 * @package Pluggable\Marketing
			 * 
			 * @author Pluggable <hi@pluggable.io>
			 */
			$feature = new Feature( AUTOLOADMANAGER_FILE );

		else : // ! is_admin() ?

			/**
			 * Front facing hooks
			 */
			$front = new App\Front();
			$front->action( 'wp_head', 'head' );
			$front->action( 'wp_footer', 'modal' );
			$front->action( 'wp_enqueue_scripts', 'enqueue_scripts' );
			$front->action( 'admin_bar_menu', 'add_admin_bar', 70 );

		endif;

		/**
		 * Common hooks
		 *
		 * Executes on both the admin area and front area
		 */
		$common = new App\Common();

		/**
		 * AJAX related hooks
		 */
		$ajax = new App\AJAX();
		$ajax->priv( 'some-route', 'some_callback' );
		$ajax->all('update_autoload_option', 'toggle_autoload_option');
		$ajax->all('update_bulk_autoload_option', 'toggle_bulk_autoload_option');
		$ajax->all('load_options_data', 'load_options_data');

	}

	/**
	 * Cloning is forbidden.
	 * 
	 * @access public
	 */
	public function __clone() { }

	/**
	 * Unserializing instances of this class is forbidden.
	 * 
	 * @access public
	 */
	public function __wakeup() { }

	/**
	 * Instantiate the plugin
	 * 
	 * @access public
	 * 
	 * @return $_instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
}

Plugin::instance();