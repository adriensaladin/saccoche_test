<?php
/**
 * @version $Id: login_SSO.php 7 2009-10-30 20:50:17Z thomas $
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

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}
$TITRE = "Connexion SSO";
?>

<?php
$structure_id = (isset($_GET['structure_id'])) ? intval($_GET['structure_id']) : 0;
$sso          = (isset($_GET['sso']))          ? clean_texte($_GET['sso'])     : '';

require_once('./_inc/tableau_sso.php');	// Charge $tab_sso['nom'] = array('txt'=>'...' , 'doc'=>'...');
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
include_once('./_inc/class.CAS.php');
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
// Appel pour se connecter
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

// Demander à CAS d'aller interroger le serveur
// Cette méthode permet de forcer CAS à demander au client de s'authentifier s'il ne trouve aucun client d'authentifié.
// (redirige vers le serveur d'authentification si aucun utilisateur authentifié n'a été trouvé par le client CAS)
phpCAS::forceAuthentication();
// Rapatrier les informations si elles sont validées par CAS (qui envoie alors un ticket en GET)
$auth = phpCAS::checkAuthentication();
// Récupérer l'identifiant (login ou numéro interne...) de l'utilisateur authentifié pour le traiter dans l'application
$login = phpCAS::getUser();

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Comparer avec les données de la base (demande de connexion comme élève ou professeur ou directeur)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

connecter_user($structure_id,$login,false,$sso);

if($_SESSION['PROFIL']!='public')
{
	// Redirection vers l'espace en cas de succès
	alert_redirection_exit($texte_alert='',$adresse='index.php?dossier='.$_SESSION['PROFIL']);
}
else
{
	// Affichage d'un message d'erreur en cas d'échec
	affich_message_exit($titre='Compte inaccessible.',$contenu='Vous vous êtes correctement identifié sur l\'ENT mais votre identifiant "'.$login.'" n\'est pas présent dans SACoche pour l\'établissement choisi, ou bien le compte est désactivé.');
}
?>