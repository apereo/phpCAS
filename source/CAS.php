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
 *
 *
 * Interface class of the phpCAS library
 * PHP Version 5
 *
 * @file     CAS/CAS.php
 * @category Authentication
 * @author   Pascal Aubry <pascal.aubry@univ-rennes1.fr>
 * @author   Olivier Berger <olivier.berger@it-sudparis.eu>
 * @author   Brett Bieber <brett.bieber@gmail.com>
 * @author   Joachim Fritschi <jfritschi@freenet.de>
 * @author   Adam Franco <afranco@middlebury.edu>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 * @ingroup public
 */

namespace phpCAS;

use Exception;
use phpCAS\CAS\Client;
use phpCAS\CAS\GracefulTerminationException;
use phpCAS\CAS\InvalidArgumentException;
use phpCAS\CAS\Languages\Catalan;
use phpCAS\CAS\Languages\English;
use phpCAS\CAS\Languages\French;
use phpCAS\CAS\Languages\German;
use phpCAS\CAS\Languages\Greek;
use phpCAS\CAS\Languages\Japanese;
use phpCAS\CAS\Languages\Spanish;
use phpCAS\CAS\OutOfSequenceBeforeClientException;
use phpCAS\CAS\OutOfSequenceBeforeProxyException;
use phpCAS\CAS\PGTStorage\AbstractStorage;
use phpCAS\CAS\ProxiedService;
use phpCAS\CAS\ProxiedService\Http\Get;
use phpCAS\CAS\ProxiedService\Http\Post;
use phpCAS\CAS\ProxiedService\Imap;
use phpCAS\CAS\ProxyChain\ProxyChainInterface;
use phpCAS\CAS\ProxyTicketException;

/**
 * The CAS class is a simple container for the CAS library. It provides CAS
 * authentication for web applications written in PHP.
 *
 * @class CAS
 * @category Authentication
 * @author   Pascal Aubry <pascal.aubry@univ-rennes1.fr>
 * @author   Olivier Berger <olivier.berger@it-sudparis.eu>
 * @author   Brett Bieber <brett.bieber@gmail.com>
 * @author   Joachim Fritschi <jfritschi@freenet.de>
 * @author   Adam Franco <afranco@middlebury.edu>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 */
class CAS
{
    // ########################################################################
    //  CONSTANTS
    // ########################################################################

    // ------------------------------------------------------------------------
    //  CAS VERSIONS
    // ------------------------------------------------------------------------

    /**
     * phpCAS version. accessible for the user by static::getVersion().
     */
    const PHPCAS_VERSION = '1.3.4+';

    /**
     * @addtogroup public
     * @{
     */

    /**
     * CAS version 1.0.
     */
    const CAS_VERSION_1_0 = '1.0';

    /**
     * CAS version 2.0.
     */
    const CAS_VERSION_2_0 = '2.0';

    /**
     * CAS version 3.0.
     */
    const CAS_VERSION_3_0 = '3.0';

    // ------------------------------------------------------------------------
    //  SAML defines
    // ------------------------------------------------------------------------

    /**
     * SAML protocol.
     */
    const SAML_VERSION_1_1 = 'S1';

    /**
     * XML header for SAML POST.
     */
    const SAML_XML_HEADER = '<?xml version="1.0" encoding="UTF-8"?>';

    /**
     * SOAP envelope for SAML POST.
     */
    const SAML_SOAP_ENV = '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"><SOAP-ENV:Header/>';

    /**
     * SOAP body for SAML POST.
     */
    const SAML_SOAP_BODY = '<SOAP-ENV:Body>';

    /**
     * SAMLP request.
     */
    const SAMLP_REQUEST = '<samlp:Request xmlns:samlp="urn:oasis:names:tc:SAML:1.0:protocol"  MajorVersion="1" MinorVersion="1" RequestID="_192.168.16.51.1024506224022" IssueInstant="2002-06-19T17:03:44.022Z">';
    const SAMLP_REQUEST_CLOSE = '</samlp:Request>';

    /**
     * SAMLP artifact tag (for the ticket).
     */
    const SAML_ASSERTION_ARTIFACT = '<samlp:AssertionArtifact>';

    /**
     * SAMLP close.
     */
    const SAML_ASSERTION_ARTIFACT_CLOSE = '</samlp:AssertionArtifact>';

    /**
     * SOAP body close.
     */
    const SAML_SOAP_BODY_CLOSE = '</SOAP-ENV:Body>';

    /**
     * SOAP envelope close.
     */
    const SAML_SOAP_ENV_CLOSE = '</SOAP-ENV:Envelope>';

    /**
     * SAML Attributes.
     */
    const SAML_ATTRIBUTES = 'SAMLATTRIBS';

    /**
     * SAML Attributes.
     */
    const DEFAULT_ERROR = 'Internal script failure';

    /** @} */

    /**
     * @addtogroup publicPGTStorage
     * @{
     */

    // ------------------------------------------------------------------------
    //  FILE PGT STORAGE
    // ------------------------------------------------------------------------

    /**
     * Default path used when storing PGT's to file.
     */
    // const CAS_PGT_STORAGE_FILE_DEFAULT_PATH = session_save_path();

    /** @} */

    // ------------------------------------------------------------------------
    // SERVICE ACCESS ERRORS
    // ------------------------------------------------------------------------

    /**
     * @addtogroup publicServices
     * @{
     */

    /**
     * static::service() error code on success.
     */
    const PHPCAS_SERVICE_OK = 0;

    /**
     * static::service() error code when the PT could not retrieve because
     * the CAS server did not respond.
     */
    const PHPCAS_SERVICE_PT_NO_SERVER_RESPONSE = 1;

    /**
     * static::service() error code when the PT could not retrieve because
     * the response of the CAS server was ill-formed.
     */
    const PHPCAS_SERVICE_PT_BAD_SERVER_RESPONSE = 2;

    /**
     * static::service() error code when the PT could not retrieve because
     * the CAS server did not want to.
     */
    const PHPCAS_SERVICE_PT_FAILURE = 3;

    /**
     * static::service() error code when the service was not available.
     */
    const PHPCAS_SERVICE_NOT_AVAILABLE = 4;

    // ------------------------------------------------------------------------
    // SERVICE TYPES
    // ------------------------------------------------------------------------

    /**
     * static::getProxiedService() type for HTTP GET.
     */
    const PHPCAS_PROXIED_SERVICE_HTTP_GET = Get::class;

    /**
     * static::getProxiedService() type for HTTP POST.
     */
    const PHPCAS_PROXIED_SERVICE_HTTP_POST = Post::class;

    /**
     * static::getProxiedService() type for IMAP.
     */
    const PHPCAS_PROXIED_SERVICE_IMAP = Imap::class;

    /** @} */

    // ------------------------------------------------------------------------
    //  LANGUAGES
    // ------------------------------------------------------------------------

    /**
     * @addtogroup publicLang
     * @{
     */

    const PHPCAS_LANG_ENGLISH = English::class;
    const PHPCAS_LANG_FRENCH = French::class;
    const PHPCAS_LANG_GREEK = Greek::class;
    const PHPCAS_LANG_GERMAN = German::class;
    const PHPCAS_LANG_JAPANESE = Japanese::class;
    const PHPCAS_LANG_SPANISH = Spanish::class;
    const PHPCAS_LANG_CATALAN = Catalan::class;

    /** @} */

    /**
     * @addtogroup internalLang
     * @{
     */

    /**
     * CAS default language (when static::setLang() is not used).
     */
    const PHPCAS_LANG_DEFAULT = self::PHPCAS_LANG_ENGLISH;

    /** @} */

    /**
     * This variable is used by the interface class CAS.
     *
     * @var Client
     * @hideinitializer
     */
    private static $_PHPCAS_CLIENT;

    /**
     * This variable is used to store where the initializer is called from
     * (to print a comprehensive error in case of multiple calls).
     *
     * @var string
     * @hideinitializer
     */
    private static $_PHPCAS_INIT_CALL;

    /**
     * This variable is used to store CAS debug mode.
     *
     * @var array
     * @hideinitializer
     */
    private static $_PHPCAS_DEBUG;

    /**
     * This variable is used to enable verbose mode
     * This prevents debug info to be show to the user. Since it's a security
     * feature the default is false.
     *
     * @var bool
     * @hideinitializer
     */
    private static $_PHPCAS_VERBOSE = false;

    // ########################################################################
    //  INITIALIZATION
    // ########################################################################

    /**
     * @addtogroup publicInit
     * @{
     */

    /**
     * phpCAS client initializer.
     *
     * @param string $server_version  the version of the CAS server
     * @param string $server_hostname the hostname of the CAS server
     * @param string $server_port     the port the CAS server is running on
     * @param string $server_uri      the URI the CAS server is responding on
     * @param bool   $changeSessionID Allow CAS to change the session_id (Single
     * Sign Out/handleLogoutRequests is based on that change)
     *
     * @return Client a newly created Client object
     * @note Only one of the static::client() and static::proxy functions should be
     * called, only once, and before all other methods (except static::getVersion()
     * and static::setDebug()).
     */
    public static function client(
        $server_version,
        $server_hostname,
        $server_port,
        $server_uri,
        $changeSessionID = true
    ) {
        static::traceBegin();
        if (is_object(static::$_PHPCAS_CLIENT)) {
            static::error(
                static::$_PHPCAS_INIT_CALL['method'].'() has already been called (at '
                .static::$_PHPCAS_INIT_CALL['file'].':'.static::$_PHPCAS_INIT_CALL['line'].')'
            );
        }

        // store where the initializer is called from
        $dbg = debug_backtrace();
        static::$_PHPCAS_INIT_CALL = [
            'done' => true,
            'file' => $dbg[0]['file'],
            'line' => $dbg[0]['line'],
            'method' => __CLASS__.'::'.__FUNCTION__,
        ];

        // initialize the object $_PHPCAS_CLIENT
        try {
            static::$_PHPCAS_CLIENT = new Client(
                $server_version,
                false,
                $server_hostname,
                $server_port,
                $server_uri,
                $changeSessionID
            );
        } catch (Exception $e) {
            static::error(get_class($e).': '.$e->getMessage());
        }
        static::traceEnd();
    }

    /**
     * phpCAS proxy initializer.
     *
     * @param string $server_version  the version of the CAS server
     * @param string $server_hostname the hostname of the CAS server
     * @param string $server_port     the port the CAS server is running on
     * @param string $server_uri      the URI the CAS server is responding on
     * @param bool   $changeSessionID Allow CAS to change the session_id (Single
     * Sign Out/handleLogoutRequests is based on that change)
     *
     * @return Client a newly created Client object
     * @note Only one of the static::client() and static::proxy functions should be
     * called, only once, and before all other methods (except static::getVersion()
     * and static::setDebug()).
     */
    public static function proxy(
        $server_version,
        $server_hostname,
        $server_port,
        $server_uri,
        $changeSessionID = true
    ) {
        static::traceBegin();
        if (is_object(static::$_PHPCAS_CLIENT)) {
            static::error(
                static::$_PHPCAS_INIT_CALL['method'].'() has already been called (at '
                .static::$_PHPCAS_INIT_CALL['file'].':'.static::$_PHPCAS_INIT_CALL['line'].')'
            );
        }

        // store where the initializer is called from
        $dbg = debug_backtrace();
        static::$_PHPCAS_INIT_CALL = [
            'done' => true,
            'file' => $dbg[0]['file'],
            'line' => $dbg[0]['line'],
            'method' => __CLASS__.'::'.__FUNCTION__,
        ];

        // initialize the object $_CAS_CLIENT
        try {
            static::$_PHPCAS_CLIENT = new Client(
                $server_version,
                true,
                $server_hostname,
                $server_port,
                $server_uri,
                $changeSessionID
            );
        } catch (Exception $e) {
            static::error(get_class($e).': '.$e->getMessage());
        }
        static::traceEnd();
    }

    /**
     * Answer whether or not the client or proxy has been initialized.
     *
     * @return bool
     */
    public static function isInitialized()
    {
        return (is_object(static::$_PHPCAS_CLIENT));
    }

    /** @} */

    // ########################################################################
    //  DEBUGGING
    // ########################################################################

    /**
     * @addtogroup publicDebug
     * @{
     */

    /**
     * Set/unset debug mode.
     *
     * @param string $filename the name of the file used for logging, or false
     * to stop debugging.
     *
     * @return void
     */
    public static function setDebug($filename = '')
    {
        if ($filename != false && gettype($filename) != 'string') {
            static::error('type mismatched for parameter $dbg (should be false or the name of the log file)');
        }
        if ($filename === false) {
            static::$_PHPCAS_DEBUG['filename'] = false;
        } else {
            if (empty($filename)) {
                if (preg_match('/^Win.*/', getenv('OS'))) {
                    if (isset($_ENV['TMP'])) {
                        $debugDir = $_ENV['TMP'].'/';
                    } else {
                        $debugDir = '';
                    }
                } else {
                    $debugDir = static::getDefaultDebugDir();
                }
                $filename = $debugDir.'CAS.log';
            }

            if (empty(static::$_PHPCAS_DEBUG['unique_id'])) {
                static::$_PHPCAS_DEBUG['unique_id'] = substr(strtoupper(md5(uniqid(''))), 0, 4);
            }

            static::$_PHPCAS_DEBUG['filename'] = $filename;
            static::$_PHPCAS_DEBUG['indent'] = 0;

            static::trace('START ('.date('Y-m-d H:i:s').') CAS-'.static::PHPCAS_VERSION.' ******************');
        }
    }

    /**
     * Return the default debug directory.
     *
     * @return string
     */
    public static function getDefaultDebugDir()
    {
        return static::getTmpDir().'/';
    }

    /**
     * The default directory for the debug file under Unix.
     *
     * @return string
     */
    public static function getTmpDir()
    {
        if (! empty($_ENV['TMP'])) {
            return realpath($_ENV['TMP']);
        }

        if (! empty($_ENV['TMPDIR'])) {
            return realpath($_ENV['TMPDIR']);
        }

        if (! empty($_ENV['TEMP'])) {
            return realpath($_ENV['TEMP']);
        }

        return '/tmp';
    }

    /**
     * Enable verbose errors messages in the website output
     * This is a security relevant since internal status info may leak an may
     * help an attacker. Default is therefore false.
     *
     * @param bool $verbose enable verbose output
     *
     * @return void
     */
    public static function setVerbose($verbose)
    {
        if ($verbose === true) {
            static::$_PHPCAS_VERBOSE = true;
        } else {
            static::$_PHPCAS_VERBOSE = false;
        }
    }

    /**
     * Show is verbose mode is on.
     *
     * @return bool verbose
     */
    public static function getVerbose()
    {
        return static::$_PHPCAS_VERBOSE;
    }

    /**
     * Logs a string in debug mode.
     *
     * @param string $str the string to write
     *
     * @return void
     * @private
     */
    public static function log($str)
    {
        $indent_str = '.';

        if (! empty(static::$_PHPCAS_DEBUG['filename'])) {
            // Check if file exists and modify file permissions to be only
            // readable by the web server
            if (! file_exists(static::$_PHPCAS_DEBUG['filename'])) {
                touch(static::$_PHPCAS_DEBUG['filename']);
                // Chmod will fail on windows
                @chmod(static::$_PHPCAS_DEBUG['filename'], 0600);
            }
            for ($i = 0; $i < static::$_PHPCAS_DEBUG['indent']; $i++) {
                $indent_str .= '|    ';
            }
            // allow for multiline output with proper indenting. Useful for
            // dumping cas answers etc.
            $str2 = str_replace("\n", "\n".static::$_PHPCAS_DEBUG['unique_id'].' '.$indent_str, $str);
            error_log(static::$_PHPCAS_DEBUG['unique_id'].' '.$indent_str.$str2."\n", 3, static::$_PHPCAS_DEBUG['filename']);
        }
    }

    /**
     * This method is used by interface methods to print an error and where the
     * function was originally called from.
     *
     * @param string $msg the message to print
     *
     * @return void
     * 
     * @throws GracefulTerminationException
     */
    public static function error($msg)
    {
        static::traceBegin();
        $dbg = debug_backtrace();
        $function = '?';
        $file = '?';
        $line = '?';
        if (is_array($dbg)) {
            for ($i = 1; $i < sizeof($dbg); $i++) {
                if (is_array($dbg[$i]) && isset($dbg[$i]['class'])) {
                    if ($dbg[$i]['class'] == __CLASS__) {
                        $function = $dbg[$i]['function'];
                        $file = $dbg[$i]['file'];
                        $line = $dbg[$i]['line'];
                    }
                }
            }
        }
        if (static::$_PHPCAS_VERBOSE) {
            echo "<br />\n<b>CAS error</b>: <span style=\"color: #FF0000\"><b>".__CLASS__.'::'.$function.'(): '.htmlentities($msg).'</b></span> in <b>'.$file.'</b> on line <b>'.$line."</b><br />\n";
        } else {
            echo "<br />\n<b>Error</b>: <span style=\"color: #FF0000\"><b>".static::DEFAULT_ERROR."</b></span><br />\n";
        }
        static::trace($msg.' in '.$file.'on line '.$line);
        static::traceEnd();

        throw new GracefulTerminationException(__CLASS__.'::'.$function.'(): '.$msg);
    }

    /**
     * This method is used to log something in debug mode.
     *
     * @param string $str string to log
     *
     * @return void
     */
    public static function trace($str)
    {
        $dbg = debug_backtrace();
        static::log($str.' ['.basename($dbg[0]['file']).':'.$dbg[0]['line'].']');
    }

    /**
     * This method is used to indicate the start of the execution of a function
     * in debug mode.
     *
     * @return void
     */
    public static function traceBegin()
    {
        $dbg = debug_backtrace();
        $str = '=> ';
        if (! empty($dbg[1]['class'])) {
            $str .= $dbg[1]['class'].'::';
        }
        $str .= $dbg[1]['function'].'(';
        if (is_array($dbg[1]['args'])) {
            foreach ($dbg[1]['args'] as $index => $arg) {
                if ($index != 0) {
                    $str .= ', ';
                }
                if (is_object($arg)) {
                    $str .= get_class($arg);
                } else {
                    $str .= str_replace(["\r\n", "\n", "\r"], '', var_export($arg, true));
                }
            }
        }
        if (isset($dbg[1]['file'])) {
            $file = basename($dbg[1]['file']);
        } else {
            $file = 'unknown_file';
        }
        if (isset($dbg[1]['line'])) {
            $line = $dbg[1]['line'];
        } else {
            $line = 'unknown_line';
        }
        $str .= ') ['.$file.':'.$line.']';
        static::log($str);
        if (! isset(static::$_PHPCAS_DEBUG['indent'])) {
            static::$_PHPCAS_DEBUG['indent'] = 0;
        } else {
            static::$_PHPCAS_DEBUG['indent']++;
        }
    }

    /**
     * This method is used to indicate the end of the execution of a function in
     * debug mode.
     *
     * @param string $res the result of the function
     *
     * @return void
     */
    public static function traceEnd($res = '')
    {
        if (empty(static::$_PHPCAS_DEBUG['indent'])) {
            static::$_PHPCAS_DEBUG['indent'] = 0;
        } else {
            static::$_PHPCAS_DEBUG['indent']--;
        }
        $str = '';
        if (is_object($res)) {
            $str .= '<= '.get_class($res);
        } else {
            $str .= '<= '.str_replace(["\r\n", "\n", "\r"], '', var_export($res, true));
        }

        static::log($str);
    }

    /**
     * This method is used to indicate the end of the execution of the program.
     *
     * @return void
     */
    public static function traceExit()
    {
        static::log('exit()');
        while (static::$_PHPCAS_DEBUG['indent'] > 0) {
            static::log('-');
            static::$_PHPCAS_DEBUG['indent']--;
        }
    }

    /** @} */

    // ########################################################################
    //  INTERNATIONALIZATION
    // ########################################################################

    /**
     * @addtogroup publicLang
     * @{
     */

    /**
     * This method is used to set the language used by phpCAS.
     *
     * @param string $lang string representing the language.
     *
     * @return void
     *
     * @sa PHPCAS_LANG_FRENCH, PHPCAS_LANG_ENGLISH
     * @note Can be called only once.
     */
    public static function setLang($lang)
    {
        static::_validateClientExists();

        try {
            static::$_PHPCAS_CLIENT->setLang($lang);
        } catch (Exception $e) {
            static::error(get_class($e).': '.$e->getMessage());
        }
    }

    /** @} */

    // ########################################################################
    //  VERSION
    // ########################################################################

    /**
     * @addtogroup public
     * @{
     */

    /**
     * This method returns the CAS version.
     *
     * @return string the CAS version.
     */
    public static function getVersion()
    {
        return static::PHPCAS_VERSION;
    }

    /** @} */

    // ########################################################################
    //  HTML OUTPUT
    // ########################################################################

    /**
     * @addtogroup publicOutput
     */

    /**
     * This method sets the HTML header used for all outputs.
     *
     * @param string $header the HTML header.
     *
     * @return void
     */
    public static function setHTMLHeader($header)
    {
        static::_validateClientExists();

        try {
            static::$_PHPCAS_CLIENT->setHTMLHeader($header);
        } catch (Exception $e) {
            static::error(get_class($e).': '.$e->getMessage());
        }
    }

    /**
     * This method sets the HTML footer used for all outputs.
     *
     * @param string $footer the HTML footer.
     *
     * @return void
     */
    public static function setHTMLFooter($footer)
    {
        static::_validateClientExists();

        try {
            static::$_PHPCAS_CLIENT->setHTMLFooter($footer);
        } catch (Exception $e) {
            static::error(get_class($e).': '.$e->getMessage());
        }
    }

    /** @} */

    // ########################################################################
    //  PGT STORAGE
    // ########################################################################

    /**
     * @addtogroup publicPGTStorage
     * @{
     */

    /**
     * This method can be used to set a custom PGT storage object.
     *
     * @param AbstractStorage $storage a PGT storage object that inherits from
     *                                 the AbstractStorage class
     *
     * @return void
     */
    public static function setPGTStorage(AbstractStorage $storage)
    {
        static::traceBegin();
        static::_validateProxyExists();

        try {
            static::$_PHPCAS_CLIENT->setPGTStorage($storage);
        } catch (Exception $e) {
            static::error(get_class($e).': '.$e->getMessage());
        }
        static::traceEnd();
    }

    /**
     * This method is used to tell phpCAS to store the response of the
     * CAS server to PGT requests in a database.
     *
     * @param string $dsn_or_pdo     a dsn string to use for creating a PDO
     *                               object or a PDO object
     * @param string $username       the username to use when connecting to the
     *                               database
     * @param string $password       the password to use when connecting to the
     *                               database
     * @param string $table          the table to use for storing and retrieving
     *                               PGT's
     * @param string $driver_options any driver options to use when connecting
     *                               to the database
     *
     * @return void
     */
    public static function setPGTStorageDb(
        $dsn_or_pdo,
        $username = '',
        $password = '',
        $table = '',
        $driver_options = null
    ) {
        static::traceBegin();
        static::_validateProxyExists();

        try {
            static::$_PHPCAS_CLIENT->setPGTStorageDb($dsn_or_pdo, $username, $password, $table, $driver_options);
        } catch (Exception $e) {
            static::error(get_class($e).': '.$e->getMessage());
        }
        static::traceEnd();
    }

    /**
     * This method is used to tell phpCAS to store the response of the
     * CAS server to PGT requests onto the filesystem.
     *
     * @param string $path the path where the PGT's should be stored
     *
     * @return void
     */
    public static function setPGTStorageFile($path = '')
    {
        static::traceBegin();
        static::_validateProxyExists();

        try {
            static::$_PHPCAS_CLIENT->setPGTStorageFile($path);
        } catch (Exception $e) {
            static::error(get_class($e).': '.$e->getMessage());
        }
        static::traceEnd();
    }

    /** @} */

    // ########################################################################
    // ACCESS TO EXTERNAL SERVICES
    // ########################################################################

    /**
     * @addtogroup publicServices
     * @{
     */

    /**
     * Answer a proxy-authenticated service handler.
     *
     * @param string $type The service type. One of
     * PHPCAS_PROXIED_SERVICE_HTTP_GET; PHPCAS_PROXIED_SERVICE_HTTP_POST;
     * PHPCAS_PROXIED_SERVICE_IMAP
     *
     * @return ProxiedService|Imap|Html|Get|Post
     * @throws InvalidArgumentException If the service type is unknown.
     */
    public static function getProxiedService($type)
    {
        static::traceBegin();
        static::_validateProxyExists();

        $res = null;
        try {
            $res = static::$_PHPCAS_CLIENT->getProxiedService($type);
        } catch (Exception $e) {
            static::error(get_class($e).': '.$e->getMessage());
        }

        static::traceEnd();

        return $res;
    }

    /**
     * Initialize a proxied-service handler with the proxy-ticket it should use.
     *
     * @param ProxiedService $proxiedService Proxied Service Handler
     *
     * @return void
     * @throws ProxyTicketException If there is a proxy-ticket failure.
     *		The code of the Exception will be one of:
     *			PHPCAS_SERVICE_PT_NO_SERVER_RESPONSE
     *			PHPCAS_SERVICE_PT_BAD_SERVER_RESPONSE
     *			PHPCAS_SERVICE_PT_FAILURE
     */
    public static function initializeProxiedService(ProxiedService $proxiedService)
    {
        static::_validateProxyExists();

        try {
            static::$_PHPCAS_CLIENT->initializeProxiedService($proxiedService);
        } catch (Exception $e) {
            static::error(get_class($e).': '.$e->getMessage());
        }
    }

    /**
     * This method is used to access an HTTP[S] service.
     *
     * @param string $url       the service to access.
     * @param string &$err_code an error code Possible values are
     * PHPCAS_SERVICE_OK (on success), PHPCAS_SERVICE_PT_NO_SERVER_RESPONSE,
     * PHPCAS_SERVICE_PT_BAD_SERVER_RESPONSE, PHPCAS_SERVICE_PT_FAILURE,
     * PHPCAS_SERVICE_NOT_AVAILABLE.
     * @param string &$output   the output of the service (also used to give an
     * error message on failure).
     *
     * @return bool true on success, false otherwise (in this later case,
     * $err_code gives the reason why it failed and $output contains an error
     * message).
     */
    public static function serviceWeb($url, &$err_code, &$output)
    {
        static::traceBegin();
        static::_validateProxyExists();

        $res = null;
        try {
            $res = static::$_PHPCAS_CLIENT->serviceWeb($url, $err_code, $output);
        } catch (Exception $e) {
            static::error(get_class($e).': '.$e->getMessage());
        }

        static::traceEnd($res);

        return $res;
    }

    /**
     * This method is used to access an IMAP/POP3/NNTP service.
     *
     * @param string $url       a string giving the URL of the service,
     * including the mailing box for IMAP URLs, as accepted by imap_open().
     * @param string $service   a string giving for CAS retrieve Proxy ticket
     * @param string $flags     options given to imap_open().
     * @param string &$err_code an error code Possible values are
     * PHPCAS_SERVICE_OK (on success), PHPCAS_SERVICE_PT_NO_SERVER_RESPONSE,
     * PHPCAS_SERVICE_PT_BAD_SERVER_RESPONSE, PHPCAS_SERVICE_PT_FAILURE,
     * PHPCAS_SERVICE_NOT_AVAILABLE.
     * @param string &$err_msg  an error message on failure
     * @param string &$pt       the Proxy Ticket (PT) retrieved from the CAS
     * server to access the URL on success, false on error).
     *
     * @return object IMAP stream on success, false otherwise (in this later
     * case, $err_code gives the reason why it failed and $err_msg contains an
     * error message).
     */
    public static function serviceMail($url, $service, $flags, &$err_code, &$err_msg, &$pt)
    {
        static::traceBegin();
        static::_validateProxyExists();

        $res = null;
        try {
            $res = static::$_PHPCAS_CLIENT->serviceMail($url, $service, $flags, $err_code, $err_msg, $pt);
        } catch (Exception $e) {
            static::error(get_class($e).': '.$e->getMessage());
        }

        static::traceEnd($res);

        return $res;
    }

    /** @} */

    // ########################################################################
    //  AUTHENTICATION
    // ########################################################################

    /**
     * @addtogroup publicAuth
     * @{
     */

    /**
     * Set the times authentication will be cached before really accessing the
     * CAS server in gateway mode:
     * - -1: check only once, and then never again (until you pre-login)
     * - 0: always check
     * - n: check every "n" time.
     *
     * @param int $n an integer.
     *
     * @return void
     */
    public static function setCacheTimesForAuthRecheck($n)
    {
        static::_validateClientExists();

        try {
            static::$_PHPCAS_CLIENT->setCacheTimesForAuthRecheck($n);
        } catch (Exception $e) {
            static::error(get_class($e).': '.$e->getMessage());
        }
    }

    /**
     * Set a callback function to be run when a user authenticates.
     *
     * The callback function will be passed a $logoutTicket as its first
     * parameter, followed by any $additionalArgs you pass. The $logoutTicket
     * parameter is an opaque string that can be used to map the session-id to
     * logout request in order to support single-signout in applications that
     * manage their own sessions (rather than letting CAS start the session).
     *
     * static::forceAuthentication() will always exit and forward client unless
     * they are already authenticated. To perform an action at the moment the user
     * logs in (such as registering an account, performing logging, etc), register
     * a callback function here.
     *
     * @param string $function       Callback function
     * @param array  $additionalArgs optional array of arguments
     *
     * @return void
     */
    public static function setPostAuthenticateCallback($function, array $additionalArgs = [])
    {
        static::_validateClientExists();

        static::$_PHPCAS_CLIENT->setPostAuthenticateCallback($function, $additionalArgs);
    }

    /**
     * Set a callback function to be run when a single-signout request is
     * received. The callback function will be passed a $logoutTicket as its
     * first parameter, followed by any $additionalArgs you pass. The
     * $logoutTicket parameter is an opaque string that can be used to map a
     * session-id to the logout request in order to support single-signout in
     * applications that manage their own sessions (rather than letting CAS
     * start and destroy the session).
     *
     * @param string $function       Callback function
     * @param array  $additionalArgs optional array of arguments
     *
     * @return void
     */
    public static function setSingleSignoutCallback($function, array $additionalArgs = [])
    {
        static::_validateClientExists();

        static::$_PHPCAS_CLIENT->setSingleSignoutCallback($function, $additionalArgs);
    }

    /**
     * This method is called to check if the user is already authenticated
     * locally or has a global cas session. A already existing cas session is
     * determined by a cas gateway call.(cas login call without any interactive
     * prompt).
     *
     * @return true when the user is authenticated, false when a previous
     * gateway login failed or the function will not return if the user is
     * redirected to the cas server for a gateway login attempt
     */
    public static function checkAuthentication()
    {
        static::traceBegin();
        static::_validateClientExists();

        $auth = static::$_PHPCAS_CLIENT->checkAuthentication();

        // store where the authentication has been checked and the result
        static::$_PHPCAS_CLIENT->markAuthenticationCall($auth);

        static::traceEnd($auth);

        return $auth;
    }

    /**
     * This method is called to force authentication if the user was not already
     * authenticated. If the user is not authenticated, halt by redirecting to
     * the CAS server.
     *
     * @return bool Authentication
     */
    public static function forceAuthentication()
    {
        static::traceBegin();
        static::_validateClientExists();
        $auth = static::$_PHPCAS_CLIENT->forceAuthentication();

        // store where the authentication has been checked and the result
        static::$_PHPCAS_CLIENT->markAuthenticationCall($auth);

        // if (! $auth) {
        //     static::trace('user is not authenticated, redirect to the CAS server');
        //     static::$_PHPCAS_CLIENT->forceAuthentication();
        // } else {
        //     static::trace('no need to authenticate (user `' . static::getUser() . '\' is already authenticated)');
        // }

        static::traceEnd();

        return $auth;
    }

    /**
     * This method is called to renew the authentication.
     *
     * @return void
     **/
    public static function renewAuthentication()
    {
        static::traceBegin();
        static::_validateClientExists();

        $auth = static::$_PHPCAS_CLIENT->renewAuthentication();

        // store where the authentication has been checked and the result
        static::$_PHPCAS_CLIENT->markAuthenticationCall($auth);

        static::traceEnd();
    }

    /**
     * This method is called to check if the user is authenticated (previously or by
     * tickets given in the URL).
     *
     * @return true when the user is authenticated.
     */
    public static function isAuthenticated()
    {
        static::traceBegin();
        static::_validateClientExists();

        // call the isAuthenticated method of the $_PHPCAS_CLIENT object
        $auth = static::$_PHPCAS_CLIENT->isAuthenticated();

        // store where the authentication has been checked and the result
        static::$_PHPCAS_CLIENT->markAuthenticationCall($auth);

        static::traceEnd($auth);

        return $auth;
    }

    /**
     * Checks whether authenticated based on $_SESSION. Useful to avoid
     * server calls.
     *
     * @return bool true if authenticated, false otherwise.
     * @since 0.4.22 by Brendan Arnold
     */
    public static function isSessionAuthenticated()
    {
        static::_validateClientExists();

        return (static::$_PHPCAS_CLIENT->isSessionAuthenticated());
    }

    /**
     * This method returns the CAS user's login name.
     *
     * @return string the login name of the authenticated user
     * @warning should only be called after static::forceAuthentication()
     * or static::checkAuthentication().
     * */
    public static function getUser()
    {
        static::_validateClientExists();

        try {
            return static::$_PHPCAS_CLIENT->getUser();
        } catch (Exception $e) {
            static::error(get_class($e).': '.$e->getMessage());
        }

        return;
    }

    /**
     * Answer attributes about the authenticated user.
     *
     * @warning should only be called after static::forceAuthentication()
     * or static::checkAuthentication().
     *
     * @return array
     */
    public static function getAttributes()
    {
        static::_validateClientExists();

        try {
            return static::$_PHPCAS_CLIENT->getAttributes();
        } catch (Exception $e) {
            static::error(get_class($e).': '.$e->getMessage());
        }

        return;
    }

    /**
     * Answer true if there are attributes for the authenticated user.
     *
     * @warning should only be called after static::forceAuthentication()
     * or static::checkAuthentication().
     *
     * @return bool
     */
    public static function hasAttributes()
    {
        static::_validateClientExists();

        try {
            return static::$_PHPCAS_CLIENT->hasAttributes();
        } catch (Exception $e) {
            static::error(get_class($e).': '.$e->getMessage());
        }

        return;
    }

    /**
     * Answer true if an attribute exists for the authenticated user.
     *
     * @param string $key attribute name
     *
     * @return bool
     * @warning should only be called after static::forceAuthentication()
     * or static::checkAuthentication().
     */
    public static function hasAttribute($key)
    {
        static::_validateClientExists();

        try {
            return static::$_PHPCAS_CLIENT->hasAttribute($key);
        } catch (Exception $e) {
            static::error(get_class($e).': '.$e->getMessage());
        }

        return;
    }

    /**
     * Answer an attribute for the authenticated user.
     *
     * @param string $key attribute name
     *
     * @return mixed string for a single value or an array if multiple values exist.
     * @warning should only be called after static::forceAuthentication()
     * or static::checkAuthentication().
     */
    public static function getAttribute($key)
    {
        static::_validateClientExists();

        try {
            return static::$_PHPCAS_CLIENT->getAttribute($key);
        } catch (Exception $e) {
            static::error(get_class($e).': '.$e->getMessage());
        }

        return;
    }

    /**
     * Handle logout requests.
     *
     * @param bool  $check_client    additional safety check
     * @param array $allowed_clients array of allowed clients
     *
     * @return void
     */
    public static function handleLogoutRequests($check_client = true, $allowed_clients = null)
    {
        static::_validateClientExists();

        static::$_PHPCAS_CLIENT->handleLogoutRequests($check_client, $allowed_clients);
    }

    /**
     * This method returns the URL to be used to login.
     * or static::isAuthenticated().
     *
     * @return string the login name of the authenticated user
     */
    public static function getServerLoginURL()
    {
        static::_validateClientExists();

        return static::$_PHPCAS_CLIENT->getServerLoginURL();
    }

    /**
     * Set the login URL of the CAS server.
     *
     * @param string $url the login URL
     *
     * @return void
     * @since 0.4.21 by Wyman Chan
     */
    public static function setServerLoginURL($url = '')
    {
        static::traceBegin();
        static::_validateClientExists();

        try {
            static::$_PHPCAS_CLIENT->setServerLoginURL($url);
        } catch (Exception $e) {
            static::error(get_class($e).': '.$e->getMessage());
        }

        static::traceEnd();
    }

    /**
     * Set the serviceValidate URL of the CAS server.
     * Used only in CAS 1.0 validations.
     *
     * @param string $url the serviceValidate URL
     *
     * @return void
     */
    public static function setServerServiceValidateURL($url = '')
    {
        static::traceBegin();
        static::_validateClientExists();

        try {
            static::$_PHPCAS_CLIENT->setServerServiceValidateURL($url);
        } catch (Exception $e) {
            static::error(get_class($e).': '.$e->getMessage());
        }

        static::traceEnd();
    }

    /**
     * Set the proxyValidate URL of the CAS server.
     * Used for all CAS 2.0 validations.
     *
     * @param string $url the proxyValidate URL
     *
     * @return void
     */
    public static function setServerProxyValidateURL($url = '')
    {
        static::traceBegin();
        static::_validateClientExists();

        try {
            static::$_PHPCAS_CLIENT->setServerProxyValidateURL($url);
        } catch (Exception $e) {
            static::error(get_class($e).': '.$e->getMessage());
        }

        static::traceEnd();
    }

    /**
     * Set the samlValidate URL of the CAS server.
     *
     * @param string $url the samlValidate URL
     *
     * @return void
     */
    public static function setServerSamlValidateURL($url = '')
    {
        static::traceBegin();
        static::_validateClientExists();

        try {
            static::$_PHPCAS_CLIENT->setServerSamlValidateURL($url);
        } catch (Exception $e) {
            static::error(get_class($e).': '.$e->getMessage());
        }

        static::traceEnd();
    }

    /**
     * This method returns the URL to be used to login.
     * or static::isAuthenticated().
     *
     * @return string the login name of the authenticated user
     */
    public static function getServerLogoutURL()
    {
        static::_validateClientExists();

        return static::$_PHPCAS_CLIENT->getServerLogoutURL();
    }

    /**
     * Set the logout URL of the CAS server.
     *
     * @param string $url the logout URL
     *
     * @return void
     * @since 0.4.21 by Wyman Chan
     */
    public static function setServerLogoutURL($url = '')
    {
        static::traceBegin();
        static::_validateClientExists();

        try {
            static::$_PHPCAS_CLIENT->setServerLogoutURL($url);
        } catch (Exception $e) {
            static::error(get_class($e).': '.$e->getMessage());
        }

        static::traceEnd();
    }

    /**
     * This method is used to logout from CAS.
     *
     * @param string $params an array that contains the optional url and
     * service parameters that will be passed to the CAS server
     *
     * @return void
     */
    public static function logout($params = '')
    {
        static::traceBegin();
        static::_validateClientExists();

        $parsedParams = [];
        if ($params != '') {
            if (is_string($params)) {
                static::error('method `'.__CLASS__.'::logout($url)\' is now deprecated, use `'.__CLASS__.'::logoutWithUrl($url)\' instead');
            }
            if (! is_array($params)) {
                static::error('type mismatched for parameter $params (should be `array\')');
            }
            foreach ($params as $key => $value) {
                if ($key != 'service' && $key != 'url') {
                    static::error('only `url\' and `service\' parameters are allowed for method `'.__CLASS__.'::logout($params)\'');
                }
                $parsedParams[$key] = $value;
            }
        }
        static::$_PHPCAS_CLIENT->logout($parsedParams);
        // never reached
        static::traceEnd();
    }

    /**
     * This method is used to logout from CAS. Halts by redirecting to the CAS
     * server.
     *
     * @param string $service a URL that will be transmitted to the CAS server
     *
     * @return void
     */
    public static function logoutWithRedirectService($service)
    {
        static::traceBegin();
        static::_validateClientExists();

        if (! is_string($service)) {
            static::error('type mismatched for parameter $service (should be `string\')');
        }
        static::$_PHPCAS_CLIENT->logout(['service' => $service]);
        // never reached
        static::traceEnd();
    }

    /**
     * This method is used to logout from CAS. Halts by redirecting to the CAS
     * server.
     *
     * @param string $url a URL that will be transmitted to the CAS server
     *
     * @return void
     * @deprecated The url parameter has been removed from the CAS server as of
     * version 3.3.5.1
     */
    public static function logoutWithUrl($url)
    {
        trigger_error('Function deprecated for cas servers >= 3.3.5.1', E_USER_DEPRECATED);
        static::traceBegin();
        if (! is_object(static::$_PHPCAS_CLIENT)) {
            static::error('this method should only be called after '.__CLASS__.'::client() or'.__CLASS__.'::proxy()');
        }
        if (! is_string($url)) {
            static::error('type mismatched for parameter $url (should be `string\')');
        }
        static::$_PHPCAS_CLIENT->logout(['url' => $url]);
        // never reached
        static::traceEnd();
    }

    /**
     * This method is used to logout from CAS. Halts by redirecting to the CAS
     * server.
     *
     * @param string $service a URL that will be transmitted to the CAS server
     * @param string $url     a URL that will be transmitted to the CAS server
     *
     * @return void
     *
     * @deprecated The url parameter has been removed from the CAS server as of
     * version 3.3.5.1
     */
    public static function logoutWithRedirectServiceAndUrl($service, $url)
    {
        trigger_error('Function deprecated for cas servers >= 3.3.5.1', E_USER_DEPRECATED);
        static::traceBegin();
        static::_validateClientExists();

        if (! is_string($service)) {
            static::error('type mismatched for parameter $service (should be `string\')');
        }
        if (! is_string($url)) {
            static::error('type mismatched for parameter $url (should be `string\')');
        }
        static::$_PHPCAS_CLIENT->logout(
            [
                'service' => $service,
                'url' => $url,
            ]
        );
        // never reached
        static::traceEnd();
    }

    /**
     * Set the fixed URL that will be used by the CAS server to transmit the
     * PGT. When this method is not called, a phpCAS script uses its own URL
     * for the callback.
     *
     * @param string $url the URL
     *
     * @return void
     */
    public static function setFixedCallbackURL($url = '')
    {
        static::traceBegin();
        static::_validateProxyExists();

        try {
            static::$_PHPCAS_CLIENT->setCallbackURL($url);
        } catch (Exception $e) {
            static::error(get_class($e).': '.$e->getMessage());
        }

        static::traceEnd();
    }

    /**
     * Set the fixed URL that will be set as the CAS service parameter. When this
     * method is not called, a phpCAS script uses its own URL.
     *
     * @param string $url the URL
     *
     * @return void
     */
    public static function setFixedServiceURL($url)
    {
        static::traceBegin();
        static::_validateProxyExists();

        try {
            static::$_PHPCAS_CLIENT->setURL($url);
        } catch (Exception $e) {
            static::error(get_class($e).': '.$e->getMessage());
        }

        static::traceEnd();
    }

    /**
     * Get the URL that is set as the CAS service parameter.
     *
     * @return string Service Url
     */
    public static function getServiceURL()
    {
        static::_validateProxyExists();

        return (static::$_PHPCAS_CLIENT->getURL());
    }

    /**
     * Retrieve a Proxy Ticket from the CAS server.
     *
     * @param string $target_service Url string of service to proxy
     * @param string &$err_code      error code
     * @param string &$err_msg       error message
     *
     * @return string Proxy Ticket
     */
    public static function retrievePT($target_service, &$err_code, &$err_msg)
    {
        static::_validateProxyExists();

        try {
            return (static::$_PHPCAS_CLIENT->retrievePT($target_service, $err_code, $err_msg));
        } catch (Exception $e) {
            static::error(get_class($e).': '.$e->getMessage());
        }

        return;
    }

    /**
     * Set the certificate of the CAS server CA and if the CN should be properly
     * verified.
     *
     * @param string $cert        CA certificate file name
     * @param bool   $validate_cn Validate CN in certificate (default true)
     *
     * @return void
     */
    public static function setCasServerCACert($cert, $validate_cn = true)
    {
        static::traceBegin();
        static::_validateClientExists();

        try {
            static::$_PHPCAS_CLIENT->setCasServerCACert($cert, $validate_cn);
        } catch (Exception $e) {
            static::error(get_class($e).': '.$e->getMessage());
        }

        static::traceEnd();
    }

    /**
     * Set no SSL validation for the CAS server.
     *
     * @return void
     */
    public static function setNoCasServerValidation()
    {
        static::traceBegin();
        static::_validateClientExists();

        static::trace('You have configured no validation of the legitimacy of the CAS server. This is not recommended for production use.');
        static::$_PHPCAS_CLIENT->setNoCasServerValidation();
        static::traceEnd();
    }

    /**
     * Disable the removal of a CAS-Ticket from the URL when authenticating
     * DISABLING POSES A SECURITY RISK:
     * We normally remove the ticket by an additional redirect as a security
     * precaution to prevent a ticket in the HTTP_REFERRER or be carried over in
     * the URL parameter.
     *
     * @return void
     */
    public static function setNoClearTicketsFromUrl()
    {
        static::traceBegin();
        static::_validateClientExists();

        static::$_PHPCAS_CLIENT->setNoClearTicketsFromUrl();
        static::traceEnd();
    }

    /** @} */

    /**
     * Change CURL options.
     * CURL is used to connect through HTTPS to CAS server.
     *
     * @param string $key   the option key
     * @param string $value the value to set
     *
     * @return void
     */
    public static function setExtraCurlOption($key, $value)
    {
        static::traceBegin();
        static::_validateClientExists();

        static::$_PHPCAS_CLIENT->setExtraCurlOption($key, $value);
        static::traceEnd();
    }

    /**
     * If you want your service to be proxied you have to enable it (default
     * disabled) and define an acceptable list of proxies that are allowed to
     * proxy your service.
     *
     * Add each allowed proxy definition object. For the normal ProxyChain
     * class, the constructor takes an array of proxies to match. The list is in
     * reverse just as seen from the service. Proxies have to be defined in reverse
     * from the service to the user. If a user hits service A and gets proxied via
     * B to service C the list of acceptable on C would be array(B,A). The definition
     * of an individual proxy can be either a string or a regexp (preg_match is used)
     * that will be matched against the proxy list supplied by the cas server
     * when validating the proxy tickets. The strings are compared starting from
     * the beginning and must fully match with the proxies in the list.
     * Example:
     * 		CAS::allowProxyChain(new ProxyChain([
     *		    'https://app.example.com/'
     *		]));
     * 		CAS::allowProxyChain(new ProxyChain([
     *			'/^https:\/\/app[0-9]\.example\.com\/rest\//',
     *			'http://client.example.com/'
     *		]));
     *
     * For quick testing or in certain production scenarios you might want to
     * allow allow any other valid service to proxy your service. To do so, add
     * the "Any" chain:
     *		CAS::allowProxyChain(new Any);
     * THIS SETTING IS HOWEVER NOT RECOMMENDED FOR PRODUCTION AND HAS SECURITY
     * IMPLICATIONS: YOU ARE ALLOWING ANY SERVICE TO ACT ON BEHALF OF A USER
     * ON THIS SERVICE.
     *
     * @param ProxyChainInterface $proxy_chain A proxy-chain that will be
     * matched against the proxies requesting access
     *
     * @return void
     */
    public static function allowProxyChain(ProxyChainInterface $proxy_chain)
    {
        static::traceBegin();
        static::_validateClientExists();

        if (static::$_PHPCAS_CLIENT->getServerVersion() !== static::CAS_VERSION_2_0
            && static::$_PHPCAS_CLIENT->getServerVersion() !== static::CAS_VERSION_3_0
        ) {
            static::error('this method can only be used with the cas 2.0/3.0 protocols');
        }
        static::$_PHPCAS_CLIENT->getAllowedProxyChains()->allowProxyChain($proxy_chain);
        static::traceEnd();
    }

    /**
     * Answer an array of proxies that are sitting in front of this application.
     * This method will only return a non-empty array if we have received and
     * validated a Proxy Ticket.
     *
     * @return array
     * @since 6/25/09
     */
    public static function getProxies()
    {
        static::_validateProxyExists();

        return(static::$_PHPCAS_CLIENT->getProxies());
    }

    // ########################################################################
    // PGTIOU/PGTID and logoutRequest rebroadcasting
    // ########################################################################

    /**
     * Add a pgtIou/pgtId and logoutRequest rebroadcast node.
     *
     * @param string $rebroadcastNodeUrl The rebroadcast node URL. Can be
     * hostname or IP.
     *
     * @return void
     */
    public static function addRebroadcastNode($rebroadcastNodeUrl)
    {
        static::traceBegin();
        static::log('rebroadcastNodeUrl:'.$rebroadcastNodeUrl);
        static::_validateClientExists();

        try {
            static::$_PHPCAS_CLIENT->addRebroadcastNode($rebroadcastNodeUrl);
        } catch (Exception $e) {
            static::error(get_class($e).': '.$e->getMessage());
        }

        static::traceEnd();
    }

    /**
     * This method is used to add header parameters when rebroadcasting
     * pgtIou/pgtId or logoutRequest.
     *
     * @param String $header Header to send when rebroadcasting.
     *
     * @return void
     */
    public static function addRebroadcastHeader($header)
    {
        static::traceBegin();
        static::_validateClientExists();

        try {
            static::$_PHPCAS_CLIENT->addRebroadcastHeader($header);
        } catch (Exception $e) {
            static::error(get_class($e).': '.$e->getMessage());
        }

        static::traceEnd();
    }

    /**
     * Checks if a client already exists.
     *
     * @throws OutOfSequenceBeforeClientException
     *
     * @return void
     */
    private static function _validateClientExists()
    {
        if (! is_object(static::$_PHPCAS_CLIENT)) {
            throw new OutOfSequenceBeforeClientException();
        }
    }

    /**
     * Checks of a proxy client already exists.
     *
     * @throws OutOfSequenceBeforeProxyException
     *
     * @return void
     */
    private static function _validateProxyExists()
    {
        if (! is_object(static::$_PHPCAS_CLIENT)) {
            throw new OutOfSequenceBeforeProxyException();
        }
    }
}

// ########################################################################
// DOCUMENTATION
// ########################################################################

// ########################################################################
//  MAIN PAGE

/**
 * @mainpage
 *
 * The following pages only show the source documentation.
 *
 */

// ########################################################################
//  MODULES DEFINITION

/** @defgroup public User interface */

/** @defgroup publicInit Initialization
 *  @ingroup public */

/** @defgroup publicAuth Authentication
 *  @ingroup public */

/** @defgroup publicServices Access to external services
 *  @ingroup public */

/** @defgroup publicConfig Configuration
 *  @ingroup public */

/** @defgroup publicLang Internationalization
 *  @ingroup publicConfig */

/** @defgroup publicOutput HTML output
 *  @ingroup publicConfig */

/** @defgroup publicPGTStorage PGT storage
 *  @ingroup publicConfig */

/** @defgroup publicDebug Debugging
 *  @ingroup public */

/** @defgroup internal Implementation */

/** @defgroup internalAuthentication Authentication
 *  @ingroup internal */

/** @defgroup internalBasic CAS Basic client features (CAS 1.0, Service Tickets)
 *  @ingroup internal */

/** @defgroup internalProxy CAS Proxy features (CAS 2.0, Proxy Granting Tickets)
 *  @ingroup internal */

/** @defgroup internalSAML CAS SAML features (SAML 1.1)
 *  @ingroup internal */

/** @defgroup internalPGTStorage PGT storage
 *  @ingroup internalProxy */

/** @defgroup internalPGTStorageDb PGT storage in a database
 *  @ingroup internalPGTStorage */

/** @defgroup internalPGTStorageFile PGT storage on the filesystem
 *  @ingroup internalPGTStorage */

/** @defgroup internalCallback Callback from the CAS server
 *  @ingroup internalProxy */

/** @defgroup internalProxyServices Proxy other services
 *  @ingroup internalProxy */

/** @defgroup internalService CAS client features (CAS 2.0, Proxied service)
 *  @ingroup internal */

/** @defgroup internalConfig Configuration
 *  @ingroup internal */

/** @defgroup internalBehave Internal behaviour of phpCAS
 *  @ingroup internalConfig */

/** @defgroup internalOutput HTML output
 *  @ingroup internalConfig */

/** @defgroup internalLang Internationalization
 *  @ingroup internalConfig
 *
 * To add a new language:
 * - 1. define a new constant PHPCAS_LANG_XXXXXX in CAS/CAS.php
 * - 2. copy any file from CAS/languages to CAS/languages/XXXXXX.php
 * - 3. Make the translations
 */

/** @defgroup internalDebug Debugging
 *  @ingroup internal */

/** @defgroup internalMisc Miscellaneous
 *  @ingroup internal */

// ########################################################################
//  EXAMPLES

/** @example example_simple.php */
/** @example example_service.php */
/** @example example_service_that_proxies.php */
/** @example example_service_POST.php */
/** @example example_proxy_serviceWeb.php */
/** @example example_proxy_serviceWeb_chaining.php */
/** @example example_proxy_POST.php */
/** @example example_proxy_GET.php */
/** @example example_lang.php */
/** @example example_html.php */
/** @example example_pgt_storage_file.php */
/** @example example_pgt_storage_db.php */
/** @example example_gateway.php */
/** @example example_logout.php */
/** @example example_rebroadcast.php */
/** @example example_custom_urls.php */
/** @example example_advanced_saml11.php */