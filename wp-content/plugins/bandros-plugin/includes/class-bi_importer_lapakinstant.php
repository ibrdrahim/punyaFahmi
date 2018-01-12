<?php
/**
 * BI_Importer_LapakInstant Class.
 *
 * @class       BI_Importer_LapakInstant
 * @version		2.0
 * @author lafif <lafif@astahdziq.in>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * BI_Importer_LapakInstant class.
 */
class BI_Importer_LapakInstant extends BI_Importer {

	private function check_product_exist($product_name='', $product_sku=''){
		global $wpdb;

		$product_exist = $wpdb->get_var( $wpdb->prepare( "
			SELECT posts.ID
			FROM $wpdb->posts AS posts
			LEFT JOIN $wpdb->postmeta AS postmeta ON ( posts.ID = postmeta.post_id ) AND (postmeta.meta_key = 'my_meta_kode_produk')
			WHERE posts.post_type = 'post'
			AND posts.post_status = 'publish'
			AND postmeta.meta_key = 'my_meta_kode_produk'
			AND ( posts.post_title = '%s' OR postmeta.meta_value = '%s' )
			LIMIT 1
		 ", $product_name, $product_sku ) );

		if(!empty($product_exist)){
			$message = sprintf( __('Produk sudah ada dengan id %d.', 'bandros_import'), $product_exist );
			$this->add_message($message);
			return $product_exist;
		}

		return false;
	}

	private function add_product(){

		// Check permissions.
		if ( ! current_user_can( 'publish_posts' ) ) {
			$message = __('Anda tidak diperbolehkan mengimport produk.', 'bandros_import');
			$this->add_message($message, 'error');
			return;
		}

		/**
		 * Create product title from prefix suffix
		 * @var [type]
		 */
		$_product_title = array();
		if(isset($this->setting['prefix_title']) && !empty($this->setting['prefix_title']))
			$_product_title[] = sanitize_text_field( $this->setting['prefix_title'] );

		$_product_title[] = sanitize_text_field( $this->product_data->nama );

		if(isset($this->setting['suffix_title']) && !empty($this->setting['suffix_title']))
			$_product_title[] = sanitize_text_field( $this->setting['suffix_title'] );

		$product_title = implode(' ', $_product_title);


		/**
		 * Create product desc from prefix suffix
		 * @var [type]
		 */
		$_product_desc = array();
		if(isset($this->setting['prefix_desc']) && !empty($this->setting['prefix_desc']))
			$_product_desc[] = $this->setting['prefix_desc'];

		if( isset( $this->product_data->long_deskripsi ) && !empty($this->product_data->long_deskripsi) )
			$_product_desc[] = $this->product_data->long_deskripsi;

		if(isset($this->setting['suffix_desc']) && !empty($this->setting['suffix_desc']))
			$_product_desc[] = $this->setting['suffix_desc'];

		$product_desc = implode('<br>', $_product_desc);

		$new_product = array(
			'post_title'   => $product_title,
			'post_status'  => $this->post_status,
			'post_type'    => 'post', // lapak instant used post as product post type
			'post_excerpt' => isset( $this->product_data->deskripsi ) ? $this->product_data->deskripsi : '',
			'post_content' => $product_desc,
			'post_author'  => get_current_user_id(),
		);

		// Attempts to create the new product.
		$id = wp_insert_post( $new_product, true );

		// Checks for an error in the product creation.
		if ( is_wp_error( $id ) ) {
			$message = __('Produk gagal diimport.', 'bandros_import');
			$this->add_message($message, 'error');
			
			return;

		} else {

			$this->add_message( 'Produk berhasil dibuat dengan id : ' .$id );

			// Check for featured/gallery images, upload it and set it.
			if ( isset( $this->product_data->foto_gallery ) ) {
				$this->save_product_images( $id, (array) $this->product_data->foto_gallery );
			}

			// // Save product meta fields.
			$this->save_product_meta( $id, $this->product_data );
			
		}

	}

	
	private function save_product_meta( $post_id, $data ) {
		global $wpdb;

		// Deskripsi/Fitur Produk singkat (optional):
		update_post_meta( $post_id, 'my_meta_desc_produk1', 'Kualitas Terbaik' );
		update_post_meta( $post_id, 'my_meta_desc_produk2', '100% Buatan Indonesia' );
		update_post_meta( $post_id, 'my_meta_desc_produk3', 'Garansi Retur 30 Hari' );

		// Rating
		update_post_meta( $post_id, 'my_meta_rating', '5_0' );

		// Dropship
		update_post_meta( $post_id, 'my_meta_harga_modal', intval($data->harga_member) );
		update_post_meta( $post_id, 'my_meta_link_source', 'https://www.bandros.co.id/search/' . $data->sku );

		// SKU
		if ( isset( $data->sku ) ) {
			update_post_meta( $post_id, 'my_meta_kode_produk', $data->sku );
		}

		// HARGA
		$new_price = intval($data->harga_member); // default

		if($this->setting['markup_type'] == 'percent'){
			
			$this->add_message('Harga awal : ' . $this->format_price($new_price) );
			
			$percent_val = floatval($this->setting['percent_val']);
			$percent_price = ($percent_val / 100) * $new_price;
			$new_price = $new_price + $percent_price;

			$this->add_message( 'Menambahkan markup harga sebesar '.$this->format_price($percent_price).' ( '.$percent_val.'% )' );

		} else if($this->setting['markup_type'] == 'fix'){

			$this->add_message('Harga awal : ' . $this->format_price($new_price) );
			
			$fix_val = floatval($this->setting['fix_val']);
			$new_price = $new_price + $fix_val;

			$this->add_message( 'Menambahkan markup harga sebesar ' . $this->format_price($fix_val) . ' (fix)');
		
		} else if( $this->setting['markup_type'] == 'normal' ){ // Harga Jual Rekomendasi
			$new_price = intval($data->harga_normal); // use harga_normal
			$this->add_message( 'Menggunakan harga normal ' . $this->format_price($data->harga_normal) . ' (rekomendasi)');
		}

		$this->add_message('Menyimpan dengan harga : ' . $this->format_price($new_price) );
		
		update_post_meta( $post_id, 'my_meta_harga', $new_price );

		// STOK
		update_post_meta( $post_id, 'my_meta_stock', 'ready' );
		// update_post_meta( $post_id, 'my_meta_status_stock', 'yes' );

		// Product categories.
		if ( isset( $data->kategori ) ) {

			if(is_array($data->kategori)){
				foreach ($data->kategori as $key => $kategori_name) {
					$this->set_term($post_id, $kategori_name, 'category');
				}
			} else {
				$this->set_term($post_id, $data->kategori, 'category');
			}
			
		}

		// Product tags.
		if ( isset( $data->tag ) ) {
			if(is_array($data->tag)){
				foreach ($data->tag as $key => $tag_name) {
					$this->set_term($post_id, $tag_name, 'post_tag');
				}
			} else {
				$this->set_term($post_id, $data->tag, 'post_tag');
			}
		}

		// BERAT
		if(isset($data->berat)){
			$weight = number_format($data->berat / 1000, 2); // convert to kg for lapakinstant
			update_post_meta( $post_id, 'my_meta_berat', $weight );
		}

		// UKURAN
		$_ukuran = false;
		if(isset($data->ukuran) && !empty($data->ukuran)){
			/**
			 * We are following the silly way lapakinstant to store data, LOL
			 * @var array
			 */
			$meta_ukuran = array(
				'multi' => 'Ukuran', // opsi name
			);

			foreach ( (array) $data->ukuran as $ukuran) {
				$meta_ukuran[$ukuran] = 0; // 0 as stock value (not defined)
			}
			update_post_meta( $post_id, 'my_nama_opsis_new', $meta_ukuran );
			$_ukuran = true;

			$this->add_message('Menyimpan opsi ukuran : ' . implode(',', $data->ukuran) );
		}

		// WARNA
		if(isset($data->warna) && !empty($data->warna)){
			$meta_warna = array(
				'multi' => 'Warna', // opsi name
			);

			foreach ( (array) $data->warna as $warna) {
				$meta_warna[$warna] = 0; // 0 as stock value (not defined)
			}

			$meta_key_warna = ($_ukuran) ? 'my_nama_opsis_new2' : 'my_nama_opsis_new';
			update_post_meta( $post_id, $meta_key_warna, $meta_warna );

			$this->add_message('Menyimpan opsi warna : ' . implode(',', $data->warna) );
		}
		
	}

	private function format_price($price){
		return number_format( $price, 0, ',', '.' );
	}


	private function save_product_images( $id, $images ) {
		if ( is_array( $images ) && !empty($images) ) {
			$gallery = array();

			$i = 0;
			$failed_upload = 0;
			foreach ( $images as $image ) {

				$upload = $this->upload_image_from_url( esc_url_raw( $image ) );

				if ( !$upload ) {
					// log failed
					$failed_upload++;
				}

				$attachment_id = $this->set_uploaded_image_as_attachment( $upload, $id );

				$gallery[] = $attachment_id;

				// if( (count($images) > 1) && ($i > 0) ){
				// 	// many images? set as gallery
				// 	$gallery[] = $attachment_id;
				// } else {
				// 	// just one? set as thumb
				// 	set_post_thumbnail( $id, $attachment_id );
				// }

			$i++;
			}

			if(!empty($failed_upload)){
				// change to draft
				wp_update_post( array('ID' => $id, 'post_status' => 'draft') );

				$message = __('Beberapa gambar gagal diupload, post status dirubah menjadi draft.', 'bandros_import');
				$this->add_message($message);
			}

			if ( ! empty( $gallery ) ) {
				update_post_meta( $id, 'my_meta_image_gallery', implode( ',', $gallery ) );
			}
		} else {
			delete_post_thumbnail( $id );
			update_post_meta( $id, 'my_meta_image_gallery', '' );
		}
	}


	public function do_import(){

		$this->post_status = isset( $this->setting['post_status'] ) ? sanitize_text_field( $this->setting['post_status'] ) : 'publish';

		$product_name = (isset($this->product_data->nama)) ? $this->product_data->nama : '';
		$product_sku = (isset($this->product_data->sku)) ? $this->product_data->sku : '';

		$this->add_message('Mengecek produk dengan nama : ' . $product_name . ' dan SKU : ' . $product_sku);

		// if exists, force to draft
		if( $this->check_product_exist($product_name, $product_sku) ){
			$this->add_message( sprintf(__('Mengimport dengan status draft', 'bandros_import') ) );
			$this->post_status = 'draft';
		} else {
			$this->add_message('Produk belum ada.');
		}

		$this->add_product();

	}

}