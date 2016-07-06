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
 *
 * PHP Version 5
 *
 * @file     CAS/PGTStorage/AbstractStorage.php
 * @category Authentication
 * @package  PhpCAS
 * @author   Andrew Ivaskevych <andrew.ivaskevych@gmail.com>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 */

/**
 * The CAS_PGTStorage_Redis class is a class for PGT file storage. An instance of
 * this class is returned by CAS_Client::SetPGTStorageFile().
 *
 * @class    CAS_PGTStorage_Redis
 * @category Authentication
 * @package  PhpCAS
 * @author   Pascal Aubry <pascal.aubry@univ-rennes1.fr>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 *
 *
 * @ingroup internalPGTStorageFile
 */

class CAS_PGTStorage_Redis extends CAS_PGTStorage_AbstractStorage
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

    var $_redis;
    var $_scheme;
    var $_host;
    var $_port;
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
        return "redis";
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
        return $this->_scheme . '://'. $this->_host . ':' . $this->_port;
    }

    // ########################################################################
    //  CONSTRUCTOR
    // ########################################################################

    /**
     * The class constructor, called by CAS_Client::SetPGTStorageFile().
     *
     * @param CAS_Client $cas_parent the CAS_Client instance that creates the object.
     * @param string     $path       the path where the PGT's should be stored
     *
     * @return void
     *
     * @public
     */
    function __construct($cas_parent, $scheme, $host, $port)
    {
        phpCAS::traceBegin();
        // call the ancestor's constructor
        parent::__construct($cas_parent);
        $this->_scheme = $scheme;
        $this->_host = $host;
        $this->_port = $port;
        phpCAS::traceEnd();
    }

    // ########################################################################
    //  INITIALIZATION
    // ########################################################################

    /**
     * This method is used to initialize the storage. Halts on error.
     *
     * @return void
     * @public
     */
    function init()
    {
        phpCAS::traceBegin();
        // if the storage has already been initialized, return immediatly
        if ($this->isInitialized()) {
            return;
        }
        // call the ancestor's method (mark as initialized)
        parent::init();

        try {
            // This connection is for a remote server
            $this->_redis = new Predis\Client(array(
                "scheme" => $this->_scheme,
                "host" => $this->_host,
                "port" => $this->_port
            ));
            phpCAS::trace('Successful connected to Redis: ' . $this->getStorageInfo());
        }
        catch (Exception $e) {
            phpCAS::error('Cannot connect to Redis: ' . $this->getStorageInfo());
            phpCAS::error($e->getMessage());
        }

        phpCAS::traceEnd();
    }

    // ########################################################################
    //  PGT I/O
    // ########################################################################

    /**
     * This method returns the Redis key corresponding to a PGT Iou.
     *
     * @param string $pgt_iou the PGT iou.
     *
     * @return a Redis key
     * @private
     */
    function getPGTIouRedisKey($pgt_iou)
    {
        phpCAS::traceBegin();
        $rKey = $pgt_iou.'.redis';
        phpCAS::traceEnd($rKey);
        return $rKey;
    }

    /**
     * This method stores a PGT and its corresponding PGT Iou into a file. Echoes a
     * warning on error.
     *
     * @param string $pgt     the PGT
     * @param string $pgt_iou the PGT iou
     *
     * @return void
     *
     * @public
     */
    function write($pgt,$pgt_iou)
    {
        phpCAS::traceBegin();
        $rKey = $this->getPGTIouRedisKey($pgt_iou);
        $answer = $this->_redis->ping();
        if ($answer == "PONG") {
            if ($this->_redis->set($rKey, $pgt)) {
                phpCAS::trace('Successful saved of PGT as `'.$rKey.'\' in Redis');
            } else {
                phpCAS::error('could not save `'.$rKey.'\' in Redis');
            }
        } else {
            phpCAS::error('Redis answered: '.$answer.'. Cannot connect to Redis: ' . $this->getStorageInfo());
        }
        phpCAS::traceEnd();
    }

    /**
     * This method reads a PGT corresponding to a PGT Iou and deletes the
     * corresponding file.
     *
     * @param string $pgt_iou the PGT iou
     *
     * @return the corresponding PGT, or FALSE on error
     *
     * @public
     */
    function read($pgt_iou)
    {
        phpCAS::traceBegin();
        $pgt = false;
        $rKey = $this->getPGTIouRedisKey($pgt_iou);
        $answer = $this->_redis->ping();
        if ($answer == "PONG") {
            $pgt = $this->_redis->get($rKey);
            if ($pgt === false) {
                phpCAS::error('Key does not exists `'.$rKey.'\' in Redis');
            } else {
                phpCAS::trace('Successful read from Redis PGT by key `'.$rKey.'\'');
            }
            // delete the PGT record in Redis
            $this->_redis->del($rKey);
        } else {
            phpCAS::error('Redis answered: '.$answer.'. Cannot connect to Redis: ' . $this->getStorageInfo());
        }
        phpCAS::traceEnd($pgt);
        return $pgt;
    }

    /** @} */

}
?>