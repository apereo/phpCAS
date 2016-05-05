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
 * @file     CAS/Tests/ProxyTicketValidationTest.php
 * @category Authentication
 * @author   Adam Franco <afranco@middlebury.edu>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 */

namespace phpCAS\CAS\Tests;

use phpCAS\CAS;
use phpCAS\CAS\AuthenticationException;
use phpCAS\CAS\Client;
use phpCAS\CAS\ProxyChain;
use phpCAS\CAS\ProxyChain\Any;
use phpCAS\CAS\ProxyChain\Trusted;
use phpCAS\CAS\TestHarness\BasicResponse;
use phpCAS\CAS\TestHarness\DummyRequest;
use PHPUnit_Framework_TestCase;

/**
 * Test class for verifying the operation of service tickets.
 *
 * @class    ProxyTicketValidationTest
 * @category Authentication
 * @author   Adam Franco <afranco@middlebury.edu>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 */
class ProxyTicketValidationTest extends PHPUnit_Framework_TestCase
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
        $_SERVER['SERVER_NAME'] = 'www.service.com';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $_SERVER['SERVER_ADMIN'] = 'root@localhost';
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['PHP_SELF'] = '/index.php';
        $_SESSION = [];

        // $_GET['ticket'] = 'ST-123456-asdfasdfasgww2323radf3';

        $this->object = new Client(
            CAS::CAS_VERSION_2_0, // Server Version
            false, // Proxy
            'cas.example.edu', // Server Hostname
            443, // Server port
            '/cas/', // Server URI
            false // Start Session
        );

        $this->object->setRequestImplementation('\phpCAS\CAS\TestHarness\DummyRequest');
        $this->object->setCasServerCACert('/path/to/ca_cert.crt', true);

        /*********************************************************
         * Enumerate our responses
         *********************************************************/
        // Valid ticket response
        $response = new BasicResponse(
            'https', 'cas.example.edu', '/cas/proxyValidate'
        );
        $response->matchQueryParameters(
            ['service' => 'http://www.service.com/',
                'ticket' => 'ST-123456-asdfasdfasgww2323radf3',
            ]
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
        $response->setResponseBody(
            "<cas:serviceResponse xmlns:cas='http://www.yale.edu/tp/cas'>
    <cas:authenticationSuccess>
        <cas:user>jsmith</cas:user>
        <cas:proxies>
            <cas:proxy>http://firstproxy.com/mysite/test</cas:proxy>
            <cas:proxy>https://anotherdomain.org/mysite/test2</cas:proxy>
        </cas:proxies>
    </cas:authenticationSuccess>
</cas:serviceResponse>
"
        );
        $response->ensureCaCertPathEquals('/path/to/ca_cert.crt');
        DummyRequest::addResponse($response);

        // Invalid ticket response
        $response = new BasicResponse(
            'https', 'cas.example.edu', '/cas/proxyValidate'
        );
        $response->matchQueryParameters(
            ['service' => 'http://www.service.com/']
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
        $response->setResponseBody(
            "<cas:serviceResponse xmlns:cas='http://www.yale.edu/tp/cas'>
    <cas:authenticationFailure code='INVALID_TICKET'>
        Ticket ST-1856339-aA5Yuvrxzpv8Tau1cYQ7 not recognized
    </cas:authenticationFailure>
</cas:serviceResponse>
"
        );
        $response->ensureCaCertPathEquals('/path/to/ca_cert.crt');
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
    }

    /**
     * Test that a service ticket can be successfully validated.
     *
     * @return void
     */
    public function testValidationSuccess()
    {
        $this->object->setTicket('ST-123456-asdfasdfasgww2323radf3');
        $this->object->getAllowedProxyChains()
            ->allowProxyChain(new Any());
        $result = $this->object
            ->validateCAS20($url, $text_response, $tree_response);
        $this->assertTrue($result);
        $this->assertEquals(
            "<cas:serviceResponse xmlns:cas='http://www.yale.edu/tp/cas'>
    <cas:authenticationSuccess>
        <cas:user>jsmith</cas:user>
        <cas:proxies>
            <cas:proxy>http://firstproxy.com/mysite/test</cas:proxy>
            <cas:proxy>https://anotherdomain.org/mysite/test2</cas:proxy>
        </cas:proxies>
    </cas:authenticationSuccess>
</cas:serviceResponse>
", $text_response
        );
        $this->assertInstanceOf('DOMElement', $tree_response);
    }

    /**
     * Test that our list of proxies is available.
     *
     * @return void
     */
    public function testValidationSuccessProxyList()
    {
        $this->object->setTicket('ST-123456-asdfasdfasgww2323radf3');
        $this->object->getAllowedProxyChains()
            ->allowProxyChain(new Any());
        $result = $this->object
            ->validateCAS20($url, $text_response, $tree_response);
        $this->assertTrue($result);
        $this->assertEquals(
            ['http://firstproxy.com/mysite/test',
                'https://anotherdomain.org/mysite/test2',
            ],
            $this->object->getProxies(),
            'The list of proxies in front of the client.'
        );
    }

    /**
     * Test that a service ticket can be successfully fails.
     *
     * @return void
     *
     * @expectedException \phpCAS\CAS\AuthenticationException
     * @outputBuffering enabled
     */
    public function testInvalidTicketFailure()
    {
        $this->object->setTicket('ST-1856339-aA5Yuvrxzpv8Tau1cYQ7');
        ob_start();
        $result = $this->object
            ->validateCAS20($url, $text_response, $tree_response);
        ob_end_clean();
        $this->assertTrue($result);
        $this->assertEquals(
            "<cas:serviceResponse xmlns:cas='http://www.yale.edu/tp/cas'>
    <cas:authenticationFailure code='INVALID_TICKET'>
        Ticket ST-1856339-aA5Yuvrxzpv8Tau1cYQ7 not recognized
    </cas:authenticationFailure>
</cas:serviceResponse>
",
            $text_response
        );
        $this->assertInstanceOf('DOMElement', $tree_response);
    }

    /**
     * Test that our list of proxies is not available on ticket failure.
     *
     * @return void
     */
    public function testInvalidTicketProxyList()
    {
        $this->object->setTicket('ST-1856339-aA5Yuvrxzpv8Tau1cYQ7');
        ob_start();
        try {
            $this->object
                ->validateCAS20($url, $text_response, $tree_response);
        } catch (AuthenticationException $e) {
        }
        ob_end_clean();
        $this->assertEquals(
            [], $this->object->getProxies(),
            'The list of proxies in front of the client.'
        );
    }

    /**
     * Test allowed proxies.
     *
     * @return void
     */
    public function testAllowedProxiesStringSuccess()
    {
        $this->object->setTicket('ST-123456-asdfasdfasgww2323radf3');
        $this->object->getAllowedProxyChains()->allowProxyChain(
            new ProxyChain(
                ['http://firstproxy.com',
                    'https://anotherdomain.org/mysite/test2',
                ]
            )
        );
        $this->object->getAllowedProxyChains()->allowProxyChain(
            new ProxyChain(
                ['https://anotherdomain.php']
            )
        );
        $result = $this->object
            ->validateCAS20($url, $text_response, $tree_response);
        $this->assertTrue($result);
        $this->assertEquals(
            "<cas:serviceResponse xmlns:cas='http://www.yale.edu/tp/cas'>
    <cas:authenticationSuccess>
        <cas:user>jsmith</cas:user>
        <cas:proxies>
            <cas:proxy>http://firstproxy.com/mysite/test</cas:proxy>
            <cas:proxy>https://anotherdomain.org/mysite/test2</cas:proxy>
        </cas:proxies>
    </cas:authenticationSuccess>
</cas:serviceResponse>
",
            $text_response
        );
        $this->assertInstanceOf('DOMElement', $tree_response);
    }
    /**
     * Test that the trusted proxy allows any proxies beyond the one we trust.
     *
     * @return void
     */
    public function testAllowedProxiesTrustedSuccess()
    {
        $this->object->setTicket('ST-123456-asdfasdfasgww2323radf3');
        $this->object->getAllowedProxyChains()->allowProxyChain(
            new Trusted(
                ['http://firstproxy.com']
            )
        );
        $this->object->getAllowedProxyChains()->allowProxyChain(
            new ProxyChain(
                ['https://anotherdomain.php']
            )
        );
        $result = $this->object
            ->validateCAS20($url, $text_response, $tree_response);
        $this->assertTrue($result);
        $this->assertEquals(
            "<cas:serviceResponse xmlns:cas='http://www.yale.edu/tp/cas'>
    <cas:authenticationSuccess>
        <cas:user>jsmith</cas:user>
        <cas:proxies>
            <cas:proxy>http://firstproxy.com/mysite/test</cas:proxy>
            <cas:proxy>https://anotherdomain.org/mysite/test2</cas:proxy>
        </cas:proxies>
    </cas:authenticationSuccess>
</cas:serviceResponse>
",
            $text_response
        );
        $this->assertInstanceOf('DOMElement', $tree_response);
    }

    /**
     * Test that proxies fail if one is missing from the chain.
     *
     * @return void
     *
     * @expectedException \phpCAS\CAS\AuthenticationException
     * @outputBuffering enabled
     */
    public function testAllowedProxiesStringFailureMissingProxy()
    {
        $this->object->setTicket('ST-123456-asdfasdfasgww2323radf3');
        $this->object->getAllowedProxyChains()->allowProxyChain(
            new ProxyChain(
                ['https://anotherdomain.php']
            )
        );
        $result = $this->object
            ->validateCAS20($url, $text_response, $tree_response);
        $this->assertFalse($result);
    }

    /**
     * Test that proxies fail if in wrong order and defined as string.
     *
     * @return void
     *
     * @expectedException \phpCAS\CAS\AuthenticationException
     * @outputBuffering enabled
     */
    public function testAllowedProxiesStringFailureWrongOrder()
    {
        $this->object->setTicket('ST-123456-asdfasdfasgww2323radf3');
        $this->object->getAllowedProxyChains()->allowProxyChain(
            new ProxyChain(
                ['https://anotherdomain.org/mysite/test2',
                    'http://firstproxy.com',
                ]
            )
        );
        $this->object->getAllowedProxyChains()->allowProxyChain(
            new ProxyChain(
                ['https://anotherdomain.php']
            )
        );
        $result = $this->object
            ->validateCAS20($url, $text_response, $tree_response);
        $this->assertFalse($result);
    }

    /**
     * Test that if proxies exist a response with proxies will fail unless allowed.
     *
     * @return void
     *
     * @expectedException \phpCAS\CAS\AuthenticationException
     * @outputBuffering enabled
     */
    public function testAllowedProxiesFailure()
    {
        $this->object->setTicket('ST-123456-asdfasdfasgww2323radf3');
        // By default no proxies are allowed.
        $result = $this->object
            ->validateCAS20($url, $text_response, $tree_response);
        $this->assertFalse($result);
    }

    /**
     * Test that regexp filtering of allowed proxies works.
     *
     * @return void
     */
    public function testAllowedProxiesRegexpSuccess()
    {
        $this->object->setTicket('ST-123456-asdfasdfasgww2323radf3');
        $this->object->getAllowedProxyChains()->allowProxyChain(
            new ProxyChain(['/badregexp/'])
        );
        $this->object->getAllowedProxyChains()->allowProxyChain(
            new ProxyChain(
                ['/http\:\/\/firstproxy\.com.*$/',
                    '/^https\:\/\/anotherdomain.org\/mysite\/test2$/',
                ]
            )
        );
        $result = $this->object
            ->validateCAS20($url, $text_response, $tree_response);
        $this->assertTrue($result);
    }

    /**
     * Wrong regexp to mach proxies.
     *
     * @return void
     *
     * @expectedException \phpCAS\CAS\AuthenticationException
     * @outputBuffering enabled
     */
    public function testAllowedProxiesRegexpFailureWrong()
    {
        $this->object->setTicket('ST-123456-asdfasdfasgww2323radf3');
        $this->object->getAllowedProxyChains()->allowProxyChain(
            new ProxyChain(
                ['/^http:\/\/secondproxy\.com/', '/^https.*$/']
            )
        );
        $result = $this->object->validateCAS20(
            $url, $text_response, $tree_response
        );
        $this->assertFalse($result);
    }

    /**
     * Wrong order of valid regexp.
     *
     * @return void
     *
     * @expectedException \phpCAS\CAS\AuthenticationException
     * @outputBuffering enabled
     */
    public function testAllowedProxiesRegexpFailureWrongOrder()
    {
        $this->object->setTicket('ST-123456-asdfasdfasgww2323radf3');
        $this->object->getAllowedProxyChains()->allowProxyChain(
            new ProxyChain(
                ['/^https\:\/\/anotherdomain.org\/mysite\/test2$/',
                    '/http\:\/\/firstproxy\.com.*$/',
                ]
            )
        );
        $result = $this->object->validateCAS20(
            $url, $text_response, $tree_response
        );
        $this->assertFalse($result);
    }
}
