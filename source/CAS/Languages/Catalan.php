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
 * @author Iván-Benjamín García Torà <ivaniclixx AT gmail DOT com
 * @sa @link internalLang Internationalization @endlink
 * @ingroup internalLang
 */
class CAS_Languages_Catalan implements CAS_Languages_LanguageInterface {

	public function getUsingServer(){
		return 'usant servidor';
	}
	public function getAuthenticationWanted(){
		return 'Autentificació CAS necessària!';
	}
	public function getLogout(){
		return 'Sortida de CAS necessària!';
	}
	public function getShouldHaveBeenRedirected(){
		return 'Ja hauria d\ haver estat redireccionat al servidor CAS. Feu click <a href="%s">aquí</a> per a continuar.';
	}
	public function getAuthenticationFailed(){
		return 'Autentificació CAS fallida!';
	}
	public function getYouWereNotAuthenticated(){
		return '<p>No estàs autentificat.</p><p>Pots tornar a intentar-ho fent click <a href="%s">aquí</a>.</p><p>Si el problema persisteix hauría de contactar amb l\'<a href="mailto:%s">administrador d\'aquest llocc</a>.</p>';
	}
	public function getServiceUnavailable(){
		return 'El servei `<b>%s</b>\' no està disponible (<b>%s</b>).';
	}
}
