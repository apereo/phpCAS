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
 * This interface defines a class library for performing multiple web requests in batches.
 * Implementations of this interface may perform requests serially or in parallel.
 */
class CAS_Request_CurlMultiRequest
	implements CAS_Request_MultiRequestInterface 
{
	private $requests = array();
	private $sent = false;

	/*********************************************************
	 * Add Requests
	 *********************************************************/

	/**
	 * Add a new Request to this batch.
	 * Note, implementations will likely restrict requests to their own concrete class hierarchy.
	 * 
	 * @param CAS_Request_RequestInterface $request
	 * @return void
	 * @throws CAS_OutOfSequenceException If called after the Request has been sent.
	 * @throws CAS_InvalidArgumentException If passed a Request of the wrong implmentation.
	 */
	public function addRequest (CAS_Request_RequestInterface $request) {
		if ($this->sent)
			throw new CAS_OutOfSequenceException('Request has already been sent cannot '.__METHOD__);
		if (!$request instanceof CAS_Request_CurlRequest)
			throw new CAS_InvalidArgumentException('As a CAS_Request_CurlMultiRequest, I can only work with CAS_Request_CurlRequest objects.');
		
		$this->requests[] = $request;
	}

	/**
	 * Retrieve the number of requests added to this batch.
	 * 
	 * @return number of request elements
	 */
	public function getNumRequests() {
		if ($this->sent)
			throw new CAS_OutOfSequenceException('Request has already been sent cannot '.__METHOD__);
		return count($this->requests);
	}
	
	/*********************************************************
	 * 2. Send the Request
	 *********************************************************/

	/**
	 * Perform the request. After sending, all requests will have their responses poulated.
	 *
	 * @return boolean TRUE on success, FALSE on failure.
	 * @throws CAS_OutOfSequenceException If called multiple times.
	 */
	public function send () {
		if ($this->sent)
			throw new CAS_OutOfSequenceException('Request has already been sent cannot send again.');
		if (!count($this->requests))
			throw new CAS_OutOfSequenceException('At least one request must be added via addRequest() before the multi-request can be sent.');
		
		$this->sent = true;
		
		// Initialize our handles and configure all requests.
		$handles = array();
		$multiHandle = curl_multi_init();
		foreach ($this->requests as $i => $request) {
			$handle = $request->_initAndConfigure();
			curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
			$handles[$i] = $handle;
			curl_multi_add_handle($multiHandle, $handle);
		}
		
		// Execute the requests in parallel.
		do {
			curl_multi_exec($multiHandle, $running);
		} while($running > 0);
		
		// Populate all of the responses or errors back into the request objects.
		foreach ($this->requests as $i => $request) {
			$buf = curl_multi_getcontent($handles[$i]);
			$request->_storeResponseBody($buf);
			curl_multi_remove_handle($multiHandle, $handles[$i]);
			curl_close($handles[$i]);
		}
		
		curl_multi_close($multiHandle);
	}
}
