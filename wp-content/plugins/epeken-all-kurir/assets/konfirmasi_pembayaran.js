(function($){
		konfirmasi_pembayaran = function(){
			 $('#submit_konfirmasi').on('click',function(){if(confirm("Submit Konfirmasi Pembayaran Anda ?") == true){
			 $("#submit_konfirmasi").text("Mengirim Konfirmasi..");//alert("Lanjut");
			 $("#submit_konfirmasi").prop('disabled', true);
			 $.get(PT_Ajax_Konfirmasi_Pembayaran.ajaxurl, 
                                                                {   
                                                                        action: 'submit_konfirmasi_pembayaran',
                                                                        nextNonce: PT_Ajax_Konfirmasi_Pembayaran.nextNonce,
                                                                        orderid: $("#orderid_pembayaran").val(),
									tglpembayaran: $("#tgl_pembayaran").val(),
									namapembayar: $("#nama_pembayar").val(),
									rekeningpembayar: $("#rekening_pembayar").val(),
									namabank: $("#nama_bank").val(),
									notespembayaran: $("#notes_pembayaran").val()	       
                                                                },  
                                                                function(data,status){
									$("#submit_konfirmasi").text("Submit");
									$("#submit_konfirmasi").prop('disabled', false);
data=data.slice(0,-1);
									window.location.replace(document.URL+'/?message='+data);
								});
			}
			});
		}
})(jQuery);
