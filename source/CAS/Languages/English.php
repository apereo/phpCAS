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
 * @author Pascal Aubry <pascal.aubry at univ-rennes1.fr>
 * @sa @link internalLang Internationalization @endlink
 * @ingroup internalLang
 */
class CAS_Languages_English implements CAS_Languages_LanguageInterface{

	public function getUsingServer(){
		return 'using server';
	}
	public function getAuthenticationWanted(){
		return 'CAS Authentication wanted!';
	}
	public function getLogout(){
		return 'CAS logout wanted!';
	}
	public function getShouldHaveBeenRedirected(){
		return 'You should already have been redirected to the CAS server. Click <a href="%s">here</a> to continue.';
	}
	public function getAuthenticationFailed(){
		return 'CAS Authentication failed!';
	}
	public function getYouWereNotAuthenticated(){
		return '<p>You were not authenticated.</p><p>You may submit your request again by clicking <a href="%s">here</a>.</p><p>If the problem persists, you may contact <a href="mailto:%s">the administrator of this site</a>.</p>';
	}
	public function getServiceUnavailable(){
		return 'The service `<b>%s</b>\' is not available (<b>%s</b>).';
	}
}