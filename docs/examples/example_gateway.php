<?php

/**
 * Example that uses the CAS gateway feature.
 *
 * PHP Version 5
 *
 * @file     example_gateway.php
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

if (isset($_REQUEST['logout'])) {
    CAS::logout();
}
if (isset($_REQUEST['login'])) {
    CAS::forceAuthentication();
}

// check CAS authentication
$auth = CAS::checkAuthentication();

?>
<html>
  <head>
    <title>phpCAS simple client</title>
  </head>
  <body>
<?php
if ($auth) {
    // for this test, simply print that the authentication was successful
        ?>
    <h1>Successful Authentication!</h1>
    <?php include 'script_info.php' ?>
    <p>the user's login is <b><?php echo CAS::getUser();
    ?></b>.</p>
    <p><a href="?logout=">Logout</a></p><?php

} else {
    ?>
    <h1>Guest mode</h1>
    <p><a href="?login=">Login</a></p><?php

}
                                      ?>
    <p>phpCAS version is <b><?php echo CAS::getVersion(); ?></b>.</p>
  </body>
</html>
