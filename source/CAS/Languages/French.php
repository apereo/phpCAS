<?php

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