<?php
function RestFulClient( $dataArray ){
  if( !isset( $dataArray['url'] ) ){
		return array(
				'result'=>false,
				'message'=>'require_url'
		);
	}
	$ch = curl_init($dataArray['url']);

	if( isset( $dataArray['id'] , $dataArray['password']) ){
		curl_setopt($ch, CURLOPT_USERPWD, $dataArray['id'].':'.$dataArray['password'] );
	}

	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//	curl_setopt($ch, CURLINFO_HEADER_OUT, 1 );
//	curl_setopt($ch, CURLOPT_HEADER, 1);
	
	curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
	curl_setopt($ch, CURLOPT_AUTOREFERER, true);
	
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Webbot)');
	

	if( isset( $dataArray['method'] ) && strtoupper( $dataArray['method']) != 'GET' ){
		curl_setopt($ch, CURLOPT_POSTFIELDS, $dataArray['request_body'] );
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper( $dataArray['method']) );
	}else{
		if( isset( $dataArray['request_body'] ) ){
			curl_setopt($ch, CURLOPT_URL , $dataArray['url'].'?'.$dataArray['request_body']);
		}else{
			curl_setopt($ch, CURLOPT_URL , $dataArray['url']);
		}
	}

	$response = curl_exec($ch);
	
	if(!curl_errno($ch)){
		$info = curl_getinfo($ch);
		
		return array(
				'result'=>true,
				'http_code'=>$info['http_code'],
				'responseInfo'=>$info,
				'response'=>$response,
		);
	}else{
		return array(
				'result'=>false,
				'message'=>'Curl error: '.curl_error($ch)
		);
	}
}

$dataArray = array(
		'url'=>'http://112.175.230.26/server.php?t=a', // Require
		'id'=>'userid', // Optional
		'password'=>'password', // Optional
		'method'=>'JSON',// Optional - Default : GET
		'request_body'=> // Optional
		array(
				'tmpname1'=>'Foo1',
				'tmpname2'=>'Foo2',
				'tmpname3'=>'Foo3',
				'filname1'=>'@/home/redcubed/gakting/p.php',
				'filname2'=>'@/home/redcubed/gakting/1.png',
		)
);

// $dataArray = array(
// 		'url'=>'http://google.com/', // Require
// );

$result = RestFulClient( $dataArray );
print_r( $result['response'] );
?>
