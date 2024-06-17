#!/usr/bin/php
<?php
define("__DEBUG__", false);
//define("__DEBUG__", true);

// set this to the name of the machine MPD/SnapServer are running on if not localhost
define("_MPD_HOST_", "127.0.0.1" );

// set this to the name of your alarm clock machine
// note that a hostname is required, you can't use localhost or 127.0.0.1
define("_ALARM_CLOCK_", "AlarmClock" );

// list the machines you want to mute, all others will be unmuted
$mutables = array( _ALARM_CLOCK_ /*, "another_machine" */ );

function debug($t) {
	if ( __DEBUG__ ){
		echo "$t\n";
	}
}

function sendMessage( $url, $data = "" ){
	$curl_handle=curl_init();
	curl_setopt($curl_handle, CURLOPT_CAINFO, "cacert.pem");
	curl_setopt($curl_handle, CURLOPT_URL, $url);
	curl_setopt($curl_handle, CURLOPT_TIMEOUT, 120);
	curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, TRUE);
	if( $data != "" ){
		curl_setopt($curl_handle, CURLOPT_HTTPHEADER,
		    array(
		        'Content-Type:application/json',
		        'Content-Length: ' . strlen($data)
		    )
		);
		curl_setopt($curl_handle, CURLOPT_POSTFIELDS, "$data");
	}
	$reply = curl_exec($curl_handle);
	if (!$reply) {
    	die('Error: "' . curl_error($curl_handle) . '" - Code: ' . curl_errno($curl_handle));
	}
	curl_close($curl_handle);
	return $reply;
}

function nextID(){
	global $mid;
	$mid++;
	return $mid;
}

$host = "romeo";
$port = "1780";
$rpcgate = "jsonrpc";
//$tstamp = time()-5; // just to make sure we do't delete anything we shouldn't
$mid = 0;

debug("Host: ".gethostname());

$url = "http://$host:$port/$rpcgate";

$data = json_encode( array(
			"id" => nextID(),
			"jsonrpc" => "2.0",
			"method" => "Server.GetStatus"
		));


$response = json_decode( sendMessage( $url, $data ), true );

$r = $response["result"]["server"]["groups"];

foreach( $r as $o => $d ) {
	debug("$o");
	debug(json_encode($d) );
	$id 	= $d["clients"][0]["id"];
//	$t 		= $d["clients"][0]["lastSeen"]["sec"];
	$host 	= $d["clients"][0]["host"]["name"];
	$pc 	= 100;

//	debug("$t, $id, $host");

	$mutable = in_array( $host, $mutables );

	$data = json_encode( array( 
		"id" => nextID(),
		"jsonrpc" => "2.0",
		"method" => "Client.SetVolume",
		"params" => array(
						"id" => $id,
						"volume" => array(
										"muted" => $mutable,
										"percent" => $pc
									)
					)
		));
	sendMessage($url, $data);

}

?>
