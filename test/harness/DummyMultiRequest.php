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
require_once dirname(__FILE__).'/../../source/CAS/Request/MultiRequestInterface.php';

/**
 * This interface defines a class library for performing multiple web requests in batches.
 * Implementations of this interface may perform requests serially or in parallel.
 */
class CAS_TestHarness_DummyMultiRequest
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
	 * @param CAS_RequestInterface $request
	 * @return void
	 * @throws CAS_OutOfSequenceException If called after the Request has been sent.
	 * @throws CAS_InvalidArgumentException If passed a Request of the wrong implmentation.
	 */
	public function addRequest (CAS_RequestInterface $request) {
		if ($this->sent)
			throw new CAS_OutOfSequenceException('Request has already been sent cannot '.__METHOD__);
		if (!$request instanceof CAS_TestHarness_DummyRequest)
			throw new CAS_InvalidArgumentException('As a CAS_TestHarness_DummyMultiRequest, I can only work with CAS_TestHarness_DummyRequest objects.');
		
		$this->requests[] = $request;
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
		
		// Run all of our requests.
		foreach ($this->requests as $request) {
			$request->send();
		}
	}
}