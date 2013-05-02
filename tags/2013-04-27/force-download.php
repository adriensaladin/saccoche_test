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

// Fichier appelé pour l'affichage d'un relevé HTML enregistré temporairement.
// Passage en GET d'un paramètre pour savoir quelle page charger.

// Atteste l'appel de cette page avant l'inclusion d'une autre
define('SACoche','force-download');

// Constantes / Configuration serveur / Autoload classes / Fonction de sortie
require('./_inc/_loader.php');

// Paramètre transmis ; attention à l'exploitation d'une vulnérabilité "include PHP" (http://www.certa.ssi.gouv.fr/site/CERTA-2003-ALE-003/)
$fichier_nom = (isset($_GET['fichier'])) ? str_replace(array('/','\\'),'',$_GET['fichier']) : ''; // On ne nettoie pas le caractère "." car le paramètre contient l'extension.

// Vérification de la cohérence du paramètre transmis
$extension = strtolower(pathinfo($fichier_nom,PATHINFO_EXTENSION));
if( !$fichier_nom || !in_array($extension,array('txt','csv')))
{
  exit_error( 'Paramètre manquant ou incorrect' /*titre*/ , 'Le nom "'.html($fichier_nom).'" du fichier demandé est invalide.' /*contenu*/ , '' /*lien*/ );
}
// Vérification de l'existence du fichier concerné
$fichier_chemin = CHEMIN_DOSSIER_EXPORT.$fichier_nom;
if(!is_file($fichier_chemin))
{
  exit_error( 'Document manquant' /*titre*/ , 'Les fichiers sont conservés sur le serveur pendant une durée limitée !' /*contenu*/ , '' /*lien*/ );
}

// Cette méthode pour forcer le téléchargement d'un fichier consomme des ressources serveur (par rapport à une banale rerirection).
// Ce n'est donc qu'à utiliser pour de petits fichiers txt ou csv donc on ne veut pas qu'ils s'ouvrent dans le navigateur
// (ni compliquer la démarche de l'utilisateur en les zippant).
header('Content-disposition: attachment; filename="'.$fichier_nom.'"');
header('Content-Type: application/force-download');
header('Content-Transfer-Encoding: binary');
header('Content-Length: '. filesize($fichier_chemin));
header('Pragma: no-cache');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0'); // IE n'aime pas "no-store" ni "no-cache".
header('Expires: 0');
readfile($fichier_chemin);
exit();
?>