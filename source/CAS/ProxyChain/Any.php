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

/**
 * A proxy-chain definition that will match any list of proxies.
 *
 * Use this class for quick testing or in certain production screnarios you might want to 
 * allow allow any other valid service to proxy your service. 
 *
 * THIS CLASS IS HOWEVER NOT RECOMMENDED FOR PRODUCTION AND HAS SECURITY 
 * IMPLICATIONS: YOU ARE ALLOWING ANY SERVICE TO ACT ON BEHALF OF A USER
 * ON THIS SERVICE.
 */
class CAS_ProxyChain_Any
	implements CAS_ProxyChain_Interface 
{
	
	/**
	 * Match a list of proxies.
	 * 
	 * @param array $list The list of proxies in front of this service.
	 * @return boolean
	 */
	public function matches(array $list) {
		phpCAS::trace("Using CAS_ProxyChain_Any. No proxy validation is performed.");
		return true;
	}
	
}