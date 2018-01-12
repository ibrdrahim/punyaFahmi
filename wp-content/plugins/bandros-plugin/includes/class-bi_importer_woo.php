<?php
/**
 * BI_Importer_Woo Class.
 *
 * @class       BI_Importer_Woo
 * @version		2.0
 * @author lafif <lafif@astahdziq.in>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * BI_Importer_Woo class.
 */
class BI_Importer_Woo extends BI_Importer {

	private function check_product_exist($product_name='', $product_sku=''){
		global $wpdb;

		// check product by title
		// $product_exist = get_page_by_title( $product_name, OBJECT, 'product' );
		if(!empty($product_name)){
			$product_exist = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_title='%s' AND post_status='publish' LIMIT 1", wc_clean( $product_name ) ) );
			if(!empty($product_exist)){
				$message = sprintf( __('Produk dengan nama %s sudah ada dengan id %d.', 'bandros_import'), $product_name, $product_exist );
				$this->add_message($message);

				return $product_exist;
			}
		}

		// check product by sku
		if(!empty($product_sku)){
			$id_exist = wc_get_product_id_by_sku($product_sku);
			if(!empty($id_exist)){
				$message = sprintf( __('Produk dengan sku %s sudah ada dengan nama %s.', 'bandros_import'), $product_sku, get_the_title( $id_exist ) );
				$this->add_message($message);

				return $id_exist;
			}
		}

		return false;
		
	}

	private function add_product(){

		// Check permissions.
		if ( ! current_user_can( 'publish_products' ) ) {
			$message = __('Anda tidak diperbolehkan mengimport produk.', 'bandros_import');
			$this->add_message($message, 'error');

			return;
		}

		// has variations? set as variable
		if( (isset($this->product_data->ukuran) && !empty($this->product_data->ukuran) ) || (isset($this->product_data->warna) && !empty($this->product_data->warna) ) ){
			$this->product_type = 'variable';

			$this->add_message( 'Variasi ditemukan, mengimport sebagai produk '.$this->product_type.'.' );

		} else {
			$this->product_type = 'simple';

			$this->add_message( 'Variasi tidak ditemukan, mengimport sebagai produk '.$this->product_type.'.' );
		}

		/**
		 * Create product title from prefix suffix
		 * @var [type]
		 */
		$_product_title = array();
		if(isset($this->setting['prefix_title']) && !empty($this->setting['prefix_title']))
			$_product_title[] = wc_clean( $this->setting['prefix_title'] );

		$_product_title[] = wc_clean( $this->product_data->nama );

		if(isset($this->setting['suffix_title']) && !empty($this->setting['suffix_title']))
			$_product_title[] = wc_clean( $this->setting['suffix_title'] );

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
			'post_type'    => 'product',
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

			// Save product meta fields.
			$this->save_product_meta( $id, $this->product_data );

			if ( $this->product_type == 'variable' ) {

				$this->link_all_variations($id, $this->product_data);
			}
		}
	}

	private function update_product(){

		$message = __('update produk belum jadi.', 'bandros_import');
		$this->add_message($message);

		// Check permissions.
		if ( ! current_user_can( 'publish_products' ) ) {
			$message = __('Anda tidak diperbolehkan mengimport produk.', 'bandros_import');
			$this->add_message($message, 'error');
			return;
		}
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

				if( (count($images) > 1) && ($i > 0) ){
					// many images? set as gallery
					$gallery[] = $attachment_id;
				} else {
					// just one? set as thumb
					set_post_thumbnail( $id, $attachment_id );
				}

			$i++;
			}

			if(!empty($failed_upload)){
				// change to draft
				wp_update_post( array('ID' => $id, 'post_status' => 'draft') );

				$message = __('Beberapa gambar gagal diupload, post status dirubah menjadi draft.', 'bandros_import');
				$this->add_message($message);
			}

			if ( ! empty( $gallery ) ) {
				update_post_meta( $id, '_product_image_gallery', implode( ',', $gallery ) );
			}
		} else {
			delete_post_thumbnail( $id );
			update_post_meta( $id, '_product_image_gallery', '' );
		}
	}

	
	private function save_product_meta( $product_id, $data ) {
		global $wpdb;

		// Product Type.
		$product_type = $this->product_type;

		$_product_type = get_the_terms( $product_id, 'product_type' ); // current product type

		if ( isset( $data->type ) ) {
			// if type declared
			$product_type = wc_clean( $data->type );
			wp_set_object_terms( $product_id, $product_type, 'product_type' );

		} else if( is_array( $_product_type ) ) {
			// if update, and already has product type
			$_product_type = current( $_product_type );
			$product_type  = $_product_type->slug;
		} else {
			// set product type on add
			wp_set_object_terms( $product_id, $product_type, 'product_type' );
		}

		// Default total sales.
		add_post_meta( $product_id, 'total_sales', '0', true ); // ignore if exists

		// SKU.
		if ( isset( $data->sku ) ) {
			$sku     = get_post_meta( $product_id, '_sku', true );
			$new_sku = wc_clean( $data->sku );

			if ( '' == $new_sku ) {
				update_post_meta( $product_id, '_sku', '' );
			} elseif ( $new_sku !== $sku ) {
				if ( ! empty( $new_sku ) ) {
					$unique_sku = wc_product_has_unique_sku( $product_id, $new_sku );
					if ( ! $unique_sku ) {
						$this->add_message( __('SKU sudah terdaftar di produk lain, post status dirubah menjadi draft.', 'bandros_import') );

						// change to draft
						wp_update_post( array('ID' => $product_id, 'post_status' => 'draft') );
					
					} else {
						update_post_meta( $product_id, '_sku', $new_sku );
					}
				} else {
					update_post_meta( $product_id, '_sku', '' );
				}
			}
		}

		// prices.
		if ( in_array( $product_type, array( 'variable', 'grouped' ) ) ) {

			// Variable and grouped products have no prices.
			update_post_meta( $product_id, '_regular_price', '' );
			update_post_meta( $product_id, '_sale_price', '' );
			update_post_meta( $product_id, '_sale_price_dates_from', '' );
			update_post_meta( $product_id, '_sale_price_dates_to', '' );
			update_post_meta( $product_id, '_price', '' );

		} else {
			
			$new_price = wc_format_decimal($data->harga_member); // default

			if($this->setting['markup_type'] == 'percent'){

				$this->add_message('Harga awal : ' . wc_price($data->harga_member) );
				
				$percent_val = wc_format_decimal($this->setting['percent_val']);
				$percent_price = ($percent_val / 100) * $new_price;
				$new_price = $new_price + $percent_price;

				$this->add_message( 'Menambahkan markup harga sebesar '.wc_price($percent_price).' ( '.$percent_val.'% )' );

			} else if($this->setting['markup_type'] == 'fix'){

				$this->add_message('Harga awal : ' . wc_price($data->harga_member) );
				
				$fix_val = wc_format_decimal($this->setting['fix_val']);
				$new_price = $new_price + $fix_val;

				$this->add_message( 'Menambahkan markup harga sebesar ' . wc_price($fix_val) . ' (fix)');
			
			} else if( $this->setting['markup_type'] == 'normal' ){ // Harga Jual Rekomendasi
				$new_price = intval($data->harga_normal); // use harga_normal
				$this->add_message( 'Menggunakan harga normal ' . wc_price($data->harga_normal) . ' (rekomendasi)');
			}

			$this->add_message('Menyimpan dengan harga : ' . wc_price($new_price) );
			
			_wc_save_product_price( $product_id, $new_price );

		}

		// stock
		// Stock status.
		if ( $this->in_stock ) {
			$stock_status = ( true === $this->in_stock ) ? 'instock' : 'outofstock';
		} else {
			$stock_status = get_post_meta( $product_id, '_stock_status', true );

			if ( '' === $stock_status ) {
				$stock_status = 'instock';
			}
		}

		// Stock Data.
		if ( 'yes' == get_option( 'woocommerce_manage_stock' ) ) {
			// Manage stock.
			$managing_stock = get_post_meta( $product_id, '_manage_stock', true );

			$backorders = get_post_meta( $product_id, '_backorders', true );

			if ( 'grouped' == $product_type ) {

				update_post_meta( $product_id, '_manage_stock', 'no' );
				update_post_meta( $product_id, '_backorders', 'no' );
				update_post_meta( $product_id, '_stock', '' );

				wc_update_product_stock_status( $product_id, $stock_status );

			} elseif ( 'external' == $product_type ) {

				update_post_meta( $product_id, '_manage_stock', 'no' );
				update_post_meta( $product_id, '_backorders', 'no' );
				update_post_meta( $product_id, '_stock', '' );

				wc_update_product_stock_status( $product_id, 'instock' );
			} elseif ( 'yes' == $managing_stock ) {
				update_post_meta( $product_id, '_backorders', $backorders );

				// Stock status is always determined by children so sync later.
				if ( 'variable' !== $product_type ) {
					wc_update_product_stock_status( $product_id, $stock_status );
				}

				// Stock quantity.
				if ( isset( $data['stock_quantity'] ) ) {
					wc_update_product_stock( $product_id, wc_stock_amount( $data['stock_quantity'] ) );
				} else if ( isset( $data['inventory_delta'] ) ) {
					$stock_quantity  = wc_stock_amount( get_post_meta( $product_id, '_stock', true ) );
					$stock_quantity += wc_stock_amount( $data['inventory_delta'] );

					wc_update_product_stock( $product_id, wc_stock_amount( $stock_quantity ) );
				}
			} else {

				// Don't manage stock.
				update_post_meta( $product_id, '_manage_stock', 'no' );
				update_post_meta( $product_id, '_backorders', $backorders );
				update_post_meta( $product_id, '_stock', '' );

				wc_update_product_stock_status( $product_id, $stock_status );
			}

		} elseif ( 'variable' !== $product_type ) {
			wc_update_product_stock_status( $product_id, $stock_status );
		}

		// Product categories.
		if ( isset( $data->kategori ) ) {

			if(is_array($data->kategori)){
				foreach ($data->kategori as $key => $kategori_name) {
					$this->set_term($product_id, $kategori_name, 'product_cat');
				}
			} else {
				$this->set_term($product_id, $data->kategori, 'product_cat');
			}
			
		}

		// Product tags.
		if ( isset( $data->tag ) ) {
			if(is_array($data->tag)){
				foreach ($data->tag as $key => $tag_name) {
					$this->set_term($product_id, $tag_name, 'product_tag');
				}
			} else {
				$this->set_term($product_id, $data->tag, 'product_tag');
			}
		}
		
		// weight
		update_post_meta( $product_id, '_weight', ( '' === $data->berat ) ? '' : wc_format_decimal( $data->berat ) );


		// visibility
		update_post_meta( $product_id, '_visibility', 'visible' );

		// attributes (ukuran, warna)
		$bandros_attributes = array();

		// ukuran
		if(isset($data->ukuran) && !empty($data->ukuran)){
			$bandros_attributes[] = array(
				'name' => 'ukuran',
				'visible' => true, // default
				'variation' => true, // default
				// 'position' => '0',
				'options' => $data->ukuran,
			);
		}

		// warna
		if(isset($data->warna) && !empty($data->warna)){
			$bandros_attributes[] = array(
				'name' => 'warna',
				'visible' => true, // default
				'variation' => true, // default
				'options' => $data->warna,
			);
		}

		// Process Attributes.
		if ( !empty($bandros_attributes) ) {
			$attributes = array();

			foreach ( $bandros_attributes as $attribute ) {
				$is_taxonomy = 0;
				$taxonomy    = 0;

				if ( ! isset( $attribute['name'] ) ) {
					continue;
				}

				$attribute_slug = sanitize_title( $attribute['name'] );

				if ( isset( $attribute['slug'] ) ) {
					$taxonomy       = $this->get_attribute_taxonomy_by_slug( $attribute['slug'] );
					$attribute_slug = sanitize_title( $attribute['slug'] );
				}

				if ( $taxonomy ) {
					$is_taxonomy = 1;
				}

				if ( $is_taxonomy ) {

					if ( isset( $attribute['options'] ) ) {
						$options = $attribute['options'];

						if ( ! is_array( $attribute['options'] ) ) {
							// Text based attributes - Posted values are term names.
							$options = explode( WC_DELIMITER, $options );
						}

						$values = array_map( 'wc_sanitize_term_text_based', $options );
						$values = array_filter( $values, 'strlen' );
					} else {
						$values = array();
					}

					// Update post terms.
					if ( taxonomy_exists( $taxonomy ) ) {
						wp_set_object_terms( $product_id, $values, $taxonomy );
					}

					if ( ! empty( $values ) ) {
						// Add attribute to array, but don't set values.
						$attributes[ $taxonomy ] = array(
							'name'         => $taxonomy,
							'value'        => '',
							'position'     => isset( $attribute['position'] ) ? (string) absint( $attribute['position'] ) : '0',
							'is_visible'   => ( isset( $attribute['visible'] ) && $attribute['visible'] ) ? 1 : 0,
							'is_variation' => ( isset( $attribute['variation'] ) && $attribute['variation'] ) ? 1 : 0,
							'is_taxonomy'  => $is_taxonomy
						);
					}

				} elseif ( isset( $attribute['options'] ) ) {
					// Array based.
					if ( is_array( $attribute['options'] ) ) {
						$values = implode( ' ' . WC_DELIMITER . ' ', array_map( 'wc_clean', $attribute['options'] ) );

					// Text based, separate by pipe.
					} else {
						$values = implode( ' ' . WC_DELIMITER . ' ', array_map( 'wc_clean', explode( WC_DELIMITER, $attribute['options'] ) ) );
					}

					// Custom attribute - Add attribute to array and set the values.
					$attributes[ $attribute_slug ] = array(
						'name'         => wc_clean( $attribute['name'] ),
						'value'        => $values,
						'position'     => isset( $attribute['position'] ) ? (string) absint( $attribute['position'] ) : '0',
						'is_visible'   => ( isset( $attribute['visible'] ) && $attribute['visible'] ) ? 1 : 0,
						'is_variation' => ( isset( $attribute['variation'] ) && $attribute['variation'] ) ? 1 : 0,
						'is_taxonomy'  => $is_taxonomy
					);
				}
			}

			uasort( $attributes, 'wc_product_attribute_uasort_comparison' );

			update_post_meta( $product_id, '_product_attributes', $attributes );
		}
	}

	private function link_all_variations($post_id, $data) {

		// if ( ! defined( 'WC_MAX_LINKED_VARIATIONS' ) ) {
		// 	define( 'WC_MAX_LINKED_VARIATIONS', 49 );
		// }

		wc_set_time_limit( 0 );

		$variations = array();
		$_product   = wc_get_product( $post_id, array( 'product_type' => 'variable' ) );

		// Put variation attributes into an array
		foreach ( $_product->get_attributes() as $attribute ) {

			if ( ! $attribute['is_variation'] ) {
				continue;
			}

			$attribute_field_name = 'attribute_' . sanitize_title( $attribute['name'] );

			if ( $attribute['is_taxonomy'] ) {
				$options = wc_get_product_terms( $post_id, $attribute['name'], array( 'fields' => 'slugs' ) );
			} else {
				$options = explode( WC_DELIMITER, $attribute['value'] );
			}

			$options = array_map( 'trim', $options );

			$variations[ $attribute_field_name ] = $options;
		}

		// Quit out if none were found
		if ( sizeof( $variations ) == 0 ) {
			return;
		}

		// Get existing variations so we don't create duplicates
		$available_variations = array();

		foreach( $_product->get_children() as $child_id ) {
			$child = $_product->get_child( $child_id );

			if ( ! empty( $child->variation_id ) ) {
				$available_variations[] = $child->get_variation_attributes();
			}
		}

		// Created posts will all have the following data
		$variation_post_data = array(
			'post_title'   => 'Product #' . $post_id . ' Variation',
			'post_content' => '',
			'post_status'  => 'publish',
			'post_author'  => get_current_user_id(),
			'post_parent'  => $post_id,
			'post_type'    => 'product_variation'
		);

		$variation_ids       = array();
		$added               = 0;
		$possible_variations = wc_array_cartesian( $variations );

		foreach ( $possible_variations as $variation ) {

			// Check if variation already exists
			if ( in_array( $variation, $available_variations ) ) {
				continue;
			}

			$variation_id = wp_insert_post( $variation_post_data );

			$variation_ids[] = $variation_id;

			foreach ( $variation as $key => $value ) {
				update_post_meta( $variation_id, $key, $value );
			}

			// Save stock status
			update_post_meta( $variation_id, '_manage_stock', 'no');

			$stock_status = ( true == $this->in_stock ) ? 'instock' : 'outofstock';
			wc_update_product_stock_status($variation_id, $stock_status);

			// weight
			update_post_meta( $variation_id, '_weight', ( '' === $data->berat ) ? '' : wc_format_decimal( $data->berat ) );

			// prices
			$new_price = wc_format_decimal($data->harga_member); // default

			$this->add_message('Harga awal : ' . wc_price($data->harga_member) );

			if($this->setting['markup_type'] == 'percent'){
				
				$percent_val = wc_format_decimal($this->setting['percent_val']);
				$percent_price = ($percent_val / 100) * $new_price;
				$new_price = $new_price + $percent_price;

				$this->add_message( 'Menambahkan markup harga sebesar '.wc_price($percent_price).' ( '.$percent_val.'% )' . ' untuk variasi produk #' . $variation_id  );

			} else if($this->setting['markup_type'] == 'fix'){
				
				$fix_val = wc_format_decimal($this->setting['fix_val']);
				$new_price = $new_price + $fix_val;

				$this->add_message( 'Menambahkan markup harga sebesar ' . wc_price($fix_val) . ' (fix)' . ' untuk variasi produk #' . $variation_id );
			
			}

			$this->add_message('Menyimpan dengan harga : ' . wc_price($new_price) . ' untuk variasi produk #' . $variation_id );

			_wc_save_product_price( $variation_id, $new_price );

			$added++;

			do_action( 'product_variation_linked', $variation_id );

			// if ( $added > WC_MAX_LINKED_VARIATIONS ) {
			// 	break;
			// }
		}

		$this->add_message( __('Mensinkronkan variasi produk.', 'bandros_import') );
		WC_Product_Variable::sync( $post_id );
		WC_Product_Variable::sync_stock_status( $post_id );

		delete_transient( 'wc_product_children_' . $post_id );
	}

	private function get_attribute_taxonomy_by_slug( $slug ) {
		$taxonomy = null;
		$attribute_taxonomies = wc_get_attribute_taxonomies();

		foreach ( $attribute_taxonomies as $key => $tax ) {
			if ( $slug == $tax->attribute_name ) {
				$taxonomy = 'pa_' . $tax->attribute_name;

				break;
			}
		}

		return $taxonomy;
	}


	public function do_import(){

		$this->post_status = isset( $this->setting['post_status'] ) ? wc_clean( $this->setting['post_status'] ) : 'publish';

		$this->add_message('Mengecek produk dengan nama : ' . $this->product_data->nama . ' dan SKU : ' . $this->product_data->sku);

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