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
 * @file     CAS/Request/AbstractRequest.php
 * @category Authentication
 * @author   Adam Franco <afranco@middlebury.edu>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 */

namespace phpCAS\CAS\Request;

use phpCAS\CAS\InvalidArgumentException;
use phpCAS\CAS\OutOfSequenceException;

/**
 * This interface defines a class library for performing multiple web requests
 * in batches. Implementations of this interface may perform requests serially
 * or in parallel.
 *
 * @class    CurlMultiRequest
 * @category Authentication
 * @author   Adam Franco <afranco@middlebury.edu>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 */
class CurlMultiRequest implements MultiRequestInterface
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
     * @param RequestInterface $request request to add
     *
     * @return void
     * @throws OutOfSequenceException If called after the Request has been sent.
     * @throws InvalidArgumentException If passed a Request of the wrong
     *                                  implementation.
     */
    public function addRequest(RequestInterface $request)
    {
        if ($this->_sent) {
            throw new OutOfSequenceException(
                'Request has already been sent cannot '.__METHOD__
            );
        }
        if (! $request instanceof CurlRequest) {
            throw new InvalidArgumentException(
                'As a CAS_Request_CurlMultiRequest, I can only work with CAS_Request_CurlRequest objects.'
            );
        }

        $this->_requests[] = $request;
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

    /*********************************************************
     * 2. Send the Request
    *********************************************************/

    /**
     * Perform the request. After sending, all requests will have their
     * responses populated.
     *
     * @return bool TRUE on success, FALSE on failure.
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

        // Initialize our handles and configure all requests.
        $handles = [];
        $multiHandle = curl_multi_init();
        foreach ($this->_requests as $i => $request) {
            $handle = $request->_initAndConfigure();
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
            $handles[$i] = $handle;
            curl_multi_add_handle($multiHandle, $handle);
        }

        // Execute the requests in parallel.
        do {
            curl_multi_exec($multiHandle, $running);
        } while ($running > 0);

        // Populate all of the responses or errors back into the request objects.
        foreach ($this->_requests as $i => $request) {
            $buf = curl_multi_getcontent($handles[$i]);
            $request->_storeResponseBody($buf);
            curl_multi_remove_handle($multiHandle, $handles[$i]);
            curl_close($handles[$i]);
        }

        curl_multi_close($multiHandle);
    }
}
