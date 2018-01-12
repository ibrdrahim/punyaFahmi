<?php
/**
 * BI_Stocks Class.
 *
 * @class       BI_Stocks
 * @version		1.0
 * @author lafif <lafif@astahdziq.in>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * BI_Stocks class.
 */
class BI_Stocks {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->includes();

		add_action( 'init', array($this, 'register_scripts') );
		add_action( 'wp_enqueue_scripts', array($this, 'enqueue_scripts') );

		add_action( 'bi_add_menu', array($this, 'add_stock_menu') );
		add_action( 'widgets_init', array($this, 'register_widget') );

		add_shortcode( 'bandros_cek_stock', array($this, 'shortcode_cek_stock') );
		add_shortcode( 'bandros_stock', array($this, 'shortcode_auto_stock') );

		// Enable shortcodes in text widgets
		add_filter('widget_text','do_shortcode');

	}

	public function register_scripts(){

		wp_register_style( 'bi_stocks', Bandros_Import()->plugin_url() . '/assets/css/bandros_stock.css', array(), Bandros_Import()->version );
		wp_register_script( 'bi_stocks', Bandros_Import()->plugin_url() . '/assets/js/bandros_stock.js', array('jquery'), Bandros_Import()->version, true );
	}

	public function enqueue_scripts(){
		global $post;
	    if( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'bandros_cek_stock') ) {
	        wp_enqueue_style( 'bi_stocks' );
	    }
	}

	public function register_widget(){
		global $bandros_import;

		/**
		 * If both woocommerce and lapak instant not installed
		 * dont add this auto check stock widget
		 *
		 * user still able to add check stock widget with shortcode `[bandros_cek_stock]`
		 */
		if( ! $bandros_import->is_wc_installed && !class_exists('LapakInstan_Function') )
			return;

		register_widget( 'BI_Widget_Stocks' );
	}

	public function shortcode_auto_stock($atts){
		global $bandros_import;
        
        if(!get_option( 'bandros_import_api_key' ) || !is_singular() )
        	return;

        wp_enqueue_style( 'bi_stocks' );
        wp_enqueue_script( 'bi_stocks' );
        wp_localize_script( 'bi_stocks', 'BI_STOCK', array(
        	'ajax_url' => Bandros_Import()->ajax_url(),
        	'nonce' => wp_create_nonce( 'bi_load_stock' )
        ) );

		if( $bandros_import->is_wc_installed && is_singular( 'product' ) ){
			global $product;
			$sku = $product->get_sku();
		} else {
			global $post;
			$sku = (class_exists('LapakInstan_Function')) ? LapakInstan_Function::smart_meta($post->ID, 'my_meta_kode_produk') : '';
		}

		ob_start();

		?>
		<div class="container-search-stock">
			<div class="result-stock bandros-load-stock" data-sku="<?php echo $sku; ?>" data-error="<?php echo (isset($atts['error_message'])) ? $atts['error_message'] : ''; ?>" data-use-image="<?php echo (isset($atts['use_image'])) ? $atts['use_image'] : 'no'; ?>">
				<div class="loading"><?php _e('Mengambil data..', 'bandros_import'); ?></div>
				<div class="stock-data"></div>
			</div>
		</div>
		<?php

		return ob_get_clean();
	}

	public function shortcode_cek_stock($atts){

		wp_enqueue_style( 'bi_stocks' );
		wp_enqueue_script( 'bi_stocks' );
        wp_localize_script( 'bi_stocks', 'BI_STOCK', array(
        	'ajax_url' => Bandros_Import()->ajax_url(),
        	'nonce' => wp_create_nonce( 'bi_load_stock' ),
        ) );

		ob_start();
		?>
		<div class="container-search-stock">
			<form class="form-cek-stock" data-error="<?php echo (isset($atts['error_message'])) ? $atts['error_message'] : ''; ?>" data-use-image="<?php echo (isset($atts['use_image'])) ? $atts['use_image'] : 'no'; ?>">
				<button type="submit" class="submit-cek-stock btn btn-info cl"><?php _e('Cek Stock', 'bandros_import'); ?></button>
				<div class="wrapper-kode-barang">
				   <input type="text" class="kode-barang"/>
				</div>
			</form>

			<div class="result-stock">
				<div class="loading"><?php _e('Mengambil data..', 'bandros_import'); ?></div>
				<div class="stock-data with-image"></div>
			</div>
		</div>

		<?php
		return ob_get_clean();
	}

	public function add_stock_menu(){
		add_submenu_page( 'bandros-juara', __('Widget Cek Stok', 'bandros_import'), __('Widget Cek Stok', 'bandros_import'), Bandros_Import()->capability, 'bandros-widget-stock', array($this, 'bandros_stock_page') );
	}

	public function bandros_stock_page(){
		global $bi_admin;

		if( isset($_GET['section']) && ($_GET['section'] == 'settings') ){

			$bi_admin->import_settings();
			return;
		}

		wp_enqueue_style( 'bandros_import' );

		$bi_url =  menu_page_url('bandros-widget-stock', false);
		$setting_url = add_query_arg(array('section' => 'settings'), $bi_url);
		?>
		<div class="funkmo-container">
			<div class="funkmo-holder">
		        <h1><span class="dashicons dashicons-welcome-widgets-menus"></span> <?php _e('Petunjuk Penggunaan Widget', 'bandros_import'); ?></h1>
		        <a href="<?php echo $setting_url; ?>" id="" class="bi_settings"><i class="dashicons dashicons-admin-generic"></i><?php _e('Pengaturan', 'bandros_import'); ?></a>
		        <div class="funkmo-clear"></div>
	        	
		        <div class="funkmo-content">
					<?php
					$file_location = Bandros_Import()->plugin_path() . "/petunjuk-widget-stock.md";
					if(is_readable($file_location)){ 
					?>
					<hr class="funkmo-hr">
	
					<div class="bi_welcome_message">
						<?php 
						$file = file_get_contents( $file_location );
						$parsedown = new Parsedown();
						echo $parsedown->text($file);
						?>
					</div>
					<?php
					} 
					?>
		        	<div class="funkmo-clear"></div>
		        </div>
	     	</div>
		</div>
		<?php
	}

	public function includes(){
		include_once( 'widgets/class-widget-stocks.php' );
	}

}

$GLOBALS['bi_stocks'] = new BI_Stocks();