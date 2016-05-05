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
 * @file     CAS/ProxiedService/Http/Abstract.php
 * @category Authentication
 * @author   Adam Franco <afranco@middlebury.edu>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 */

namespace phpCAS\CAS\ProxiedService\Http;

use phpCAS\CAS;
use phpCAS\CAS\CookieJar;
use phpCAS\CAS\InvalidArgumentException;
use phpCAS\CAS\OutOfSequenceException;
use phpCAS\CAS\ProxiedService\AbstractProxiedService;
use phpCAS\CAS\ProxiedService\Http;
use phpCAS\CAS\ProxiedService\ProxiedServiceException;
use phpCAS\CAS\ProxyTicketException;
use phpCAS\CAS\Request\RequestInterface;

/**
 * This class implements common methods for ProxiedService implementations included
 * with CAS.
 *
 * @class    AbstractHttpProxiedService
 * @category Authentication
 * @author   Adam Franco <afranco@middlebury.edu>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 */
abstract class AbstractHttpProxiedService extends AbstractProxiedService implements Http
{
    /**
     * The HTTP request mechanism talking to the target service.
     *
     * @var RequestInterface
     */
    protected $requestHandler;

    /**
     * The storage mechanism for cookies set by the target service.
     *
     * @var CookieJar
     */
    private $_cookieJar;

    /**
     * Constructor.
     *
     * @param RequestInterface $requestHandler request handler object
     * @param CookieJar        $cookieJar      cookieJar object
     */
    public function __construct(
        RequestInterface $requestHandler,
        CookieJar $cookieJar
    ) {
        $this->requestHandler = $requestHandler;
        $this->_cookieJar = $cookieJar;
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
                'No URL set via '.get_class($this).'->setUrl($url).'
            );
        }

        return $this->_url;
    }

    /*********************************************************
     * Configure the Request
     *********************************************************/

    /**
     * Set the URL of the Request.
     *
     * @param string $url url to set
     *
     * @return void
     * @throws OutOfSequenceException If called after the Request has been sent.
     * @throws InvalidArgumentException
     */
    public function setUrl($url)
    {
        if ($this->hasBeenSent()) {
            throw new OutOfSequenceException(
                'Cannot set the URL, request already sent.'
            );
        }
        if (! is_string($url)) {
            throw new InvalidArgumentException('$url must be a string.');
        }

        $this->_url = $url;
    }

    /*********************************************************
     * 2. Send the Request
     *********************************************************/

    /**
     * Perform the request.
     *
     * @return void
     * @throws OutOfSequenceException If called multiple times.
     * @throws ProxyTicketException If there is a proxy-ticket failure.
     *		The code of the Exception will be one of:
     *			CAS::CAS_SERVICE_PT_NO_SERVER_RESPONSE
     *			CAS::CAS_SERVICE_PT_BAD_SERVER_RESPONSE
     *			CAS::CAS_SERVICE_PT_FAILURE
     * @throws ProxiedServiceException If there is a failure sending the
     * request to the target service.
     */
    public function send()
    {
        if ($this->hasBeenSent()) {
            throw new OutOfSequenceException(
                'Cannot send, request already sent.'
            );
        }

        CAS::traceBegin();

        // Get our proxy ticket and append it to our URL.
        $this->initializeProxyTicket();
        $url = $this->getServiceUrl();
        if (strstr($url, '?') === false) {
            $url = $url.'?ticket='.$this->getProxyTicket();
        } else {
            $url = $url.'&ticket='.$this->getProxyTicket();
        }

        try {
            $this->makeRequest($url);
        } catch (ProxiedServiceException $e) {
            CAS::traceEnd();
            throw $e;
        }
    }

    /**
     * Indicator of the number of requests (including redirects performed.
     *
     * @var int;
     */
    private $_numRequests = 0;

    /**
     * The response headers.
     *
     * @var array;
     */
    private $_responseHeaders = [];

    /**
     * The response status code.
     *
     * @var string;
     */
    private $_responseStatusCode = '';

    /**
     * The response headers.
     *
     * @var string;
     */
    private $_responseBody = '';

    /**
     * Build and perform a request, following redirects.
     *
     * @param string $url url for the request
     *
     * @return void
     * @throws ProxyTicketException If there is a proxy-ticket failure.
     *		The code of the Exception will be one of:
     *			CAS_SERVICE_PT_NO_SERVER_RESPONSE
     *			CAS_SERVICE_PT_BAD_SERVER_RESPONSE
     *			CAS_SERVICE_PT_FAILURE
     * @throws ProxiedServiceException If there is a failure sending the
     * request to the target service.
     */
    protected function makeRequest($url)
    {
        // Verify that we are not in a redirect loop
        $this->_numRequests++;
        if ($this->_numRequests > 4) {
            $message = 'Exceeded the maximum number of redirects (3) in proxied service request.';
            CAS::trace($message);
            throw new ProxiedServiceException($message);
        }

        // Create a new request.
        $request = clone $this->requestHandler;
        $request->setUrl($url);

        // Add any cookies to the request.
        $request->addCookies($this->_cookieJar->getCookies($url));

        // Add any other parts of the request needed by concrete classes
        $this->populateRequest($request);

        // Perform the request.
        CAS::trace('Performing proxied service request to \''.$url.'\'');
        if (! $request->send()) {
            $message = 'Could not perform proxied service request to URL`'
            .$url.'\'. '.$request->getErrorMessage();
            CAS::trace($message);
            throw new ProxiedServiceException($message);
        }

        // Store any cookies from the response;
        $this->_cookieJar->storeCookies($url, $request->getResponseHeaders());

        // Follow any redirects
        if ($redirectUrl = $this->getRedirectUrl($request->getResponseHeaders())
        ) {
            CAS::trace('Found redirect:'.$redirectUrl);
            $this->makeRequest($redirectUrl);
        } else {
            $this->_responseHeaders = $request->getResponseHeaders();
            $this->_responseBody = $request->getResponseBody();
            $this->_responseStatusCode = $request->getResponseStatusCode();
        }
    }

    /**
     * Add any other parts of the request needed by concrete classes.
     *
     * @param RequestInterface $request request interface object
     *
     * @return void
     */
    abstract protected function populateRequest(RequestInterface $request);

    /**
     * Answer a redirect URL if a redirect header is found, otherwise null.
     *
     * @param array $responseHeaders response header to extract a redirect from
     *
     * @return string or null
     */
    protected function getRedirectUrl(array $responseHeaders)
    {
        // Check for the redirect after authentication
        foreach ($responseHeaders as $header) {
            if (preg_match('/^(Location:|URI:)\s*([^\s]+.*)$/', $header, $matches)
            ) {
                return trim(array_pop($matches));
            }
        }

        return;
    }

    /*********************************************************
     * 3. Access the response
     *********************************************************/

    /**
     * Answer true if our request has been sent yet.
     *
     * @return bool
     */
    protected function hasBeenSent()
    {
        return ($this->_numRequests > 0);
    }

    /**
     * Answer the headers of the response.
     *
     * @return array An array of header strings.
     * @throws OutOfSequenceException If called before the Request has been sent.
     */
    public function getResponseHeaders()
    {
        if (! $this->hasBeenSent()) {
            throw new OutOfSequenceException(
                'Cannot access response, request not sent yet.'
            );
        }

        return $this->_responseHeaders;
    }

    /**
     * Answer HTTP status code of the response.
     *
     * @return int
     * @throws OutOfSequenceException If called before the Request has been sent.
     */
    public function getResponseStatusCode()
    {
        if (! $this->hasBeenSent()) {
            throw new OutOfSequenceException(
                'Cannot access response, request not sent yet.'
            );
        }

        return $this->_responseStatusCode;
    }

    /**
     * Answer the body of response.
     *
     * @return string
     * @throws OutOfSequenceException If called before the Request has been sent.
     */
    public function getResponseBody()
    {
        if (! $this->hasBeenSent()) {
            throw new OutOfSequenceException(
                'Cannot access response, request not sent yet.'
            );
        }

        return $this->_responseBody;
    }

    /**
     * Answer the cookies from the response. This may include cookies set during
     * redirect responses.
     *
     * @return array An array containing cookies. E.g. array('name' => 'val');
     */
    public function getCookies()
    {
        return $this->_cookieJar->getCookies($this->getServiceUrl());
    }
}
