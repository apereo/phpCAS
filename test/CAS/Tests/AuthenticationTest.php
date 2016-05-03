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
 * @file     CAS/Tests/AuthenticationTest.php
 * @category Authentication
 * @author   Adam Franco <afranco@middlebury.edu>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 */

namespace phpCAS\CAS\Tests;

use phpCAS\CAS;
use phpCAS\CAS\Client;
use phpCAS\CAS\GracefulTerminationException;
use phpCAS\CAS\TestHarness\BasicResponse;
use phpCAS\CAS\TestHarness\DummyRequest;
use PHPUnit_Framework_TestCase;

/**
 * Test class for verifying the operation of service tickets.
 *
 * @class    AuthenticationTest
 * @category Authentication
 * @author   Adam Franco <afranco@middlebury.edu>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 */
class AuthenticationTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Client
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp()
    {
        //     	CAS::setDebug(dirname(__FILE__).'/../test.log');
        // 		error_reporting(E_ALL);
        @session_start();

        GracefulTerminationException::throwInsteadOfExiting();

        $_SERVER['SERVER_NAME'] = 'www.clientapp.com';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $_SERVER['SERVER_ADMIN'] = 'root@localhost';
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['PHP_SELF'] = '/index.php';
        $_SESSION = [];

        $this->object = new Client(
            CAS::CAS_VERSION_2_0, // Server Version
            true, // Proxy
            'cas.example.edu', // Server Hostname
            443, // Server port
            '/cas/', // Server URI
            false // Start Session
        );

        $this->object->setRequestImplementation(DummyRequest::class);
        $this->object->setCasServerCACert('/path/to/ca_cert.crt', true);

        /*********************************************************
         * Enumerate our responses
         *********************************************************/

        // Set up our response.
        $response = new BasicResponse(
            'https', 'cas.example.edu', '/cas/serviceValidate'
        );
        $response->setResponseHeaders(
            ['HTTP/1.1 200 OK', 'Date: Wed, 29 Sep 2010 19:20:57 GMT',
                'Server: Apache-Coyote/1.1', 'Pragma: no-cache',
                'Expires: Thu, 01 Jan 1970 00:00:00 GMT',
                'Cache-Control: no-cache, no-store',
                'Content-Type: text/html;charset=UTF-8',
                'Content-Language: en-US', 'Via: 1.1 cas.example.edu',
                'Connection: close', 'Transfer-Encoding: chunked',
            ]
        );
        $response
            ->setResponseBody(
                "<cas:serviceResponse xmlns:cas='http://www.yale.edu/tp/cas'>
    <cas:authenticationSuccess>
        <cas:user>jsmith</cas:user>
    </cas:authenticationSuccess>
</cas:serviceResponse>
"
            );
        DummyRequest::addResponse($response);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown()
    {
        DummyRequest::clearResponses();
        $_SESSION = [];
    }

    /**
     * Test that the user is redirected to the CAS server.
     *
     * @return void
     */
    public function testRedirect()
    {
        try {
            ob_start();
            $this->object->forceAuthentication();
            $this->assertTrue(
                false, 'Should have thrown a GracefulTerminationException.'
            );
        } catch (GracefulTerminationException $e) {
            ob_end_clean();
            // It would be great to test for the existence of headers here, but
            // the don't get set properly due to output before the test.
        }
    }
}
