(function($){

	$.fn.contrastingText = function () {
        var el = this,
            transparent;
        transparent = function (c) {
            var m = c.match(/[0-9]+/g);
            if (m !== null) {
                return !!m[3];
            }
            else return false;
        };
        while (transparent(el.css('background-color'))) {
            el = el.parent();
        }
        parts = el.css('background-color').match(/[0-9]+/g);
        this.lightBackground = !!Math.round(
            (
                parseInt(parts[0], 10) + // red
                parseInt(parts[1], 10) + // green
                parseInt(parts[2], 10) // blue
            ) / 765 // 255 * 3, so that we avg, then normalise to 1
        );
        if (this.lightBackground) {
            // this.css('color', 'black');
            this.addClass('light');
        } else {
            // this.css('color', 'white');
            this.addClass('dark');
        }
        return this;
    };

	$.fn.bandrosLoadStock = function() {

		// traverse all nodes
		this.each(function() {

			// express a single node as a jQuery object
			var _this = $(this),
				kode = _this.attr('data-sku') || false,
				error_message = _this.attr('data-error'),
				use_image = _this.attr('data-use-image'),
				loading = _this.find('.loading'),
				container = _this.find('.stock-data'),
				processing = false;

			if(processing)
				return;

			if(!kode || (typeof(BI_STOCK) == 'undefined') || (typeof(BI_STOCK.ajax_url) == 'undefined' ) ) return;

			// do ajax
			processing = true;
			container.hide();
			loading.show();
			$.ajax( {
				dataType: "json",
				data: {
					action: 'bi_load_stock',
					kode: kode,
					with_image: use_image,
					info_position : 'top',
					nonce: BI_STOCK.nonce
				},
				type:     'post',
				url:      BI_STOCK.ajax_url,
				success: function(res){

					if(res.status == 'success'){
						container.html(res.html).show();
					} else {
						// error
						if($.trim(error_message).length < 1){
							container.html(res.html).show();
						} else {
							container.html(error_message).show();
						}
					}
					
					loading.hide();
              	}
			} );

		});

		// allow jQuery chaining
		return this;
	};

	$('.bandros-load-stock').bandrosLoadStock();

	$('.container-search-stock').each(function(){
		$(this).contrastingText();
	});
	
	/**
	 * Tidy up for small container
	 */
	$('.form-cek-stock').each(function(){
		var container = $(this).closest('.container-search-stock');
		if( container.outerWidth() < 200 ){
			// tell container, we are too narrow
			container.addClass('narrow-wrapper');
			
			// var button = $(this).find('.submit-cek-stock');
			// button.css({
			// 	"width": "auto",
			// 	"padding-left": "10px",
			// 	"padding-right": "10px",
			// });

			// var button_width = button.outerWidth() + 15;
			// $(this).find('label').css({
			// 	"width": "calc(100% - " + button_width + "px)"
			// });
		}
	});

	/**
	 * Check stock
	 * @param  {[type]} e){		e.preventDefault();		var container     [description]
	 * @return {[type]}                                [description]
	 */
	$(document).on('submit', '.form-cek-stock', function(e){
		e.preventDefault();
		var container = $(this).closest('.container-search-stock'),
			kode = $(this).find('.kode-barang').val(),
			error_message = $(this).attr('data-error'),
			button = $(this).find('.submit-cek-stock'),
			container_result = container.find('.result-stock'),
			use_image = $(this).attr('data-use-image'),
			loading = container_result.find('.loading'),
			container_data = container_result.find('.stock-data'),
			processing = false;

		if(processing)
			return;

		if($.trim(kode).length < 1){
			alert( "Silahkan isi kode barang." );
			return;
		}

		// if( container.outerWidth() < 200 ){
		// 	use_image = 'no'; // force to not use image
		// }

		// do ajax
		processing = true;
		container_data.hide();
		loading.show();
		$.ajax( {
			dataType: "json",
			data: {
				action: 'bi_load_stock',
				kode: kode,
				with_image: use_image,
				info_position : 'side',
				nonce: BI_STOCK.nonce
			},
			type:     'post',
			url:      BI_STOCK.ajax_url,
			success: function(res){
				if(res.status == 'success'){
					container_data.html(res.html).show();
				} else {
					// error
					if($.trim(error_message).length < 1){
						container_data.html(res.html).show();
					} else {
						container_data.html(error_message).show();
					}
				}

				processing = false;
				loading.hide();
          	}
		} );

	});

})(jQuery);