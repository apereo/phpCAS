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
 * @file     CAS/TestHarness/DummyMultiRequest.php
 * @category Authentication
 * @author   Adam Franco <afranco@middlebury.edu>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 */

namespace phpCAS\CAS\TestHarness;

use phpCAS\CAS\InvalidArgumentException;
use phpCAS\CAS\OutOfSequenceException;
use phpCAS\CAS\Request\MultiRequestInterface;
use phpCAS\CAS\Request\RequestInterface;

/**
 * This interface defines a class library for performing multiple web requests
 * in batches. Implementations of this interface may perform requests serially
 * or in parallel.
 *
 * @class    DummyMultiRequest
 * @category Authentication
 * @author   Adam Franco <afranco@middlebury.edu>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 */
class DummyMultiRequest implements MultiRequestInterface
{
    private $_requests = [];
    private $_sent = false;

    /*********************************************************
     * Add Requests
     *********************************************************/

    /**
     * Add a new Request to this batch.
     * Note, implementations will likely restrict requests to their own concrete
     * class hierarchy.
     *
     * @param RequestInterface $request request interface
     *
     * @return void
     *
     * @throws OutOfSequenceException If called after the Request has been sent.
     * @throws InvalidArgumentException If passed a Request of the wrong
     * implementation.
     */
    public function addRequest(RequestInterface $request)
    {
        if ($this->_sent) {
            throw new OutOfSequenceException(
                'Request has already been sent cannot '.__METHOD__
            );
        }
        if (! $request instanceof DummyRequest) {
            throw new InvalidArgumentException(
                'As a CAS_TestHarness_DummyMultiRequest, I can only work with CAS_TestHarness_DummyRequest objects.'
            );
        }

        $this->_requests[] = $request;
    }

    /*********************************************************
     * 2. Send the Request
     *********************************************************/

    /**
     * Perform the request. After sending, all requests will have their
     * responses populated.
     *
     * @return bool TRUE on success, FALSE on failure.
     *
     * @throws OutOfSequenceException If called multiple times.
     */
    public function send()
    {
        if ($this->_sent) {
            throw new OutOfSequenceException(
                'Request has already been sent cannot send again.'
            );
        }
        if (! count($this->_requests)) {
            throw new OutOfSequenceException(
                'At least one request must be added via addRequest() before the multi-request can be sent.'
            );
        }
        $this->_sent = true;

        // Run all of our requests.
        foreach ($this->_requests as $request) {
            $request->send();
        }
    }

    /**
     * Retrieve the number of requests added to this batch.
     *
     * @return number of request elements
     */
    public function getNumRequests()
    {
        if ($this->_sent) {
            throw new OutOfSequenceException(
                'Request has already been sent cannot '.__METHOD__
            );
        }

        return count($this->_requests);
    }
}
