<?php
// Advanced example for SAML with attributes and single logout

// Load the settings from the central config file
include_once('config.php');
// Load the CAS lib
include_once($phpcas_path.'/CAS.php');

// Uncomment to enable debugging
phpCAS::setDebug();

// Initialize phpCAS
phpCAS::client(SAML_VERSION_1_1, $cas_host, $cas_port, $cas_context);

// For production use set the CA certificate that is the issuer of the cert 
// on the CAS server and uncomment the line below
phpCAS::setCasServerCACert($cas_server_ca_cert_path);

// For quick testing you can disable SSL validation of the CAS server. 
// THIS SETTING IS NOT RECOMMENDED FOR PRODUCTION. 
// VALIDATING THE CAS SERVER IS CRUCIAL TO THE SECURITY OF THE CAS PROTOCOL! 
// phpCAS::setNoCasServerValidation();

// Handle SAML logout requests that emanate from the CAS host exclusively.
// Failure to restrict SAML logout requests to authorized hosts could
// allow denial of service attacks where at the least the server is
// tied up parsing bogus XML messages.
phpCAS::handleLogoutRequests(true, $cas_real_hosts);

// Force CAS authentication on any page that includes this file
phpCAS::forceAuthentication();

?>
<h2>Secure Page</h2>
<?php include 'script_info.php' ?>

Authentication succeeded for user
<strong><?php echo phpCAS::getUser(); ?></strong>.

<h3>User Attributes</h3>
<ul>
<?php
foreach (phpCAS::getAttributes() as $key => $value) {
if (is_array($value)) {
echo '<li>', $key, ':<ol>';
foreach($value as $item) {
      echo '<li><strong>', $item, '</strong></li>';
    }
echo '</ol></li>';
} else {
    echo '<li>', $key, ': <strong>', $value, '</strong></li>';
  }
}
?>
</ul>