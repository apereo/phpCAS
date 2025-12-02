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
 * @file     CAS/Tests/CookieJarTest.php
 * @category Authentication
 * @package  PhpCAS
 * @author   Adam Franco <afranco@middlebury.edu>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 */

namespace PhpCas\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Test harness for the cookie Jar to allow us to test protected methods.
 *
 * @class    CookieJarExposed
 * @category Authentication
 * @package  PhpCAS
 * @author   Adam Franco <afranco@middlebury.edu>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 */

class CookieJarExposed extends \CAS_CookieJar
{
    /**
     * Wrapper to call protected methods
     *
     * @param string $method function name
     * @param array  $args   function args
     *
     * @throws BadMethodCallException
     *
     * @return mixed
     */
    public function __call($method, array $args = array())
    {
        if (!method_exists($this, $method)) {
            throw new BadMethodCallException("method '$method' does not exist");
        }
        return call_user_func_array(array($this, $method), $args);
    }
}

/**
 * Test class for verifying the operation of cookie handling methods used in
 * serviceWeb() proxy calls.
 *
 * @class    CookieJarTest
 * @category Authentication
 * @package  PhpCAS
 * @author   Adam Franco <afranco@middlebury.edu>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 */
class CookieJarTest extends TestCase
{
    /**
     * @var CAS_Client
     */
    protected $object;

    protected $serviceUrl_1 = 'http://service.example.com/lookup/?action=search&query=username';
    protected $responseHeaders_1 = array('HTTP/1.1 302 Found',
            'Date: Tue, 07 Sep 2010 17:51:54 GMT',
            'Server: Apache/2.2.3 (Red Hat)', 'X-Powered-By: PHP/5.1.6',
            'Set-Cookie: SID=k1jut1r1bqrumpei837kk4jks0; path=/',
            'Expires: Thu, 19 Nov 1981 08:52:00 GMT',
            'Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0',
            'Pragma: no-cache',
            'Location: https://cas.example.edu:443/cas/login?service=http%3A%2F%2Fservice.example.edu%2Flookup%2F%3Faction%3Dsearch%26query%3Dusername',
            'Content-Length: 525', 'Connection: close',
            'Content-Type: text/html; charset=UTF-8',
        );
    protected $serviceUrl_1b = 'http://service.example.com/lookup/?action=search&query=another_username';
    protected $serviceUrl_1c = 'http://service.example.com/make_changes.php';

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $cookieArray = array();
        $this->object = new CookieJarExposed($cookieArray);

        // Verify that there are no cookies to start.
        $this->assertEquals(
            0, count($this->object->getCookies($this->serviceUrl_1))
        );
        $this->assertEquals(
            0, count($this->object->getCookies($this->serviceUrl_1b))
        );
        $this->assertEquals(
            0, count($this->object->getCookies($this->serviceUrl_1c))
        );

        // Add service cookies as if we just made are request to serviceUrl_1
        // and recieved responseHeaders_1 as the header to the response.
        $this->object
            ->storeCookies($this->serviceUrl_1, $this->responseHeaders_1);
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
     * Verify that our first response will set a cookie that will be available to
     * the same URL.
     *
     * @return void
     */
    public function testPublicGetCookiesSameUrl()
    {
        // Verify that our cookie is available.
        $cookies = $this->object->getCookies($this->serviceUrl_1);
        $this->assertEquals(1, count($cookies));
        $this->assertEquals('k1jut1r1bqrumpei837kk4jks0', $cookies['SID']);
    }

    /**
     * Verify that our first response will set a cookie that is available to a second
     * request to a different url on the same host.
     *
     * @return void
     */
    public function testPublicGetCookiesSamePathDifferentQuery()
    {
        // Verify that our cookie is available.
        $cookies = $this->object->getCookies($this->serviceUrl_1b);
        $this->assertEquals(1, count($cookies));
        $this->assertEquals('k1jut1r1bqrumpei837kk4jks0', $cookies['SID']);
    }

    /**
     * Verify that our first response will set a cookie that is available to a second
     * request to a different url on the same host.
     *
     * @return void
     */
    public function testPublicGetCookiesDifferentPath()
    {
        // Verify that our cookie is available.
        $cookies = $this->object->getCookies($this->serviceUrl_1c);
        $this->assertEquals(1, count($cookies));
        $this->assertEquals('k1jut1r1bqrumpei837kk4jks0', $cookies['SID']);
    }

    /**
     * Verify that our cookies set with the 'secure' token will only go to https
     * URLs.
     *
     * @return void
     */
    public function testPublicGetCookiesSecure()
    {
        $headers = array('Set-Cookie: person="bob jones"; path=/; Secure');
        $url = 'https://service.example.com/lookup/?action=search&query=username';
        $this->object->storeCookies($url, $headers);

        // Ensure that only the SID cookie not available to non https URLs
        $cookies = $this->object
            ->getCookies('http://service.example.com/lookup/');
        $this->assertArrayHasKey('SID', $cookies);
        $this->assertEquals('k1jut1r1bqrumpei837kk4jks0', $cookies['SID']);
        $this->assertArrayNotHasKey('person', $cookies);

        // Ensure that the SID cookie is avalailable to https urls.
        $cookies = $this->object
            ->getCookies('https://service.example.com/lookup/');
        $this->assertArrayHasKey('SID', $cookies);
        $this->assertEquals('k1jut1r1bqrumpei837kk4jks0', $cookies['SID']);
        $this->assertArrayHasKey('person', $cookies);
        $this->assertEquals('bob jones', $cookies['person']);
    }

    /**
     * Verify that our cookies set with the 'secure' token will only go to https
     * URLs.
     *
     * @return void
     */
    public function testPublicGetCookiesSecureLC()
    {
        $headers = array('Set-Cookie: person="bob jones"; path=/; secure');
        $url = 'https://service.example.com/lookup/?action=search&query=username';
        $this->object->storeCookies($url, $headers);

        // Ensure that only the SID cookie not available to non https URLs
        $cookies = $this->object
            ->getCookies('http://service.example.com/lookup/');
        $this->assertArrayHasKey('SID', $cookies);
        $this->assertEquals('k1jut1r1bqrumpei837kk4jks0', $cookies['SID']);
        $this->assertArrayNotHasKey('person', $cookies);

        // Ensure that the SID cookie is avalailable to https urls.
        $cookies = $this->object
            ->getCookies('https://service.example.com/lookup/');
        $this->assertArrayHasKey('SID', $cookies);
        $this->assertEquals('k1jut1r1bqrumpei837kk4jks0', $cookies['SID']);
        $this->assertArrayHasKey('person', $cookies);
        $this->assertEquals('bob jones', $cookies['person']);
    }

    /**
     * Verify that when no domain is set for the cookie, it will be unavailable
     * to other hosts
     *
     * @return void
     */
    public function testPublicGetCookiesDifferentHost()
    {
        // Verify that our cookie isn't available when the hostname is changed.
        $cookies = $this->object
            ->getCookies('http://service2.example.com/make_changes.php');
        $this->assertEquals(0, count($cookies));

        // Verify that our cookie isn't available when the domain is changed.
        $cookies = $this->object
            ->getCookies('http://service.example2.com/make_changes.php');
        $this->assertEquals(0, count($cookies));

        // Verify that our cookie isn't available when the tdl is changed.
        $cookies = $this->object
            ->getCookies('http://service.example.org/make_changes.php');
        $this->assertEquals(0, count($cookies));
    }

    /**
     * Verify that our set with the domain name will work
     *
     * @return void
     */
    public function testPublicGetCookiesDomain()
    {
        $headers = array(
            'Set-Cookie: SID="thisisthesid"; domain=".example.org"; path=/'
        );
        $url = 'http://host.example.org/path/to/somthing';
        $this->object->storeCookies($url, $headers);

        // Ensure the SID cookie is available to the domain
        $cookies = $this->object->getCookies('http://example.org/path/');
        $this->assertArrayHasKey(
            'SID', $cookies, "example.org should match .example.org cookies"
        );

        // Ensure the SID cookie is available to the host
        $cookies = $this->object->getCookies('http://host.example.org/path/');
        $this->assertArrayHasKey(
            'SID', $cookies, "host.example.org should match .example.org cookies"
        );
        $this->assertEquals(
            'thisisthesid', $cookies['SID'],
            "host.example.org should match .example.org cookies"
        );

        // Ensure the SID cookie is NOT available to a subdomain of the host
        // See RFC 2965 section 3.3.2  Rejecting Cookies for more details:
        // http://www.ietf.org/rfc/rfc2965.txt
        $cookies = $this->object
            ->getCookies('http://sub.host.example.org/path/');
        $this->assertArrayNotHasKey(
            'SID', $cookies,
            "sub.host.example.org shouldn't match .example.org cookies"
        );
    }

    /**
     * Verify that our set with the host name explicitly will work
     *
     * @return void
     */
    public function testPublicGetCookiesDomainHost()
    {
        $headers = array(
            'Set-Cookie: SID="thisisthesid"; domain="host.example.org"; path=/'
        );
        $url = 'http://host.example.org/path/to/somthing';
        $this->object->storeCookies($url, $headers);

        // Ensure the SID cookie is NOT available to the domain
        $cookies = $this->object->getCookies('http://example.org/path/');
        $this->assertArrayNotHasKey(
            'SID', $cookies,
            "example.org shouldn't match host.example.org cookies"
        );

        // Ensure the SID cookie is available to the host
        $cookies = $this->object->getCookies('http://host.example.org/path/');
        $this->assertArrayHasKey(
            'SID', $cookies,
            "host.example.org should match host.example.org cookies"
        );
        $this->assertEquals(
            'thisisthesid', $cookies['SID'],
            "host.example.org should match host.example.org cookies"
        );

        // Ensure the SID cookie is NOT available to a subdomain of the host
        // See RFC 2965 section 3.3.2  Rejecting Cookies for more details:
        // http://www.ietf.org/rfc/rfc2965.txt
        $cookies = $this->object
            ->getCookies('http://sub.host.example.org/path/');
        $this->assertArrayNotHasKey(
            'SID', $cookies,
            "sub.host.example.org shouldn't match host.example.org cookies"
        );
    }

    /**
     * Verify that our set with the host name explicitly will work
     *
     * @return void
     */
    public function testPublicGetCookiesDomainHostDotted()
    {
        $headers = array(
            'Set-Cookie: SID="thisisthesid"; domain=".host.example.org"; path=/'
        );
        $url = 'http://host.example.org/path/to/somthing';
        $this->object->storeCookies($url, $headers);

        // Ensure the SID cookie is NOT available to the domain
        $cookies = $this->object->getCookies('http://example.org/path/');
        $this->assertArrayNotHasKey(
            'SID', $cookies,
            "example.org shouldn't match .host.example.org cookies"
        );

        // Ensure the SID cookie is available to the host
        $cookies = $this->object->getCookies('http://host.example.org/path/');
        $this->assertArrayHasKey(
            'SID', $cookies,
            "host.example.org should match .host.example.org cookies"
        );
        $this->assertEquals(
            'thisisthesid', $cookies['SID'],
            "host.example.org should match host.example.org cookies"
        );

        // Ensure the SID cookie IS available to a subdomain of the host
        $cookies = $this->object
            ->getCookies('http://sub.host.example.org/path/');
        $this->assertArrayHasKey(
            'SID', $cookies,
            "sub.host.example.org should match .host.example.org cookies"
        );
    }

    /**
     * Verify that cookies are getting stored in our storage array.
     *
     * @return void
     */
    public function testPublicStoreCookies()
    {
        $array = array();
        $cookieJar = new \CAS_CookieJar($array);
        $this->assertEquals(0, count($array));
        $cookieJar->storeCookies($this->serviceUrl_1, $this->responseHeaders_1);
        $this->assertEquals(1, count($array));
    }

    /**
     * Verify that cookie header with max-age value will be available for that
     * length of time.
     *
     * @return void
     */
    public function testPublicStoreCookiesMaxAge()
    {
        // Verify that we have on cookie to start.
        $this->assertEquals(
            1, count($this->object->getCookies($this->serviceUrl_1))
        );

        // Send set-cookie header to remove the cookie
        $headers = array('Set-Cookie2: person="bob jones"; path=/; max-age=2');
        $this->object->storeCookies($this->serviceUrl_1, $headers);

        // Ensure that the cookie exists after 1 second
        sleep(1);
        $cookies = $this->object->getCookies($this->serviceUrl_1);
        $this->assertArrayHasKey('person', $cookies);
        $this->assertEquals('bob jones', $cookies['person']);

        // Wait 3 total seconds and then ensure that the cookie has been removed
        sleep(2);
        $cookies = $this->object->getCookies($this->serviceUrl_1);
        $this->assertArrayNotHasKey('person', $cookies);
    }

    /**
     * Verify that cookie header with max-age=0 will remove the cookie.
     * Documented in RFC2965 section 3.2.2
     * http://www.ietf.org/rfc/rfc2965.txt
     *
     * @return void
     */
    public function testPublicStoreCookiesRemoveViaMaxAge0()
    {
        // Verify that we have on cookie to start.
        $this->assertEquals(
            1, count($this->object->getCookies($this->serviceUrl_1))
        );

        // Send set-cookie header to remove the cookie
        $headers = array(
            'Set-Cookie2: SID=k1jut1r1bqrumpei837kk4jks0; path=/; max-age=0'
        );
        $this->object->storeCookies($this->serviceUrl_1, $headers);

        $this->assertEquals(
            0, count($this->object->getCookies($this->serviceUrl_1))
        );
    }

    /**
     * Verify that cookie header with expires in the past will remove the cookie.
     * Documented in RFC2965 section 3.2.2
     * http://www.ietf.org/rfc/rfc2965.txt
     *
     * @return void
     */
    public function testPublicStoreCookiesRemoveViaExpiresPast()
    {
        // Verify that we have on cookie to start.
        $this->assertEquals(
            1, count($this->object->getCookies($this->serviceUrl_1))
        );

        // Send set-cookie header to remove the cookie
        $headers = array(
            'Set-Cookie: SID=k1jut1r1bqrumpei837kk4jks0; path=/; expires=Fri, 31-Dec-2009 23:59:59 GMT'
        );
        $this->object->storeCookies($this->serviceUrl_1, $headers);

        $this->assertEquals(
            0, count($this->object->getCookies($this->serviceUrl_1))
        );
    }

    /**
     * Verify that cookie header that expires in the past will not be stored.
     *
     * http://www.ietf.org/rfc/rfc2965.txt
     *
     * @return void
     */
    public function testPublicStoreCookiesDontStoreExpiresPast()
    {
        // Verify that we have on cookie to start.
        $this->assertEquals(
            1, count($this->object->getCookies($this->serviceUrl_1))
        );

        // Send set-cookie header to remove the cookie
        $headers = array(
            'Set-Cookie: bob=jones; path=/; expires='
            . gmdate('D, d-M-Y H:i:s e', time() - 90000)
        );
        $this->object->storeCookies($this->serviceUrl_1, $headers);

        $cookies = $this->object->getCookies($this->serviceUrl_1);
        $this->assertEquals(1, count($cookies));
        $this->assertArrayNotHasKey('jones', $cookies);
    }

    /**
     * Verify that cookie header that expires in the futre will not be removed.
     *
     * http://www.ietf.org/rfc/rfc2965.txt
     *
     * @return void
     */
    public function testPublicStoreCookiesExpiresFuture()
    {
        // Verify that we have on cookie to start.
        $this->assertEquals(
            1, count($this->object->getCookies($this->serviceUrl_1))
        );

        // Send set-cookie header to remove the cookie
        $headers = array(
            'Set-Cookie: bob=jones; path=/; expires='
            . gmdate('D, d-M-Y H:i:s e', time() + 600)
        );
        $this->object->storeCookies($this->serviceUrl_1, $headers);

        $cookies = $this->object->getCookies($this->serviceUrl_1);
        $this->assertEquals(2, count($cookies));
        $this->assertEquals('jones', $cookies['bob']);
    }

    /**
     * Test the inclusion of an httponly attribute.
     *
     * @return void
     */
    public function testPublicStoreCookiesHttponly()
    {
        $headers = array(
            'Set-Cookie: SID="hello world"; path=/; domain=.example.com; HttpOnly'
        );
        $this->object->storeCookies($this->serviceUrl_1, $headers);

        $cookies = $this->object->getCookies($this->serviceUrl_1b);

        $this->assertIsArray($cookies);
        $this->assertEquals('hello world', $cookies['SID']);
        $this->assertEquals(
            1, count($cookies),
            "Should only a single SID cookie, not a cookie for the HttpOnly attribute"
        );
    }

    /**
     * Test the inclusion of an comment attribute.
     *
     * @return void
     */
    public function testPublicStoreCookiesComment()
    {
        $headers = array(
            'Set-Cookie: SID="hello world"; path=/; domain=.example.com; HttpOnly; comment="A session cookie"'
        );
        $this->object->storeCookies($this->serviceUrl_1, $headers);

        $cookies = $this->object->getCookies($this->serviceUrl_1b);

        $this->assertIsArray($cookies);
        $this->assertEquals('hello world', $cookies['SID']);
        $this->assertEquals(
            1, count($cookies),
            "Should only a single SID cookie, not a cookie for the comment attribute"
        );
    }

    /**
     * Test the inclusion of a semicolon in a quoted cookie value.
     *
     * Note: As of September 12th, the current implementation is known to
     * fail this test since it explodes values on the semicolon symbol. This
     * behavior is not ideal but should be ok for most cases. Since this is the
     * default behaviour for most browsers anyway the test is disabled.
     */
    /*
     public function test_public_storeCookies_QuotedSemicolon()
     {
     $headers = array('Set-Cookie: SID="hello;world"; path=/; domain=.example.com');
     $this->object->storeCookies($this->serviceUrl_1, $headers);

     $cookies = $this->object->getCookies($this->serviceUrl_1b);

     $this->assertInternalType('array', $cookies);
     $this->assertEquals('hello;world', $cookies['SID'], "\tNote: The implementation as of Sept 15, 2010 makes the assumption \n\tthat semicolons will not be present in quoted attribute values. \n\tWhile attribute values that contain semicolons are allowed by \n\tRFC2965, they are hopefully rare enough to ignore for our purposes.");
     $this->assertEquals(1, count($cookies));
     }
     */

    /**
     * Test the inclusion of an equals in a quoted cookie value.
     *
     * Note: As of September 12th, the current implementation is known to
     * fail this test since it explodes values on the equals symbol. This
     * behavior is not ideal but should be ok for most cases.
     *
     * @return void
     */
    public function testPublicStoreCookiesQuotedEquals()
    {
        $headers = array(
            'Set-Cookie: SID="hello=world"; path=/; domain=.example.com'
        );
        $this->object->storeCookies($this->serviceUrl_1, $headers);

        $cookies = $this->object->getCookies($this->serviceUrl_1b);

        $this->assertIsArray($cookies);
        $this->assertEquals(
            'hello=world', $cookies['SID'],
            "\tNote: The implementation as of Sept 15, 2010 makes the assumption \n\tthat equals symbols will not be present in quoted attribute values. \n\tWhile attribute values that contain equals symbols are allowed by \n\tRFC2965, they are hopefully rare enough to ignore for our purposes."
        );
        $this->assertEquals(1, count($cookies));
    }

    /**
     * Test the inclusion of an escaped quote in a quoted cookie value.
     *
     *  @return void
     */
    public function testPublicStoreCookiesQuotedEscapedQuote()
    {
        $headers = array(
            'Set-Cookie: SID="hello\"world"; path=/; domain=.example.com'
        );
        $this->object->storeCookies($this->serviceUrl_1, $headers);

        $cookies = $this->object->getCookies($this->serviceUrl_1b);

        $this->assertIsArray($cookies);
        $this->assertEquals('hello"world', $cookies['SID']);
        $this->assertEquals(1, count($cookies));
    }

    /*********************************************************
     * Tests of protected (implementation) methods
     *
     * Most of these should likely be reworked to test their edge
     * cases via the two public methods to allow refactoring of the
     * protected methods without breaking the tests.
     *********************************************************/

    /**
     * Test the basic operation of parseCookieHeaders.
     *
     * @return void
     */
    public function testProtectedParseCookieHeaders()
    {
        $cookies = $this->object->parseCookieHeaders(
            $this->responseHeaders_1, 'service.example.com'
        );

        $this->assertIsArray($cookies);
        $this->assertEquals(1, count($cookies));
        $this->assertEquals('SID', $cookies[0]['name']);
        $this->assertEquals('k1jut1r1bqrumpei837kk4jks0', $cookies[0]['value']);
        $this->assertEquals('/', $cookies[0]['path']);
        $this->assertEquals('service.example.com', $cookies[0]['domain']);
        $this->assertFalse($cookies[0]['secure']);
    }

    /**
     * Test the addition of a domain to the parsing of cookie headers
     *
     * @return void
     */
    public function testProtectedParseCookieHeadersWithDomain()
    {
        $headers = array(
            'Set-Cookie: SID=k1jut1r1bqrumpei837kk4jks0; path=/; domain=.example.com'
        );
        $cookies = $this->object
            ->parseCookieHeaders($headers, 'service.example.com');

        $this->assertIsArray($cookies);
        $this->assertEquals(1, count($cookies));
        $this->assertEquals('SID', $cookies[0]['name']);
        $this->assertEquals('k1jut1r1bqrumpei837kk4jks0', $cookies[0]['value']);
        $this->assertEquals('/', $cookies[0]['path']);
        $this->assertEquals('.example.com', $cookies[0]['domain']);
        $this->assertFalse($cookies[0]['secure']);
    }

    /**
     * Test the addition of a domain to the parsing of cookie headers
     *
     * @return void
     */
    public function testProtectedParseCookieHeadersWithHostname()
    {
        $headers = array(
            'Set-Cookie: SID=k1jut1r1bqrumpei837kk4jks0; path=/; domain=service.example.com'
        );
        $cookies = $this->object
            ->parseCookieHeaders($headers, 'service.example.com');

        $this->assertIsArray($cookies);
        $this->assertEquals(1, count($cookies));
        $this->assertEquals('SID', $cookies[0]['name']);
        $this->assertEquals('k1jut1r1bqrumpei837kk4jks0', $cookies[0]['value']);
        $this->assertEquals('/', $cookies[0]['path']);
        $this->assertEquals('service.example.com', $cookies[0]['domain']);
        $this->assertFalse($cookies[0]['secure']);
    }

    /**
     * Test the usage of a hostname that is different from the default URL.
     *
     * @return void
     */
    public function testProtectedParseCookieHeadersNonDefaultHostname()
    {
        $headers = array(
            'Set-Cookie: SID=k1jut1r1bqrumpei837kk4jks0; path=/; domain=service2.example.com'
        );
        $cookies = $this->object
            ->parseCookieHeaders($headers, 'service.example.com');

        $this->assertIsArray($cookies);
        $this->assertEquals(1, count($cookies));
        $this->assertEquals('SID', $cookies[0]['name']);
        $this->assertEquals('k1jut1r1bqrumpei837kk4jks0', $cookies[0]['value']);
        $this->assertEquals('/', $cookies[0]['path']);
        $this->assertEquals('service2.example.com', $cookies[0]['domain']);
        $this->assertFalse($cookies[0]['secure']);
    }

    /**
     * Test the the inclusion of a path in the cookie.
     *
     * @return void
     */
    public function testProtectedParseCookieHeadersWithPath()
    {
        $headers = array(
            'Set-Cookie: SID=k1jut1r1bqrumpei837kk4jks0; path=/something/; domain=service2.example.com'
        );
        $cookies = $this->object
            ->parseCookieHeaders($headers, 'service.example.com');

        $this->assertIsArray($cookies);
        $this->assertEquals(1, count($cookies));
        $this->assertEquals('SID', $cookies[0]['name']);
        $this->assertEquals('k1jut1r1bqrumpei837kk4jks0', $cookies[0]['value']);
        $this->assertEquals('/something/', $cookies[0]['path']);
        $this->assertEquals('service2.example.com', $cookies[0]['domain']);
        $this->assertFalse($cookies[0]['secure']);
    }

    /**
     * Test the addition of a 'Secure' parameter
     *
     * @return void
     */
    public function testProtectedParseCookieHeadersSecure()
    {
        $headers = array(
            'Set-Cookie: SID=k1jut1r1bqrumpei837kk4jks0; Secure; path=/something/; domain=service2.example.com'
        );
        $cookies = $this->object
            ->parseCookieHeaders($headers, 'service.example.com');

        $this->assertIsArray($cookies);
        $this->assertEquals(1, count($cookies));
        $this->assertEquals('SID', $cookies[0]['name']);
        $this->assertEquals('k1jut1r1bqrumpei837kk4jks0', $cookies[0]['value']);
        $this->assertEquals('/something/', $cookies[0]['path']);
        $this->assertEquals('service2.example.com', $cookies[0]['domain']);
        $this->assertTrue($cookies[0]['secure']);
    }

    /**
     * Test the addition of a 'Secure' parameter that is lower-case
     *
     * @return void
     */
    public function testProtectedParseCookieHeadersSecureLC()
    {
        $headers = array(
            'Set-Cookie: SID=k1jut1r1bqrumpei837kk4jks0; secure; path=/something/; domain=service2.example.com'
        );
        $cookies = $this->object
            ->parseCookieHeaders($headers, 'service.example.com');

        $this->assertIsArray($cookies);
        $this->assertEquals(1, count($cookies));
        $this->assertEquals('SID', $cookies[0]['name']);
        $this->assertEquals('k1jut1r1bqrumpei837kk4jks0', $cookies[0]['value']);
        $this->assertEquals('/something/', $cookies[0]['path']);
        $this->assertEquals('service2.example.com', $cookies[0]['domain']);
        $this->assertTrue($cookies[0]['secure']);
    }

    /**
     * Test the inclusion of a trailing semicolon
     *
     * @return void
     */
    public function testProtectedParseCookieHeadersTrailingSemicolon()
    {
        $headers = array('Set-Cookie: SID="hello world"; path=/;');
        $cookies = $this->object
            ->parseCookieHeaders($headers, 'service.example.com');

        $this->assertIsArray($cookies);
        $this->assertEquals(1, count($cookies));
        $this->assertEquals('SID', $cookies[0]['name']);
        $this->assertEquals('hello world', $cookies[0]['value']);
        $this->assertEquals('/', $cookies[0]['path']);
        $this->assertEquals('service.example.com', $cookies[0]['domain']);
        $this->assertFalse($cookies[0]['secure']);
    }

    /**
     * Test setting a single service cookie
     *
     * @return void
     */
    public function testProtectedSetCookie()
    {
        $cookies = $this->object->getCookies($this->serviceUrl_1c);
        $this->assertIsArray($cookies);
        $this->assertEquals(1, count($cookies));
        $this->assertEquals('k1jut1r1bqrumpei837kk4jks0', $cookies['SID']);
    }

    /**
     * Test setting a single service cookie
     *
     * @return void
     */
    public function testProtectedStoreCookieWithDuplicates()
    {
        $headers = array('Set-Cookie: SID="hello world"; path=/');
        $cookiesToSet = $this->object
            ->parseCookieHeaders($headers, 'service.example.com');
        $this->object->storeCookie($cookiesToSet[0]);

        $headers = array('Set-Cookie: SID="goodbye world"; path=/');
        $cookiesToSet = $this->object
            ->parseCookieHeaders($headers, 'service.example.com');
        $this->object->storeCookie($cookiesToSet[0]);

        $cookies = $this->object->getCookies($this->serviceUrl_1c);
        $this->assertIsArray($cookies);
        $this->assertEquals(1, count($cookies));
        $this->assertEquals('goodbye world', $cookies['SID']);
    }

    /**
     * Test setting two service cookies
     *
     * @return void
     */
    public function testProtectedStoreCookieTwoCookies()
    {
        // Second cookie
        $headers = array('Set-Cookie: message="hello world"; path=/');
        $cookiesToSet = $this->object
            ->parseCookieHeaders($headers, 'service.example.com');
        $this->object->storeCookie($cookiesToSet[0]);

        $cookies = $this->object->getCookies($this->serviceUrl_1c);
        $this->assertIsArray($cookies);
        $this->assertEquals(2, count($cookies));
        $this->assertEquals('k1jut1r1bqrumpei837kk4jks0', $cookies['SID']);
        $this->assertEquals('hello world', $cookies['message']);
    }

    /**
     * Test setting two service cookies
     *
     * @return void
     */
    public function testProtectedStoreCookieTwoCookiesOneAtDomain()
    {

        // Second cookie
        $headers = array(
            'Set-Cookie: message="hello world"; path=/; domain=.example.com'
        );
        $cookiesToSet = $this->object
            ->parseCookieHeaders($headers, 'service.example.com');
        $this->object->storeCookie($cookiesToSet[0]);

        $cookies = $this->object->getCookies($this->serviceUrl_1c);
        $this->assertIsArray($cookies);
        $this->assertEquals(2, count($cookies));
        $this->assertEquals('k1jut1r1bqrumpei837kk4jks0', $cookies['SID']);
        $this->assertEquals('hello world', $cookies['message']);
    }

    /**
     * Test matching a domain cookie.
     *
     * @return void
     */
    public function testProtectedCookieMatchesTargetDomainCookie()
    {
        $headers = array(
            'Set-Cookie: message="hello world"; path=/; domain=.example.com'
        );
        $cookies = $this->object
            ->parseCookieHeaders($headers, 'otherhost.example.com');

        $this->assertTrue(
            $this->object->cookieMatchesTarget(
                $cookies[0],
                parse_url('http://service.example.com/make_changes.php')
            )
        );
    }

}
?>
