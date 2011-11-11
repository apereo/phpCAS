<?php

require_once dirname(__FILE__).'/../source/CAS.php';
require_once dirname(__FILE__).'/../source/CAS/Autoload.php';


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