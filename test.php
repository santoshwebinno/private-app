<?php
/*
define('SHOPIFY_APP_SECRET', '3981821ba9728aa931924f03f4542b8e783cd5750665afe912122f068d5b5b3d');
function verify_webhook($data, $hmac_header){
  $calculated_hmac = base64_encode(hash_hmac('sha256', $data, SHOPIFY_APP_SECRET, true));
  return hash_equals($hmac_header, $calculated_hmac);
}

$hmac_header = $_SERVER['HTTP_X_SHOPIFY_HMAC_SHA256'];
$data = file_get_contents('php://input');
$verified = verify_webhook($data, $hmac_header);
error_log('Webhook verified: '.var_export($verified, true)); //check error.log to see the result

echo file_put_contents("test.txt",json_encode($data));


        $shop = 'test-sample-app.myshopify.com';
        $api = '122aee1f442c4dfd0e6d68cbebeb4ac1';
        $access_token = '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';
        /*print_r(json_encode($shop));
        exit();*/
			/*	$args = ["first: 20"];
        if (!empty($cursor)) {
            $args[] = "after: \"$cursor\"";
        }
$graphQL =  'query {
  productVariants(first: 1, query: "AD-03-black-OS") {
    edges {
      node {
        id
      }
    }
  }
}'; */
	// Shop details
	$shop = 'test-sample-app.myshopify.com';
    $api = '122aee1f442c4dfd0e6d68cbebeb4ac1';
    $access_token = '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';
	//
	function curlRequest($url, $method,$products_arrays,$access_token){
		//error_log($url);
		//error_log("work1");
		echo $url;
		echo "<br>";
		//echo $method;
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8', 'Content-Length: ' . strlen($products_arrays)));
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('X-Shopify-Access-Token: '.$access_token));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_VERBOSE, 0);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
		//curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($products_arrays));
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		$response = curl_exec($curl);
		curl_close($curl);
		return $response; 
		//print_r($response);
	}
	function curlRequestPost($url, $method,$products_arrays,$access_token){
		//error_log($url);
		//error_log("work2");
		//echo $url;
		//echo "<br>";
		//echo $method;
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8', 'Content-Length: ' . strlen($products_arrays)));
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('X-Shopify-Access-Token: '.$access_token));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_VERBOSE, 0);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($products_arrays));
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		$response = curl_exec($curl);
		curl_close($curl);
		return $response; 
		//print_r($response);
	}
	//count
	/*$urls = 'https://' . $api . ':' . $access_token . '@' . $shop . '/admin/api/2020-01/products.json?fields=id,variants&limit=5&since_id=0';
	$response_variant = curlRequest($urls, 'GET','',$access_token);
	$response_variant = json_decode($response_variant);
	echo "<pre>";
	print_r($response_variant);
	die;*/
	//
	$urls_count = 'https://' . $api . ':' . $access_token . '@' . $shop . '/admin/api/2020-01/products/count.json';
	$product_count = curlRequest($urls_count, 'GET','',$access_token);
	sleep(2);
	$product_count = json_decode($product_count);
	echo "<pre>";
	print_r($product_count);
	if (isset($product_count->count)){
	$count = $product_count->count;
	//echo "die";
	error_log($count); 
	if ($count > 0){
		$page = ceil((int)$count / 5);
		$since_id = 0;
		for ($i=1; $i<=$page; $i++) {
			echo $i;
			echo "<br>";
			if($i ==1){
				usleep(20000);
				$urls = 'https://' . $api . ':' . $access_token . '@' . $shop . '/admin/api/2020-01/products.json?fields=id,variants&limit=5&since_id='.$since_id;
				$response_variant = curlRequest($urls, 'GET','',$access_token);
			}else{
				usleep(20000);
				$urls  = 'https://' . $api . ':' . $access_token . '@' . $shop . '/admin/api/2020-01/products.json?fields=id,variants&limit=5&since_id='.$since_id;
				$response_variant = curlRequest($urls, 'GET','',$access_token);
			}
			echo "<pre>"; error_log($response_variant); echo "<pre>";
			$response_variant = json_decode($response_variant);
			
			foreach($response_variant->products as $keys => $products){
				$since_id = $products->id;
				//echo $since_id;
			}
		}
	}
	
	}
	die;
	// Search all variants from shop
	$urls = 'https://' . $api . ':' . $access_token . '@' . $shop . '/admin/api/2020-01/products.json?fields=variants&limit=250';
	$response_variant = curlRequest($urls, 'GET','',$access_token);
	
	$file = fopen('inventory.csv', "r");
	
	$key =0;
	$response_variant = json_decode($response_variant);
	
	while (($column = fgetcsv($file)) !== FALSE) {
		
	//echo "<br>";
		if($key >0){
			$Id = "";
			if (isset($column[0])) {
		   // echo    $Id = trim($column[0]);
			}
			$sku = "";
			if (isset($column[1])) {
				$sku = $column[1];
			}
			$price = "";
			if (isset($column[2])) {
				$price = $column[2];
			}
			$qantity = "";
			if (isset($column[3])) {
				$qantity = $column[3];
			}
			//echo $sku;
			foreach($response_variant->products as $keys => $products){
				foreach($products->variants as $keys => $variant){
					if($variant->sku == $sku){
						// Update Quantity
						$url_inv ='https://' . $api . ':' . $access_token . '@' . $shop . '/admin/api/2020-01/inventory_levels.json?inventory_item_ids='.$variant->inventory_item_id;
						$inventry_Qantity =curlRequest($url_inv, 'GET','',$access_token);
						
						$inventry_Qantity = json_decode($inventry_Qantity);
						$inventry_level = $inventry_Qantity->inventory_levels[0];
						//echo $inventry_level->location_id;
						$update_url = 'https://' . $api . ':' . $access_token . '@' . $shop . '/admin/api/2020-07/inventory_levels/set.json';
						$quantitys =  array('location_id' => $inventry_level->location_id,'inventory_item_id' => $inventry_level->inventory_item_id, 'available' =>$qantity);
						//print_r($quantitys);
						$update_inventry_Qantity =curlRequestPost($update_url, 'POST', $quantitys,$access_token);
						print_r($update_inventry_Qantity);
						echo "<br>";
						//
						//Update Price 
						$products_array = array('variant' => array('id' => $variant->id,'price' => $price));
						$url = 'https://' . $api . ':' . $access_token . '@' . $shop . '/admin/api/2020-01/variants/'.$variant->id.'.json';
						/*$update_price =curlRequest($url, 'POST', $products_array);
						print_r($update_price);
						echo "<br>";*/
						$curl = curl_init();
						curl_setopt($curl, CURLOPT_URL, $url);
						curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8', 'Content-Length: ' . strlen($products_array)));
						curl_setopt($curl, CURLOPT_HTTPHEADER, array('X-Shopify-Access-Token: '.$access_token));
						curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
						curl_setopt($curl, CURLOPT_VERBOSE, 0);
						curl_setopt($curl, CURLOPT_HEADER, false);
						curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
						curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($products_array));
						curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
						$response = curl_exec($curl);
						curl_close($curl);
						//print_r($response);
					}
				}
			}
		}
		$key++;	
	}
	
	?>