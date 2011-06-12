<?php
/*
 * Created on 26.03.2011
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 * 
 */
 
 abstract class CAS_Protocol{
 	
 	const DEFAULT_REQUEST_CLASS =  'CAS_Request_CurlRequest';
 	 	
 	private $_requestImplementation;
 	
 	abstract function getLoginURL($service);
    abstract function getLogoutURL($service = null);
    abstract function getVersion();
    abstract function validateTicket($ticket, $service);
    
    function getRequest()
    {
        if(empty($this->_requestImplementation)){
             $class =  self::DEFAULT_REQUEST_CLASS;
             $this->_requestImplementation = new $class();
        }
        return $this->_requestImplementation; 
    }
    
    /**
     * Set the HTTP Request object.
     *
     * @param $request
     */
    function setRequest($request)
    {
        $this->request = $request;
    }
 }
?>
