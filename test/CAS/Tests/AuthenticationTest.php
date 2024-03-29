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
 * PHP Version 7
 *
 * @file     CAS/Tests/AuthenticationTest.php
 * @category Authentication
 * @package  PhpCAS
 * @author   Adam Franco <afranco@middlebury.edu>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 */

namespace PhpCas\Tests;

use PhpCas\TestHarness\BasicResponse;
use PhpCas\TestHarness\DummyRequest;
use PHPUnit\Framework\TestCase;

/**
 * Test class for verifying the operation of service tickets.
 *
 * @class    AuthenticationTest
 * @category Authentication
 * @package  PhpCAS
 * @author   Adam Franco <afranco@middlebury.edu>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 */
class AuthenticationTest extends TestCase
{
    /**
     * @var CAS_Client
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        //     	phpCAS::setDebug(dirname(__FILE__).'/../test.log');
        // 		error_reporting(E_ALL);

        \CAS_GracefullTerminationException::throwInsteadOfExiting();

        $_SERVER['SERVER_NAME'] = 'www.clientapp.com';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $_SERVER['SERVER_ADMIN'] = 'root@localhost';
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['PHP_SELF'] = '/index.php';
        $_SESSION = array();

        $this->object = new \CAS_Client(
            CAS_VERSION_2_0, // Server Version
            true, // Proxy
            'cas.example.edu', // Server Hostname
            443, // Server port
            '/cas/', // Server URI
            'http://www.clientapp.com', // Service Name
            false // Start Session
        );

        $this->object->setRequestImplementation('PhpCas\TestHarness\DummyRequest');
        $this->object->setCasServerCACert(__FILE__, true);

        /*********************************************************
         * Enumerate our responses
         *********************************************************/

        // Set up our response.
        $response = new BasicResponse(
            'https', 'cas.example.edu', '/cas/serviceValidate'
        );
        $response->setResponseHeaders(
            array('HTTP/1.1 200 OK', 'Date: Wed, 29 Sep 2010 19:20:57 GMT',
                'Server: Apache-Coyote/1.1', 'Pragma: no-cache',
                'Expires: Thu, 01 Jan 1970 00:00:00 GMT',
                'Cache-Control: no-cache, no-store',
                'Content-Type: text/html;charset=UTF-8',
                'Content-Language: en-US', 'Via: 1.1 cas.example.edu',
                'Connection: close', 'Transfer-Encoding: chunked',
            )
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
     */
    protected function tearDown(): void
    {
        DummyRequest::clearResponses();
        $_SESSION = array();
    }

    /**
     * Test that the user is redirected to the CAS server
     *
     * @return void
     */
    public function testRedirect()
    {
        ob_start();
        $this->expectException(\CAS_GracefullTerminationException::class);
        try {
            $this->object->forceAuthentication();
        } catch (\Exception $e) {
            ob_end_clean();
            throw $e;
        }
        ob_end_clean();
    }
}
