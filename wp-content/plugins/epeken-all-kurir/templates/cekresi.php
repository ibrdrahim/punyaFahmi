<?php
 get_header();
 $_noresi = $_GET["noresi"];
 $kec_url = admin_url('admin-ajax.php');
 wp_enqueue_script('ajax_cek_resi',plugins_url('/js/cekresi.js',__FILE__), array('jquery'));
 wp_localize_script( 'ajax_cek_resi', 'PT_Ajax_Cek_Resi', array(
 'ajaxurl'       => $kec_url,
 'nextNonce'     => wp_create_nonce('myajax-next-nonce'),
 ));

?>

 <div class="clearfix"> </div>
 <div style="position: relative; float: left; width: 100%;height: auto; margin-top: 10px;margin-bottom: 10px;z-index: 9999;">
  <div style="margin: 0 auto;width: 61%; border: 1">
   <div id="form_div">
   <h3>Cek Resi</h3>
     <div style="margin: 2px;">
      <label for="noresi" style="width: 20%">Nomor Resi:</label>
      <input type="text"  name="noresi" style="width: 40%;border: 1px solid #286090" id="noresi" value="<?php echo $_noresi; ?>"/>
      <input type="hidden" name="kurir" id="kurir" value="jne"/>
	<button type="submit" class="btn button" id="cekbutton">Cek Resi</button>      
     </div>
    <div class="clearfix"></div><div><em>Tracking ini baru support kurir JNE saja.</em></div>
    </div>
  <div id="cekresiresult" style="width: 100%;">
        </div>
  </div>
 </div>
<script type="text/javascript">
jQuery(document).ready(function($){
do_cek_resi();
});     
</script>
<?php
  get_footer();
?>
