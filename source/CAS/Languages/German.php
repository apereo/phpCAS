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

/**
 * @author Henrik Genssen <hg at mediafactory.de>
 * @sa @link internalLang Internationalization @endlink
 * @ingroup internalLang
 */
class CAS_Languages_German implements CAS_Languages_LanguageInterface {
	
	
	public function getUsingServer(){
		return 'via Server';
	}
	public function getAuthenticationWanted(){
		return 'CAS Authentifizierung erforderlich!';
	}
	public function getLogout(){
		return 'CAS Abmeldung!';
	}
	public function getShouldHaveBeenRedirected(){
		return 'eigentlich h&auml;ten Sie zum CAS Server weitergeleitet werden sollen. Dr&uuml;cken Sie <a href="%s">hier</a> um fortzufahren.';
	}
	public function getAuthenticationFailed(){
		return 'CAS Anmeldung fehlgeschlagen!';
	}
	public function getYouWereNotAuthenticated(){
		return '<p>Sie wurden nicht angemeldet.</p><p>Um es erneut zu versuchen klicken Sie <a href="%s">hier</a>.</p><p>Wenn das Problem bestehen bleibt, kontkatieren Sie den <a href="mailto:%s">Administrator</a> dieser Seite.</p>';
	}
	public function getServiceUnavailable(){
		return 'Der Dienst `<b>%s</b>\' ist nicht verf&uuml;gbar (<b>%s</b>).';
	}
}

?>