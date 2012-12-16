<?php
/**
 * Packaging File to create a pear package.xml
 *
 * PHP Version 5
 *
 * @category Authentication
 * @package  PhpCAS
 * @author   Brett Bieber <brett.bieber@gmail.com>
 * @author   Joachim Fritschi <jfritschi@freenet.de>
 * @license  http://www1.unl.edu/wdn/wiki/Software_License New BSD License
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 *
 */
error_reporting(E_ALL ^ E_DEPRECATED);
ini_set('display_errors', true);

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
$pfm = new PEAR_PackageFileManager2();
$pfm->setOptions(
    array(
    'packagedirectory'  => '${basedir}/tmp/CAS-${phpcas.version}',
    'outputdirectory'  => '${basedir}/tmp',
    'baseinstalldir'    => '/',
    'filelistgenerator' => 'file',
    'simpleoutput' => true,
    'roles'=>array('php'=>'php'),
    'exceptions'=>array('README.md' => 'doc',
                        'LICENSE' => 'doc',
                        'NOTICE' => 'doc')
    )
);
$pfm->setPackage('CAS');
$pfm->setPackageType('php'); // this is a PEAR-style php script package
$pfm->setSummary('Central Authentication Service client library in php');
$pfm->setDescription('This package is a PEAR installable library for using a Central Authentication Service.');
$pfm->setChannel('__uri');
$pfm->setAPIStability('${phpcas.apiStability}');
$pfm->setReleaseStability('${phpcas.releaseStability}');
$pfm->setAPIVersion('${phpcas.version}');
$pfm->setReleaseVersion('${phpcas.version}');
$pfm->setNotes('see https://github.com/Jasig/phpCAS/blob/master/docs/ChangeLog');

$pfm->addMaintainer('lead', 'jfritschi', 'Joachim Fritschi', 'jfritschi@freenet.de');
$pfm->addMaintainer('contributor', 'adamfranco', 'Adam Franco', 'afranco@middlebury.edu');

$pfm->setLicense('Apache 2.0 License', 'https://github.com/Jasig/phpCAS/blob/master/LICENSE');
$pfm->clearDeps();
$pfm->setPhpDep('5.0.0');
$pfm->setPearinstallerDep('1.4.3');

$pfm->addExtensionDep('required', 'curl');
$pfm->addExtensionDep('required', 'openssl');
$pfm->addExtensionDep('required', 'dom');
$pfm->addExtensionDep('required', 'zlib');
$pfm->addExtensionDep('required', 'pdo');


$pfm->generateContents();
$pfm->writePackageFile();

