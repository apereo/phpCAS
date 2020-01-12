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
 * @package  PhpCAS
 * @author   Adam Franco <afranco@middlebury.edu>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 */

/**
 * Provides support for performing dummy web-requests
 *
 * @class    CAS_TestHarness_DummyRequest
 * @category Authentication
 * @package  PhpCAS
 * @author   Adam Franco <afranco@middlebury.edu>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 */
class CAS_TestHarness_DummyRequest extends CAS_Request_AbstractRequest
implements CAS_Request_RequestInterface
{
    private static $_responses = array();

    /**
     * Configure a URL/Response that the test harness will respond to.
     *
     * @param CAS_TestHarness_ResponseInterface $response response interface
     *
     * @return void
     */
    public static function addResponse(
        CAS_TestHarness_ResponseInterface $response
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
        self::$_responses = array();
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
                if (!$response->validateUrl($this->url)) {
                    $this->storeErrorMessage('Validation of url failed.');
                    return false;
                }
                if (!$response->validateRequestHeaders($this->headers)) {
                    $this->storeErrorMessage('Validation of headers failed.');
                    return false;
                }
                if (!$response->validateRequestCookies($this->cookies)) {
                    $this->storeErrorMessage('Validation of cookies failed.');
                    return false;
                }
                if (!$response->validateRequestIsPost($this->isPost)) {
                    $this->storeErrorMessage(
                        'Validation of GET/POST type failed.'
                    );
                    return false;
                }
                if (!$response->validatePostBody($this->postBody)) {
                    $this->storeErrorMessage('Validation of POST body failed.');
                    return false;
                }
                if (!$response->validateCaCert($this->caCertPath)) {
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
            '404 URL ' . $this->url . ' not found in test harness.'
        );
        return false;
    }
}
