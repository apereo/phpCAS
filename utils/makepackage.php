<?php

/**
 * Licensed to Jasig under one or more contributor license
 * agreements. See the NOTICE file distributed with this work for
 * additional information regarding copyright ownership.
 * 
 * Jasig licenses this file to you under the Apache License,
 * Version 2.0 (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at:
 * 
 * http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * Make package file for the UNL_UCBCN package.
 * 
 * @package CAS
 * @author  Brett Bieber
 */
error_reporting(E_ALL ^ E_DEPRECATED);
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
$pfm->setAPIStability('${phpcas.apiStability}');
$pfm->setReleaseStability('${phpcas.releaseStability}');
$pfm->setAPIVersion('${phpcas.version}');
$pfm->setReleaseVersion('${phpcas.version}');
$pfm->setNotes('see https://source.jasig.org/cas-clients/phpcas/trunk/docs/ChangeLog');

$pfm->addMaintainer('lead','fritschi','Joachim Fritschi','jfritschi@freenet.de');
$pfm->addMaintainer('helper','adamfranco','Adam Franco','afranco@middlebury.edu');

$pfm->setLicense('New BSD License', 'https://wiki.jasig.org/display/CASC/phpCAS+license');
$pfm->clearDeps();
$pfm->setPhpDep('5.0.0');
$pfm->setPearinstallerDep('1.4.3');

$pfm->addExtensionDep('required', 'curl');
$pfm->addExtensionDep('required', 'openssl');
$pfm->addExtensionDep('required', 'dom');
$pfm->addExtensionDep('required', 'zlib');
$pfm->addExtensionDep('required', 'pdo');

//$pfm->addPackageDepWithChannel('required', 'DB', 'pear.php.net', '1.4.0');

$pfm->generateContents();
//if (isset($_SERVER['argv']) && $_SERVER['argv'][1] == 'make') {
    $pfm->writePackageFile();
//} else {
//    $pfm->debugPackageFile();
//}
