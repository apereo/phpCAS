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
//$pfm = PEAR_PackageFileManager2::importOptions('package.xml', array(
$pfm = new PEAR_PackageFileManager2();
$pfm->setOptions(array(
//    'packagedirectory'  => 'c:/devel/phpcas-devel/tmp/cas-${phpcas.version}/',
//    'outputdirectory'  => 'c:/devel/phpcas-devel/tmp',
//    'outputdirectory'  => 'c:\\devel\\phpcas-devel\\tmp',
    'packagedirectory'  => '${basedir}/tmp/cas-${phpcas.version}',
    'outputdirectory'  => '${basedir}/tmp',
    'baseinstalldir'    => '/',
    'filelistgenerator' => 'file',
    'simpleoutput' => true,
    'roles'=>array('php'=>'php'),
    'exceptions'=>array()
));
$pfm->setPackage('CAS');
$pfm->setPackageType('php'); // this is a PEAR-style php script package
$pfm->setSummary('Central Authentication Service client library in php');
$pfm->setDescription('This package is a PEAR installable library for using a Central
Authentication Service.');
$pfm->setChannel('__uri');
$pfm->setAPIStability('stable');
$pfm->setReleaseStability('stable');
$pfm->setAPIVersion('${phpcas.version}');
$pfm->setReleaseVersion('${phpcas.version}');
$pfm->setNotes('
see http://www.ja-sig.org/wiki/display/CASC/phpCAS+ChangeLog
');
$pfm->addMaintainer('helper','saltybeagle','Brett Bieber','brett.bieber@gmail.com');
$pfm->addMaintainer('helper','fritschi','Joachim Fritschi','fritschi@hrz.tu-darmstadt.de');
$pfm->addMaintainer('lead','paubry','Pascal Aubry','pascal.aubry@univ-rennes1.fr');
$pfm->setLicense('New BSD License', 'http://www.ja-sig.org/wiki/display/CASC/phpCAS');
$pfm->clearDeps();
$pfm->setPhpDep('5.0.0');
$pfm->setPearinstallerDep('1.4.3');

$pfm->addExtensionDep('required', 'curl');
$pfm->addExtensionDep('required', 'openssl');
$pfm->addExtensionDep('required', 'dom');
$pfm->addExtensionDep('required', 'zlib');

$pfm->addPackageDepWithChannel('required', 'DB', 'pear.php.net', '1.4.0');

$pfm->generateContents();
//if (isset($_SERVER['argv']) && $_SERVER['argv'][1] == 'make') {
    $pfm->writePackageFile();
//} else {
//    $pfm->debugPackageFile();
//}
