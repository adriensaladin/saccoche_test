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

//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
// Avertissement : le contenu de ce fichier doit pas être modifié à la légère !
// Seul un développeur averti peut jouer sur certains paramètres...
//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

// VERSION_PROG : version des fichiers installés, à comparer avec la dernière version disponible sur le serveur communautaire ; pour une conversion en entier : list($annee,$mois,$jour) = explode('-',substr(VERSION_PROG,0,10); $indice_version = (date('Y')-2011)*365 + date('z',mktime(0,0,0,$mois,$jour,$annee));
// VERSION_BASE : version de la base associée, à comparer avec la version de la base actuellement installée
define('VERSION_PROG', @file_get_contents('VERSION.txt') );	// Ne pas mettre de chemin ! Dans un fichier texte pour permettre un appel au serveur communautaire sans lui faire utiliser PHP.
define('VERSION_BASE','2011-11-20');

// Quelques chemins, avec le séparateur final
define('CHEMIN_SACOCHE'       , realpath(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR);
define('DOSSIER_MYSQL'        , '__private'.DIRECTORY_SEPARATOR.'mysql'.DIRECTORY_SEPARATOR);
define('DOSSIER_CONFIG'       , '__private'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR);
define('DOSSIER_FONT'         , '_lib'.DIRECTORY_SEPARATOR.'FPDF'.DIRECTORY_SEPARATOR.'font'.DIRECTORY_SEPARATOR);
define('DOSSIER_SQL_STRUCTURE', '_sql'.DIRECTORY_SEPARATOR.'structure'.DIRECTORY_SEPARATOR);
define('DOSSIER_SQL_WEBMESTRE', '_sql'.DIRECTORY_SEPARATOR.'webmestre'.DIRECTORY_SEPARATOR);
define('CHEMIN_MYSQL'         , CHEMIN_SACOCHE.DOSSIER_MYSQL);
define('CHEMIN_CONFIG'        , CHEMIN_SACOCHE.DOSSIER_CONFIG);
define('CHEMIN_SQL_STRUCTURE' , CHEMIN_SACOCHE.DOSSIER_SQL_STRUCTURE);
define('CHEMIN_SQL_WEBMESTRE' , CHEMIN_SACOCHE.DOSSIER_SQL_WEBMESTRE);
define('FPDF_FONTPATH'        , CHEMIN_SACOCHE.DOSSIER_FONT); // Pour FPDF (répertoire où se situent les polices)

define('ID_DEMO'                  ,9999);          // id de l'établissement de démonstration (pour $_SESSION['SESAMATH_ID']) ; 0 pose des pbs, et il faut prendre un id disponible dans la base d'établissements de Sésamath
define('ID_MATIERE_TRANSVERSALE'  ,99);            // id de la matière transversale dans la table "sacoche_matiere"
define('LISTING_ID_NIVEAUX_CYCLES','.1.2.3.4.5.'); // listing des id des cycles dans la table "sacoche_niveau"
define('LISTING_ID_PALIERS'       ,'.1.2.3.');     // listing des id des paliers dans la table "sacoche_socle_palier"

// CHARSET : "iso-8859-1" ou "utf-8" suivant l'encodage utilisé ; présence aussi d'un "AddDefaultCharset ..." dans le fichier .htaccess
// Cependant, tout le site ayant été prévu et conçu en UTF-8, changer le CHARSET semble assez hasardeux pour ne pas dire risqué...
define('CHARSET','utf-8');

// SERVEUR_ADRESSE
$protocole = ( isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS']=='on') ) ? 'https://' : 'http://';
$chemin = $protocole.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];
$fin = strpos($chemin,SACoche); // pas mb_strpos pour éviter une erreur fatale d'entrée.
if($fin)
{
	$chemin = substr($chemin,0,$fin-1); // pas mb_substr pour éviter une erreur fatale d'entrée.
}
define('SERVEUR_ADRESSE',$chemin);

// SERVEUR_TYPE
$serveur = (gethostbyname($_SERVER['HTTP_HOST'])=='127.0.0.1') ? 'LOCAL' : ( (strpos($_SERVER['HTTP_HOST'],'.devsesamath.net')) ? 'DEV' : 'PROD' ) ;
define('SERVEUR_TYPE',$serveur); // PROD | DEV | LOCAL

define('SERVEUR_PROJET'        ,'http://sacoche.sesamath.net');         // URL du projet SACoche
define('SERVEUR_COMMUNAUTAIRE' ,SERVEUR_PROJET.'/appel_externe.php');   // URL du fichier chargé d'effectuer la liaison entre les installations de SACoche et le serveur communautaire concernant les référentiels.
define('SERVEUR_DOCUMENTAIRE'  ,SERVEUR_PROJET.'/appel_doc.php');       // URL du fichier chargé d'afficher les documentations
define('SERVEUR_VERSION'       ,SERVEUR_PROJET.'/sacoche/VERSION.txt'); // URL du fichier chargé de renvoyer le numéro de la dernière version disponible
define('SERVEUR_TELECHARGEMENT',SERVEUR_PROJET.'/telechargement.php');  // URL du fichier renvoyant le ZIP de la dernière archive de SACoche disponible
define('SERVEUR_RSS'           ,SERVEUR_PROJET.'/_rss/rss.xml');        // URL du fichier comportant le flux RSS

define('COOKIE_STRUCTURE','SACoche-etablissement');  // nom du cookie servant à retenir l'établissement sélectionné, afin de ne pas à avoir à le sélectionner de nouveau, et à pouvoir le retrouver si perte d'une session et tentative de reconnexion SSO.
define('COOKIE_AUTHMODE' ,'SACoche-mode-connexion'); // nom du cookie servant à retenir le dernier mode de connexion utilisé par un user connecté, afin de pouvoir le retrouver si perte d'une session et tentative de reconnexion SSO.
define('COOKIE_DEBUG'    ,'SACoche-debug');          // nom du cookie servant à retenir si le mode debug est activé (pas en PROD).

// DEBUG
if(SERVEUR_TYPE=='PROD')
{
	// pas de DEBUG en PROD
	define('DEBUG',FALSE);
}
elseif(isset($_GET['debug']))
{
	if($_GET['debug'])
	{
		// demande explicite d'activer le mode DEBUG
		define('DEBUG',TRUE);
		setcookie(COOKIE_DEBUG,1,0,'');
	}
	else
	{
		// demande explicite de désactiver le mode DEBUG
		define('DEBUG',FALSE);
		setcookie(COOKIE_DEBUG,'',time()-42000,'');
	}
}
elseif(isset($_COOKIE[COOKIE_DEBUG]))
{
	// mode DEBUG à conserver
	define('DEBUG',TRUE);
}
else
{
	// pas de mode DEBUG à conserver
	define('DEBUG',FALSE);
}

?>