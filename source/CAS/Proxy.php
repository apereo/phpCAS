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
 * @file     CAS/Client.php
 * @category Authentication
 * @package  PhpCAS
 * @author   Pascal Aubry <pascal.aubry@univ-rennes1.fr>
 * @author   Olivier Berger <olivier.berger@it-sudparis.eu>
 * @author   Brett Bieber <brett.bieber@gmail.com>
 * @author   Joachim Fritschi <jfritschi@freenet.de>
 * @author   Adam Franco <afranco@middlebury.edu>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 */

/**
 * The CAS_Proxy class is a client interface that provides CAS authentication
 * to PHP applications and adds support for proxying requests to other services.
 *
 * @class    CAS_Client
 * @category Authentication
 * @package  PhpCAS
 * @author   Pascal Aubry <pascal.aubry@univ-rennes1.fr>
 * @author   Olivier Berger <olivier.berger@it-sudparis.eu>
 * @author   Brett Bieber <brett.bieber@gmail.com>
 * @author   Joachim Fritschi <jfritschi@freenet.de>
 * @author   Adam Franco <afranco@middlebury.edu>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 *
 */

class CAS_Proxy extends CAS_Client
{
    /**
     * CAS_Client constructor.
     *
     * @param string $server_version  the version of the CAS server
     * @param bool   $proxy           true if the CAS client is a CAS proxy
     * @param string $server_hostname the hostname of the CAS server
     * @param int    $server_port     the port the CAS server is running on
     * @param string $server_uri      the URI the CAS server is responding on
     * @param bool   $changeSessionID Allow phpCAS to change the session_id
     *                                (Single Sign Out/handleLogoutRequests
     *                                is based on that change)
     *
     * @return a newly created CAS_Client object
     */
    public function __construct(
        $server_version,
        $proxy,
        $server_hostname,
        $server_port,
        $server_uri,
        $changeSessionID = true
    ) {
        // Make cookie handling available.
		if (!isset($_SESSION['phpCAS'])) {
			$_SESSION['phpCAS'] = array();
		}
		if (!isset($_SESSION['phpCAS']['service_cookies'])) {
			$_SESSION['phpCAS']['service_cookies'] = array();
		}
		$this->_serviceCookieJar = new CAS_CookieJar(
			$_SESSION['phpCAS']['service_cookies']
		);
		
        //check version
        switch ($server_version) {
        case CAS_VERSION_2_0:
            break;
        default:
            phpCAS::error(
                'the CAS server protocol selected (`'.$server_version
                .'\') is does not support proxying of requests.'
            );
        }
        
        parent::__construct($server_version, $proxy, $server_hostname, $server_port, $server_uri, $changeSessionID);
    }
    
    /**
     * Extract tickets from the current request parameters
     * 
     * @return void
     */
    protected function extractTicketsFromRequest () {
        $this->_setCallbackMode(!empty($_GET['pgtIou'])&&!empty($_GET['pgtId']));
        
        if ( $this->_isCallbackMode() ) {
            //callback mode: check that phpCAS is secured
            if ( !$this->_isHttps() ) {
                phpCAS::error(
                    'CAS proxies must be secured to use phpCAS; PGT\'s will not be received from the CAS server'
                );
            }
        } else {
            parent::extractTicketsFromRequest();
        }
    }
    
    
    /**
     * Tells if a CAS client is a CAS proxy or not
     *
     * @return true when the CAS client is a CAs proxy, false otherwise
     */
    public function isProxy()
    {
        return true;
    }
    
    /**
     * This method tells if the user has already been (previously) authenticated
     * by looking into the session variables.
     *
     * @note This function switches to callback mode when needed.
     *
     * @return true when the user has already been authenticated; false otherwise.
     */
    protected function _wasPreviouslyAuthenticated()
    {
        phpCAS::traceBegin();
        
        if ( $this->_isCallbackMode() ) {
            // Rebroadcast the pgtIou and pgtId to all nodes
            if ($this->_rebroadcast&&!isset($_POST['rebroadcast'])) {
                $this->_rebroadcast(self::PGTIOU);
            }
            $this->_callback();
        }
        
        $auth = false;
        
        // CAS proxy: username and PGT must be present
        if ( $this->isSessionAuthenticated()
            && !empty($_SESSION['phpCAS']['pgt'])
        ) {
            // authentication already done
            $this->_setUser($_SESSION['phpCAS']['user']);
            if (isset($_SESSION['phpCAS']['attributes'])) {
                $this->setAttributes($_SESSION['phpCAS']['attributes']);
            }
            $this->_setPGT($_SESSION['phpCAS']['pgt']);
            phpCAS::trace(
                'user = `'.$_SESSION['phpCAS']['user'].'\', PGT = `'
                .$_SESSION['phpCAS']['pgt'].'\''
            );

            // Include the list of proxies
            if (isset($_SESSION['phpCAS']['proxies'])) {
                $this->_setProxies($_SESSION['phpCAS']['proxies']);
                phpCAS::trace(
                    'proxies = "'
                    .implode('", "', $_SESSION['phpCAS']['proxies']).'"'
                );
            }

            $auth = true;
        } elseif ( $this->isSessionAuthenticated()
            && empty($_SESSION['phpCAS']['pgt'])
        ) {
            // these two variables should be empty or not empty at the same time
            phpCAS::trace(
                'username found (`'.$_SESSION['phpCAS']['user']
                .'\') but PGT is empty'
            );
            // unset all tickets to enforce authentication
            unset($_SESSION['phpCAS']);
            $this->setTicket('');
        } elseif ( !$this->isSessionAuthenticated()
            && !empty($_SESSION['phpCAS']['pgt'])
        ) {
            // these two variables should be empty or not empty at the same time
            phpCAS::trace(
                'PGT found (`'.$_SESSION['phpCAS']['pgt']
                .'\') but username is empty'
            );
            // unset all tickets to enforce authentication
            unset($_SESSION['phpCAS']);
            $this->setTicket('');
        } else {
            phpCAS::trace('neither user nor PGT found');
        }
        
        phpCAS::traceEnd($auth);
        return $auth;
    }
    
    // ########################################################################
    //  PT VALIDATION
    // ########################################################################
    /**
    * @addtogroup internalProxied
    * @{
    */

    /**
     * This method is used to validate a cas 2.0 ST or PT; halt on failure
     * Used for all CAS 2.0 validations
     *
     * @param string &$validate_url  the url of the reponse
     * @param string &$text_response the text of the repsones
     * @param string &$tree_response the domxml tree of the respones
     *
     * @return bool true when successfull and issue a CAS_AuthenticationException
     * and false on an error
     */
    public function validateCAS20(&$validate_url,&$text_response,&$tree_response)
    {
        $result = parent::validateCAS20($validate_url, $text_response, $tree_response);
        
        $this->_validatePGT($validate_url, $text_response, $tree_response); // idem
        phpCAS::trace('PGT `'.$this->_getPGT().'\' was validated');
        $_SESSION['phpCAS']['pgt'] = $this->_getPGT();
        
        return $result;
    }
    
    /** @} */
    
    // ########################################################################
    //  CALLBACK MODE
    // ########################################################################
    /**
    * @addtogroup internalCallback
    * @{
    */
    /**
     * each PHP script using phpCAS in proxy mode is its own callback to get the
     * PGT back from the CAS server. callback_mode is detected by the constructor
     * thanks to the GET parameters.
     */

    /**
     * a boolean to know if the CAS client is running in callback mode. Written by
     * CAS_Client::setCallBackMode(), read by CAS_Client::_isCallbackMode().
     *
     * @hideinitializer
     */
    private $_callback_mode = false;

    /**
     * This method sets/unsets callback mode.
     *
     * @param bool $callback_mode true to set callback mode, false otherwise.
     *
     * @return void
     */
    private function _setCallbackMode($callback_mode)
    {
        $this->_callback_mode = $callback_mode;
    }

    /**
     * This method returns true when the CAs client is running i callback mode,
     * false otherwise.
     *
     * @return A boolean.
     */
    private function _isCallbackMode()
    {
        return $this->_callback_mode;
    }

    /**
     * the URL that should be used for the PGT callback (in fact the URL of the
     * current request without any CGI parameter). Written and read by
     * CAS_Client::_getCallbackURL().
     *
     * @hideinitializer
     */
    private $_callback_url = '';

    /**
     * This method returns the URL that should be used for the PGT callback (in
     * fact the URL of the current request without any CGI parameter, except if
     * phpCAS::setFixedCallbackURL() was used).
     *
     * @return The callback URL
     */
    private function _getCallbackURL()
    {
        // the URL is built when needed only
        if ( empty($this->_callback_url) ) {
            $final_uri = '';
            // remove the ticket if present in the URL
            $final_uri = 'https://';
            $final_uri .= $this->_getServerUrl();
            $request_uri = $_SERVER['REQUEST_URI'];
            $request_uri = preg_replace('/\?.*$/', '', $request_uri);
            $final_uri .= $request_uri;
            $this->setCallbackURL($final_uri);
        }
        return $this->_callback_url;
    }

    /**
     * This method sets the callback url.
     *
     * @param string $url url to set callback
     *
     * @return void
     */
    public function setCallbackURL($url)
    {
        return $this->_callback_url = $url;
    }

    /**
     * This method is called by CAS_Client::CAS_Client() when running in callback
     * mode. It stores the PGT and its PGT Iou, prints its output and halts.
     *
     * @return void
     */
    private function _callback()
    {
        phpCAS::traceBegin();
        if (preg_match('/PGTIOU-[\.\-\w]/', $_GET['pgtIou'])) {
            if (preg_match('/[PT]GT-[\.\-\w]/', $_GET['pgtId'])) {
                $this->printHTMLHeader('phpCAS callback');
                $pgt_iou = $_GET['pgtIou'];
                $pgt = $_GET['pgtId'];
                phpCAS::trace('Storing PGT `'.$pgt.'\' (id=`'.$pgt_iou.'\')');
                echo '<p>Storing PGT `'.$pgt.'\' (id=`'.$pgt_iou.'\').</p>';
                $this->_storePGT($pgt, $pgt_iou);
                $this->printHTMLFooter();
                phpCAS::traceExit("Successfull Callback");
            } else {
                phpCAS::error('PGT format invalid' . $_GET['pgtId']);
                phpCAS::traceExit('PGT format invalid' . $_GET['pgtId']);
            }
        } else {
            phpCAS::error('PGTiou format invalid' . $_GET['pgtIou']);
            phpCAS::traceExit('PGTiou format invalid' . $_GET['pgtIou']);
        }

        // Flush the buffer to prevent from sending anything other then a 200
        // Success Status back to the CAS Server. The Exception would normally
        // report as a 500 error.
        flush();
        throw new CAS_GracefullTerminationException();
    }


    /** @} */
    
    // ########################################################################
    //  PGT
    // ########################################################################
    /**
    * @addtogroup internalProxy
    * @{
    */

    /**
     * the Proxy Grnting Ticket given by the CAS server (empty otherwise).
     * Written by CAS_Client::_setPGT(), read by CAS_Client::_getPGT() and
     * CAS_Client::_hasPGT().
     *
     * @hideinitializer
     */
    private $_pgt = '';

    /**
     * This method returns the Proxy Granting Ticket given by the CAS server.
     *
     * @return string the Proxy Granting Ticket.
     */
    private function _getPGT()
    {
        return $this->_pgt;
    }

    /**
     * This method stores the Proxy Granting Ticket.
     *
     * @param string $pgt The Proxy Granting Ticket.
     *
     * @return void
     */
    private function _setPGT($pgt)
    {
        $this->_pgt = $pgt;
    }

    /**
     * This method tells if a Proxy Granting Ticket was stored.
     *
     * @return true if a Proxy Granting Ticket has been stored.
     */
    private function _hasPGT()
    {
        return !empty($this->_pgt);
    }

    /** @} */

    

    // ########################################################################
    //  PGT STORAGE
    // ########################################################################
    /**
    * @addtogroup internalPGTStorage
    * @{
    */

    /**
     * an instance of a class inheriting of PGTStorage, used to deal with PGT
     * storage. Created by CAS_Client::setPGTStorageFile(), used
     * by CAS_Client::setPGTStorageFile() and CAS_Client::_initPGTStorage().
     *
     * @hideinitializer
     */
    private $_pgt_storage = null;

    /**
     * This method is used to initialize the storage of PGT's.
     * Halts on error.
     *
     * @return void
     */
    private function _initPGTStorage()
    {
        // if no SetPGTStorageXxx() has been used, default to file
        if ( !is_object($this->_pgt_storage) ) {
            $this->setPGTStorageFile();
        }

        // initializes the storage
        $this->_pgt_storage->init();
    }

    /**
     * This method stores a PGT. Halts on error.
     *
     * @param string $pgt     the PGT to store
     * @param string $pgt_iou its corresponding Iou
     *
     * @return void
     */
    private function _storePGT($pgt,$pgt_iou)
    {
        // ensure that storage is initialized
        $this->_initPGTStorage();
        // writes the PGT
        $this->_pgt_storage->write($pgt, $pgt_iou);
    }

    /**
     * This method reads a PGT from its Iou and deletes the corresponding
     * storage entry.
     *
     * @param string $pgt_iou the PGT Iou
     *
     * @return mul The PGT corresponding to the Iou, false when not found.
     */
    private function _loadPGT($pgt_iou)
    {
        // ensure that storage is initialized
        $this->_initPGTStorage();
        // read the PGT
        return $this->_pgt_storage->read($pgt_iou);
    }

    /**
     * This method can be used to set a custom PGT storage object.
     *
     * @param CAS_PGTStorage_AbstractStorage $storage a PGT storage object that
     * inherits from the CAS_PGTStorage_AbstractStorage class
     *
     * @return void
     */
    public function setPGTStorage($storage)
    {
        // check that the storage has not already been set
        if ( is_object($this->_pgt_storage) ) {
            phpCAS::error('PGT storage already defined');
        }

        // check to make sure a valid storage object was specified
        if ( !($storage instanceof CAS_PGTStorage_AbstractStorage) ) {
            phpCAS::error('Invalid PGT storage object');
        }

        // store the PGTStorage object
        $this->_pgt_storage = $storage;
    }

    /**
     * This method is used to tell phpCAS to store the response of the
     * CAS server to PGT requests in a database.
     *
     * @param string $dsn_or_pdo     a dsn string to use for creating a PDO
     * object or a PDO object
     * @param string $username       the username to use when connecting to the
     * database
     * @param string $password       the password to use when connecting to the
     * database
     * @param string $table          the table to use for storing and retrieving
     * PGTs
     * @param string $driver_options any driver options to use when connecting
     * to the database
     *
     * @return void
     */
    public function setPGTStorageDb(
        $dsn_or_pdo, $username='', $password='', $table='', $driver_options=null
    ) {
        // create the storage object
        $this->setPGTStorage(
            new CAS_PGTStorage_Db(
                $this, $dsn_or_pdo, $username, $password, $table, $driver_options
            )
        );
    }

    /**
     * This method is used to tell phpCAS to store the response of the
     * CAS server to PGT requests onto the filesystem.
     *
     * @param string $path the path where the PGT's should be stored
     *
     * @return void
     */
    public function setPGTStorageFile($path='')
    {
        // create the storage object
        $this->setPGTStorage(new CAS_PGTStorage_File($this, $path));
    }
    
    /** @} */
    
    // XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
    // XX                                                                    XX
    // XX                     PROXY FEATURES (CAS 2.0)                       XX
    // XX                                                                    XX
    // XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

    // ########################################################################
    //  PROXYING
    // ########################################################################
    /**
    * @addtogroup internalProxy
    * @{
    */

    /**
     * Handler for managing service cookies.
     */
    private $_serviceCookieJar;


    // ########################################################################
    //  PGT VALIDATION
    // ########################################################################
    /**
    * This method is used to validate a PGT; halt on failure.
    *
    * @param string &$validate_url the URL of the request to the CAS server.
    * @param string $text_response the response of the CAS server, as is
    *                              (XML text); result of
    *                              CAS_Client::validateCAS10() or
    *                              CAS_Client::validateCAS20().
    * @param string $tree_response the response of the CAS server, as a DOM XML
    * tree; result of CAS_Client::validateCAS10() or CAS_Client::validateCAS20().
    *
    * @return bool true when successfull and issue a CAS_AuthenticationException
    * and false on an error
    */
    private function _validatePGT(&$validate_url,$text_response,$tree_response)
    {
        phpCAS::traceBegin();
        if ( $tree_response->getElementsByTagName("proxyGrantingTicket")->length == 0) {
            phpCAS::trace('<proxyGrantingTicket> not found');
            // authentication succeded, but no PGT Iou was transmitted
            throw new CAS_AuthenticationException(
                $this, 'Ticket validated but no PGT Iou transmitted',
                $validate_url, false/*$no_response*/, false/*$bad_response*/,
                $text_response
            );
        } else {
            // PGT Iou transmitted, extract it
            $pgt_iou = trim(
                $tree_response->getElementsByTagName("proxyGrantingTicket")->item(0)->nodeValue
            );
            if (preg_match('/PGTIOU-[\.\-\w]/', $pgt_iou)) {
                $pgt = $this->_loadPGT($pgt_iou);
                if ( $pgt == false ) {
                    phpCAS::trace('could not load PGT');
                    throw new CAS_AuthenticationException(
                        $this,
                        'PGT Iou was transmitted but PGT could not be retrieved',
                        $validate_url, false/*$no_response*/,
                        false/*$bad_response*/, $text_response
                    );
                }
                $this->_setPGT($pgt);
            } else {
                phpCAS::trace('PGTiou format error');
                throw new CAS_AuthenticationException(
                    $this, 'PGT Iou was transmitted but has wrong format',
                    $validate_url, false/*$no_response*/, false/*$bad_response*/,
                    $text_response
                );
            }
        }
        phpCAS::traceEnd(true);
        return true;
    }

    // ########################################################################
    //  PGT VALIDATION
    // ########################################################################

    /**
     * This method is used to retrieve PT's from the CAS server thanks to a PGT.
     *
     * @param string $target_service the service to ask for with the PT.
     * @param string &$err_code      an error code (PHPCAS_SERVICE_OK on success).
     * @param string &$err_msg       an error message (empty on success).
     *
     * @return a Proxy Ticket, or false on error.
     */
    public function retrievePT($target_service,&$err_code,&$err_msg)
    {
        phpCAS::traceBegin();

        // by default, $err_msg is set empty and $pt to true. On error, $pt is
        // set to false and $err_msg to an error message. At the end, if $pt is false
        // and $error_msg is still empty, it is set to 'invalid response' (the most
        // commonly encountered error).
        $err_msg = '';

        // build the URL to retrieve the PT
        $cas_url = $this->getServerProxyURL().'?targetService='
            .urlencode($target_service).'&pgt='.$this->_getPGT();

        // open and read the URL
        if ( !$this->_readURL($cas_url, $headers, $cas_response, $err_msg) ) {
            phpCAS::trace(
                'could not open URL \''.$cas_url.'\' to validate ('.$err_msg.')'
            );
            $err_code = PHPCAS_SERVICE_PT_NO_SERVER_RESPONSE;
            $err_msg = 'could not retrieve PT (no response from the CAS server)';
            phpCAS::traceEnd(false);
            return false;
        }

        $bad_response = false;

        if ( !$bad_response ) {
            // create new DOMDocument object
            $dom = new DOMDocument();
            // Fix possible whitspace problems
            $dom->preserveWhiteSpace = false;
            // read the response of the CAS server into a DOM object
            if ( !($dom->loadXML($cas_response))) {
                phpCAS::trace('dom->loadXML() failed');
                // read failed
                $bad_response = true;
            }
        }

        if ( !$bad_response ) {
            // read the root node of the XML tree
            if ( !($root = $dom->documentElement) ) {
                phpCAS::trace('documentElement failed');
                // read failed
                $bad_response = true;
            }
        }

        if ( !$bad_response ) {
            // insure that tag name is 'serviceResponse'
            if ( $root->localName != 'serviceResponse' ) {
                phpCAS::trace('localName failed');
                // bad root node
                $bad_response = true;
            }
        }

        if ( !$bad_response ) {
            // look for a proxySuccess tag
            if ( $root->getElementsByTagName("proxySuccess")->length != 0) {
                $proxy_success_list = $root->getElementsByTagName("proxySuccess");

                // authentication succeded, look for a proxyTicket tag
                if ( $proxy_success_list->item(0)->getElementsByTagName("proxyTicket")->length != 0) {
                    $err_code = PHPCAS_SERVICE_OK;
                    $err_msg = '';
                    $pt = trim(
                        $proxy_success_list->item(0)->getElementsByTagName("proxyTicket")->item(0)->nodeValue
                    );
                    phpCAS::trace('original PT: '.trim($pt));
                    phpCAS::traceEnd($pt);
                    return $pt;
                } else {
                    phpCAS::trace('<proxySuccess> was found, but not <proxyTicket>');
                }
            } else if ($root->getElementsByTagName("proxyFailure")->length != 0) {
                // look for a proxyFailure tag
                $proxy_failure_list = $root->getElementsByTagName("proxyFailure");

                // authentication failed, extract the error
                $err_code = PHPCAS_SERVICE_PT_FAILURE;
                $err_msg = 'PT retrieving failed (code=`'
                .$proxy_failure_list->item(0)->getAttribute('code')
                .'\', message=`'
                .trim($proxy_failure_list->item(0)->nodeValue)
                .'\')';
                phpCAS::traceEnd(false);
                return false;
            } else {
                phpCAS::trace('neither <proxySuccess> nor <proxyFailure> found');
            }
        }

        // at this step, we are sure that the response of the CAS server was
        // illformed
        $err_code = PHPCAS_SERVICE_PT_BAD_SERVER_RESPONSE;
        $err_msg = 'Invalid response from the CAS server (response=`'
            .$cas_response.'\')';

        phpCAS::traceEnd(false);
        return false;
    }

    /** @} */
    
    // ########################################################################
    // ACCESS TO EXTERNAL SERVICES
    // ########################################################################

    /**
     * @addtogroup internalProxyServices
     * @{
     */


    /**
     * Answer a proxy-authenticated service handler.
     *
     * @param string $type The service type. One of:
     * PHPCAS_PROXIED_SERVICE_HTTP_GET, PHPCAS_PROXIED_SERVICE_HTTP_POST,
     * PHPCAS_PROXIED_SERVICE_IMAP
     *
     * @return CAS_ProxiedService
     * @throws InvalidArgumentException If the service type is unknown.
     */
    public function getProxiedService ($type)
    {
        switch ($type) {
        case PHPCAS_PROXIED_SERVICE_HTTP_GET:
        case PHPCAS_PROXIED_SERVICE_HTTP_POST:
            $requestClass = $this->_requestImplementation;
            $request = new $requestClass();
            if (count($this->_curl_options)) {
                $request->setCurlOptions($this->_curl_options);
            }
            $proxiedService = new $type($request, $this->_serviceCookieJar);
            if ($proxiedService instanceof CAS_ProxiedService_Testable) {
                $proxiedService->setCasClient($this);
            }
            return $proxiedService;
        case PHPCAS_PROXIED_SERVICE_IMAP;
            $proxiedService = new CAS_ProxiedService_Imap($this->getUser());
            if ($proxiedService instanceof CAS_ProxiedService_Testable) {
                $proxiedService->setCasClient($this);
            }
            return $proxiedService;
        default:
            throw new CAS_InvalidArgumentException(
                "Unknown proxied-service type, $type."
            );
        }
    }

    /**
     * Initialize a proxied-service handler with the proxy-ticket it should use.
     *
     * @param CAS_ProxiedService $proxiedService service handler
     *
     * @return void
     *
     * @throws CAS_ProxyTicketException If there is a proxy-ticket failure.
     *		The code of the Exception will be one of:
     *			PHPCAS_SERVICE_PT_NO_SERVER_RESPONSE
     *			PHPCAS_SERVICE_PT_BAD_SERVER_RESPONSE
     *			PHPCAS_SERVICE_PT_FAILURE
     * @throws CAS_ProxiedService_Exception If there is a failure getting the
     * url from the proxied service.
     */
    public function initializeProxiedService (CAS_ProxiedService $proxiedService)
    {
        $url = $proxiedService->getServiceUrl();
        if (!is_string($url)) {
            throw new CAS_ProxiedService_Exception(
                "Proxied Service ".get_class($proxiedService)
                ."->getServiceUrl() should have returned a string, returned a "
                .gettype($url)." instead."
            );
        }
        $pt = $this->retrievePT($url, $err_code, $err_msg);
        if (!$pt) {
            throw new CAS_ProxyTicketException($err_msg, $err_code);
        }
        $proxiedService->setProxyTicket($pt);
    }

    /**
     * This method is used to access an HTTP[S] service.
     *
     * @param string $url       the service to access.
     * @param int    &$err_code an error code Possible values are
     * PHPCAS_SERVICE_OK (on success), PHPCAS_SERVICE_PT_NO_SERVER_RESPONSE,
     * PHPCAS_SERVICE_PT_BAD_SERVER_RESPONSE, PHPCAS_SERVICE_PT_FAILURE,
     * PHPCAS_SERVICE_NOT_AVAILABLE.
     * @param string &$output   the output of the service (also used to give an error
     * message on failure).
     *
     * @return true on success, false otherwise (in this later case, $err_code
     * gives the reason why it failed and $output contains an error message).
     */
    public function serviceWeb($url,&$err_code,&$output)
    {
        try {
            $service = $this->getProxiedService(PHPCAS_PROXIED_SERVICE_HTTP_GET);
            $service->setUrl($url);
            $service->send();
            $output = $service->getResponseBody();
            $err_code = PHPCAS_SERVICE_OK;
            return true;
        } catch (CAS_ProxyTicketException $e) {
            $err_code = $e->getCode();
            $output = $e->getMessage();
            return false;
        } catch (CAS_ProxiedService_Exception $e) {
            $lang = $this->getLangObj();
            $output = sprintf(
                $lang->getServiceUnavailable(), $url, $e->getMessage()
            );
            $err_code = PHPCAS_SERVICE_NOT_AVAILABLE;
            return false;
        }
    }

    /**
     * This method is used to access an IMAP/POP3/NNTP service.
     *
     * @param string $url        a string giving the URL of the service, including
     * the mailing box for IMAP URLs, as accepted by imap_open().
     * @param string $serviceUrl a string giving for CAS retrieve Proxy ticket
     * @param string $flags      options given to imap_open().
     * @param int    &$err_code  an error code Possible values are
     * PHPCAS_SERVICE_OK (on success), PHPCAS_SERVICE_PT_NO_SERVER_RESPONSE,
     * PHPCAS_SERVICE_PT_BAD_SERVER_RESPONSE, PHPCAS_SERVICE_PT_FAILURE,
     *  PHPCAS_SERVICE_NOT_AVAILABLE.
     * @param string &$err_msg   an error message on failure
     * @param string &$pt        the Proxy Ticket (PT) retrieved from the CAS
     * server to access the URL on success, false on error).
     *
     * @return object an IMAP stream on success, false otherwise (in this later
     *  case, $err_code gives the reason why it failed and $err_msg contains an
     *  error message).
     */
    public function serviceMail($url,$serviceUrl,$flags,&$err_code,&$err_msg,&$pt)
    {
        try {
            $service = $this->getProxiedService(PHPCAS_PROXIED_SERVICE_IMAP);
            $service->setServiceUrl($serviceUrl);
            $service->setMailbox($url);
            $service->setOptions($flags);

            $stream = $service->open();
            $err_code = PHPCAS_SERVICE_OK;
            $pt = $service->getImapProxyTicket();
            return $stream;
        } catch (CAS_ProxyTicketException $e) {
            $err_msg = $e->getMessage();
            $err_code = $e->getCode();
            $pt = false;
            return false;
        } catch (CAS_ProxiedService_Exception $e) {
            $lang = $this->getLangObj();
            $err_msg = sprintf(
                $lang->getServiceUnavailable(),
                $url,
                $e->getMessage()
            );
            $err_code = PHPCAS_SERVICE_NOT_AVAILABLE;
            $pt = false;
            return false;
        }
    }

    /** @} **/
}

?>
