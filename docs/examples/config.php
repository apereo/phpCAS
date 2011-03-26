<?php

// The purpose of this central config file is configuring all examples
// in one place with minimal work for your working environment

$phpcas_path = '../source/';

///////////////////////////////////////
// Basic Config of the phpCAS client //
///////////////////////////////////////

// Full Hostname of your CAS Server
$cas_host = 'cas.example.com';

// Context of the CAS Server
$cas_context = '/cas-server';

// Port of your CAS server. Normally for a https server it's 443
$cas_port = 443;

// Path to the ca chain that issued the cas server certificate
$cas_server_ca_cert_path = '/path/to/cachain.pem';

//////////////////////////////////////////
// Advanced Config for special purposes //
//////////////////////////////////////////

// The "real" hosts of clustered cas server that send SAML logout messages
// Assumes the cas server is load balanced across multiple hosts
$cas_real_hosts = array (
	'cas-real-1.example.com',
	'cas-real-2.example.com'
);

// Generating the URLS for the local cas example services for proxy testing
$curdir = dirname($_SERVER['REQUEST_URI'])."/";
// access to a singe service
$service = $curdir.'/example_session_service.php';
// access to external services
$services = array (
	$curdir.'example_session_service.php',
	$curdir.'/example_proxy2.php'
);

$cas_url = 'https://'.$cas_host;
if ($cas_port != '443')
{
	$cas_url = $cas_url.':'.$cas_port;
}
$cas_url = $cas_url.$cas_context;
?>
