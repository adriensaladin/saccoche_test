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

//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
// Avertissement : le contenu de ce fichier doit pas être modifié à la légère !
// Seul un développeur averti peut jouer sur certains paramètres...
//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

// VERSION_PROG : version des fichiers installés, à comparer avec la dernière version disponible sur le serveur communautaire
// VERSION_BASE : version de la base associée, à comparer avec la version de la base actuellement installée
define('VERSION_PROG','2010-06-12');
define('VERSION_BASE','2010-06-12');

// VERSION_CSS_SCREEN / VERSION_CSS_PRINT / VERSION_JS_BIBLIO / VERSION_JS_GLOBAL / VERSION_JS_FILE
// Pour éviter les problèmes de mise en cache (hors serveur localhost), modifier ces valeurs lors d'une mise à jour
define('VERSION_CSS_SCREEN',25);
define('VERSION_CSS_PRINT',1);
define('VERSION_JS_BIBLIO',2);
define('VERSION_JS_GLOBAL',24);
$VERSION_JS_FILE = 2;	// Modifiée ensuite si besoin dans le script associé à la page

// Quelques chemins... pouvant être modifiés dans un cadre particulier (installation Sésamath)
$CHEMIN_MYSQL  = './__private/mysql/';
$CHEMIN_CONFIG = './__private/config/';

// $ALERTE_SSO : pour signaler éventuellement qu'une deconnexion de SACoche n'entraîne pas une déconnexion d'un ENT
$ALERTE_SSO = false;

// ID_DEMO : valeur de $_SESSION['SESAMATH_ID'] correspondant à l'établissement de démonstration
// 0 pose des pbs, et il faut prendre un id disponible dans la base d'établissements de Sésamath
define('ID_DEMO',9999);

// ID_MATIERE_TRANSVERSALE : id de la matière transversale dans la table "sacoche_matiere"
// LISTING_ID_NIVEAUX_PALIERS : tableau des id des niveaux des paliers dans la table "sacoche_niveau"
define('ID_MATIERE_TRANSVERSALE',99);
define('LISTING_ID_NIVEAUX_PALIERS','.46.47.48.49.');

// CHARSET : "iso-8859-1" ou "utf-8" suivant l'encodage utilisé ; ajouter si besoin "AddDefaultCharset ..." dans le fichier .htaccess
define('CHARSET','utf-8');

// SERVEUR_ADRESSE
$protocole = ( isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS']=='on') ) ? 'https://' : 'http://';
$chemin = $protocole.$_SERVER["SERVER_NAME"].$_SERVER["SCRIPT_NAME"];
$fin = mb_strpos($chemin,SACoche);
if($fin)
{
	$chemin = mb_substr($chemin,0,$fin-1);
}
define('SERVEUR_ADRESSE',$chemin);

// SERVEUR_TYPE : Serveur local de développement (LOCAL) ou serveur en ligne de production (PROD)
$serveur = in_array($_SERVER['SERVER_NAME'],array('localhost','127.0.0.1')) ? 'LOCAL' : 'PROD';
define('SERVEUR_TYPE',$serveur);

// SERVEUR_PROJET        : URL du projet SACoche
// SERVEUR_COMMUNAUTAIRE : URL complète du fichier chargé d'effectuer la liaison entre les installations de SACoche et le serveur communautaire concernant les référentiels.
// SERVEUR_DOCUMENTAIRE  : URL complète du fichier chargé d'afficher les documentations
// SERVEUR_VERSION       : URL complète du fichier chargé de renvoyer le numéro de la dernière version disponible
define('SERVEUR_PROJET'        , 'http://sacoche.xcasenligne.fr'); // http://competences.sesamath.net
define('SERVEUR_COMMUNAUTAIRE' , SERVEUR_PROJET.'/appel_externe.php');
define('SERVEUR_DOCUMENTAIRE'  , SERVEUR_PROJET.'/appel_doc.php');
define('SERVEUR_VERSION'       , SERVEUR_PROJET.'/appel_version.php');

?>