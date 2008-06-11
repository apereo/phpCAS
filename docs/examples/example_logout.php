<?php

//
// phpCAS client that handles central logout requests from the CAs server
//

// import phpCAS lib
include_once('CAS.php');

phpCAS::setDebug();

// initialize phpCAS
phpCAS::client(CAS_VERSION_2_0,'sso-cas.univ-rennes1.fr',443,'');

// no SSL validation for the CAS server
phpCAS::setNoCasServerValidation();

// handle incoming logout requests
phpCAS::handleLogoutRequests();

// force CAS authentication
phpCAS::forceAuthentication();

// for this test, simply print that the authentication was successfull
?>
<html>
  <head>
    <title>phpCAS simple client</title>
  </head>
  <body>
    <h1>Successfull Authentication!</h1>
    <p>the user's login is <b><?php echo phpCAS::getUser(); ?></b>.</p>
    <p>phpCAS version is <b><?php echo phpCAS::getVersion(); ?></b>.</p>
  </body>
</html>
