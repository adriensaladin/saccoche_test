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

$groupe_type = (isset($_POST['f_groupe_type'])) ? Clean::texte($_POST['f_groupe_type']) : ''; // d n c g b
$groupe_id   = (isset($_POST['f_groupe_id']))   ? Clean::entier($_POST['f_groupe_id'])  : 0;

$tab_types   = array('d'=>'all' , 'n'=>'niveau' , 'c'=>'classe' , 'g'=>'groupe' , 'b'=>'besoin');

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Afficher les élèves et leurs photos si existantes
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( (!$groupe_id) || (!isset($tab_types[$groupe_type])) )
{
	exit('Erreur avec les données transmises !');
}
// On récupère les élèves
$DB_TAB = DB_STRUCTURE_COMMUN::DB_lister_users_regroupement( 'eleve' , TRUE /*statut*/ , $tab_types[$groupe_type] , $groupe_id ) ;
if(!count($DB_TAB))
{
	exit('Aucun élève trouvé dans ce regroupement.');
}
$tab_vignettes = array();
$img_height = PHOTO_DIMENSION_MAXI;
$img_width  = PHOTO_DIMENSION_MAXI*2/3;
foreach($DB_TAB as $DB_ROW)
{
	$tab_vignettes[$DB_ROW['user_id']] = array(
		'identite' => html($DB_ROW['user_nom']).'<br />'.html($DB_ROW['user_prenom']),
		'image'    => '<img width="'.$img_width.'" height="'.$img_height.'" src="./_img/trombinoscope_vide.png" alt="" title="absence de photo" />'
	);
}
// On récupère les photos
$listing_user_id = implode(',',array_keys($tab_vignettes));
$DB_TAB = DB_STRUCTURE_PHOTO::lister_photos($listing_user_id);
foreach($DB_TAB as $DB_ROW)
{
	$tab_vignettes[$DB_ROW['user_id']]['image'] = '<img width="'.$DB_ROW['image_largeur'].'" height="'.$DB_ROW['image_hauteur'].'" src="data:'.image_type_to_mime_type(IMAGETYPE_JPEG).';base64,'.$DB_ROW['image_contenu'].'" alt="" />';
}
// On affiche tout ça
foreach($tab_vignettes as $user_id => $tab)
{
	echo'<div id="div_'.$user_id.'" class="photo">'.$tab['image'].'<br />'.$tab['identite'].'</div>';
}
exit();

?>
