<?php

//
// phpCAS client with custom validation urls
//

// import phpCAS lib
include_once('CAS.php');

phpCAS::setDebug();

// initialize phpCAS
phpCAS::client(CAS_VERSION_2_0,'sso.hrz.tu-darmstadt.de',443,'');
// Override the validation url for any (ST and PT) CAS 2.0 validation
phpCAS::setServerProxyValidateURL('https://sso.hrz.tu-darmstadt.de:1443/proxyValidate');
// Override the validation url for any CAS 1.0 validation
//phpCAS::setServerServiceValidateURL('https://sso.hrz.tu-darmstadt.de:1443/serviceValidate');
//Override the validation url for any SAML11 validation
//phpCAS::setServerSamlValidateURL('https://sso.hrz.tu-darmstadt.de:1443/samlValidate');


// no SSL validation for the CAS server
phpCAS::setNoCasServerValidation();

// force CAS authentication
phpCAS::forceAuthentication();

// at this step, the user has been authenticated by the CAS server
// and the user's login name can be read with phpCAS::getUser().

// logout if desired
if (isset($_REQUEST['logout'])) {
	phpCAS::logout();
}

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
    <p><a href="?logout=">Logout</a></p>
  </body>
</html>
