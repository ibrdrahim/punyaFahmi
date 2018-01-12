<?php
/**
 * BI_Ajax Class.
 *
 * @class       BI_Ajax
 * @version		1.0
 * @author lafif <lafif@astahdziq.in>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use \Curl\Curl;

/**
 * BI_Ajax class.
 */
class BI_Ajax {

	private $api_key;

	/**
	 * @since 2.0
	 * @var array
	 */
	private $available_import_as = array('woocommerce', 'lapakinstant');

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->includes();

		$this->api_key = get_option( 'bandros_import_api_key' );

		add_action( 'wp_ajax_get_brands', array($this, 'ajax_get_brands_callback') );
		add_action( 'wp_ajax_get_tags', array($this, 'ajax_get_tags_callback') );
		add_action( 'wp_ajax_search_product_codes', array($this, 'ajax_search_product_codes_callback') );

		add_action( 'wp_ajax_get_product_data', array($this, 'ajax_get_product_data_callback') );
		add_action( 'wp_ajax_process_import', array($this, 'ajax_process_import_callback') );
		add_action( 'wp_ajax_end_process_import', array($this, 'ajax_end_process_import_callback') );

		add_action( 'wp_ajax_bi_load_stock', array($this, 'load_stock_callback') );
		add_action( 'wp_ajax_nopriv_bi_load_stock', array($this, 'load_stock_callback') );

		add_action( 'wp_loaded', array($this, 'change_stupid_lapakinstant_check_ajax') );

		add_action( 'init', array($this, 'debug') );
	}

	public function debug(){
		if(!isset($_GET['x']))
			return;

		$term_exists = term_exists('Atasan Anak', 'product_cat');
		if(is_array($term_exists) && isset($term_exists['term_id'])){
			$term_exists = $term_exists['term_id'];
		}
		echo "<pre>";
		print_r($term_exists);
		echo "</pre>";
		exit();

		$args = array( 
			'api_key' => $this->api_key,
			'id_tags' => '[67]',
			// 'page' => '1',
		);

		$curl = new Curl();
		$curl->post(BANDROS_IMPORT_API_URL . '/list_produk_by_tags', $args);

		// $response = json_decode($curl->response);
		$response = $curl->response;

		// $items = array();

		// if (!$curl->error && (strtolower($response->pesan) == 'sukses') ) {
		// 	$items = $response->data;
		// }

		// $response = $curl->response;
		echo "<pre>";
		print_r($response);
		echo "</pre>";
		exit();

		// $args = array( 
		// 	'api_key' => $this->api_key,
		// 	'id_brand' => 5,
			// 'page' => '1',
		// );

		// $curl = new Curl();
		// $curl->post(BANDROS_IMPORT_API_URL . '/list_produk_by_brand', $args);

		// $response = json_decode($curl->response);

		// $items = array();

		// if (!$curl->error && (strtolower($response->pesan) == 'sukses') ) {
		// 	$items = $response->data;
		// }

		// update_option( 'debug_items', $items );

		$s = Bandros_Import()->session->get( 'items_to_process' );
		// $s = get_option( 'debug_items_2' );

		echo "<pre>";
		print_r($s);
		echo "</pre>";

		exit();
	}

	/**
	 * remove default lapakinstant redirect with bad ajax check
	 * they used `$_SERVER['DOING_AJAX'] != '/wp-admin/admin-ajax.php'`
	 * changed to `!( defined( 'DOING_AJAX' ) && DOING_AJAX )`
	 * @return [type] [description]
	 */
	public function change_stupid_lapakinstant_check_ajax(){

		if(!function_exists('no_mo_dashboard'))
			return;

		remove_action( 'admin_init', 'no_mo_dashboard' );
		add_action( 'admin_init', array($this, 'redirect_on_frontend') );
	}

	public function redirect_on_frontend(){
		if (!current_user_can('edit_posts') && !( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
		  	wp_redirect(home_url()); 
		  	exit;
	  	}
	}

	public function check_api_key($api_key = false, $email = false){
		$allowed  = false;
		$message = '';

		$email = get_option( 'bandros_import_email' );
		if(empty($email)){
			$email = get_option( 'admin_email' ); // empty try with admin email
		}

		$api_key = get_option( 'bandros_import_api_key' );

		$args = array( 
			'email' => $email,
			'api_key' => $api_key 
		);
		$curl = new Curl();
		$curl->post( BANDROS_IMPORT_API_URL . '/validate_api', $args);

		// echo $email . '<br>';
		// echo $this->api_key . '<br>';
		// echo "<pre>";
		// print_r($response);
		// echo "</pre>";
		// exit();

		if ($curl->error) {
			$allowed  = false;
			$message = $curl->errorMessage;
		} else {

		    $response = json_decode($curl->response);

		    if(strtolower($response->pesan) != 'sukses'){
				$allowed  = false;
				$message = $response->pesan;
		    } else {
				$allowed  = true;
				$message = __('API key terdaftar', 'bandros_import');
		    }
		}

		/**
		 * Track activation
		 * @since  1.3 [<description>]
		 */
		$activated = get_option( 'bandros_import_activated' );
		if(empty($activated) || ($activated != $api_key ) ){
			/**
			 * Activation through rest api
			 */
			$args = array( 
				'email' => $email,
				'api_key' => $api_key,
				'urlweb' => get_home_url(),
				'version' => Bandros_Import()->version,
			);

			$curl->post( BANDROS_IMPORT_API_URL . '/activate', $args);

			$activated_success = false;
			if (!$curl->error) {
				// $response = json_decode($curl->response);
				$response = $curl->response;

			    if(strtolower($response->pesan) == 'aktifasi berhasil'){
			    	// save to option
			    	$activated_success = update_option( 'bandros_import_activated', $api_key );
			    }
			}

			// may need to stop if activation failed
			if(!$activated_success){
				$allowed = false;
			}
		}
		
		// set message to session
		Bandros_Import()->session->set( 'bi_api_key_status', array(
			'status' => ($allowed) ? 'sukses' : 'gagal',
			'message' => $message,
		) );

		return apply_filters( 'bandros_import_allowed', $allowed );
	}

	public function ajax_get_brands_callback(){
		// $user_id = get_current_user_id();

		$result = array(
			'status' => 'failed',
			'message' => __('Brand gagal dimuat', 'bandros_import'),
			'result' => array()
		);

		check_ajax_referer( 'bi-import', 'nonce' );
		
		$args = array( 'api_key' => $this->api_key );
		$curl = new Curl();
		$curl->post( BANDROS_IMPORT_API_URL . '/get_brand', $args);

		if ($curl->error) {
			$result['status'] = 'failed';
			$result['message'] = $curl->errorCode . ': ' . $curl->errorMessage;
		} else {

		    $response = json_decode($curl->response);

		    if(strtolower($response->pesan) != 'sukses'){
				$result['status'] = 'failed';
		    	$result['message'] = __('Brand gagal dimuat', 'bandros_import');
		    } else {
				$result['status'] = 'success';
				$result['message'] = '';
		    	
		    	foreach ($response->data as $data) {
		    		$result['result'][] = array(
		    			'id' => $data->id,
						'text' => $data->nama
		    		);
		    	}
		    }
		}

		wp_die( json_encode($result) );
	}

	/**
	 * Get Tags ajax
	 * @since 1.3
	 * @return [type] [description]
	 */
	public function ajax_get_tags_callback(){
		// $user_id = get_current_user_id();

		$result = array(
			'status' => 'failed',
			'message' => __('Tag gagal dimuat', 'bandros_import'),
			'result' => array()
		);

		check_ajax_referer( 'bi-import', 'nonce' );
		
		$args = array( 'api_key' => $this->api_key );
		$curl = new Curl();
		$curl->post( BANDROS_IMPORT_API_URL . '/get_tags', $args);

		if ($curl->error) {
			$result['status'] = 'failed';
			$result['message'] = $curl->errorCode . ': ' . $curl->errorMessage;
		} else {

		    // $response = json_decode($curl->response); 

		    // Get tag response was different, it results as object instead of json
		    $response = $curl->response;

		    if(strtolower($response->pesan) != 'sukses'){
				$result['status'] = 'failed';
		    	$result['message'] = __('Tag gagal dimuat', 'bandros_import');
		    } else {
				$result['status'] = 'success';
				$result['message'] = '';
		    	
		    	foreach ($response->data as $data) {
		    		$result['result'][] = array(
		    			'id' => $data->id,
						'text' => $data->nama
		    		);
		    	}
		    }
		}

		wp_die( json_encode($result) );
	}

	public function ajax_search_product_codes_callback(){
		// $user_id = get_current_user_id();
		$result = array(
			'status' => 'failed',
			'message' => __('Kode produk gagal dimuat', 'bandros_import'),
			'codes' => array()
		);

		// check_ajax_referer( 'bi-import', 'nonce' );
		$search = (isset($_REQUEST['search']) && !empty($_REQUEST['search'])) ? $_REQUEST['search'] : '';

		if(!empty($search)){
			$args = array( 
				'apikey' => $this->api_key,
				'kode' => $search
			);

			$curl = new Curl();
			$curl->get( BANDROS_IMPORT_API_URL . '/auto_complete/', $args);

			if ($curl->error) {
				$result['status'] = 'failed';
				$result['message'] = $curl->errorCode . ': ' . $curl->errorMessage;
			} else {

			    $response = (is_string($curl->response)) ? json_decode($curl->response) : $curl->response;

			    if( absint( $response->error ) > 0){
					$result['status'] = 'failed';
			    	$result['message'] = __('Brand gagal dimuat', 'bandros_import');
			    } else {
					$result['status'] = 'success';
					$result['message'] = '';
			    	
			    	foreach ($response->result as $data) {
			    		$result['codes'][] = array(
			    			'id' => $data->kode,
							'text' => $data->kode
			    		);
			    	}
			    }
			} 
		}

		// $result['codes'] = array(
		// 	array(
  //   			'id' => ($search) ? $search : 'kosong',
		// 		'text' => ($search) ? $search : 'kosong'
  //   		),
		// 	array(
  //   			'id' => 'www',
		// 		'text' => 'WWW'
  //   		),
		// );
		

		wp_die( json_encode($result) );
	}

	function ajax_get_product_data_callback(){

		check_ajax_referer( 'bi-import', 'nonce' );

		parse_str($_POST['formdata'], $formdata);

		$items = array();
		switch ($formdata['import_type']) {
			case 'product':
				$items = (array) $this->get_product_items($formdata['product_id']);
				break;

			case 'tag':
				$items = (array) $this->get_tag_items($formdata['tag_id']);
				break;
			
			default:
				$items = (array) $this->get_brand_items($formdata['brand_id']);
				break;
		}

		// debug
		// $items = get_option( 'debug_items_2' );

		if(!empty($items)){
			$import_data = array(
				'formdata' => $formdata,
				'items' => $items
			);

			// store items to session 
			Bandros_Import()->session->set( 'items_to_process', $import_data );

			$result = array(
				'status' => 'success',
				'message' => sprintf( __('Produk berhasil diambil, %d produk ditemukan', 'bandros_import'), count($items) ),
				'items' => array_keys($items)
			);
		} else {
			$result = array(
				'status' => 'failed',
				'message' => 'Produk gagal diambil',
				'items' => $items
			);
		}

		die( json_encode( $result ) );
	}

	public function ajax_process_import_callback(){

		check_ajax_referer( 'bi-import', 'nonce' );

		$id = $_REQUEST['id'];

		$import_data = Bandros_Import()->session->get( 'items_to_process' );
		$items_to_process = $import_data['items'];
		$import_as = (isset($import_data['formdata']['import_as']) && !empty($import_data['formdata']['import_as'])) ? $import_data['formdata']['import_as'] : false;

		// debug
		// $result = array( 
	 //    	'status' => 'failed',
		// 	'message' => empty($import_as) ? 'empty' : $import_as
		// );

		// // clear session
		// Bandros_Import()->session->set( 'items_to_process', array() );

		// die( json_encode( $result ) );

		if(empty($items_to_process)) {
	    	$result = array( 
	    		'status' => 'failed',
				'message' => __('Item data tidak ditemukan', 'bandros_import')
			);
	    } elseif (empty($import_as) || !in_array($import_as, $this->available_import_as)) {
	    	$result = array( 
	    		'status' => 'failed',
				'message' => __('Produk tujuan tidak valid.', 'bandros_import')
			);

			// clear session
			Bandros_Import()->session->set( 'items_to_process', array() );

	    } else {

	    	if($import_as == 'lapakinstant'){
	    		/**
	    		 * @since 2.0
	    		 * @var BI_LapakInstant_Importer
	    		 */
	    		include_once( 'class-bi_importer_lapakinstant.php' );
	    		$import = new BI_Importer_LapakInstant($import_data['formdata'], $items_to_process[$id]);
	    	} else {
	    		include_once( 'class-bi_importer_woo.php' );
	    		$import = new BI_Importer_Woo($import_data['formdata'], $items_to_process[$id]);
	    	}
	    	
	    	$import->do_import(); // trigger import

	    	// get message
	    	$error = $import->get_message('error');
	    	$notice = $import->get_message('notice');

	    	if(empty($error)){
	    		$result = array( 
			    	'status' => 'success',
					'message' => __('Produk berhasil diimport', 'bandros_import'),
					'notices' => $notice
				);
	    	} else {
	    		$result = array( 
			    	'status' => 'failed',
					'message' => __('Produk gagal diimport', 'bandros_import'),
					'notices' => $notice
				);

				// clear session
				// Bandros_Import()->session->set( 'items_to_process', array() );
	    	}

	    	

		  //   if(empty($message)){
		  //   	$result = array( 
			 //    	'status' => 'success',
				// 	'message' => sprintf( __('%s berhasil di import (%ss)', 'bandros_import'), $items_to_process[$id]->nama, timer_stop() )
				// );
		  //   } else {
		  //   	$result = array( 
			 //    	'status' => 'failed',
				// 	'message' => sprintf( __('%s gagal di import', 'bandros_import'), $items_to_process[$id]->nama )
				// );
		  //   }
	    }

		die( json_encode( $result ) );
	}

	public function ajax_end_process_import_callback(){

		check_ajax_referer( 'bi-import', 'nonce' );

		// clear session
		Bandros_Import()->session->set( 'items_to_process', array() );

		$result = array( 
	    	'status' => 'success',
			'message' => 'dasd'
		);

		die( json_encode( $result ) );

	}

	public function get_product_items($product_ids){
		
		if(!is_array($product_ids)){
			$product_ids = explode(',', $product_ids);
		}

		$ids_str = '["'.implode('","', $product_ids).'"]';

		$args = array( 
			'api_key' => $this->api_key,
			// 'kode_barang' => '["ASR 0001","ON 02"]',
			'kode_barang' => $ids_str,
		);

		$curl = new Curl();
		$curl->post(BANDROS_IMPORT_API_URL . '/list_produk_by_kode', $args);

		$response = json_decode($curl->response);

		$items = array();

		if (!$curl->error && (strtolower($response->pesan) == 'sukses') ) {
			$items = $response->data;
		}

		return $items;
	}

	public function get_brand_items($id){

		$args = array( 
			'api_key' => $this->api_key,
			'id_brand' => $id,
			// 'page' => '1',
		);

		$curl = new Curl();
		$curl->post(BANDROS_IMPORT_API_URL . '/list_produk_by_brand', $args);

		$response = json_decode($curl->response);

		$items = array();

		if (!$curl->error && (strtolower($response->pesan) == 'sukses') ) {
			$items = $response->data;
		}

		return $items;
		
	}

	/**
	 * Get product by tag
	 * @since  1.3 [<description>]
	 * @param  [type] $id [description]
	 * @return [type]     [description]
	 */
	public function get_tag_items($tag_ids){

		if(!is_array($tag_ids)){
			$tag_ids = explode(',', $tag_ids);
		}

		$ids_str = '["'.implode('","', $tag_ids).'"]';

		$args = array( 
			'api_key' => $this->api_key,
			'id_tags' => $ids_str,
			// 'page' => '1',
		);

		$curl = new Curl();
		$curl->post(BANDROS_IMPORT_API_URL . '/list_produk_by_tags', $args);

		// $response = json_decode($curl->response);
		
		// Get tag response was different, it results as object instead of json
	    $response = $curl->response;

		$items = array();

		if (!$curl->error && (strtolower($response->pesan) == 'sukses') ) {
			$items = $response->data;
		}

		return $items;
		
	}

	public function load_stock_callback(){

		$result = array(
			'status' => 'failed',
			'html' => '<div>'.__('Untuk informasi stok silahkan hubungi cs kami', 'bandros_import').'</div>'
		);

		if ( !isset($_REQUEST['kode']) || ! wp_verify_nonce( $_REQUEST['nonce'], 'bi_load_stock' ) ) {
			echo json_encode($result);
			exit();
		}

		$data_stock = $this->get_stock_from_kode($_REQUEST['kode']);
		$with_image = (isset($_REQUEST['with_image']) && ($_REQUEST['with_image'] == 'yes') ) ? true : false;
		$info_position = ( isset($_REQUEST['info_position']) ) ? $_REQUEST['info_position'] : false;

		if( $data_stock && !empty($data_stock) ){
			$result['status'] = 'success';
			
			ob_start();

			foreach ($data_stock as $data) {
				?>
				<div class="results uk-grid">
					<?php if($info_position && ($info_position == 'top')): ?>
					<div class="uk-width-1-1">
						<table class="info-table">
							<tr><td><?php echo sprintf( __('Kode : %s', 'bandros_import'), $data->kode); ?></td></tr>
							<tr><td><?php echo sprintf( __('Warna : %s', 'bandros_import'), (!empty($data->warna)) ? $data->warna : '-'); ?></td></tr>
							<tr><td><?php echo sprintf( __('Brand : %s', 'bandros_import'), $data->source); ?></td></tr>
						</table>
					</div>
					<?php endif; // info_position ?>

					<?php if($with_image){ ?>
					<div class="image-stock uk-width-1-3 uk-width-medium-1-4">
						<img src="<?php echo $data->image_url; ?>" alt="<?php echo $data->source; ?>">
					</div>
					<?php } ?>
					<div class="<?php echo ($with_image) ? 'uk-width-2-3 uk-width-medium-3-4' : 'uk-width-1-1'; ?>">
						<table class="table-stock" cellspacing="0">
							<thead>
								<tr>
									<?php if(!$info_position || ($info_position != 'top')): ?>
									<th class=""><?php _e( 'Kode', 'bandros_import' ); ?></th>
									<th class=""><?php _e( 'Warna', 'bandros_import' ); ?></th>
									<?php endif; ?>
									<th class=""><?php _e( 'Size', 'bandros_import' ); ?></th>
									<th class=""><?php _e( 'Stok', 'bandros_import' ); ?></th>
									<th class=""><?php _e( 'Update', 'bandros_import' ); ?></th>
								</tr>
							</thead>
							<tbody>
							<?php
							foreach ($data->stok as $stock) {
								?>
								<tr class="tr_stock">
									<?php if(!$info_position || ($info_position != 'top')): ?>
									<td class="kode"><?php echo $data->kode; ?></td>
									<td class="warna"><?php echo $stock->warna; ?></td>
									<?php endif; ?>
									<td class="size"><?php echo $stock->ukuran; ?></td>
									<td class="stok"><?php echo sprintf( __('%s Pcs', 'bandros_import'), $stock->stok ); // maybe stok_bandros ?></td>
									<td class="update"><?php echo $stock->last_updated; ?></td>
								</tr>
								<?php if($stock->terjamin == '1'){ ?>
								<tr class="tr_terjamin">
									<td class="terjamin" colspan="<?php echo (!$info_position || ($info_position != 'top')) ? '5' : '3'; ?>"><b><?php echo sprintf( __('Stok Terjamin : %s Pcs', 'bandros_import'), $stock->stok_bandros ); ?></b></td>
								</tr>
								<?php }
							}
							?>
							</tbody>
						</table>
					</div>
				</div>
					<?php
			}

			$result['html'] = ob_get_clean();
		}

		echo json_encode($result);
		exit();
	}

	public function get_stock_from_kode($kode){
		$args = array( 
			'apikey' => $this->api_key,
			'kode' => $kode
		);

		$curl = new Curl();
		$curl->get( BANDROS_STOCK_API_URL . '/produk/cari/', $args);
		
		$response = (is_string($curl->response)) ? json_decode($curl->response) : $curl->response;

		if( (absint( $response->error ) <= 0) && !empty($response->result) ){
			return $response->result;
		} else {
			return false;
		}
	}

	public function includes(){

	}

}

$GLOBALS['bi_ajax'] = new BI_Ajax();