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

class CAS_GracefullTerminationException
	extends RuntimeException
	implements CAS_Exception
{

	/**
	 * Test if exceptions should be thrown or if we should just exit.
	 * In production usage we want to just exit cleanly when prompting the user
	 * for a redirect without filling the error logs with uncaught exceptions.
	 * In unit testing scenarios we cannot exit or we won't be able to continue
	 * with our tests.
	 */
	public function __construct ($message = 'Terminate Gracefully', $code = 0) {
		// Exit cleanly to avoid filling up the logs with uncaught exceptions.
		if (self::$exitWhenThrown) {
			exit;
		} 
		// Throw exceptions to allow unit testing to continue;
		else {
			parent::__construct($message, $code);
		}
	}
	
	private static $exitWhenThrown = true;
	/**
	 * Force phpcas to thow Exceptions instead of calling exit()
	 * Needed for unit testing. Generally shouldn't be used in production
	 * due to an increase in Apache error logging if CAS_GracefulTerminiationExceptions
	 * are not caught and handled.
	 */
	public static function throwInsteadOfExiting() {
		self::$exitWhenThrown = false;
	}
	
}
?>