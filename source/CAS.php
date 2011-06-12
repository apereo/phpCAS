<?php

define('PHPCAS_VERSION', '${phpcas.version}');

class CAS {
	
	 /**
     * Singleton CAS object
     *
     * @var CAS
     */
    static private $_instance;
    
     /**
     * Protocol used for authentication
     *
     * @var CAS_Protocol
     */
    protected $protocol;
    
    /**
     * User's login name if authenticated.
     *
     * @var string
     */
    protected $username;
    
    
	
	function __autoload($className) {
    // this is to take care of the PEAR style of naming classes
    	$path = str_ireplace('_', '/', $className);
    	if(@include_once $path.'.php'){
        	return;
    	}
	}
	
	 /**
     * Construct a CAS client object.
     *
     * @param CAS_Protocol $protocol Protocol to use for authentication
     */
    private function __construct(CAS_Protocol $protocol)
    {
        $this->protocol = $protocol;
        
    }

	function forceAuthenticate(){
		
	}
	
	function checkAuthenticate(){
		
	}

	public static function getVersion(){
		return PHPCAS_VERSION;
	}
	
}

?>