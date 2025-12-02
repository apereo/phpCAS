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
 * @file     CAS/Tests/CallbackTest.php
 * @category Authentication
 * @package  PhpCAS
 * @author   Phy <phpcas@phy25.com>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
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
class CallbackTest extends TestCase
{
    /**
     * @var CAS_Client
     */
    protected static $client;

    /**
     * @var \ReflectionClass
     */
    protected static $isXmlResponse;

    /**
     * Set up CAS_Client ReflectionClass
     */
    public static function setUpBeforeClass(): void
    {
        \CAS_GracefullTerminationException::throwInsteadOfExiting();

        self::$client = new \CAS_Client(
            CAS_VERSION_2_0, // Server Version
            true, // Proxy
            'cas.example.edu', // Server Hostname
            443, // Server port
            '/cas/', // Server URI
            'http://www.clientapp.com', // Service Name
            false // Start Session
        );

        $class = new \ReflectionClass('\CAS_Client');
        $method = $class->getMethod('isXmlResponse');
        if (PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }
        self::$isXmlResponse = $method;
    }

    /**
     * Test isXmlResponse
     *
     * @return void
     *
     * @dataProvider acceptXMLDataProvider
     */
    public function testAcceptXML($accept, $expected)
    {
        if ($accept !== false) {
            $_SERVER['HTTP_ACCEPT'] = $accept;
        } else {
            unset($_SERVER['HTTP_ACCEPT']);
        }
        $this->assertEquals($expected, self::$isXmlResponse->invokeArgs(self::$client, array()), $accept);
    }

    public static function acceptXMLDataProvider()
    {
        return array(
            array(false, false),
            array('application/xml', true),
            array('text/xml', true),
            array('text/html', false),
            array('text/html,application/xhtml+xml,application/xml;q=0.9', true),
            // XML has a higher priority than HTML
        );
    }
}
