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
 * @file     CAS/TestHarness/DummyRequest.php
 * @category Authentication
 * @author   Adam Franco <afranco@middlebury.edu>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 */

namespace phpCAS\CAS\TestHarness;

use phpCAS\CAS\OutOfSequenceException;
use phpCAS\CAS\Request\AbstractRequest;
use phpCAS\CAS\Request\RequestInterface;

/**
 * Provides support for performing dummy web-requests.
 *
 * @class    CAS_TestHarness_DummyRequest
 * @category Authentication
 * @author   Adam Franco <afranco@middlebury.edu>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 */
class DummyRequest extends AbstractRequest implements RequestInterface
{
    private static $_responses = [];

    /**
     * Configure a URL/Response that the test harness will respond to.
     *
     * @param ResponseInterface $response response interface
     *
     * @return void
     */
    public static function addResponse(
        ResponseInterface $response
    ) {
        self::$_responses[] = $response;
    }

    /**
     * Clear out the URLs/Responses that the test harness will respond to.
     *
     * @return void
     */
    public static function clearResponses()
    {
        self::$_responses = [];
    }

    /**
     * Send the request and store the results.
     *
     * @return bool TRUE on success, FALSE on failure.
     */
    protected function sendRequest()
    {
        foreach (self::$_responses as $response) {
            if ($response->matchesUrl($this->url)) {
                if (! $response->validateUrl($this->url)) {
                    $this->storeErrorMessage('Validation of url failed.');

                    return false;
                }
                if (! $response->validateRequestHeaders($this->headers)) {
                    $this->storeErrorMessage('Validation of headers failed.');

                    return false;
                }
                if (! $response->validateRequestCookies($this->cookies)) {
                    $this->storeErrorMessage('Validation of cookies failed.');

                    return false;
                }
                if (! $response->validateRequestIsPost($this->isPost)) {
                    $this->storeErrorMessage(
                        'Validation of GET/POST type failed.'
                    );

                    return false;
                }
                if (! $response->validatePostBody($this->postBody)) {
                    $this->storeErrorMessage('Validation of POST body failed.');

                    return false;
                }
                if (! $response->validateCaCert($this->caCertPath)) {
                    $this->storeErrorMessage('Validation of CA cert failed.');

                    return false;
                }

                $this->storeResponseHeaders($response->getResponseHeaders());
                $this->storeResponseBody($response->getResponseBody());

                return true;
            }
        }
        // 		print_r("\n404 URL ".$this->url." not found in test harness.\n");

        $this->storeErrorMessage(
            '404 URL '.$this->url.' not found in test harness.'
        );

        return false;
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
        parent::addCookies($cookies);
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
        parent::addHeader($header);
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
        parent::addHeaders($headers);
    }

    /**
     * Make the request a POST request rather than the default GET request.
     *
     * @return void
     * @throws OutOfSequenceException If called after the Request has been sent.
     */
    public function makePost()
    {
        parent::makePost();
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
        parent::setPostBody($body);
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
        parent::setSslCaCert($caCertPath, $validate_cn);
    }

    /**
     * Perform the request.
     *
     * @return bool TRUE on success, FALSE on failure.
     * @throws OutOfSequenceException If called multiple times.
     */
    public function send()
    {
        return parent::send();
    }

    /**
     * Answer the headers of the response.
     *
     * @return array An array of header strings.
     * @throws OutOfSequenceException If called before the Request has been sent.
     */
    public function getResponseHeaders()
    {
        return parent::getResponseHeaders();
    }

    /**
     * Answer HTTP status code of the response.
     *
     * @return int
     * @throws OutOfSequenceException If called before the Request has been sent.
     */
    public function getResponseStatusCode()
    {
        return parent::getResponseStatusCode();
    }

    /**
     * Answer the body of response.
     *
     * @return string
     * @throws OutOfSequenceException If called before the Request has been sent.
     */
    public function getResponseBody()
    {
        return parent::getResponseBody();
    }

    /**
     * Answer a message describing any errors if the request failed.
     *
     * @return string
     * @throws OutOfSequenceException If called before the Request has been sent.
     */
    public function getErrorMessage()
    {
        return parent::getErrorMessage();
    }
}
