#!/usr/bin/php
<?php
define("__DEBUG__", false);

// set this to the name of the machine MPD/SnapServer are running on if not localhost
define("_MPD_HOST_", "127.0.0.1" );

// set this to the name of your alarm clock machine
// note that a hostname is required, you can't use localhost or 127.0.0.1
define("_ALARM_CLOCK_", "AlarmClock" );

define("__ALARM_PLAYLIST__", "Alarm" );

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

$host = "127.0.0.1";
$port = "1780";
$tcp_port = "6600";
$rpcgate = "jsonrpc";
$tstamp = time()-5; // just to make sure we don't delete anything we shouldn't
$mid = 0;

debug("Host: ".gethostname());

if( strtoupper(gethostname()) != _MPD_HOST_ ){
	$host = _MPD_HOST_;
	debug("Host: $host");
}

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
	$t 		= $d["clients"][0]["lastSeen"]["sec"];
	$host 	= $d["clients"][0]["host"]["name"];
	$pc 	= 100;

	debug("$t, $id, $host");

	// delete any streams no longer active (muted streams are still active)
	if( $t < $tstamp ){
		debug("deleting $host/$xid");
		// old stream, no longer used (it's easy enough to create the stream again if we need it)
		$data = json_encode( array(
					"id" => nextID(),
					"jsonrpc" => "2.0",
					"method" => "Server.DeleteClient",
					"params" => array(
									"id" => $id
								)
				));
		debug($data);
		sendMessage($url, $data);
	}
	// mute any stream that isn't the alarm clock
	$data = json_encode( array( 
		"id" => nextID(),
		"jsonrpc" => "2.0",
		"method" => "Client.SetVolume",
		"params" => array(
						"id" => $id,
						"volume" => array(
										"muted" => ($host != _ALARM_CLOCK_),
										"percent" => $pc
									)
					)
		));
	sendMessage($url, $data);


}

//debug( json_encode( $r ));

//** MPD Direct Comms **//
$port = "6600";
$url = "tcp://$host:$port";
$command = "command_list_begin\r\n"
		. "clear\r\n"
		. "load ".__ALARM_PLAYLIST__."\r\n"
		. "play\r\n"
		. "command_list_end\r\n";

debug($command);

//echo sendMessage( $url, $command );
$h = fsockopen( $host, $port, $errno, $errstr, 3 );
if (!$h) {
    debug("Error: $errstr ($errno)<br />\n");
}
else {
	$v = fgets($h, 128);
	debug("read #1:\n".$v);
	fwrite( $h, $command );

	// we don't actually care about the success/failure response
	// it either worked or it didn't. If it didn't, we probably won't wake up
    $v = fgets($h, 128);
	debug("read #2:\n".$v);
    fclose($h);
}

?>
