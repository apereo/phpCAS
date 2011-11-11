<?php

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
