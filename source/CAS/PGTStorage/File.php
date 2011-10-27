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
 * @file CAS/PGTStorage/File.php
 * Basic class for PGT file storage
 */

/**
 * @class CAS_PGTStorage_File
 * The CAS_PGTStorage_File class is a class for PGT file storage. An instance of
 * this class is returned by CAS_Client::SetPGTStorageFile().
 *
 * @author Pascal Aubry <pascal.aubry at univ-rennes1.fr>
 *
 * @ingroup internalPGTStorageFile
 */

class CAS_PGTStorage_File extends CAS_PGTStorage_AbstractStorage
{
	/**
	 * @addtogroup internalPGTStorageFile
	 * @{
	 */

	/**
	 * a string telling where PGT's should be stored on the filesystem. Written by
	 * PGTStorageFile::PGTStorageFile(), read by getPath().
	 *
	 * @private
	 */
	var $_path;

	/**
	 * This method returns the name of the directory where PGT's should be stored
	 * on the filesystem.
	 *
	 * @return the name of a directory (with leading and trailing '/')
	 *
	 * @private
	 */
	function getPath()
	{
		return $this->_path;
	}

	// ########################################################################
	//  DEBUGGING
	// ########################################################################

	/**
	 * This method returns an informational string giving the type of storage
	 * used by the object (used for debugging purposes).
	 *
	 * @return an informational string.
	 * @public
	 */
	function getStorageType()
	{
		return "file";
	}

	/**
	 * This method returns an informational string giving informations on the
	 * parameters of the storage.(used for debugging purposes).
	 *
	 * @return an informational string.
	 * @public
	 */
	function getStorageInfo()
	{
		return 'path=`'.$this->getPath().'\'';
	}

	// ########################################################################
	//  CONSTRUCTOR
	// ########################################################################

	/**
	 * The class constructor, called by CAS_Client::SetPGTStorageFile().
	 *
	 * @param $cas_parent the CAS_Client instance that creates the object.
	 * @param $path the path where the PGT's should be stored
	 *
	 * @public
	 */
	function __construct($cas_parent,$path)
	{
		phpCAS::traceBegin();
		// call the ancestor's constructor
		parent::__construct($cas_parent);
		
		if (empty($path) ) $path = CAS_PGT_STORAGE_FILE_DEFAULT_PATH;
		// check that the path is an absolute path
		if (getenv("OS")=="Windows_NT"){
			 
			if (!preg_match('`^[a-zA-Z]:`', $path)) {
				phpCAS::error('an absolute path is needed for PGT storage to file');
			}
			 
		}
		else
		{

			if ( $path[0] != '/' ) {
				phpCAS::error('an absolute path is needed for PGT storage to file');
			}

			// store the path (with a leading and trailing '/')
			$path = preg_replace('|[/]*$|','/',$path);
			$path = preg_replace('|^[/]*|','/',$path);
		}

		$this->_path = $path;
		phpCAS::traceEnd();
	}

	// ########################################################################
	//  INITIALIZATION
	// ########################################################################

	/**
	 * This method is used to initialize the storage. Halts on error.
	 *
	 * @public
	 */
	function init()
	{
		phpCAS::traceBegin();
		// if the storage has already been initialized, return immediatly
		if ( $this->isInitialized() )
		return;
		// call the ancestor's method (mark as initialized)
		parent::init();
		phpCAS::traceEnd();
	}

	// ########################################################################
	//  PGT I/O
	// ########################################################################

	/**
	 * This method returns the filename corresponding to a PGT Iou.
	 *
	 * @param $pgt_iou the PGT iou.
	 *
	 * @return a filename
	 * @private
	 */
	function getPGTIouFilename($pgt_iou)
	{
		phpCAS::traceBegin();
		$filename = $this->getPath().$pgt_iou.'.plain';
		phpCAS::traceEnd($filename);
		return $filename;
	}

	/**
	 * This method stores a PGT and its corresponding PGT Iou into a file. Echoes a
	 * warning on error.
	 *
	 * @param $pgt the PGT
	 * @param $pgt_iou the PGT iou
	 *
	 * @public
	 */
	function write($pgt,$pgt_iou)
	{
		phpCAS::traceBegin();
		$fname = $this->getPGTIouFilename($pgt_iou);
		if(!file_exists($fname)){
			if ($f=fopen($fname,"w") ) {
				if ( fputs($f,$pgt) === FALSE ) {
					phpCAS::error('could not write PGT to `'.$fname.'\'');
				}
				fclose($f);
			} else {
				phpCAS::error('could not open `'.$fname.'\'');
			}
		}else{
			phpCAS::error('File exists: `'.$fname.'\'');
		}
		phpCAS::traceEnd();
	}

	/**
	 * This method reads a PGT corresponding to a PGT Iou and deletes the
	 * corresponding file.
	 *
	 * @param $pgt_iou the PGT iou
	 *
	 * @return the corresponding PGT, or FALSE on error
	 *
	 * @public
	 */
	function read($pgt_iou)
	{
		phpCAS::traceBegin();
		$pgt = FALSE;
		$fname = $this->getPGTIouFilename($pgt_iou);
		if (file_exists($fname)){
			if ( !($f=fopen($fname,"r")) ) {
				phpCAS::trace('could not open `'.$fname.'\'');
			} else {
				if ( ($pgt=fgets($f)) === FALSE ) {
					phpCAS::trace('could not read PGT from `'.$fname.'\'');
				}
				fclose($f);
			}
			
			// delete the PGT file
			@unlink($fname);
		}else{
			phpCAS::trace('No such file `'.$fname.'\'');
		}
		phpCAS::traceEnd($pgt);
		return $pgt;
	}

	/** @} */

}


?>