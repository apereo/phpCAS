<?php

/**
 *  Example that changes the storage of the pgt tickets.
 *
 * PHP Version 5
 *
 * @file     example_pgt_storage_db.php
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

// Initialize CAS
CAS::proxy(CAS::CAS_VERSION_2_0, $cas_host, $cas_port, $cas_context);

// For production use set the CA certificate that is the issuer of the cert
// on the CAS server and uncomment the line below
// CAS::setCasServerCACert($cas_server_ca_cert_path);

// For quick testing you can disable SSL validation of the CAS server.
// THIS SETTING IS NOT RECOMMENDED FOR PRODUCTION.
// VALIDATING THE CAS SERVER IS CRUCIAL TO THE SECURITY OF THE CAS PROTOCOL!
CAS::setNoCasServerValidation();

// set PGT storage to file in plain format in the same directory as session files
CAS::setPGTStorageDb($db, $db_user, $db_password, $db_table);

// force CAS authentication
CAS::forceAuthentication();

// at this step, the user has been authenticated by the CAS server
// and the user's login name can be read with CAS::getUser().

// moreover, a PGT was retrieved from the CAS server that will
// permit to gain accesses to new services.

?>
<html>
  <head>
    <title>phpCAS proxy example with PGT storage to a database</title>
    <link rel="stylesheet" type='text/css' href='example.css'/>
  </head>
  <body>
    <h1>phpCAS proxy example with PGT storage to file</h1>
    <?php require 'script_info.php' ?>
    <p>the user's login is <b><?php echo CAS::getUser(); ?></b>.</p>
    <h2>Response from service <?php echo $serviceUrl; ?></h2>
<?php
flush();
// call a service and change the color depending on the result
if (CAS::serviceWeb($serviceUrl, $err_code, $output)) {
    echo '<div class="success">';
} else {
    echo '<div class="error">';
}
echo $output;
echo '</div>';
                                                             ?>
  </body>
</html>
