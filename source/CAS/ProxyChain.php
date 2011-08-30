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

/**
 * A normal proxy-chain definition that lists each level of the chain as either
 * a string or regular expression.
 */
class CAS_ProxyChain
	implements CAS_ProxyChain_Interface 
{
	
	protected $_chain = array();
	
	/**
	 * A chain is an array of strings or regexp strings that will be matched
	 * against. Regexp will be matched with preg_match and strings will be
	 * matched from the beginning. A string must fully match the beginning of
	 * an proxy url. So you can define a full domain as acceptable or go further
	 * down.
	 * Proxies have to be defined in reverse from the service to the user. If a
	 * user hits service A get proxied via B to service C the list of acceptable
	 * proxies on C would be array(B,A);
	 *
	 * @param array $chain
	 */
	public function __construct(array $chain) {
		$this->_chain = array_values($chain);	// Ensure that we have an indexed array
	}
	
	/**
	 * Match a list of proxies.
	 * 
	 * @param array $list The list of proxies in front of this service.
	 * @return boolean
	 */
	public function matches(array $list) {
		$list = array_values($list);  // Ensure that we have an indexed array
		if ($this->isSizeValid($list)) {
			$mismatch = false;
			foreach ($this->_chain as $i => $search) {
				$proxy_url = $list[$i];
				if (preg_match('/^\/.*\/[ixASUXu]*$/s',$search)) {
					if (preg_match($search, $proxy_url)) {
						phpCAS::trace("Found regexp " .  $search . " matching " . $proxy_url);
					} else {
						phpCAS::trace("No regexp match " .  $search . " != " . $proxy_url);
						$mismatch = true;
						break;
					}
				} else {
					if (strncasecmp($search, $proxy_url, strlen($search)) == 0) {
						phpCAS::trace("Found string " .  $search . " matching " . $proxy_url);
					} else {
						phpCAS::trace("No match " .  $search . " != " . $proxy_url);
						$mismatch = true;
						break;
					}
				}
			}
			if (!$mismatch) {
				phpCAS::trace("Proxy chain matches");
				return true;
			}
		} else {
			phpCAS::trace("Proxy chain skipped: size mismatch");
		}
		return false;
	}
	
	/**
	 * Validate the size of the the list as compared to our chain.
	 * 
	 * @param array $list
	 * @return boolean
	 */
	protected function isSizeValid (array $list) {
		return (sizeof($this->_chain) == sizeof($list));
	}
}