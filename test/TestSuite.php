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
 */

require_once dirname(__FILE__).'/../source/CAS.php';

class PhpcasTestSuite extends PHPUnit_Framework_TestSuite
{
	public static function suite()
	{
		$suite = new PhpcasTestSuite('phpCAS Test Suite');

		self::recursiveAddTests($suite, dirname(__FILE__).'/tests');

		return $suite;
	}

	protected function setUp()
	{

	}

	protected function tearDown()
	{

	}

	/**
	 * Recursively add test files in subdirectories
	 *
	 * @param PHPUnit_Framework_TestSuite $suite
	 * @param string $dir
	 * @return void
	 * @access protected
	 * @since 6/3/09
	 */
	protected static function recursiveAddTests (PHPUnit_Framework_TestSuite $suite, $dir) {
		foreach (scandir($dir) as $file) {
			if (preg_match('/Test\.php$/', $file)) {
				$suite->addTestFile($dir.'/'.$file);
			} else if (is_dir($dir.'/'.$file) && preg_match('/^[a-z0-9]+/i', $file)) {
				self::recursiveAddTests($suite, $dir.'/'.$file);
			}
		}
	}
}