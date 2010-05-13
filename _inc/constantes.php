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

// FILE_CSS_SCREEN / FILE_CSS_PRINT / FILE_JS_BIBLIO / FILE_JS_SCRIPT / FILE_JS_BIBLIO / FILE_JS_SCRIPT
// Pour éviter les problèmes de mise en cache, modifier les noms des fichiers lors d'une mise à jour
define('FILE_CSS_SCREEN','style-23.css'); // .min
define('FILE_CSS_PRINT','style_print.min.css');
define('FILE_JS_BIBLIO','jquery-librairies-2.js');
define('FILE_JS_SCRIPT','script-22.min.js');

// VERSION_JS : pour éviter le problème de mise en cache du js de la page d'accueil principale
$VERSION_JS = '';

// ID_DEMO : id de l'établissement de démonstration
// 0 pose des pbs, et il faut prendre un id disponible dans Sésaprof
// Attention, on ne peut pas changer cette valeur à la légère, il faut aussi modifier les entrées correspondantes dans la BDD...
define('ID_DEMO',9999);

// ID_MATIERE_TRANSVERSALE : id de la matière transversale dans la table "sacoche_matiere"
// LISTING_ID_NIVEAUX_PALIERS : tableau des id des niveaux des paliers dans la table "sacoche_niveau"
// Ne pas changer ces valeurs !
define('ID_MATIERE_TRANSVERSALE',99);
define('LISTING_ID_NIVEAUX_PALIERS','.46.47.48.49.');

// CHARSET : "iso-8859-1" ou "utf-8" suivant l'encodage utilisé ; ajouter si besoin "AddDefaultCharset ..." dans le fichier .htaccess
// Ne pas changer cette valeur !
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
$serveur = ($_SERVER['SERVER_NAME']=='localhost') ? 'LOCAL' : 'PROD';
define('SERVEUR_TYPE',$serveur);

// SERVEUR_COMMUNAUTAIRE : URL du fichier sur le serveur communautaire servant à faire la liaison avec les installations de SACoche
// Ne pas changer cette valeur !
define('SERVEUR_COMMUNAUTAIRE','http://competences.sesamath.net/V2/appel_externe.php');

?>