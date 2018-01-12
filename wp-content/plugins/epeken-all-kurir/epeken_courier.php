<?php
if ( ! defined( 'ABSPATH' ) ) exit; 
/*
Plugin Name: Epeken-All-Kurir Plugin - Full Version
Plugin URI: https://wordpress.org/plugins/epeken-all-kurir 
Description: Calculated Shipping Plugin for some shipping companies (JNE, TIKI, POS, RPX, JET.CO.ID, WAHANA, SICEPAT) in Indonesia. It comes with bank accounts payment method with some banks in Indonesia. This is full version plugin. Hopefully you can enjoy this plugin to build your own ecommerce for Indonesia sales.
Version: 1.1.7.3
Author: www.epeken.com
Author URI: http://www.epeken.com
*/
if (!session_id()) {
    session_start();
}
error_reporting(E_ERROR | E_PARSE | E_WARNING);
define('EPEKEN_SERVER_URL', 'http://103.252.101.131'); 
define('EPEKEN_ITEM_REFERENCE', 'epeken_courier');
include_once('includes/epeken_courier_ajax_backend.php');
include_once('class/widget_cekresi.php');
$upload_dir = wp_upload_dir();
$plugin_dir = plugin_dir_path(__FILE__);
$kotakab_json = $plugin_dir.'data/kotakabupaten.json';
$kotakec_json = $plugin_dir.'data/kotakecamatan.json';
$api_dir_url=EPEKEN_SERVER_URL.'/api/index.php/epeken_calculated_shipping_extracustom/';
$valid_origin_url=EPEKEN_SERVER_URL.'/api/index.php/validorigin/';
$api_pos_url_v2=EPEKEN_SERVER_URL.'/api/index.php/epeken_get_ptpos_ongkirv2/';
$api_get_provinces=EPEKEN_SERVER_URL.'/api/index.php/get_all_provinces/';
$api_wahana_v2=EPEKEN_SERVER_URL.'/api/index.php/epeken_get_wahana_ongkirv2/';
$api_jet=EPEKEN_SERVER_URL.'/api/index.php/epeken_get_jnt_ongkir/';
$api_sicepat=EPEKEN_SERVER_URL.'/api/index.php/epeken_get_sicepat_ongkir/';
$api_get_currency_rate=EPEKEN_SERVER_URL.'/api/index.php/getcurrencytoidr/';
$tracking_end_point=EPEKEN_SERVER_URL.'/api/index.php/tracks/';
define('EPEKEN_TRACKING_END_POINT',$tracking_end_point);
define('EPEKEN_KOTA_KAB',$kotakab_json);
define('EPEKEN_KOTA_KEC',$kotakec_json);
define('EPEKEN_API_DIR_URL',$api_dir_url);
define('EPEKEN_VALID_ORIGIN',$valid_origin_url);
define('EPEKEN_API_POS_URL_V2',$api_pos_url_v2);
define('EPEKEN_API_WAHANA',$api_wahana_v2);
define('EPEKEN_API_SICEPAT', $api_sicepat);
define('EPEKEN_API_JET',$api_jet);
define('EPEKEN_API_GET_PRV',$api_get_provinces);
define('EPEKEN_API_GET_CURRENCY_RATE', $api_get_currency_rate);
$epeken_tikijne = null; //This object will be initiated in class/shipping.php
if (in_array('woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins'))) || array_key_exists( 'woocommerce/woocommerce.php', maybe_unserialize( get_site_option( 'active_sitewide_plugins') ) )) {
	function epeken_all_kurir_init() {
		if(!class_exists('WC_Shipping_Tikijne'))
 		{
    			include_once('class/shipping.php');   
 		}
	}
	add_action( 'woocommerce_shipping_init', 'epeken_all_kurir_init' );
	function epeken_add_indonesia_shipping_method( $methods ) {
			$methods[] = 'WC_Shipping_Tikijne';
			return $methods;
		}
	add_filter( 'woocommerce_shipping_methods', 'epeken_add_indonesia_shipping_method' );
	
	
	add_action( 'plugins_loaded', 'epeken_btpn_payment_method_init', 0 );
        function epeken_btpn_payment_method_init(){
                if(!class_exists('BTPN')){
                        include_once('class/btpn_payment_method.php');
                }   
        }   
        function epeken_add_btpn_payment_method( $methods ) { 
          $methods[] = 'BTPN';
          return $methods;
        }  	
	add_filter( 'woocommerce_payment_gateways', 'epeken_add_btpn_payment_method' );

	add_action( 'plugins_loaded', 'epeken_bank_mandiri_payment_method_init', 0 );
	function epeken_bank_mandiri_payment_method_init(){
		if(!class_exists('Mandiri')){
			include_once('class/mandiri_payment_method.php');
		}
	}
	function epeken_add_bank_mandiri_payment_method( $methods ) {
          $methods[] = 'Mandiri';
          return $methods;
    	}	
	add_filter( 'woocommerce_payment_gateways', 'epeken_add_bank_mandiri_payment_method' );
	add_action( 'plugins_loaded', 'epeken_bank_bca_payment_method_init', 0 );
	function epeken_bank_bca_payment_method_init(){
		if(!class_exists('BCA')){
			include_once('class/bca_payment_method.php');
		}
	}
	function epeken_add_bank_bca_payment_method( $methods ) {
          $methods[] = 'BCA';
          return $methods;
    	}	
	add_filter( 'woocommerce_payment_gateways', 'epeken_add_bank_bca_payment_method' );
        add_action( 'plugins_loaded', 'epeken_bank_bni_payment_method_init', 0 );
	function epeken_bank_bni_payment_method_init(){
		if(!class_exists('BNI')){
			include_once('class/bni_payment_method.php');
		}
	}
	function epeken_add_bank_bni_payment_method( $methods ) {
          $methods[] = 'BNI';
          return $methods;
    	}	
	add_filter( 'woocommerce_payment_gateways', 'epeken_add_bank_bni_payment_method' );
	add_action( 'plugins_loaded', 'epeken_bank_bri_payment_method_init', 0 );
        function epeken_bank_bri_payment_method_init(){
                if(!class_exists('BRI')){
                        include_once('class/bri_payment_method.php');
                }
        }
        function epeken_add_bank_bri_payment_method( $methods ) {
          $methods[] = 'BRI';
          return $methods;
        }
        add_filter( 'woocommerce_payment_gateways', 'epeken_add_bank_bri_payment_method' );
	add_action( 'plugins_loaded', 'epeken_bsm_payment_method_init', 0 );
        function epeken_bsm_payment_method_init(){
                if(!class_exists('BSM')){
                        include_once('class/bsm_payment_method.php');
                }
        }
        function epeken_add_bsm_payment_method( $methods ) {
          $methods[] = 'BSM';
          return $methods;
        }
        add_filter( 'woocommerce_payment_gateways', 'epeken_add_bsm_payment_method' );
	add_action( 'plugins_loaded', 'epeken_niaga_payment_method_init', 0 );
        function epeken_niaga_payment_method_init(){
                if(!class_exists('Niaga')){
                        include_once('class/niaga_payment_method.php');
                }
        }
        function epeken_add_niaga_payment_method( $methods ) {
          $methods[] = 'Niaga';
          return $methods;
        }
        add_filter( 'woocommerce_payment_gateways', 'epeken_add_niaga_payment_method' );
        add_action( 'plugins_loaded', 'epeken_bii_payment_method_init', 0 );
        function epeken_bii_payment_method_init(){
                if(!class_exists('BII')){
                        include_once('class/bii_payment_method.php');
                }
        }
        function epeken_add_bii_payment_method( $methods ) {
          $methods[] = 'BII';
          return $methods;
        }
        add_filter( 'woocommerce_payment_gateways', 'epeken_add_bii_payment_method' );
	// Customize order review fields when checkout 
	function epeken_custom_checkout_fields( $fields ) {
		 $billing_first_name_tmp = $fields['billing']['billing_first_name'];
		 $billing_last_name_tmp = $fields['billing']['billing_last_name'];
	         $shipping_first_name_tmp = $fields['shipping']['shipping_first_name'];
		 $shipping_last_name_tmp = $fields['shipping']['shipping_last_name'];
		 $billing_state_tmp = $fields['billing']['billing_state'];
		 $shipping_state_tmp = $fields['shipping']['shipping_state'];
		 $billing_address_1_tmp = $fields['billing']['billing_address_1'];
		 $shipping_address_1_tmp = $fields['shipping']['shipping_address_1'];
	  	 $billing_city_tmp = $fields['billing']['billing_city'];
		 $shipping_city_tmp = $fields['shipping']['shipping_city'];
		 $billing_address_2_tmp = $fields['billing']['billing_address_2'];
		 $shipping_address_2_tmp = $fields['shipping']['shipping_address_2'];
		 $billing_postcode_tmp = $fields['billing']['billing_postcode'];
		 $shipping_postcode_tmp = $fields['shipping']['shipping_postcode'];
		 $billing_phone_tmp = $fields['billing']['billing_phone'];
		 $billing_email_tmp = $fields['billing']['billing_email'];
		 $shipping_country_tmp = $fields['shipping']['shipping_country'];
		 $billing_country_tmp = $fields['billing']['billing_country'];
		 unset($fields['billing']);
		 unset($fields['shipping']);
		 $fields['billing']['billing_first_name'] = $billing_first_name_tmp;
		 $fields['billing']['billing_last_name'] = $billing_last_name_tmp;
		 $fields['billing']['billing_last_name']['required'] = false;
		 $fields['shipping']['shipping_first_name'] = $shipping_first_name_tmp;
		 $fields['shipping']['shipping_last_name'] = $shipping_last_name_tmp;
		 $fields['shipping']['shipping_last_name']['required'] = false;
		 $fields['billing']['billing_address_1'] = $billing_address_1_tmp;
                 $fields['billing']['billing_address_1']['label'] = 'Alamat Lengkap';
                 $fields['billing']['billing_address_1']['placeholder'] = '';
		 $fields['shipping']['shipping_address_1'] = $shipping_address_1_tmp;
                 $fields['shipping']['shipping_address_1']['label'] = 'Alamat Lengkap';
                 $fields['shipping']['shipping_address_1']['placeholder'] = '';
		 $list_of_kota_kabupaten = epeken_get_list_of_kota_kabupaten();
		 $fields['billing']['billing_city'] = $billing_city_tmp;
                 $fields['billing']['billing_city']['label'] = 'Kota/Kabupaten';
                 $fields['billing']['billing_city']['placeholder'] = 'Pilih Kota/Kabupaten';
                 $fields['billing']['billing_city']['type'] = 'select';
                 $fields['billing']['billing_city']['options'] = $list_of_kota_kabupaten;
                 $fields['shipping']['shipping_city'] = $shipping_city_tmp;
                 $fields['shipping']['shipping_city']['label'] = 'Kota/Kabupaten';
                 $fields['shipping']['shipping_city']['placeholder'] = 'Pilih Kota/Kabupaten';
                 $fields['shipping']['shipping_city']['type'] = 'select';
                 $fields['shipping']['shipping_city']['options'] = $list_of_kota_kabupaten;
		 $list_of_kecamatan = epeken_get_list_of_kecamatan('init');
		 $fields['billing']['billing_address_2'] = $billing_address_2_tmp;
		 $fields['billing']['billing_address_2']['label'] = 'Kecamatan';
		 $fields['billing']['billing_address_2']['type'] = 'select'; 
		 $fields['billing']['billing_address_2']['placeholder'] = 'Pilih Kecamatan';
		 $fields['billing']['billing_address_2']['required'] = true;
		 $fields['billing']['billing_address_2']['class'] = array(
                         'form-row','form-row-wide','address-field','validate-required','update_totals_on_change');
		 $fields['billing']['billing_address_2']['options'] = $list_of_kecamatan;
 		 $fields['shipping']['shipping_address_2'] = $shipping_address_2_tmp;
		 $fields['shipping']['shipping_address_2']['label'] = 'Kecamatan';
		 $fields['shipping']['shipping_address_2']['type'] = 'select';
		 $fields['shipping']['shipping_address_2']['placeholder'] = 'Pilih Kecamatan';
		 $fields['shipping']['shipping_address_2']['required'] = true;
		 $fields['shipping']['shipping_address_2']['class'] = array(
                         'form-row','form-row-wide','address-field','validate-required','update_totals_on_change');
	  	 $fields['shipping']['shipping_address_2']['options'] = $list_of_kecamatan;
		 $fields['billing']['billing_address_3']['label'] = 'Kelurahan';
                 $fields['billing']['billing_address_3']['type'] = 'text';
		 $fields['billing']['billing_address_3']['required'] = false;
                 $fields['shipping']['shipping_address_3']['label'] = 'Kelurahan';
		 $fields['shipping']['shipping_address_3']['required'] = false;
                 $fields['shipping']['shipping_address_3']['type'] = 'text';
		 $fields['billing']['billing_state'] = $billing_state_tmp;
		 $fields['billing']['billing_state']['class'] = array('form-row','form-row-first','address_field','validate-required');
	      	 $fields['billing']['billing_postcode'] = $billing_postcode_tmp;
		 $fields['shipping']['shipping_state'] = $shipping_state_tmp;
	 	 $fields['shipping']['shipping_state']['class'] = array('form-row','form-row-first','address_field','validate-required');
	  	 $fields['shipping']['shipping_postcode'] = $shipping_postcode_tmp;
		 $fields['billing']['billing_country'] = $billing_country_tmp;
		 $fields['billing']['billing_email'] = $billing_email_tmp;
		 $fields['billing']['billing_email']['required'] = false;
		 $fields['billing']['billing_phone'] = $billing_phone_tmp;
		 $fields['shipping']['shipping_country'] = $shipping_country_tmp;
		 return $fields;
	}

	function override_default_address() {
		$fields = array(
			'first_name' => array(
                                'label'        => __( 'Nama Depan', 'woocommerce' ),
                                'required'     => true,
                                'class'        => array( 'form-row-first' ),
                                'autocomplete' => 'given-name',
                                'autofocus'    => true,
                                'priority'     => 10,
                        ),
                        'last_name' => array(
                                'label'        => __( 'Nama Belakang', 'woocommerce' ),
                                'required'     => true,
                                'class'        => array( 'form-row-last' ),
                                'autocomplete' => 'family-name',
                                'priority'     => 20,
                        ),
                        'company' => array(
                                'label'        => __( 'Company name', 'woocommerce' ),
                                'class'        => array( 'form-row-wide' ),
                                'autocomplete' => 'organization',
                                'priority'     => 30,
                        ),
                        'country' => array(
                                'type'         => 'country',
                                'label'        => __( 'Negara', 'woocommerce' ),
                                'required'     => true,
                                'class'        => array( 'form-row-wide', 'address-field', 'update_totals_on_change' ),
                                'autocomplete' => 'country',
                                'priority'     => 40,
                        ),   
                        'address_1' => array(
                                'label'        => __( 'Address', 'woocommerce' ),
                                'placeholder'  => esc_attr__( 'Street address', 'woocommerce' ),
                                'required'     => true,
                                'class'        => array( 'form-row-wide', 'address-field' ),
                                'autocomplete' => 'address-line1',
                                'priority'     => 50,
                        ),   
                        'address_2' => array(
                                'placeholder'  => esc_attr__( 'Apartment, suite, unit etc. (optional)', 'woocommerce' ),
                                'class'        => array( 'form-row-wide', 'address-field' ),
                                'required'     => false,
                                'autocomplete' => 'address-line2',
                                'priority'     => 60,
                        ),   
			'state' => array(
                                'type'         => 'state',
                                'label'        => __( 'State / County', 'woocommerce' ),
                                'required'     => true,
                                'class'        => array( 'form-row-wide', 'address-field' ),
                                'validate'     => array( 'state' ),
                                'autocomplete' => 'address-level1',
                                'priority'     => 80,
                        ),
                        'postcode' => array(
                                'label'        => __( 'Kodepos', 'woocommerce' ),
                                'required'     => true,
                                'class'        => array( 'form-row-wide', 'address-field' ),
                                'validate'     => array( 'postcode' ),
                                'autocomplete' => 'postal-code',
                                'priority'     => 90,
                        ), 
		  );
		return $fields;
	}
	add_filter ('woocommerce_default_address_fields', 'override_default_address');
	add_filter( 'woocommerce_checkout_fields' ,  'epeken_custom_checkout_fields' );
	add_action( 'woocommerce_cart_calculate_fees','epeken_insurance_fee' );
	function epeken_insurance_fee() {
     		global $woocommerce;
		global $epeken_tikijne;
     		if ( is_admin() && ! defined( 'DOING_AJAX' ) )
          		return;
     		if(!$_POST["insurance_chkbox"])
        		return;
     		$percentage = 0.002;
                $premium = (($woocommerce->cart->cart_contents_total) * $percentage) + 5000;
		if($epeken_tikijne -> current_currency !== 'IDR' && is_numeric($epeken_tikijne->current_currency) && $epeken_tikijne -> current_currency > 0) {
                        $premium = (($woocommerce->cart->cart_contents_total) * $percentage) + (5000/($epeken_tikijne -> current_currency_rate));
                }
     		$woocommerce->cart->add_fee( 'Asuransi', $premium, true, '' );
	}
	function epeken_checkout_insurance_field() {
                global $woocommerce;
		global $epeken_tikijne;
		if(empty($epeken_tikijne)){
			 $filepath = realpath (dirname(__FILE__));
                         include_once($filepath.'/class/shipping.php');
			 $epeken_tikijne = new WC_Shipping_Tikijne;
		}
		$array_of_mdtry_ins_prod_cat = explode(",",$epeken_tikijne -> settings['prodcat_with_insurance']);
                $contents = $woocommerce->cart->cart_contents;
                $is_insurance_mandatory = false;
                foreach($contents as $content) {
                        $product_id = $content['product_id'];
                        for($i=0;$i<sizeof($array_of_mdtry_ins_prod_cat);$i++){
                          $is_insurance_mandatory = epeken_is_product_in_category($product_id,trim($array_of_mdtry_ins_prod_cat[$i]));
			  /* insurance mandatory product based */
                          if(!$is_insurance_mandatory) {
                                $product_insurance_mandatory = get_post_meta($product_id,'product_insurance_mandatory',true);    
                                if ($product_insurance_mandatory === 'on')
                                        $is_insurance_mandatory = true;
                          }   
                          /* --- */
                          if($is_insurance_mandatory == true)
                                break;
                        }
                          if($is_insurance_mandatory == true)
                                break;
                }
	        $label = "Kirimkan Paket Dengan Asuransi (Khusus JNE)";	
		if($is_insurance_mandatory == true) {
			$label = "Dengan Asuransi.<p>Paket ini sangat kami rekomendasikan dikirim dengan menggunakan asuransi. Khusus untuk pilihan kurir JNE.</p>";
		}
                if($epeken_tikijne -> settings['enable_insurance'] == "yes") {
                $checkout = $woocommerce -> checkout;
                echo "<div id='checkout_insurance_field'>";
                woocommerce_form_field( 'insurance_chkbox', array(
                        'type'          => 'checkbox',
                        'class'         => array('form-row-wide','address-field','update_totals_on_change','validated-required'),
                        'label'         => __($label)
                ), $checkout->get_value( 'insurance_chkbox' ));
                echo "</div>";
		}else{
			return;
		}
		if($is_insurance_mandatory) {
			?> 
			<script language="javascript">
				document.getElementById("insurance_chkbox").checked = true;
			</script>
			<?php
		}
        }
	function epeken_is_product_in_category($productid,$product_category_name){
		$returnvalue = false;
		$terms = get_the_terms( $productid, 'product_cat' );
		if (!is_array($terms)) {
		  return false;
		}
		foreach ($terms as $term) {
    			if($product_category_name == $term->name)
			{
				$returnvalue = true;
				break;
			}
		}
		return $returnvalue;
	 }

        add_action( 'woocommerce_before_order_notes', 'epeken_checkout_insurance_field' );

	function epeken_js_change_select_class() {
			wp_enqueue_script('init_controls',plugins_url('/js/init_controls.js',__FILE__), array('jquery'));
			?>
			<script type="text/javascript">
			jQuery(document).ready(function($) { init_control(); $('#billing_address_3').val('<?php global $current_user; echo get_user_meta($current_user -> ID, 'kelurahan', true); ?>'); $('#shipping_address_3').val('<?php global $current_user; echo get_user_meta($current_user -> ID, 'kelurahan', true); ?>');});
			</script>
			<?php
	}
	add_action ('woocommerce_after_order_notes', 'epeken_js_change_select_class');

	function epeken_js_query_kecamatan_shipping_form(){
		$kec_url = admin_url('admin-ajax.php');
		wp_enqueue_script('ajax_shipping_kec',plugins_url('/js/shipping_kecamatan.js',__FILE__), array('jquery'));
                                 wp_localize_script( 'ajax_shipping_kec', 'PT_Ajax_Ship_Kec', array(
                                        'ajaxurl'       => $kec_url,
					'nextNonce'     => wp_create_nonce('myajax-next-nonce'),
                                 ));

	?>	
		<script type="text/javascript">
			jQuery(document).ready(function($){
					shipping_kecamatan();
				});
		    </script>
	  <?php
	  }
	  function epeken_js_query_kecamatan_billing_form(){
			$kec_url = admin_url('admin-ajax.php');
			wp_enqueue_script('ajax_billing_kec',plugins_url('/js/billing_kecamatan.js',__FILE__), array('jquery'));
                                 wp_localize_script( 'ajax_billing_kec', 'PT_Ajax_Bill_Kec', array(
                                        'ajaxurl'       => $kec_url,
                                        'nextNonce'     => wp_create_nonce('myajax-next-nonce'),
                                 ));
          ?>
               	<script type="text/javascript">
			jQuery(document).ready(function($){
                                        billing_kecamatan();
                                });
		</script>
	<?php	
	}
   	add_action('woocommerce_after_checkout_shipping_form','epeken_js_query_kecamatan_shipping_form');
	add_action('woocommerce_after_checkout_billing_form','epeken_js_query_kecamatan_billing_form');

/**
 * Update the order meta with field value
 */
add_action( 'woocommerce_checkout_update_order_meta', 'epeken_checkout_field_update_order_meta' );
 
function epeken_checkout_field_update_order_meta( $order_id ) {
    global $current_user;
	$flag = false;
    if ( ! empty( $_POST['billing_address_3'] ) ) {
        update_post_meta( $order_id, 'billing_kelurahan', sanitize_text_field( $_POST['billing_address_3'] ) );
	update_user_meta( $current_user -> ID, 'kelurahan', sanitize_text_field( $_POST['billing_address_3'] ) );
	$flag = true;
    }
    if ( ! empty( $_POST['billing_address_2'] ) ) {
        update_post_meta( $order_id, 'billing_kecamatan', sanitize_text_field( $_POST['billing_address_2'] ) );
    }
	
    if ( ! empty( $_POST['shipping_address_3'] ) ) {
        update_post_meta( $order_id, 'shipping_kelurahan', sanitize_text_field( $_POST['shipping_address_3'] ) );
	if (!$flag)
		update_user_meta( $current_user -> ID, 'kelurahan', sanitize_text_field( $_POST['shipping_address_3'] ) );
    }
    if ( ! empty( $_POST['shipping_address_2'] ) ) {
        update_post_meta( $order_id, 'shipping_kecamatan', sanitize_text_field( $_POST['shipping_address_2'] ) );
    }
	$_SESSION['ANGKA_UNIK'] = '';
}

/**
 * Display field value on the order edit page
 */
add_action( 'woocommerce_admin_order_data_after_billing_address', 'epeken_billing_field_display_admin_order_meta', 10, 1 );

function epeken_billing_field_display_admin_order_meta($order){
    echo '<p><strong>'.__('Kelurahan').':</strong> ' . get_post_meta( $order->id, 'billing_kelurahan', true ) . '</p>';
    echo '<p><strong>'.__('Kecamatan').':</strong> ' . get_post_meta( $order->id, 'billing_kecamatan', true ) . '</p>';
}

add_action( 'woocommerce_admin_order_data_after_shipping_address', 'epeken_shipping_field_display_admin_order_meta', 10, 1 );

function epeken_shipping_field_display_admin_order_meta($order){
    echo '<p><strong>'.__('Kelurahan').':</strong> ' . get_post_meta( $order->id, 'shipping_kelurahan', true ) . '</p>';
    echo '<p><strong>'.__('Kecamatan').':</strong> ' . get_post_meta( $order->id, 'shipping_kecamatan', true ) . '</p>';
}

} // End checking if woocommerce is installed.

add_action("template_redirect", 'epeken_theme_redirect');

function epeken_theme_redirect(){
  $plugindir = dirname( __FILE__ );
  if (get_the_title() == 'cekresi') {
        $templatefilename = 'cekresi.php';
        $return_template = $plugindir . '/templates/' . $templatefilename;
        epeken_do_theme_redirect($return_template);
    }
}

function epeken_do_theme_redirect($url) {
    global $post, $wp_query;
    if (have_posts()) {
        include($url);
        die();
    } else {
        $wp_query->is_404 = true;
    }
}

add_action('admin_menu', 'epeken_license_menu');

function epeken_license_menu() {
    add_options_page('License Activation Menu', 'Epeken License Management', 'manage_options', __FILE__, 'epeken_license_management_page');
}

function epeken_license_management_page() {
    echo '<div class="wrap">';
    echo '<h2>Epeken License Management</h2>';

    /*** License activate button was clicked ***/
    if (isset($_REQUEST['activate_license'])) {
        $license_key = $_REQUEST['epeken_wcjne_license_key'];

        // API query parameters
        $api_params = array(
            'slm_action' => 'slm_activate',
            'license_key' => $license_key,
            'registered_domain' => home_url(),
            'item_reference' => urlencode(EPEKEN_ITEM_REFERENCE),
        );

        // Send query to the license manager server
        $response = wp_remote_get(add_query_arg($api_params, "http://www.epeken.com"), array('timeout' => 20, 'sslverify' => false));

        // Check for error in the response
        if (is_wp_error($response)){
	    $error_msg = $response -> errors['http_request_failed'][0]; 
            echo "Unexpected Error! The query returned with an error. ".$error_msg;
		update_option('epeken_wcjne_license_key', trim($license_key));
        }


        // License data.
        $license_data = json_decode(wp_remote_retrieve_body($response));

        // TODO - Do something with it.

        if($license_data->result == 'success'){//Success was returned for the license activation

            //Uncomment the followng line to see the message that returned from the license server
            echo '<br />The following message was returned from the server: '.$license_data->message;

            //Save the license key in the options table
            update_option('epeken_wcjne_license_key', trim($license_key));
        }
        else{
            //Show error to the user. Probably entered incorrect license key.

            //Uncomment the followng line to see the message that returned from the license server
            echo '<br />The following message was returned from the server: '.$license_data->message;
        }
	}
    /*** License activate button was clicked ***/
    if (isset($_REQUEST['deactivate_license'])) {
        $license_key = $_REQUEST['epeken_wcjne_license_key'];

        // API query parameters
        $api_params = array(
            'slm_action' => 'slm_deactivate',
            'license_key' => $license_key,
            'registered_domain' => home_url(),
            'item_reference' => urlencode(EPEKEN_ITEM_REFERENCE),
        );

        // Send query to the license manager server
        $response = wp_remote_get(add_query_arg($api_params, "http://www.epeken.com"), array('timeout' => 20, 'sslverify' => false));

        // Check for error in the response
        if (is_wp_error($response)){
            echo "Unexpected Error! The query returned with an error.";
        }


        // License data.
        $license_data = json_decode(wp_remote_retrieve_body($response));

        // TODO - Do something with it.

        if($license_data->result == 'success'){//Success was returned for the license activation

            //Uncomment the followng line to see the message that returned from the license server
            echo '<br />The following message was returned from the server: '.$license_data->message;

            //Remove the licensse key from the options table. It will need to be activated again.
            update_option('epeken_wcjne_license_key', '');
        }
        else{
            //Show error to the user. Probably entered incorrect license key.

            //Uncomment the followng line to see the message that returned from the license server
            echo '<br />The following message was returned from the server: '.$license_data->message;
        }

    }
    /*** End of sample license deactivation ***/

    ?>
    <p>Masukkan license untuk epeken-all-kurir plugin. Hubungi <a href='mailto:support@epeken.com'>epeken</a> untuk mendapatkan license.</p>
    <form action="" method="post">
        <table class="form-table">
            <tr>
                <th style="width:100px;"><label for="epeken_wcjne_license_key">License Key</label></th>
                <td ><input class="regular-text" type="text" id="epeken_wcjne_license_key" name="epeken_wcjne_license_key"  value="<?php echo get_option('epeken_wcjne_license_key'); ?>" ></td>
            </tr>
        </table>
        <p class="submit">
            <input type="submit" name="activate_license" value="Activate" class="button-primary" />
            <input type="submit" name="deactivate_license" value="Deactivate" class="button" />
        </p>
    </form>
    <?php

    echo '</div>';

}

    add_action( 'woocommerce_product_write_panel_tabs', 'epeken_product_write_panel_tab');
    add_action( 'woocommerce_product_write_panels',     'epeken_product_write_panel');

        function epeken_product_write_panel_tab() {
                echo "<li class=\"product_tabs_lite_tab\"><a href=\"#woocommerce_product_tabs_lite\">" . __( 'Epeken Product Config', 'woocommerce' ) . "</a></li>";
        }

        function epeken_product_write_panel() {
		global $post;
		$epeken_product_config = array (
			"product_origin" => get_post_meta($post->ID,'product_origin',true)
		);

		$product_origin = $epeken_product_config['product_origin'];

                ?>
                <div id="woocommerce_product_tabs_lite" class="panel wc-metaboxes-wrapper woocommerce_options_panel" style="padding: 10px;">
		<table>
               	<?php
		  $license = get_option('epeken_wcjne_license_key');		  
		  $origins = epeken_get_valid_origin($license);
		  $origins = json_decode($origins,true);
		  $origins = $origins["validorigin"];
		  ?>
			<tr>
			<td width=40% height=30px>Kota asal pengiriman produk ini </td><td><strong><?php echo epeken_code_to_city($product_origin); ?></strong></td>
			</tr>
			<tr><td width=40%>Ubah Kota Asal Pengiriman Ke</td> <td><select name="epeken_valid_origin_option" id="epeken_valid_origin_option">
		<?php
			foreach($origins as $origin) {
			  ?>
				<option value=<?php echo $origin["origin_code"]; ?> <?php if ($product_origin === $origin["origin_code"]) echo " selected";?>> <?php echo $origin["kota_kabupaten"];?></option>
			  <?php
			}
		
			if (empty($origins)) {
				?>
				<option value=<?php echo get_option('epeken_data_asal_kota');?>> <?php echo epeken_code_to_city(get_option('epeken_data_asal_kota')); ?> </option>
				<?php
			}
		?> 
			</select></td></tr>
		</table>
			<?php 
			$product_id = $post -> ID; 
                $product_insurance_mandatory = get_post_meta($product_id,'product_insurance_mandatory',true);
                $product_wood_pack_mandatory = get_post_meta($product_id,'product_wood_pack_mandatory',true);
                $product_free_ongkir = get_post_meta($product_id,'product_free_ongkir',true);
			?><div style="margin-top: 10px;">
			<table>
                        <tr><td valign="top">
                        <input type="checkbox" name="epeken_product_insurance_mandatory" id="epeken_product_insurance_mandatory" <?php if($product_insurance_mandatory === 'on') echo 'checked'; ?> /></td><td> Wajib Dikirim Menggunakan Asuransi
                        </td></tr>
                        <tr><td valign="top">
                        <input type="checkbox" name="epeken_product_wood_pack_mandatory" id="epeken_product_wood_pack_mandatory" <?php if($product_wood_pack_mandatory === 'on') echo 'checked';?> /></td><td> Wajib Dikirim Menggunakan Packing Kayu. Untuk mewajibkan packing kayu pada item ini, pastikan Anda sudah melakukan Enable Packing Kayu di WooCommerce > Shipping > Epeken Courier > Packing Kayu Settings.
                        </td></tr>
                        <tr><td valign="top">
                        <input type="checkbox" name="epeken_product_free_ongkir" id="epeken_product_free_ongkir" <?php if($product_free_ongkir === 'on') echo 'checked'; ?> /> </td><td>Gratiskan Ongkos Kirim Untuk Produk Ini
                        </td></tr>
                        <tr><td colspan=2>
                        <div style='float: right;position: relative;'><input name="save" type="submit" style="margin-left: 0px !important" class="button button-primary button-large" id="publish" value="Update"></div>
                        </td></tr>
                        </table>
			</div>
                	</div>
			<script language='javascript'>
                        var chkfreeongkir = document.getElementById('epeken_product_free_ongkir');
                        var chkinsman = document.getElementById('epeken_product_insurance_mandatory');
                        var chkwoodpackman = document.getElementById('epeken_product_wood_pack_mandatory'); 
                        chkfreeongkir.onclick = function() {
                                if(chkfreeongkir.checked) {
                                        chkinsman.checked = false; chkwoodpackman.checked = false;      
                                }
                        }       
                        chkinsman.onclick = function() {
                                if (chkinsman.checked && chkfreeongkir.checked) {
                                        alert('Tidak bisa diset bersama dengan gratis ongkir.');
                                        chkinsman.checked = false;
                                }
                        }
                        chkwoodpackman.onclick = function() {
                                if (chkwoodpackman.checked && chkfreeongkir.checked) {
                                                alert('Tidak bisa diset bersama dengan gratis ongkir.');
                                                chkwoodpackman.checked = false;
                                        }
                        }
                	</script>
                <?php
        }

    function epeken_process_epeken_product_conf( $post_id ) {
	$product_origin_selected = isset($_POST['epeken_valid_origin_option']) ? $_POST['epeken_valid_origin_option'] : '';
	$product_origin = get_post_meta($post_id,'product_origin',true);
	$data_asal_kota = get_option('epeken_data_asal_kota');
	if (empty($product_origin) && !empty($data_asal_kota)) {
		update_post_meta( $post_id, 'product_origin', $data_asal_kota);
	}else{
		update_post_meta( $post_id, 'product_origin', $product_origin_selected);
	}
		$product_insurance_mandatory = isset($_POST['epeken_product_insurance_mandatory']) ? $_POST['epeken_product_insurance_mandatory'] : '';
                 update_post_meta( $post_id, 'product_insurance_mandatory', $product_insurance_mandatory);

                $product_wood_pack_mandatory = isset($_POST['epeken_product_wood_pack_mandatory']) ? $_POST['epeken_product_wood_pack_mandatory'] : '';
                 update_post_meta( $post_id, 'product_wood_pack_mandatory', $product_wood_pack_mandatory);

                $product_free_ongkir = isset($_POST['epeken_product_free_ongkir']) ? $_POST['epeken_product_free_ongkir'] : '';
                 update_post_meta( $post_id, 'product_free_ongkir', $product_free_ongkir);
    }
    add_action('woocommerce_process_product_meta', 'epeken_process_epeken_product_conf');


function validate_add_cart_item( $passed, $product_id, $quantity, $variation_id = '', $variations= '' ) {
    global $woocommerce;
    $items = $woocommerce -> cart -> get_cart();
    $passed = true;

    if(sizeof($items) === 0){
		return $passed;
	}

    $first_item = reset($items); //get first element of items.

    $product_origin_in_cart = get_post_meta($first_item['product_id'],'product_origin',true) ;
    $product_origin_added_cart_item = get_post_meta($product_id,'product_origin',true) ;

    if(empty($product_origin_in_cart)) {
                $product_origin_in_cart = get_option('epeken_data_asal_kota')                   ;
        }

    if(empty($product_origin_added_cart_item)) {
                $product_origin_added_cart_item = get_option('epeken_data_asal_kota')                   ;
        }


    if ( $product_origin_in_cart != $product_origin_added_cart_item ){
        $passed = false;
        wc_add_notice( __( 'Mohon maaf, Anda tidak dapat melakukan pembelian item ini bersamaan dengan item sebelumnya karena perbedaan kota asal pengiriman kurir dengan item sebelumnya. Selesaikan dahulu pembelian item sebelumnya sampai dengan place order selesai, kemudian pembelian item ini bisa dilakukan dengan order yang terpisah.', 'woocommerce' ), 'error' );
    }
    return $passed;
}
add_filter( 'woocommerce_add_to_cart_validation', 'validate_add_cart_item', 10, 5 );

add_action( 'show_user_profile', 'epeken_extra_user_profile_fields' );
add_action( 'edit_user_profile', 'epeken_extra_user_profile_fields' );
function epeken_extra_user_profile_fields( $user ) {
?>
  <h3><?php _e("Informasi Tambahan", "blank"); ?></h3>
  <table class="form-table">
    <tr>
      <th><label for="kelurahan"><?php _e("Kelurahan"); ?></label></th>
      <td>
        <input type="text" name="kelurahan" id="kelurahan" class="regular-text" 
            value="<?php echo esc_attr( get_the_author_meta( 'kelurahan', $user->ID ) ); ?>" /><br />
        <span class="description"><?php _e("Data Kelurahan User"); ?></span>
    </td>
    </tr>
  </table>
<?php
}


add_action( 'personal_options_update', 'epeken_save_extra_user_profile_fields' );
add_action( 'edit_user_profile_update', 'epeken_save_extra_user_profile_fields' );
function epeken_save_extra_user_profile_fields( $user_id ) {
  $saved = false;
  if ( current_user_can( 'edit_user', $user_id ) ) {
    update_user_meta( $user_id, 'kelurahan', $_POST['kelurahan'] );
    $saved = true;
  }
  return true;
}

add_action('woocommerce_after_cart_totals','epeken_disable_shipping_in_cart');
function epeken_disable_shipping_in_cart (){

 ?> <script language="javascript"> 
        var elements = document.getElementsByClassName('shipping'); 
        elements[0].style.display = 'none';
  </script><?php

}

function epeken_short_code_cekresi() {
        $plugindir =  dirname( __FILE__ );
        include($plugindir.'/templates/cekresi.php');
}

add_shortcode('epeken_cekresi','epeken_short_code_cekresi');

add_filter('woocommerce_my_account_my_orders_actions','epeken_add_konfirmasi_pembayaran_button',1,2);

function epeken_add_konfirmasi_pembayaran_button($actions,$order) {
        $order_number = $order->get_order_number();
	$order = new WC_Order($order_number);
        if($order -> get_status() === 'completed')
                return $actions;
        $action['name'] = 'Konfirmasi Pembayaran';
        $action['url'] = get_permalink(get_page_by_title('Konfirmasi Pembayaran')).'?order_id='.$order_number;
        array_push($actions, $action);
        return $actions;
}

function epeken_konfirmasi_pembayaran_shortcode_details() {
	$message = trim($_GET['message']);
	if(!empty($message)) {
		$class = "woocommerce-message";
		if($message === 'Order number tidak ditemukan. Silakan mencoba lagi.' || $message === "Semua Field Harus Diisi, Tidak boleh kosong") {
			$class = "woocommerce-error";
		}
		if($class === "woocommerce-error") {
                         ?> <div class="<?php echo $class; ?>">
                  <?php 
                    echo $message; echo '<span style="position: relative;float: right;"><a href="./konfirmasi-pembayaran/" class="button">Kembali Ke Konfirmasi Pembayaran</a></span>'; 
                  ?>  
                         </div>
                <?php   }else{
                ?>  
                        <div class="<?php echo $class; ?>"><?php echo $message; ?></div> 
                <?php   }   
		return;
	}	
        $order_id = trim($_GET['order_id']);
        $order = new WC_Order($order_id);
        ?>
        <p style="margin-bottom: 20px;">Silakan melakukan konfirmasi pembayaran dengan melengkapi form berikut ini lalu submit.</p>
        <div class="sepeken_form">
		<?php if(!empty($order_id)) { ?>
                <div class="sepeken_td_header">
                        Order ID
                </div>
                <div class="sepeken_td">
                        <?php echo $order->get_order_number(); ?> | <a href="<?php echo $order->get_view_order_url(); ?>">View Order</a>
                        <input type="hidden" value="<?php echo $order->get_order_number(); ?>" name="orderid_pembayaran" id="orderid_pembayaran"/>
                </div>
		<?php }else{ ?>
		<div class="sepeken_td_header">
                        Order ID
                </div>
		<div class="sepeken_td">	
			<input type="number" name="orderid_pembayaran" id="orderid_pembayaran"/>
		</div>
		<?php } ?>
                <div class="sepeken_td_header">
                        Tanggal Pembayaran
                </div>
                <div class="sepeken_td">
                        <input type="text" name="tgl_pembayaran" id="tgl_pembayaran"></input>
		<script type="text/javascript">
                jQuery(document).ready(function($){
                        $("#tgl_pembayaran").datepicker({
                                dateFormat: "dd-mm-yy",
                                onSelect: function(dateText, inst) {
                                        var date = $.datepicker.parseDate(inst.settings.dateFormat || $.datepicker._defaults.dateFormat, dateText, inst.settings);
                                        var dateText1 = $.datepicker.formatDate("d M yy", date, inst.settings);
                                        date.setDate(date.getDate() + 7);
                                        var dateText2 = $.datepicker.formatDate("D, d M yy", date, inst.settings);
                                        $("#tgl_pembayaran").val(dateText1);
                                }
                        });
                });
                </script>
                </div>
		<div style="clear: both; zoom: 1"></div>
                <div class="sepeken_td_header">
                        Nama Pembayar/Nama Rekening
                </div>
                <div class="sepeken_td">
                        <input type="text" name="nama_pembayar" id="nama_pembayar"></input>
                </div>
                <div class="sepeken_td_header">
                        Nama Bank
                </div>
                <div class="sepeken_td">
                        <input type="text" name="nama_bank" id="nama_bank"></input>
                </div>
                <div class="sepeken_td_header">
                        Notes
                </div>
                <div class="sepeken_td">
                        <textarea style="height: 100px;" name="notes_pembayaran" id="notes_pembayaran" ></textarea>
                </div>
                <div class="sepeken_td_header">
                        &nbsp;
                </div>
                <div class="sepeken_td">
                        <button id="submit_konfirmasi">Submit</button>
                </div>
        </div>
		<script type="text/javascript">
                        jQuery(document).ready(function($){
                                       konfirmasi_pembayaran();
                                });
                 </script>
        <?php
}

add_shortcode('epeken_konfirmasi_pembayaran', 'epeken_konfirmasi_pembayaran_shortcode_details');
add_action('wp_enqueue_scripts','epeken_register_scripts');
function epeken_register_scripts() {
wp_enqueue_script('jquery-cookie',plugins_url('assets/jquery.cookie.js',__FILE__), array('jquery'));
wp_enqueue_style('epeken_plugin_styles', plugins_url('/class/assets/css/epeken-plugin-style.css',__FILE__));
$title = get_the_title();
if ($title !== "Konfirmasi Pembayaran")
return;
 wp_enqueue_style('sepeken_style',plugins_url('assets/epeken-style.css',__FILE__));
 wp_enqueue_style('epeken_jquery_style',plugins_url('assets/jquery-ui.min.css',__FILE__));
 wp_enqueue_script('jquery-min',plugins_url('assets/jquery.min.js',__FILE__), array('jquery'));
 wp_enqueue_script('jquery-ui',plugins_url('assets/jquery-ui.min.js',__FILE__), array('jquery'));
 wp_enqueue_script('ajax_epeken_konfirmasi_pembayaran',plugins_url('assets/konfirmasi_pembayaran.js',__FILE__), array('jquery'));
 wp_localize_script( 'ajax_epeken_konfirmasi_pembayaran', 'PT_Ajax_Konfirmasi_Pembayaran', array(
    'ajaxurl'       => admin_url('admin-ajax.php'),
    'nextNonce'     => wp_create_nonce('epeken-konfirmasi-pembayaran'),
 ));  
}

add_action('wp_ajax_submit_konfirmasi_pembayaran','epeken_submit_konfirmasi_pembayaran');
add_action('wp_ajax_nopriv_submit_konfirmasi_pembayaran','epeken_submit_konfirmasi_pembayaran');

function epeken_submit_konfirmasi_pembayaran() {
        $order_id = sanitize_text_field($_GET['orderid']);
        $tgl_pembayaran = sanitize_text_field($_GET['tglpembayaran']);
        $nama_pembayar = sanitize_text_field($_GET['namapembayar']);
        $nama_bank = sanitize_text_field($_GET['namabank']);    
        $notes_pembayaran = sanitize_text_field($_GET['notespembayaran']);
        $nextNonce = sanitize_text_field($_GET['nextNonce']);

        if(!wp_verify_nonce($nextNonce,'epeken-konfirmasi-pembayaran')){
                        die('Invalid Invocation');
                }     

	if (empty($order_id) || empty($tgl_pembayaran) || empty ($nama_pembayar) || /*empty($rekening_pembayar) ||*/ empty($nama_bank) || empty($notes_pembayaran)) {
		echo "Semua Field Harus Diisi, Tidak boleh kosong";
		return;
	}
     
        $order = new WC_Order($order_id);

	$is_payment_confirmed = get_post_meta($order_id,'payment_confirmation', true);
	if($is_payment_confirmed === 'yes')
	{
		echo "Order ini sudah pernah dikonfirmasikan pembayarannya. Kami akan segera memproses order ini. Terima kasih.";
		return;
	}

	if(!empty($order->post)) {
        	$string_note = "Konfirmasi Pembayaran dengan Detail : Sudah ditransfer/deposit untuk pembayaran order #".$order_id." pada tanggal ".$tgl_pembayaran.", dari pemilik rekening dengan nama: ". $nama_pembayar.", rekening bank ".$nama_bank.", notes: ".$notes_pembayaran;
        	$order->add_order_note($string_note);
	}else {
		echo "Order number tidak ditemukan. Silakan mencoba lagi.";
		return;
	}
        $admin_email = get_option('admin_email');
        $user_email .= ','.$admin_email;
        wc_mail($user_email,'Konfirmasi Pembayaran Order #'.$order_id,$string_note,'','');
        echo 'Terima kasih. Order dengan ID '.$order_id.' berhasil dikonfirmasikan pembayarannya. Kami akan segera memproses pesanan Anda. Terima kasih.';
	update_post_meta($order_id, 'payment_confirmation', 'yes');
	update_post_meta($order_id, 'tanggal_pembayaran', $tgl_pembayaran);
	update_post_meta($order_id, 'nama_pembayar', $nama_pembayar);
	update_post_meta($order_id, 'bank_pembayar', $nama_bank);
	update_post_meta($order_id, 'notes_konfirmasi', $notes_pembayaran); 
}
add_action ('woocommerce_single_product_summary', 'epeken_display_kota_asal_pengiriman');
 function epeken_display_kota_asal_pengiriman() {
                global $epeken_tikijne;
                if(empty($epeken_tikijne)){
                         $filepath = realpath (dirname(__FILE__));
                         include_once($filepath.'/class/shipping.php');
                         $epeken_tikijne = new WC_Shipping_Tikijne;
                }   
                if ($epeken_tikijne -> settings['is_kota_asal_in_product_details'] === 'no')
                        return;

                $post = get_post();
                $origin_code = get_post_meta($post -> ID,'product_origin',true) ;
                if(!empty($origin_code)) {
                 $city = epeken_code_to_city($origin_code);    
                }else{
                 $city = epeken_code_to_city(get_option('epeken_data_asal_kota')); //$this -> settings['data_kota_asal']); 
                }    
                ?>  
                <div class="summary entry-summary" style="margin-top: 20px; margin-bottom: 20px;">Kota Asal Pengiriman : <?php echo $city;?></div><div style="clear:both"></div>
                <?php
 }
  function plugin_add_settings_link( $links ) {
    $settings_link = '<a href="admin.php?page=wc-settings&tab=shipping&section=epeken_courier">' . __( 'Settings' ) . '</a>';
    array_push( $links, $settings_link );
  	return $links;
  }
  $plugin = plugin_basename( __FILE__ );
  add_filter( "plugin_action_links_$plugin", 'plugin_add_settings_link' );
    
