<?php

/**
 * Class for SAML 2 LogoutResponse messages.
 *
 * @package simpleSAMLphp
 * @version $Id$
 */
class SAML2_LogoutResponse extends SAML2_StatusResponse {

	/**
	 * Constructor for SAML 2 response messages.
	 *
	 * @param string $tagName  The tag name of the root element.
	 * @param DOMElement|NULL $xml  The input message.
	 */
	public function __construct(DOMElement $xml = NULL) {
		parent::__construct('LogoutResponse', $xml);

		/* No new fields added by LogoutResponse. */
	}

}

?>