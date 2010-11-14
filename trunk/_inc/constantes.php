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

// VERSION_PROG : version des fichiers installés, à comparer avec la dernière version disponible sur le serveur communautaire
// VERSION_BASE : version de la base associée, à comparer avec la version de la base actuellement installée
define('VERSION_PROG', @file_get_contents('VERSION.txt') );	// Ne pas mettre de chemin ! Dans un fichier texte pour permettre un appel au serveur communautaire sans lui faire utiliser PHP.
define('VERSION_BASE','2010-11-14');

// VERSION_CSS_SCREEN / VERSION_CSS_PRINT / VERSION_JS_BIBLIO / VERSION_JS_GLOBAL / VERSION_JS_FILE
// Pour éviter les problèmes de mise en cache (hors serveur localhost), modifier ces valeurs lors d'une mise à jour
define('VERSION_CSS_SCREEN',50);
define('VERSION_CSS_PRINT',2);
define('VERSION_JS_BIBLIO',3);
define('VERSION_JS_GLOBAL',35);
$VERSION_JS_FILE = 4;	// Modifiée ensuite si besoin dans le script associé à la page

// Quelques chemins... en variables et non constantes car pouvant être modifiés ensuite dans un cadre particulier (installation Sésamath)
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
define('LISTING_ID_NIVEAUX_PALIERS','.1.2.3.4.');

// CHARSET : "iso-8859-1" ou "utf-8" suivant l'encodage utilisé ; présence aussi d'un "AddDefaultCharset ..." dans le fichier .htaccess
// Cependant, tout le site ayant été prévu et conçu en UTF-8, changer le CHARSET est assez hasardeux pour ne pas dire risqué...
define('CHARSET','utf-8');

// SERVEUR_ADRESSE
$protocole = ( isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS']=='on') ) ? 'https://' : 'http://';
$chemin = $protocole.$_SERVER["SERVER_NAME"].$_SERVER["SCRIPT_NAME"];
$fin = strpos($chemin,SACoche); // pas mb_strpos pour éviter une erreur fatale d'entrée.
if($fin)
{
	$chemin = substr($chemin,0,$fin-1); // pas mb_substr pour éviter une erreur fatale d'entrée.
}
define('SERVEUR_ADRESSE',$chemin);

// SERVEUR_TYPE : Serveur local de développement (LOCAL) ou serveur en ligne de production (PROD)
$serveur = in_array($_SERVER['SERVER_NAME'],array('localhost','127.0.0.1')) ? 'LOCAL' : 'PROD';
define('SERVEUR_TYPE',$serveur);

// SERVEUR_PROJET        : URL du projet SACoche
// SERVEUR_COMMUNAUTAIRE : URL complète du fichier chargé d'effectuer la liaison entre les installations de SACoche et le serveur communautaire concernant les référentiels.
// SERVEUR_DOCUMENTAIRE  : URL complète du fichier chargé d'afficher les documentations
// SERVEUR_VERSION       : URL complète du fichier chargé de renvoyer le numéro de la dernière version disponible
define('SERVEUR_PROJET'        , 'http://sacoche.sesamath.net');
define('SERVEUR_COMMUNAUTAIRE' , SERVEUR_PROJET.'/appel_externe.php');
define('SERVEUR_DOCUMENTAIRE'  , SERVEUR_PROJET.'/appel_doc.php');
define('SERVEUR_VERSION'       , SERVEUR_PROJET.'/sacoche/VERSION.txt');

// COOKIE_STRUCTURE : nom du cookie servant à retenir l'établissement sélectionné.
define('COOKIE_STRUCTURE','SACoche-etablissement');

?>