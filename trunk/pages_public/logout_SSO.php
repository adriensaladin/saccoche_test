<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2009
 * 
 * ****************************************************************************************************
 * SACoche [http://competences.sesamath.net] - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath [http://www.sesamath.net]
 * Distribution sous licence libre prévue pour l'été 2010.
 * ****************************************************************************************************
 * 
 * Connecteur SSO avec l'ENT Argos de l'académie de Bordeaux
 * Code initial de Sébastien Cogez [sebastien.cogez@sesamath.net] pour Mathenpoche
 * Aide de Julien Jocal [collegerb@free.fr] vu l'interdiction d'accéder à l'annuaire LDAP
 * 
 */

$structure_id = (isset($_GET['structure_id'])) ? intval($_GET['structure_id']) : 0;
$sso          = (isset($_GET['sso']))          ? $_GET['sso']                  : '';

require_once('../_inc/tableau_sso.php');	// !!! Remonter d'un dossier !!!	// Charge $tab_sso['nom'] = array('txt'=>'...' , 'doc'=>'...');
unset($tab_sso['normal']);

if( (!$structure_id) || (!isset($tab_sso[$sso])) )
{
	affich_message_exit($titre='Paramètres manquants',$contenu='Paramètres manquants.');
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Préparation de la connexion à l'ENT
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

// La classe phpCAS nécessite la bibliothèque de fonction cURL http://fr2.php.net/manual/fr/book.curl.php
if(!in_array( 'curl' , get_loaded_extensions() ))
{
	affich_message_exit($titre='PHP incomplet',$contenu='Le module PHP "curl" est manquant (bibliothèque requise pour CAS).');
}
// Inclure la classe phpCAS
include_once('../_inc/class.CAS.php');	// !!! Remonter d'un dossier !!!
// Pour tester, cette méthode statique créé un fichier de log sur ce qui se passe avec CAS
// phpCAS::setDebug('debugcas.txt');
// Initialiser la connexion avec CAS 
$cas_host = $sso; // l'hôte du serveur CAS
$cas_port = 443; // Le port
$cas_root = ''; //inutile avec ARGOS
// Le premier argument est la version du protocole CAS
phpCAS::client(CAS_VERSION_2_0, $cas_host, $cas_port, $cas_root, false);
phpCAS::setLang('french');
// On indique qu'il n'y a pas de validation du certificat SSL à faire
phpCAS::setNoCasServerValidation();
// Gestion du single sign-out
phpCAS::handleLogoutRequests(false);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Appel pour se déconnecter
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

phpCAS::logout();

?>
