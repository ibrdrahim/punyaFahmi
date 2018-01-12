<?php 
/*
Plugin : Epeken-All-Kurir
File Name : shipping.php
version : 1.1.6.9
writer : epeken.com
*/
    class WC_Shipping_Tikijne extends WC_Shipping_Method
	{	
		public  $jneclass;
		public  $shipping_cost;
	  	public  $shipping_kecamatan;	
		public  $shipping_city;
		public  $popup_message;
		public  $shipping_total_weight;
		public  $shipping_total_length;
		public  $shipping_total_width;
		public  $shipping_total_height;
                public  $shipping_metric_dimension;
		public  $min_allow_fs;
		public  $total_cart;
		public  $is_free_shipping;
		public  $insurance_premium;	
		public  $array_of_tarif;
		public  $additionalLabel;
		public  $destination_province;
		public  $origin_city;
		public  $valid_origins;
		public  $chosen_shipping_method;
		public  $is_packing_kayu_valid;
		public  $current_currency;
		public  $current_currency_rate;
		
		public function __construct(){
			$this -> id = 'epeken_courier';
			$this -> current_currency_rate = 1;
			$this -> method_title = __('Epeken Courier');
			$this -> method_description = __('Shipping Method using JNE TIKI RPX POS Indonesia ESL Express for Indonesia e-commerce market');
			$this -> enabled = 'yes';
			$this -> title = 'Epeken Courier';
			$this -> is_free_shipping = false;
			$this -> init();			
			$this -> array_of_tarif = array();
			$this -> initiate_epeken_options();
		}

		public function initiate_epeken_options() {
			if(get_option('epeken_free_pc',false) === false){
                                add_option('epeken_free_pc','','','yes');
                        }
                        if(get_option('epeken_free_pc_q',false) === false) {
                                add_option('epeken_free_pc_1','','','yes');
                        }
			if(get_option('epeken_enabled_jne',false) === false) {
                                add_option('epeken_enabled_jne','','','yes');
                        }
			if(get_option('epeken_enabled_tiki',false) === false) {
                                add_option('epeken_enabled_tiki','','','yes');
                        }
			if(get_option('epeken_enabled_pos',false) === false) {
                                add_option('epeken_enabled_pos','','','yes');
                        }
			if(get_option('epeken_enabled_rpx',false) === false) {
                                add_option('epeken_enabled_rpx','','','yes');
                        }
			if(get_option('epeken_enabled_esl',false) === false) {
                                add_option('epeken_enabled_esl','','','yes');
                        }			
			if(get_option('epeken_enabled_jne_reg') === false) {
				add_option('epeken_enabled_jne_reg','','','yes');
			}
			if(get_option('epeken_enabled_jne_oke') === false) {
                                add_option('epeken_enabled_jne_oke','','','yes');
                        }
			if(get_option('epeken_enabled_jne_yes') === false) {
                                add_option('epeken_enabled_jne_yes','','','yes');
                        }
		}

		public function create_cek_resi_page(){
                        global $user_ID;

                        $pageckresi = get_page_by_title( 'cekresi','page' );
                        if(!is_null($pageckresi))
                          return;

                        $page['post_type']    = 'page';
                        //$page['post_content'] = 'Put your page content here';
                        $page['post_parent']  = 0;
                        $page['post_author']  = $user_ID;
                        $page['post_status']  = 'publish';
                        $page['post_title']   = 'cekresi';
                        $page = apply_filters('epeken_add_new_page', $page, 'teams');

                    $pageid = wp_insert_post ($page);
                    if ($pageid == 0) { /* Add Page Failed */ }

                }

		public function create_konfirmasi_pembayaran_page(){
                        global $user_ID;

                        $pagekonfirmasi = get_page_by_title( 'konfirmasi_pembayaran','page' );
                        if(!is_null($pagekonfirmasi))
                          return;

                        $page['post_type']    = 'page';
                        $page['post_content'] = '[epeken_konfirmasi_pembayaran]'; //shortcode
                        $page['post_parent']  = 0; 
                        $page['post_author']  = $user_ID;
                        $page['post_status']  = 'publish';
                        $page['post_title']   = 'konfirmasi_pembayaran';
                        $page = apply_filters('epeken_add_new_page', $page, 'teams');

                    $pageid = wp_insert_post ($page);
                    if ($pageid == 0) { /* Add Page Failed */ }

                }	

                public function add_cek_resi_page_to_prim_menu(){
                        $menu_name = 'primary';
                        $locations = get_nav_menu_locations();

			if(!isset($locations) || !is_array($locations))
                                return;

                        if(!array_key_exists($menu_name,$locations))
                                return;

                        $menu_id = $locations[ $menu_name ] ;
                        $menu_object = wp_get_nav_menu_object($menu_id);

                        if(!$menu_object){
                                return;
                        }
                        $menu_items = wp_get_nav_menu_items($menu_object->term_id);
                        $is_menu_exist = false;
                        foreach ( (array) $menu_items as $key => $menu_item ) {
                                $post_title = $menu_item->post_title;
                                if ($post_title === "Cek Resi"){
                                        $is_menu_exist = true;
                                        break;
                                }
                        }

                        if($is_menu_exist){
                                return;
                        }

                        $url = get_permalink( get_page_by_title( 'cekresi','page' ) );
                        if($url) {
                        wp_update_nav_menu_item($menu_object->term_id, 0, array(
                                'menu-item-title' =>  __('Cek Resi'),
                                'menu-item-url' =>  $url,
                                'menu-item-status' => 'publish')
                                );
                        }

                }

		public function delete_cek_resi(){
			$menu_name = 'primary';
                        $locations = get_nav_menu_locations();

                        if(!isset($locations) || !is_array($locations))
                                return;

                        if(!array_key_exists($menu_name,$locations))
                                return;

                        $menu_id = $locations[ $menu_name ] ;
                        $menu_object = wp_get_nav_menu_object($menu_id);

                        if(!$menu_object){
                                return;
                        }
                        $menu_items = wp_get_nav_menu_items($menu_object->term_id);
                        $is_menu_exist = false;
                        foreach ( (array) $menu_items as $key => $menu_item ) {
                                $post_title = $menu_item->post_title;
                                if ($post_title === "Cek Resi"){
                                        $is_menu_exist = true;
					wp_delete_post($menu_item->ID,true);
                                }
                        }

			$page = get_page_by_title( 'cekresi','page' ) ;
			wp_delete_post($page->ID,true);
		}

		public function activate(){
			global $wpdb;
			$enable_cekresi = $this -> settings['enable_cekresi_page'];
			if($enable_cekresi === 'yes') {		
			 	$this->create_cek_resi_page();
                         	$this->add_cek_resi_page_to_prim_menu();
			}else{
				$this -> delete_cek_resi();
			}
			
			//create konfirmasi pembayaran page
			//$this -> add_konfirmasi_pembayaran_page();	
			
		
		}

		public function writelog($logstr){
			$logdir = plugin_dir_path( __FILE__ )."log/";
			$sesid = session_id();
			$logfile = fopen ($logdir."debug.log","a");
			$now = date("Y-m-d H:i:s");
			fwrite($logfile,$now.":".$logstr."\n");
			fclose($logfile);
		}

		public function popup(){

        		//do_action('wp_login', "dummytoo");

			?>
			<div  id="div_epeken_popup">
                                        <p style='margin: 0 auto; text-align: center;padding-top: 5%;'>
                        <?php echo $this->popup_message; ?><br>
			<img style="display: block; margin: 0 auto;" src='<?php echo plugins_url('assets/ajax-loader.gif',__FILE__); ?>'>
                                        </p>
                        </div>
			<?php	
		}

		public function reset_user_address() {
				global $current_user;
		                get_currentuserinfo();
				update_user_meta($current_user -> ID,'billing_city','');
				 update_user_meta($current_user -> ID,'shipping_city','');
				update_user_meta($current_user -> ID,'billing_address_1','');
                                 update_user_meta($current_user -> ID,'shipping_address_1','');
				update_user_meta($current_user -> ID,'billing_address_2','');
                                 update_user_meta($current_user -> ID,'shipping_address_2','');
		}

		public function load_jne_tariff(){
                                 $ajax_url = admin_url('admin-ajax.php');
				 wp_enqueue_script('ajax_load_jne_tariff',plugins_url('/js/jne_load_tariff.js',__FILE__), array('jquery'));
				 wp_localize_script( 'ajax_load_jne_tariff', 'PT_Ajax', array(
        				'ajaxurl'       => $ajax_url
    				 ));
		}

		public function register_jne_plugin(){
                                 $ajax_url = admin_url('admin-ajax.php');
				 wp_enqueue_script('ajax_epeken_register',plugins_url('/js/register.js',__FILE__), array('jquery'));
				 wp_localize_script( 'ajax_epeken_register', 'PT_Ajax', array(
        				'ajaxurl'       => $ajax_url
    				 ));
		}


		public function init() {
					// Load the settings API
					$this->init_form_fields(); // This is part of the settings API. Override the method to add your own settings
					$this->init_settings(); // This is part of the settings API. Loads settings you previously init.
					// Save settings in admin if you have any defined, when save button in admin setting screen is clicked
					add_action('woocommerce_update_options_shipping_' . $this->id,array(&$this, 'process_admin_options'));
					// To display new shipping method in woocommerce shipping menu
					add_action('woocommerce_update_options_shipping_methods', array(&$this, 'process_admin_options'));
					//add_action('woocommerce_update_options_payment_gateways',array(&$this, 'process_admin_options'));
				
					$this -> popup_message = "Mohon menunggu";
       					add_action('woocommerce_before_checkout_billing_form',array(&$this, 'popup'));
					add_action('woocommerce_checkout_process', array(&$this, 'reset_user_address'));
					//add_action( 'woocommerce_cart_calculate_fees', array($this,'calculate_insurance'));
					//add_action('woocommerce_cart_calculate_fees', array($this, 'calculate_biaya_tambahan'));
					//add_action('woocommerce_cart_calculate_fees', array($this, 'calculate_angka_unik'));
					add_action( 'woocommerce_update_options_shipping_' . $this->id, array( &$this, 'process_update_data_tarif' ) );
					//add_action('woocommerce_review_order_before_payment',array(&$this,'add_volume_dimension_label'));
					$this -> activate();
		}

/**
 * Initialise Gateway Settings Form Fields
 */
	public function init_form_fields() {
		 
		  $license = get_option('epeken_wcjne_license_key');
                  $origins = epeken_get_valid_origin($license);
                  $origins = json_decode($origins,true);
                  $origins = $origins["validorigin"];	
		  $this -> valid_origins = $origins;
 
     			$this->form_fields = array(
						
							'enabled' => array(
                                                        'title'                 => __( 'Enable/Disable', 'woocommerce' ),
                                                        'type'                  => 'checkbox',
                                                        'label'                 => __( 'Enable this shipping method', 'woocommerce' ),
                                                        'default'               => 'yes',
                                                	),
						 'panel_enable_kurir' => array(
							'type' => 'panel_enable_kurir',
						),
						/*'data_kota_asal' => array(
                                                        'title' => __('Data Kota Asal (Default)','woocommerce'),
                                                        'type' => 'select',
                                                        'options' => $array_kota_01
                                                ),*/
						'data_asal_kota' => array(			
							'type' => 'data_asal_kota'
						),
						'is_kota_asal_in_product_details' => array (
                                                        'title' => 'Kota Asal Di Product Details',
                                                        'type' => 'checkbox',
                                                        'label' => 'Tampilkan kota asal di halaman product details dan checkout order details',
                                                        'default' => 'no' 
                                                ),   	
							'volume_matrix' => array(
								'title' => __('Volume Metrics Enable Checkbox', 'woocommerce'),
								'type' 		=> 'checkbox',
								'label'		=> __('Enable/Disable volume metrics. Berat dalam kg dan dimensi(length, width, height) dalam cm. (Khusus JNE dan TIKI)', 'woocommerce'),
								'default'	=> 'yes'
							),
						'treshold_pembulatan' => array (
							'title' => __('Treshold pembulatan berat ke atas (Khusus JNE dan Tiki) dalam gram', 'woocommerce'), 
							'type' => 'number',
							'default' => 300,
						),
						'satuan_berat' => array (
							'title' => __('Satuan Berat','woocommerce'),
							'type' => 'select',
							'options' => array('kg','g'),
							'default' => 0, //it means kilogram
						),
                                                'freeship' => array(
                                                        'title' => __('Nominal Belanja Minimum (Rupiah), Dapat Free Shipping (Biarkan 0 jika ingin free shipping disabled.)','woocommerce'),
                                                        'type'  => 'text',
                                                        'default' => '0',
                                                 ),
						'free_shipping_product_category' => array(
							 'type' => 'free_shipping_product_category',
						),
						 'city_for_free_shipping' => array(
                                                        'title' => __('Kota/Kabupaten yang tidak dikenakan biaya shipping(Pisahkan dengan tanda koma, jika lebih dari satu)','woocommerce'),
                                                        'type' => 'text',
                                                ),
						'kombinasikan_free_shipping' => array(
							'type' => 'kombinasikan_free_shipping',
						),
						 'province_for_free_shipping' => array(
                                                        'type' => 'province_for_free_shipping'
                                                ),
						'enable_cekresi_page' => array(
							'title' => __('Enable Cek Resi appears in main Menu'),
							'type' => 'checkbox',
							'label' => __('Enable/Disable Cek Resi Page.<br> Jika Anda ingin menaruh halaman cek resi di sub menu, disable checkbox ini, lalu gunakan shortcode [epeken_cekresi] untuk membuat halaman cekresi lalu menambahkan halaman tersebut pada sub menu yang Anda kehendaki.'),
							'default' => 'no'
						),
						'form_biaya_tambahan' => array (
                                                        'title' => __('Biaya Tambahan (Misal. Biaya Packing)'),
                                                        'type' => 'form_biaya_tambahan'
                                                ),
						'enable_kode_pembayaran' => array (
							'title' => __('Enable Kode Pembayaran(Angka Unik)'),
							'type' => 'checkbox',
							'label' => __('Aktifkan Kode Pembayaran(Angka Unik) saat checkout'),
							'default' => 'yes'
						),
						'max_angka_unik' => array(
							'title' => __('Maksimum Angka Unik untuk kode pembayaran (Disarankan maksimal 999)'),
							'type' => 'number',
							'label' => __('Maksimum Angka Unik untuk kode pembayaran (Disarankan maksimal 999)'),
							'default' => '99'
						),
						'mode_kode_pembayaran' => array (
                                                        'type' => 'mode_kode_pembayaran'
                                                 ),
						'enable_insurance' => array (
								'title' => __('Enable Insurance(Fitur Asuransi) <strong>Khusus JNE</strong>','woocommerce'),
								'type' => 'checkbox',
								'label' => __('Aktifkan fitur asuransi'),
								'default' => 'no'
						),					
						'prodcat_with_insurance' => array(
							'title' => __('Product Category dimana asuransi adalah diwajibkan (Pisahkan dengan tanda koma, jika lebih dari satu)'),
							'type' => 'text'
						),
						'packing_kayu_settings' => array(
                                                        'type' => 'packing_kayu_settings'
                                                ),
						'multiple_currency_rate' => array(
                                                        'title' => __('Multi Currency Rate Settings'),
                                                        'type' => 'multiple_currency_rate'
                                                ),
						'epeken_tools' => array (
                                                        'type' => 'epeken_tools'
                                                ),
     				);
	} // End init_form_fields()


   // Our hooked in function - $fields is passed via the filter!
	public function admin_options() {
 		?>
 		<h2><?php _e('Epeken-All-Kurir Shipping Settings','woocommerce'); ?></h2>
		 <table class="form-table">
		 <?php $this->generate_settings_html(); ?>
		 </table> <?php
 	}

	public function generate_multiple_currency_rate_html() {
                ob_start();
                ?>   
                <tr valign="top">
                <th>Multiple Currency Rate Settings</th>
                        <td>
                                <?php $check = get_option('epeken_multiple_rate_setting');
                                ?>
                                <p><input <?php if(empty($check) || $check==='manual') {echo "checked";}?> type="radio" name="epeken_multiple_rate_setting" value="manual"/>Manual</p>
                                <p><input <?php if($check ==='auto'){ echo "checked";}?> type="radio" name="epeken_multiple_rate_setting" value="auto"/>Refers to Bank Indonesia</p>
                                <p><em>Abaikan setting ini jika Anda tidak menginstal plugin <a href="https://id.wordpress.org/plugins/woo-multi-currency/" target="_blank">woocommerce multi currency</a> bersama - sama dengan plugin epeken.</em></p>
                        </td>
                </tr>
                <?php
                return ob_get_clean();
        }

	public function generate_epeken_tools_html() {
                ob_start();
                ?><tr valign="top">
                        <th>Tools</th>
<table style="width: 100%"> <tr>
                        <td>Set Berat Barang 1 kg untuk Barang yang Belum diset beratnya. <input class="button-primary" type=submit name="tools1" value="Lakukan"/></td> </tr>
			<tr>
			<td>
			 Reset Dropship Enabled, kembalikan kota asal dari semua produk ke kota asal default. <input class="button-primary" type="submit" name="tools2" value="Reset Dropship" > 
			</td>
			</tr>
			</table>
                        </tr>
                <?php return ob_get_clean();
        }

 	public function generate_mode_kode_pembayaran_html() {
                ob_start();
                ?>
                <tr valign="top">
                        <th scope="row" class="titledesc"><label for="woocommerce_wc_shipping_tikijne_mode_kode_pembayaran"></label>Mode Kode Pembayaran</th>
                        <td>
                                <?php $mode_kode_pembayaran = get_option('epeken_mode_kode_pembayaran');
                                        if (empty($mode_kode_pembayaran)){
                                                update_option('epeken_mode_kode_pembayaran','+');$mode_kode_pembayaran = '+';
                                        }
                                ?>

                                <input type="radio" name="mode_kode_pembayaran" id="mode_kode_pembayaran" value="+" <?php if(($mode_kode_pembayaran) === '+') echo 'checked';?>>Menambah Ongkos Kirim</input><br><br>
                                <input type="radio" name="mode_kode_pembayaran" id="mode_kode_pembayaran" value="-" <?php if(($mode_kode_pembayaran) === '-') echo 'checked';?>> Mengurangi Ongkos Kirim </input>
                        </td>
                </tr>
                <?php
                return ob_get_clean();
        }

	public function generate_kombinasikan_free_shipping_html() {
		ob_start();
			$freeship_n_city_for_free_shipping = get_option('epeken_freeship_n_city_for_free_shipping');
			$freeship_n_province_for_free_shipping = get_option('epeken_freeship_n_province_for_free_shipping');		
		?>
			<tr valign="top">
                        <th scope="row" class="titledesc"><label for="woocommerce_wc_shipping_tikijne_kombinasikan_free_shipping"></label>Kombinasikan Parameter Free Shipping</th>
                        <td>
				<p>
				<?php $checked = ""; if ($freeship_n_city_for_free_shipping === "on") {$checked = "checked"; } ?> <input type="checkbox" name="freeship_n_city_for_free_shipping" <?php echo $checked; ?> > Kombinasikan Nominal Minimum Belanjaan dan Kota Tujuan Pengiriman Untuk Free Shipping
				</p>
				<p style="margin-top:20px;">
                                   <?php $checked = ""; if ($freeship_n_province_for_free_shipping === "on") {$checked = "checked"; } ?> <input type="checkbox" name="freeship_n_province_for_free_shipping" <?php echo $checked; ?> > Kombinasikan Nominal Minimum Belanjaan dan Provinsi Tujuan Pengiriman Untuk Free Shipping 
                                </p>
			</td>
			</tr>	
		<?
		return ob_get_clean();	
	}

       public function generate_province_for_free_shipping_html() {
                ob_start();
                ?>
                        <tr valign="top">
                        <th scope="row" class="titledesc">
                                <label for="woocommerce_wc_shipping_tikijne_province_for_free_shipping">Pilihan Provinsi</label>
                        </th>
                        <td>  <table><tr><td>
                                <fieldset>
                                <legend class="screen-reader-text"><span>Pilihan Provinsi</span></legend>
                                <select multiple="multiple" class="multiselect chosen_select ajax_chosen_select_city" name="woocommerce_wc_shipping_tikijne_province_for_free_shipping[]" id="woocommerce_wc_shipping_tikijne_province_for_free_shipping" style="width: 450px;" data-placeholder="Pilih Provinsi&hellip; Kosongkan jika tak ingin diset">
                                <?php
                                 $json_all_prv = epeken_get_all_provinces();
				 $provinces = json_decode($json_all_prv, true);
				 $provinces = $provinces["provinces"];
				if(!empty($provinces)){
                                 foreach($provinces as $province) {
                                 $selected = '';
                                 $existing_config = get_option('epeken_province_for_free_shipping');
                                 for($x=0;$x<sizeof($existing_config);$x++){
                                        if($province === $existing_config[$x]){
                                                $selected = 'selected';
                                                break;
                                        }
                                 }
                                ?>
                                <option value="<?php echo $province;?>" <?php echo $selected; ?>><?php echo $province;?></option>
                                <?php
                                 }
				}
                                ?>
                                </select>
                                </fieldset>
                                </td></tr>
                                <tr><td>
                                        <?php $checked = 'checked'; $existing_config = get_option('epeken_is_provinsi_free'); ?>
                                        <input type="radio" name="epeken_is_provinsi_free" value="these_are_free" <?php if($existing_config === 'these_are_free'){echo $checked;}?>>Gratiskan Ongkos Kirim untuk pilihan provinsi tersebut
                                        </input><br><br>
                                         <input type="radio" name="epeken_is_provinsi_free" value="others_are_free" <?php if($existing_config === 'others_are_free'){echo $checked;}?>>Ongkos Kirim selain pilihan provinsi tersebut gratis. Hanya pilihan provinsi tersebut bayar Ongkos Kirim.
                                        </input>
                                </td></tr>
                             </table>
                        </td>
                        </tr>
                <?php
                 return ob_get_clean();
        }
	public function validate_province_for_free_shipping_field($key) {
                $value = $_POST['woocommerce_wc_shipping_tikijne_province_for_free_shipping'];
                return $value;
        }
	public function generate_form_biaya_tambahan_html() {
                ob_start();
                ?>
                <tr>
                  <th scope="row" class="titledesc">Biaya Tambahan</th>
                  <td>
                        <table>
                                <tr>
                                        <td>
                                        Nama Biaya Tambahan
                                        </td>
                                        <td>
                                        <?php $epeken_biaya_tambahan_name = get_option('epeken_biaya_tambahan_name'); ?>
                                        <input type='text' name='epeken_biaya_tambahan_name' value='<?php echo $epeken_biaya_tambahan_name; ?>'/>
                                        </td>
                                <tr>
                                <tr>
                                        <td>
                                        Nominal Biaya Tambahan
                                        </td>
                                        <td>
                                        <?php $epeken_biaya_tambahan_amount = get_option('epeken_biaya_tambahan_amount'); ?>
                                        <input type='text' name='epeken_biaya_tambahan_amount' value='<?php echo $epeken_biaya_tambahan_amount; ?>'/>
                                        </td>
                                <tr>
				 <tr>
                                        <td>
                                        Perhitungan
                                        </td>
                                        <td>
                                        <?php $epeken_perhitungan_biaya_tambahan=get_option('epeken_perhitungan_biaya_tambahan');?>
                                        <select name="epeken_perhitungan_biaya_tambahan">
                                        <option value="percent" <?php if($epeken_perhitungan_biaya_tambahan === 'percent'){echo "selected";}?>>Percentage(%)</option>
                                        <option value="nominal" <?php if($epeken_perhitungan_biaya_tambahan === 'nominal'){echo "selected";}?>>Nominal Addition</option>
                                        </select>
                                        </td>
                                <tr>
                        </table>
                  </td>
                </tr>
                <?php
                return ob_get_clean();
        }


 	public function generate_data_asal_kota_html() {
		ob_start();
		?>
		<tr>
                <th scope="row" class="titledesc">Data Kota Asal (Default)</th>
                <td>
		   <select name="data_asal_kota" id="data_asal_kota">
			<?php 
			$origins = $this -> valid_origins;
				foreach($origins as $element ) {
				echo "<option value='".$element["origin_code"]."'"; if(get_option('epeken_data_asal_kota') === $element['origin_code']){echo " selected";} echo ">".$element["kota_kabupaten"]."</option>";
			} 

			if (empty($origins)) {
				$string = file_get_contents(EPEKEN_KOTA_KAB);
                 	 	$json = json_decode($string,true);
                 	 	$array_kota = $json['listkotakabupaten'];
				?><option value=0>None</option><?php
				$idx = 1;
                	 	foreach($array_kota as $element){
					?><option value=<?php echo $idx; if(get_option('epeken_data_asal_kota') == $idx){echo " selected";}?>><?php echo $element["kotakab"]?></option>
					<?php
					$idx++;
	                 	}
			}
		
			?>
		   </select> 
		<script type='text/javascript'>
                                jQuery(document).ready(function($){
                                        $('#data_asal_kota').select2();
                                });
                        </script>
                </td>
                </tr>
		<?php
		return ob_get_clean();
	}

	public function generate_packing_kayu_settings_html() {
                ob_start(); 
                ?>   
                 <tr> 
                        <th scope="row" class="titledesc">Packing Kayu Settings (Khusus JNE)</th>
                        <td> 
                        <div style="position: relative; float: left; margin-top: 00px;">      
                                <?php 
                                        $epeken_packing_kayu_enabled = get_option('epeken_packing_kayu_enabled'); 
                                        $epeken_pengali_packing_kayu = get_option('epeken_pengali_packing_kayu');
                                        $epeken_pc_packing_kayu = get_option('epeken_pc_packing_kayu');
                                        $tmptxt = "";
                                        if ($epeken_packing_kayu_enabled === "yes") {$tmptxt = "checked";};
                                ?>   
                                <input type="checkbox" name="woocommerce_epeken_packing_kayu_enabled" id="woocommerce_epeken_packing_kayu_enabled" <?php echo $tmptxt; ?>> Enable/Disable Packing Kayu<br>
                                Rumus Perhitungan Packing Kayu : <input type="text" name="woocommerce_epeken_pengali_packing_kayu" id="woocommerce_epeken_pengali_packing_kayu" value="<?php echo $epeken_pengali_packing_kayu;?>"> kali dari berat paket keseluruhan.<br>
                                Product Category Wajib dengan Packing Kayu (Pisahkan dengan tanda koma, jika lebih dari satu) : <input type="text" name="woocommerce_epeken_pc_packing_kayu" id="woocommerce_epeken_pc_packing_kayu" value = "<?php echo $epeken_pc_packing_kayu; ?>">
                        </div>
                        </td>
                </tr>
                 <?php
                return ob_get_clean();
        }    

	public function generate_free_shipping_product_category_html() {
		ob_start();
		 ?>
	       <tr>
                <th scope="row" class="titledesc">Free Shipping Based on Product Category</th>
		<td>
		 <div style="position: relative; float: left;width: 40%; padding: 5px;">
		  Masukkan Produk Category gratis ongkir, pisahkan dengan tanda koma jika lebih dari satu:<br>
		  <input type="text" name="woocommerce_epeken_free_pc" id="woocommerce_epeken_free_pc" style="width: 350px;" value="<?php echo get_option('epeken_free_pc','') ?>">
		 </div>
		  <div style="position: relative; float: left;width: 40%; padding: 5px;">
		  Jumlah(Quantity) minimal dari item produk category gratis ongkir:<br>
		  <input type="number" min="1" style="width: 60px;" name="woocommerce_epeken_free_pc_q" id="woocommerce_epeken_free_pc_q" value="<?php echo get_option('epeken_free_pc_q','1') ?>">
                 </div>
		</td>
		</tr>
		 <?php
		return ob_get_clean();
	}

	public function generate_panel_enable_kurir_html() {
		ob_start();
		 ?>
		<tr>
		<th scope="row" class="titledesc">Pilih Kurir Yang di-enable</th>	
		<td style="height: 100px;">
			<?php $en_jne = get_option('epeken_enabled_jne'); $en_tiki = get_option('epeken_enabled_tiki'); $en_pos = get_option('epeken_enabled_pos'); $en_rpx = get_option('epeken_enabled_rpx');	$en_esl = get_option('epeken_enabled_esl'); $en_jne_reg = get_option('epeken_enabled_jne_reg'); $en_jne_oke = get_option('epeken_enabled_jne_oke'); $en_jne_yes = get_option('epeken_enabled_jne_yes'); $en_tiki_hds = get_option('epeken_enabled_tiki_hds'); $en_tiki_ons = get_option('epeken_enabled_tiki_ons'); $en_tiki_reg = get_option('epeken_enabled_tiki_reg'); $en_tiki_eco = get_option('epeken_enabled_tiki_eco'); $en_wahana = get_option('epeken_enabled_wahana'); $en_jetez = get_option('epeken_enabled_jetez');$en_sicepat_reg = get_option('epeken_enabled_sicepat_reg');$en_sicepat_best = get_option('epeken_enabled_sicepat_best');?>
			<div style="clear: left;">
			<p><div style="position: relative; float: left; padding: 5px"><input name="enabled_jne" id = "enabled_jne" type="checkbox" <?php if ($en_jne === "on"){echo "checked";} ?>><strong>JNE</strong></input></div></p>
			<p><div style="position: relative; float: left; padding: 5px"><input name="enabled_jne_reg" id = "enabled_jne_reg" type="checkbox" <?php if ($en_jne_reg === "on"){echo "checked";} ?> onclick='f01()'>JNE REGULAR</input></div></p>
			<p><div style="position: relative; float: left; padding: 5px"><input name="enabled_jne_oke" id = "enabled_jne_oke" type="checkbox" <?php if ($en_jne_oke === "on"){echo "checked";} ?> onclick='f01()'>JNE OKE</input></div></p>
			<p><div style="position: relative; float: left; padding: 5px"><input name="enabled_jne_yes" id = "enabled_jne_yes" type="checkbox" <?php if ($en_jne_yes === "on"){echo "checked";} ?> onclick='f01()'>JNE YES</input></div>
<?php $epeken_markup_tarif_jne = get_option('epeken_markup_tarif_jne'); ?>
                        <div style="float: left;width: 100%; margin-bottom: 5px;">Mark up tarif JNE : Rp. <input type="number" placeholder="0" style="width: 80px;" name="epeken_markup_tarif_jne"  value="<?php echo $epeken_markup_tarif_jne; ?>"/> /kg</div>
<?php $epeken_diskon_tarif_jne = get_option('epeken_diskon_tarif_jne'); ?>
			<div style="float: left;width: 100%; margin-bottom: 5px;">Diskon Tarif JNE : <input type="number" placeholder="0" style="width: 80px;" name="epeken_diskon_tarif_jne"  value="<?php echo $epeken_diskon_tarif_jne; ?>"/>%</div> 
			<div style="float: left;width: 100%; margin-bottom: 5px;"><hr></div>
</p>
			</div>
			<div style="clear: left;">
			<p><div style="position: relative; float: left; padding: 5px"><input name="enabled_tiki" id="enabled_tiki" type="checkbox" <?php if ($en_tiki === "on"){echo "checked";} ?>><strong>TIKI</strong></input></div></p>
			 <p><div style="position: relative; float: left; padding: 5px"><input name="enabled_tiki_hds" id="enabled_tiki_hds" type="checkbox" <?php if ($en_tiki_hds === "on"){echo "checked";} ?> onclick='f02()'>TIKI HDS</input></div></p>
			  <p><div style="position: relative; float: left; padding: 5px"><input name="enabled_tiki_ons" id="enabled_tiki_ons" type="checkbox" <?php if ($en_tiki_ons === "on"){echo "checked";} ?> onclick='f02()'>TIKI ONS</input></div></p>
			 <p><div style="position: relative; float: left; padding: 5px"><input name="enabled_tiki_reg" id="enabled_tiki_reg" type="checkbox" <?php if ($en_tiki_reg === "on"){echo "checked";} ?> onclick='f02()'>TIKI REG</input></div></p>
			<p><div style="position: relative; float: left; padding: 5px"><input name="enabled_tiki_eco" id="enabled_tiki_eco" type="checkbox" <?php if ($en_tiki_eco === "on"){echo "checked";} ?> onclick='f02()'>TIKI ECO</input></div>
<?php $epeken_markup_tarif_tiki = get_option('epeken_markup_tarif_tiki'); ?>
                        <div style="float: left;width: 100%; margin-bottom: 5px;">Mark up tarif TIKI : Rp. <input type="number" placeholder="0" style="width: 80px;" name="epeken_markup_tarif_tiki" value="<?php echo $epeken_markup_tarif_tiki; ?>" /> /kg</div>
<?php $epeken_diskon_tarif_tiki = get_option('epeken_diskon_tarif_tiki'); ?>
			<div style="float: left;width: 100%; margin-bottom: 5px;">Diskon Tarif TIKI : <input type="number" placeholder="0" style="width: 80px;" name="epeken_diskon_tarif_tiki"  value="<?php echo $epeken_diskon_tarif_tiki; ?>"/>%</div> 	
			 <div style="float: left;width: 100%; margin-bottom: 5px;"><hr></div>
			</p>
			</div>
			<div style="clear: left;">
			<p><div style="position: relative; float: left; padding: 5px"><input name="enabled_pos" id = "enabled_pos" type="checkbox" <?php if ($en_pos === "on"){echo "checked";} ?>>POS Indonesia</input></div></p>
                        <p><div style="position: relative; float: left; padding: 5px"><input name="enabled_rpx" id="enabled_rpx" type="checkbox" <?php if ($en_rpx === "on"){echo "checked";} ?>>RPX</input></div></p>
                        <p><div style="position: relative; float: left; padding: 5px"><input name="enabled_esl" id="enabled_esl" type="checkbox" <?php if ($en_esl === "on"){echo "checked";} ?>>ESL</input></div></p>
			</div>
<?php $epeken_markup_tarif_pos = get_option('epeken_markup_tarif_pos'); ?>
                        <div style="float: left;width: 100%; margin-bottom: 5px;">Mark up tarif POS : Rp. <input type="number" placeholder="0" style="width: 80px;" name="epeken_markup_tarif_pos" value="<?php echo $epeken_markup_tarif_pos; ?>"/> /kg</div>	
			<?php $epeken_diskon_tarif_pos = get_option('epeken_diskon_tarif_pos'); ?>
<div style="float: left;width: 100%; margin-bottom: 5px;">Diskon Tarif POS : <input type="number" placeholder="0" style="width: 80px;" name="epeken_diskon_tarif_pos"  value="<?php echo $epeken_diskon_tarif_pos; ?>"/>%</div>
			<div style="float: left;width: 100%; margin-bottom: 5px;"><hr></div>
			<div style="clear: left;">
			<p><div style="position: relative; float: left; padding: 5px"><input name="enabled_wahana" id = "enabled_wahana" type="checkbox" <?php if ($en_wahana === "on"){echo "checked";} ?>>WAHANA</input></div></p>
			</div>
			<div style="clear: left;">
                        <p><div style="position: relative; float: left; padding: 5px"><input name="enabled_jetez" id = "enabled_jetez" type="checkbox" <?php if ($en_jetez === "on"){echo "checked";} ?>>JET EZ (<em>www.jet.co.id</em>)</input></div></p>
                       </div>	
			<div style="clear: left;"> 
			<p><div style="position: relative; float: left; padding: 5px"><input name="enabled_sicepat_reg" id="enabled_sicepat_reg" type="checkbox" <?php if($en_sicepat_reg === "on"){echo "checked";}?>>SICEPAT REGULAR</input></div></p><p><div style="position: relative; float: left; padding: 5px"><input name="enabled_sicepat_best" id="enabled_sicepat_best" type="checkbox" <?php if($en_sicepat_best === "on"){echo "checked";}  ?>>SICEPAT BEST</input></div></p>               
			<div style="float: left;width: 100%; margin-bottom: 5px;"><hr></div>
			<div style="clear: left;">
			<?php $epeken_subsidi_ongkir =  get_option('epeken_subsidi_ongkir'); $epeken_subsidi_min_purchase = get_option('epeken_subsidi_min_purchase'); ?>	
			<p>Subsidi Ongkos Kirim Rp. <input type="number" name="txt_subsidi_ongkir" value="<?php echo $epeken_subsidi_ongkir; ?>"/> dengan syarat minimal pembelian Rp. <input type="number" name="txt_subsidi_min_purchase" value = "<?php echo $epeken_subsidi_min_purchase; ?>"/></p>
			<p><em>Isikan nilai subsidi ongkir yang ingin Anda berikan untuk pelanggan Anda. Jika Ongkir lebih dari nilai ini, maka Pelanggan akan mendapatkan potongan sejumlah yang Anda masukkan nilainya sebagai subsidi ongkir. Jika nilai ongkir kurang dari jumlah ini, maka ongkir akan gratis. Subsidi ongkir ini akan berlaku untuk semua kurir. Kosongkan syarat minimal pembelian jika subsidi ini tanpa ada syarat minimal pembelian.</em></p>
			</div>
		</td>
		</tr>
		<script language="javascript">
                        var epjneelm = document.getElementById('enabled_jne');
			var epjneregelm = document.getElementById('enabled_jne_reg');
			var epjneokeelm = document.getElementById('enabled_jne_oke');
			var epjneyeselm = document.getElementById('enabled_jne_yes');
			var eptikielm = document.getElementById('enabled_tiki');
			var eptikihdselm = document.getElementById('enabled_tiki_hds');
                        var eptikionselm = document.getElementById('enabled_tiki_ons');
                        var eptikiregelm = document.getElementById('enabled_tiki_reg');
			var eptikiecoelm = document.getElementById('enabled_tiki_eco');
                        epjneelm.onclick=function(){
				if(!epjneelm.checked)
				{
					epjneregelm.checked = false;
					epjneokeelm.checked = false;
					epjneyeselm.checked = false;
				}else{
					epjneregelm.checked = true;
                                        epjneokeelm.checked = true;
                                        epjneyeselm.checked = true;
				}
                        };

			eptikielm.onclick=function(){
				if(!eptikielm.checked)
				{
					eptikihdselm.checked = false;
					eptikionselm.checked = false;
					eptikiregelm.checked = false;
					eptikiecoelm.checked = false;
				}else{
					eptikihdselm.checked = true;
                                        eptikionselm.checked = true;
                                        eptikiregelm.checked = true;
                                        eptikiecoelm.checked = true;
				}
			};
			function f01() {
				epjneelm.checked = true;
				if(!epjneregelm.checked && !epjneokeelm.checked && !epjneyeselm.checked) {
					epjneelm.checked = false;	
				}
			}      
			function f02() {
                                eptikielm.checked = true;
                                if(!eptikihdselm.checked && !eptikionselm.checked && !eptikiregelm.checked && !eptikiecoelm.checked) {
                                        eptikielm.checked = false;       
                                }
                        }     			
                </script>
		 <?php
		return ob_get_clean();
	}

        public function get_jne_class_value(){
                $postdata = explode('&',$_POST['post_data']);
                $jneclasspost = '';
                foreach ($postdata as $value) {
                        if (strpos($value,'order_comments') !== FALSE) {
                                $jneclasspost = $value; 
                                $jneclassar = explode('=',$jneclasspost);
                                $jneclasspost = $jneclassar[1]; 
                                break;
                        }
                }
       	         $this -> jneclass = $jneclasspost;
        }                       

	public function get_checkout_post_data($itemdata){
		$postdata = explode('&',$_POST['post_data']);
		$post_data_ret = '';
		foreach ($postdata as $value) {
                        if (strpos($value,$itemdata) !== FALSE) {
                                $post_data_ret = $value;
                                $ar = explode('=',$post_data_ret);
                                $post_data_ret = $ar[1];
                                break;
                        }
                }
		$post_data_ret = str_replace('+',' ',$post_data_ret);
		return $post_data_ret;
	}

        public function get_origin_kurir() {
		global $woocommerce;
		$city = "";
		$items = $woocommerce -> cart -> get_cart();
		if(sizeof($items) === 0)
			return $city;
		$first_item = reset($items);
		$origin_code = get_post_meta($first_item['product_id'],'product_origin',true) ;
		if(!empty($origin_code)) {
		 $city = epeken_code_to_city($origin_code);	
		}else{
		 $city = epeken_code_to_city(get_option('epeken_data_asal_kota')); //$this -> settings['data_kota_asal']); 
		}
		return $city;
	}
		
	public function set_shipping_cost() {
			global $wpdb;
                        $sql = 'DELETE FROM ' . $wpdb->options . ' WHERE option_name LIKE "_transient_%"';
                        $wpdb->query($sql);
			$wooversion = $this -> epeken_get_woo_version_number();
                        $wooversion = substr($wooversion, 0,3);

                        $post_action = '';
                        $val_post_action = '';
                        if ($wooversion > 2.3) {
                          $post_action = isset($_GET['wc-ajax']) ? $_GET['wc-ajax'] : '';
                          $val_post_action = 'update_order_review';
                        } else {
                          $post_action = isset($_POST['action']) ? $_POST['action'] : '';
                          $val_post_action = 'woocommerce_update_order_review';
                        }

                         //if($post_action === 'woocommerce_update_order_review')       { // this is obsolete.
                           if ($post_action === $val_post_action)      { // woocommerce starting v.2.4 use this
				$isshippedifadr = $this -> get_checkout_post_data('ship_to_different_address');
				$_SESSION['isshippedifadr'] = $isshippedifadr;
                                        if($isshippedifadr === '1'){ 
                                         $this -> shipping_kecamatan = $this -> get_checkout_post_data('shipping_address_2');
					 $this -> shipping_city = $this -> get_checkout_post_data('shipping_city');
                                        }else{
					 $this -> shipping_city = $this -> get_checkout_post_data('billing_city');
                                         $this -> shipping_kecamatan = $this -> get_checkout_post_data('billing_address_2');
                                        }
			   }else{
				   //if(!empty($_POST['shipping_city']))	{
				   if($_SESSION['isshippedifadr'] === '1' ) {
				     $this -> shipping_city = sanitize_text_field($_POST['shipping_city']);
				   } else {
				     $this -> shipping_city = sanitize_text_field($_POST['billing_city']);
				   }
                                   //if(!empty($_POST['shipping_address_2']))  {
                                   if($_SESSION['isshippedifadr'] === '1' ) {
				     $this -> shipping_kecamatan = sanitize_text_field($_POST['shipping_address_2']);
                                   } else {
                                     $this -> shipping_kecamatan = sanitize_text_field($_POST['billing_address_2']);
                                   }   
		 	   }
			  $this -> origin_city = $this -> get_origin_kurir(); //city_name
			  unset($this -> array_of_tarif);
                          $this -> array_of_tarif = array();

			  $content_tarif = epeken_get_tarif($this -> shipping_city,$this -> shipping_kecamatan, $this -> origin_city);

			  if($content_tarif === "") {
					//array_push($this -> array_of_tarif, array('id' => 'Epeken-Courier','label' => 'Terjadi gangguan menentukan ongkos kirim. Silakan hubungi Administrator.', 'cost' => '0'));
					return;
				}
			  $json = json_decode($content_tarif);

			  $status = $json -> {'status'} -> {'code'};

			  if(empty($status)) {
				//array_push($this -> array_of_tarif, array('id' => 'Epeken-Courier','label' => 'Terjadi gangguan menentukan ongkos kirim. Silakan hubungi Administrator.', 'cost' => '0')); 
				return;
			 }
			  
			  if ($status != 200){
				array_push($this -> array_of_tarif, array('id' => 'Epeken-Courier','label' => 'Error '.$status.':'.$json -> {'status'} -> {'description'}.' atau silakan menghubungi Administrator.', 'cost' => '0'));	
				return;
			  }

 			  $this -> destination_province = $json -> {'destination_details'} -> {'province'};
                          $this -> map_destination_province();

                         if($isshippedifadr === '1'){
                           add_action('woocommerce_review_order_before_cart_contents',array(&$this,'epeken_triger_shipping_province'));
                         } else {
                           add_action('woocommerce_review_order_before_cart_contents',array(&$this,'epeken_triger_billing_province'));
                         }
			
			 $this -> chosen_shipping_method = trim($_POST['shipping_method'][0]);
			 $chosen_shipping = WC()->session->get('chosen_shipping_methods');

			 if(strpos($chosen_shipping[0], 'jne') !==false || strpos($chosen_shipping[0], 'JNE') !==false ||strpos($this -> chosen_shipping_method,'jne') !==false || strpos($this -> chosen_shipping_method,'JNE') !==false) {
			    add_action( 'woocommerce_cart_calculate_fees', array($this,'calculate_insurance'));
			 }

			if(strpos(strtolower($chosen_shipping[0]), 'pickup') === false)
                          add_action('woocommerce_cart_calculate_fees', array($this, 'calculate_biaya_tambahan'));
			  $opt_vol_matrix = $this -> settings['volume_matrix'];
			  $json_tarrifs= $json->{'results'};//[0]->{'costs'};
			  $services = array();
			  $berat_asli = 0;
			  foreach($json_tarrifs as $element){
				$kurir = $element -> {'code'};
				$is_volumetrik = $element->{'is_volumetrik'};
				$element = $element -> {'costs'};
				foreach($element as $element_cost) {
				 $service = $element_cost -> {'service'};
				 $rate = $element_cost ->{'cost'}[0]->{'value'};				
				 if ($opt_vol_matrix === "yes") {
					$this -> count_cart_weight_and_dimension();
					$this -> is_packing_kayu_valid = false;
					$shipping_total_weight_woodpack = 1;
					$shipping_metric_dimension_woodpack = 1;

					if(get_option('epeken_packing_kayu_enabled') === "yes"){
                                        $pengali = get_option('epeken_pengali_packing_kayu');
                                        if(empty($pengali) || $pengali < 1) { 
                                                $pengali = 1; 
                                        }    
     
                                        $pengali = str_replace(",",".", $pengali);
     
                                        $array_of_packing_kayu_prod_cat = explode(",",get_option('epeken_pc_packing_kayu',''));
                                        global $woocommerce;
                                        $contents = $woocommerce->cart->cart_contents;
                                        foreach($contents as $content) {
                                                $product_id = $content['product_id'];
                                                $tmp_boolean = false;
                                                for($i=0;$i<sizeof($array_of_packing_kayu_prod_cat);$i++){
                                                 $tmp_boolean = epeken_is_product_in_category($product_id,trim($array_of_packing_kayu_prod_cat[$i]));
                                                 /* packing kayu product based */
                                                 if (!$tmp_boolean) {
                                                        $product_wood_pack_mandatory = get_post_meta($product_id,'product_wood_pack_mandatory',true);
                                                        if ($product_wood_pack_mandatory === 'on')
                                                         $tmp_boolean = true;   
                                                 }    
                                                 /* --- */
                                                        if($tmp_boolean == true){
                                                                break;
                                                        }    
                                                 }    
                                                 if($tmp_boolean == true){
                                                                $this -> is_packing_kayu_valid = true;
								$shipping_total_weight_woodpack = ($this->bulatkan_berat($this -> shipping_total_weight)) * $pengali;
                                                                $shipping_metric_dimension_woodpack = ($this -> bulatkan_berat($this -> shipping_metric_dimension)) * $pengali;
                                                                break;
                                                 }    

                                            }    
                                        }

					$berat_asli = $this -> shipping_total_weight;
					$this -> shipping_total_weight = $this -> bulatkan_berat($this->shipping_total_weight);
                                        $this -> shipping_metric_dimension = $this -> bulatkan_berat ($this -> shipping_metric_dimension);
					if($kurir === "jne" || $kurir === "tiki" || $kurir === "JNE" || $kurir === "TIKI") {	
					 if ($this -> shipping_total_weight >= $this -> shipping_metric_dimension) {
						$rate = $rate * $this -> shipping_total_weight;
						if(trim(strtolower($kurir)) === 'jne' && $this -> is_packing_kayu_valid) 
						  $rate = $rate * $shipping_total_weight_woodpack;
							
					 }else{
						$rate = $rate * $this -> shipping_metric_dimension;
						if(trim(strtolower($kurir)) === 'jne' && $this -> is_packing_kayu_valid) 
							$rate = $rate * $shipping_metric_dimension_woodpack;
					 }
					}else{
						$tmp_rate = $rate;
						$rate = $rate * $this -> shipping_total_weight;
						if(!empty($is_volumetrik) && $is_volumetrik === "N")
						 $rate = $tmp_rate; 
					}
				  }
				 $markup = $this -> additional_mark_up($kurir,$this -> shipping_total_weight);
                                 $rate = $rate + $markup;
				 $service_detail = array('kurir' => $kurir, 'service' => $service , 'rate' => $rate);
				 array_push($services,$service_detail);
				}
			  }
			add_action('woocommerce_cart_calculate_fees', array($this, 'calculate_angka_unik'));

                	 $this -> additionalLabel ="";
                	 if ($opt_vol_matrix === "yes"){
                         	$this -> additionalLabel = "<br><span style='font-weight: normal;'>Total Weight(Berat Asli): ".$berat_asli."kg<br>Total Weight(Pembulatan): ".$this->shipping_total_weight." kg<br>Dimension: ".$this->shipping_metric_dimension."</span><br>";
				add_action('woocommerce_review_order_after_cart_contents',array(&$this,'add_volume_dimension_label'));
				add_action('woocommerce_checkout_update_order_meta',  array($this, 'add_order_meta_weight_dimension'));
                	 }
			 if($this -> settings['is_kota_asal_in_product_details'] === 'yes') {
				add_action('woocommerce_review_order_after_cart_contents',array(&$this,'add_ori_dest_info'));
			 }

		      foreach($services as $services_element) {
			 $id = $services_element['kurir'].'_'.$services_element['service'];
			 $label = strtoupper($services_element['kurir'].' '.$services_element['service']);
			 if ($this -> is_shipping_exclude($label))
				continue;
			 $cost = $services_element['rate'];
			 array_push($this -> array_of_tarif, array('id' => $id,'label' => $label, 'cost' => $cost));
			}

			$en_pos = get_option('epeken_enabled_pos');
			if ($en_pos === "on") {
			 $weight = 1000;
			 $length = 0;
			 $width = 0;
			 $height = 0;
			 $price = 0;

			 if ($opt_vol_matrix === "yes") {
			  $this -> count_cart_weight_and_dimension();
			  $weight = $this -> shipping_total_weight*1000;
			  $length = $this -> shipping_total_length;
			  $width = $this -> shipping_total_width;
			  $height = $this -> shipping_total_height;
			  $price = $this -> get_cart_total();
			 }

			 if($this -> current_currency !== "IDR") {
				$price = $price * ($this -> current_currency_rate);
			 }

			 $content_pos = epeken_get_tarif_pt_pos_v2($this -> shipping_city,$weight, $price, $length, $width, $height, $this -> origin_city );
			 $content_pos_json_decode = json_decode($content_pos);
			 $content_pos_json_decode = $content_pos_json_decode -> {'tarifpos'};
			 foreach($content_pos_json_decode as $element){
				$package_name = $element -> {'class'};
				$cost_value = $element -> {'cost'};
				$markup = $this->additional_mark_up('pos',$this -> shipping_total_weight);
                                $cost_value = $cost_value + $markup;
				array_push($this -> array_of_tarif, array('id' => $package_name,'label' => "PT POS - ".$package_name, 'cost' => $cost_value));
			 } 
			}

			$en_wahana = get_option('epeken_enabled_wahana');
			if ($en_wahana === "on") {
			 $content_wahana = epeken_get_wahana_ongkir($this->shipping_city,$this-> shipping_kecamatan,$this->bulatkan_berat($this->shipping_total_weight), $this->origin_city);
			 $content_wahana_decoded = json_decode($content_wahana);
			 if (!empty($content_wahana_decoded)) {
			 $content_wahana_decoded = $content_wahana_decoded -> {'tarifwahana'};
				foreach($content_wahana_decoded as $element) {
				 $package_name = $element -> {'class'};
				 $cost_value = $element -> {'cost'};
				 if ($cost_value !== "0")
				 array_push($this -> array_of_tarif, array('id' => $package_name,'label' => $package_name, 'cost' => $cost_value));
				}
			 }
			}

			$en_jetez = get_option('epeken_enabled_jetez');
                         if($en_jetez === "on") {
                                $content_jet = epeken_get_jet_ongkir($this -> shipping_city, $this -> shipping_kecamatan, $this->bulatkan_berat($this->shipping_total_weight), $this -> origin_city);
                                $content_jet_decoded = json_decode($content_jet);
                                if(!empty($content_jet_decoded)) {
                                        $content_jet_decoded = $content_jet_decoded -> {'tarifjnt'};
                                       foreach($content_jet_decoded as $element) {
                                                $package_name = $element -> {'class'}; $cost_value = $element -> {'cost'}; if ($cost_value !== "0") array_push($this -> array_of_tarif, array('id' => 'jet.co.id_'.$package_name,'label' => 'J&T Express '.$package_name, 'cost' => $cost_value));
                                        }    
                                }    
     
                         } 	
			$en_sicepat_reg = get_option('epeken_enabled_sicepat_reg');
			$en_sicepat_best = get_option('epeken_enabled_sicepat_best');
			if($en_sicepat_reg === "on" || $en_sicepat_best === "on") {
				$content_sicepat = epeken_get_sicepat_ongkir($this -> shipping_city, $this -> shipping_kecamatan, $this->bulatkan_berat($this->shipping_total_weight), $this -> origin_city);
				$content_sicepat_decoded = json_decode($content_sicepat);
				$content_sicepat_decoded = $content_sicepat_decoded -> {'tarifsicepat'};
				foreach($content_sicepat_decoded as $element) {
						$package_name = $element -> {'class'}; if($package_name === "REGULAR" && $en_sicepat_reg !== "on") continue; if($package_name === "BEST" && $en_sicepat_best !== "on") continue; $cost_value = $element -> {'cost'}; if ($cost_value !== "0") array_push($this -> array_of_tarif, array('id' => 'sicepat_'.$package_name,'label' => 'SICEPAT '.$package_name, 'cost' => $cost_value));		
				}
			}	
			
			  
	}

	public function add_order_meta_weight_dimension($order_id) {
                update_post_meta($order_id, 'weight', $this -> shipping_total_weight);
                update_post_meta($order_id, 'dimension', $this -> shipping_metric_dimension);
        }  

	public function additional_mark_up($kurir,$weight) {
		
                if(strtolower($kurir) === 'jne' && is_numeric(get_option('epeken_markup_tarif_jne')))
                        return $weight*(get_option('epeken_markup_tarif_jne'));

                if(strtolower($kurir) === 'tiki' && is_numeric(get_option('epeken_markup_tarif_tiki')))
                        return $weight*(get_option('epeken_markup_tarif_tiki'));

                if(strtolower($kurir) === 'pos' && is_numeric(get_option('epeken_markup_tarif_pos')))
                        return $weight*(get_option('epeken_markup_tarif_pos'));


                return 0;
        }    

	public function is_shipping_exclude ($shipping_label) {
		$ret = false;
		if ($shipping_label === 'JNE SPS' || $shipping_label === 'JNE CTCSPS' || $shipping_label === 'TIKI SDS' || $shipping_label === 'RPX SDP' || $shipping_label === 'JNE CTCBDO' || $shipping_label === 'JNE PELIK')  {
			$ret = true;	
			return $ret;
		}
	        	
                $en_jne = get_option('epeken_enabled_jne'); $en_tiki = get_option('epeken_enabled_tiki'); $en_pos = get_option('epeken_enabled_pos'); 
		$en_rpx = get_option('epeken_enabled_rpx'); $en_esl = get_option('epeken_enabled_esl');
		$en_jne_reg = get_option('epeken_enabled_jne_reg');
		$en_jne_oke = get_option('epeken_enabled_jne_oke');
		$en_jne_yes = get_option('epeken_enabled_jne_yes');
		$en_tiki_hds = get_option('epeken_enabled_tiki_hds');
		$en_tiki_ons = get_option('epeken_enabled_tiki_ons');
		$en_tiki_reg = get_option('epeken_enabled_tiki_reg');
		$en_tiki_eco = get_option('epeken_enabled_tiki_eco');

	 	if (empty($en_jne) && strpos(substr($shipping_label,0,3),"JNE") !== false) {
			$ret = true;
		}else if(empty($en_tiki) && strpos(substr($shipping_label,0,3),"TIK") !== false) {
			$ret = true;
		}else if(empty($en_pos) && strpos(substr($shipping_label,0,3),"POS") !== false)  {
			$ret = true;
		}else if(empty($en_rpx) && strpos(substr($shipping_label,0,3),"RPX") !== false) {
			$ret = true;
		}else if(empty($en_esl) && strpos(substr($shipping_label,0,3),"ESL") !== false) {
			$ret = true;
		}else if(empty($en_jne_reg) && ($shipping_label === "JNE CTC" || $shipping_label === "JNE REG") !== false){
			$ret = true;
		}else if(empty($en_jne_oke) && ($shipping_label === "JNE CTCOKE" || $shipping_label === "JNE OKE") !== false){
			$ret = true;
		}else if(empty($en_jne_yes) && ($shipping_label === "JNE CTCYES" || $shipping_label === "JNE YES") !== false){
			$ret = true;
		}else if(empty($en_tiki_hds) && ($shipping_label === "TIKI HDS") !== false){
                        $ret = true;
		}else if(empty($en_tiki_ons) && ($shipping_label === "TIKI ONS") !== false){
                        $ret = true;
                }else if(empty($en_tiki_reg) && ($shipping_label === "TIKI REG") !== false){
                        $ret = true;
                }else if(empty($en_tiki_eco) && ($shipping_label === "TIKI ECO") !== false){
                        $ret = true;
                }
		return $ret;
	}

	public function map_destination_province(){
                if($this->destination_province === "Nanggroe Aceh Darussalam (NAD)"){
                        $this -> destination_province = "Daerah Istimewa Aceh";
                }else if($this->destination_province === "DI Yogyakarta"){
                        $this -> destination_province = "Daerah Istimewa Yogyakarta";
                }else if($this->destination_province === "Nusa Tenggara Barat (NTB)"){
                        $this -> destination_province = "Nusa Tenggara Barat";
                }else if($this->destination_province === "Nusa Tenggara Timur (NTT)"){
                        $this -> destination_province = "Nusa Tenggara Timur";
                }
        }

         public function epeken_triger_billing_province () {
          ?>      <script type="text/javascript">
                        jQuery(document).ready(function($){
                                        var pro = '<?php echo $this->destination_province; ?>';
                                        //alert(pro);
                                        $('#billing_state').attr('disabled',false);
                                        //$('#billing_state option').removeAttr('selected');
                                        $('#billing_state option').each(function(){if($.trim($(this).text()) == $.trim(pro)){$(this).attr('selected',true);}});
					$('#billing_state').change();
                                        $('#billing_state').attr('disabled',true);
                                });
                    </script>
                <?php
          }

        public function epeken_triger_shipping_province () {
          ?>      <script type="text/javascript">
                        jQuery(document).ready(function($){
                                        $('#billing_state').attr('disabled',false);
                                        var pro = '<?php echo $this->destination_province; ?>';
                                        $('#shipping_state').attr('disabled',false);
                                        $('#shipping_state option').removeAttr('selected');
                                        $('#shipping_state option').each(function(){if($.trim($(this).text()) == $.trim(pro)){$(this).attr('selected',true);$('#shipping_state').change();}});
                                        $('#shipping_state').attr('disabled',true);
                                });
                    </script>
                <?php
          }

	public function add_volume_dimension_label() {
		?>
			<tr>
				<td><strong>Berat/Dimensi</strong>
				</td>
				<td>
				<?php echo $this->additionalLabel; 
				if($this -> is_packing_kayu_valid === true) {
                                   ?><br>Khusus untuk pengiriman dengan JNE paket ini wajib dikirim dengan packing kayu sehingga beratnya dihitung <?php echo get_option("epeken_pengali_packing_kayu"); ?> kali dari berat semula.<?php
                                }
				?>
				</td>
			</tr>
		<?php
	}

	public function add_ori_dest_info() {
		?>
			<tr>
                                <td><strong>Kota Asal Pengiriman</strong>
                                </td>
                                <td>
                                  <?php echo $this->origin_city; ?>
                                </td>
                        </tr>
			<tr>
                                <td><strong>Kota Tujuan Pengiriman</strong>
                                </td>
                                <td>
                                  <?php echo $this->shipping_city; ?>
                                </td>
                        </tr>
		<?php
	}
	
	
	public function count_decimal_value($weight){
                if ($weight < 1){
                        return 0;
                }
                $dec_val = 0;
                $tmp_weight = $weight;
                while($tmp_weight >= 1){
                        $tmp_weight = $tmp_weight - 1;
                }

                $dec_val = $tmp_weight;
                return $dec_val;
        }

	public function get_cart_total() {
		global $woocommerce;
		$price = 0;
		foreach($woocommerce -> cart -> get_cart() as $value){
			$product_data = $value['data'];
			$price = $price + (floatval($value['quantity']) * floatval($product_data -> price));
		}
		return $price;
	}

	public function count_cart_weight_and_dimension(){
		 global $woocommerce;
		 $this -> shipping_total_weight = 0;
		 $this -> shipping_metric_dimension = 0;
                        $cart_weight = 0;
			$metric_dimension = 0;
			$length=0;$width=0;$height=0;
                                foreach($woocommerce -> cart -> get_cart() as $value){
                                        $product_data = $value['data'];
                                        $cart_weight = $cart_weight + (floatval($value['quantity']) * floatval($product_data -> weight));
					
					//if($metric_dimension < ((intval($product_data -> length) * intval($product_data -> width) * intval ($product_data -> height))/6000)){
					$metric_dimension = $metric_dimension + ((intval($product_data -> length) * intval($product_data -> width) * intval ($product_data -> height)) * $value['quantity'] /6000);
					$length = $length + intval($product_data -> length);
					$width = $width + intval($product_data -> width);
					$height = $height + intval($product_data -> height);
					//}
                                }
		$this -> shipping_total_length = $length;
		$this -> shipping_total_width = $width;
		$this -> shipping_total_height = $height;
		$this -> shipping_total_weight = $cart_weight;
		$this -> shipping_metric_dimension = $metric_dimension;
		if($this -> settings['satuan_berat'] === "1") {
			$this -> shipping_total_weight = $this -> shipping_total_weight/1000;
		}
	}

	public function bulatkan_berat($cart_weight){
		$treshold = 0.3;
		if($this -> origin_city === 'Kota Batam')
			$treshold = 0.4;

		if (!empty($this -> settings['treshold_pembulatan']) && $this -> settings['treshold_pembulatan'] > 0) {
			$treshold = ($this -> settings['treshold_pembulatan']) / 1000;
		}
                
		$dec_val = $this->count_decimal_value($cart_weight);
                if ($dec_val > $treshold) {
                 $cart_weight = ceil($cart_weight);
                }else{
                 $cart_weight = floor($cart_weight);
                }
                                if ($cart_weight == 0)
                        $cart_weight = 1;
                $retu = $cart_weight;
                return $retu;
        }


	public function yes_not_found(){
		?>
		<script language="javascript">alert('Tariff JNE tak ditemukan. Tarif dikembalikan ke JNE Regular.');
		var val = 'REGULAR';
						        var sel = document.getElementById('order_comments');
							var opts = sel.options;
							for(var opt, j = 0; opt = opts[j]; j++) {
    							    if(opt.value == val) {
           							sel.selectedIndex = j;
            							break;
        						    }
    							}
		</script>
		<?php
	}

	public function examine_current_currency () {
		global $wp_filter;
                $logger = new WC_Logger();
                $SESSION['current_currency']  = $this -> current_currency;
                $this -> current_currency_rate = 1;
                $currency = $wp_filter['woocommerce_currency'];
                if (empty($currency))
                { $this -> current_currency = ""; return; }
                $currency = $currency -> callbacks;
                $currency = $currency["function"][0];
                $this -> current_currency = trim($currency -> current_currency);

                $epeken_currency_rate_setting = get_option('epeken_multiple_rate_setting');
                if(empty($epeken_currency_rate_setting) ||  $epeken_currency_rate_setting === 'manual'){
                        $wmc_config = get_option('wmc_selected_currencies');
                        $rate = $wmc_config[$this -> current_currency]['rate'];
                        $_SESSION['rate_currency'] = 1/$rate;
                        return;
                }

                if(empty( $this -> current_currency ))
                 $this -> current_currency = 'IDR';

                if($this -> current_currency === 'IDR') {
                        $SESSION['current_currency'] = 'IDR';
                        $_SESSION['rate_currency'] = 1;
                        return;
                }
    
                //manual        
                $epeken_currency_rate_setting = get_option('epeken_multiple_rate_setting');
                if(empty($epeken_currency_rate_setting) ||  $epeken_currency_rate_setting === 'manual'){
                        $wmc_config = get_option('wmc_selected_currencies');
                        $rate = $wmc_config[$this -> current_currency]['rate'];
                        $_SESSION['rate_currency'] = 1/$rate;
                        $this -> current_currency_rate = $_SESSION['rate_currency'];
                        return;
                }   

                //get currency from central bank
              if($this -> current_currency !== 'IDR') {
                if($SESSION['current_currency'] === $this -> current_currency && !empty($_SESSION['rate_currency']) && is_numeric($_SESSION['rate_currency'])){
                        $wmc_config = get_option('wmc_selected_currencies');
                        $wmc_config[$this -> current_currency]['rate'] = 1/($_SESSION['rate_currency']);
                        update_option('wmc_selected_currencies',$wmc_config);
                        $this -> current_currency_rate = $_SESSION['rate_currency'];
                        return;
                }   
		$rate_query_result = epeken_get_currency_rate($this->current_currency);
                $rate_query_result = json_decode($rate_query_result,true);
                if($rate_query_result["status"]["code"] == 200 && is_numeric($rate_query_result["status"]["amount"]))
                        {
                                $this -> current_currency_rate = $rate_query_result["status"]["amount"];
                                $_SESSION['rate_currency'] = $this -> current_currency_rate;
                                $wmc_config = get_option('wmc_selected_currencies');
                                $wmc_config[$this -> current_currency]['rate'] = 1/($_SESSION['rate_currency']);
                                update_option('wmc_selected_currencies',$wmc_config);
                        }
              }   
        }

    	public function apply_subsidi ($rate) {
		$epeken_subsidi_ongkir =  get_option('epeken_subsidi_ongkir');
		$epeken_subsidi_min_purchase = get_option('epeken_subsidi_min_purchase');
		$total = $this -> get_cart_total();

		if (!empty($epeken_subsidi_ongkir) && (!empty($total) || $total > 0) && $total < $epeken_subsidi_min_purchase)
			return $rate;


		if (empty($epeken_subsidi_ongkir) || $epeken_subsidi_ongkir == 0)
		 	return $rate;
	
		if($epeken_subsidi_ongkir >= $rate['cost'])
			{
				$rate['cost'] = 0;
				$rate['label'] = $rate['label'].' Bebas Ongkos Kirim';
				return $rate;
			}	

		 $rate['cost'] =  $rate['cost'] - $epeken_subsidi_ongkir;	
		 return $rate;
		
			
	}

	public function calculate_shipping( $package = array()) {	
		$this -> examine_current_currency();
		$this -> set_shipping_cost();
		$this -> if_total_got_free_shipping();
                if($this -> is_free_shipping){
			WC()->customer->calculated_shipping( true );	
                         $rate = array(
                        'id' => $this -> id,
                        'label' => 'Bebas Ongkos Kirim',
                        'cost' => 0
                        );
                        $this->add_rate($rate);
                        return;
                }
		if(sizeof($this -> array_of_tarif) > 0) {
		 WC()->customer->calculated_shipping( true );	
		if($this -> current_currency !== 'IDR') {
                        update_option('woocommerce_price_thousand_sep',',');
                        update_option('woocommerce_price_decimal_sep','.');
                 } else {
                        update_option('woocommerce_price_thousand_sep','.');
                        update_option('woocommerce_price_decimal_sep',',');
                 }
		 foreach($this -> array_of_tarif as $rate) {
			$rate = $this -> apply_subsidi($rate);
			$rate = $this -> apply_discount($rate);
			$this -> add_rate ($rate);
		 }
		}
	}

 	public function apply_discount($rate) {
			$epeken_subsidi_ongkir =  get_option('epeken_subsidi_ongkir');
			if($epeken_subsidi_ongkir > 0)
				return $rate;

                        $value_diskon_jne = get_option('epeken_diskon_tarif_jne');    
                        if(!empty($value_diskon_jne) && $value_diskon_jne > 0 && strpos(strtolower($rate['label']),'jne') !== false) {
                                $rate['cost'] = ((100-$value_diskon_jne)/100) * $rate['cost'];
                        }    
                        $value_diskon_tiki = get_option('epeken_diskon_tarif_tiki');    
                        if(!empty($value_diskon_tiki) && $value_diskon_tiki > 0 && strpos(strtolower($rate['label']),'tiki') !== false) {
                                $rate['cost'] = ((100-$value_diskon_tiki)/100) * $rate['cost'];
                        }     
                        $value_diskon_pos = get_option('epeken_diskon_tarif_pos');    
                        if(!empty($value_diskon_pos) && $value_diskon_pos > 0 && strpos(strtolower($rate['label']),'pos') !== false) {
                                $rate['cost'] = ((100-$value_diskon_pos)/100) * $rate['cost'];
                        }    
                        return $rate;
        }	

	public function if_total_got_free_shipping(){
		global $woocommerce;
                $this -> total_cart = $this -> get_cart_total();
                $this -> min_allow_fs  = floatval($this -> settings['freeship']);
                $existing_config_free_province = get_option('epeken_province_for_free_shipping'); //array of province
                $existing_config_epeken_is_provinsi_free = get_option('epeken_is_provinsi_free'); //options consist of others are free and these are free
                $kombinasi_province_n_minumum = get_option('epeken_freeship_n_province_for_free_shipping');
	
		$logger = new WC_Logger();	
		$logger -> add ('epeken-all-kurir', $existing_config_epeken_is_provinsi_free);

                /* Free shipping based on province */
                $prov_criteria = false;
                if(!empty($existing_config_free_province))      {
                        if($existing_config_epeken_is_provinsi_free === "these_are_free"){
                                foreach($existing_config_free_province as $province){
                                        if($this -> destination_province === $province) {
                                                 $prov_criteria = true;
                                        }
                                }
                        }
                        if($existing_config_epeken_is_provinsi_free === "others_are_free"){
                                $prov_criteria = true;
                                foreach($existing_config_free_province as $province){
                                        if($this -> destination_province === $province) {
                                                 $prov_criteria = false;
                                        }
                                }
                        }
                }      

                /* Free shipping based on Product Category */
                $prod_cat_criteria = false;
                if(get_option('epeken_free_pc',false) !== false){
                $array_of_free_prod_cat = explode(",",get_option('epeken_free_pc',''));
                $contents = $woocommerce->cart->cart_contents;
                $is_free_pc = false;
                $boolarr = array();
                $counter_quantity = 0;
                $total_item = 0;
                foreach($contents as $content) {
                        $product_id = $content['product_id'];
                                $tmp_boolean = false;
                        for($i=0;$i<sizeof($array_of_free_prod_cat);$i++){
                          $tmp_boolean = epeken_is_product_in_category($product_id,trim($array_of_free_prod_cat[$i]));

                          /* free shipping product based */
                          if(!$tmp_boolean)     {
                                $product_free_ongkir = get_post_meta($product_id,'product_free_ongkir',true);
                                if ($product_free_ongkir === 'on')
                                        $tmp_boolean = true;
		}
                         /* --- */

                          if($tmp_boolean == true){
                                $counter_quantity = $counter_quantity + $content['quantity'];
                                break;
                                }
                        }
                        //array_push($boolarr,$tmp_boolean);
                        $total_item = $total_item + $content['quantity'];
                }
                $free_pc_q = get_option('epeken_free_pc_q',false) ;
                if( $free_pc_q !== false && $free_pc_q > 0){
                      if($counter_quantity >= $free_pc_q && $total_item === $counter_quantity) {
                         $prod_cat_criteria = true;
                        }
                }else{
                        if($counter_quantity > 0 && $total_item === $counter_quantity) {
                                $prod_cat_criteria = true;
                        }else{
                                $prod_cat_criteria = false;
                        }
                 }
                }
                /* Free shipping based on city */
                $destination_city = strtoupper($this -> shipping_city);
                $cities_for_free = explode(",",$this -> settings["city_for_free_shipping"]);
                $kombinasi_minimum_n_city = get_option('epeken_freeship_n_city_for_free_shipping');
                $city_for_free = false;
                $city_criteria = false;
                if (is_array($cities_for_free)) {
                 foreach($cities_for_free as $city) {
                        $city = urldecode($city);
                        $city = trim($city);
                        $city = strtoupper($city);

                        if(empty($city))
                                continue;

                        if (strpos($destination_city, $city) !== FALSE) {
                                $city_criteria = true;
                                break;
                        }
                 }
                }

                /* Free shipping based on minimum total */
		$mintotal_criteria = $this -> validate_freeship_minimum_total();

                $kombinasi_minimum_n_city_criteria = false;
                if($kombinasi_minimum_n_city === "on")
                        $kombinasi_minimum_n_city_criteria = ($mintotal_criteria) && ($city_criteria);

                $kombinasi_minimum_n_province_criteria = false;
                if($kombinasi_province_n_minumum === "on")
                        $kombinasi_minimum_n_province_criteria = ($mintotal_criteria)  && ($prov_criteria);

                $this -> is_free_shipping = ($prov_criteria) || ($city_criteria) || ($prod_cat_criteria) || ($mintotal_criteria) || ($kombinasi_minimum_n_city_criteria) || ($kombinasi_minimum_n_province_criteria);
		
                if($kombinasi_province_n_minumum === "on" || $kombinasi_minimum_n_city === "on") {
                        $this -> is_free_shipping = $kombinasi_minimum_n_province_criteria || $kombinasi_minimum_n_city_criteria;
                }
	}

	public function validate_freeship_minimum_total() {
		/* Free shipping based on minimum total */
                if(empty($this->min_allow_fs) || $this->min_allow_fs == 0)
                        return false;

                if ($this->total_cart >= $this->min_allow_fs && $this->min_allow_fs > 0)
                {
                        //$this -> is_free_shipping = true;
                        //$this -> title = "Gratis untuk pembelian diatas ". $this->min_allow_fs;
                        return true;
                }else{
                        return false;
                }
	}

	public function calculate_insurance() {
                 global $woocommerce;
                 $is_with_insurance = $this -> get_checkout_post_data("insurance_chkbox");
                 if(!$is_with_insurance){
                        return;
                 }

                 if ( is_admin() && ! defined( 'DOING_AJAX' ) )
                                return;

                 $percentage = 0.002;
                 $premium = (($woocommerce->cart->cart_contents_total) * $percentage) + 5000;
		 if(is_numeric($this -> current_currency_rate) && $this -> current_currency_rate > 0) { 
                        $premium = round(($premium / $this -> current_currency_rate), 2);
                 }  
                 $this -> insurance_premium = $premium;	
                 $woocommerce->cart->add_fee( 'Asuransi', $premium, true, '' );
        }

	public function calculate_angka_unik() {
		global $woocommerce;

                if($this -> current_currency !== 'IDR') {
                        return;
                }
                
                if ($this -> settings['enable_kode_pembayaran'] === "no")
                        return;
                
                if ($_SESSION['ANGKA_UNIK']=='') {
                 $max_angka_unik = $this -> settings['max_angka_unik'];
                 
                 if ($max_angka_unik < 0 || $max_angka_unik > 999)
                        $max_angka_unik = 999;
                 
                 
                 $_SESSION['ANGKA_UNIK'] = rand(1,$max_angka_unik);
                }       

                 $mode_kode_pembayaran = get_option('epeken_mode_kode_pembayaran');
                 if($mode_kode_pembayaran === '-') {
                        $_SESSION['ANGKA_UNIK'] = $_SESSION['ANGKA_UNIK'] * (-1);
                 }
                
                $woocommerce->cart->add_fee('Kode Pembayaran',$_SESSION['ANGKA_UNIK'], true, '');	
	}

	public function calculate_biaya_tambahan() {
                global $woocommerce;
                $epeken_biaya_tambahan_name = get_option('epeken_biaya_tambahan_name');
                $epeken_biaya_tambahan_amount = get_option('epeken_biaya_tambahan_amount');
		$epeken_perhitungan_biaya_tambahan = get_option('epeken_perhitungan_biaya_tambahan');
                if (empty($epeken_biaya_tambahan_name))
                        $epeken_biaya_tambahan_name = "Biaya Tambahan";

		if (is_numeric($epeken_biaya_tambahan_amount) && $epeken_biaya_tambahan_amount > 0) { 
                  if($epeken_perhitungan_biaya_tambahan === 'percent')
                        $epeken_biaya_tambahan_amount = ($epeken_biaya_tambahan_amount/100)*($woocommerce->cart->subtotal);
    
		  $epeken_biaya_tambahan_amount = round(($epeken_biaya_tambahan_amount / $this -> current_currency_rate),2); 
                  $woocommerce->cart->add_fee($epeken_biaya_tambahan_name,$epeken_biaya_tambahan_amount, true, ''); 
                }    
        }
	
	public function process_update_data_tarif() {
		include_once 'tools/update_tarif.php';		
	}

	public function admin_error($message) {
        $class = "error";
        echo"<div class=\"$class\"> <p>$message</p></div>";
	}

	public function epeken_product_write_panel_tab() {
                echo "<li class=\"product_tabs_lite_tab\"><a href=\"#woocommerce_product_tabs_lite\">" . __( 'Epeken Config', 'woocommerce' ) . "</a></li>";
        }

	public function epeken_product_write_panel() {
		?>
		<div id="woocommerce_product_tabs_lite" class="panel wc-metaboxes-wrapper woocommerce_options_panel"> 
		Test
		</div>
		<?php
	}

	public function epeken_get_woo_version_number() {
        // If get_plugins() isn't available, require it
        if ( ! function_exists( 'get_plugins' ) )
                require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

        // Create the plugins folder and file variables
        $plugin_folder = get_plugins( '/' . 'woocommerce' );
        $plugin_file = 'woocommerce.php';

        // If the plugin version number is set, return it 
        if ( isset( $plugin_folder[$plugin_file]['Version'] ) ) {
                return $plugin_folder[$plugin_file]['Version'];

        } else {
        // Otherwise return null
                return NULL;
        }
	}
	
	}	// End Class WC_Shipping_Tikijne
$epeken_tikijne = new WC_Shipping_Tikijne;
?>
