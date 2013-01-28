<?php
$tmpfile = array();
function RESTfulServer(){
  global $tmpfile;
	$requestData = array();
	
	$requestData['method'] = strtoupper( $_SERVER['REQUEST_METHOD'] );
	
	if( isset( $_SERVER['PHP_AUTH_USER'] )){
		$requestData['ID'] = $_SERVER['PHP_AUTH_USER'];
	}
	
	if( isset( $_SERVER['PHP_AUTH_PW'] )){
		$requestData['PW'] = $_SERVER['PHP_AUTH_PW'];
	}
	
	$requestData['GET'] = $_GET;
	
	switch( $requestData['method'] ){
		case 'GET':
			break;
		case 'POST':
			$requestData['FILES'] = $_FILES;
			$requestData['POST'] = $_POST;
			break;
		default:
			$input = file_get_contents('php://input');
			
			preg_match('/boundary=(.*)$/', $_SERVER['CONTENT_TYPE'], $matches);
			$boundary = $matches[1];
			
			$a_blocks = preg_split("/-+$boundary/", $input);
			array_pop($a_blocks);
			
			$a_data = array();
			
			foreach ($a_blocks as $id => $block)
			{
				if (empty($block))
					continue;
				
				if (strpos($block, 'application/octet-stream') !== FALSE)
				{
					$tmp = $block;
					$blockData = explode( "\n" ,  $tmp );
					$blockData = explode( ";" , $blockData[1] );
					
					$name = explode( "=" , $blockData[ 1 ] ); 
					$name = trim( $name[ 1 ] , " \"" );
					
					$filename = explode( "=" , $blockData[ 2 ] );
					$filename = explode( "/" , trim( str_replace( "\"" , "" , $filename[ 1 ] )));
					$filename = array_pop( $filename );
					
					preg_match("/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s", $block, $matches);
					
					$matches[2] = substr( $matches[2] , 0 , -2 );
					
					$tmpname = "/tmp/".substr( dechex( time() ) , 5 ).uniqid();
					file_put_contents( $tmpname , $matches[2] );
						
					$requestData['FILES'][ $name ] = array(
							'name'=>$filename,
							'tmp_name'=>$tmpname,
							'size'=>strlen( $matches[2] ),
					);
					$tmpfile[] = $tmpname;
				}
				else
				{
					preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $block, $matches);
					$a_data[$matches[1]] = $matches[2];
				}
			}
			
			$requestData[ $requestData['method'] ] = $a_data;
				
				
			break;
	}
	
	return $requestData; 
}

function ClearTmpFile(){
	global $tmpfile;
	
	for( $i = 0 ; $i < count( $tmpfile ) ; $i++ ){
		unlink( $tmpfile[ $i ]);
	}
}

$request = RESTfulServer();
print_r( $request );

ClearTmpFile();
?>
