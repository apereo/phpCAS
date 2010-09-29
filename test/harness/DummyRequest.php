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

require_once dirname(__FILE__).'/../../source/CAS/RequestInterface.php';
require_once dirname(__FILE__).'/../../source/CAS/AbstractRequest.php';

/**
 * Provides support for performing dummy web-requests
 */
class CAS_TestHarness_DummyRequest
	extends CAS_AbstractRequest
	implements CAS_RequestInterface
{
	private static $responses = array();

	/**
	 * Configure a URL/Response that the test harness will respond to.
	 *
	 * @param CAS_TestHarness_ResponseInterface $response
	 * @return void
	 */
	public static function addResponse (CAS_TestHarness_ResponseInterface $response) {
		self::$responses[] = $response;
	}

	/**
	 * Clear out the URLs/Responses that the test harness will respond to.
	 *
	 * @return void
	 */
	public static function clearResponses () {
		self::$responses = array();
	}

	/**
	 * Send the request and store the results.
	 *
	 * @return boolean TRUE on success, FALSE on failure.
	 */
	protected function _sendRequest () {
		foreach (self::$responses as $response) {
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
					$this->storeErrorMessage('Validation of GET/POST type failed.');
					return false;
				}
				if (!$response->validatePostBody($this->postBody)) {
					$this->storeErrorMessage('Validation of POST body failed.');
					return false;
				}
				if (!$response->validateCert($this->certPath)) {
					$this->storeErrorMessage('Validation of cert failed.');
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
		print_r("\n404 URL ".$this->url." not found in test harness.\n");

		$this->storeErrorMessage('404 URL '.$this->url.' not found in test harness.');
		return false;
	}
}