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

use PHPUnit\Framework\TestCase;

/**
 * Test class for verifying the operation of the proxy-chains validation system
 *
 * @class    ProxyChainsTests
 * @category Authentication
 * @package  PhpCAS
 * @author   Adam Franco <afranco@middlebury.edu>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 */
class ProxyChainsTest extends TestCase
{
    /**
     * @var CAS_Client
     */
    protected $object;

    protected $list_size_0 = array();
    protected $list_size_1 = array('https://service1.example.com/rest',);
    protected $list_size_2 = array('https://service1.example.com/rest',
            'http://service2.example.com/my/path',
        );
    protected $list_size_3 = array('https://service1.example.com/rest',
            'http://service2.example.com/my/path',
            'http://service3.example.com/other/',
        );
    protected $list_size_4 = array('https://service1.example.com/rest',
            'http://service2.example.com/my/path',
            'http://service3.example.com/other/',
            'https://service4.example.com/',
        );

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->object = new \CAS_ProxyChain_AllowedList;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {

    }

    /*********************************************************
     * Tests of public (interface) methods
     *********************************************************/

    /**
     * Verify that not configuring any proxies will prevent acccess.
     *
     * @return void
     */
    public function testNone()
    {
        $this->assertTrue(
            $this->object->isProxyListAllowed($this->list_size_0),
            'Should be ok with no proxies in front.'
        );
        $this->assertFalse(
            $this->object->isProxyListAllowed($this->list_size_1),
            'Should prevent proxies in front.'
        );
        $this->assertFalse(
            $this->object->isProxyListAllowed($this->list_size_1),
            'Should prevent proxies in front.'
        );
        $this->assertFalse(
            $this->object->isProxyListAllowed($this->list_size_1),
            'Should prevent proxies in front.'
        );
        $this->assertFalse(
            $this->object->isProxyListAllowed($this->list_size_1),
            'Should prevent proxies in front.'
        );
    }

    /**
     * Verify that using the CAS_ProxyChain_Any will work with any URL.
     *
     * @return void
     */
    public function testAny()
    {
        $this->object->allowProxyChain(new \CAS_ProxyChain_Any);
        $this->assertTrue(
            $this->object->isProxyListAllowed($this->list_size_0),
            'Should allow any proxies in front.'
        );
        $this->assertTrue(
            $this->object->isProxyListAllowed($this->list_size_1),
            'Should allow any proxies in front.'
        );
        $this->assertTrue(
            $this->object->isProxyListAllowed($this->list_size_1),
            'Should allow any proxies in front.'
        );
        $this->assertTrue(
            $this->object->isProxyListAllowed($this->list_size_1),
            'Should allow any proxies in front.'
        );
        $this->assertTrue(
            $this->object->isProxyListAllowed($this->list_size_1),
            'Should allow any proxies in front.'
        );
    }

    /**
     * Verify that using the CAS_ProxyChain will only allow an exact match to
     * the chain.
     *
     * @return void
     */
    public function testExactMatch2()
    {
        $this->object->allowProxyChain(
            new \CAS_ProxyChain(
                array('https://service1.example.com/rest',
                    'http://service2.example.com/my/path',
                )
            )
        );
        $this->assertTrue(
            $this->object->isProxyListAllowed($this->list_size_0),
            'Should be ok with no proxies in front.'
        );
        $this->assertFalse(
            $this->object->isProxyListAllowed($this->list_size_1),
            'Should not allow inexact matches in length.'
        );
        $this->assertTrue(
            $this->object->isProxyListAllowed($this->list_size_2),
            'Should allow an exact match in length and URL'
        );
        $this->assertFalse(
            $this->object->isProxyListAllowed($this->list_size_3),
            'Should not allow inexact matches in length.'
        );
        $this->assertFalse(
            $this->object->isProxyListAllowed($this->list_size_4),
            'Should not allow inexact matches in length.'
        );
    }

    /**
     * Verify that using the CAS_ProxyChain will only allow an exact match to
     * the chain.
     *
     * @return void
     */
    public function testExactMatch2Failure()
    {
        $this->object->allowProxyChain(
            new \CAS_ProxyChain(
                array('https://service1.example.com/rest',
                    'http://other.example.com/my/path',
                )
            )
        );
        $this->assertTrue(
            $this->object->isProxyListAllowed($this->list_size_0),
            'Should be ok with no proxies in front.'
        );
        $this->assertFalse(
            $this->object->isProxyListAllowed($this->list_size_1),
            'Should not allow inexact matches in length.'
        );
        $this->assertFalse(
            $this->object->isProxyListAllowed($this->list_size_2),
            'Should not allow inexact URL match'
        );
        $this->assertFalse(
            $this->object->isProxyListAllowed($this->list_size_3),
            'Should not allow inexact matches in length.'
        );
        $this->assertFalse(
            $this->object->isProxyListAllowed($this->list_size_4),
            'Should not allow inexact matches in length.'
        );
    }

    /**
     * Verify that using the CAS_ProxyChain_Trusted will allow an exact match or
     * greater length of chain.
     *
     * @return void
     */
    public function testTrustedMatch2()
    {
        $this->object->allowProxyChain(
            new \CAS_ProxyChain_Trusted(
                array('https://service1.example.com/rest',
                    'http://service2.example.com/my/path',
                )
            )
        );
        $this->assertTrue(
            $this->object->isProxyListAllowed($this->list_size_0),
            'Should be ok with no proxies in front.'
        );
        $this->assertFalse(
            $this->object->isProxyListAllowed($this->list_size_1),
            'Should not allow inexact matches in length.'
        );
        $this->assertTrue(
            $this->object->isProxyListAllowed($this->list_size_2),
            'Should allow an exact match in length and URL'
        );
        $this->assertTrue(
            $this->object->isProxyListAllowed($this->list_size_3),
            'Should allow an exact match or greater in length'
        );
        $this->assertTrue(
            $this->object->isProxyListAllowed($this->list_size_4),
            'Should allow an exact match or greater in length'
        );
    }

    /**
     * Verify that using the CAS_ProxyChain will match strings as prefixes
     *
     * @return void
     */
    public function testPrefixMatch3()
    {
        $this->object->allowProxyChain(
            new \CAS_ProxyChain(
                array('https://service1.example.com/',
                    'http://service2.example.com/my',
                    'http://service3.example.com/',
                )
            )
        );
        $this->assertTrue(
            $this->object->isProxyListAllowed($this->list_size_0),
            'Should be ok with no proxies in front.'
        );
        $this->assertFalse(
            $this->object->isProxyListAllowed($this->list_size_1),
            'Should not allow inexact matches in length.'
        );
        $this->assertFalse(
            $this->object->isProxyListAllowed($this->list_size_2),
            'Should not allow inexact matches in length.'
        );
        $this->assertTrue(
            $this->object->isProxyListAllowed($this->list_size_3),
            'Should allow an exact match in length and URL'
        );
        $this->assertFalse(
            $this->object->isProxyListAllowed($this->list_size_4),
            'Should not allow inexact matches in length.'
        );
    }

    /**
     * Verify that using the CAS_ProxyChain will match with Regular expressions
     *
     * @return void
     */
    public function testRegexMatch2()
    {
        $this->object->allowProxyChain(
            new \CAS_ProxyChain(
                array('/^https?:\/\/service1\.example\.com\/.*/',
                    '/^http:\/\/service[0-9]\.example\.com\/[^\/]+\/path/',
                )
            )
        );
        $this->assertTrue(
            $this->object->isProxyListAllowed($this->list_size_0),
            'Should be ok with no proxies in front.'
        );
        $this->assertFalse(
            $this->object->isProxyListAllowed($this->list_size_1),
            'Should not allow inexact matches in length.'
        );
        $this->assertTrue(
            $this->object->isProxyListAllowed($this->list_size_2),
            'Should allow an exact match in length and URL'
        );
        $this->assertFalse(
            $this->object->isProxyListAllowed($this->list_size_3),
            'Should not allow inexact matches in length.'
        );
        $this->assertFalse(
            $this->object->isProxyListAllowed($this->list_size_4),
            'Should not allow inexact matches in length.'
        );
    }

    /**
     * Verify that using the CAS_ProxyChain will match a mixture of with Regular
     * expressions and plain strings
     *
     * @return void
     */
    public function testMixedRegexMatch3()
    {
        $this->object->allowProxyChain(
            new \CAS_ProxyChain(
                array('https://service1.example.com/',
                    '/^http:\/\/service[0-9]\.example\.com\/[^\/]+\/path/',
                    'http://service3.example.com/',
                )
            )
        );
        $this->assertTrue($this->object->isProxyListAllowed($this->list_size_0));
        $this->assertFalse($this->object->isProxyListAllowed($this->list_size_1));
        $this->assertFalse($this->object->isProxyListAllowed($this->list_size_2));
        $this->assertTrue($this->object->isProxyListAllowed($this->list_size_3));
        $this->assertFalse($this->object->isProxyListAllowed($this->list_size_4));
    }

    /**
     * Verify that using the CAS_ProxyChain_Trusted will match a mixture of with
     * Regular expressions and plain strings
     *
     * @return void
     */
    public function testMixedRegexTrusted3()
    {
        $this->object->allowProxyChain(
            new \CAS_ProxyChain_Trusted(
                array('https://service1.example.com/',
                    '/^http:\/\/service[0-9]\.example\.com\/[^\/]+\/path/',
                    'http://service3.example.com/',
                )
            )
        );
        $this->assertTrue($this->object->isProxyListAllowed($this->list_size_0));
        $this->assertFalse($this->object->isProxyListAllowed($this->list_size_1));
        $this->assertFalse($this->object->isProxyListAllowed($this->list_size_2));
        $this->assertTrue($this->object->isProxyListAllowed($this->list_size_3));
        $this->assertTrue($this->object->isProxyListAllowed($this->list_size_4));
    }

    /**
     * Verify that using the CAS_ProxyChain will allow regex modifiers
     *
     * @return void
     */
    public function testRegexModifiers()
    {
        $this->object->allowProxyChain(
            new \CAS_ProxyChain(
                array('/^https?:\/\/service1\.EXAMPLE\.com\/.*/i',
                    '/^http:\/\/serVice[0-9]\.example\.com\/[^\/]+\/path/ix',
                )
            )
        );
        $this->assertTrue(
            $this->object->isProxyListAllowed($this->list_size_0),
            'Should be ok with no proxies in front.'
        );
        $this->assertFalse(
            $this->object->isProxyListAllowed($this->list_size_1),
            'Should not allow inexact matches in length.'
        );
        $this->assertTrue(
            $this->object->isProxyListAllowed($this->list_size_2),
            'Should allow modifiers on Regular expressions'
        );
        $this->assertFalse(
            $this->object->isProxyListAllowed($this->list_size_3),
            'Should not allow inexact matches in length.'
        );
        $this->assertFalse(
            $this->object->isProxyListAllowed($this->list_size_4),
            'Should not allow inexact matches in length.'
        );
    }
}
