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
 * @author   Joachim Fritschi <jfritschi@freenet.de>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 */

/**
 * The CAS_Util_Validator class is used to validate the right use and
 * sequence of phpCAS library. It prevents users from using wrong config values
 * and using them in a wrong sequence. It is implemented as a singleton
 *
 * @class    CAS_Client
 * @category Authentication
 * @package  PhpCAS
 * @author   Joachim Fritschi <jfritschi@freenet.de>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 *
 */


final class CAS_Util_Validator
{

    private $_client;
    private $_init_call;
    private static $_instance = null;

    /**
     * Private constructor to enforce the singleton
     */
    private function __construct()
    {
    }

    /**
     * Get the single instance of this validator classe
     *
     * @return void
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    /**
     * Override cloning
     *
     * @return void
     */
    public function __clone()
    {
        trigger_error('Cloning is not allowed.', E_USER_ERROR);
    }

    /**
     * Override deserialisation
     *
     * @return void
     */
    public function __wakeup()
    {
        trigger_error('Deserialisation is not allowed.', E_USER_ERROR);
    }

    /**
    * Validate the input types of a function. The types are extracted from
    * the code and code documentation through the reflection API.
    *
    * @param array $function_args arguments of the calling function
    *
    * @return void
    */

    public function validateParameters(array $function_args)
    {
        $trace=debug_backtrace();
        $previousCall = $trace[1];

        //if (isset($previousCall['class']) && $previousCall['function']) {
        //    echo "Called by {$previousCall['function']}";
        //    echo " in {$previousCall['class']} <br/>";
        //}
        $method = new ReflectionMethod(
            $previousCall['class'], $previousCall['function']
        );
        $params = $method->getParameters();
        // Extract the types from the documenation

        $types = $this->_getParameterTypes($method->getDocComment());
        if ( $method->getNumberOfParameters() === count($types)) {
            if (count($function_args) <= $method->getNumberOfParameters()
                && count($function_args) >= $method->getNumberOfRequiredParameters()
            ) {
                for ($i = 0; $i < count($function_args); $i++) {
                    $this->_checkType(
                        $function_args[$i],
                        $types[$params[$i]->getName()],
                        $params[$i]->getName(),
                        $params[$i]->isPassedByReference()
                    );
                }
            } else {
                phpCAS::trace(
                    "Size mismatch of runtime parameters and code. No validation possible"
                );
            }
        } else {
            phpCAS::trace("Size mismatch of doc and code. No validation possible");
        }
    }

    /**
     * Extract types from the function documentation
     *
     * @param string $doc code documentation
     *
     * @return void
     */

    private function _getParameterTypes($doc)
    {
        $matches = array();
        $types = array();
        if (preg_match_all('/\@\w+\s+(\w+)\s+&?\$(\w+)/', $doc, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $val) {
                //  echo "matched: " . $val[0] . "<br/>";
                //  echo "part 1: " . $val[1] . "<br/>";
                //  echo "part 2: " . $val[2] . "<br/>";
                $types[$val[2]] = $val[1];
            }
        } else {
            //echo "No match<br/>";
        }
        return $types;
    }

    /**
     * Check the parameter type
     *
     * @param unknown_type $arg  paraemter value
     * @param string       $type parameter type
     * @param string       $name parameter name
     * @param bool         $ref  reference
     *
     * @return success
     */
    private function _checkType($arg, $type, $name, $ref)
    {
        //echo "Checking " . $arg . " for type " . $type . "<br/>";
        $success = false;
        // Skip any values passed as empty references
        if ($ref || empty($arg)) {
            return true;
        }
        switch ($type) {
        case 'string':
            if (is_string($arg)) {
                $success = true;
            }
            break;
        case 'int':
            if (is_int($arg)) {
                $success = true;
            }
            break;
        case 'bool':
            if (is_bool($arg)) {
                $success = true;
            }
            break;
        case 'array':
            if (is_array($arg)) {
                $success = true;
            }
            break;
        default:
            if ((class_exists($type) || interface_exists($type))
                && $arg instanceof $type
            ) {
                $success = true;
            } else {
                $success = false;
            }
            break;
        }

        if (!$success) {
            phpCAS :: error(
                'type mismatched for parameter '
                . $name
                . ' (should be '
                . $type
                . ') but found : '
                . (gettype($arg) == 'object'? get_class($arg) : gettype($arg))
            );
        }

    }


    /**
     * Set the cas client object
     *
     * @param CAS_Client $client The client object
     *
     * @return void
     */

    public function setClient(CAS_Client $client)
    {
        $this->_client = $client;
        // store where the initializer is called from
        $dbg = debug_backtrace();
        $this->_init_call = array (
                'done' => true,
                'file' => $dbg[1]['file'],
                'line' => $dbg[1]['line'],
                'method' => __CLASS__ . '::' . __FUNCTION__
        );
    }


    /**
     * Check of client() or proxy was already called
     *
     * @return void
     */
    public function checkClientConflict()
    {
        if (is_object($this->_client)) {
            phpCAS :: error(
                $this->_init_call['method']
                . '() has already been called (at '
                . $this->_init_call['file']
                . ':' . $this->_init_call['line'] . ')'
            );
        }
    }


    /**
     * Check of client() or proxy was already called
     *
     * @return void
     */
    public function checkClientExists()
    {
        if (!is_object($this->_client)) {
            phpCAS :: error('this method should not be called before phpCAS::client() or phpCAS::proxy()');
        }
    }


    /**
     * Check if client is a proxy() client
     *
     * @return void
     */
    public function checkClientIsProxy()
    {
        if (!is_object($this->_client) || !$this->_client->isProxy()) {
            phpCAS :: error(
                'this method should only be called after phpCAS::proxy()'
            );
        }
    }

    /**
     * Check if any authentication method was calle
     *
     * @return void;
     */
    public function checkClientBeforeAuthenticationCalled()
    {
        if ($this->_client->wasAuthenticationCalled()) {
            phpCAS :: error(
                'this method should only be called before '
                . $this->_client->getAuthenticationCallerMethod()
                . '() (called at '
                . $this->_client->getAuthenticationCallerFile()
                . ':' . $this->_client->getAuthenticationCallerLine() . ')'
            );
        }
    }


    /**
     * Check that any authentication method was called
     *
     * @return void;
     */
    public function checkClientAfterAuthenticationCalled()
    {
        if (!$this->_client->wasAuthenticationCalled()) {
            phpCAS :: error('this method should only be called after the programmer is sure the user has been authenticated (by calling phpCAS::checkAuthentication() or phpCAS::forceAuthentication()');
        }
    }

    /**
     * Check that the authentication call was successfull. If you hit this you
     * probably have an error in your authentication code.
     *
     * @return void;
     */
    public function checkClientAuthenticationCalledSucessful()
    {
        $this->checkClientAfterAuthenticationCalled();
        if (!$this->_client->wasAuthenticationCallSuccessful()) {
            phpCAS :: error(
                'authentication was checked (by '
                . $this->_client->getAuthenticationCallerMethod()
                . '() at ' . $this->_client->getAuthenticationCallerFile()
                . ':' . $this->_client->getAuthenticationCallerLine()
                . ') but the method returned false'
            );
        }
    }


    /**
     * Check if the server protocol is compatibe with the function call.
     *
     * @param string $protocol CAS Server protocol
     *
     * @return void
     */
    public function checkClientProtocol( $protocol )
    {
        if (!$this->_client->getServerVersion() === $protocol) {
            phpCAS :: error(
                'this method cannot be used with this cas server protool'
            );
        }
    }
}
?>