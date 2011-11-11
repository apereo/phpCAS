<?php

/**
 * @author Vangelis Haniotakis <haniotak at ucnet.uoc.gr>
 * @sa @link internalLang Internationalization @endlink
 * @ingroup internalLang
 */
class CAS_Languages_Greek implements CAS_Languages_LanguageInterface {
	
	public function getUsingServer(){
		return '��������������� � ������������';
	}
	public function getAuthenticationWanted(){
		return '���������� � ����������� CAS!';
	}
	public function getLogout(){
		return '���������� � ���������� ��� CAS!';
	}
	public function getShouldHaveBeenRedirected(){
		return '�� ������ �� ������ �������������� ���� ����������� CAS. ����� ���� <a href="%s">���</a> ��� �� ����������.';
	}
	public function getAuthenticationFailed(){
		return '� ����������� CAS �������!';
	}
	public function getYouWereNotAuthenticated(){
		return '<p>��� ���������������.</p><p>�������� �� ����������������, �������� ���� <a href="%s">���</a>.</p><p>��� �� �������� ���������, ����� �� ����� �� ��� <a href="mailto:%s">�����������</a>.</p>';
	}
	public function getServiceUnavailable(){
		return '� �������� `<b>%s</b>\' ��� ����� ��������� (<b>%s</b>).';
	}
}
?>