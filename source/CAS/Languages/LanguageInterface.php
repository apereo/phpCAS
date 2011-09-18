<?php

interface CAS_Languages_LanguageInterface{

	public function getUsingServer();
	public function getAuthenticationWanted();
	public function getLogout();
	public function getShouldHaveBeenRedirected();
	public function getAuthenticationFailed();
	public function getYouWereNotAuthenticated();
	public function getServiceUnavailable();

}
?>