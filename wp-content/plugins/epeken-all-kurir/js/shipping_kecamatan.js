 
(function($){
	shipping_kecamatan = function(){
			 $('#shipping_city').on('change',function(){
						$('#div_epeken_popup').css('display','block');
                                                $.get(PT_Ajax_Ship_Kec.ajaxurl, 
                                                                {
                                                                        action: 'get_list_kecamatan',
                                                                        nextNonce: PT_Ajax_Ship_Kec.nextNonce,
                                                                        kota: this.value        
                                                                },
                                                                function(data,status){
                                                                $('#shipping_address_2').empty();
                                                                        var arr = data.split(';');
                                                                           $('#shipping_address_2').append('<option value="">Please Select Kecamatan</option>'); 
                                                                        $.each(arr, function (i,valu) {
                                                                         if (valu != '' && valu != '0') {               
                                                                           $('#shipping_address_2').append('<option value="'+valu+'">'+valu+'</option>');       
                                                                         }
                                                                        });
                                                               $('#shipping_address_2').trigger('chosen:updated');
								$('#div_epeken_popup').css('display','none');
                                                });
                                        });
					$('.checkout').on('submit', function(){
                                                $('#billing_state').attr('disabled',false);
                                                $('#shipping_state').attr('disabled',false);
                                        });
	 	if($('#insurance_chkbox') != null) {
                $('#insurance_chkbox').on('change', function(){
						$('#insurance_chkbox').on('change',function() {$('#shipping_address_2 option').removeAttr('selected');$('#shipping_address_2').change();$('#shipping_city option').removeAttr('selected');$('#shipping_city').change();alert('Silakan pilih kota dan kecamatan lagi.');});
                                        });
          	}
	}
})(jQuery);
