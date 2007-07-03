<?php

//
// phpCAS simple client configured with another language
//

// import phpCAS lib
include_once('CAS.php');

// initialize phpCAS
phpCAS::client(CAS_VERSION_2_0,'sso-cas.univ-rennes1.fr',443,'');

// no SSL validation for the CAS server
phpCAS::setNoCasServerValidation();

// set the language to french
phpCAS::setLang(PHPCAS_LANG_FRENCH);

// force CAS authentication
phpCAS::forceAuthentication();

// at this step, the user has been authenticated by the CAS server
// and the user's login name can be read with phpCAS::getUser().

// moreover, a PGT was retrieved from the CAS server that will
// permit to gain accesses to new services.

// for this test, simply print that the authentication was successfull
?>
<html>
  <head>
    <title>Exemple d'internationalisation de phpCAS</title>
  </head>
  <body>
    <h1>Authentification r&eacute;ussie&nbsp;!</h1>
    <p>L'utilisateur connect&eacute; est <b><?php echo phpCAS::getUser(); ?></b>.</p>
    <p>La version de phpCAS est <b><?php echo phpCAS::getVersion(); ?></b>.</p>
  </body>
</html>
