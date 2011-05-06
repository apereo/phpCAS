<?php
// Example that changes language of phpcas pages

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
    <?php include 'script_info.php' ?>
    <p>L'utilisateur connect&eacute; est <b><?php echo phpCAS::getUser(); ?></b>.</p>
    <p>La version de phpCAS est <b><?php echo phpCAS::getVersion(); ?></b>.</p>
  </body>
</html>
