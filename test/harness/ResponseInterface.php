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
 * Implementations of this interface can validate a request and provide response
 * headers and body, allowing the spoofing of responses to web requests for testing
 * purposes.
 */
interface CAS_TestHarness_ResponseInterface {

	/**
	 * Test if this response should be supplied for the URL passed.
	 *
	 * @param string $url
	 * @return bool
	 */
	public function matchesUrl ($url);

	/**
	 * Answer an array of response headers.
	 *
	 * @return array
	 */
	public function getResponseHeaders ();

	/**
	 * Answer the response body
	 *
	 * @return string
	 */
	public function getResponseBody ();

	/*********************************************************
	 * Validation of the request
	 *********************************************************/

	/**
	 * Validate that the URL or its components (port, query parameters, etc) pass muster.
	 *
	 * @param string $url
	 * @return bool TRUE if the URL is valid.
	 */
	public function validateUrl ($url);

	/**
	 * Validate an array of request headers.
	 *
	 * @param array $headers
	 * @return bool TRUE if the headers are valid.
	 */
	public function validateRequestHeaders (array $headers);

	/**
	 * Validate an array of request cookies.
	 *
	 * @param array $cookies
	 * @return bool TRUE if the cookies are valid.
	 */
	public function validateRequestCookies (array $cookies);

	/**
	 * Validate the type of request.
	 *
	 * @param bool $isPost
	 * @return bool TRUE if the type is valid.
	 */
	public function validateRequestIsPost ($isPost);

	/**
	 * Validate the body of the post request.
	 *
	 * @param string $postBody
	 * @return bool TRUE if the post body is valid.
	 */
	public function validatePostBody ($postBody);

	/**
	 * Validate an SSL certificate path.
	 *
	 * @param string $certPath
	 * @return bool TRUE if the cert path is correct.
	 */
	public function validateCert ($certPath);

	/**
	 * Validate an SSL CA certificate path.
	 *
	 * @param string $caCertPath
	 * @return bool TRUE if the cert path is correct.
	 */
	public function validateCaCert ($caCertPath);

}