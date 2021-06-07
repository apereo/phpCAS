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
 * @file     CAS/Tests/MultiRequestTest.php
 * @category Authentication
 * @package  PhpCAS
 * @author   Adam Franco <afranco@middlebury.edu>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 */

namespace PhpCas\Tests;

use PhpCas\TestHarness\BasicResponse;
use PhpCas\TestHarness\DummyMultiRequest;
use PhpCas\TestHarness\DummyRequest;
use PHPUnit\Framework\TestCase;

/**
 * Test class for verifying the operation of service tickets.
 *
 * @class    MultiRequestTest
 * @category Authentication
 * @package  PhpCAS
 * @author   Adam Franco <afranco@middlebury.edu>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 */
class MultiRequestTest extends TestCase
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

        /*********************************************************
         * Enumerate our responses
         *********************************************************/
        $response = new BasicResponse(
            'http', 'www.jasig.org', '/some/path'
        );
        $response->ensureIsGet();
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
        $response->setResponseBody("I am Jasig");
        DummyRequest::addResponse($response);

        $response = new BasicResponse(
            'http', 'www.example.org', '/some/other/path'
        );
        $response->ensureIsGet();
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
        $response->setResponseBody("I am Example");
        DummyRequest::addResponse($response);

        $response = new BasicResponse(
            'http', 'www.educause.edu', '/path'
        );
        $response->ensureIsGet();
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
        $response->setResponseBody("I am Educause");
        DummyRequest::addResponse($response);

    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
        DummyRequest::clearResponses();
    }

    /**
     * Test a single request
     *
     * @return voud
     */
    public function testSingle()
    {
        $request = new DummyRequest();
        $request->setUrl('http://www.example.org/some/other/path');
        $this->assertTrue($request->send());
        $this->assertEquals("I am Example", $request->getResponseBody());
    }

    /**
     * Test a multiple requests
     *
     * @return void
     */
    public function testMultiple()
    {
        $multi = new DummyMultiRequest();

        $request1 = new DummyRequest();
        $request1->setUrl('http://www.jasig.org/some/path');
        $multi->addRequest($request1);

        $request2 = new DummyRequest();
        $request2->setUrl('http://www.example.org/some/other/path');
        $multi->addRequest($request2);

        $request3 = new DummyRequest();
        $request3->setUrl('http://www.educause.edu/path');
        $multi->addRequest($request3);

        $multi->send();

        $this->assertEquals("I am Jasig", $request1->getResponseBody());
        $this->assertEquals("I am Example", $request2->getResponseBody());
        $this->assertEquals("I am Educause", $request3->getResponseBody());
    }
}
?>
