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

// Fichier appelé par un service externe
// Une clef est transmise ; elle est comparée avec celle stockée dans un fichier du dossier /webservices/ donc non visible dans les sources / le svn.
// Passage en POST des paramètres.

// Constantes / Configuration serveur / Autoload classes / Fonction de sortie
require('./_inc/_loader.php');

// Fonctions
require(CHEMIN_DOSSIER_INCLUDE.'fonction_divers.php');

// On récupère les paramètres
$WS_qui  = (isset($_POST['qui']))  ? Clean::texte($_POST['qui']) : ( (isset($_GET['qui'])) ? Clean::texte($_GET['qui']) : '' ) ;
$WS_cle  = (isset($_POST['cle']))  ? Clean::texte($_POST['cle']) : '';
$WS_uai  = (isset($_POST['uai']))  ? Clean::uai($_POST['uai'])   : '';
$WS_uid  = (isset($_POST['uid']))  ? Clean::texte($_POST['uid']) : '';
$WS_data = (isset($_POST['data'])) ? $_POST['data']              : ''; // tableau sérializé

/**
 * Cas d'un service externe récupérant les données d'un user authentifié sur SACoche.
 * C'est un webservice un peu particulier qui ne requiert pas d'autre fichier de code dans CHEMIN_DOSSIER_WEBSERVICES.
 * Il y a en revanche quelques lignes de code associées dans le fichier /index.php
 */
if($WS_qui=='AutoMaths')
{
  // Attention à l'exploitation d'une vulnérabilité "include PHP" (http://www.certa.ssi.gouv.fr/site/CERTA-2003-ALE-003/).
  $WS_cle = str_replace(array('.','/','\\'),'',$WS_cle);
  if(!$WS_cle)
  {
    exit('Erreur : clef non transmise !');
  }
  $fichier = CHEMIN_DOSSIER_LOGINPASS.$WS_cle.'.txt';
  if(!is_file($fichier))
  {
    exit('Erreur : absence de données associées à cette clef !');
  }
  $infos_user = file_get_contents($fichier);
  unlink($fichier);
  exit($infos_user);
}

/**
 * Place aux autres webservices à présent (pour l'instant seulement deux, qui se truovent sur le serveur de Bordeaux).
 * On ne vérifie dans un premier temps que le 1er paramètre (le service web prendra éventuellement en charge la suite).
 */
$tab_ws = array('argos_parent','argos_ajout');
if(!in_array($WS_qui,$tab_ws))
{
  exit('Erreur : nom du service web manquant ou incorrect !');
}
$fichier = CHEMIN_DOSSIER_WEBSERVICES.$WS_qui.'.php';
if(!is_file($fichier))
{
  exit('Erreur : le service web "'.$WS_qui.'" n\'est pas disponible sur cette installation !');
}
require($fichier);

?>