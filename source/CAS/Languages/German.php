<?php

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