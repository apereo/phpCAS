<?php
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
		// Throw exceptions to allow unit testing to continue;
		if (phpCAS::shouldThrowExceptionsInsteadOfExiting()) {
			parent::__construct($message, $code);
		} 
		// Exit cleanly to avoid filling up the logs with uncaught exceptions.
		else {
			exit;
		}
	}
	
}
?>