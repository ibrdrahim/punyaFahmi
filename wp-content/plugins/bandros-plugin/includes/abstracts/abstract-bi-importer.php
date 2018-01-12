<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract Bandros Importer Class
 *
 * @version  2.0
 * @author lafif <lafif@astahdziq.in>
 */
abstract class BI_Importer {

	protected $setting = array();
	
	protected $product_type = 'simple';

	protected $post_status = 'publish';

	protected $in_stock = true;
	
	protected $product_data = array();

	protected $message = array();

	/**
	 * Constructor
	 */
	public function __construct($setting, $product_data) {
		$this->setting = $setting;
		$this->product_data = $product_data;
	}
	
	/**
	 * Add message
	 * @param [type] $message [description]
	 * @param string $type    [description]
	 */
	public function add_message($message, $type='notice'){
		if(!isset($this->message[$type])){
			$this->message[$type] = array();
		}

		$this->message[$type][] = apply_filters( 'bi_add_message', $message, $type, $this );
	}

	/**
	 * Get message
	 * @param  string $type [description]
	 * @return [type]       [description]
	 */
	public function get_message($type='notice'){
		if(!isset($this->message[$type])){
			$this->message[$type] = array();
		}
		
		return apply_filters( 'bi_import_get_message', $this->message[$type], $type, $this );
	}

	public function upload_image_from_url( $image_url ) {
		$upload_for = 'product_image';
		$file_name = basename( current( explode( '?', $image_url ) ) );
		$parsed_url = @parse_url( $image_url );

		// Check parsed URL.
		if ( ! $parsed_url || ! is_array( $parsed_url ) ) {
			$message = __('URL gambar tidak valid.', 'bandros_import');
			$this->add_message($message);
			return false;
		}

		// Ensure url is valid.
		$image_url = str_replace( ' ', '%20', $image_url );

		// Get the file.
		$response = wp_safe_remote_get( $image_url, array(
			'timeout' => 10
		) );

		if ( is_wp_error( $response ) ) {
			$message = __('Gagal mengambil gambar dari url ('.$response->get_error_message().').', 'bandros_import');
			$this->add_message($message);
			return false;

		} elseif ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			$message = __('Gagal mengambil gambar dari url.', 'bandros_import');
			$this->add_message($message);
			return false;
		}

		// Ensure we have a file name and type.
		$wp_filetype = wp_check_filetype( $file_name, $this->allowed_image_mime_types() );

		if ( ! $wp_filetype['type'] ) {
			$headers = wp_remote_retrieve_headers( $response );
			if ( isset( $headers['content-disposition'] ) && strstr( $headers['content-disposition'], 'filename=' ) ) {
				$disposition = end( explode( 'filename=', $headers['content-disposition'] ) );
				$disposition = sanitize_file_name( $disposition );
				$file_name   = $disposition;
			} elseif ( isset( $headers['content-type'] ) && strstr( $headers['content-type'], 'image/' ) ) {
				$file_name = 'image.' . str_replace( 'image/', '', $headers['content-type'] );
			}
			unset( $headers );

			// Recheck filetype
			$wp_filetype = wp_check_filetype( $file_name, $this->allowed_image_mime_types() );

			if ( ! $wp_filetype['type'] ) {
				$message = __('Jenis gambar tidak valid.', 'bandros_import');
				$this->add_message($message);
				return false;
			}
		}

		// Upload the file.
		$upload = wp_upload_bits( $file_name, '', wp_remote_retrieve_body( $response ) );

		if ( $upload['error'] ) {
			$message = $upload['error'];
			$this->add_message($message);
			return false;
		}

		// Get filesize.
		$filesize = filesize( $upload['file'] );

		if ( 0 == $filesize ) {
			@unlink( $upload['file'] );
			unset( $upload );
			$message =  __('Ukuran gambar 0.', 'bandros_import');
			$this->add_message($message);
			return false;
		}

		unset( $response );

		do_action( 'bi_uploaded_image_from_url', $upload, $image_url, $upload_for  );

		return $upload;
	}

	public function set_uploaded_image_as_attachment( $upload, $id = 0 ) {
		$info    = wp_check_filetype( $upload['file'] );
		$title   = '';
		$content = '';

		if ( $image_meta = @wp_read_image_metadata( $upload['file'] ) ) {
			if ( trim( $image_meta['title'] ) && ! is_numeric( sanitize_title( $image_meta['title'] ) ) ) {
				$title = sanitize_text_field( $image_meta['title'] );
			}
			if ( trim( $image_meta['caption'] ) ) {
				$content = sanitize_text_field( $image_meta['caption'] );
			}
		}

		$attachment = array(
			'post_mime_type' => $info['type'],
			'guid'           => $upload['url'],
			'post_parent'    => $id,
			'post_title'     => $title,
			'post_content'   => $content
		);

		$attachment_id = wp_insert_attachment( $attachment, $upload['file'], $id );
		if ( ! is_wp_error( $attachment_id ) ) {
			wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $upload['file'] ) );
		}

		return $attachment_id;
	}

	public function allowed_image_mime_types() {
		return apply_filters( 'bi_allowed_image_mime_types', array(
			'jpg|jpeg|jpe' => 'image/jpeg',
			'gif'          => 'image/gif',
			'png'          => 'image/png',
			'bmp'          => 'image/bmp',
			'tiff|tif'     => 'image/tiff',
			'ico'          => 'image/x-icon',
		) );
	}

	public function set_term($product_id, $term_name, $taxonomy ){
		$term_exists = term_exists($term_name, $taxonomy);

		$taxonomy_name = $taxonomy;
		
		if( ($taxonomy == 'product_cat') || ($taxonomy == 'category') )
			$taxonomy_name = 'kategori produk';

		if( ($taxonomy == 'product_tag') || ($taxonomy == 'post_tag') )
			$taxonomy_name = 'produk tag';

		$message = 'Menyimpan ' . $taxonomy_name . ' ' . $term_name;

		if(!empty($term_exists)){
			$term_id = (is_array($term_exists) && isset($term_exists['term_id'])) ? absint( $term_exists['term_id'] ) : absint( $term_exists );
			wp_set_object_terms( $product_id, $term_id, $taxonomy );
			$message = 'Menyimpan '.$taxonomy_name.' '.$term_name.' ( id : ' . $term_id . ' )';
		} else {
			$insert_term = wp_insert_term($term_name, $taxonomy);
			if(isset($insert_term['term_id'])){
				$term_id = absint( $insert_term['term_id'] );
				wp_set_object_terms( $product_id, $term_id, $taxonomy );
				$message = 'Menyimpan '.$taxonomy_name.' '.$term_name.' ( baru )';
			}
		}

		$this->add_message($message);
	}

	/**
	 * main action to do import
	 * Force Extending class to define this method
	 * @return [type] [description]
	 */
	abstract public function do_import();
}
