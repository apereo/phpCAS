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
 * @file     CAS/Tests/Cas20AttributeTest.php
 * @category Authentication
 * @package  PhpCAS
 * @author   Adam Franco <afranco@middlebury.edu>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 */

/**
 * Test class for verifying the operation of service tickets.
 *
 * @class    CAS_Tests_Cas20AttributeTest
 * @category Authentication
 * @package  PhpCAS
 * @author   Adam Franco <afranco@middlebury.edu>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 */
class CAS_Tests_Cas20AttributesTest extends PHPUnit_Framework_TestCase
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
        $_SERVER['SERVER_NAME'] = 'www.service.com';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $_SERVER['SERVER_ADMIN'] = 'root@localhost';
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['PHP_SELF'] = '/index.php';
        $_SESSION = array();

        $this->object = new CAS_Client(
            CAS_VERSION_2_0, // Server Version
            false, // Proxy
            'cas.example.edu', // Server Hostname
            443, // Server port
            '/cas/', // Server URI
            false // Start Session
        );

        $this->object->setRequestImplementation('CAS_TestHarness_DummyRequest');
        $this->object->setCasServerCACert('/path/to/ca_cert.crt', true);
        $this->object->setNoClearTicketsFromUrl();
        // 		phpCAS::setDebug(dirname(__FILE__).'/../test.log');
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
     * Verify that phpCAS will successfully fetch RubyCAS-style attributes:
     *
     * @return void
     */
    public function testRubycasAttributes()
    {
        // Set up our response.
        $response = new CAS_TestHarness_BasicResponse(
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
        $response->setResponseBody(
            "<cas:serviceResponse xmlns:cas='http://www.yale.edu/tp/cas'>
    <cas:authenticationSuccess>
        <cas:user>jsmith</cas:user>

        <cas:attraStyle>RubyCAS</cas:attraStyle>
        <cas:surname>Smith</cas:surname>
        <cas:givenName>John</cas:givenName>
        <cas:memberOf>CN=Staff,OU=Groups,DC=example,DC=edu</cas:memberOf>
        <cas:memberOf>CN=Spanish Department,OU=Departments,OU=Groups,DC=example,DC=edu</cas:memberOf>

        <cas:proxyGrantingTicket>PGTIOU-84678-8a9d2sfa23casd</cas:proxyGrantingTicket>
    </cas:authenticationSuccess>
</cas:serviceResponse>
"
        );
        CAS_TestHarness_DummyRequest::addResponse($response);

        $this->object->setTicket('ST-123456-asdfasdfasgww2323radf3');
        $this->object->isAuthenticated();

        // Verify that we have attributes from this response
        $attras = $this->object->getAttributes();
        $this->assertTrue($this->object->hasAttribute('attraStyle'));
        // direct access
        $this
            ->assertEquals('RubyCAS', $this->object->getAttribute('attraStyle'));
        // array access
        $this->assertArrayHasKey('attraStyle', $attras);
        $this->assertEquals('RubyCAS', $attras['attraStyle']);

        $this->validateUserAttributes();
    }

    /**
     * Verify that phpCAS will successfully fetch RubyCAS-style attributes:
     *
     * @return void
     */
    public function testJasigAttributes()
    {
        // Set up our response.
        $response = new CAS_TestHarness_BasicResponse(
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
        $response->setResponseBody(
            "<cas:serviceResponse xmlns:cas='http://www.yale.edu/tp/cas'>
    <cas:authenticationSuccess>
        <cas:user>jsmith</cas:user>

        <cas:attributes>
            <cas:attraStyle>Jasig</cas:attraStyle>
            <cas:surname>Smith</cas:surname>
            <cas:givenName>John</cas:givenName>
            <cas:memberOf>CN=Staff,OU=Groups,DC=example,DC=edu</cas:memberOf>
            <cas:memberOf>CN=Spanish Department,OU=Departments,OU=Groups,DC=example,DC=edu</cas:memberOf>
        </cas:attributes>

        <cas:proxyGrantingTicket>PGTIOU-84678-8a9d2sfa23casd</cas:proxyGrantingTicket>
    </cas:authenticationSuccess>
</cas:serviceResponse>
"
        );
        CAS_TestHarness_DummyRequest::addResponse($response);

        $this->object->setTicket('ST-123456-asdfasdfasgww2323radf3');
        $this->object->isAuthenticated();

        // Verify that we have attributes from this response
        $attras = $this->object->getAttributes();
        $this->assertTrue($this->object->hasAttribute('attraStyle'));
        // direct access
        $this->assertEquals('Jasig', $this->object->getAttribute('attraStyle'));
        // array access
        $this->assertArrayHasKey('attraStyle', $attras);
        $this->assertEquals('Jasig', $attras['attraStyle']);

        $this->validateUserAttributes();

    }
    /**
     * Test Jasig Attributes with international characters
     *
     * @return void
     */
    public function testJasigAttributesInternational()
    {
        // Set up our response.
        $response = new CAS_TestHarness_BasicResponse(
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
        $response->setResponseBody(
            "<cas:serviceResponse xmlns:cas='http://www.yale.edu/tp/cas'>
    <cas:authenticationSuccess>
        <cas:user>Iñtërnâtiônàlizætiøn</cas:user>
        <cas:attributes>
            <cas:attraStyle>Jasig</cas:attraStyle>
            <cas:givenName>Iñtërnâtiônàlizætiøn</cas:givenName>
        </cas:attributes>
        <cas:proxyGrantingTicket>PGTIOU-84678-8a9d2sfa23casd</cas:proxyGrantingTicket>
    </cas:authenticationSuccess>
</cas:serviceResponse>
"
        );
        CAS_TestHarness_DummyRequest::addResponse($response);

        $this->object->setTicket('ST-123456-asdfasdfasgww2323radf3');
        $this->object->isAuthenticated();

        // Verify that we have attributes from this response
        $attras = $this->object->getAttributes();
        $this->assertTrue($this->object->hasAttribute('attraStyle'));
        // direct access
        $this->assertEquals('Jasig', $this->object->getAttribute('attraStyle'));
        // array access
        $this->assertArrayHasKey('attraStyle', $attras);
        $this->assertEquals('Jasig', $attras['attraStyle']);

        $this->assertTrue($this->object->hasAttribute('givenName'));
        // direct access
        $this->assertEquals(
            'Iñtërnâtiônàlizætiøn',
            $this->object->getAttribute('givenName')
        );
        // array access
        $this->assertArrayHasKey('givenName', $attras);
        $this->assertEquals('Iñtërnâtiônàlizætiøn', $attras['givenName']);

    }

    /**
     * Verify that phpCAS will successfully fetch name-value-style attributes:
     *
     * @return void
     */
    public function testNameValueAttributes()
    {
        // Set up our response.
        $response = new CAS_TestHarness_BasicResponse(
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
        $response->setResponseBody(
            "<cas:serviceResponse xmlns:cas='http://www.yale.edu/tp/cas'>
    <cas:authenticationSuccess>
        <cas:user>jsmith</cas:user>

        <cas:attribute name='attraStyle' value='Name-Value' />
        <cas:attribute name='surname' value='Smith' />
        <cas:attribute name='givenName' value='John' />
        <cas:attribute name='memberOf' value='CN=Staff,OU=Groups,DC=example,DC=edu' />
        <cas:attribute name='memberOf' value='CN=Spanish Department,OU=Departments,OU=Groups,DC=example,DC=edu' />

        <cas:proxyGrantingTicket>PGTIOU-84678-8a9d2sfa23casd</cas:proxyGrantingTicket>
    </cas:authenticationSuccess>
</cas:serviceResponse>
"
        );
        CAS_TestHarness_DummyRequest::addResponse($response);

        $this->object->setTicket('ST-123456-asdfasdfasgww2323radf3');
        $this->object->isAuthenticated();

        // Verify that we have attributes from this response
        $attras = $this->object->getAttributes();
        $this->assertTrue(
            $this->object->hasAttribute('attraStyle'),
            "Should have an attraStyle attribute"
        );
        // direct access
        $this->assertEquals(
            'Name-Value',
            $this->object->getAttribute('attraStyle')
        );
        // array access
        $this->assertArrayHasKey('attraStyle', $attras);
        $this->assertEquals('Name-Value', $attras['attraStyle']);

        $this->validateUserAttributes();
    }

    /**
     * Validate user attributes.
     *
     * @return void
     */
    public function validateUserAttributes()
    {
        $attras = $this->object->getAttributes();
        $this->assertInternalType('array', $attras);

        if (count($attras) != 4 || !is_array($attras['memberOf'])) {
            print "\n";
            print_r($attras);
        }

        $this->assertEquals(4, count($attras));

        $this->assertTrue($this->object->hasAttribute('givenName'));
        // direct access
        $this->assertEquals('John', $this->object->getAttribute('givenName'));
        // array access
        $this->assertArrayHasKey('givenName', $attras);
        $this->assertEquals('John', $attras['givenName']);

        $this->assertTrue($this->object->hasAttribute('surname'));
        // direct access
        $this->assertEquals('Smith', $this->object->getAttribute('surname'));
        // array access
        $this->assertArrayHasKey('surname', $attras);
        $this->assertEquals('Smith', $attras['surname']);

        $this->assertTrue($this->object->hasAttribute('memberOf'));
        // direct access
        $memberOf = $this->object->getAttribute('memberOf');
        $this->assertInternalType('array', $memberOf);
        $this->assertEquals(2, count($memberOf));
        $this->assertTrue(
            in_array('CN=Staff,OU=Groups,DC=example,DC=edu', $memberOf)
        );
        $this->assertTrue(
            in_array(
                'CN=Spanish Department,OU=Departments,OU=Groups,DC=example,DC=edu',
                $memberOf
            )
        );
        // array access
        $this->assertArrayHasKey('memberOf', $attras);
        $this->assertInternalType('array', $attras['memberOf']);
        $this->assertEquals(2, count($attras['memberOf']));
        $this->assertTrue(
            in_array('CN=Staff,OU=Groups,DC=example,DC=edu', $attras['memberOf'])
        );
        $this->assertTrue(
            in_array(
                'CN=Spanish Department,OU=Departments,OU=Groups,DC=example,DC=edu',
                $attras['memberOf']
            )
        );
    }

}
?>
