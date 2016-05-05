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
 *
 * PHP Version 5
 *
 * @class    CAS/ProxyTicketException.php
 * @category Authentication
 * @author   Adam Franco <afranco@middlebury.edu>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 */

namespace phpCAS\CAS;

use BadMethodCallException;
use phpCAS\CAS;

/**
 * An Exception for errors related to fetching or validating proxy tickets.
 *
 * @class    ProxyTicketException
 * @category Authentication
 * @author   Adam Franco <afranco@middlebury.edu>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 */
class ProxyTicketException extends BadMethodCallException implements CASExceptionInterface
{
    /**
     * Constructor.
     *
     * @param string $message Message text
     * @param int    $code    Error code
     */
    public function __construct($message, $code = CAS::PHPCAS_SERVICE_PT_FAILURE)
    {
        // Warn if the code is not in our allowed list
        $ptCodes = [
            CAS::PHPCAS_SERVICE_PT_FAILURE,
            CAS::PHPCAS_SERVICE_PT_NO_SERVER_RESPONSE,
            CAS::PHPCAS_SERVICE_PT_BAD_SERVER_RESPONSE,
        ];
        if (! in_array($code, $ptCodes)) {
            trigger_error(
                'Invalid code '.$code
                .' passed. Must be one of CAS::PHPCAS_SERVICE_PT_FAILURE, CAS::PHPCAS_SERVICE_PT_NO_SERVER_RESPONSE, or CAS::PHPCAS_SERVICE_PT_BAD_SERVER_RESPONSE.'
            );
        }

        parent::__construct($message, $code);
    }
}
