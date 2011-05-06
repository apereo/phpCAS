<?php
// Example that uses the CAS gateway feature

// Load the settings from the central config file
include_once('config.php');
// Load the CAS lib
include_once($phpcas_path.'/CAS.php');

// Uncomment to enable debugging
phpCAS::setDebug();

// Initialize phpCAS
phpCAS::client(CAS_VERSION_2_0, $cas_host, $cas_port, $cas_context);

// For production use set the CA certificate that is the issuer of the cert 
// on the CAS server and uncomment the line below
// phpCAS::setCasServerCACert($cas_server_ca_cert_path);

// For quick testing you can disable SSL validation of the CAS server. 
// THIS SETTING IS NOT RECOMMENDED FOR PRODUCTION. 
// VALIDATING THE CAS SERVER IS CRUCIAL TO THE SECURITY OF THE CAS PROTOCOL! 
phpCAS::setNoCasServerValidation();

if (isset($_REQUEST['logout'])) {
  phpCAS::logout();
}
if (isset($_REQUEST['login'])) {
  phpCAS::forceAuthentication();
}

// check CAS authentication
$auth = phpCAS::checkAuthentication();

?>
<html>
  <head>
    <title>phpCAS simple client</title>
  </head>
  <body>
<?php
if ($auth) {
  // for this test, simply print that the authentication was successfull
?>
    <h1>Successfull Authentication!</h1>
    <?php include 'script_info.php' ?>
    <p>the user's login is <b><?php echo phpCAS::getUser(); ?></b>.</p>
    <p><a href="?logout=">Logout</a></p>
<?php
} else {
?>
    <h1>Guest mode</h1>
    <p><a href="?login=">Login</a></p>
<?php
}
?>
    <p>phpCAS version is <b><?php echo phpCAS::getVersion(); ?></b>.</p>
  </body>
</html>
