<?php
/*
 * Copyright © 2003-2010, The ESUP-Portail consortium & the JA-SIG Collaborative.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *	   * Redistributions of source code must retain the above copyright notice,
 *		 this list of conditions and the following disclaimer.
 *	   * Redistributions in binary form must reproduce the above copyright notice,
 *		 this list of conditions and the following disclaimer in the documentation
 *		 and/or other materials provided with the distribution.
 *	   * Neither the name of the ESUP-Portail consortium & the JA-SIG
 *		 Collaborative nor the names of its contributors may be used to endorse or
 *		 promote products derived from this software without specific prior
 *		 written permission.

 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 * ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
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
	 * @return boolean
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
	 * @return boolean TRUE if the URL is valid.
	 */
	public function validateUrl ($url);

	/**
	 * Validate an array of request headers.
	 *
	 * @param array $headers
	 * @return boolean TRUE if the headers are valid.
	 */
	public function validateRequestHeaders (array $headers);

	/**
	 * Validate an array of request cookies.
	 *
	 * @param array $cookies
	 * @return boolean TRUE if the cookies are valid.
	 */
	public function validateRequestCookies (array $cookies);

	/**
	 * Validate the type of request.
	 *
	 * @param boolean $isPost
	 * @return boolean TRUE if the type is valid.
	 */
	public function validateRequestIsPost ($isPost);

	/**
	 * Validate the body of the post request.
	 *
	 * @param string $postBody
	 * @return boolean TRUE if the post body is valid.
	 */
	public function validatePostBody ($postBody);

	/**
	 * Validate an SSL certificate path.
	 *
	 * @param string $certPath
	 * @return boolean TRUE if the cert path is correct.
	 */
	public function validateCert ($certPath);

	/**
	 * Validate an SSL CA certificate path.
	 *
	 * @param string $caCertPath
	 * @return boolean TRUE if the cert path is correct.
	 */
	public function validateCaCert ($caCertPath);

}