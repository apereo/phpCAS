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
 * @file     CAS/Tests/ServiceMailTest.php
 * @category Authentication
 * @package  PhpCAS
 * @author   Adam Franco <afranco@middlebury.edu>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 */

/**
 * Test class for verifying the operation of service tickets.
 *
 * @class    CAS_Tests_ServiceMailTest
 * @category Authentication
 * @package  PhpCAS
 * @author   Adam Franco <afranco@middlebury.edu>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 */
class CAS_Tests_ServiceMailTest extends PHPUnit_Framework_TestCase
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
                'targetService' => 'imap://mail.example.edu/path/to/something',
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

        /*********************************************************
         * 2. Proxy Ticket Error
         *********************************************************/

        // Error Proxy ticket Response
        $response = new CAS_TestHarness_BasicResponse(
            'https', 'cas.example.edu', '/cas/proxy'
        );
        $response->matchQueryParameters(
            array(
                'targetService' => 'imap://mail.example.edu/path/that/doesnt/exist',
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
         * Ensure that IMAP constants are defined even if the IMAP
         * module is not installed.
         *********************************************************/
        if (!defined('OP_READONLY')) {
            // Not sure what this should actually  be. It is defined as:
            //  REGISTER_LONG_CONSTANT(
            //      "OP_READONLY", OP_READONLY, CONST_PERSISTENT | CONST_CS
            //  );
            // in http://php-imap.sourcearchive.com/lines/5.1.2-1/php__imap_8c-source.html
            // For now, just ensure that it is an integer.
            define('OP_READONLY', 1);
        }

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
            'imap://mail.example.edu/path/to/something', $err_code, $err_msg
        );
        $this->assertEquals('PT-asdfas-dfasgww2323radf3', $pt);
    }

    /**
     * Test that we can at least retrieve a proxy-ticket for the service.
     *
     * @return void
     */
    public function testServiceMail()
    {
        // Stop here and mark this test as incomplete.
        $this->markTestIncomplete('This test has not been implemented yet.');

        //      $stream = $this->object->serviceMail(
        //          'mailbox_name',
        //          'imap://mail.example.edu/path/to/something',
        //          OP_READONLY, $err_code, $err_msg, $pt
        //      );
        //      $this->assertInternalType('resource', $stream);
        //      $this->assertEquals(PHPCAS_SERVICE_OK, $err_code);
        //      $this->assertEquals('', $err_msg);
        //      $this->assertEquals('PT-asdfas-dfasgww2323radf3', $pt);
    }

    /**
     * Verify that proxy-ticket Exceptions are caught and converted to error
     * codes in serviceMail().
     *
     * @return void
     */
    public function testServiceMailPtError()
    {
        $stream = $this->object->serviceMail(
            'mailbox_name', 'imap://mail.example.edu/path/that/doesnt/exist',
            OP_READONLY, $err_code, $err_msg, $pt
        );
        $this->assertFalse(
            $stream, "serviceMail() should have returned false on a PT error."
        );
        $this->assertEquals(PHPCAS_SERVICE_PT_FAILURE, $err_code);
        $this->assertStringStartsWith("PT retrieving failed", $err_msg);
        $this->assertFalse($pt, '$pt should be false.');
    }

    /**
     * Verify that proxied-service Exceptions are caught and converted to error
     * codes in serviceMail().
     *
     * @return void
     */
    public function testServiceMailServiceError()
    {
        // Stop here and mark this test as incomplete.
        $this->markTestIncomplete('This test has not been implemented yet.');

        //      $stream = $this->object->serviceMail(
        //          'mailbox_name', 'ssh://me.example.net', OP_READONLY,
        //          $err_code, $err_msg, $pt
        //      );
        //      $this->assertFalse(
        //          $stream,
        //          "serviceMail() should have returned false on a service error."
        //      );
        //      $this->assertEquals(PHPCAS_SERVICE_NOT_AVAILABLE, $err_code);
        //      $this->assertStringStartsWith("The service", $err_msg);
        //      $this->assertFalse($pt, '$pt should be false.');
    }

    /**
     * Direct usage of the Proxied Imap service.
     *
     * @return void
     */
    public function testImap()
    {
        // Stop here and mark this test as incomplete.
        $this->markTestIncomplete('This test has not been implemented yet.');

        //     	$service = $this->object->getProxiedService(
        //          PHPCAS_PROXIED_SERVICE_IMAP
        //      );
        //     	$service->setServiceUrl('imap://mail.example.edu/path/to/something');
        //     	$service->setMailbox('mailbox_name');
        //     	$service->setOptions(OP_READONLY);
        //     	$stream = $service->open();
        //     	$this->assertInternalType('resource', $stream);
        //     	$this->assertInternalType('resource', $service->getStream());
        //     	$this->assertEquals(
        //          'PT-asdfas-dfasgww2323radf3', $service->getImapProxyTicket()
        //      );

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
        $service = $this->object->getProxiedService(PHPCAS_PROXIED_SERVICE_IMAP);
        $service->setServiceUrl(
            'imap://mail.example.edu/path/that/doesnt/exist'
        );
        $service->setMailbox('mailbox_name');
        $service->setOptions(OP_READONLY);
        $stream = $service->open();
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
        // Stop here and mark this test as incomplete.
        $this->markTestIncomplete('This test has not been implemented yet.');

        //     	$service = $this->object->getProxiedService(
        //          PHPCAS_PROXIED_SERVICE_IMAP
        //      );
        //     	$service->setServiceUrl('ssh://me.example.net');
        //     	$service->setMailbox('mailbox_name');
        //     	$service->setOptions(OP_READONLY);
        //     	$stream = $service->open();
    }
}
?>
