<?php

//
// phpCAS simple client with HTML output customization
//

// import phpCAS lib
include_once('CAS.php');

// initialize phpCAS
phpCAS::client(CAS_VERSION_2_0,'sso-cas.univ-rennes1.fr',443,'');

// no SSL validation for the CAS server
phpCAS::setNoCasServerValidation();

// customize HTML output
phpCAS::setHTMLHeader('
<html>
  <head>
    <title>__TITLE__</title>
  </head>
  <body>
  <h1>__TITLE__</h1>
');
phpCAS::setHTMLFooter('
    <hr>
    <address>
      phpCAS __PHPCAS_VERSION__, 
      CAS __CAS_VERSION__ (__SERVER_BASE_URL__)
    </address>
  </body>
</html>
');


// force CAS authentication
phpCAS::forceAuthentication();

// at this step, the user has been authenticated by the CAS server
// and the user's login name can be read with phpCAS::getUser().

// for this test, simply print that the authentication was successfull
?>
<html>
  <head>
    <title>phpCAS simple client with HTML output customization</title>
  </head>
  <body>
    <h1>Successfull Authentication!</h1>
    <p>the user's login is <b><?php echo phpCAS::getUser(); ?></b>.</p>
    <p>phpCAS version is <b><?php echo phpCAS::getVersion(); ?></b>.</p>
  </body>
</html>
