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
 * @file     CAS/Tests/ServiceWebTest.php
 * @category Authentication
 * @package  PhpCAS
 * @author   Adam Franco <afranco@middlebury.edu>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 */

/**
 * Test class for verifying the operation of service tickets.
 *
 * @class    CAS_Tests_ServiceWebTest
 * @category Authentication
 * @package  PhpCAS
 * @author   Adam Franco <afranco@middlebury.edu>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 */
class CAS_Tests_ServiceWebTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var CAS_Client
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
        //     	phpCAS::setDebug(dirname(__FILE__).'/../test.log');
        // 		error_reporting(E_ALL);

        $_SERVER['SERVER_NAME'] = 'www.clientapp.com';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $_SERVER['SERVER_ADMIN'] = 'root@localhost';
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['PHP_SELF'] = '/index.php';
        $_SESSION = array();

        $this->object = new CAS_Client(
            CAS_VERSION_2_0, // Server Version
            true, // Proxy
            'cas.example.edu', // Server Hostname
            443, // Server port
            '/cas/', // Server URI
            false // Start Session
        );

        $this->object->setRequestImplementation('CAS_TestHarness_DummyRequest');
        $this->object->setCasServerCACert('/path/to/ca_cert.crt', true);

        // Bypass PGT storage since CAS_Client->callback() will exit. Just build
        // up the session manually so that we are in a state from which we can
        // attempt to fetch proxy tickets and make proxied requests.
        $_SESSION['phpCAS']['user'] = 'jdoe';
        $_SESSION['phpCAS']['pgt'] = 'PGT-clientapp-abc123';
        $_SESSION['phpCAS']['proxies'] = array();
        $_SESSION['phpCAS']['service_cookies'] = array();
        $_SESSION['phpCAS']['attributes'] = array();

        // Force Authentication to initialize the client.
        $this->object->forceAuthentication();

        /*********************************************************
         * Enumerate our responses
         *********************************************************/

        /*********************************************************
         * 1. Valid Proxy ticket and service
         *********************************************************/

        // Proxy ticket Response
        $response = new CAS_TestHarness_BasicResponse(
            'https', 'cas.example.edu', '/cas/proxy'
        );
        $response->matchQueryParameters(
            array(
                'targetService' => 'http://www.service.com/my_webservice',
                'pgt' => 'PGT-clientapp-abc123',
            )
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
        $response->setResponseBody(
            "<cas:serviceResponse xmlns:cas='http://www.yale.edu/tp/cas'>
    <cas:proxySuccess>
        <cas:proxyTicket>PT-asdfas-dfasgww2323radf3</cas:proxyTicket>
    </cas:proxySuccess>
</cas:serviceResponse>
"
        );
        $response->ensureCaCertPathEquals('/path/to/ca_cert.crt');
        CAS_TestHarness_DummyRequest::addResponse($response);

        // Valid Service Response
        $response = new CAS_TestHarness_BasicResponse(
            'http', 'www.service.com', '/my_webservice'
        );
        $response->matchQueryParameters(
            array('ticket' => 'PT-asdfas-dfasgww2323radf3',)
        );
        $response->ensureIsGet();
        $response->setResponseHeaders(
            array('HTTP/1.1 200 OK', 'Date: Wed, 29 Sep 2010 19:20:57 GMT',
                'Server: Apache-Coyote/1.1', 'Pragma: no-cache',
                'Expires: Thu, 01 Jan 1970 00:00:00 GMT',
                'Cache-Control: no-cache, no-store',
                'Content-Type: text/plain;charset=UTF-8',
                'Content-Language: en-US', 'Via: 1.1 cas.example.edu',
                'Connection: close', 'Transfer-Encoding: chunked',
            )
        );
        $response->setResponseBody("Hello from the service.");
        CAS_TestHarness_DummyRequest::addResponse($response);

        /*********************************************************
         * 2. Proxy Ticket Error
         *********************************************************/

        // Error Proxy ticket Response
        $response = new CAS_TestHarness_BasicResponse(
            'https', 'cas.example.edu', '/cas/proxy'
        );
        $response->matchQueryParameters(
            array(
                'targetService' => 'http://www.service.com/my_other_webservice',
                'pgt' => 'PGT-clientapp-abc123',
            )
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
        $response->setResponseBody(
            "<cas:serviceResponse xmlns:cas='http://www.yale.edu/tp/cas'>
    <cas:proxyFailure code='INTERNAL_ERROR'>
        an internal error occurred during ticket validation
    </cas:proxyFailure>
</cas:serviceResponse>
"
        );

        $response->ensureCaCertPathEquals('/path/to/ca_cert.crt');
        CAS_TestHarness_DummyRequest::addResponse($response);

        /*********************************************************
         * 3. Server that doesn't respond/exist (sending failure)
         *********************************************************/

        // Proxy ticket Response
        $response = new CAS_TestHarness_BasicResponse(
            'https', 'cas.example.edu', '/cas/proxy'
        );
        $response->matchQueryParameters(
            array('targetService' => 'ssh://me.example.net',
                'pgt' => 'PGT-clientapp-abc123',
            )
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
        $response->setResponseBody(
            "<cas:serviceResponse xmlns:cas='http://www.yale.edu/tp/cas'>
    <cas:proxySuccess>
        <cas:proxyTicket>PT-ssh-1234abce</cas:proxyTicket>
    </cas:proxySuccess>
</cas:serviceResponse>
"
        );
        $response->ensureCaCertPathEquals('/path/to/ca_cert.crt');
        CAS_TestHarness_DummyRequest::addResponse($response);

        /*********************************************************
         * 4. Service With Error status.
         *********************************************************/

        // Proxy ticket Response
        $response = new CAS_TestHarness_BasicResponse(
            'https', 'cas.example.edu', '/cas/proxy'
        );
        $response->matchQueryParameters(
            array(
                'targetService' => 'http://www.service.com/my_webservice_that_has_problems',
                'pgt' => 'PGT-clientapp-abc123',
            )
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
        $response->setResponseBody(
            "<cas:serviceResponse xmlns:cas='http://www.yale.edu/tp/cas'>
    <cas:proxySuccess>
        <cas:proxyTicket>PT-12345-abscasdfasdf</cas:proxyTicket>
    </cas:proxySuccess>
</cas:serviceResponse>
"
        );
        $response->ensureCaCertPathEquals('/path/to/ca_cert.crt');
        CAS_TestHarness_DummyRequest::addResponse($response);

        // Service Error Response
        $response = new CAS_TestHarness_BasicResponse(
            'http', 'www.service.com', '/my_webservice_that_has_problems'
        );
        $response->matchQueryParameters(
            array('ticket' => 'PT-12345-abscasdfasdf',)
        );
        $response->ensureIsGet();
        $response->setResponseHeaders(
            array('HTTP/1.1 500 INTERNAL SERVER ERROR',
                'Date: Wed, 29 Sep 2010 19:20:57 GMT',
                'Server: Apache-Coyote/1.1', 'Pragma: no-cache',
                'Expires: Thu, 01 Jan 1970 00:00:00 GMT',
                'Cache-Control: no-cache, no-store',
                'Content-Type: text/plain;charset=UTF-8',
                'Content-Language: en-US', 'Via: 1.1 cas.example.edu',
                'Connection: close', 'Transfer-Encoding: chunked',
            )
        );
        $response->setResponseBody("Problems have Occurred.");
        CAS_TestHarness_DummyRequest::addResponse($response);

        /*********************************************************
         * 5. Valid Proxy ticket and POST service
         *********************************************************/

        // Proxy ticket Response
        $response = new CAS_TestHarness_BasicResponse(
            'https', 'cas.example.edu', '/cas/proxy'
        );
        $response->matchQueryParameters(
            array(
                'targetService' => 'http://www.service.com/post_webservice',
                'pgt' => 'PGT-clientapp-abc123',
            )
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
        $response->setResponseBody(
            "<cas:serviceResponse xmlns:cas='http://www.yale.edu/tp/cas'>
    <cas:proxySuccess>
        <cas:proxyTicket>PT-posting-dfasgww2323radf3</cas:proxyTicket>
    </cas:proxySuccess>
</cas:serviceResponse>
"
        );
        $response->ensureCaCertPathEquals('/path/to/ca_cert.crt');
        CAS_TestHarness_DummyRequest::addResponse($response);

        // Valid Service Response
        $response = new CAS_TestHarness_BasicResponse(
            'http', 'www.service.com', '/post_webservice'
        );
        $response->matchQueryParameters(
            array('ticket' => 'PT-posting-dfasgww2323radf3',)
        );
        $response->ensureIsPost();
        $response->ensurePostBodyEquals(
            '<request><method>doSomething</method><param type="string">with this</param></request>'
        );
        $response->ensureHasHeader(
            'Content-Length: '
            . strlen(
                '<request><method>doSomething</method><param type="string">with this</param></request>'
            )
        );
        $response->ensureHasHeader('Content-Type: text/xml');
        $response->setResponseHeaders(
            array('HTTP/1.1 200 OK', 'Date: Wed, 29 Sep 2010 19:20:57 GMT',
                'Server: Apache-Coyote/1.1', 'Pragma: no-cache',
                'Expires: Thu, 01 Jan 1970 00:00:00 GMT',
                'Cache-Control: no-cache, no-store',
                'Content-Type: text/xml;charset=UTF-8',
                'Content-Language: en-US', 'Via: 1.1 cas.example.edu',
                'Connection: close', 'Transfer-Encoding: chunked',
            )
        );
        $response->setResponseBody(
            "<result><string>Yay, it worked.</string></result>"
        );
        CAS_TestHarness_DummyRequest::addResponse($response);

    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown()
    {
        CAS_TestHarness_DummyRequest::clearResponses();
    }

    /**
     * Test that we can at least retrieve a proxy-ticket for the service.
     *
     * @return void
     */
    public function testRetrievePT()
    {
        $pt = $this->object->retrievePT(
            'http://www.service.com/my_webservice', $err_code, $err_msg
        );
        $this->assertEquals('PT-asdfas-dfasgww2323radf3', $pt);
    }

    /**
     * Test that we can at least retrieve a proxy-ticket for the service.
     *
     * @return void
     */
    public function testServiceWeb()
    {
        $result = $this->object->serviceWeb(
            'http://www.service.com/my_webservice', $err_code, $output
        );
        $this->assertTrue($result, $output);
        $this->assertEquals(PHPCAS_SERVICE_OK, $err_code);
        $this->assertEquals("Hello from the service.", $output);
    }

    /**
     * Verify that proxy-ticket Exceptions are caught and converted to error
     * codes in serviceWeb().
     *
     * @return void
     */
    public function testServiceWebPtError()
    {
        $result = $this->object->serviceWeb(
            'http://www.service.com/my_other_webservice', $err_code, $output
        );
        $this->assertFalse(
            $result,
            "serviceWeb() should have returned false on a PT error."
        );
        $this->assertEquals(PHPCAS_SERVICE_PT_FAILURE, $err_code);
        $this->assertStringStartsWith("PT retrieving failed", $output);
    }

    /**
     * Verify that proxied-service Exceptions are caught and converted to error
     * codes in serviceWeb().
     *
     * @return void
     */
    public function testServiceWebServiceError()
    {
        $result = $this->object->serviceWeb(
            'ssh://me.example.net', $err_code, $output
        );
        $this->assertFalse(
            $result,
            "serviceWeb() should have returned false on a service error."
        );
        $this->assertEquals(PHPCAS_SERVICE_NOT_AVAILABLE, $err_code);
        $this->assertStringStartsWith("The service", $output);
    }

    /**
     * Direct usage of the Proxied GET service.
     *
     * @return void
     */
    public function testHttpGet()
    {
        $service = $this->object
            ->getProxiedService(PHPCAS_PROXIED_SERVICE_HTTP_GET);
        $service->setUrl('http://www.service.com/my_webservice');
        $service->send();
        $this->assertEquals(200, $service->getResponseStatusCode());
        $this->assertEquals(
            "Hello from the service.", $service->getResponseBody()
        );
    }

    /**
     * Verify that a CAS_ProxyTicketException is thrown if we try to access a service
     * that results in a proxy-ticket failure.
     *
     * @return void
     *
     * @expectedException CAS_ProxyTicketException
     */
    public function testPtException()
    {
        $service = $this->object
            ->getProxiedService(PHPCAS_PROXIED_SERVICE_HTTP_GET);
        $service->setUrl('http://www.service.com/my_other_webservice');
        $this->assertFalse($service->send(), 'Sending should have failed');
    }

    /**
     * Verify that sending fails if we try to access a service
     * that has a valid proxy ticket, but where the service has a sending error.
     *
     * @return void
     *
     * @expectedException CAS_ProxiedService_Exception
     */
    public function testHttpGetServiceFailure()
    {
        $service = $this->object
            ->getProxiedService(PHPCAS_PROXIED_SERVICE_HTTP_GET);
        $service->setUrl('ssh://me.example.net');
        $service->send();
    }

    /**
     * Verify that sending fails if we try to access a service
     * that has a valid proxy ticket, but where the service sends an HTTP error
     * status.
     *
     * @return void
     */
    public function testHttpGetService500Error()
    {
        $service = $this->object
            ->getProxiedService(PHPCAS_PROXIED_SERVICE_HTTP_GET);
        $service
            ->setUrl('http://www.service.com/my_webservice_that_has_problems');
        $service->send();
        $this->assertEquals(500, $service->getResponseStatusCode());
        $this->assertEquals(
            "Problems have Occurred.", $service->getResponseBody()
        );
    }

    /**
     * Direct usage of the Proxied POST service.
     *
     * @return void
     */
    public function testHttpPost()
    {
        $service = $this->object
            ->getProxiedService(PHPCAS_PROXIED_SERVICE_HTTP_POST);
        $service->setUrl('http://www.service.com/post_webservice');
        $service->setBody(
            '<request><method>doSomething</method><param type="string">with this</param></request>'
        );
        $service->setContentType('text/xml');
        $service->send();
        $this->assertEquals(200, $service->getResponseStatusCode());
        $this->assertEquals(
            "<result><string>Yay, it worked.</string></result>",
            $service->getResponseBody()
        );
    }
}
?>
