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
class CAS_Languages_Spanish implements CAS_Languages_LanguageInterface {
	
	public function getUsingServer(){
		return 'usando servidor';
	}
	public function getAuthenticationWanted(){
		return '¡Autentificación CAS necesaria!';
	}
	public function getLogout(){
		return '¡Salida CAS necesaria!';
	}
	public function getShouldHaveBeenRedirected(){
		return 'Ya debería haber sido redireccionado al servidor CAS. Haga click <a href="%s">aquí</a> para continuar.';
	}
	public function getAuthenticationFailed(){
		return '¡Autentificación CAS fallida!';
	}
	public function getYouWereNotAuthenticated(){
		return '<p>No estás autentificado.</p><p>Puedes volver a intentarlo haciendo click <a href="%s">aquí</a>.</p><p>Si el problema persiste debería contactar con el <a href="mailto:%s">administrador de este sitio</a>.</p>';
	}
	public function getServiceUnavailable(){
		return 'El servicio `<b>%s</b>\' no está disponible (<b>%s</b>).';
	}
}
?>
