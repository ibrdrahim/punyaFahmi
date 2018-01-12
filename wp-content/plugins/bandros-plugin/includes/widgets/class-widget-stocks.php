<?php
/**
 * BI_Widget_Stocks Class.
 *
 * @class       BI_Widget_Stocks
 * @version		1.0
 * @author lafif <lafif@astahdziq.in>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Adds BI_Widget_Stocks widget.
 */
class BI_Widget_Stocks extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'bi_widget_stocks', // Base ID
			esc_html__( 'Bandros Stock', 'bandros_import' ), // Name
			array( 'description' => esc_html__( 'Widget untuk menampilkan stok produk bandros', 'bandros_import' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
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
			$product = wc_get_product( get_queried_object_id() );
			$sku = $product->get_sku();
		} elseif (is_singular( 'post' ) && class_exists( 'LapakInstan_Function' )) {
			$sku = LapakInstan_Function::smart_meta( get_queried_object_id(), 'my_meta_kode_produk');
		} else {
			// not single product
			return;
		}

		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

		// echo "<pre>";
		// print_r($post);
		// echo "</pre>";

		?>
		<div class="container-search-stock">
			<div class="result-stock bandros-load-stock" data-sku="<?php echo $sku; ?>" data-error="<?php echo $instance['error_message']; ?>">
				<div class="loading"><?php _e('Mengambil data..', 'bandros_import'); ?></div>
				<div class="stock-data"></div>
			</div>
		</div>
		<?php

		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'Stok Produk', 'bandros_import' );
		$error_message = ! empty( $instance['error_message'] ) ? $instance['error_message'] : '';
		?>
		<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Judul:', 'bandros_import' ); ?></label> 
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'error_message' ) ); ?>"><?php esc_attr_e( 'Pesan Error:', 'bandros_import' ); ?></label> 
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'error_message' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'error_message' ) ); ?>" type="text" value="<?php echo esc_attr( $error_message ); ?>">
		</p>
		<?php 
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['error_message'] = ( ! empty( $new_instance['error_message'] ) ) ? strip_tags( $new_instance['error_message'] ) : '';

		return $instance;
	}

} // class BI_Widget_Stocks