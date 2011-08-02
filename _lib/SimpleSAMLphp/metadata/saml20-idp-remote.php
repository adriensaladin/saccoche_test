<?php
/**
 * SAML 2.0 remote IdP metadata for simpleSAMLphp.
 *
 * Remember to remove the IdPs you don't use from this file.
 *
 * See: https://rnd.feide.no/content/idp-remote-metadata-reference
 */

$metadata['gepi-idp'] = array(
	'name' => array('fr' => 'Gepi'),
	'SingleSignOnService'  => $_SESSION['SACoche-SimpleSAMLphp']['GEPI_URL'].'/lib/simplesaml/www/saml2/idp/SSOService.php'.'?rne='.$_SESSION['SACoche-SimpleSAMLphp']['GEPI_RNE'], /* issu de SACoche */
	'SingleLogoutService'  => $_SESSION['SACoche-SimpleSAMLphp']['GEPI_URL'].'/lib/simplesaml/www/saml2/idp/SingleLogoutService.php'.'?rne='.$_SESSION['SACoche-SimpleSAMLphp']['GEPI_RNE'], /* issu de SACoche */
	'certFingerprint'      => $_SESSION['SACoche-SimpleSAMLphp']['GEPI_CERTIFICAT_EMPREINTE'] /* issu de SACoche */
);
?>
