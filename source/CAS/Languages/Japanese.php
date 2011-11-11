<?php

/**
 * @author fnorif (fnorif@yahoo.co.jp)
 *
 * Now Encoding is EUC-JP and LF
 **/
class CAS_Languages_Japanese implements CAS_Languages_LanguageInterface {
	public function getUsingServer(){
		return 'using server';
	}
	public function getAuthenticationWanted(){
		return 'CAS�ˤ��ǧ�ڤ�Ԥ��ޤ�';
	}
	public function getLogout(){
		return 'CAS����?�����Ȥ��ޤ�!';
	}
	public function getShouldHaveBeenRedirected(){
		return 'CAS�����Ф˹Ԥ�ɬ�פ�����ޤ�����ưŪ��ž������ʤ����� <a href="%s">������</a> �򥯥�å�����³�Ԥ��ޤ��';
	}
	public function getAuthenticationFailed(){
		return 'CAS�ˤ��ǧ�ڤ˼��Ԥ��ޤ���';
	}
	public function getYouWereNotAuthenticated(){
		return '<p>ǧ�ڤǤ��ޤ���Ǥ���.</p><p>�⤦���٥ꥯ�����Ȥ������������<a href="%s">������</a>�򥯥�å�.</p><p>���꤬��褷�ʤ����� <a href="mailto:%s">���Υ����Ȥδ����</a>���䤤��碌�Ƥ�������.</p>';
	}
	public function getServiceUnavailable(){
		return '�����ӥ� `<b>%s</b>\' �����ѤǤ��ޤ��� (<b>%s</b>).';
	}
}
?>