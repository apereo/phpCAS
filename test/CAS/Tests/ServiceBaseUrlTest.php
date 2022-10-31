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
 * @file     CAS/Tests/ServiceBaseUrlTest.php
 * @category Authentication
 * @package  PhpCAS
 * @author   Henry Pan <git@phy25.com>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 */

namespace PhpCas\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Test class for verifying the operation of the ServiceBaseUrl classes.
 *
 * @class    ServiceBaseUrlTest
 * @category Authentication
 * @package  PhpCAS
 * @author   Henry Pan <git@phy25.com>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 */
class ServiceBaseUrlTest extends TestCase
{
    /**
     * @var CAS_Client
     */
    protected $object;

    const DEFAULT_NAME = 'https://default.domain';

    const DOMAIN_1 = 'http://domain1';

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->object = new \CAS_ServiceBaseUrl_AllowedListDiscovery(array(self::DEFAULT_NAME, self::DOMAIN_1));
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
     * Verify that non allowlisted SERVER_NAME will return default name.
     *
     * @return void
     */
    public function testNonAllowlistedServerName()
    {
        $_SERVER['SERVER_NAME'] = 'domain1:8080';

        $this->assertSame(self::DEFAULT_NAME, $this->object->get());
    }

    /**
     * Verify that non allowlisted HTTP_HOST will return default name.
     *
     * @return void
     */
    public function testNonAllowlistedHttpHost()
    {
        $_SERVER['HTTP_HOST'] = 'domain1:8080';
        $_SERVER['SERVER_NAME'] = '';

        $this->assertSame(self::DEFAULT_NAME, $this->object->get());
    }

    /**
     * Verify that non allowlisted HTTP_X_FORWARDED_SERVER will return default name.
     *
     * @return void
     */
    public function testNonAllowlistedXForwardedServer()
    {
        $_SERVER['HTTP_X_FORWARDED_SERVER'] = 'domain1';
        $_SERVER['HTTP_X_FORWARDED_PORT'] = '8080';

        $this->assertSame(self::DEFAULT_NAME, $this->object->get());
    }

    /**
     * Verify that non allowlisted HTTP_X_FORWARDED_SERVER will return.
     *
     * @return void
     */
    public function testNonAllowlistedXForwardedHost()
    {
        $_SERVER['HTTP_X_FORWARDED_HOST'] = 'domain1:8080';

        $this->assertSame(self::DEFAULT_NAME, $this->object->get());
    }

    /**
     * Verify that allowlisted SERVER_NAME will return in standarized form.
     *
     * @return void
     */
    public function testAllowlistedServerName()
    {
        $_SERVER['SERVER_NAME'] = 'domain1';
        $_SERVER['HTTP_X_FORWARDED_PORT'] = '80';

        $this->assertSame(self::DOMAIN_1, $this->object->get());
    }

    /**
     * Verify that allowlisted HTTP_HOST will return.
     *
     * @return void
     */
    public function testAllowlistedHttpHost()
    {
        $_SERVER['HTTP_HOST'] = 'domain1';
        $_SERVER['SERVER_NAME'] = '';
        $_SERVER['HTTP_X_FORWARDED_PORT'] = '80';

        $this->assertSame(self::DOMAIN_1, $this->object->get());
    }

    /**
     * Verify that allowlisted HTTP_X_FORWARDED_SERVER will return.
     *
     * @return void
     */
    public function testAllowlistedXForwardedServer()
    {
        $_SERVER['HTTP_X_FORWARDED_SERVER'] = 'domain1';
        $_SERVER['HTTP_X_FORWARDED_PORT'] = '80';

        $this->assertSame(self::DOMAIN_1, $this->object->get());
    }

    /**
     * Verify that allowlisted HTTP_X_FORWARDED_HOST will return.
     *
     * @return void
     */
    public function testAllowlistedXForwardedHost()
    {
        $_SERVER['HTTP_X_FORWARDED_HOST'] = 'domain1';

        $this->assertSame(self::DOMAIN_1, $this->object->get());
    }

    /**
     * Verify that allowlisted HTTP_X_FORWARDED_HOST will return with a HTTP allowlist
     * that needs to be standardized.
     *
     * @return void
     */
    public function testAllowlistedXForwardedHostHttpStandardized()
    {
        $_SERVER['HTTP_X_FORWARDED_HOST'] = 'domain1';

        $this->object = new \CAS_ServiceBaseUrl_AllowedListDiscovery(array(self::DEFAULT_NAME, "http://domain1:80/"));
        $this->assertSame(self::DOMAIN_1, $this->object->get());
    }

    /**
     * Verify that allowlisted HTTP_X_FORWARDED_HOST will return with a HTTP allowlist
     * that needs to be standardized.
     *
     * @return void
     */
    public function testAllowlistedXForwardedHostWithSslHttpsStandardized()
    {
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['HTTP_X_FORWARDED_HOST'] = 'default.domain:443';

        $this->object = new \CAS_ServiceBaseUrl_AllowedListDiscovery(array("https://default.domain:443/", self::DOMAIN_1));
        $this->assertSame(self::DEFAULT_NAME, $this->object->get());
    }

    /**
     * Verify that allowlisted HTTP_X_FORWARDED_HOST will return with a HTTP allowlist
     * that needs to be standardized.
     *
     * @return void
     */
    public function testAllowlistedXForwardedHostHttpsStandardized()
    {
        $_SERVER['HTTP_X_FORWARDED_HOST'] = 'default.domain:443';

        $this->object = new \CAS_ServiceBaseUrl_AllowedListDiscovery(array("https://default.domain:443/", "http://default.domain:443/"));
        $this->assertSame("http://default.domain:443", $this->object->get());
    }

    /**
     * Verify that static configuration always return the standardized base URL.
     *
     * @return void
     */
    public function testStaticHappyPath()
    {
        $this->object = new \CAS_ServiceBaseUrl_Static("https://default.domain:443/");
        $this->assertSame(self::DEFAULT_NAME, $this->object->get());
    }

    /**
     * Verify that static configuration always return the standardized base URL.
     *
     * @return void
     */
    public function testStaticNoProtocol()
    {
        $this->expectException(\CAS_InvalidArgumentException::class);
        $this->object = new \CAS_ServiceBaseUrl_Static("default.domain/");
    }

}
