<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2009-2015
 *
 * ****************************************************************************************************
 * SACoche <http://sacoche.sesamath.net> - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath <http://www.sesamath.net> - Tous droits réservés.
 * Logiciel placé sous la licence libre Affero GPL 3 <https://www.gnu.org/licenses/agpl-3.0.html>.
 * ****************************************************************************************************
 *
 * Ce fichier est une partie de SACoche.
 *
 * SACoche est un logiciel libre ; vous pouvez le redistribuer ou le modifier suivant les termes 
 * de la “GNU Affero General Public License” telle que publiée par la Free Software Foundation :
 * soit la version 3 de cette licence, soit (à votre gré) toute version ultérieure.
 *
 * SACoche est distribué dans l’espoir qu’il vous sera utile, mais SANS AUCUNE GARANTIE :
 * sans même la garantie implicite de COMMERCIALISABILITÉ ni d’ADÉQUATION À UN OBJECTIF PARTICULIER.
 * Consultez la Licence Publique Générale GNU Affero pour plus de détails.
 *
 * Vous devriez avoir reçu une copie de la Licence Publique Générale GNU Affero avec SACoche ;
 * si ce n’est pas le cas, consultez : <http://www.gnu.org/licenses/>.
 *
 */
 
// Extension de classe qui étend DB (pour permettre l'autoload)

// Ces méthodes ne concernent qu'une base STRUCTURE.
// Ces méthodes ne concernent essentiellement que les tables "sacoche_officiel_saisie", "sacoche_officiel_fichier", "sacoche_officiel_assiduite".

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
public static function DB_recuperer_bilan_officiel_infos( $classe_id , $periode_id , $bilan_type )
{
  $DB_SQL = 'SELECT jointure_date_debut, jointure_date_fin, officiel_'.$bilan_type.', periode_nom, groupe_nom ';
  $DB_SQL.= 'FROM sacoche_jointure_groupe_periode ';
  $DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_periode USING (periode_id) ';
  $DB_SQL.= 'WHERE groupe_id=:classe_id AND periode_id=:periode_id ';
  $DB_VAR = array(
    ':classe_id'  => $classe_id,
    ':periode_id' => $periode_id,
  );
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
 * recuperer_bilan_officiel_saisies_eleves
 *
 * @param string $officiel_type
 * @param int    $periode_id
 * @param string $liste_eleve_id
 * @param int    $prof_id     Pour restreindre aux saisies d'un prof.
 * @param bool   $with_rubrique_nom      On récupère aussi le nom de la matière correspondante (pas prévu pour le socle).
 * @param bool   $with_periodes_avant    On récupère aussi les données des périodes antérieures.
 * @param bool   $only_synthese_generale Pour restreindre aux synthèses générales.
 * @return array
 */
public static function DB_recuperer_bilan_officiel_saisies_eleves( $officiel_type , $periode_id , $liste_eleve_id , $prof_id , $with_rubrique_nom , $with_periodes_avant , $only_synthese_generale )
{
  if($with_rubrique_nom)
  {
    $rubrique_table       = (substr($officiel_type,0,6)!='palier') ? 'sacoche_matiere' : 'sacoche_socle_pilier' ;
    $rubrique_champ_id    = (substr($officiel_type,0,6)!='palier') ? 'matiere_id'      : 'pilier_id' ;
    $rubrique_champ_nom   = (substr($officiel_type,0,6)!='palier') ? 'matiere_nom'     : 'CONCAT("Compétence ",pilier_ref)' ;
    $rubrique_champ_ordre = (substr($officiel_type,0,6)!='palier') ? 'matiere_ordre'   : 'pilier_ordre' ;
  }
  $periode_where = ($with_periodes_avant) ? '' : 'AND periode_id=:periode_id' ;
  $DB_SQL = 'SELECT prof_id, eleve_ou_classe_id AS eleve_id, rubrique_id, saisie_note, saisie_appreciation, user_genre, user_nom, user_prenom ';
  $DB_SQL.= ($with_rubrique_nom)   ? ', '.$rubrique_champ_nom.' as rubrique_nom ' : '' ;
  $DB_SQL.= ($with_periodes_avant) ? ', periode_id , periode_ordre , periode_nom ' : '' ;
  $DB_SQL.= 'FROM sacoche_officiel_saisie ';
  $DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_officiel_saisie.prof_id=sacoche_user.user_id ';
  $DB_SQL.= ($with_rubrique_nom)   ? 'LEFT JOIN '.$rubrique_table.' ON sacoche_officiel_saisie.rubrique_id='.$rubrique_table.'.'.$rubrique_champ_id.' ' : '' ;
  $DB_SQL.= ($with_periodes_avant) ? 'LEFT JOIN sacoche_periode USING(periode_id) ' : '' ;
  $DB_SQL.= 'WHERE officiel_type=:officiel_type '.$periode_where.' AND eleve_ou_classe_id IN('.$liste_eleve_id.') AND saisie_type=:saisie_type ';
  $DB_SQL.= ($prof_id) ? ( ( ($officiel_type=='bulletin') && $_SESSION['OFFICIEL']['BULLETIN_MOYENNE_SCORES'] ) ? 'AND prof_id IN(:prof_id,0) ' :  'AND prof_id=:prof_id ' ) : '' ;
  $DB_SQL.= ($only_synthese_generale) ? 'AND rubrique_id=0 ' : '' ;
  $DB_SQL.= 'ORDER BY ';
  $DB_SQL.= ($with_rubrique_nom)   ? $rubrique_champ_ordre.' ASC, ' : '' ;
  $DB_SQL.= ($with_periodes_avant) ? 'periode_ordre ASC, ' : '' ;
  $DB_SQL.= 'user_nom ASC, user_prenom ASC ';
  $DB_VAR = array(
    ':officiel_type' => $officiel_type,
    ':periode_id'    => $periode_id,
    ':prof_id'       => $prof_id,
    ':saisie_type'   => 'eleve',
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * recuperer_bilan_officiel_saisies_classe
 *
 * @param int    $periode_id
 * @param int    $classe_id
 * @param int    $prof_id     Pour restreindre aux saisies d'un prof.
 * @param bool   $with_periodes_avant   On récupère aussi les données des périodes antérieures.
 * @param bool   $only_synthese_generale Pour restreindre aux synthèses générales.
 * @return array
 */
public static function DB_recuperer_bilan_officiel_saisies_classe( $periode_id , $classe_id , $prof_id , $with_periodes_avant , $only_synthese_generale )
{
  $periode_where = ($with_periodes_avant) ? '' : 'AND periode_id=:periode_id' ;
  $DB_SQL = 'SELECT prof_id, 0 AS eleve_id, rubrique_id, saisie_note, saisie_appreciation, user_genre, user_nom, user_prenom ';
  $DB_SQL.= ', matiere_nom as rubrique_nom ';
  $DB_SQL.= ($with_periodes_avant) ? ', periode_id , periode_ordre , periode_nom ' : '' ;
  $DB_SQL.= 'FROM sacoche_officiel_saisie ';
  $DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_officiel_saisie.prof_id=sacoche_user.user_id ';
  $DB_SQL.= 'LEFT JOIN sacoche_matiere ON sacoche_officiel_saisie.rubrique_id=sacoche_matiere.matiere_id ';
  $DB_SQL.= ($with_periodes_avant) ? 'LEFT JOIN sacoche_periode USING(periode_id) ' : '' ;
  $DB_SQL.= 'WHERE officiel_type=:officiel_type '.$periode_where.' AND eleve_ou_classe_id=:classe_id AND saisie_type=:saisie_type ';
  $DB_SQL.= ($prof_id) ? ( ($_SESSION['OFFICIEL']['BULLETIN_MOYENNE_SCORES']) ? 'AND prof_id IN(:prof_id,0) ' :  'AND prof_id=:prof_id ' ) : '' ;
  $DB_SQL.= ($only_synthese_generale) ? 'AND rubrique_id=0 ' : '' ;
  $DB_SQL.= 'ORDER BY matiere_ordre ASC, ';
  $DB_SQL.= ($with_periodes_avant) ? 'periode_ordre ASC, ' : '' ;
  $DB_SQL.= 'user_nom ASC, user_prenom ASC ';
  $DB_VAR = array(
    ':officiel_type' => 'bulletin',
    ':periode_id'    => $periode_id,
    ':classe_id'     => $classe_id,
    ':prof_id'       => $prof_id,
    ':saisie_type'   => 'classe',
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * recuperer_bilan_officiel_notes_eleves_periode
 *
 * @param int     $periode_id
 * @param string  $liste_eleve_id
 * @param bool    $tri_matiere
 * @return array
 */
public static function DB_recuperer_bilan_officiel_notes_eleves_periode( $periode_id , $liste_eleve_id , $tri_matiere )
{
  $DB_SQL = 'SELECT eleve_ou_classe_id AS eleve_id, rubrique_id, saisie_note, saisie_appreciation ';
  $DB_SQL.= ($tri_matiere)   ? ', matiere_nom as rubrique_nom ' : '' ;
  $DB_SQL.= 'FROM sacoche_officiel_saisie ';
  $DB_SQL.= ($tri_matiere) ? 'LEFT JOIN sacoche_matiere ON sacoche_officiel_saisie.rubrique_id=sacoche_matiere.matiere_id ' : '' ;
  $DB_SQL.= 'WHERE officiel_type=:officiel_type AND periode_id=:periode_id AND eleve_ou_classe_id IN ('.$liste_eleve_id.') AND prof_id=:prof_id AND saisie_type=:saisie_type ';
  $DB_SQL.= ($tri_matiere) ? 'ORDER BY matiere_ordre ASC ' : '' ;
  $DB_VAR = array(
    ':officiel_type' => 'bulletin',
    ':periode_id'    => $periode_id,
    ':prof_id'       => 0,
    ':saisie_type'   => 'eleve',
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * recuperer_bilan_officiel_notes_eleve_periodes
 *
 * @param int     $eleve_id
 * @return array
 */
public static function DB_recuperer_bilan_officiel_notes_eleve_periodes($eleve_id)
{
  $DB_SQL = 'SELECT rubrique_id AS matiere_id, periode_id, saisie_note, periode_nom ';
  $DB_SQL.= 'FROM sacoche_officiel_saisie ';
  $DB_SQL.= 'LEFT JOIN sacoche_periode USING(periode_id) ';
  $DB_SQL.= 'WHERE officiel_type=:officiel_type AND eleve_ou_classe_id=:eleve_id AND prof_id=:prof_id AND saisie_type=:saisie_type AND saisie_note IS NOT NULL ';
  $DB_SQL.= 'ORDER BY periode_ordre ASC ';
  $DB_VAR = array(
    ':officiel_type' => 'bulletin',
    ':eleve_id'      => $eleve_id,
    ':prof_id'       => 0,
    ':saisie_type'   => 'eleve',
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * recuperer_bilan_officiel_notes_classe
 *
 * @param int    $periode_id
 * @param int    $classe_id
 * @return array
 */
public static function DB_recuperer_bilan_officiel_notes_classe( $periode_id , $classe_id )
{
  $DB_SQL = 'SELECT rubrique_id, saisie_note ';
  $DB_SQL.= 'FROM sacoche_officiel_saisie ';
  $DB_SQL.= 'WHERE officiel_type=:officiel_type AND periode_id=:periode_id AND eleve_ou_classe_id=:classe_id AND prof_id=:prof_id AND saisie_type=:saisie_type ';
  $DB_VAR = array(
    ':officiel_type' => 'bulletin',
    ':periode_id'    => $periode_id,
    ':classe_id'     => $classe_id,
    ':prof_id'       => 0,
    ':saisie_type'   => 'classe',
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * recuperer_officiel_assiduite
 *
 * @param int    $periode_id
 * @param array  $eleve_id
 * @return array
 */
public static function DB_recuperer_officiel_assiduite( $periode_id , $eleve_id )
{
  $DB_SQL = 'SELECT assiduite_absence, assiduite_absence_nj, assiduite_retard, assiduite_retard_nj ';
  $DB_SQL.= 'FROM sacoche_officiel_assiduite ';
  $DB_SQL.= 'WHERE periode_id=:periode_id AND user_id=:user_id ';
  $DB_VAR = array(
    ':periode_id' => $periode_id,
    ':user_id'    => $eleve_id,
  );
  return DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Retourner une liste de professeurs rattachés à des élèves (d'une classe donnée) et des matières données.
 *
 * @param int    $classe_id
 * @param string $liste_eleve_id
 * @param string $liste_matiere_id
 * @return array
 */
public static function DB_recuperer_professeurs_eleves_matieres( $classe_id , $liste_eleve_id , $liste_matiere_id )
{
  // Lever si besoin une limitation de GROUP_CONCAT (group_concat_max_len est par défaut limité à une chaine de 1024 caractères) ; éviter plus de 8096 (http://www.glpi-project.org/forum/viewtopic.php?id=23767).
  DB::query(SACOCHE_STRUCTURE_BD_NAME , 'SET group_concat_max_len = 8096');
  // On connait la classe ($classe_id), donc on commence par récupérer les groupes éventuels associés à aux élèves
  $DB_SQL = 'SELECT GROUP_CONCAT(DISTINCT groupe_id SEPARATOR ",") AS sacoche_liste_groupe_id ';
  $DB_SQL.= 'FROM sacoche_jointure_user_groupe ';
  $DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
  $DB_SQL.= 'WHERE user_id IN('.$liste_eleve_id.') AND groupe_type=:type2 ';
  $DB_VAR = array( ':type2' => 'groupe' );
  $liste_groupe_id = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  $liste_groupes = (!$liste_groupe_id) ? $classe_id : $classe_id.','.$liste_groupe_id ;
  // Maintenant qu'on a les matières et la classe / les groupes, on cherche les profs à la fois dans sacoche_jointure_user_matiere et sacoche_jointure_user_groupe .
  // On part de sacoche_jointure_user_matiere qui ne contient que des profs.
  $DB_SQL = 'SELECT DISTINCT CONCAT(matiere_id,"_",user_id) AS concat_id, matiere_id, user_id, user_nom, user_prenom ';
  $DB_SQL.= 'FROM sacoche_jointure_user_matiere ';
  $DB_SQL.= 'LEFT JOIN sacoche_user USING (user_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_user_groupe USING (user_id) ';
  $DB_SQL.= 'WHERE matiere_id IN('.$liste_matiere_id.') AND groupe_id IN('.$liste_groupes.') AND user_sortie_date>NOW() ';
  $DB_SQL.= 'ORDER BY user_nom ASC, user_prenom ASC';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * lister_bilan_officiel_fichiers
 *
 * @param string $officiel_type  rien pour tous les types
 * @param int    $periode_id     0 pour toutes les périodes
 * @param array  $tab_eleve_id
 * @param bool   $with_periode_nom   FALSE par défaut
 * @param string $only_profil_non_vu   '' par défaut ; 'parent' | 'eleve' sinon
 * @return array   array( [eleve_id] => array( 0 => array( 'fichier_date_generation' , 'fichier_date_consultation_eleve' , 'fichier_date_consultation_parent' ) ) ) OU array( array( 'user_id' , 'officiel_type', 'periode_id' , 'fichier_date_generation' , 'fichier_date_consultation_eleve' , 'fichier_date_consultation_parent' ) )
 */
public static function DB_lister_bilan_officiel_fichiers( $officiel_type , $periode_id , $tab_eleve_id , $with_periode_nom=FALSE , $only_profil_non_vu='' )
{
  $champ_consultation = 'fichier_date_consultation_'.$only_profil_non_vu;
  $select_type    = ($officiel_type)         ? '' : 'officiel_type , ' ;
  $select_periode = ($periode_id)            ? '' : 'periode_id , '    ;
  $select_per_nom = ($with_periode_nom)      ? 'periode_nom , '                                : '' ;
  $join_periode   = ($with_periode_nom)      ? 'LEFT JOIN sacoche_periode USING (periode_id) ' : '' ;
  $where_type     = ($officiel_type)         ? 'officiel_type=:officiel_type AND '             : '' ;
  $where_periode  = ($periode_id)            ? 'periode_id=:periode_id AND '                   : '' ;
  $where_profil   = ($only_profil_non_vu)    ? $champ_consultation.' IS NULL AND '             : '' ;
  $order_type     = (!$officiel_type)        ? 'officiel_type ASC '                            : '' ;
  $order_user     = (count($tab_eleve_id)>1) ? 'user_id ASC '                                  : '' ;
  $order_sep      = ( $order_type && $order_user ) ? ' , ' : '';
  $key_eleve_id   = ($officiel_type)         ? TRUE : FALSE ;
  $DB_SQL = 'SELECT user_id , '.$select_type.$select_periode.$select_per_nom.'fichier_date_generation, fichier_date_consultation_eleve, fichier_date_consultation_parent ';
  $DB_SQL.= 'FROM sacoche_officiel_fichier '.$join_periode;
  $DB_SQL.= 'WHERE '.$where_type.$where_periode.$where_profil.'user_id IN ('.implode(',',$tab_eleve_id).') ';
  $DB_SQL.= ( $order_type || $order_user ) ? 'ORDER BY '.$order_type.$order_sep.$order_user : '' ;
  $DB_VAR = array(
    ':officiel_type' => $officiel_type,
    ':periode_id'    => $periode_id,
  );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR, $key_eleve_id);
}

/**
 * lister_officiel_assiduite
 *
 * @param int    $periode_id
 * @param array  $tab_eleve_id
 * @return array
 */
public static function DB_lister_officiel_assiduite( $periode_id , $tab_eleve_id )
{
  $DB_SQL = 'SELECT user_id, assiduite_absence, assiduite_absence_nj, assiduite_retard, assiduite_retard_nj ';
  $DB_SQL.= 'FROM sacoche_officiel_assiduite ';
  $DB_SQL.= 'WHERE periode_id=:periode_id AND user_id IN ('.implode(',',$tab_eleve_id).') ';
  $DB_VAR = array(':periode_id'=>$periode_id);
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_profs_principaux
 *
 * @param int   $classe_id
 * @return array
 */
public static function DB_lister_profs_principaux($classe_id)
{
  $DB_SQL = 'SELECT user_genre, user_nom, user_prenom ';
  $DB_SQL.= 'FROM sacoche_jointure_user_groupe ';
  $DB_SQL.= 'LEFT JOIN sacoche_user USING (user_id) ';
  $DB_SQL.= 'WHERE groupe_id=:groupe_id AND jointure_pp=:pp AND user_sortie_date>NOW() ';
  $DB_SQL.= 'ORDER BY user_nom ASC, user_prenom ASC ';
  $DB_VAR = array(
    ':groupe_id' => $classe_id,
    ':pp'        => 1,
  );
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
  $DB_SQL = 'SELECT eleve_id, resp_legal_num, parent.user_genre, parent.user_nom, parent.user_prenom, sacoche_parent_adresse.* ';
  $DB_SQL.= 'FROM sacoche_user AS enfant ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_parent_eleve ON enfant.user_id=sacoche_jointure_parent_eleve.eleve_id ';
  $DB_SQL.= 'LEFT JOIN sacoche_user AS parent ON sacoche_jointure_parent_eleve.parent_id=parent.user_id ';
  $DB_SQL.= 'LEFT JOIN sacoche_parent_adresse USING (parent_id) ';
  $DB_SQL.= 'WHERE enfant.user_id IN ('.$listing_user_id.') AND parent.user_sortie_date>NOW() ';
  $DB_SQL.= 'ORDER BY eleve_id ASC, resp_legal_num ASC ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL, TRUE);
}

/**
 * ajouter_bilan_officiel_fichier
 *
 * @param int     $user_id
 * @param string  $officiel_type
 * @param int     $periode_id
 * @return void
 */
public static function DB_ajouter_bilan_officiel_fichier( $user_id , $officiel_type , $periode_id )
{
  $DB_SQL = 'INSERT INTO sacoche_officiel_fichier (user_id, officiel_type, periode_id, fichier_date_generation) ';
  $DB_SQL.= 'VALUES(:user_id, :officiel_type, :periode_id, NOW() ) ';
  $DB_VAR = array(
    ':user_id'       => $user_id,
    ':officiel_type' => $officiel_type,
    ':periode_id'    => $periode_id,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_bilan_officiel_fichier_consultation
 *
 * @param int     $user_id
 * @param string  $officiel_type
 * @param int     $periode_id
 * @param string  $champ   "generation" | "consultation_eleve" | "consultation_parent"
 * @return void
 */
public static function DB_modifier_bilan_officiel_fichier_date( $user_id , $officiel_type , $periode_id , $champ )
{
  $DB_SQL = 'UPDATE sacoche_officiel_fichier ';
  $DB_SQL.= 'SET fichier_date_'.$champ.'=NOW() ';
  $DB_SQL.= 'WHERE user_id=:user_id AND officiel_type=:officiel_type AND periode_id=:periode_id ';
  $DB_VAR = array(
    ':user_id'       => $user_id,
    ':officiel_type' => $officiel_type,
    ':periode_id'    => $periode_id,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_bilan_officiel_saisie
 *
 * @param string  $officiel_type
 * @param int     $periode_id
 * @param int     $eleve_ou_classe_id
 * @param int     $rubrique_id
 * @param int     $prof_id
 * @param string  $saisie_type
 * @param decimal $note
 * @param string  $appreciation
 * @return void
 */
public static function DB_modifier_bilan_officiel_saisie( $officiel_type , $periode_id , $eleve_ou_classe_id , $rubrique_id , $prof_id , $saisie_type , $note , $appreciation )
{
  $DB_SQL = 'REPLACE INTO sacoche_officiel_saisie (officiel_type, periode_id, eleve_ou_classe_id, rubrique_id, prof_id, saisie_type, saisie_note, saisie_appreciation) ';
  $DB_SQL.= 'VALUES(:officiel_type, :periode_id, :eleve_ou_classe_id, :rubrique_id, :prof_id, :saisie_type, :saisie_note, :saisie_appreciation) ';
  $DB_VAR = array(
    ':officiel_type'       => $officiel_type,
    ':periode_id'          => $periode_id,
    ':eleve_ou_classe_id'  => $eleve_ou_classe_id,
    ':rubrique_id'         => $rubrique_id,
    ':prof_id'             => $prof_id,
    ':saisie_type'         => $saisie_type,
    ':saisie_note'         => $note,
    ':saisie_appreciation' => $appreciation,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_officiel_assiduite
 *
 * @param string   $mode     sconet | siecle | gepi | pronote | manuel
 * @param int      $periode_id
 * @param int      $user_id
 * @param int|null $nb_absence
 * @param int|null $nb_absence_nj
 * @param int|null $nb_retard
 * @param int|null $nb_retard_nj
 * @return void
 */
public static function DB_modifier_officiel_assiduite( $mode , $periode_id , $user_id , $nb_absence , $nb_absence_nj , $nb_retard , $nb_retard_nj )
{
  // Pronote exporte un fichier pour les absences, et un autre pour les retards, il ne faut donc pas réinitialiser ce qui n'est pas importé.
  $update_absences = 'assiduite_absence=:assiduite_absence, assiduite_absence_nj=:assiduite_absence_nj';
  $update_retards  = 'assiduite_retard=:assiduite_retard, assiduite_retard_nj=:assiduite_retard_nj';
  $update = ($mode!='pronote') ? $update_absences.', '.$update_retards : ( ($nb_absence!==NULL) ? $update_absences : $update_retards ) ;
  $DB_SQL = 'INSERT INTO sacoche_officiel_assiduite ( periode_id,  user_id,  assiduite_absence,  assiduite_absence_nj,  assiduite_retard,  assiduite_retard_nj) ';
  $DB_SQL.= 'VALUES                                 (:periode_id, :user_id, :assiduite_absence, :assiduite_absence_nj, :assiduite_retard, :assiduite_retard_nj) ';
  $DB_SQL.= 'ON DUPLICATE KEY UPDATE '.$update;
  $DB_VAR = array(
    ':periode_id'           => $periode_id,
    ':user_id'              => $user_id,
    ':assiduite_absence'    => $nb_absence,
    ':assiduite_absence_nj' => $nb_absence_nj,
    ':assiduite_retard'     => $nb_retard,
    ':assiduite_retard_nj'  => $nb_retard_nj,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * supprimer_bilan_officiel_saisie
 *
 * @param string  $officiel_type
 * @param int     $periode_id
 * @param int     $eleve_ou_classe_id
 * @param int     $rubrique_id
 * @param int     $prof_id
 * @param string  $saisie_type
 * @return void
 */
public static function DB_supprimer_bilan_officiel_saisie( $officiel_type , $periode_id , $eleve_ou_classe_id , $rubrique_id , $prof_id , $saisie_type )
{
  $DB_SQL = 'DELETE FROM sacoche_officiel_saisie ';
  $DB_SQL.= 'WHERE officiel_type=:officiel_type AND periode_id=:periode_id AND eleve_ou_classe_id=:eleve_ou_classe_id AND rubrique_id=:rubrique_id AND saisie_type=:saisie_type ';
  $DB_SQL.= ($rubrique_id>0) ? 'AND prof_id=:prof_id ' : '' ;
  $DB_VAR = array(
    ':officiel_type'      => $officiel_type,
    ':periode_id'         => $periode_id,
    ':eleve_ou_classe_id' => $eleve_ou_classe_id,
    ':rubrique_id'        => $rubrique_id,
    ':prof_id'            => $prof_id,
    ':saisie_type'        => $saisie_type,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

}
?>