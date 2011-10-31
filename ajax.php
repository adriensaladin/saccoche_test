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

// Constantes / Fonctions de redirections / Configuration serveur
require_once('./_inc/constantes.php');
require_once('./_inc/fonction_redirection.php');
require_once('./_inc/config_serveur.php');
require_once('./_inc/fonction_sessions.php');

// Détermination du CHARSET d'en-tête
$test_xml = (strpos($_SERVER['HTTP_ACCEPT'],'/xml')) ? TRUE : FALSE;
$test_upload = ( (isset($_SERVER['CONTENT_TYPE'])) &&(strpos($_SERVER['CONTENT_TYPE'],'multipart/form-data')!==FALSE) ) ? TRUE : FALSE; // L'upload d'un fichier XML change le HTTP_ACCEPT, d'où ce second test
$format = ( $test_xml && !$test_upload ) ? 'text/xml' : 'text/html' ;
header('Content-Type: '.$format.'; charset=utf-8');

// Page appelée
if(!isset($_GET['page']))
{
	affich_message_exit($titre='Référence manquante',$contenu='Référence de page manquante.');
}
$PAGE = $_GET['page'];

// Fichier d'informations sur l'hébergement (requis avant la gestion de la session).
$fichier_constantes = CHEMIN_CONFIG.'constantes.php';
if(is_file($fichier_constantes))
{
	require_once($fichier_constantes);
}
elseif($PAGE!='public_installation')
{
	affich_message_exit($titre='Informations hébergement manquantes',$contenu='Informations concernant l\'hébergeur manquantes.',$lien='<a href="./index.php?page=public_installation">Procédure d\'installation de SACoche.</a>');
}

// Ouverture de la session et gestion des droits d'accès
require_once('./_inc/tableau_droits.php');
if(!isset($tab_droits[$PAGE]))
{
	affich_message_exit($titre='Droits manquants',$contenu='Droits de la page "'.$PAGE.'" manquants.');
}
gestion_session($tab_droits[$PAGE]);

// Pour le devel
if (DEBUG) afficher_infos_debug();

// Arrêt s'il fallait seulement mettre la session à jour (la session d'un user connecté n'a pas été perdue si on arrive jusqu'ici)
if($PAGE=='conserver_session_active')
{
	exit('ok');
}

// Arrêt s'il fallait seulement fermer la session
if($PAGE=='fermer_session')
{
	close_session();
	// $_SESSION['USER_PROFIL'] = 'public'; // En ne faisant que ça on oblige à une reconnexion sans détruire la session (donc les infos des fournisseurs de SSO).
	exit('ok');
}

// Blocage éventuel par le webmestre ou un administrateur
tester_blocage_application($_SESSION['BASE'],$demande_connexion_profil=false);

// Autres fonctions à charger
require_once('./_inc/fonction_clean.php');
require_once('./_inc/fonction_divers.php');
require_once('./_inc/fonction_appel_serveur_communautaire.php');
require_once('./_inc/fonction_affichage.php');

// Annuler un blocage par l'automate anormalement long
annuler_blocage_anormal();

// Patch fichier de config
if(is_file($fichier_constantes))
{
	// DEBUT PATCH CONFIG 1
	// A compter du 05/12/2010, ajout de paramètres dans le fichier de constantes pour paramétrer cURL. [à retirer dans quelques mois]
	if(!defined('SERVEUR_PROXY_USED') && function_exists('enregistrer_informations_session'))
	{
		fabriquer_fichier_hebergeur_info( array('SERVEUR_PROXY_USED'=>'','SERVEUR_PROXY_NAME'=>'','SERVEUR_PROXY_PORT'=>'','SERVEUR_PROXY_TYPE'=>'','SERVEUR_PROXY_AUTH_USED'=>'','SERVEUR_PROXY_AUTH_METHOD'=>'','SERVEUR_PROXY_AUTH_USER'=>'','SERVEUR_PROXY_AUTH_PASS'=>'') );
	}
	// FIN PATCH CONFIG 1
	// DEBUT PATCH CONFIG 2
	// A compter du 26/05/2011, ajout de paramètres dans le fichier de constantes pour les dates CNIL. [à retirer dans quelques mois]
	if(!defined('CNIL_NUMERO') && function_exists('enregistrer_informations_session'))
	{
		fabriquer_fichier_hebergeur_info( array('CNIL_NUMERO'=>HEBERGEUR_CNIL,'CNIL_DATE_ENGAGEMENT'=>'','CNIL_DATE_RECEPISSE'=>'') );
	}
	// FIN PATCH CONFIG 2
}

// Interface de connexion à la base, chargement et config (test sur $fichier_constantes car à éviter si procédure d'installation non terminée).
if(is_file($fichier_constantes))
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
		affich_message_exit($titre='Configuration anormale',$contenu='Une anomalie dans les données d\'hébergement et/ou de session empêche l\'application de se poursuivre.');
	}
	// Ajout du chemin correspondant
	$fichier_mysql_config = CHEMIN_MYSQL.$fichier_mysql_config.'.php';
	$fichier_class_config = './_inc/'.$fichier_class_config.'.php';
	// Chargement du fichier de connexion à la BDD
	if(is_file($fichier_mysql_config))
	{
		require_once($fichier_mysql_config);
		require_once($fichier_class_config);
	}
	elseif($PAGE!='public_installation')
	{
		affich_message_exit($titre='Paramètres BDD manquants',$contenu='Paramètres de connexion à la base de données manquants.',$lien='<a href="./index.php?page=public_installation">Procédure d\'installation de SACoche.</a>');
	}
}

// Chargement de la page concernée
$filename_php = './pages/'.$PAGE.'.ajax.php';
if(is_file($filename_php))
{
	require($filename_php);
}
else
{
	echo'Page "'.$filename_php.'" manquante.';
}
?>
