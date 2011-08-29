<?php

class ProxyChains{
	
	private $_chains = array();
	
	public function __construct(array $chain){
		$this->_chains[] = $chain;
	}
	
	public function addChain(array $chain){
		$this->_chains[] = $chain;
	}
	
	public function getProxyChain(){
		return $this->_chains;
	}
	
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