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
		$lang = $client->getLangObj();
		$client->printHTMLHeader($lang->getAuthenticationFailed());
		printf($lang->getYouWereNotAuthenticated(),htmlentities($client->getURL()),$_SERVER['SERVER_ADMIN']);
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
