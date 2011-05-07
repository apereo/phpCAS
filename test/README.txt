**************************
* Unit Tests for phpCAS
**************************

These unit tests currently only cover a small portion of the operation of phpCAS.


**************************
* Running tests
**************************
1. Install PHPUnit using instructions on this page:
	http://pear.phpunit.de/

2. cd to the phpcas/test/ directory.

3. Run the following command:
	phpunit TestSuite.php



**************************
* Creating tests
**************************
Any files you place in phpcas/test/tests/ whose name ends with 'Test.php' will
be added as a test file.

A template test file can be created via the following:
1. Create the skeleton.
	phpunit --skeleton-test  CAS_Client source/CAS/Client.php

2. Move the skeleton to our tests/ directory.
	mv source/CAS/CAS_ClientTest.php test/tests/ClientTest.php

Notes:

You may want to clear the session in the setUp() method of the test so that each
test has a clean state to start from.

If you want to test methods that require authentication, then we need a real CAS
server to be configured for testing. Alternatively, a dummy 'CAS server' might be
implemented with static XML documents or simple PHP scripts that given certain
parameters always return the same content.