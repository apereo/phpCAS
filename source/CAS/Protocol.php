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
 	private $_username;
 	
 	public abstract function setLoginURL($url);
 	public abstract function getLoginURL($service);
 	public abstract function setLogoutUrl($url);
    public abstract function getLogoutURL($service = null);
    public abstract function getVersion();
    public abstract function validateTicket($ticket, $service);
    
    protected function setUsername($name){
    	$this->_username = $name;
    }
      
    public function getUsername(){
    	return $this->_username;
    }
    
    protected function getRequest()
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
    protected function setRequest($request)
    {
        $this->request = $request;
    }
 }
?>
