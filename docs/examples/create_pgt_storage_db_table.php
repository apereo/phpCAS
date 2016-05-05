<?php

/**
 *  Script that generates a default table for PGT/PGTiou storage. This script
 *  assumes a database with proper permissions exists and we are habe
 *  permissions to create a table.
 *  All database settings have to be set in the config.php file. Or the
 *  CAS_PGTStorage_Db() options:
 *  $db, $db_user, $db_password, $db_table, $driver_options
 *  have to filled out directly. Option examples can be found in the
 *  config.example.php.
 *
 * PHP Version 5
 *
 * @file     create_pgt_storage_table.php
 * @category Authentication
 * @author   Joachim Fritschi <jfritschi@freenet.de>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 */

// Load the autoloader
require_once '../../vendor/autoload.php';

// Load the settings from the central config file
require_once 'config.php';

use phpCAS\CAS;
use phpCAS\CAS\Client;
use phpCAS\CAS\PGTStorage\Db;

// Dummy client because we need a 'client' object
$client = new Client(
    CAS::CAS_VERSION_2_0, true, $cas_host, $cas_port, $cas_context, false
);

// Set the storage object
$cas_obj = new Db(
    $client, $db, $db_user, $db_password, $db_table, $driver_options
);
$cas_obj->init();
$cas_obj->createTable();
?>
<html>
  <head>
    <title>phpCAS PGT db storage table creation</title>
    <link rel="stylesheet" type='text/css' href='example.css'/>
  </head>
<body>
<div class="success">
<?php
echo 'Table <b>'.$db_table.'</b> successfully created in database <b>'.$db.'</b>';
?>
</div>
</body>
</html>