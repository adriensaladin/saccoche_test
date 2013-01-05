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
// Ces méthodes ne concernent essentiellement les tables "sacoche_officiel_saisie", "sacoche_officiel_fichier", "sacoche_officiel_assiduite".

class DB_STRUCTURE_OFFICIEL extends DB
{

/**
 * recuperer_bilan_officiel_infos
 *
 * @param int    $classe_id
 * @param int    $periode_id
 * @param string $bilan_type
 * @return array
 */
public static function DB_recuperer_bilan_officiel_infos($classe_id,$periode_id,$bilan_type)
{
	$DB_SQL = 'SELECT jointure_date_debut, jointure_date_fin, officiel_'.$bilan_type.', periode_nom, groupe_nom ';
	$DB_SQL.= 'FROM sacoche_jointure_groupe_periode ';
	$DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_periode USING (periode_id) ';
	$DB_SQL.= 'WHERE groupe_id=:classe_id AND periode_id=:periode_id ';
	$DB_VAR = array(':classe_id'=>$classe_id,':periode_id'=>$periode_id);
	return DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * recuperer_pays_majoritaire
 *
 * @return string
 */
public static function DB_recuperer_pays_majoritaire()
{
	$DB_SQL = 'SELECT adresse_pays_nom ';
	$DB_SQL.= 'FROM sacoche_parent_adresse ';
	$DB_SQL.= 'WHERE adresse_pays_nom!="" ';
	$DB_SQL.= 'GROUP BY adresse_pays_nom ';
	$DB_SQL.= 'ORDER BY COUNT(adresse_pays_nom) DESC ';
	$DB_SQL.= 'LIMIT 1 ';
	return DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * recuperer_bilan_officiel_saisies
 *
 * @param string $officiel_type
 * @param int    $periode_id
 * @param string $liste_eleve_id
 * @param int    $prof_id     Pour restreindre aux saisies d'un prof.
 * @param bool   $with_rubrique_nom     On récupère aussi le nom de la matière correspondante (pas prévu pour le socle).
 * @return array
 */
public static function DB_recuperer_bilan_officiel_saisies($officiel_type,$periode_id,$liste_eleve_id,$prof_id,$with_rubrique_nom)
{
	if($with_rubrique_nom)
	{
		$rubrique_table       = (substr($officiel_type,0,6)!='palier') ? 'sacoche_matiere' : 'sacoche_socle_pilier' ;
		$rubrique_champ_id    = (substr($officiel_type,0,6)!='palier') ? 'matiere_id'      : 'pilier_id' ;
		$rubrique_champ_nom   = (substr($officiel_type,0,6)!='palier') ? 'matiere_nom'     : 'CONCAT("Compétence ",pilier_ref)' ;
		$rubrique_champ_ordre = (substr($officiel_type,0,6)!='palier') ? 'matiere_ordre'   : 'pilier_ordre' ;
	}
	$DB_SQL = 'SELECT prof_id, eleve_id, rubrique_id, saisie_note, saisie_appreciation, CONCAT(user_nom," ",SUBSTRING(user_prenom,1,1),".") AS prof_info ';
	$DB_SQL.= ($with_rubrique_nom) ? ', '.$rubrique_champ_nom.' as rubrique_nom ' : '' ;
	$DB_SQL.= 'FROM sacoche_officiel_saisie ';
	$DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_officiel_saisie.prof_id=sacoche_user.user_id ';
	$DB_SQL.= ($with_rubrique_nom) ? 'LEFT JOIN '.$rubrique_table.' ON sacoche_officiel_saisie.rubrique_id='.$rubrique_table.'.'.$rubrique_champ_id.' ' : '' ;
	$DB_SQL.= 'WHERE officiel_type=:officiel_type AND periode_id=:periode_id AND eleve_id IN('.$liste_eleve_id.') ';
	$DB_SQL.= ($prof_id) ? ( ($_SESSION['OFFICIEL']['BULLETIN_MOYENNE_SCORES']) ? 'AND prof_id IN(:prof_id,0) ' :  'AND prof_id=:prof_id ' ) : '' ;
	$DB_SQL.= ($with_rubrique_nom) ? 'ORDER BY '.$rubrique_champ_ordre.' ASC, prof_info ASC ' : '' ;
	$DB_VAR = array(':officiel_type'=>$officiel_type,':periode_id'=>$periode_id,':prof_id'=>$prof_id);
	$prof_key = ($prof_id) ? TRUE : FALSE ;
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR, $prof_key);
}

/**
 * recuperer_bilan_officiel_notes
 *
 * @param int    $periode_id
 * @param array  $tab_eleve_id
 * @return array
 */
public static function DB_recuperer_bilan_officiel_notes($periode_id,$tab_eleve_id)
{
	$DB_SQL = 'SELECT eleve_id, rubrique_id, saisie_note, saisie_appreciation ';
	$DB_SQL.= 'FROM sacoche_officiel_saisie ';
	$DB_SQL.= 'WHERE officiel_type=:officiel_type AND periode_id=:periode_id AND eleve_id IN ('.implode(',',$tab_eleve_id).') AND prof_id=:prof_id ';
	$DB_VAR = array(':officiel_type'=>'bulletin',':periode_id'=>$periode_id,':prof_id'=>0);
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * recuperer_officiel_assiduite
 * @param int    $periode_id
 * @param array  $eleve_id
 *
 * @return array
 */
public static function DB_recuperer_officiel_assiduite($periode_id,$eleve_id)
{
	$DB_SQL = 'SELECT assiduite_absence, assiduite_non_justifie, assiduite_retard ';
	$DB_SQL.= 'FROM sacoche_officiel_assiduite ';
	$DB_SQL.= 'WHERE periode_id=:periode_id AND user_id=:user_id ';
	$DB_VAR = array(':periode_id'=>$periode_id,':user_id'=>$eleve_id);
	return DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_bilan_officiel_fichiers
 * @param string $officiel_type  rien pour tous les types
 * @param int    $periode_id     0 pour toutes les périodes
 * @param array  $tab_eleve_id
 *
 * @return array   array( [eleve_id] => array( 0 => array( 'fichier_date' ) ) ) OU array( array( 'user_id' , 'officiel_type', 'periode_id' , 'fichier_date' ) )
 */
public static function DB_lister_bilan_officiel_fichiers($officiel_type,$periode_id,$tab_eleve_id)
{
	$select_type    = ($officiel_type) ? '' : 'officiel_type , ' ;
	$select_periode = ($periode_id)    ? '' : 'periode_id , '    ;
	$where_type     = ($officiel_type) ? 'officiel_type=:officiel_type AND ' : '' ;
	$where_periode  = ($periode_id)    ? 'periode_id=:periode_id AND '       : '' ;
	$key_eleve_id   = ($officiel_type) ? TRUE : FALSE ;
	$DB_SQL = 'SELECT user_id , '.$select_type.$select_periode.'fichier_date ';
	$DB_SQL.= 'FROM sacoche_officiel_fichier ';
	$DB_SQL.= 'WHERE '.$where_type.$where_periode.'user_id IN ('.implode(',',$tab_eleve_id).') ';
	$DB_SQL.= ($officiel_type) ? '' : 'ORDER BY officiel_type ASC ' ;
	$DB_VAR = array(':officiel_type'=>$officiel_type,':periode_id'=>$periode_id);
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR, $key_eleve_id);
}

/**
 * lister_officiel_assiduite
 * @param int    $periode_id
 * @param array  $tab_eleve_id
 *
 * @return array
 */
public static function DB_lister_officiel_assiduite($periode_id,$tab_eleve_id)
{
	$DB_SQL = 'SELECT user_id, assiduite_absence, assiduite_non_justifie, assiduite_retard ';
	$DB_SQL.= 'FROM sacoche_officiel_assiduite ';
	$DB_SQL.= 'WHERE periode_id=:periode_id AND user_id IN ('.implode(',',$tab_eleve_id).') ';
	$DB_VAR = array(':periode_id'=>$periode_id);
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_adresses_parents_for_enfants
 *
 * @param string   $listing_user_id
 * @return array   array( eleve_id => array( i => array(info_resp) ) )
 */
public static function DB_lister_adresses_parents_for_enfants($listing_user_id)
{
	$DB_SQL = 'SELECT eleve_id, resp_legal_num, parent.user_nom, parent.user_prenom, sacoche_parent_adresse.* ';
	$DB_SQL.= 'FROM sacoche_user AS enfant ';
	$DB_SQL.= 'LEFT JOIN sacoche_jointure_parent_eleve ON enfant.user_id=sacoche_jointure_parent_eleve.eleve_id ';
	$DB_SQL.= 'LEFT JOIN sacoche_user AS parent ON sacoche_jointure_parent_eleve.parent_id=parent.user_id ';
	$DB_SQL.= 'LEFT JOIN sacoche_parent_adresse USING (parent_id) ';
	$DB_SQL.= 'WHERE enfant.user_id IN ('.$listing_user_id.') AND parent.user_sortie_date>NOW() ';
	$DB_SQL.= 'ORDER BY eleve_id ASC, resp_legal_num ASC ';
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL, TRUE);
}

/**
 * modifier_bilan_officiel_saisie
 *
 * @param string  $officiel_type
 * @param int     $periode_id
 * @param int     $eleve_id
 * @param int     $rubrique_id
 * @param int     $prof_id
 * @param decimal $note
 * @param string  $appreciation
 * @return void
 */
public static function DB_modifier_bilan_officiel_saisie($officiel_type,$periode_id,$eleve_id,$rubrique_id,$prof_id,$note,$appreciation)
{
	$DB_SQL = 'REPLACE INTO sacoche_officiel_saisie (officiel_type, periode_id, eleve_id, rubrique_id, prof_id, saisie_note, saisie_appreciation) ';
	$DB_SQL.= 'VALUES(:officiel_type, :periode_id, :eleve_id, :rubrique_id, :prof_id, :saisie_note, :saisie_appreciation) ';
	$DB_VAR = array(':officiel_type'=>$officiel_type,':periode_id'=>$periode_id,':eleve_id'=>$eleve_id,':rubrique_id'=>$rubrique_id,':prof_id'=>$prof_id,':saisie_note'=>$note,':saisie_appreciation'=>$appreciation);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_bilan_officiel_fichier
 *
 * @param int     $user_id
 * @param string  $officiel_type
 * @param int     $periode_id
 * @return void
 */
public static function DB_modifier_bilan_officiel_fichier($user_id,$officiel_type,$periode_id)
{
	$DB_SQL = 'REPLACE INTO sacoche_officiel_fichier (user_id, officiel_type, periode_id, fichier_date) ';
	$DB_SQL.= 'VALUES(:user_id, :officiel_type, :periode_id, NOW() ) ';
	$DB_VAR = array(':user_id'=>$user_id,':officiel_type'=>$officiel_type,':periode_id'=>$periode_id);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_officiel_assiduite
 *
 * @param int     $periode_id
 * @param int     $user_id
 * @param int     $nb_absence
 * @param int     $nb_non_justifie
 * @param int     $nb_retard
 * @return void
 */
public static function DB_modifier_officiel_assiduite($periode_id,$user_id,$nb_absence,$nb_non_justifie,$nb_retard)
{
	$DB_SQL = 'REPLACE INTO sacoche_officiel_assiduite (periode_id, user_id, assiduite_absence, assiduite_non_justifie, assiduite_retard) ';
	$DB_SQL.= 'VALUES(:periode_id, :user_id, :assiduite_absence, :assiduite_non_justifie, :assiduite_retard) ';
	$DB_VAR = array(':periode_id'=>$periode_id,':user_id'=>$user_id,':assiduite_absence'=>$nb_absence,':assiduite_non_justifie'=>$nb_non_justifie,':assiduite_retard'=>$nb_retard);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * supprimer_bilan_officiel_saisie
 *
 * @param string  $officiel_type
 * @param int     $periode_id
 * @param int     $eleve_id
 * @param int     $rubrique_id
 * @param int     $prof_id
 * @return void
 */
public static function DB_supprimer_bilan_officiel_saisie($officiel_type,$periode_id,$eleve_id,$rubrique_id,$prof_id=0)
{
	$DB_SQL = 'DELETE FROM sacoche_officiel_saisie ';
	$DB_SQL.= 'WHERE officiel_type=:officiel_type AND periode_id=:periode_id AND eleve_id=:eleve_id AND rubrique_id=:rubrique_id ';
	$DB_SQL.= ($rubrique_id>0) ? 'AND prof_id=:prof_id ' : '' ;
	$DB_VAR = array(':officiel_type'=>$officiel_type,':periode_id'=>$periode_id,':eleve_id'=>$eleve_id,':rubrique_id'=>$rubrique_id,':prof_id'=>$prof_id);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

}
?>