<?php

class CAS_ProxyChains{
	
	private $_chains = array();
	
	/**
	 * ProxyChain is a container for storing chains of valid proxies that can
	 * be used to validate proxied requests to a service
	 * A chain is an array of strings or regexp strings that will be matched
	 * against. Regexp will be matched with preg_match and strings will be
	 * matched from the beginning. A string must fully match the beginning of
	 * an prox url. So you can define a full domain as acceptable or go further
	 * down.
	 * Proxies have to be defined in reverse from the service to the user. If a
	 * user hits service A get proxied via B to service C the list of acceptable
	 * proxies on C would be array(B,A);
	 * @param array $chain The first chain of proxies.
	 */
	public function __construct(array $chain){
		$this->_chains[] = $chain;
	}
	
	/**
	 * Add a chain of proxies to the list of possible chains
	 * @param array $chain
	 */
	public function addChain(array $chain){
		$this->_chains[] = $chain;
	}
	
	/**
	 * Return the chains of proxies
	 * @return array of arrays
	 */
	public function getProxyChain(){
		return $this->_chains;
	}
	
	/**
	 * 
	 * Validate the proxies from the proxy ticket validation against the
	 * chains that were definded. 
	 * @param array $list List of proxies from the proxy ticket validation.
	 * @return if any chain fully matches the supplied list
	 */
	public function contains(array $list){
		phpCAS::traceBegin();
		$count = 0;
		foreach( $this->_chains as $chain) {
			phpCAS::trace("Checking chain ". $count++);
			if(sizeof($chain) == sizeof($list)){
				for($i = 0; $i < sizeof($list); $i++) {
					$mismatch = false;
					if(preg_match('/^\/.*\//',$chain[$i])){
						if(preg_match($chain[$i], $list[$i])){
							phpCAS::trace("Found regexp " .  $chain[$i] . " matching " . $list[$i]);
						}else{
							phpCAS::trace("No regexp match " .  $chain[$i] . " != " . $list[$i]);
							$mismatch = true;
							break;
						}
					}else{
						if(strncasecmp($chain[$i],$list[$i],strlen($chain[$i])) == 0){
							phpCAS::trace("Found string " .  $chain[$i] . " matching " . $list[$i]);
						}else{
							phpCAS::trace("No match " .  $chain[$i] . " != " . $list[$i]);
							$mismatch = true;
							break;
						}
					}
				}
				if(!$mismatch){
					phpCAS::trace("Proxy chain matches");
					return true;
				}
			}else{
				phpCAS::trace("Proxy chain skipped: size mismatch");
			}
		}
		phpCAS::traceEnd();
		return false;
	}
	
}


?>