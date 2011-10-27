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

// Fichier appelé par un service externe, pour l'instant juste utilisé en test sur Bordeaux.
// Une clef est transmise ; elle est comparée avec celle stockée dans un fichier du dossier /webservices/ donc non visible dans les sources / le svn.
// Passage en POST des paramètres.

// Atteste l'appel de cette page avant l'inclusion d'une autre
define('SACoche','webservices');

// Constantes / Fonctions de redirections / Configuration serveur
require_once('./_inc/constantes.php');
require_once('./_inc/fonction_redirection.php');
require_once('./_inc/config_serveur.php');

// Fonctions
require_once('./_inc/fonction_clean.php');
require_once('./_inc/fonction_sessions.php');
require_once('./_inc/fonction_divers.php');
require_once('./_inc/fonction_formulaires_select.php');
require_once('./_inc/fonction_affichage.php');

// On récupère les paramètres
$WS_qui = (isset($_POST['qui'])) ? clean_texte($_POST['qui']) : ( (isset($_GET['qui'])) ? clean_texte($_GET['qui']) : '' ) ;
$WS_cle = (isset($_POST['cle'])) ? clean_texte($_POST['cle']) : '';
$WS_uai = (isset($_POST['uai'])) ? clean_uai($_POST['uai'])   : '';
$WS_uid = (isset($_POST['uid'])) ? clean_texte($_POST['uid']) : '';

// On ne vérifie que le 1er paramètre (le service web prendra éventuellement en charge la suite).
$tab_ws = array('argos_parent','argos_ajout');
if(!in_array($WS_qui,$tab_ws))
{
	exit('Erreur : nom du service web manquant ou incorrect !');
}
$fichier = './webservices/'.$WS_qui.'.php';
if(!is_file($fichier))
{
	exit('Erreur : le service web "'.$WS_qui.'" n\'est pas disponible sur cette installation !');
}
// Place au service web à présent.
require($fichier);

?>