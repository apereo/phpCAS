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

require_once dirname(__FILE__).'/ResponseInterface.php';

/**
 * The BasicResponse allows tests to dynamically create a response that can be used
 * in unit tests.
 *
 */
class CAS_TestHarness_BasicResponse
	implements CAS_TestHarness_ResponseInterface
{
	protected $scheme = 'http';
	protected $host = null;
	protected $port = null;
	protected $path = '/';
	protected $queryParams = array();
	protected $responseHeaders = array();
	protected $responseBody = '';
	protected $verifyIsPost = NULL;
	protected $postBodyToMatch = NULL;
	protected $headersToHave = array();
	protected $headersToNotHave = array();
	protected $cookiesToHave = array();
	protected $cookiesToNotHave = array();
	protected $certPathToMatch = NULL;
	protected $caCertPathToMatch = NULL;

	/*********************************************************
	 * Creation and configuration.
	 *********************************************************/

	/**
	 * Create a new response.
	 *
	 * @param string $scheme  'http' or 'https'
	 * @param string $host
	 * @param string $path
	 * @param optional integer $port
	 *
	 * @return void
	 */
	public function __construct ($scheme, $host, $path, $port = null) {
		$this->scheme = $scheme;
		$this->host = $host;
		$this->path = $path;
		$this->port = $port;
	}

	/**
	 * Add query parameters that must exist for the response to match a URL.
	 *
	 * @param array $queryParams
	 * @return void
	 */
	public function matchQueryParameters (array $queryParams) {
		$this->queryParams = $queryParams;
	}

	/**
	 * Set an array of response headers to return.
	 *
	 * @param array $responseHeaders
	 * @return void
	 */
	public function setResponseHeaders (array $responseHeaders) {
		$this->responseHeaders = $responseHeaders;
	}

	/**
	 * Set the response body to return
	 *
	 * @param string $responseBody
	 * @return void
	 */
	public function setResponseBody ($responseBody) {
		$this->responseBody = $responseBody;
	}

	/**
	 * Ensure that the request is a POST request.
	 *
	 * @return void
	 */
	public function ensureIsPost () {
		$this->verifyIsPost = true;
	}

	/**
	 * Ensure that the request is a GET request.
	 *
	 * @return void
	 */
	public function ensureIsGet () {
		$this->verifyIsPost = FALSE;
	}

	/**
	 * Ensure that the POST body equals a given string.
	 *
	 * @param string $postBodyToMatch
	 * @return void
	 */
	public function ensurePostBodyEquals ($postBodyToMatch) {
		$this->postBodyToMatch = $postBodyToMatch;
	}

	/**
	 * Ensure that the request has a given header string
	 *
	 * @param string $header
	 * @return void
	 */
	public function ensureHasHeader ($header) {
		$this->headersToHave[] = $header;
	}

	/**
	 * Ensure that the request does not have a given header string
	 *
	 * @param string $header
	 * @return void
	 */
	public function ensureDoesNotHaveHeader ($header) {
		$this->headersToNotHave[] = $header;
	}

	/**
	 * Ensure that the request has a given cookie
	 *
	 * @param string $name
	 * @param optional string $value If NULL, the presense of the cookie will be checked, but not its value.
	 * @return void
	 */
	public function ensureHasCookie ($name, $value = null) {
		$this->cookiesToHave[$name] = $value;
	}

	/**
	 * Ensure that the request does not have a given cookie
	 *
	 * @param string $name
	 * @return void
	 */
	public function ensureDoesNotHaveCookie ($name) {
		$this->cookiesNotToHave[] = $name;
	}

	/**
	 * Ensure that the request uses a particular cert path.
	 *
	 * @param string $certPath
	 * @return void
	 */
	public function ensureCertPathEquals ($certPath) {
		$this->certPathToMatch = $certPath;
	}

	/**
	 * Ensure that the request uses a particular ca cert path.
	 *
	 * @param string $caCertPath
	 * @return void
	 */
	public function ensureCaCertPathEquals ($caCertPath) {
		$this->caCertPathToMatch = $caCertPath;
	}

	/*********************************************************
	 * Interface methods
	 *********************************************************/

	/**
	 * Test if this response should be supplied for the URL passed.
	 *
	 * @param string $url
	 * @return boolean
	 */
	public function matchesUrl ($url) {
		$parts = parse_url($url);
		if ($parts['scheme'] != $this->scheme)
			return false;

		if ($parts['host'] != $this->host)
			return false;

		if ($this->scheme == 'https')
			$defaultPort = 443;
		else
			$defaultPort = 80;
		if (isset($parts['port'])) {
			if ($this->port && $parts['port'] != $this->port)
				return false;
			if ($parts['port'] != $defaultPort)
				return false;
		}
		// Allow no port to be manually specified if we are using the default port for our scheme
		else {
			if ($this->port && $this->port != $defaultPort)
				return false;
		}

		if ($parts['path'] != $this->path)
			return false;

		if (count($this->queryParams)) {
			if (!isset($parts['query']))
				return false;

			parse_str($parts['query'], $query);
			foreach ($this->queryParams as $name => $value) {
				if (!isset($query[$name]))
					return false;
				if ($query[$name] != $value)
					return false;
			}
		}

		return true;
	}

	/**
	 * Answer an array of response headers.
	 *
	 * @return array
	 */
	public function getResponseHeaders () {
		return $this->responseHeaders;
	}

	/**
	 * Answer the response body
	 *
	 * @return string
	 */
	public function getResponseBody () {
		return $this->responseBody;
	}

	/*********************************************************
	 * Validation of the request
	 *********************************************************/

	/**
	 * Validate that the URL or its components (port, query parameters, etc) pass muster.
	 *
	 * @param string $url
	 * @return boolean TRUE if the URL is valid.
	 */
	public function validateUrl ($url) {
		return $this->matchesUrl($url);
	}

	/**
	 * Validate an array of request headers.
	 *
	 * @param array $headers
	 * @return boolean TRUE if the headers are valid.
	 */
	public function validateRequestHeaders (array $headers) {
		foreach ($this->headersToHave as $headerToCheck) {
			if (!in_array($headers, headerToCheck))
				return false;
		}
		foreach ($this->headersToNotHave as $headerToCheck) {
			if (in_array($headers, headerToCheck))
				return false;
		}
		return true;
	}

	/**
	 * Validate an array of request cookies.
	 *
	 * @param array $cookies
	 * @return boolean TRUE if the cookies are valid.
	 */
	public function validateRequestCookies (array $cookies) {
		foreach ($this->cookiesToHave as $name => $value) {
			if (!isset($cookies[$name]))
				return false;
			if (!is_null($value) && $cookies[$name] != $value)
				return false;
		}
		foreach ($this->cookiesToNotHave as $name) {
			if (isset($cookies[$name]))
				return false;
		}
		return true;
	}

	/**
	 * Validate the type of request.
	 *
	 * @param boolean $isPost
	 * @return boolean TRUE if the type is valid.
	 */
	public function validateRequestIsPost ($isPost) {
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
	 * @param string $postBody
	 * @return boolean TRUE if the post body is valid.
	 */
	public function validatePostBody ($postBody) {
		if (!is_null($this->postBodyToMatch) && $this->postBodyToMatch != $postBodyToMatch) {
			return false;
		}
		return true;
	}

	/**
	 * Validate an SSL certificate path.
	 *
	 * @param string $certPath
	 * @return boolean TRUE if the cert path is correct.
	 */
	public function validateCert ($certPath) {
		if (!is_null($this->certPathToMatch) && $this->certPathToMatch != $certPath)
			return false;
		return true;
	}

	/**
	 * Validate an SSL CA certificate path.
	 *
	 * @param string $caCertPath
	 * @return boolean TRUE if the cert path is correct.
	 */
	public function validateCaCert ($caCertPath) {
		if (!is_null($this->caCertPathToMatch) && $this->caCertPathToMatch != $caCertPath)
			return false;
		return true;
	}

}