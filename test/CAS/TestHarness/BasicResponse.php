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
 * @file     CAS/TestHarness/BasicResponse
 * @category Authentication
 * @package  PhpCAS
 * @author   Adam Franco <afranco@middlebury.edu>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 */

/**
 * The BasicResponse allows tests to dynamically create a response that can be used
 * in unit tests.
 *
 * @class    CAS_TestHarness_BasicResponse
 * @category Authentication
 * @package  PhpCAS
 * @author   Adam Franco <afranco@middlebury.edu>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 */

class CAS_TestHarness_BasicResponse implements CAS_TestHarness_ResponseInterface
{
    protected $scheme = 'http';
    protected $host = null;
    protected $port = null;
    protected $path = '/';
    protected $queryParams = array();
    protected $responseHeaders = array();
    protected $responseBody = '';
    protected $verifyIsPost = null;
    protected $postBodyToMatch = null;
    protected $headersToHave = array();
    protected $headersToNotHave = array();
    protected $cookiesToHave = array();
    protected $cookiesToNotHave = array();
    protected $certPathToMatch = null;
    protected $caCertPathToMatch = null;

    /*********************************************************
     * Creation and configuration.
     *********************************************************/

    /**
     * Create a new response.
     *
     * @param string  $scheme 'http' or 'https'
     * @param string  $host   Hostname
     * @param string  $path   Path
     * @param integer $port   Portnumber
     *
     * @return void
     */
    public function __construct($scheme, $host, $path, $port = null)
    {
        $this->scheme = $scheme;
        $this->host = $host;
        $this->path = $path;
        $this->port = $port;
    }

    /**
     * Add query parameters that must exist for the response to match a URL.
     *
     * @param array $queryParams Query paremeters
     *
     * @return void
     */
    public function matchQueryParameters(array $queryParams)
    {
        $this->queryParams = $queryParams;
    }

    /**
     * Set an array of response headers to return.
     *
     * @param array $responseHeaders headers added to the response
     *
     * @return void
     */
    public function setResponseHeaders(array $responseHeaders)
    {
        $this->responseHeaders = $responseHeaders;
    }

    /**
     * Set the response body to return
     *
     * @param string $responseBody body to return
     *
     * @return void
     */
    public function setResponseBody($responseBody)
    {
        $this->responseBody = $responseBody;
    }

    /**
     * Ensure that the request is a POST request.
     *
     * @return void
     */
    public function ensureIsPost()
    {
        $this->verifyIsPost = true;
    }

    /**
     * Ensure that the request is a GET request.
     *
     * @return void
     */
    public function ensureIsGet()
    {
        $this->verifyIsPost = false;
    }

    /**
     * Ensure that the POST body equals a given string.
     *
     * @param string $postBodyToMatch body of the POST to match
     *
     * @return void
     */
    public function ensurePostBodyEquals($postBodyToMatch)
    {
        $this->postBodyToMatch = $postBodyToMatch;
    }

    /**
     * Ensure that the request has a given header string
     *
     * @param string $header header that the request must match
     *
     * @return void
     */
    public function ensureHasHeader($header)
    {
        $this->headersToHave[] = $header;
    }

    /**
     * Ensure that the request does not have a given header string
     *
     * @param string $header header the must not match
     *
     * @return void
     */
    public function ensureDoesNotHaveHeader($header)
    {
        $this->headersToNotHave[] = $header;
    }

    /**
     * Ensure that the request has a given cookie
     *
     * @param string $name  name of cookie
     * @param string $value If null, the presense of the cookie will be checked,
     *  but not its value.
     *
     * @return void
     */
    public function ensureHasCookie($name, $value = null)
    {
        $this->cookiesToHave[$name] = $value;
    }

    /**
     * Ensure that the request does not have a given cookie
     *
     * @param string $name name of cookie
     *
     * @return void
     */
    public function ensureDoesNotHaveCookie($name)
    {
        $this->cookiesNotToHave[] = $name;
    }

    /**
     * Ensure that the request uses a particular cert path.
     *
     * @param string $certPath certificate path name
     *
     * @return void
     */
    public function ensureCertPathEquals($certPath)
    {
        $this->certPathToMatch = $certPath;
    }

    /**
     * Ensure that the request uses a particular ca cert path.
     *
     * @param string $caCertPath certificate path name
     *
     * @return void
     */
    public function ensureCaCertPathEquals($caCertPath)
    {
        $this->caCertPathToMatch = $caCertPath;
    }

    /*********************************************************
     * Interface methods
     *********************************************************/

    /**
     * Test if this response should be supplied for the URL passed.
     *
     * @param string $url url that should be matched
     *
     * @return bool
     */
    public function matchesUrl($url)
    {
        $parts = parse_url($url);
        if ($parts['scheme'] != $this->scheme) {
            return false;
        }
        if ($parts['host'] != $this->host) {
            return false;
        }

        if ($this->scheme == 'https') {
            $defaultPort = 443;
        } else {
            $defaultPort = 80;
        }
        if (isset($parts['port'])) {
            if ($this->port && $parts['port'] != $this->port) {
                return false;
            }
            if ($parts['port'] != $defaultPort) {
                return false;
            }
            // Allow no port to be manually specified if we are using the
            // default port for our scheme
        } else {
            if ($this->port && $this->port != $defaultPort) {
                return false;
            }
        }

        if ($parts['path'] != $this->path) {
            return false;
        }

        if (count($this->queryParams)) {
            if (!isset($parts['query'])) {
                return false;
            }

            parse_str($parts['query'], $query);
            foreach ($this->queryParams as $name => $value) {
                if (!isset($query[$name])) {
                    return false;
                }
                if ($query[$name] != $value) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Answer an array of response headers.
     *
     * @return array
     */
    public function getResponseHeaders()
    {
        return $this->responseHeaders;
    }

    /**
     * Answer HTTP status code of the response
     *
     * @return int
     * @throws CAS_OutOfSequenceException If called before the Request has been sent.
     */
    public function getResponseStatusCode()
    {
        if (!$this->sent) {
            throw new CAS_OutOfSequenceException(
                'Request has not been sent yet. Cannot ' . __METHOD__
            );
        }
        if (!preg_match(
            '/HTTP\/[0-9.]+\s+([0-9]+)\s*(.*)/',
            $this->responseHeaders[0], $matches
        )
        ) {
            throw new CAS_Request_Exception(
                "Bad response, no status code was found in the first line."
            );
        }

        return intval($matches[1]);
    }

    /**
     * Answer the response body
     *
     * @return string
     */

    public function getResponseBody()
    {
        return $this->responseBody;
    }

    /*********************************************************
     * Validation of the request
     *********************************************************/

    /**
     * Validate that the URL or its components (port, query parameters, etc)
     * pass muster.
     *
     * @param string $url url to check
     *
     * @return bool TRUE if the URL is valid.
     */
    public function validateUrl($url)
    {
        return $this->matchesUrl($url);
    }

    /**
     * Validate an array of request headers.
     *
     * @param array $headers headers to validate
     *
     * @return bool TRUE if the headers are valid.
     */
    public function validateRequestHeaders(array $headers)
    {
        foreach ($this->headersToHave as $headerToCheck) {
            if (!in_array($headerToCheck, $headers)) {
                return false;
            }
        }
        foreach ($this->headersToNotHave as $headerToCheck) {
            if (in_array($headerToCheck, $headers)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Validate an array of request cookies.
     *
     * @param array $cookies cookies to check
     *
     * @return bool TRUE if the cookies are valid.
     */
    public function validateRequestCookies(array $cookies)
    {
        foreach ($this->cookiesToHave as $name => $value) {
            if (!isset($cookies[$name])) {
                return false;
            }
            if (!is_null($value) && $cookies[$name] != $value) {
                return false;
            }
        }
        foreach ($this->cookiesToNotHave as $name) {
            if (isset($cookies[$name])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Validate the type of request.
     *
     * @param bool $isPost if the request should be POST
     *
     * @return bool TRUE if the type is valid.
     */
    public function validateRequestIsPost($isPost)
    {
        if ($this->verifyIsPost === true && !$isPost) {
            return false;
        } else if ($this->verifyIsPost === false && $isPost) {
            return false;
        }
        return true;
    }

    /**
     * Validate the body of the post request.
     *
     * @param string $postBody POST body
     *
     * @return bool TRUE if the post body is valid.
     */
    public function validatePostBody($postBody)
    {
        if (!is_null($this->postBodyToMatch)
            && $this->postBodyToMatch != $postBody
        ) {
            return false;
        }
        return true;
    }

    /**
     * Validate an SSL certificate path.
     *
     * @param string $certPath certificate path
     *
     * @return bool TRUE if the cert path is correct.
     */
    public function validateCert($certPath)
    {
        if (!is_null($this->certPathToMatch)
            && $this->certPathToMatch != $certPath
        ) {
            return false;
        }
        return true;
    }

    /**
     * Validate an SSL CA certificate path.
     *
     * @param string $caCertPath certificate path
     *
     * @return bool TRUE if the cert path is correct.
     */
    public function validateCaCert($caCertPath)
    {
        if (!is_null($this->caCertPathToMatch)
            && $this->caCertPathToMatch != $caCertPath
        ) {
            return false;
        }
        return true;
    }

}
