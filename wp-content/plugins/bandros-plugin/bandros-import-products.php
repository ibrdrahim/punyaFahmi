<?php
/**
 * Plugin Name: Bandros Plugin
 * Description: Plugin upload produk massal dan widget cek stok online hanya untuk member Bandros tercinta. Support WooCommerce dan Lapak Instan. Pakai dengan bijak ya. :)
 * Author: Bandros Dev
 * Author URI: https://www.bandros.co.id
 * Author Email: cs@bandros.co.id
 * Version: 2.0
 * Text Domain: bandros_import
 * Domain Path: /languages/ 
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Bandros_Import' ) ) :

/**
 * Main Bandros_Import Class
 *
 * @class Bandros_Import
 * @version	1.0
 */
final class Bandros_Import {

	/**
	 * @var string
	 */
	public $version = '2.0';

	public $capability = 'read_private_pages'; // woocommerce shop manager role

	public $session;

	private $options;

	public $is_wc_installed = false;
	public $is_lapakinstant_installed = false;

	/**
	 * @var Bandros_Import The single instance of the class
	 * @since 1.0
	 */
	protected static $_instance = null;

	/**
	 * Main Bandros_Import Instance
	 *
	 * Ensures only one instance of Bandros_Import is loaded or can be loaded.
	 *
	 * @since 1.0
	 * @static
	 * @return Bandros_Import - Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Bandros_Import Constructor.
	 */
	public function __construct() {
		$this->define_constants();

		$this->check_wc_installed();
		$this->check_lapakintant_installed();

		$this->includes();
		$this->init_hooks();

		do_action( 'bandros_import_loaded' );
	}

	private function check_wc_installed(){

		$needed = array('woocommerce/woocommerce.php');
		$activated = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );

		$this->is_wc_installed = count(array_intersect($needed, $activated)) == count($needed);
	}

	/**
	 * Check if lapakinstant installed
	 * @since 2.0 [<description>]
	 * @return [type] [description]
	 */
	private function check_lapakintant_installed(){
		// may need better check
		$theme = wp_get_theme();
		$this->is_lapakinstant_installed = ( 'Lapakinstan Theme' == $theme->name || 'Lapakinstan Theme' == $theme->parent_theme );
	}

	/**
	 * Hook into actions and filters
	 * @since  2.3
	 */
	private function init_hooks() {

		register_activation_hook( __FILE__, array( $this, 'install' ) );

		add_action( 'init', array( $this, 'init' ), 0 );

		register_uninstall_hook( __FILE__, 'uninstall' );
	}

	/**
	 * All install stuff
	 * @return [type] [description]
	 */
	public function install() {
		
		// we add role for pending member
		do_action( 'on_bandros_import_install' );
	}

	/**
	 * All uninstall stuff
	 * @return [type] [description]
	 */
	public function uninstall() {

		// we remove what we did 
		do_action( 'on_bandros_import_uninstall' );
	}

	/**
	 * Init Bandros_Import when WordPress Initialises.
	 */
	public function init() {
		// Before init action
		do_action( 'before_bandros_import_init' );

		$this->session = new BI_Session();

		$this->options = get_option( 'bandros_import' );

		// register all scripts
		$this->register_scripts();

		// Init action
		do_action( 'after_bandros_import_init' );
	}

	/**
	 * Register all scripts to used on our pages
	 * @return [type] [description]
	 */
	public function register_scripts(){

		wp_register_style( 'bi-select2', plugins_url( '/assets/css/select2.min.css', __FILE__ ), array(), '4.0.3' );
		wp_register_script( 'bi-select2', plugins_url( '/assets/js/select2.min.js', __FILE__ ), array('jquery'), '4.0.3', true );

		wp_register_style( 'bandros_import', plugins_url( '/assets/css/bandros_import.css', __FILE__ ), array( 'bi-select2' ) );
		wp_register_script( 'asPieProgress', plugins_url( '/assets/js/jquery-asPieProgress.js', __FILE__ ), array('jquery'), '', true );
		wp_register_script( 'bandros_import', plugins_url( '/assets/js/bandros_import.js', __FILE__ ), array('jquery', 'asPieProgress', 'bi-select2'), '', true );
 	}

	/**
	 * Define Bandros_Import Constants
	 */
	private function define_constants() {

		$this->define( 'BANDROS_IMPORT_PLUGIN_FILE', __FILE__ );
		$this->define( 'BANDROS_IMPORT_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
		$this->define( 'BANDROS_IMPORT_VERSION', $this->version );
		$this->define( 'BANDROS_IMPORT_API_URL', 'https://www.bandros.co.id/mobile/api_wp' );
		$this->define( 'BANDROS_STOCK_API_URL', 'http://bandros.id/stok_api' );
	}

	/**
	 * Define constant if not already set
	 * @param  string $name
	 * @param  string|bool $value
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * What type of request is this?
	 * string $type ajax, frontend or admin
	 * @return bool
	 */
	public function is_request( $type ) {
		switch ( $type ) {
			case 'admin' :
				return is_admin();
			case 'ajax' :
				return defined( 'DOING_AJAX' );
			case 'cron' :
				return defined( 'DOING_CRON' );
			case 'frontend' :
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
		}
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 */
	public function includes() {
		// all public includes
		
		// loading composer for curl helper
		require __DIR__ . '/vendor/autoload.php';

		include_once( 'includes/abstracts/abstract-bi-importer.php' );
		include_once( 'includes/class-bi_admin.php' );
		include_once( 'includes/class-bi_session.php' );
		include_once( 'includes/class-bi_ajax.php' );
		include_once( 'includes/class-bi_stocks.php' );

		if ( $this->is_request( 'admin' ) ) {
		}

		if ( $this->is_request( 'ajax' ) ) {
			// include_once( 'includes/ajax/..*.php' );
		}

		if ( $this->is_request( 'frontend' ) ) {
		}
	}

	/**
	 * Get the plugin url.
	 * @return string
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', __FILE__ ) );
	}

	/**
	 * Get the plugin path.
	 * @return string
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

	/**
	 * Get Ajax URL.
	 * @return string
	 */
	public function ajax_url() {
		return admin_url( 'admin-ajax.php', 'relative' );
	}

	public function get_options($optname=''){
		$bandros_import_opt = $this->options;

		if(!empty($optname))
			return (isset($bandros_import_opt[$optname])) ? $bandros_import_opt[$optname] : false;

		return (object) $bandros_import_opt;
	}

}

endif;

/**
 * Returns the main instance of Bandros_Import to prevent the need to use globals.
 *
 * @since  1.0
 * @return Bandros_Import
 */
function Bandros_Import() {
	return Bandros_Import::instance();
}

// Global for backwards compatibility.
$GLOBALS['bandros_import'] = Bandros_Import();