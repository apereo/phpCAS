<?php

/**
 * Licensed to Jasig under one or more contributor license
 * agreements. See the NOTICE file distributed with this work for
 * additional information regarding copyright ownership.
 * 
 * Jasig licenses this file to you under the Apache License,
 * Version 2.0 (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at:
 * 
 * http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
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