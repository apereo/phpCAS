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
class CAS_Languages_French implements CAS_Languages_LanguageInterface {
	
	public function getUsingServer(){
		return 'utilisant le serveur';
	}
	public function getAuthenticationWanted(){
		return 'Authentication CAS n�cessaire&nbsp;!';
	}
	public function getLogout(){
		return 'D�connexion demand�e&nbsp;!';
	}
	public function getShouldHaveBeenRedirected(){
		return 'Vous auriez du etre redirig�(e) vers le serveur CAS. Cliquez <a href="%s">ici</a> pour continuer.';
	}
	public function getAuthenticationFailed(){
		return 'Authentification CAS infructueuse&nbsp;!';
	}
	public function getYouWereNotAuthenticated(){
		return '<p>Vous n\'avez pas �t� authentifi�(e).</p><p>Vous pouvez soumettre votre requete � nouveau en cliquant <a href="%s">ici</a>.</p><p>Si le probl�me persiste, vous pouvez contacter <a href="mailto:%s">l\'administrateur de ce site</a>.</p>';
	}
	public function getServiceUnavailable(){
		return 'Le service `<b>%s</b>\' est indisponible (<b>%s</b>)';
	}
}

?>