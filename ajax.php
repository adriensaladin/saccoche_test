<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010
 * 
 * ****************************************************************************************************
 * SACoche <http://competences.sesamath.net> - Suivi d'Acquisitions de Compétences
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

// Paramètres transmis
$DOSSIER = (isset($_GET['dossier'])) ? $_GET['dossier'] : 'public';
$FICHIER = (isset($_GET['fichier'])) ? $_GET['fichier'] : 'index';

// Fonctions
require_once('./_inc/fonction_clean.php');
require_once('./_inc/fonction_sessions.php');
require_once('./_inc/fonction_divers.php');
require_once('./_inc/fonction_requetes.php');
require_once('./_inc/fonction_formulaires_select.php');
require_once('./_inc/fonction_affichage.php');

// Détermination du CHARSET d'en-tête
$test_xml = (strpos($_SERVER['HTTP_ACCEPT'],'/xml')) ? TRUE : FALSE;
$test_upload = ( (isset($_SERVER['CONTENT_TYPE'])) &&(strpos($_SERVER['CONTENT_TYPE'],'multipart/form-data')!==FALSE) ) ? TRUE : FALSE; // L'upload d'un fichier XML change le HTTP_ACCEPT, d'où ce second test
$format = ( $test_xml && !$test_upload ) ? 'text/xml' : 'text/html' ;
header('Content-Type: '.$format);header('Charset: utf-8');

// Ouverture de la session et gestion des droits d'accès
gestion_session($PROFIL_REQUIS = $DOSSIER);

// Arrêt s'il fallait seulement mettre la session à jour (la session d'un user connecté n'a pas été perdue si on arrive jusqu'ici)
if($FICHIER=='conserver_session_active')
{
	exit('ok');
}

// Blocage éventuel par le webmestre ou un administrateur
tester_blocage_application($_SESSION['BASE'],$demande_connexion_profil=false);

// Informations sur l'hébergement
$fichier_constantes = './__private/config/constantes.php';
if(is_file($fichier_constantes))
{
	require_once($fichier_constantes);
	// Classe de connexion aux BDD
	require_once('./_inc/class.DB.php');
	// Choix des paramètres de connexion à la base de données adaptée...
	// ...multi-structure ; base sacoche_structure_***
	if( (in_array($_SESSION['USER_PROFIL'],array('administrateur','directeur','professeur','eleve'))) && (HEBERGEUR_INSTALLATION=='multi-structures') )
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
	$fichier_mysql_config = './__private/mysql/'.$fichier_mysql_config.'.php';
	$fichier_class_config = './_inc/'.$fichier_class_config.'.php';
	// Chargement du fichier de connexion à la BDD
	if(is_file($fichier_mysql_config))
	{
		require_once($fichier_mysql_config);
		require_once($fichier_class_config);
	}
	elseif($FICHIER!='installation')
	{
		affich_message_exit($titre='Paramètres BDD manquants',$contenu='Paramètres de connexion à la base de données manquants.<br /><a href="./index.php?fichier=installation">Procédure d\'installation du site SACoche.</a>');
	}
}
elseif($FICHIER!='installation')
{
	affich_message_exit($titre='Informations hébergement manquantes',$contenu='Informations concernant l\'hébergeur manquantes.<br /><a href="./index.php?fichier=installation">Procédure d\'installation du site SACoche.</a>');
}

// Chargement de la page concernée
$filename_php = './pages_'.$DOSSIER.'/'.$FICHIER.'.ajax.php';
if(is_file($filename_php))
{
	include($filename_php);
}
else
{
	echo'Page "'.$filename_php.'" manquante.';
}
?>
