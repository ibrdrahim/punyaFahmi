 
(function($){
	billing_kecamatan = function(){
			 $('#billing_city').on('change',function(){
						$('#div_epeken_popup').css('display','block');
                                                $.get(PT_Ajax_Bill_Kec.ajaxurl, 
                                                                {
                                                                        action: 'get_list_kecamatan',
                                                                        nextNonce: PT_Ajax_Bill_Kec.nextNonce,
                                                                        kota: this.value        
                                                                },
                                                                function(data,status){
                                                                $('#billing_address_2').empty();
                                                                        var arr = data.split(';');
                                                                           $('#billing_address_2').append('<option value="">Please Select Kecamatan</option>'); 
                                                                        $.each(arr, function (i,valu) {
                                                                         if (valu != '' && valu != '0') {               
                                                                           $('#billing_address_2').append('<option value="'+valu+'">'+valu+'</option>');       
                                                                         }
                                                                        });
                                                                $('#billing_address_2').trigger('chosen:updated');
								$('#div_epeken_popup').css('display','none');
                                                });
                                        });
					$('.checkout').on('submit', function(){
                                                $('#billing_state').attr('disabled',false);
                                                $('#shipping_state').attr('disabled',false);
                                        });
	  if($('#insurance_chkbox') != null) {
			$('#insurance_chkbox').on('change',function() {$('#billing_address_2 option').removeAttr('selected');$('#billing_address_2').change();$('#billing_city option').removeAttr('selected');$('#billing_city').change();alert('Silakan pilih kota dan kecamatan lagi.');});
	  }
	}
})(jQuery);
