<?php

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
