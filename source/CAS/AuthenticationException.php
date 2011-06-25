<?php
class CAS_AuthenticationException
	extends RuntimeException
	implements CAS_Exception
{

	/**
	* This method is used to print the HTML output when the user was not authenticated.
	*
	* @param $client phpcas client
	* @param $failure the failure that occured
	* @param $cas_url the URL the CAS server was asked for
	* @param $no_response the response from the CAS server (other
	* parameters are ignored if TRUE)
	* @param $bad_response bad response from the CAS server ($err_code
	* and $err_msg ignored if TRUE)
	* @param $cas_response the response of the CAS server
	* @param $err_code the error code given by the CAS server
	* @param $err_msg the error message given by the CAS server
	*/
	public function __construct($client,$failure,$cas_url,$no_response,$bad_response='',$cas_response='',$err_code='',$err_msg='')
	{
		phpCAS::traceBegin();
		$client->printHTMLHeader($client->getString(CAS_STR_AUTHENTICATION_FAILED));
		printf($client->getString(CAS_STR_YOU_WERE_NOT_AUTHENTICATED),htmlentities($client->getURL()),$_SERVER['SERVER_ADMIN']);
		phpCAS::trace('CAS URL: '.$cas_url);
		phpCAS::trace('Authentication failure: '.$failure);
		if ( $no_response ) {
			phpCAS::trace('Reason: no response from the CAS server');
		} else {
			if ( $bad_response ) {
				phpCAS::trace('Reason: bad response from the CAS server');
			} else {
				switch ($client->getServerVersion()) {
					case CAS_VERSION_1_0:
						phpCAS::trace('Reason: CAS error');
						break;
					case CAS_VERSION_2_0:
						if ( empty($err_code) )
						phpCAS::trace('Reason: no CAS error');
						else
						phpCAS::trace('Reason: ['.$err_code.'] CAS error: '.$err_msg);
						break;
				}
			}
			phpCAS::trace('CAS response: '.$cas_response);
		}
		$client->printHTMLFooter();
		phpCAS::traceExit();
	}
	
}
?>