<?php
 
# PHP SSL Fingerprint Checker written by W. Al Maawali  
# (c) 2013 Founder of Eagle Eye Digital Solutions
# http://www.digi77.com
# http://www.om77.net
# script starts here:
# Usage: http://www.yourdomain.com/sslf.php
# Example: http://www.digi77.com/software/fingerprint/fp-public.php?hosts=www.facebook.com
 
//avoid timeouts
set_time_limit(0);
 
//For String variable use prevent sql injections
function StringInputCleaner($data)
{
	$data = trim($data); 
	$data = stripslashes($data); 
	$data=(filter_var($data, FILTER_SANITIZE_STRING));
	return $data;
}	
 
function getSllCertificate($hostname, $port = 443)
{
	$context = stream_context_create(array("ssl" => array("capture_peer_cert" => true)));
	$socket = @stream_socket_client("ssl://$hostname:$port", $errno, $errstr, ini_get("default_socket_timeout"), STREAM_CLIENT_CONNECT, $context);
 
	if(!$socket)
		return array("md5" => "error", "sha1" => "error");
 
	$contextdata = stream_context_get_params($socket);
	$contextparams = $contextdata['options']['ssl']['peer_certificate'];
 
	fclose($socket);
 
	openssl_x509_export($contextparams, $cert, true);
	openssl_x509_free($contextparams);
 
	$repl = array("\r", "\n", "-----BEGIN CERTIFICATE-----", "-----END CERTIFICATE-----");
	$repw = array("", "", "", "");
 
	$cert = str_replace($repl, $repw, $cert);
 
	$decoded = base64_decode($cert);
	$fingerprints = array(
		"md5" => md5($decoded),
		"sha1" => sha1($decoded),
	);
	
	
	return $fingerprints ;
}
 
$host=$_REQUEST['hosts'];
//clean string safer coding
$host=StringInputCleaner($host);
$port=443;
$hashes = getSllCertificate($host, $port);
 
$hash = "";
$hash_split = str_split($hashes['sha1'], 2);

$first_h = true;
for ($h = 0; $h <= count($hash_split); $h++) {
	if($first_h) {
		$hash .= $hash_split[$h];
		$first_h = false;
	} else {
		$hash .= " ".$hash_split[$h];
	}
}

print_r(trim(strtoupper($hash))); 
 
?>