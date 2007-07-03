<?php

//
// phpCAS proxy client with PGT storage to database
//

// import phpCAS lib
include_once('CAS.php');

// set debug mode
phpCAS::setDebug();

// initialize phpCAS
phpCAS::proxy(CAS_VERSION_2_0,'sso-cas.univ-rennes1.fr',443,'');

// no SSL validation for the CAS server
phpCAS::setNoCasServerValidation();

// set PGT storage to file in XML format in the same directory as session files
phpCAS::setPGTStorageDB('user',
			'password',
			'',// database_type defaults to `mysql'
			'',// hostname defaults to `localhost'
			0,// use default port
			'',// database defaults to phpCAS
			'' // table defaults to `pgt'
			);

// force CAS authentication
phpCAS::forceAuthentication();

// at this step, the user has been authenticated by the CAS server
// and the user's login name can be read with phpCAS::getUser().

// moreover, a PGT was retrieved from the CAS server that will
// permit to gain accesses to new services.

$service = 'http://phpcas-test.univ-rennes1.fr/examples/example_service.php';

?>
<html>
  <head>
    <title>phpCAS proxy example with PGT storage to database</title>
  </head>
  <body>
    <h1>phpCAS proxy example with PGT storage to database</h1>
    <p>the user's login is <b><?php echo phpCAS::getUser(); ?></b>.</p>
    <h2>Response from service <?php echo $service; ?></h2><ul><hr>
<?php
  flush();
  // call a service and change the color depending on the result
  if ( phpCAS::serviceWeb($service,$err_code,$output) ) {
    echo '<font color="#00FF00">';
  } else {
    echo '<font color="#FF0000">';
  }
  echo $output;
  echo '</font><hr></ul>';
?>
  </body>
</html>
