<?php
/**
 * Make package file for the UNL_UCBCN package.
 * 
 * @package CAS
 * @author  Brett Bieber
 */

ini_set('display_errors',true);

/**
 * Require the PEAR_PackageFileManager2 classes, and other
 * necessary classes for package.xml file creation.
 */
require_once 'PEAR/PackageFileManager2.php';
require_once 'PEAR/PackageFileManager/File.php';
require_once 'PEAR/Task/Postinstallscript/rw.php';
require_once 'PEAR/Config.php';
require_once 'PEAR/Frontend.php';

/**
 * @var PEAR_PackageFileManager
 */
PEAR::setErrorHandling(PEAR_ERROR_DIE);
chdir(dirname(__FILE__));
$pfm = PEAR_PackageFileManager2::importOptions('package.xml', array(
//$pfm = new PEAR_PackageFileManager2();
//$pfm->setOptions(array(
    'packagedirectory'  => dirname(__FILE__),
    'baseinstalldir'    => 'CAS',
    'filelistgenerator' => 'svn',
    'ignore' => array(  'package.xml',
                        '.project',
                        '*.tgz',
                        'makepackage.php',
                        '*CVS/*',
                        '*.sh',
                        '*.svg',
                        '.cache',
                        'dataobject.ini',
                        'DBDataObjects',
                        'insert_sample_data.php',
                        'install.sh',
                        '*tests*',
                        '*scripts*'),
    'simpleoutput' => true,
    'roles'=>array('php'=>'php'),
    'exceptions'=>array()
));
$pfm->setPackage('CAS');
$pfm->setPackageType('php'); // this is a PEAR-style php script package
$pfm->setSummary('Central Authentication Service client library in php');
$pfm->setDescription('This package is a PEAR installable library for using a Central
Authentication Service.');
$pfm->setChannel('pear.unl.edu');
$pfm->setAPIStability('beta');
$pfm->setReleaseStability('beta');
$pfm->setAPIVersion('0.6.0');
$pfm->setReleaseVersion('0.6.0');
$pfm->setNotes('
Bug Fix:
* fixed PGT storage path on Windows (Olivier Thebault).

New Features:
* added methods setCasServerCert() and setCasServerCaCert() to authenticate the CAS server, and method setNoCasServerValidation() to skip the SSL checks (Pascal Aubry, requested by Andrew Petro).
* Added spanish and catalan translations (Ivan Garcia).
');

//$pfm->addMaintainer('helper','saltybeagle','Brett Bieber','brett.bieber@gmail.com');
//$pfm->addMaintainer('lead','paubry','Pascal Aubry','pascal.aubry@univ-rennes1.fr');
$pfm->setLicense('?', '?');
$pfm->clearDeps();
$pfm->setPhpDep('5.0.0');
$pfm->setPearinstallerDep('1.4.3');

$pfm->addExtensionDep('required', 'curl');
$pfm->addExtensionDep('required', 'openssl');
$pfm->addExtensionDep('required', 'dom');
$pfm->addExtensionDep('required', 'zlib');

$pfm->addPackageDepWithChannel('required', 'DB', 'pear.php.net', '1.4.0');

$pfm->generateContents();
if (isset($_SERVER['argv']) && $_SERVER['argv'][1] == 'make') {
    $pfm->writePackageFile();
} else {
    $pfm->debugPackageFile();
}
