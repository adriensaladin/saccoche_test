<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010
 * 
 * ****************************************************************************************************
 * SACoche <http://sacoche.sesamath.net> - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath <http://www.sesamath.net> - Tous droits réservés.
 * Logiciel placé sous la licence libre GPL 3 <http://www.rodage.org/gpl-3.0.fr.html>.
 * ****************************************************************************************************
 * 
 * Ce fichier est une partie de SACoche.
 * 
 * SACoche est un logiciel libre ; vous pouvez le redistribuer ou le modifier suivant les termes 
 * de la “GNU General Public License” telle que publiée par la Free Software Foundation :
 * soit la version 3 de cette licence, soit (à votre gré) toute version ultérieure.
 * 
 * SACoche est distribué dans l’espoir qu’il vous sera utile, mais SANS AUCUNE GARANTIE :
 * sans même la garantie implicite de COMMERCIALISABILITÉ ni d’ADÉQUATION À UN OBJECTIF PARTICULIER.
 * Consultez la Licence Générale Publique GNU pour plus de détails.
 * 
 * Vous devriez avoir reçu une copie de la Licence Générale Publique GNU avec SACoche ;
 * si ce n’est pas le cas, consultez : <http://www.gnu.org/licenses/>.
 * 
 */

// Fichier appelé pour chaque appel ajax.
// Passage en GET des paramètres pour savoir quelle page charger.

// Atteste l'appel de cette page avant l'inclusion d'une autre
define('SACoche','ajax');

// Constantes / Configuration serveur / Autoload classes / Fonction de sortie
require('./_inc/_loader.php');

// Détermination du CHARSET d'en-tête
/*
$test_xml = (strpos($_SERVER['HTTP_ACCEPT'],'/xml')) ? TRUE : FALSE;
$test_upload = ( (isset($_SERVER['CONTENT_TYPE'])) &&(strpos($_SERVER['CONTENT_TYPE'],'multipart/form-data')!==FALSE) ) ? TRUE : FALSE; // L'upload d'un fichier XML change le HTTP_ACCEPT, d'où ce second test
$format = ( $test_xml && !$test_upload ) ? 'text/xml' : 'text/html' ;
header('Content-Type: '.$format.'; charset=utf-8');
*/
header('Content-Type: text/html; charset=utf-8');

// Page appelée
if(!isset($_GET['page']))
{
	exit_error( 'Référence manquante' /*titre*/ , 'Référence de page manquante (le paramètre "page" n\'a pas été transmis en GET).' /*contenu*/ );
}
$PAGE = $_GET['page'];

// Fichier d'informations sur l'hébergement (requis avant la gestion de la session).
if(is_file(CHEMIN_FICHIER_CONFIG_INSTALL))
{
	require(CHEMIN_FICHIER_CONFIG_INSTALL);
}
elseif($PAGE!='public_installation')
{
	exit_error( 'Informations hébergement manquantes' /*titre*/ , 'Les informations relatives à l\'hébergeur n\'ont pas été trouvées.<br />C\'est probablement votre première installation de SACoche, ou bien le fichier "'.FileSystem::fin_chemin(CHEMIN_FICHIER_CONFIG_INSTALL).'" a été supprimé.<br />Cliquer sur le lien ci-dessous.' /*contenu*/ , TRUE /*setup*/ );
}

// Le fait de lister les droits d'accès de chaque page empêche de surcroit l'exploitation d'une vulnérabilité "include PHP" (http://www.certa.ssi.gouv.fr/site/CERTA-2003-ALE-003/).
if(!Session::verif_droit_acces($PAGE))
{
	exit_error( 'Droits manquants' /*titre*/ , 'Droits de la page "'.$PAGE.'" manquants.<br />Soit le paramètre "page" transmis en GET est incorrect, soit les droits de cette page n\'ont pas été attribués dans le fichier "'.FileSystem::fin_chemin(CHEMIN_DOSSIER_INCLUDE.'tableau_droits.php').'".' /*contenu*/ );
}

// Ouverture de la session et gestion des droits d'accès
Session::execute();

// Infos DEBUG dans FirePHP
if (DEBUG>3) afficher_infos_debug_FirePHP();

// Arrêt s'il fallait seulement mettre la session à jour (la session d'un user connecté n'a pas été perdue si on arrive jusqu'ici)
if($PAGE=='conserver_session_active')
{
	exit('ok');
}

// Arrêt s'il fallait seulement fermer la session
if($PAGE=='fermer_session')
{
	Session::close();
	// $_SESSION['USER_PROFIL'] = 'public'; // En ne faisant que ça on oblige à une reconnexion sans détruire la session (donc les infos des fournisseurs de SSO).
	exit('ok');
}

// Blocage éventuel par le webmestre ou un administrateur ou l'automate (on ne peut pas le tester avant car il faut avoir récupéré les données de session)
LockAcces::stopper_si_blocage( $_SESSION['BASE'] , FALSE /*demande_connexion_profil*/ );

// Autres fonctions à charger
require(CHEMIN_DOSSIER_INCLUDE.'fonction_divers.php');

// Jeton CSRF
Session::verifier_jeton_anti_CSRF($PAGE);

// Patch fichier de config
if(is_file(CHEMIN_FICHIER_CONFIG_INSTALL))
{
	// DEBUT PATCH CONFIG 1
	// A compter du 05/12/2010, ajout de paramètres dans le fichier de constantes pour paramétrer cURL. [TODO] peut être retiré dans un an environ
	if(!defined('SERVEUR_PROXY_USED'))
	{
		FileSystem::fabriquer_fichier_hebergeur_info( array('SERVEUR_PROXY_USED'=>'','SERVEUR_PROXY_NAME'=>'','SERVEUR_PROXY_PORT'=>'','SERVEUR_PROXY_TYPE'=>'','SERVEUR_PROXY_AUTH_USED'=>'','SERVEUR_PROXY_AUTH_METHOD'=>'','SERVEUR_PROXY_AUTH_USER'=>'','SERVEUR_PROXY_AUTH_PASS'=>'') );
	}
	// FIN PATCH CONFIG 1
	// DEBUT PATCH CONFIG 2
	// A compter du 26/05/2011, ajout de paramètres dans le fichier de constantes pour les dates CNIL. [TODO] peut être retiré dans un an environ
	if(!defined('CNIL_NUMERO'))
	{
		FileSystem::fabriquer_fichier_hebergeur_info( array('CNIL_NUMERO'=>HEBERGEUR_CNIL,'CNIL_DATE_ENGAGEMENT'=>'','CNIL_DATE_RECEPISSE'=>'') );
	}
	// FIN PATCH CONFIG 2
	// DEBUT PATCH CONFIG 3
	// A compter du 14/03/2012, ajout de paramètres dans le fichier de constantes pour les fichiers associés aux devoirs. [TODO] peut être retiré dans un an environ
	if(!defined('FICHIER_DUREE_CONSERVATION'))
	{
		FileSystem::fabriquer_fichier_hebergeur_info( array('FICHIER_TAILLE_MAX'=>500,'FICHIER_DUREE_CONSERVATION'=>12) );
		$ancien_fichier = CHEMIN_DOSSIER_TMP.'debugcas_'.md5($_SERVER['DOCUMENT_ROOT']).'.txt';
		if(is_file($ancien_fichier)) unlink($ancien_fichier);
	}
	// FIN PATCH CONFIG 3
	// DEBUT PATCH CONFIG 4
	// A compter du 18/10/2012, ajout de paramètre dans le fichier de constantes pour le chemin des logs phpCAS. [TODO] peut être retiré dans un an environ
	if(!defined('CHEMIN_LOGS_PHPCAS'))
	{
		FileSystem::fabriquer_fichier_hebergeur_info( array('CHEMIN_LOGS_PHPCAS'=>CHEMIN_DOSSIER_TMP) );
	}
	// FIN PATCH CONFIG 4
}

// Interface de connexion à la base, chargement et config (test sur CHEMIN_FICHIER_CONFIG_INSTALL car à éviter si procédure d'installation non terminée).
if(is_file(CHEMIN_FICHIER_CONFIG_INSTALL))
{
	// Choix des paramètres de connexion à la base de données adaptée...
	// ...multi-structure ; base sacoche_structure_***
	if( (in_array($_SESSION['USER_PROFIL'],array('administrateur','directeur','professeur','parent','eleve'))) && (HEBERGEUR_INSTALLATION=='multi-structures') )
	{
		$fichier_mysql_config = 'serveur_sacoche_structure_'.$_SESSION['BASE'];
		$fichier_class_config = 'class.DB.config.sacoche_structure';
	}
	// ...multi-structure ; base sacoche_webmestre
	elseif( (in_array($_SESSION['USER_PROFIL'],array('webmestre','public'))) && (HEBERGEUR_INSTALLATION=='multi-structures') )
	{
		$fichier_mysql_config = 'serveur_sacoche_webmestre';
		$fichier_class_config = 'class.DB.config.sacoche_webmestre';
	}
	// ...mono-structure ; base sacoche_structure
	elseif(HEBERGEUR_INSTALLATION=='mono-structure')
	{
		$fichier_mysql_config = 'serveur_sacoche_structure';
		$fichier_class_config = 'class.DB.config.sacoche_structure';
	}
	else
	{
		exit_error( 'Configuration anormale' /*titre*/ , 'Une anomalie dans les données d\'hébergement et/ou de session empêche l\'application de se poursuivre.<br />HEBERGEUR_INSTALLATION vaut '.HEBERGEUR_INSTALLATION.'<br />$_SESSION["USER_PROFIL"] vaut '.$_SESSION['USER_PROFIL'] /*contenu*/ );
	}
	// Chargement du fichier de connexion à la BDD
	define('CHEMIN_FICHIER_CONFIG_MYSQL',CHEMIN_DOSSIER_MYSQL.$fichier_mysql_config.'.php');
	if(is_file(CHEMIN_FICHIER_CONFIG_MYSQL))
	{
		require(CHEMIN_FICHIER_CONFIG_MYSQL);
		require(CHEMIN_DOSSIER_INCLUDE.$fichier_class_config.'.php');
	}
	elseif($PAGE!='public_installation')
	{
		exit_error( 'Paramètres BDD manquants' /*titre*/ , 'Les paramètres de connexion à la base de données n\'ont pas été trouvés.<br />C\'est probablement votre première installation de SACoche, ou bien le fichier "'.FileSystem::fin_chemin(CHEMIN_FICHIER_CONFIG_MYSQL).'" a été supprimé.<br />Cliquer sur le lien ci-dessous.' /*contenu*/ , TRUE /*setup*/ );
	}
}

// Chargement de la page concernée
$filename_php = CHEMIN_DOSSIER_PAGES.$PAGE.'.ajax.php';
if(is_file($filename_php))
{
	require($filename_php);
}
else
{
	echo'Page "'.$filename_php.'" manquante.';
}
?>
