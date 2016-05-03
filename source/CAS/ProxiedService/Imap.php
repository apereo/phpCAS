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
 * @file     CAS/ProxiedService/Imap.php
 * @category Authentication
 * @author   Adam Franco <afranco@middlebury.edu>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 */

namespace phpCAS\CAS\ProxiedService;

use phpCAS\CAS;
use phpCAS\CAS\InvalidArgumentException;
use phpCAS\CAS\OutOfSequenceException;
use phpCAS\CAS\ProxyTicketException;

/**
 * Provides access to a proxy-authenticated IMAP stream.
 *
 * @class    Imap
 * @category Authentication
 * @author   Adam Franco <afranco@middlebury.edu>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 */
class Imap extends AbstractProxiedService
{
    /**
     * The username to send via imap_open.
     *
     * @var string;
     */
    private $_username;

    /**
     * Constructor.
     *
     * @param string $username Username
     */
    public function __construct($username)
    {
        if (! is_string($username) || ! strlen($username)) {
            throw new InvalidArgumentException('Invalid username.');
        }

        $this->_username = $username;
    }

    /**
     * The target service url.
     * @var string;
     */
    private $_url;

    /**
     * Answer a service identifier (URL) for whom we should fetch a proxy ticket.
     *
     * @return string
     * @throws ProxiedServiceException If no service url is available.
     */
    public function getServiceUrl()
    {
        if (empty($this->_url)) {
            throw new ProxiedServiceException(
                'No URL set via '.get_class($this).'->getServiceUrl($url).'
            );
        }

        return $this->_url;
    }

    /*********************************************************
     * Configure the Stream
    *********************************************************/

    /**
     * Set the URL of the service to pass to CAS for proxy-ticket retrieval.
     *
     * @param string $url Url to set
     *
     * @return void
     * @throws OutOfSequenceException If called after the stream has been opened.
     * @throws InvalidArgumentException
     */
    public function setServiceUrl($url)
    {
        if ($this->hasBeenOpened()) {
            throw new OutOfSequenceException(
                'Cannot set the URL, stream already opened.'
            );
        }
        if (! is_string($url) || ! strlen($url)) {
            throw new InvalidArgumentException('Invalid url.');
        }

        $this->_url = $url;
    }

    /**
     * The mailbox to open. See the $mailbox parameter of imap_open().
     *
     * @var string
     */
    private $_mailbox;

    /**
     * Set the mailbox to open. See the $mailbox parameter of imap_open().
     *
     * @param string $mailbox Mailbox to set
     *
     * @return void
     * @throws OutOfSequenceException If called after the stream has been opened.
     * @throws InvalidArgumentException
     */
    public function setMailbox($mailbox)
    {
        if ($this->hasBeenOpened()) {
            throw new OutOfSequenceException(
                'Cannot set the mailbox, stream already opened.'
            );
        }
        if (! is_string($mailbox) || ! strlen($mailbox)) {
            throw new InvalidArgumentException('Invalid mailbox.');
        }

        $this->_mailbox = $mailbox;
    }

    /**
     * A bit mask of options to pass to imap_open() as the $options parameter.
     *
     * @var int
     */
    private $_options = null;

    /**
     * Set the options for opening the stream. See the $options parameter of
     * imap_open().
     *
     * @param int $options Options for the stream
     *
     * @return void
     * @throws OutOfSequenceException If called after the stream has been opened.
     * @throws InvalidArgumentException
     */
    public function setOptions($options)
    {
        if ($this->hasBeenOpened()) {
            throw new OutOfSequenceException(
                'Cannot set options, stream already opened.'
            );
        }
        if (! is_int($options)) {
            throw new InvalidArgumentException('Invalid options.');
        }

        $this->_options = $options;
    }

    /*********************************************************
     * 2. Open the stream
    *********************************************************/

    /**
     * Open the IMAP stream (similar to imap_open()).
     * @return resource Returns an IMAP stream on success
     * @throws OutOfSequenceException If called multiple times.
     * @throws ProxyTicketException If there is a proxy-ticket failure.
     *		The code of the Exception will be one of:
     *			CAS::PHPCAS_SERVICE_PT_NO_SERVER_RESPONSE
     *			CAS::PHPCAS_SERVICE_PT_BAD_SERVER_RESPONSE
     *			CAS::PHPCAS_SERVICE_PT_FAILURE
     * @throws ProxiedServiceException If there is a failure sending the
     *         request to the target service.
     */
    public function open()
    {
        if ($this->hasBeenOpened()) {
            throw new OutOfSequenceException('Stream already opened.');
        }
        if (empty($this->_mailbox)) {
            throw new ProxiedServiceException(
                'You must specify a mailbox via '.get_class($this)
                .'->setMailbox($mailbox)'
            );
        }

        CAS::traceBegin();

        // Get our proxy ticket and append it to our URL.
        $this->initializeProxyTicket();
        CAS::trace('opening IMAP mailbox `'.$this->_mailbox.'\'...');
        $this->_stream = @imap_open(
            $this->_mailbox, $this->_username, $this->getProxyTicket(),
            $this->_options
        );
        if ($this->_stream) {
            CAS::trace('ok');
        } else {
            CAS::trace('could not open mailbox');
            // @todo add localization integration.
            $message = 'IMAP Error: '.$this->_url.' '.var_export(imap_errors(), true);
            CAS::trace($message);
            throw new ProxiedServiceException($message);
        }

        CAS::traceEnd();

        return $this->_stream;
    }

    /**
     * Answer true if our request has been sent yet.
     *
     * @return bool
     */
    protected function hasBeenOpened()
    {
        return ! empty($this->_stream);
    }

    /*********************************************************
     * 3. Access the result
    *********************************************************/
    /**
     * The IMAP stream.
     *
     * @var resource
     */
    private $_stream;

    /**
     * Answer the IMAP stream.
     *
     * @return resource
     */
    public function getStream()
    {
        if (! $this->hasBeenOpened()) {
            throw new OutOfSequenceException(
                'Cannot access stream, not opened yet.'
            );
        }

        return $this->_stream;
    }

    /**
     * Client::serviceMail() needs to return the proxy ticket for some reason,
     * so this method provides access to it.
     *
     * @return string
     * @throws OutOfSequenceException If called before the stream has been
     * opened.
     */
    public function getImapProxyTicket()
    {
        if (! $this->hasBeenOpened()) {
            throw new OutOfSequenceException(
                'Cannot access errors, stream not opened yet.'
            );
        }

        return $this->getProxyTicket();
    }
}
