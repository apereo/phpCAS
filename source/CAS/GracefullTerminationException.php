<?php
class CAS_GracefullTerminationException
	extends RuntimeException
	implements CAS_Exception
{
	/**
	 * Decide what to do with the exception
	 * @param CAS_Client $client
	 */
	public function handleException(CAS_Client $client){
		phpCAS::traceBegin();
		if($client->isThrowingExceptionsEnabled() || php_sapi_name() === 'cli'){
			phpCAS::traceEnd();
			throw $this;
		}else{
			phpCAS::traceExit();
			exit();
		}
	}
	
}
?>