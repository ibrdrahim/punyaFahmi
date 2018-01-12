(function($){

	$(document).ready(function(){
		$('.btn-group > a').on('click', function(e){
			e.preventDefault();
			$(this).siblings('a').removeClass('active');
			$(this).addClass('active');
		});

		$('input[type=radio][name=import_type]').change(function() {
			// alert(this.value);
			$('.import_type:not([data-if="'+this.value+'"])').addClass('hide');
			$('.import_type[data-if="'+this.value+'"]').removeClass('hide');
	    });

		$('select[name=markup_type]').change(function() {
			// alert(this.value);
			
			var select_container = $(this).closest('.select'),
				selected_type = $('.markup_type[data-if="'+this.value+'"]');
			
			select_container.removeAttr('style');

			$('.markup_type:not([data-if="'+this.value+'"])').addClass('hide');

			if(selected_type.outerWidth() != null ){
				var updated_width = select_container.outerWidth() - ( selected_type.outerWidth() + 5 );
				select_container.css('width', updated_width + 'px');
			}

			// alert(select_container.outerWidth() +' | '+ selected_type.outerWidth());
			selected_type.removeClass('hide');
	    });

		$('#window').on('click', function(e){
			e.preventDefault();
			$('#cmd').toggleClass('hide');
		});

		$('.import_progress').asPieProgress({
	        namespace: 'import_progress'
	  	});

		/**
		 * @since 2.0
		 * use our own select2 enqueue
		 * @param  {[type]} searchParams [description]
		 * @param  {[type]} data         [description]
		 * @return {[type]}              [description]
		 */
	 	var tagMatcher = function(searchParams, data) {
		  	// This bit taken from Select2's default matcher
		  	var match = $.extend(true, {}, data);
		  	if (searchParams.term == null || $.trim(searchParams.term) === '')
		    	return match;

		  	// Don't partial match tags, otherwise if a user has a tag 'abc' it is
		  	// impossible to then create a tag 'ab'.
		  	if (searchParams.term === data.text)
		    	return match;

		  	return null;
		}

		var $el_select_product = $("#select-product").select2({
	 		placeholder: 'Cari kode produk',
	 		language: {
		       	errorLoading: function () {
			      return 'Sedang mencari…';
			    },
			    inputTooLong: function (args) {
			      var overChars = args.input.length - args.maximum;

			      var message = 'Silahkan hapus ' + overChars + ' karakter';

			      if (overChars != 1) {
			        message += 's';
			      }

			      return message;
			    },
			    inputTooShort: function (args) {
			      var remainingChars = args.minimum - args.input.length;

			      var message = 'Silahkan masukkan minimal ' + remainingChars + ' karakter untuk mencari';

			      return message;
			    },
			    loadingMore: function () {
			      return 'Mengambil data kode produk…';
			    },
			    maximumSelected: function (args) {
			      var message = 'Anda hanya dapat memilih maksimal ' + args.maximum + ' kode';

			      if (args.maximum != 1) {
			        message += 's';
			      }

			      return message;
			    },
			    noResults: function () {
			      return 'Kode produk tidak ditemukan.';
			    },
			    searching: function () {
			      return 'Sedang mencari…';
			    }
		   	},
		  	maximumSelectionLength: 20,
		  	multiple: true,
		  	selectOnClose: true,
		  	tags: true,
			createTag: function(params) {
				return undefined;
			},
		  	// matcher: tagMatcher,
 		  	// allowClear: true,
		  	// dropdownCssClass: 'hideSearch',
		  	// tokenSeparators: [','],
		  	ajax: {
			    url: BI.ajax_url,
			    dataType: 'json',
			    delay: 250,
			    data: function (params) {
			      	return {
			      		action: 'search_product_codes',
			      		nonce: BI.nonce,
				        search: params.term, // search term
			      	};
			    },
			    processResults: function (data, params) {
			    	// alert(data);
			    	// console.log(data);
			    	// console.log(params);
			    	// params.page = params.page || 1;
			      	return {
			        	results: data.codes,
			        	// pagination: {
				        //   	more: (params.page * 30) < data.total_count
				        // }
			      	};
			    },
			    cache: true
		  	},
		  	escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
		  	minimumInputLength: 1,
		});

	 	/**
	 	 * Select branch
	 	 * @type {[type]}
	 	 */
		var $el_select_branch = $('#select-brand').select2();
		var $el_select_branch_request = $.ajax({
          	url: BI.ajax_url,
          	data: { 
          		action: 'get_brands',
          		nonce: BI.nonce
          	},
          	dataType: 'json',
          	type: 'GET',
          	success: function(res) {

          		var data = res.result;
          		// alert(data.length);

          		for (var d = 0; d < data.length; d++) {
				    var item = data[d];

				    // Create the DOM option
				    var option = new Option(item.text, item.id, false, false);

				    // Append it to the select
				    $el_select_branch.append(option);
			  	}

			  	// Update the selected options that are displayed
			  	$el_select_branch.trigger('change');
          	}
        });

	 	/**
	 	 * Select tag
	 	 * @type {[type]}
	 	 */
		var $el_select_tag = $('#select-tag').select2();
		var $el_select_tag_request = $.ajax({
          	url: BI.ajax_url,
          	data: { 
          		action: 'get_tags',
          		nonce: BI.nonce
          	},
          	dataType: 'json',
          	type: 'GET',
          	success: function(res) {

          		var data = res.result;
          		// alert(data.length);

          		for (var d = 0; d < data.length; d++) {
				    var item = data[d];

				    // Create the DOM option
				    var option = new Option(item.text, item.id, false, false);

				    // Append it to the select
				    $el_select_tag.append(option);
			  	}

			  	// Update the selected options that are displayed
			  	$el_select_tag.trigger('change');
          	}
        });

		/**
		 * AJAX ACTION
		 */

		// default
		var i,
			items_to_process = [],
			items_total = items_to_process.length,
			items_count = 0,
			items_percent = 0,
			items_successes = 0,
			items_errors = 0,
			items_failedlist = '',
			items_resulttext = '',
			items_timestart = new Date().getTime(),
			items_timeend = 0,
			items_totaltime = 0,
			items_continue = true;

		var ajaxUrl = BI.ajax_url,
			ajaxNonce = BI.nonce;

		$('#button_start').on( 'click', function(e) {

			var form = $(this).closest('form');

			// TODO js validate 
			var import_type = $("input[name='import_type']:checked").val();
			if (!import_type) {
		       	alert('Silahkan pilih jenis import');
		        return false;
		    } else {
		      	if( (import_type == 'brand') && !form.find("select[name='brand_id']").first().val() ){
		      		alert('Silahkan pilih brand.');
		        	return false;
		      	}
		      	if( (import_type == 'tag') && !form.find("#select-tag").first().val() ){
		      		alert('Silahkan pilih tag.');
		        	return false;
		      	}

		      	if( (import_type == 'product') && !form.find("#select-product").first().val() ){
		      		alert('Silahkan masukkan kode produk.');
		        	return false;
		      	}
		    }

			$(this).addClass('disabled');

			// Clear out the log
			$('.import_progress').asPieProgress('reset');
			// $("#cmd_text").html('');

			$('#cmd_text').append('<span class="process">Mengambil data produk</span>');

			$.ajax( {
				'dataType': "json",
				'data': {
					action: 'get_product_data',
					formdata: form.serialize(),
					nonce: ajaxNonce,
				},
				'type':     'post',
				'url':      ajaxUrl,
				'nonce': ajaxNonce,
				'success': function(data) {

					if(data.status == 'success'){

						$('#bi-stop').text('Batalkan').removeClass('hide');

						// reset vars and set items
						reset_items_to_process(data.items);

						// write log
						$('#cmd_text').append('<span class="result success">'+data.message+'</span>');
						$('#cmd_text').append('<span class="process">Mengimport produk</span>');
						
						BandrosImport( items_to_process.shift() );

					} else {
						$('#cmd_text').append('<span class="result failed">'+data.message+'</span>');
					}
	              	
              	}
			} );

			

			e.preventDefault();
		});

		function reset_items_to_process(items){
			i;
			items_to_process = items;
			items_total = items_to_process.length;
			items_count = 0;
			items_percent = 0;
			items_successes = 0;
			items_errors = 0;
			items_failedlist = '';
			items_resulttext = '';
			items_timestart = new Date().getTime();
			items_timeend = 0;
			items_totaltime = 0;
			items_continue = true;
		}

		// Stop button
		$(document).on('click', '#bi-stop', function(e){
			e.preventDefault();

			items_continue = false;
			$(this).text('Membatalkan..');
		});

		// Called after each resize. Updates debug information and the progress bar.
		function BandrosImportUpdateStatus( id, success, response ) {
			var processed = items_count + 1;
			$('.import_progress').asPieProgress('go', ( processed / items_total ) * 100 + '%');
			
			// console.log('items : ' + processed + ' from '+ items_total + ' = ' + ( processed / items_total ) * 100 + '%' );

			items_count = items_count + 1;

			if ( success ) {
				items_successes = items_successes + 1;
				
				// write to log
				// console.log(response.notices);
				$('#cmd_text').append('<span class="result success">'+response.message+'</span>');

				var notices = response.notices;
				if ( (typeof(notices) != 'undefined') && notices.length ) {
					var details = '<span class="result detail">';
					for (i = 0; i < notices.length; i++) { 
					    details += '<span class="content-detail">'+notices[i]+'</span>';
					}
					details += '</span>';

					$('#cmd_text').append(details);
				}
			}
			else {
				items_errors = items_errors + 1;
				items_failedlist = items_failedlist + ',' + id;

				// write to log
				// console.log(response.notices);
				$('#cmd_text').append('<span class="result failed">'+response.message+'</span>');

				var notices = response.notices;
				if ( (typeof(notices) != 'undefined') && notices.length ) {
					var details = '<span class="result detail">';
					for (i = 0; i < notices.length; i++) { 
					    details += '<span class="content-detail">'+notices[i]+'</span>';
					}
					details += '</span>';

					$('#cmd_text').append(details);
				}
			}
		}

		// Called when all items have been processed. Shows the results and cleans up.
		function BandrosImportFinishUp() {
			items_timeend = new Date().getTime();
			items_totaltime = Math.round( ( items_timeend - items_timestart ) / 1000 );

			// call ajax to clear session
			$.ajax( {
				'dataType': "json",
				'data': {
					action: 'end_process_import',
					nonce: ajaxNonce,
				},
				'type':     'post',
				'url':      ajaxUrl,
				'success': function(data) {

					if(data.status == 'success'){

						$('#cmd_text').append('<span class="process">Selesai</span>');

						if ( items_errors > 0 ) {
							items_resulttext = items_errors + ' dari '+items_count+' produk gagal diimport';
							$('#cmd_text').append('<span class="result failed">'+items_resulttext+'.</span>');
						} else {
							items_resulttext = 'Semua produk berhasil di import.';
							if(parseInt(items_count) < parseInt(items_total) ){
								items_resulttext = items_count + ' produk berhasil di import.';
							}
							$('#cmd_text').append('<span class="result success">'+items_resulttext+'</span>');
						}

					}
	              	
              	}
			} );

			$('#bi-stop').addClass('hide');
		}

		// Process via AJAX
		function BandrosImport( id ) {
			$.ajax({
				type: 'POST',
				dataType: "json",
				url: ajaxUrl,
				data: { 
					action: "process_import", 
					id: id,
					nonce: ajaxNonce
				},
				success: function( response ) {
					// console.log(response);

					if ( response !== Object( response ) || ( typeof response.status === "undefined" && typeof response.message === "undefined" ) ) {
						response = new Object;
						response.status = 'failed';
						response.message = "Something went wrong";
					}

					if ( response.status == 'success' ) {
						BandrosImportUpdateStatus( id, true, response );
					}
					else {
						BandrosImportUpdateStatus( id, false, response );
					}

					if ( items_to_process.length && items_continue ) {
						BandrosImport( items_to_process.shift() );
					}
					else {
						BandrosImportFinishUp();
					}
				},
				error: function( jqXHR, exception ) {

					console.log(jqXHR);
					
					// get error message
					var msg = '';
			        if (jqXHR.status === 0) {
			            msg = 'Not connect.\n Verify Network.';
			        } else if (jqXHR.status == 404) {
			            msg = 'Requested page not found. [404]';
			        } else if (jqXHR.status == 500) {
			            msg = 'Internal Server Error [500].';
			        } else if (exception === 'parsererror') {
			            msg = 'Requested JSON parse failed.';
			        } else if (exception === 'timeout') {
			            msg = 'Time out error.';
			        } else if (exception === 'abort') {
			            msg = 'Ajax request aborted.';
			        } else {
			            msg = 'Uncaught Error.\n' + jqXHR.responseText;
			        }

			        var response = new Object;
					response.status = 'failed';
					response.message = msg;

					BandrosImportUpdateStatus( id, false, response );

					if ( items_to_process.length && items_continue ) {
						BandrosImport( items_to_process.shift() );
					}
					else {
						BandrosImportFinishUp();
					}
				}
			});
		}

	});



})(jQuery);