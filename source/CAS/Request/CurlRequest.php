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
 * @file     CAS/Request/CurlRequest.php
 * @category Authentication
 * @author   Adam Franco <afranco@middlebury.edu>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 */

namespace phpCAS\CAS\Request;

use phpCAS\CAS;
use phpCAS\CAS\OutOfSequenceException;

/**
 * Provides support for performing web-requests via curl.
 *
 * @class    CurlRequest
 * @category Authentication
 * @author   Adam Franco <afranco@middlebury.edu>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 */
class CurlRequest extends AbstractRequest implements RequestInterface
{
    /**
     * Set additional curl options.
     *
     * @param array $options option to set
     *
     * @return void
     */
    public function setCurlOptions(array $options)
    {
        $this->_curlOptions = $options;
    }

    private $_curlOptions = [];

    /**
     * Send the request and store the results.
     *
     * @return bool true on success, false on failure.
     */
    protected function sendRequest()
    {
        CAS::traceBegin();

        /*********************************************************
         * initialize the CURL session
        *********************************************************/
        $ch = $this->_initAndConfigure();

        /*********************************************************
         * Perform the query
        *********************************************************/
        $buf = curl_exec($ch);
        if ($buf === false) {
            CAS::trace('curl_exec() failed');
            $this->storeErrorMessage(
                'CURL error #'.curl_errno($ch).': '.curl_error($ch)
            );
            $res = false;
        } else {
            $this->storeResponseBody($buf);
            CAS::trace("Response Body: \n".$buf."\n");
            $res = true;
        }
        // close the CURL session
        curl_close($ch);

        CAS::traceEnd($res);

        return $res;
    }

    /**
     * Internal method to initialize our cURL handle and configure the request.
     * This method should NOT be used outside of the CurlRequest or the
     * CurlMultiRequest.
     *
     * @return resource The cURL handle on success, false on failure
     */
    private function _initAndConfigure()
    {
        /*********************************************************
         * initialize the CURL session
        *********************************************************/
        $ch = curl_init($this->url);

        if (version_compare(PHP_VERSION, '5.1.3', '>=')) {
            //only available in php5
            curl_setopt_array($ch, $this->_curlOptions);
        } else {
            foreach ($this->_curlOptions as $key => $value) {
                curl_setopt($ch, $key, $value);
            }
        }

        /*********************************************************
         * Set SSL configuration
        *********************************************************/
        if ($this->caCertPath) {
            if ($this->validateCN) {
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            } else {
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            }
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
            curl_setopt($ch, CURLOPT_CAINFO, $this->caCertPath);
            CAS::trace('CURL: Set CURLOPT_CAINFO '.$this->caCertPath);
        } else {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }

        /*********************************************************
         * Configure curl to capture our output.
        *********************************************************/
        // return the CURL output into a variable
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // get the HTTP header with a callback
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, [$this, '_curlReadHeaders']);

        /*********************************************************
         * Add cookie headers to our request.
        *********************************************************/
        if (count($this->cookies)) {
            $cookieStrings = [];
            foreach ($this->cookies as $name => $val) {
                $cookieStrings[] = $name.'='.$val;
            }
            curl_setopt($ch, CURLOPT_COOKIE, implode(';', $cookieStrings));
        }

        /*********************************************************
         * Add any additional headers
        *********************************************************/
        if (count($this->headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        }

        /*********************************************************
         * Flag and Body for POST requests
        *********************************************************/
        if ($this->isPost) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->postBody);
        }

        return $ch;
    }

    /**
     * Store the response body.
     * This method should NOT be used outside of the CurlRequest or the
     * CurlMultiRequest.
     *
     * @param string $body body to store
     *
     * @return void
     */
    private function _storeResponseBody($body)
    {
        $this->storeResponseBody($body);
    }

    /**
     * Internal method for capturing the headers from a curl request.
     *
     * @param resource $ch     handle of curl
     * @param string   $header header
     *
     * @return int
     */
    private function _curlReadHeaders($ch, $header)
    {
        $this->storeResponseHeader($header);

        return strlen($header);
    }

    /**
     * Set the URL of the Request.
     *
     * @param string $url url to set
     *
     * @return void
     * @throws OutOfSequenceException If called after the Request has been sent.
     */
    public function setUrl($url)
    {
        parent::setUrl($url);
    }

    /**
     * Add a cookie to the request.
     *
     * @param string $name name of cookie
     * @param string $value value of cookie
     *
     * @return void
     * @throws OutOfSequenceException If called after the Request has been sent.
     */
    public function addCookie($name, $value)
    {
        parent::addCookie($name, $value);
    }

    /**
     * Add an array of cookies to the request.
     * The cookie array is of the form
     *     array('cookie_name' => 'cookie_value', 'cookie_name2' => cookie_value2').
     *
     * @param array $cookies cookies to add
     *
     * @return void
     * @throws OutOfSequenceException If called after the Request has been sent.
     */
    public function addCookies(array $cookies)
    {
        // TODO: Implement addCookies() method.
    }

    /**
     * Add a header string to the request.
     *
     * @param string $header header to add
     *
     * @return void
     * @throws OutOfSequenceException If called after the Request has been sent.
     */
    public function addHeader($header)
    {
        // TODO: Implement addHeader() method.
    }

    /**
     * Add an array of header strings to the request.
     *
     * @param array $headers headers to add
     *
     * @return void
     * @throws OutOfSequenceException If called after the Request has been sent.
     */
    public function addHeaders(array $headers)
    {
        // TODO: Implement addHeaders() method.
    }

    /**
     * Make the request a POST request rather than the default GET request.
     *
     * @return void
     * @throws OutOfSequenceException If called after the Request has been sent.
     */
    public function makePost()
    {
        // TODO: Implement makePost() method.
    }

    /**
     * Add a POST body to the request.
     *
     * @param string $body body to add
     *
     * @return void
     * @throws OutOfSequenceException If called after the Request has been sent.
     */
    public function setPostBody($body)
    {
        // TODO: Implement setPostBody() method.
    }

    /**
     * Specify the path to an SSL CA certificate to validate the server with.
     *
     * @param string $caCertPath path to cert file
     * @param bool $validate_cn validate CN of SSL certificate
     *
     * @return void
     * @throws OutOfSequenceException If called after the Request has been sent.
     */
    public function setSslCaCert($caCertPath, $validate_cn = true)
    {
        // TODO: Implement setSslCaCert() method.
    }

    /**
     * Perform the request.
     *
     * @return bool TRUE on success, FALSE on failure.
     * @throws OutOfSequenceException If called multiple times.
     */
    public function send()
    {
        // TODO: Implement send() method.
    }

    /**
     * Answer the headers of the response.
     *
     * @return array An array of header strings.
     * @throws OutOfSequenceException If called before the Request has been sent.
     */
    public function getResponseHeaders()
    {
        // TODO: Implement getResponseHeaders() method.
    }

    /**
     * Answer HTTP status code of the response.
     *
     * @return int
     * @throws OutOfSequenceException If called before the Request has been sent.
     */
    public function getResponseStatusCode()
    {
        // TODO: Implement getResponseStatusCode() method.
    }

    /**
     * Answer the body of response.
     *
     * @return string
     * @throws OutOfSequenceException If called before the Request has been sent.
     */
    public function getResponseBody()
    {
        // TODO: Implement getResponseBody() method.
    }

    /**
     * Answer a message describing any errors if the request failed.
     *
     * @return string
     * @throws OutOfSequenceException If called before the Request has been sent.
     */
    public function getErrorMessage()
    {
        // TODO: Implement getErrorMessage() method.
    }
}
