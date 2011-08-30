<?php

/*
 * Copyright © 2003-2011, The ESUP-Portail consortium & the JA-SIG Collaborative.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *     * Redistributions of source code must retain the above copyright notice,
 *       this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright notice,
 *       this list of conditions and the following disclaimer in the documentation
 *       and/or other materials provided with the distribution.
 *     * Neither the name of the ESUP-Portail consortium & the JA-SIG
 *       Collaborative nor the names of its contributors may be used to endorse or
 *       promote products derived from this software without specific prior
 *       written permission.

 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 * ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

include_once(dirname(__FILE__).'/ProxyChain/Interface.php');
include_once(dirname(__FILE__).'/ProxyChain.php');
include_once(dirname(__FILE__).'/ProxyChain/Any.php');
include_once(dirname(__FILE__).'/ProxyChain/Trusted.php');

/**
 * ProxyChain is a container for storing chains of valid proxies that can
 * be used to validate proxied requests to a service
 */
class CAS_ProxyChains {
	
	private $_chains = array();
	
	/**
	 * Check whether proxies are allowed by configuration 
	 */
	public function isProxyingAllowed(){
		return (count($this->_chains) > 0);
	}
	
	/**
	 * Add a chain of proxies to the list of possible chains
	 * @param array $chain
	 */
	public function allowProxyingBy(CAS_ProxyChain_Interface $chain) {
		$this->_chains[] = $chain;
	}
	
	/**
	 *
	 * Check if the proxies found in the response match the allowed proxies
	 * @param array $proxies
	 * @return boolean whether the proxies match the allowed proxies
	 */
	public function isProxyListAllowed(array $proxies){
		phpCAS::traceBegin();
		if(empty($proxies)){
			phpCAS::trace("No proxies were found in the response");
			phpCAS::traceEnd(true);
			return true;
		}elseif(!$this->isProxyingAllowed()){
			phpCAS::trace("Proxies are not allowed");
			phpCAS::traceEnd(false);
			return false;
		}else{
			$res = $this->contains($proxies);
			phpCAS::traceEnd($res);
			return $res;
		}
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
		foreach ($this->_chains as $chain) {
			phpCAS::trace("Checking chain ". $count++);
			if ($chain->matches($list)) {
				phpCAS::traceEnd(true);
				return true;
			}
		}
		phpCAS::trace("No proxy chain matches.");
		phpCAS::traceEnd(false);
		return false;
	}
	
}


?>