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
 * @file     CAS/ProxiedService/Abstract.php
 * @category Authentication
 * @author   Adam Franco <afranco@middlebury.edu>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 */

namespace phpCAS\CAS\ProxiedService;

use phpCAS\CAS;
use phpCAS\CAS\Client;
use phpCAS\CAS\InvalidArgumentException;
use phpCAS\CAS\OutOfSequenceException;
use phpCAS\CAS\ProxiedService;

/**
 * This class implements common methods for ProxiedService implementations included
 * with CAS.
 *
 * @class    AbstractProxiedService
 * @category Authentication
 * @author   Adam Franco <afranco@middlebury.edu>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 */
abstract class AbstractProxiedService implements ProxiedService, Testable
{
    /**
     * The proxy ticket that can be used when making service requests.
     * @var string;
     */
    private $_proxyTicket;

    /**
     * Register a proxy ticket with the Proxy that it can use when making requests.
     *
     * @param string $proxyTicket proxy ticket
     *
     * @return void
     * @throws InvalidArgumentException If the $proxyTicket is invalid.
     * @throws OutOfSequenceException If called after a proxy ticket has
     *         already been initialized/set.
     */
    public function setProxyTicket($proxyTicket)
    {
        if (empty($proxyTicket)) {
            throw new InvalidArgumentException(
                'Trying to initialize with an empty proxy ticket.'
            );
        }
        if (! empty($this->_proxyTicket)) {
            throw new OutOfSequenceException(
                'Already initialized, cannot change the proxy ticket.'
            );
        }
        $this->_proxyTicket = $proxyTicket;
    }

    /**
     * Answer the proxy ticket to be used when making requests.
     *
     * @return string
     * @throws OutOfSequenceException If called before a proxy ticket has
     * already been initialized/set.
     */
    protected function getProxyTicket()
    {
        if (empty($this->_proxyTicket)) {
            throw new OutOfSequenceException(
                'No proxy ticket yet. Call $this->initializeProxyTicket() to aquire the proxy ticket.'
            );
        }

        return $this->_proxyTicket;
    }

    /**
     * @var Client;
     */
    private $_casClient;

    /**
     * Use a particular Client->initializeProxiedService() rather than the
     * static CAS::initializeProxiedService().
     *
     * This method should not be called in standard operation, but is needed for unit
     * testing.
     *
     * @param Client $casClient cas client
     *
     * @return void
     * @throws OutOfSequenceException If called after a proxy ticket has
     * already been initialized/set.
     */
    public function setCasClient(Client $casClient)
    {
        if (! empty($this->_proxyTicket)) {
            throw new OutOfSequenceException(
                'Already initialized, cannot change the CAS_Client.'
            );
        }

        $this->_casClient = $casClient;
    }

    /**
     * Fetch our proxy ticket.
     *
     * Descendant classes should call this method once their service URL is available
     * to initialize their proxy ticket.
     *
     * @return void
     * @throws OutOfSequenceException If called after a proxy ticket has
     * already been initialized.
     */
    protected function initializeProxyTicket()
    {
        if (! empty($this->_proxyTicket)) {
            throw new OutOfSequenceException(
                'Already initialized, cannot initialize again.'
            );
        }
        // Allow usage of a particular CAS_Client for unit testing.
        if (empty($this->_casClient)) {
            CAS::initializeProxiedService($this);
        } else {
            $this->_casClient->initializeProxiedService($this);
        }
    }
}
