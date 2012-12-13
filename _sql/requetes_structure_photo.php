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
 
// Extension de classe qui étend DB (pour permettre l'autoload)

// Ces méthodes ne concernent qu'une base STRUCTURE.
// Ces méthodes ne concernent essentiellement les tables "sacoche_officiel_saisie", "sacoche_officiel_fichier", "sacoche_image".

class DB_STRUCTURE_PHOTO extends DB
{

/**
 * recuperer_photo
 *
 * @param int   $user_id
 * @return array
 */
public static function recuperer_photo($user_id)
{
	$DB_SQL = 'SELECT * ';
	$DB_SQL.= 'FROM sacoche_image ';
	$DB_SQL.= 'WHERE user_id=:user_id ';
	$DB_VAR = array(':user_id'=>$user_id);
	return DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_photos
 *
 * @param string   $listing_user_id
 * @return array
 */
public static function lister_photos($listing_user_id)
{
	$DB_SQL = 'SELECT * ';
	$DB_SQL.= 'FROM sacoche_image ';
	$DB_SQL.= 'WHERE user_id IN ('.$listing_user_id.') ';
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * modifier_photo
 *
 * @param int    $user_id   0 pour le tampon de l'établissement
 * @param string $image_contenu
 * @param int    $image_largeur
 * @param int    $image_hauteur
 * @return void
 */
public static function DB_modifier_photo($user_id,$image_contenu,$image_largeur,$image_hauteur)
{
	$DB_SQL = 'REPLACE INTO sacoche_image (user_id, image_objet, image_contenu, image_format, image_largeur, image_hauteur) ';
	$DB_SQL.= 'VALUES(:user_id, :image_objet, :image_contenu, :image_format, :image_largeur, :image_hauteur) ';
	$DB_VAR = array(':user_id'=>$user_id,':image_objet'=>'photo',':image_contenu'=>$image_contenu,':image_format'=>'jpeg',':image_largeur'=>$image_largeur,':image_hauteur'=>$image_hauteur);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * supprimer_photo
 *
 * @param int    $user_id
 * @return void
 */
public static function DB_supprimer_photo($user_id)
{
	$DB_SQL = 'DELETE FROM sacoche_image ';
	$DB_SQL.= 'WHERE user_id=:user_id ';
	$DB_VAR = array(':user_id'=>$user_id);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

}
?>