<?php

/**
 *  Example that changes language of phpcas pages
 *
 * PHP Version 5
 *
 * @file     example_lang.php
 * @category Authentication
 * @author   Joachim Fritschi <jfritschi@freenet.de>
 * @author   Adam Franco <afranco@middlebury.edu>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 */

// Load the autoloader
require_once '../../vendor/autoload.php';

// Load the settings from the central config file
require_once 'config.php';

use phpCAS\CAS;

// Enable debugging
CAS::setDebug();
// Enable verbose error messages. Disable in production!
CAS::setVerbose(true);

// Initialize phpCAS
CAS::client(CAS::CAS_VERSION_2_0, $cas_host, $cas_port, $cas_context);

// For production use set the CA certificate that is the issuer of the cert
// on the CAS server and uncomment the line below
// CAS::setCasServerCACert($cas_server_ca_cert_path);

// For quick testing you can disable SSL validation of the CAS server.
// THIS SETTING IS NOT RECOMMENDED FOR PRODUCTION.
// VALIDATING THE CAS SERVER IS CRUCIAL TO THE SECURITY OF THE CAS PROTOCOL!
CAS::setNoCasServerValidation();

// set the language to french
CAS::setLang(CAS::PHPCAS_LANG_FRENCH);

// force CAS authentication
CAS::forceAuthentication();

// at this step, the user has been authenticated by the CAS server
// and the user's login name can be read with CAS::getUser().

// moreover, a PGT was retrieved from the CAS server that will
// permit to gain accesses to new services.

// for this test, simply print that the authentication was successful
?>
<html>
  <head>
    <title>Exemple d'internationalisation de phpCAS</title>
  </head>
  <body>
    <h1>Authentification r&eacute;ussie&nbsp;!</h1>
    <?php require 'script_info.php' ?>
    <p>L'utilisateur connect&eacute; est <b><?php echo CAS::getUser(); ?></b>.</p>
    <p>La version de phpCAS est <b><?php echo CAS::getVersion(); ?></b>.</p>
  </body>
</html>
