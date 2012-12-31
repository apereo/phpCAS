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
 * @file     TestSuite.php
 * @category Authentication
 * @package  PhpCAS
 * @author   Adam Franco <afranco@middlebury.edu>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 */

ob_start();
require_once dirname(__FILE__) . '/../source/CAS.php';

/**
 * Suite of all tests
 *
 * @class    TestSuite
 * @category Authentication
 * @package  PhpCAS
 * @author   Adam Franco <afranco@middlebury.edu>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 */

class TestSuite extends PHPUnit_Framework_TestSuite
{
    /**
     * Create a new testsuite
     *
     * @return PhpcasTestSuite
     */
    public static function suite()
    {
        $suite = new TestSuite('phpCAS Test Suite');

        self::recursiveAddTests($suite, dirname(__FILE__) . '/CAS/Tests');
        return $suite;
    }

    /**
    * Empty function
    *
    * @return void
    */
    protected function setUp()
    {

    }

    /**
     * Empty function
     *
     * @return void
     */
    protected function tearDown()
    {

    }

    /**
     * Recursively add test files in subdirectories
     *
     * @param PHPUnit_Framework_TestSuite $suite a test suite class
     * @param string                      $dir   dir from which to add tests
     *
     * @return void
     *
     * @access protected
     */
    protected static function recursiveAddTests(
        PHPUnit_Framework_TestSuite $suite, $dir
    ) {
        foreach (scandir($dir) as $file) {
            if (preg_match('/Test\.php$/', $file)) {
                $suite->addTestFile($dir . '/' . $file);
            } else if (is_dir($dir . '/' . $file)
                && preg_match('/^[a-z0-9]+/i', $file)
            ) {
                self::recursiveAddTests($suite, $dir . '/' . $file);
            }
        }
    }
}
