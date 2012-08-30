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

// Tableau avec les messages d'erreurs correspondants aux codes renvoyés par la librairie ZipArchive
// http://fr.php.net/manual/fr/zip.constants.php#83827

$tab_zip_error = array();

$tab_zip_error[ 0] = " | OK | pas d'erreur";
$tab_zip_error[ 1] = " | MULTIDISK | multi-volumes non supporté";
$tab_zip_error[ 2] = " | RENAME | échec renommage fichier temporaire";
$tab_zip_error[ 3] = " | CLOSE | échec fermeture archive";
$tab_zip_error[ 4] = " | SEEK | erreur recherche";
$tab_zip_error[ 5] = " | READ | erreur lecture";
$tab_zip_error[ 6] = " | WRITE | erreur écriture";
$tab_zip_error[ 7] = " | CRC | erreur contrôle redondance cyclique";
$tab_zip_error[ 8] = " | ZIPCLOSED | conteneur de l'archive fermé";
$tab_zip_error[ 9] = " | NOENT | pas de fichier";
$tab_zip_error[10] = " | EXISTS | fichier déjà existant";
$tab_zip_error[11] = " | OPEN | fichier impossible à ouvrir";
$tab_zip_error[12] = " | TMPOPEN | échec création fichier temporaire";
$tab_zip_error[13] = " | ZLIB | erreur Zlib";
$tab_zip_error[14] = " | MEMORY | défaillance allocation mémoire";
$tab_zip_error[15] = " | CHANGED | entrée modifiée";
$tab_zip_error[16] = " | COMPNOTSUPP | méthode de compression non supportée";
$tab_zip_error[17] = " | EOF | fin de fichier prématurée";
$tab_zip_error[18] = " | INVAL | argument invalide";
$tab_zip_error[19] = " | NOZIP | n'est pas une archive zip";
$tab_zip_error[20] = " | INTERNAL | erreur interne";
$tab_zip_error[21] = " | INCONS | archive incohérente";
$tab_zip_error[22] = " | REMOVE | fichier impossible à supprimer";
$tab_zip_error[23] = " | DELETED | entrée supprimée";

?>