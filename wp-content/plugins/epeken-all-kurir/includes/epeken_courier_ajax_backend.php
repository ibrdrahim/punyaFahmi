<?php
  if ( ! defined( 'ABSPATH' ) ) exit;

  include_once('query.php');
  function epeken_get_list_kecamatan() {
	$kotakab = sanitize_text_field($_GET['kota']);
	$nextnonce = sanitize_text_field($_GET['nextNonce']);
	
	if(!wp_verify_nonce($nextnonce,'myajax-next-nonce')){
			die('Invalid Invocation');
		}
	$li_kecamatan = array();
	if(!empty($kotakab))
	{
		$li_kecamatan = epeken_get_list_of_kecamatan($kotakab);		
	}

	foreach($li_kecamatan as $value){
		echo trim($value).';';
	} 
    }
   function epeken_get_awb_tracking() {
                $awb = sanitize_text_field($_GET['awb']);
                $kurir = sanitize_text_field($_GET['kurir']);
                $nextnonce = sanitize_text_field($_GET['nextNonce']);
                if(!wp_verify_nonce($nextnonce,'myajax-next-nonce')){
                        die('Invalid Invocation');
                }
                $tracking_html = '';
                if (!empty($awb) && !empty($kurir)){
                        $tracking_html = epeken_get_track_info($kurir,$awb);
                }
                echo $tracking_html;
   }


   add_action('wp_ajax_get_list_kecamatan','epeken_get_list_kecamatan');
   add_action('wp_ajax_nopriv_get_list_kecamatan','epeken_get_list_kecamatan');
   add_action('wp_ajax_get_track_awb','epeken_get_awb_tracking');
   add_action('wp_ajax_nopriv_get_track_awb','epeken_get_awb_tracking');
  
?>
