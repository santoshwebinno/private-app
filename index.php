<?php
	// Shop details
	$shop = 'test-sample-app.myshopify.com';
    $api = '122aee1f442c4dfd0e6d68cbebeb4ac1';
    $access_token = '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';
	//
	function curlRequest($url, $method,$products_arrays,$access_token){
		error_log($url);
		error_log("work1");
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('X-Shopify-Access-Token: '.$access_token));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_VERBOSE, 0);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
		//curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($products_arrays));
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_TIMEOUT, 300);
		$response = curl_exec($curl);
		curl_close($curl);
		//error_log($response);
		return $response; 
	}
	function curlRequestPost($url, $method,$products_arrays,$access_token){
		error_log($url);
		error_log("work2");
		//sleep(5);
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('X-Shopify-Access-Token: '.$access_token));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_VERBOSE, 0);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($products_arrays));
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_TIMEOUT, 300);
		$response = curl_exec($curl);
		curl_close($curl);
		error_log($response);
		return $response; 
		
	}
	// count all product
	
$headers = array(
    "Content-Type"=> "application/graphql",
    "X-Shopify-Storefront-Access-Token"=> $access_token
);
 $query='{ shop { products(first: 3) {  pageInfo { hasNextPage,hasPreviousPage
                 }, edges { cursor, node {id, variants(first: 100){ edges { node { id, sku, inventoryItem{id } }}} }}}}}';
               $req = $request->get('https://'.$shop.'.myshopify.com/api/graphql',  $query, $headers);
//                 print_r($req);
//                 die; 
	$urls_count = 'https://' . $api . ':' . $access_token . '@' . $shop . '/admin/api/2020-01/products/count.json';
	$product_count = curlRequest($urls_count, 'GET','',$access_token);
	sleep(5);
	flush();
	$product_count = json_decode($product_count);
	//echo "<pre>";
	//print_r($product_count);
	if (isset($product_count->count)){
		$count = $product_count->count;
	}else{
		$count = 0;
	}
	//echo "die";
	error_log($count);
	// Search all variants from shop
	if ($count > 0){
		$page = ceil((int)$count / 250);
		$since_id = 0;
		for ($i=1; $i<=$page; $i++) {
			if($i ==1){
				sleep(5);
				flush();
				$urls = 'https://' . $api . ':' . $access_token . '@' . $shop . '/admin/api/2020-01/products.json?fields=id,variants&limit=250&since_id='.$since_id;

				//$response_variant = curlRequest($urls, 'GET','',$access_token);
			}else{
				sleep(5);
				flush();
				$urls  = 'https://' . $api . ':' . $access_token . '@' . $shop . '/admin/api/2020-01/products.json?fields=id,variants&limit=250&since_id='.$since_id;
				$response_variant = curlRequest($urls, 'GET','',$access_token);
			}
			$response_variant = json_decode($response_variant);
			//Get csv file 
			$file = fopen('inventory.csv', "r");
			$key =0;
			while (($column = fgetcsv($file)) !== FALSE) {
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
					//Compare shopify and csv file sku
					if (isset($response_variant->products)){
						$response_variants = $response_variant->products;
					}else{
						$response_variants =array();
					}
					foreach($response_variants as $keys => $products){
						$since_id = $products->id;
						foreach($products->variants as $keys => $variant){
							if($variant->sku == $sku){
								error_log($products->id);
								// Update Quantity
								sleep(5);
								flush();
								$url_inv ='https://' . $api . ':' . $access_token . '@' . $shop . '/admin/api/2020-01/inventory_levels.json?inventory_item_ids='.$variant->inventory_item_id;
								$inventry_Qantity =curlRequest($url_inv, 'GET','',$access_token);
								
								$inventry_Qantity = json_decode($inventry_Qantity);
								$inventry_level = $inventry_Qantity->inventory_levels[0];
								//echo $inventry_level->location_id;
								$update_url = 'https://' . $api . ':' . $access_token . '@' . $shop . '/admin/api/2020-07/inventory_levels/set.json';
								$quantitys =  array('location_id' => $inventry_level->location_id,'inventory_item_id' => $inventry_level->inventory_item_id, 'available' =>$qantity);
								//print_r($quantitys);
								sleep(5);
								flush();
								$update_inventry_Qantity =curlRequestPost($update_url, 'POST', $quantitys,$access_token);
								
								//Update Price 
								$products_array = array('variant' => array('id' => $variant->id,'price' => $price));
								$url = 'https://' . $api . ':' . $access_token . '@' . $shop . '/admin/api/2020-01/variants/'.$variant->id.'.json';
								/*$update_price =curlRequest($url, 'POST', $products_array);
								print_r($update_price);
								echo "<br>";*/
								sleep(5);
								flush();
								$curl = curl_init();
								curl_setopt($curl, CURLOPT_URL, $url);
								curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));
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
		}
	}	
?>