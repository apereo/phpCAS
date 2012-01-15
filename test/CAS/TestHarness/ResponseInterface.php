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
 * @file     CAS/TestHarness/ResponseInterface.php
 * @category Authentication
 * @package  PhpCAS
 * @author   Adam Franco <afranco@middlebury.edu>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 */

/**
 * Implementations of this interface can validate a request and provide response
 * headers and body, allowing the spoofing of responses to web requests for testing
 * purposes.
 *
 * @class    CAS_TestHarness_ResponseInterface
 * @category Authentication
 * @package  PhpCAS
 * @author   Adam Franco <afranco@middlebury.edu>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 */
interface CAS_TestHarness_ResponseInterface
{

    /**
     * Test if this response should be supplied for the URL passed.
     *
     * @param string $url url to match
     *
     * @return bool
     */
    public function matchesUrl($url);

    /**
     * Answer an array of response headers.
     *
     * @return array
     */
    public function getResponseHeaders();

    /**
     * Answer the response body
     *
     * @return string
     */
    public function getResponseBody();

    /*********************************************************
     * Validation of the request
     *********************************************************/

    /**
     * Validate that the URL or its components (port, query parameters, etc)
     * pass muster.
     *
     * @param string $url url to validate
     *
     * @return bool TRUE if the URL is valid.
     */
    public function validateUrl($url);

    /**
     * Validate an array of request headers.
     *
     * @param array $headers headers to validate
     *
     * @return bool TRUE if the headers are valid.
     */
    public function validateRequestHeaders(array $headers);

    /**
     * Validate an array of request cookies.
     *
     * @param array $cookies cookies to validate
     *
     * @return bool TRUE if the cookies are valid.
     */
    public function validateRequestCookies(array $cookies);

    /**
     * Validate the type of request.
     *
     * @param bool $isPost if POST true or false
     *
     * @return bool TRUE if the type is valid.
     */
    public function validateRequestIsPost($isPost);

    /**
     * Validate the body of the post request.
     *
     * @param string $postBody POST body
     *
     * @return bool TRUE if the post body is valid.
     */
    public function validatePostBody($postBody);

    /**
     * Validate an SSL certificate path.
     *
     * @param string $certPath certificate path
     *
     * @return bool TRUE if the cert path is correct.
     */
    public function validateCert($certPath);

    /**
     * Validate an SSL CA certificate path.
     *
     * @param string $caCertPath ca certificate path
     *
     * @return bool TRUE if the cert path is correct.
     */
    public function validateCaCert($caCertPath);

}
