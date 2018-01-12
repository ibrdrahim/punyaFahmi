<?php
  if ( ! defined( 'ABSPATH' ) ) exit;
  function epeken_get_list_of_kota_kabupaten ()
	{
		$kotakabreturn = array();
		$string = file_get_contents(EPEKEN_KOTA_KAB);
		$json = json_decode($string,true);
		$array_kota = $json['listkotakabupaten'];
		$kotakabreturn [''] = 'Pilih Kota/Kabupaten';
		foreach($array_kota as $element){
			$kotakabreturn[$element['kotakab']] = $element['kotakab'];	
		}
		return $kotakabreturn;
	}


  function epeken_get_all_provinces() {
		$license_key = get_option('epeken_wcjne_license_key');
		$url = EPEKEN_API_GET_PRV.$license_key;
		$ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $content = curl_exec($ch);
                curl_close($ch);
		return $content;
   }

  function epeken_get_track_info($kurir,$awb) {
        $license = get_option('epeken_wcjne_license_key');    
        $ch = curl_init();
        $endpoint = EPEKEN_TRACKING_END_POINT.$license.'/'.$kurir.'/'.$awb;
        curl_setopt($ch, CURLOPT_URL, $endpoint);
                  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                  $content = curl_exec($ch);
                  curl_close($ch);
                  return $content;
   }   
  
  function epeken_get_list_of_kecamatan ($kotakab)
	{
		$kotakab = sanitize_text_field(trim($kotakab));
		$kecamatanreturn = array();
		 if ($kotakab === 'init'){
                  $kecamatanreturn [''] = 'Please Select Kecamatan';
                  return $kecamatanreturn;
                }

		$string = file_get_contents(EPEKEN_KOTA_KEC);
		$json = json_decode($string, true);
		$array_kecamatan = $json['listkecamatan'];
		$kecamatanreturn[''] = 'Pilih Kecamatan';
		foreach($array_kecamatan as $element){
			if ($element["kota_kabupaten"] === $kotakab) {
				$kecamatanreturn [$element["kecamatan"]] = $element["kecamatan"];
			}	
		}
		return $kecamatanreturn;
	}

	function writelog($logstr){
                        $logdir = plugin_dir_path( __FILE__ )."log/";
                        $sesid = session_id();
                        $logfile = fopen ($logdir."debug.log","a");
                        $now = date("Y-m-d H:i:s");
                        fwrite($logfile,$now.":".$logstr."\n");
	                        fclose($logfile);
                }

  function epeken_code_to_city($code) {
		$string = file_get_contents(EPEKEN_KOTA_KAB);
		$city = "";
		$json = json_decode($string,true);
                $array_kota = $json['listkotakabupaten'];
		foreach($array_kota as $element){
                        if($element['code'] === $code){
                                $city = $element["kotakab"];
                                break;
                        }
                }
		return $city;
  }

  function epeken_city_to_code($city) {
		$string = file_get_contents(EPEKEN_KOTA_KAB);
                $code = "";
                $json = json_decode($string,true);
                $array_kota = $json['listkotakabupaten'];
                foreach($array_kota as $element){
                        if($element['kotakab'] === $city){
                                $code = $element["code"];
                                break;
                        }
                }
                return $code;
  }

  function epeken_get_tarif($kotakab, $kecamatan, $product_origin = false) {		
		$license_key = get_option('epeken_wcjne_license_key');
		$options = get_option('woocommerce_epeken_courier_settings');
                $origin_code = $options['data_kota_asal'];
		$destination_code = "";
		$string = file_get_contents(EPEKEN_KOTA_KAB);
                $json = json_decode($string,true);
		$array_kota = $json['listkotakabupaten'];
                foreach($array_kota as $element){
			if($element['kotakab'] === $kotakab){
				$destination_code = $element["code"];
				break;
			}
                }
		$content = "";	
		
		if ($product_origin != false)
			$origin_code = epeken_city_to_code($product_origin);
	
		if ($destination_code !=="") {	
		  	$url = EPEKEN_API_DIR_URL.$license_key."/".$origin_code."/".$destination_code."/".urlencode($kotakab)."/".urlencode($kecamatan);
			$ch = curl_init();
	 		curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  		
			$content = curl_exec($ch);
			//writelog($url."\n".$content);
  	 		curl_close($ch);
		}
		return $content;
	}

 function epeken_get_valid_origin($license) {
		$content = "";
		$url = EPEKEN_VALID_ORIGIN.$license;
		$ch = curl_init();
		 curl_setopt($ch, CURLOPT_URL, $url);
                 curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                 $content = curl_exec($ch);
                 curl_close($ch);
		 return $content;
  }

  function epeken_get_tarif_pt_pos_v2($kotakab,$weight, $price, $length, $width, $height, $product_origin=false ){
		//weight is in gram	
		$license_key = get_option('epeken_wcjne_license_key');
                $options = get_option('woocommerce_epeken_courier_settings');
                $origin_code = $options['data_kota_asal'];
                $destination_code = "";
                $string = file_get_contents(EPEKEN_KOTA_KAB);
                $json = json_decode($string,true);
                $array_kota = $json['listkotakabupaten'];
                foreach($array_kota as $element){
                        if($element['kotakab'] === $kotakab){
                                $destination_code = $element["code"];
                                break;
                        }
                }
                $content = "";
                if ($product_origin != false)
                        $origin_code = epeken_city_to_code($product_origin);
		if ($destination_code !=="") {
                        $url = EPEKEN_API_POS_URL_V2.$license_key."/".$origin_code."/".$destination_code."/".$weight."/".$price."/".$length."/".$width."/".$height;
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $content = curl_exec($ch);
                        //writelog($url."\n".$content);
                        curl_close($ch);
                }
                return $content;
  }

  function epeken_get_wahana_ongkir($kotakab, $kecamatan, $weight, $product_origin=false) {
					      //weight in kg
		if (empty($weight)) 
			$weight = 1;

		if ($weight < 1)
			$weight = 1;

		$license_key = get_option('epeken_wcjne_license_key');
		$options = get_option('woocommerce_epeken_courier_settings');
		$origin_code = $options['data_kota_asal'];	
		$origin_city = epeken_code_to_city($origin_code);
		if ($product_origin != false)
			$origin_city = $product_origin;
		
		if(empty($weight) || $weight < 1)
			$weight = 1;

		$origin_city = urlencode($origin_city);
		$kotakab = urlencode ($kotakab);
		$weight = urlencode($weight);

		$url = EPEKEN_API_WAHANA.$license_key."/".$origin_city."/".$kotakab."/".$kecamatan."/".$weight;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$content = curl_exec($ch);
		curl_close($ch);
		return $content;
	}

   function epeken_get_jet_ongkir($kotakab, $kecamatan, $weight, $product_origin=false) {
                                              //weight in kg
                if (empty($weight)) 
                        $weight = 1;

                if ($weight < 1)
                        $weight = 1;

                $license_key = get_option('epeken_wcjne_license_key');
                $options = get_option('woocommerce_epeken_courier_settings');
                $origin_code = $options['data_kota_asal'];    
                $origin_city = epeken_code_to_city($origin_code);
		
                if ($product_origin != false)
                        $origin_city = $product_origin;
    
                if(empty($weight) || $weight < 1)
                        $weight = 1;

                $origin_city = urlencode($origin_city);
                $kotakab = urlencode ($kotakab);
                $weight = urlencode($weight);
		$kecamatan = urlencode($kecamatan);

                $url = EPEKEN_API_JET.$license_key."/".$origin_city."/".$kotakab."/".$kecamatan."/".$weight;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $content = curl_exec($ch);
                curl_close($ch);
                return $content;
        } 

   function epeken_get_sicepat_ongkir($kotakab, $kecamatan, $weight, $product_origin=false) {
                                              //weight in kg
                if (empty($weight)) 
                        $weight = 1;

                if ($weight < 1)
                        $weight = 1;

                $license_key = get_option('epeken_wcjne_license_key');
                $options = get_option('woocommerce_epeken_courier_settings');
                $origin_code = $options['data_kota_asal'];    
                $origin_city = epeken_code_to_city($origin_code);
    
                if ($product_origin != false)
                        $origin_city = $product_origin;
    
                if(empty($weight) || $weight < 1)
                        $weight = 1;

                $origin_city = urlencode($origin_city);
                $kotakab = urlencode ($kotakab);
                $weight = urlencode($weight);
                $kecamatan = urlencode($kecamatan);

                $url = EPEKEN_API_SICEPAT.$license_key."/".$origin_city."/".$kotakab."/".$kecamatan."/".$weight;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $content = curl_exec($ch);
                curl_close($ch);
                return $content;
        }   


   function epeken_get_currency_rate($currency_name) {
		$license_key = get_option('epeken_wcjne_license_key');
		$url = EPEKEN_API_GET_CURRENCY_RATE.$license_key."/".$currency_name;
		$ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $content = curl_exec($ch);
                curl_close($ch);
                return $content;		
	}
 
?>
