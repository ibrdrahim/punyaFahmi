<?php
/**
 * BI_Admin Class.
 *
 * @class       BI_Admin
 * @version		1.0
 * @author lafif <lafif@astahdziq.in>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * BI_Admin class.
 */
class BI_Admin {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->includes();

		add_action('admin_menu', array($this, 'add_menu') );
		add_action( 'admin_init', array($this, 'save_settings') );
		add_action( 'admin_init', array($this, 'check_api_key') );
	}

	public function add_menu(){
		global $bandros_import;

		add_menu_page( __('Bandros Juara', 'bandros_import'), __('Bandros Juara', 'bandros_import'), Bandros_Import()->capability, 'bandros-juara', false, 'dashicons-thumbs-up', 56 );
		add_submenu_page( 'bandros-juara', __('Import Produk', 'bandros_import'), __('Import Produk', 'bandros_import'), Bandros_Import()->capability, 'bandros-import-product', array($this, 'bandros_import_page') );
		
		// add_submenu_page( 'bandros-juara', __('Pengaturan Import', 'bandros_import'), __('Pengaturan Import', 'bandros_import'), Bandros_Import()->capability, 'bandros-import-settings', array($this, 'import_settings'));
	
		remove_submenu_page( 'bandros-juara', 'bandros-juara' );

		/**
		 * Currently support for WC and LapakInstant
		 * @since 2.0 [<description>]
		 */
		if(!$bandros_import->is_wc_installed && !$bandros_import->is_lapakinstant_installed ){
			remove_submenu_page( 'bandros-juara', 'bandros-import-product' );
		}

		do_action( 'bi_add_menu' );
	}

	public function bandros_import_page(){
		if( isset($_GET['section']) && ($_GET['section'] == 'settings') ){
			$this->import_settings();
		} else {
			$this->import_page();
		}
	}

	public function import_page(){
		global $bandros_import;
		
		wp_enqueue_style( 'bandros_import' );
		wp_enqueue_script( 'bandros_import' );
		wp_localize_script( 'bandros_import', 'BI', array(
			'ajax_url' => Bandros_Import()->ajax_url(),
			'nonce' => wp_create_nonce( 'bi-import' ),
			'is_lapakinstant_installed' => Bandros_Import()->is_lapakinstant_installed,
			'is_woo_istalled' => Bandros_Import()->is_wc_installed,
			'is_woo_3' => ( Bandros_Import()->is_wc_installed && version_compare( WC()->version, '3.0.0', '>=' ) ) ? 'yes' : 'no'
		) );
		$bi_url =  menu_page_url('bandros-import-product', false);
		$setting_url = add_query_arg(array('section' => 'settings'), $bi_url);

		$import_as = '';
		if(Bandros_Import()->is_lapakinstant_installed)
			$import_as = 'lapakinstant';

		if(Bandros_Import()->is_wc_installed)
			$import_as = 'woocommerce';

		?>
		<div class="funkmo-container <?php echo $import_as; ?>">
			<div class="funkmo-row">
			  	<div class="funkmo-left">
			     	<div class="funkmo-holder">
				        <h1><span class="dashicons dashicons-download"></span> <?php _e('Upload Produk', 'bandros_import'); ?></h1>
				        <a href="<?php echo $setting_url; ?>" id="" class="bi_settings"><i class="dashicons dashicons-admin-generic"></i><?php _e('Pengaturan', 'bandros_import'); ?></a>
				        <div class="funkmo-clear"></div>
				        <hr class="funkmo-hr">
				        <noscript>
				        	<p class="alert error"><strong>Erorr!</strong> Anda harus mengaktifkan javascript untuk melakukan import. </p>
					    </noscript>
				        <div class="funkmo-content">
							<form>
								<table>
									<tr>
										<td><?php _e('Jenis Import', 'bandros_import'); ?></td>
										<td>:</td>
										<td>
											<input type="hidden" name="import_as" value="<?php echo $import_as; ?>">
											<div class="option-wrapper">
												<label class="option">
										          	<input type="radio" id="type_brand" name="import_type" value="brand" checked="checked" class="focus">
										          	<span class="radio"></span>
								        		</label>
								        		<label class="option-label" for="type_brand"><?php _e('Brand', 'bandros_import'); ?></label>
								        	</div>

								        	<div class="option-wrapper">
												<label class="option">
										          	<input type="radio" id="type_tag" name="import_type" value="tag" class="focus">
										          	<span class="radio"></span>
								        		</label>
								        		<label class="option-label" for="type_tag"><?php _e('Tag', 'bandros_import'); ?></label>
											</div>

											<div class="option-wrapper">
									        	<label class="option">
										          	<input type="radio" id="type_product" name="import_type" value="product" class="focus">
										          	<span class="radio"></span>
										        </label>
								        		<label class="option-label" for="type_product"><?php _e('Kode Produk (Max 20 produk per-upload)', 'bandros_import') ?></label>
									    	</div>
									    </td>
									</tr>
									<tr class="import_type" data-if="brand">
										<td><?php _e('Pilih Brand', 'bandros_import'); ?></td>
										<td>:</td>
										<td>
											<select id="select-brand" name="brand_id" data-placeholder="Silahkan pilih brand" style="width: 100%;">
												<option value=""></option>
											</select>
										</td>
									</tr>
									<tr class="hide import_type" data-if="tag">
										<td><?php _e('Pilih Tag', 'bandros_import'); ?></td>
										<td>:</td>
										<td>
											<select id="select-tag" name="tag_id[]" data-placeholder="Silahkan pilih tag" style="width: 100%;" multiple="multiple">
												<option value=""></option>
											</select>
										</td>
									</tr>
									<tr class="hide import_type" data-if="product">
										<td><?php _e('Pilih Produk', 'bandros_import'); ?></td>
										<td>:</td>
										<td>
											<select id="select-product" name="product_id[]" placeholder="Masukkan kode produk" style="width: 100%;">
											</select>
										</td>
									</tr>
									<tr>
										<td><?php _e('Mark-up Harga', 'bandros_import'); ?></td>
										<td>:</td>
										<td>
											<div class="select">
												<select name="markup_type">
										          	<option value="normal" selected="selected">Harga Jual Rekomendasi</option>
										          	<option value="percent">Persentase</option>
										          	<option value="fix">Fix</option>
										        </select>
											</div>
									        <span class="markup_type hide" data-if="percent">
									        	<input name="percent_val" type="number" max="100" placeholder="0" min="0">
									        	<span class="input-addons append">%</span>
									        </span>
									        <span class="markup_type hide" data-if="fix" >
									        	<span class="input-addons prepend">Rp.</span>
									        	<input name="fix_val" type="number" min="0" placeholder="0">
											</span>
										</td>
									</tr>
									<tr>
										<td><?php _e('Status Publikasi', 'bandros_import'); ?></td>
										<td>:</td>
										<td>
											<label class="option">
									          	<input type="radio" id="status_publish" name="post_status" value="publish" checked="checked" class="focus">
									          	<span class="radio"></span>
							        		</label>
							        		<label class="option-label" for="status_publish"><?php _e('Publish', 'bandros_import'); ?></label>

								        	<label class="option">
									          	<input type="radio" id="status_draft" name="post_status" value="draft" class="focus">
									          	<span class="radio"></span>
									        </label>
							        		<label class="option-label" for="status_draft"><?php _e('Draft', 'bandros_import'); ?></label>
										</td>
									</tr>
									<tr>
										<td><?php _e('Prefix Judul', 'bandros_import'); ?></td>
										<td>:</td>
										<td>
											<input type="text" name="prefix_title" placeholder="Contoh: Jual">
										</td>
									</tr>
									<tr>
										<td><?php _e('Suffix Judul', 'bandros_import'); ?></td>
										<td>:</td>
										<td>
											<input type="text" name="suffix_title" placeholder="Contoh: Murah">
										</td>
									</tr>
									<tr>
										<td><?php _e('Prefix Deskripsi', 'bandros_import'); ?></td>
										<td>:</td>
										<td>
											<textarea name="prefix_desc" id="" cols="30" rows="10" placeholder="Contoh: Produk Best Seller!!"></textarea>
										</td>
									</tr>
									<tr>
										<td><?php _e('Suffix Deskripsi', 'bandros_import'); ?></td>
										<td>:</td>
										<td>
											<textarea name="suffix_desc" id="" cols="30" rows="10" placeholder="Contoh: Sebelum order, jangan lupa cek stok ya!"></textarea>
										</td>
									</tr>
								</table>
								<hr class="funkmo-hr">
								<a href="#" id="button_start" class="btn btn-green"><?php _e('Mulai Upload', 'bandros_import'); ?></a>
								<a href="#" id="bi-stop" class="btn btn-default hide" style="float:right;"><?php _e('Batalkan', 'bandros_import'); ?></a>
							</form>
				        </div>
			     	</div>
					
					<?php
					$file_location = Bandros_Import()->plugin_path() . "/petunjuk.md"; 
					if(is_readable($file_location)){
					?>
			     	<div class="funkmo-holder">
				        <?php 
						$file = file_get_contents( $file_location );
						$parsedown = new Parsedown();
						echo $parsedown->text($file);
						?>
				    </div>
				    <?php 
					}
					?>
			  	</div>

			  	<div class="funkmo-right">
					<div class="funkmo-holder">
				        <h1><span class="dashicons dashicons-lightbulb"></span> <?php _e('Log', 'bandros_import'); ?></h1>
						<div class="funkmo-clear"></div>
				        <hr class="funkmo-hr">
				        <div class="funkmo-content">
							<div class="import_progress" role="progressbar">
					          	<div class="pie_progress__number">0%</div>
					          	<div class="pie_progress__label">Completed</div>
					        </div>

							<div>
								<div id="window">>_ Log<span id="persen"></span></div>
								<div id="cmd">
									<div id="cmd_text"></div>
									<!-- <div id="cmd_text">
										<span class="process">Mengambil data produk</span>
										<span class="result success">Produk berhasil diambil</span>
										<span class="process">Mengimport produk</span>
										<span class="result success">Produk berhasil diimport</span>
										<span class="result detail">
											<span class="content-detail">Gambar gagal diupload</span>
										</span>
										<span class="process">Selesai</span>
										<span class="result success">Semua produk berhasil di import.</span>
									</div> -->
								</div>
							</div>
				        </div>
				    </div>
			  	</div>
			</div>
		</div>
		<?php
	}

	public function import_settings(){
		global $bi_ajax, $save_message;

		wp_enqueue_style( 'bandros_import' );
		

		$api_key_status = Bandros_Import()->session->get( 'bi_api_key_status' );

		?>
		<div class="funkmo-container">
			<div class="funkmo-holder">
		        <h1><span class="dashicons dashicons-admin-generic"></span> <?php _e('Pengaturan', 'bandros_import'); ?></h1>
		        <div class="funkmo-clear"></div>
		        <hr class="funkmo-hr">
		        <?php if(isset($api_key_status['status']) && ($api_key_status['status'] != 'sukses') && (isset($api_key_status['message']) && !empty($api_key_status['message'])) ){ ?>
				<p class="alert">
			        <?php echo $api_key_status['message']; ?>
			        <a href="#" class="alert-close">&times;</a>
		      	</p>
		        <?php } else if(!empty($save_message)){ ?>
				<p class="alert notice">
			        <?php echo $save_message; ?>
			        <a href="#" class="alert-close">&times;</a>
		      	</p>
		        <?php } ?>
	        	
		        <div class="funkmo-content">
		        <form method="post">
					<table>
						<tr>
							<td><?php _e('Email', 'bandros_import'); ?></td>
							<td>:</td>
							<td>
						        <input type="text" name="bandros_import_email" value="<?php echo get_option( 'bandros_import_email' ); ?>" placeholder="Email Member">
							</td>
						</tr>
						<tr>
							<td><?php _e('API KEY', 'bandros_import'); ?></td>
							<td>:</td>
							<td>
						        <input type="text" name="bandros_import_api_key" value="<?php echo get_option( 'bandros_import_api_key' ); ?>" placeholder="Kode Akses / Api Key">
							</td>
						</tr>
					</table>
					
					<?php wp_nonce_field( 'bandros_import_setting', '_bi_nonce', true, true ); ?>
					<input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>">
					<?php
					$file_location = Bandros_Import()->plugin_path() . "/readme.md";
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
					<hr class="funkmo-hr">
					<button type="submit" class="btn btn-green" style="float: right;"><?php _e('Simpan', 'bandros_import'); ?></button>
		        	<div class="funkmo-clear"></div>
		        </form>
		        </div>
	     	</div>
		</div>

		<script>
		(function($){
			$(document).on('click', 'a.alert-close', function(e){
				e.preventDefault();
				$(this).closest('p.alert').fadeOut();
			});
		})(jQuery);
		</script>
		<?php
	}

	public function save_settings(){
		global $bi_ajax,$save_message;

		if ( isset( $_POST['_bi_nonce'] ) && wp_verify_nonce( $_POST['_bi_nonce'], 'bandros_import_setting' ) ) {
			$save_message = false;

			if(isset($_POST['bandros_import_api_key'])){
				update_option( 'bandros_import_api_key', sanitize_text_field( $_POST['bandros_import_api_key'] ) );
			}
			if(isset($_POST['bandros_import_email'])){
				update_option( 'bandros_import_email', sanitize_text_field( $_POST['bandros_import_email'] ) );
			}

			// re check
			$status = $bi_ajax->check_api_key();
			
			if($status){
				// redirect
				$page_slug = (isset($_GET['page'])) ? $_GET['page'] : 'bandros-import-product';
				$bi_url =  menu_page_url($page_slug, false);
				wp_redirect( $bi_url );
				exit();
			}

			$save_message =  '<strong>Berhasil!</strong> Setting berhasil disimpan.';
		}
	}

	
	public function check_api_key(){
		global $bi_ajax;

		if(!isset($_GET['page']))
			return;

		if(isset($_GET['section']) && ($_GET['section'] == 'settings' ))
			return;

		$pages = apply_filters( 'bi_pages_need_valid_api_key', array('bandros-import-product', 'bandros-widget-stock') );
		// echo "<pre>";
		// print_r($pages);
		// echo "</pre>";

		// exit();


		if(!in_array($_GET['page'], $pages))
			return;

		$api_key_allowed = $bi_ajax->check_api_key();
		if(!$api_key_allowed){
			$page_slug = (isset($_GET['page'])) ? $_GET['page'] : 'bandros-import-product';
			$bi_url =  menu_page_url($page_slug, false);
			$setting_url = add_query_arg(array('section' => 'settings'), $bi_url);
			// echo $setting_url;
			wp_redirect( $setting_url );
			exit();
		}
	}

	public function includes(){

	}

}

$GLOBALS['bi_admin'] = new BI_Admin();