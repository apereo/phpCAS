<?php

//
// phpCAS simple client
//

// import phpCAS lib
include_once('CAS.php');

phpCAS::setDebug();

// initialize phpCAS
phpCAS::client(CAS_VERSION_2_0,'sso-cas.univ-rennes1.fr',443,'');

// no SSL validation for the CAS server
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
