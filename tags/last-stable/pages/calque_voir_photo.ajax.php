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

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}
if($_SESSION['SESAMATH_ID']==ID_DEMO) {}

// On récupère l'identifiant de l'élève
$user_id = (isset($_GET['user_id'])) ? (int)$_GET['user_id'] : 0 ;
if(!$user_id)
{
	exit('Erreur avec les données transmises !');
}

// On récupère la photo
$DB_ROW = DB_STRUCTURE_PHOTO::recuperer_photo($user_id);
$image = (!empty($DB_ROW)) ? '<img width="'.$DB_ROW['image_largeur'].'" height="'.$DB_ROW['image_hauteur'].'" src="data:'.image_type_to_mime_type(IMAGETYPE_JPEG).';base64,'.$DB_ROW['image_contenu'].'" alt="" />' : '<img width="'.(PHOTO_DIMENSION_MAXI*2/3).'" height="'.PHOTO_DIMENSION_MAXI.'" src="./_img/trombinoscope_vide.png" alt="" title="absence de photo" />' ;

// On affiche le résultat
exit($image);

?>
