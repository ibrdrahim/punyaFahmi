<?php
 function set_weight_1() {
        $full_product_list = array();
        $loop = new WP_Query( array( 'post_type' => array('product', 'product_variation'), 'posts_per_page' => -1 ) );
        while ( $loop->have_posts() ) : $loop->the_post();
                $theid = get_the_ID();
                $product = new WC_Product($theid);
                $weight = get_post_meta($theid,'_weight',true);
                if(empty($weight) || $weight === '0') {
                        update_post_meta($theid, '_weight', '1' );
                }   
        endwhile; wp_reset_query();
} 
 if(!empty ($_POST['tools1'])) {
        set_weight_1(); 
 }


function reset_dropship_origin_for_all_products () {
	$full_product_list = array();
        $loop = new WP_Query( array( 'post_type' => array('product', 'product_variation'), 'posts_per_page' => -1 ) );
        while ( $loop->have_posts() ) : $loop->the_post();
                $theid = get_the_ID();
                delete_post_meta($theid,'product_origin');
        endwhile; wp_reset_query();
 }

if(!empty ($_POST['tools2'])) {
        reset_dropship_origin_for_all_products(); 
 }

 update_option('epeken_subsidi_min_purchase', $_POST['txt_subsidi_min_purchase']);
 update_option('epeken_free_pc', $_POST['woocommerce_epeken_free_pc']);
 update_option('epeken_free_pc_q', $_POST['woocommerce_epeken_free_pc_q']);
 update_option('epeken_is_provinsi_free' , $_POST['epeken_is_provinsi_free']);
 update_option('epeken_province_for_free_shipping',$_POST['woocommerce_wc_shipping_tikijne_province_for_free_shipping']);
 update_option('epeken_mode_kode_pembayaran', $_POST['mode_kode_pembayaran']);
 update_option('epeken_freeship_n_province_for_free_shipping' , $_POST['freeship_n_province_for_free_shipping']);
 update_option('epeken_multiple_rate_setting', $_POST['epeken_multiple_rate_setting']);
 if(empty($_POST['epeken_multiple_rate_setting']))
  update_option('epeken_multiple_rate_setting', 'manual');

 update_option('epeken_enabled_jne', $_POST['enabled_jne']);
 update_option('epeken_subsidi_ongkir', $_POST['txt_subsidi_ongkir']);
 update_option('epeken_enabled_tiki', $_POST['enabled_tiki']);
 update_option('epeken_enabled_pos', $_POST['enabled_pos']);
 update_option('epeken_enabled_rpx', $_POST['enabled_rpx']);
 update_option('epeken_enabled_esl', $_POST['enabled_esl']);
 update_option('epeken_data_asal_kota', $_POST['data_asal_kota']);
 update_option('epeken_enabled_jne_reg',$_POST['enabled_jne_reg']);
 update_option('epeken_enabled_jne_oke',$_POST['enabled_jne_oke']);
 update_option('epeken_enabled_jne_yes',$_POST['enabled_jne_yes']);
 update_option('epeken_enabled_tiki_hds',$_POST['enabled_tiki_hds']);
 update_option('epeken_enabled_tiki_ons',$_POST['enabled_tiki_ons']);
 update_option('epeken_enabled_tiki_reg',$_POST['enabled_tiki_reg']);
 update_option('epeken_enabled_tiki_eco',$_POST['enabled_tiki_eco']); 
 update_option('epeken_enabled_wahana', $_POST['enabled_wahana']);
 update_option('epeken_enabled_jetez', $_POST['enabled_jetez']);
 update_option('epeken_enabled_sicepat_reg', $_POST['enabled_sicepat_reg']);
 update_option('epeken_enabled_sicepat_best', $_POST['enabled_sicepat_best']);
 update_option('epeken_perhitungan_biaya_tambahan',$_POST['epeken_perhitungan_biaya_tambahan']);
 update_option('epeken_markup_tarif_jne', $_POST['epeken_markup_tarif_jne']);
 update_option('epeken_markup_tarif_tiki', $_POST['epeken_markup_tarif_tiki']);
 update_option('epeken_markup_tarif_pos' , $_POST['epeken_markup_tarif_pos']);
 update_option('epeken_diskon_tarif_jne', $_POST['epeken_diskon_tarif_jne']);
 update_option('epeken_diskon_tarif_tiki', $_POST['epeken_diskon_tarif_tiki']);
 update_option('epeken_diskon_tarif_pos' , $_POST['epeken_diskon_tarif_pos']);
 update_option('epeken_freeship_n_city_for_free_shipping' , $_POST['freeship_n_city_for_free_shipping']);

 $enable_cekresi = $this -> settings['enable_cekresi_page'];
 if($enable_cekresi === 'yes') {
       $this->create_cek_resi_page();
       $this->add_cek_resi_page_to_prim_menu();
 }else{
       $this -> delete_cek_resi();
 }

 $epeken_biaya_tambahan_name = get_option('epeken_biaya_tambahan_name');
 $epeken_biaya_tambahan_amount  = get_option('epeken_biaya_tambahan_amount');
 $biaya_tambahan_name = $_POST['epeken_biaya_tambahan_name'];
 $biaya_tambahan_amount = $_POST['epeken_biaya_tambahan_amount'];

 if (!is_null($epeken_biaya_tambahan_name)){
        update_option ('epeken_biaya_tambahan_name',$biaya_tambahan_name);
 }else{
        add_option('epeken_biaya_tambahan_name',$biaya_tambahan_name,'','no');
 }

 if (!is_null($epeken_biaya_tambahan_amount)){
      if(is_numeric($biaya_tambahan_amount))
        update_option ('epeken_biaya_tambahan_amount',$biaya_tambahan_amount);
 }else{
        add_option('epeken_biaya_tambahan_amount','0','','no');
 }

 $epeken_packing_kayu_enabled = get_option('epeken_packing_kayu_enabled');
 $packing_kayu_enabled = $_POST['woocommerce_epeken_packing_kayu_enabled'];
 if ($packing_kayu_enabled === "on") {
                $packing_kayu_enabled = "yes";
 } else 
 {
                $packing_kayu_enabled = "no";
 }
 if(!is_null($epeken_packing_kayu_enabled)) {
        update_option('epeken_packing_kayu_enabled',$packing_kayu_enabled);
        if(is_numeric(trim($_POST['woocommerce_epeken_pengali_packing_kayu']))) {
         update_option('epeken_pengali_packing_kayu',trim($_POST['woocommerce_epeken_pengali_packing_kayu']));
        }   
        update_option('epeken_pc_packing_kayu', $_POST['woocommerce_epeken_pc_packing_kayu']);
 }else{
        add_option('epeken_packing_kayu_enabled',$packing_kayu_enabled,'','no');
        if(is_numeric(trim($_POST['woocommerce_epeken_pengali_packing_kayu']))) {
         add_option('epeken_pengali_packing_kayu',trim($_POST['woocommerce_epeken_pengali_packing_kayu'],'','no'));
        } else {
         add_option('epeken_pengali_packing_kayu','1','','no');
        }   
        add_option('epeken_pc_packing_kayu', $_POST['woocommerce_epeken_pc_packing_kayu'],'','no');
 }



?>
