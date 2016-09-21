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

// Ces méthodes ne concernent que les tables "sacoche_livret_*".

class DB_STRUCTURE_LIVRET extends DB
{

/**
 * Vérifier qu'il y a au moins une classe dans l'établissement
 * Vérifier qu'il y a au moins une classe associée au livret, et sinon essayer de faire le boulot automatiquement
 *
 * @param void
 * @return bool | int
 */
public static function DB_initialiser_jointures_livret_classes()
{
  // Vérifier qu'il y a au moins une classe associée au livret
  $DB_SQL = 'SELECT COUNT(*) ';
  $DB_SQL.= 'FROM sacoche_livret_jointure_groupe ';
  if( DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL) )
  {
    return TRUE;
  }
  // Vérifier qu'il y a au moins une classe dans l'établissement
  $DB_SQL = 'SELECT COUNT(*) ';
  $DB_SQL.= 'FROM sacoche_groupe ';
  $DB_SQL.= 'WHERE groupe_type="classe" ';
  if( !DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL) )
  {
    return FALSE;
  }
  // Fonction pour essayer de déterminer le bon type de période associé à une classe
  function determiner_periode_probable( $listing_periodes , $periode_nb )
  {
    if( substr_count( $listing_periodes , 'T' ) )
    { return array('T1','T2','T3'); }
    if( substr_count( $listing_periodes , 'S' ) )
    { return array('S1','S2'); }
    if( $periode_nb == 3 )
    { return array('T1','T2','T3'); }
    if( $periode_nb == 2 )
    { return array('S1','S2'); }
    return array('T1','T2','T3');
  }
  // Initialiser les jointures livret / classes
  $nb_associations = 0;
  $DB_SQL = 'SELECT groupe_id, niveau_ordre, ';
  $DB_SQL.= 'COUNT( periode_id ) AS periode_nb, GROUP_CONCAT(periode_livret) AS listing_periodes ';
  $DB_SQL.= 'FROM sacoche_groupe ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_groupe_periode USING(groupe_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_periode USING(periode_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING(niveau_id) ';
  $DB_SQL.= 'WHERE groupe_type="classe" ';
  $DB_SQL.= 'GROUP BY groupe_id ';
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  foreach($DB_TAB as $DB_ROW)
  {
    $jointure_periode = determiner_periode_probable( $listing_periodes , $periode_nb );
    switch($DB_ROW['niveau_ordre'])
    {
      case 3 : // GS
        DB_STRUCTURE_LIVRET::DB_ajouter_jointure_groupe( $DB_ROW['groupe_id'] , 'cycle1' , 'cycle' , array('') );
        $nb_associations += 1;
        break;
      case 11 : // CP
        DB_STRUCTURE_LIVRET::DB_ajouter_jointure_groupe( $DB_ROW['groupe_id'] , 'cp' , 'periode' , $jointure_periode );
        $nb_associations += 1;
        break;
      case 21 : // CE1
        DB_STRUCTURE_LIVRET::DB_ajouter_jointure_groupe( $DB_ROW['groupe_id'] , 'ce1' , 'periode' , $jointure_periode );
        $nb_associations += 1;
        break;
      case 22 : // CE2
        DB_STRUCTURE_LIVRET::DB_ajouter_jointure_groupe( $DB_ROW['groupe_id'] , 'ce2' , 'periode' , $jointure_periode );
        DB_STRUCTURE_LIVRET::DB_ajouter_jointure_groupe( $DB_ROW['groupe_id'] , 'cycle2' , 'cycle' , array('') );
        $nb_associations += 2;
        break;
      case 31 : // CM1
        DB_STRUCTURE_LIVRET::DB_ajouter_jointure_groupe( $DB_ROW['groupe_id'] , 'cm1' , 'periode' , $jointure_periode );
        $nb_associations += 1;
        break;
      case 32 : // CM2
        DB_STRUCTURE_LIVRET::DB_ajouter_jointure_groupe( $DB_ROW['groupe_id'] , 'cm2' , 'periode' , $jointure_periode );
        $nb_associations += 1;
        break;
      case 100 : // Sixièmes
        DB_STRUCTURE_LIVRET::DB_ajouter_jointure_groupe( $DB_ROW['groupe_id'] , '6e' , 'periode' , $jointure_periode );
        DB_STRUCTURE_LIVRET::DB_ajouter_jointure_groupe( $DB_ROW['groupe_id'] , 'cycle3' , 'cycle' , array('') );
        $nb_associations += 2;
        break;
      case 101 : // Cinquièmes
        DB_STRUCTURE_LIVRET::DB_ajouter_jointure_groupe( $DB_ROW['groupe_id'] , '5e' , 'periode' , $jointure_periode );
        $nb_associations += 1;
        break;
      case 102 : // Quatrièmes
        DB_STRUCTURE_LIVRET::DB_ajouter_jointure_groupe( $DB_ROW['groupe_id'] , '4e' , 'periode' , $jointure_periode );
        $nb_associations += 1;
        break;
      case 103 : // Troisièmes
        DB_STRUCTURE_LIVRET::DB_ajouter_jointure_groupe( $DB_ROW['groupe_id'] , '3e' , 'periode' , $jointure_periode );
        DB_STRUCTURE_LIVRET::DB_ajouter_jointure_groupe( $DB_ROW['groupe_id'] , 'cycle4' , 'cycle' , array('') );
        DB_STRUCTURE_LIVRET::DB_ajouter_jointure_groupe( $DB_ROW['groupe_id'] , 'brevet' , 'college' , array('') );
        $nb_associations += 3;
        break;
    }
  }
  return $nb_associations;
}

/**
 * lister_pages
 *
 * @param bool   $with_info_classe
 * @return array
 */
public static function DB_lister_pages( $with_info_classe )
{
  $DB_SQL = 'SELECT sacoche_livret_page.* ';
  if($with_info_classe)
  {
    $DB_SQL.= ', COUNT( DISTINCT groupe_id ) AS groupe_nb ';
    $DB_SQL.= ', GROUP_CONCAT( DISTINCT groupe_nom SEPARATOR "<br />" ) AS listing_groupe_nom ';
    $DB_SQL.= ', COUNT( DISTINCT element_id ) AS element_nb ';
  }
  $DB_SQL.= 'FROM sacoche_livret_page ';
  if($with_info_classe)
  {
    $DB_SQL.= 'LEFT JOIN sacoche_livret_jointure_groupe USING(livret_page_ref) ';
    $DB_SQL.= 'LEFT JOIN sacoche_groupe USING(groupe_id) ';
    $DB_SQL.= 'LEFT JOIN sacoche_livret_jointure_referentiel ON sacoche_livret_page.livret_page_rubrique_type = sacoche_livret_jointure_referentiel.livret_rubrique_type ';
    $DB_SQL.= 'GROUP BY livret_page_ref ';
    // Ajouter un ordre sur le nom du groupe ne fonctionne pas : traité en PHP après
  }
  $DB_SQL.= 'ORDER BY livret_page_ordre ASC ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * recuperer_page_info
 *
 * @param string   $page_ref
 * @return array
 */
public static function DB_recuperer_page_info($page_ref)
{
  $DB_SQL = 'SELECT * ';
  $DB_SQL.= 'FROM sacoche_livret_page ';
  $DB_SQL.= 'WHERE livret_page_ref=:page_ref ';
  $DB_VAR = array( ':page_ref' => $page_ref );
  return DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Matières du livret
// ////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * tester_livret_matiere_siecle
 *
 * @param void
 * @return bool
 */
public static function DB_tester_livret_matiere_siecle()
{
  $DB_SQL = 'SELECT (livret_siecle_code_gestion IS NOT NULL) AS is_siecle ';
  $DB_SQL.= 'FROM sacoche_livret_matiere ';
  $DB_SQL.= 'GROUP BY is_siecle ';
  // Normalement, livret_siecle_code_gestion est renseigné pour tout ou rien, donc une seule ligne est retournée.
  return (bool)DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * lister_livret_matiere
 *
 * @param void
 * @return array
 */
public static function DB_lister_livret_matiere()
{
  $DB_SQL = 'SELECT * ';
  $DB_SQL.= 'FROM sacoche_livret_matiere ';
  $DB_SQL.= 'ORDER BY livret_matiere_ordre ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * vider_livret_matiere
 *
 * @param void
 * @return void
 */
public static function DB_vider_livret_matiere()
{
  $DB_SQL = 'TRUNCATE sacoche_livret_matiere';
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * ajouter_livret_matiere
 *
 * @param int    $matiere_id
 * @param int    $matiere_ordre
 * @param string $siecle_code_matiere
 * @param string $siecle_code_gestion
 * @param string $siecle_libelle
 * @return void
 */
public static function DB_ajouter_livret_matiere( $matiere_id , $matiere_ordre , $siecle_code_matiere , $siecle_code_gestion , $siecle_libelle )
{
  $DB_SQL = 'INSERT INTO sacoche_livret_matiere( livret_matiere_id , livret_matiere_ordre , livret_siecle_code_matiere , livret_siecle_code_gestion , livret_siecle_libelle) ';
  $DB_SQL.= 'VALUES ( :matiere_id , :matiere_ordre , :siecle_code_matiere , :siecle_code_gestion , :siecle_libelle ) ';
  $DB_VAR = array(
    ':matiere_id'          => $matiere_id,
    ':matiere_ordre'       => $matiere_ordre,
    ':siecle_code_matiere' => $siecle_code_matiere,
    ':siecle_code_gestion' => $siecle_code_gestion,
    ':siecle_libelle'      => $siecle_libelle,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Rubriques & Jointures rubriques / référentiels
// ////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * lister_rubriques_avec_jointures_référentiels
 *
 * CONCAT_WS a une syntaxe un peu différente de CONCAT et ici 2 avantages :
 * - si un champ est NULL, les autres éléments sont retournés, avec que CONCAT renvoie NULL dans ce cas
 * - si un champ est NULL ou vide, il n'est pas concaténé, alors qu'avec CONCAT on obtient un séparateur suivi de rien
 *
 * @param string   $rubrique_type
 * @return array
 */
public static function DB_lister_rubriques_avec_jointures_référentiels( $rubrique_type )
{
  $test_livret_matiere = ( substr($rubrique_type,3) == 'matiere' ) ? TRUE : FALSE ;
  $origine  = ($test_livret_matiere) ? 'matiere' : 'rubrique' ;
  $champ_id = ($test_livret_matiere) ? 'livret_matiere_id AS livret_rubrique_id' : 'livret_rubrique_id' ;
  $select   = ($test_livret_matiere) ? 'livret_siecle_libelle' : 'CONCAT_WS( " - ", livret_rubrique_titre, livret_rubrique_sous_titre)' ;
  $where    = ($test_livret_matiere) ? '' : 'sacoche_livret_rubrique.livret_rubrique_type=:rubrique_type AND' ;
  $DB_SQL = 'SELECT '.$champ_id.', '.$select.' AS livret_rubrique_nom, GROUP_CONCAT(element_id SEPARATOR ",") AS listing_elements ';
  $DB_SQL.= 'FROM sacoche_livret_'.$origine.' ';
  $DB_SQL.= 'LEFT JOIN sacoche_livret_jointure_referentiel ON sacoche_livret_'.$origine.'.livret_'.$origine.'_id = sacoche_livret_jointure_referentiel.livret_rubrique_ou_matiere_id ';
  $DB_SQL.= 'WHERE '.$where.' (sacoche_livret_jointure_referentiel.livret_rubrique_type=:rubrique_type OR sacoche_livret_jointure_referentiel.livret_rubrique_type IS NULL ) ';
  $DB_SQL.= 'GROUP BY livret_rubrique_id ';
  $DB_SQL.= 'ORDER BY livret_'.$origine.'_ordre ASC ';
  $DB_VAR = array( ':rubrique_type' => $rubrique_type );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * tester_page_jointure_rubrique
 *
 * @param string $rubrique_type
 * @param string $rubrique_join
 * @param int    $rubrique_id
 * @return bool
 */

public static function DB_tester_page_jointure_rubrique( $rubrique_type , $rubrique_join , $rubrique_id )
{
  if( $rubrique_join != 'matiere' )
  {
    $DB_SQL = 'SELECT 1 ';
    $DB_SQL.= 'FROM sacoche_livret_page ';
    $DB_SQL.= 'LEFT JOIN sacoche_livret_rubrique ON livret_page_rubrique_type = livret_rubrique_type ';
    $DB_SQL.= 'WHERE livret_page_rubrique_type=:rubrique_type AND livret_page_rubrique_join=:rubrique_join AND livret_rubrique_id=:rubrique_id ';
    $DB_VAR = array(
      ':rubrique_type' => $rubrique_type,
      ':rubrique_join' => $rubrique_join,
      ':rubrique_id'   => $rubrique_id,
    );
    return (bool)DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  }
  else
  {
    $DB_SQL1 = 'SELECT 1 ';
    $DB_SQL1.= 'FROM sacoche_livret_page ';
    $DB_SQL1.= 'WHERE livret_page_rubrique_type=:rubrique_type AND livret_page_rubrique_join=:rubrique_join ';
    $DB_SQL2 = 'SELECT 1 ';
    $DB_SQL2.= 'FROM sacoche_livret_matiere ';
    $DB_SQL2.= 'WHERE livret_matiere_id=:rubrique_id ';
    $DB_VAR = array(
      ':rubrique_type' => $rubrique_type,
      ':rubrique_join' => $rubrique_join,
      ':rubrique_id'   => $rubrique_id,
    );
    return DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL1 , $DB_VAR) && DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL2 , $DB_VAR);
  }
}

/**
 * modifier_jointure_référentiel
 *
 * @param string $rubrique_type
 * @param int    $rubrique_id
 * @param int    $element_id
 * @param bool   $etat   TRUE pour ajouter ; FALSE pour retirer
 * @return void
 */
public static function DB_modifier_jointure_référentiel( $rubrique_type , $rubrique_id , $element_id , $etat )
{
  if($etat)
  {
    // IGNORE car comme on peut cocher parmi les éléments déjà utilisés on n'est pas à l'abri d'une demande de liaison déjà existante.
    $DB_SQL = 'INSERT IGNORE INTO sacoche_livret_jointure_referentiel ( livret_rubrique_type , livret_rubrique_ou_matiere_id , element_id ) ';
    $DB_SQL.= 'VALUES( :rubrique_type , :rubrique_id , :element_id ) ';
  }
  else
  {
    $DB_SQL = 'DELETE FROM sacoche_livret_jointure_referentiel ';
    $DB_SQL.= 'WHERE livret_rubrique_type=:rubrique_type AND livret_rubrique_ou_matiere_id=:rubrique_id AND element_id=:element_id ';
  }
  $DB_VAR = array(
    ':rubrique_type' => $rubrique_type,
    ':rubrique_id'   => $rubrique_id,
    ':element_id'    => $element_id,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_type_jointure
 *
 * @param string $rubrique_type
 * @param string $rubrique_join
 * @return void
 */
public static function DB_modifier_type_jointure( $rubrique_type , $rubrique_join )
{
  $DB_SQL = 'UPDATE sacoche_livret_page ';
  $DB_SQL.= 'SET livret_page_rubrique_join=:rubrique_join ';
  $DB_SQL.= 'WHERE livret_page_rubrique_type=:rubrique_type ';
  $DB_VAR = array(
    ':rubrique_type' => $rubrique_type,
    ':rubrique_join' => $rubrique_join,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * supprimer_jointures_référentiel
 *
 * @param string $rubrique_type
 * @return void
 */
public static function DB_supprimer_jointures_référentiel( $rubrique_type )
{
  $DB_SQL = 'DELETE FROM sacoche_livret_jointure_referentiel ';
  $DB_SQL.= 'WHERE livret_rubrique_type=:rubrique_type ';
  $DB_VAR = array( ':rubrique_type' => $rubrique_type );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Seuils / Paramétrages de colonnes
// ////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * lister_colonnes_infos
 *
 * @param void
 * @return array
 */
public static function DB_lister_colonnes_infos()
{
  $DB_SQL = 'SELECT livret_colonne_type, livret_colonne_id, livret_colonne_titre, livret_colonne_legende, livret_colonne_seuil_defaut_min, livret_colonne_seuil_defaut_max, livret_colonne_couleur_1 ';
  $DB_SQL.= 'FROM sacoche_livret_colonne ';
  $DB_SQL.= 'ORDER BY livret_colonne_ordre ASC ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL , TRUE );
}

/**
 * lister_seuils_valeurs
 *
 * @param void
 * @return array
 */
public static function DB_lister_seuils_valeurs()
{
  $DB_SQL = 'SELECT livret_colonne_id, livret_seuil_min, livret_seuil_max ';
  $DB_SQL.= 'FROM sacoche_livret_seuil ';
  $DB_SQL.= 'ORDER BY livret_colonne_id ASC ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL , TRUE );
}

/**
 * modifier_page_colonne
 *
 * @param string $page_ref
 * @param string $colonne
 * @return void
 */
public static function DB_modifier_page_colonne( $page_ref , $colonne )
{
  $DB_SQL = 'UPDATE sacoche_livret_page ';
  $DB_SQL.= 'SET livret_page_colonne=:colonne ';
  $DB_SQL.= 'WHERE livret_page_ref=:page_ref ';
  $DB_VAR = array(
    ':page_ref' => $page_ref,
    ':colonne'  => $colonne,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_seuils
 *
 * @param string $page_ref
 * @param array  $tab_seuils
 * @return void
 */
public static function DB_modifier_seuils( $page_ref , $tab_seuils )
{
  $DB_SQL = 'UPDATE sacoche_livret_seuil ';
  $DB_SQL.= 'SET livret_seuil_min=:seuil_min, livret_seuil_max=:seuil_max ';
  $DB_SQL.= 'WHERE livret_page_ref=:page_ref AND livret_colonne_id=:colonne_id ';
  $DB_VAR = array( ':page_ref' => $page_ref );
  foreach($tab_seuils as $colonne_id => $tab)
  {
    $DB_VAR[':colonne_id'] = $colonne_id;
    $DB_VAR[':seuil_min'] = $tab['min'];
    $DB_VAR[':seuil_max'] = $tab['max'];
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Jointures livret / groupe
// ////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * lister_classes_avec_jointures_livret
 *
 * @param int   $groupe_id   facultatif, pour restreindre à une classe donnée
 * @return array
 */
public static function DB_lister_classes_avec_jointures_livret( $groupe_id = NULL )
{
  $where_groupe = ($groupe_id) ? 'AND groupe_id=:groupe_id ' : '' ;
  $index_classe = ($groupe_id) ? FALSE : TRUE ;
  // À partir de MySQL 5.7 on peut utiliser ANY_VALUE()
  // @see http://dev.mysql.com/doc/refman/5.7/en/sql-mode.html#sqlmode_only_full_group_by
  // @see http://dev.mysql.com/doc/refman/5.7/en/miscellaneous-functions.html#function_any-value
  if(empty($_SESSION['MYSQL_VERSION']))
  {
    $_SESSION['MYSQL_VERSION'] = DB_STRUCTURE_COMMUN::DB_recuperer_version_MySQL();
  }
  $function = version_compare($_SESSION['MYSQL_VERSION'],'5.7','>=') ? 'ANY_VALUE' : 'MAX' ;
  $DB_SQL = 'SELECT groupe_id, groupe_nom, livret_page_ref, livret_page_moment, livret_page_titre_classe, livret_page_resume, '.$function.'(sacoche_livret_jointure_groupe.livret_page_periodicite) AS periodicite, GROUP_CONCAT(jointure_periode) AS listing_periodes ';
  $DB_SQL.= 'FROM sacoche_groupe ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING(niveau_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_livret_jointure_groupe USING(groupe_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_livret_page USING(livret_page_ref) ';
  $DB_SQL.= 'WHERE groupe_type="classe" '.$where_groupe;
  $DB_SQL.= 'GROUP BY groupe_id, livret_page_ref ';
  $DB_SQL.= 'ORDER BY niveau_id ASC, groupe_nom ASC ';
  $DB_VAR = array( ':groupe_id' => $groupe_id );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR, $index_classe);
}

/**
 * ajouter_jointure_groupe
 *
 * @param int      $groupe_id
 * @param string   $page_ref
 * @param string   $page_periodicite
 * @param array    $tab_jointure_periode
 * @return array
 */
public static function DB_ajouter_jointure_groupe( $groupe_id , $page_ref , $page_periodicite , $tab_jointure_periode )
{
  $DB_SQL = 'INSERT INTO sacoche_livret_jointure_groupe( groupe_id, livret_page_ref, livret_page_periodicite, jointure_periode, jointure_etat) ';
  $DB_SQL.= 'VALUES                                    (:groupe_id,       :page_ref,       :page_periodicite,:jointure_periode,:jointure_etat)';
  $DB_VAR = array(
    ':groupe_id'        => $groupe_id,
    ':page_ref'         => $page_ref,
    ':page_periodicite' => $page_periodicite,
    ':jointure_etat'    => '1vide',
  );
  foreach($tab_jointure_periode as $jointure_periode)
  {
    $DB_VAR[':jointure_periode'] = $jointure_periode;
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  }
}

/**
 * supprimer_jointure_groupe
 *
 * @param int      $groupe_id
 * @param string   $page_ref
 * @param string   $page_periodicite
 * @return void
 */
public static function DB_supprimer_jointure_groupe( $groupe_id , $page_ref , $page_periodicite )
{
  $DB_SQL = 'DELETE FROM sacoche_livret_jointure_groupe ';
  $DB_SQL.= 'WHERE groupe_id=:groupe_id AND livret_page_ref=:page_ref AND livret_page_periodicite=:page_periodicite';
  $DB_VAR = array(
    ':groupe_id'        => $groupe_id,
    ':page_ref'         => $page_ref,
    ':page_periodicite' => $page_periodicite,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// EPI | AP | Parcours
// ////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * pages_for_dispositif
 *
 * @param string   $dispositif     ap | epi | parcours
 * @param string   $parcours_code  facultatif, seulement si parcours
 * @return array
 */
public static function DB_lister_pages_for_dispositif( $dispositif , $parcours_code=NULL )
{
  $test = ($dispositif!='parcours') ? ' = 1 ' : ' LIKE "%'.$parcours_code.'%" ' ;
  $DB_SQL = 'SELECT livret_page_ref, livret_page_ordre, livret_page_moment ';
  $DB_SQL.= 'FROM sacoche_livret_page ';
  $DB_SQL.= 'INNER JOIN sacoche_livret_jointure_groupe USING(livret_page_ref) ';
  $DB_SQL.= 'WHERE livret_page_'.$dispositif.$test;
  $DB_SQL.= 'GROUP BY livret_page_ref ';
  $DB_SQL.= 'ORDER BY livret_page_ordre ASC ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * tester_page_avec_dispositif
 *
 * @param string $page_ref
 * @param string $dispositif     epi | ap | parcours
 * @param string $parcours_code  facultatif, seulement si parcours
 * @return int
 */

public static function DB_tester_page_avec_dispositif( $page_ref , $dispositif , $parcours_code=NULL )
{
  $test = ($dispositif!='parcours') ? ' = 1 ' : ' LIKE "%'.$parcours_code.'%" ' ;
  $DB_SQL = 'SELECT livret_page_ordre ';
  $DB_SQL.= 'FROM sacoche_livret_page ';
  $DB_SQL.= 'WHERE livret_page_ref=:page_ref AND livret_page_'.$dispositif.$test;
  $DB_VAR = array( ':page_ref' => $page_ref );
  return (int)DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// EPI
// ////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * lister_epi_theme
 *
 * @param void
 * @return array
 */
public static function DB_lister_epi_theme()
{
  $DB_SQL = 'SELECT sacoche_livret_epi_theme.* ';
  $DB_SQL.= 'FROM sacoche_livret_epi_theme ';
  $DB_SQL.= 'ORDER BY livret_epi_theme_nom ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * lister_epi
 *
 * @param void
 * @return array
 */
public static function DB_lister_epi()
{
  $DB_SQL = 'SELECT sacoche_livret_epi.*, livret_page_ordre, livret_page_moment, groupe_nom, livret_epi_theme_nom, ';
  $DB_SQL.= 'GROUP_CONCAT( DISTINCT CONCAT(matiere_id,"_",user_id) SEPARATOR " ") AS matiere_prof_id, ';
  $DB_SQL.= 'GROUP_CONCAT( DISTINCT CONCAT(matiere_nom," - ",user_nom," ",user_prenom) SEPARATOR "§BR§") AS matiere_prof_texte ';
  $DB_SQL.= 'FROM sacoche_livret_epi ';
  $DB_SQL.= 'LEFT JOIN sacoche_livret_epi_theme USING(livret_epi_theme_code) ';
  $DB_SQL.= 'INNER JOIN sacoche_livret_jointure_groupe USING(livret_page_ref, groupe_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_livret_jointure_epi_prof USING(livret_epi_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_livret_page USING(livret_page_ref) ';
  $DB_SQL.= 'LEFT JOIN sacoche_groupe USING(groupe_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_matiere USING(matiere_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_livret_jointure_epi_prof.prof_id = sacoche_user.user_id ';
  $DB_SQL.= 'GROUP BY livret_epi_id ';
  $DB_SQL.= 'ORDER BY livret_page_ordre ASC, groupe_nom ASC, livret_epi_theme_nom ASC ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * compter_epi_par_page
 *
 * @param void
 * @return array
 */
public static function DB_compter_epi_par_page()
{
  $DB_SQL = 'SELECT livret_page_ref, livret_epi_theme_code, COUNT(DISTINCT livret_epi_id) AS nombre ';
  $DB_SQL.= 'FROM sacoche_livret_epi ';
  $DB_SQL.= 'INNER JOIN sacoche_livret_jointure_groupe USING(livret_page_ref, groupe_id) ';
  $DB_SQL.= 'GROUP BY livret_page_ref, livret_epi_theme_code ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * tester_epi_theme
 *
 * @param string $theme_code
 * @return int
 */
public static function DB_tester_epi_theme( $theme_code )
{
  $DB_SQL = 'SELECT 1 ';
  $DB_SQL.= 'FROM sacoche_livret_epi_theme ';
  $DB_SQL.= 'WHERE livret_epi_theme_code=:theme_code ';
  $DB_VAR = array( ':theme_code' => $theme_code );
  return (int)DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * tester_epi
 *
 * @param string $theme_code
 * @param string $page_ref
 * @param int    $groupe_id
 * @param int    $epi_id   inutile si recherche pour un ajout, mais id à éviter si recherche pour une modification
 * @return int
 */
public static function DB_tester_epi( $theme_code , $page_ref , $groupe_id , $epi_id=FALSE )
{
  $DB_SQL = 'SELECT livret_epi_id ';
  $DB_SQL.= 'FROM sacoche_livret_epi ';
  $DB_SQL.= 'WHERE livret_epi_theme_code=:theme_code AND livret_page_ref=:page_ref AND groupe_id=:groupe_id ';
  $DB_SQL.= ($epi_id) ? 'AND livret_epi_id!=:epi_id ' : '' ;
  $DB_SQL.= 'LIMIT 1'; // utile
  $DB_VAR = array(
    ':theme_code' => $theme_code,
    ':page_ref'   => $page_ref,
    ':groupe_id'  => $groupe_id,
    ':epi_id'     => $epi_id,
  );
  return (int)DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * ajouter_epi
 *
 * @param string $theme_code
 * @param string $page_ref
 * @param int    $groupe_id
 * @param string $epi_titre
 * @return int
 */
public static function DB_ajouter_epi( $theme_code , $page_ref , $groupe_id , $epi_titre )
{
  $DB_SQL = 'INSERT INTO sacoche_livret_epi( livret_epi_theme_code,livret_page_ref, groupe_id,livret_epi_titre) ';
  $DB_SQL.= 'VALUES                        (           :theme_code,      :page_ref,:groupe_id,      :epi_titre)';
  $DB_VAR = array(
    ':theme_code' => $theme_code,
    ':page_ref'   => $page_ref,
    ':groupe_id'  => $groupe_id,
    ':epi_titre'  => $epi_titre,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return DB::getLastOid(SACOCHE_STRUCTURE_BD_NAME);
}

/**
 * ajouter_epi_jointure
 *
 * @param int    $epi_id
 * @param int    $matiere_id
 * @param int    $prof_id
 * @return void
 */
public static function DB_ajouter_epi_jointure( $epi_id , $matiere_id , $prof_id )
{
  $DB_SQL = 'INSERT INTO sacoche_livret_jointure_epi_prof( livret_epi_id, matiere_id, prof_id) ';
  $DB_SQL.= 'VALUES                                      (       :epi_id,:matiere_id,:prof_id)';
  $DB_VAR = array(
    ':epi_id'     => $epi_id,
    ':matiere_id' => $matiere_id,
    ':prof_id'    => $prof_id,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_epi
 *
 * @param int    $epi_id
 * @param string $theme_code
 * @param string $page_ref
 * @param int    $groupe_id
 * @param string $epi_titre
 * @return void
 */
public static function DB_modifier_epi( $epi_id , $theme_code , $page_ref , $groupe_id , $epi_titre )
{
  $DB_SQL = 'UPDATE sacoche_livret_epi ';
  $DB_SQL.= 'SET livret_epi_theme_code=:theme_code, livret_page_ref=:page_ref, groupe_id=:groupe_id, livret_epi_titre=:epi_titre ';
  $DB_SQL.= 'WHERE livret_epi_id=:epi_id ';
  $DB_VAR = array(
    ':epi_id'     => $epi_id,
    ':theme_code' => $theme_code,
    ':page_ref'   => $page_ref,
    ':groupe_id'  => $groupe_id,
    ':epi_titre'  => $epi_titre,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * supprimer_epi
 *
 * @param int    $epi_id
 * @return void
 */
public static function DB_supprimer_epi( $epi_id )
{
  $DB_SQL = 'DELETE sacoche_livret_epi, sacoche_livret_jointure_epi_prof ';
  $DB_SQL.= 'FROM sacoche_livret_epi ';
  $DB_SQL.= 'LEFT JOIN sacoche_livret_jointure_epi_prof USING (livret_epi_id) ';
  $DB_SQL.= 'WHERE livret_epi_id=:epi_id ';
  $DB_VAR = array( ':epi_id' => $epi_id );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * supprimer_epi_jointure
 *
 * @param int    $epi_id
 * @return void
 */
public static function DB_supprimer_epi_jointure( $epi_id )
{
  $DB_SQL = 'DELETE FROM sacoche_livret_jointure_epi_prof ';
  $DB_SQL.= 'WHERE livret_epi_id=:epi_id ';
  $DB_VAR = array( ':epi_id' => $epi_id );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// AP
// ////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * lister_ap
 *
 * @param void
 * @return array
 */
public static function DB_lister_ap()
{
  $DB_SQL = 'SELECT sacoche_livret_ap.*, livret_page_ordre, livret_page_moment, groupe_nom, ';
  $DB_SQL.= 'GROUP_CONCAT( DISTINCT CONCAT(matiere_id,"_",user_id) SEPARATOR " ") AS matiere_prof_id, ';
  $DB_SQL.= 'GROUP_CONCAT( DISTINCT CONCAT(matiere_nom," - ",user_nom," ",user_prenom) SEPARATOR "§BR§") AS matiere_prof_texte ';
  $DB_SQL.= 'FROM sacoche_livret_ap ';
  $DB_SQL.= 'INNER JOIN sacoche_livret_jointure_groupe USING(livret_page_ref, groupe_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_livret_jointure_ap_prof USING(livret_ap_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_livret_page USING(livret_page_ref) ';
  $DB_SQL.= 'LEFT JOIN sacoche_groupe USING(groupe_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_matiere USING(matiere_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_livret_jointure_ap_prof.prof_id = sacoche_user.user_id ';
  $DB_SQL.= 'GROUP BY livret_ap_id ';
  $DB_SQL.= 'ORDER BY livret_page_ordre ASC, groupe_nom ASC ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * compter_ap_par_page
 *
 * @param void
 * @return array
 */
public static function DB_compter_ap_par_page()
{
  $DB_SQL = 'SELECT livret_page_ref, COUNT(DISTINCT livret_ap_id) AS nombre ';
  $DB_SQL.= 'FROM sacoche_livret_ap ';
  $DB_SQL.= 'INNER JOIN sacoche_livret_jointure_groupe USING(livret_page_ref, groupe_id) ';
  $DB_SQL.= 'GROUP BY livret_page_ref ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * ajouter_ap
 *
 * @param string $page_ref
 * @param int    $groupe_id
 * @param string $ap_titre
 * @return int
 */
public static function DB_ajouter_ap( $page_ref , $groupe_id , $ap_titre )
{
  $DB_SQL = 'INSERT INTO sacoche_livret_ap( livret_page_ref, groupe_id, livret_ap_titre) ';
  $DB_SQL.= 'VALUES                       (       :page_ref,:groupe_id,       :ap_titre)';
  $DB_VAR = array(
    ':page_ref'   => $page_ref,
    ':groupe_id'  => $groupe_id,
    ':ap_titre'   => $ap_titre,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return DB::getLastOid(SACOCHE_STRUCTURE_BD_NAME);
}

/**
 * ajouter_ap_jointure
 *
 * @param int    $ap_id
 * @param int    $matiere_id
 * @param int    $prof_id
 * @return void
 */
public static function DB_ajouter_ap_jointure( $ap_id , $matiere_id , $prof_id )
{
  $DB_SQL = 'INSERT INTO sacoche_livret_jointure_ap_prof( livret_ap_id, matiere_id, prof_id) ';
  $DB_SQL.= 'VALUES                                     (       :ap_id,:matiere_id,:prof_id)';
  $DB_VAR = array(
    ':ap_id'     => $ap_id,
    ':matiere_id' => $matiere_id,
    ':prof_id'    => $prof_id,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_ap
 *
 * @param int    $ap_id
 * @param string $page_ref
 * @param int    $groupe_id
 * @param string $ap_titre
 * @return void
 */
public static function DB_modifier_ap( $ap_id , $page_ref , $groupe_id , $ap_titre )
{
  $DB_SQL = 'UPDATE sacoche_livret_ap ';
  $DB_SQL.= 'SET livret_page_ref=:page_ref, groupe_id=:groupe_id, livret_ap_titre=:ap_titre ';
  $DB_SQL.= 'WHERE livret_ap_id=:ap_id ';
  $DB_VAR = array(
    ':ap_id'     => $ap_id,
    ':page_ref'  => $page_ref,
    ':groupe_id' => $groupe_id,
    ':ap_titre'  => $ap_titre,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * supprimer_ap
 *
 * @param int    $ap_id
 * @return void
 */
public static function DB_supprimer_ap( $ap_id )
{
  $DB_SQL = 'DELETE sacoche_livret_ap, sacoche_livret_jointure_ap_prof ';
  $DB_SQL.= 'FROM sacoche_livret_ap ';
  $DB_SQL.= 'LEFT JOIN sacoche_livret_jointure_ap_prof USING (livret_ap_id) ';
  $DB_SQL.= 'WHERE livret_ap_id=:ap_id ';
  $DB_VAR = array( ':ap_id' => $ap_id );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * supprimer_ap_jointure
 *
 * @param int    $ap_id
 * @return void
 */
public static function DB_supprimer_ap_jointure( $ap_id )
{
  $DB_SQL = 'DELETE FROM sacoche_livret_jointure_ap_prof ';
  $DB_SQL.= 'WHERE livret_ap_id=:ap_id ';
  $DB_VAR = array( ':ap_id' => $ap_id );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Parcours
// ////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * lister_parcours_type
 *
 * @param void
 * @return array
 */
public static function DB_lister_parcours_type()
{
  $DB_SQL = 'SELECT sacoche_livret_parcours_type.* ';
  $DB_SQL.= 'FROM sacoche_livret_parcours_type ';
  $DB_SQL.= 'ORDER BY livret_parcours_type_nom ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL, TRUE);
}

/**
 * lister_parcours
 *
 * @param string $parcours_code
 * @return array
 */
public static function DB_lister_parcours($parcours_code)
{
  $DB_SQL = 'SELECT sacoche_livret_parcours.*, livret_page_ordre, livret_page_moment, ';
  $DB_SQL.= 'groupe_nom, user_nom AS prof_nom, user_prenom AS prof_prenom ';
  $DB_SQL.= 'FROM sacoche_livret_parcours ';
  $DB_SQL.= 'INNER JOIN sacoche_livret_jointure_groupe USING(livret_page_ref, groupe_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_livret_page USING(livret_page_ref) ';
  $DB_SQL.= 'LEFT JOIN sacoche_groupe USING(groupe_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_livret_parcours.prof_id = sacoche_user.user_id ';
  $DB_SQL.= 'WHERE livret_parcours_type_code=:parcours_code ';
  $DB_SQL.= 'GROUP BY livret_page_ref, groupe_id, prof_id ';
  $DB_SQL.= 'ORDER BY livret_page_ordre ASC, groupe_nom ASC ';
  $DB_VAR = array( ':parcours_code' => $parcours_code );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * compter_parcours_par_page
 *
 * @param void
 * @return array
 */
public static function DB_compter_parcours_par_page()
{
  $DB_SQL = 'SELECT livret_page_ref, livret_parcours_type_code, COUNT(livret_parcours_id) AS nombre ';
  $DB_SQL.= 'FROM sacoche_livret_parcours ';
  $DB_SQL.= 'INNER JOIN sacoche_livret_jointure_groupe USING(livret_page_ref, groupe_id) ';
  $DB_SQL.= 'GROUP BY livret_page_ref, livret_parcours_type_code ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * tester_parcours
 *
 * Remarque : Il n'y a qu'un parcours par classe, on ne teste pas l'enseignant.
 *
 * @param string $parcours_code
 * @param string $page_ref
 * @param int    $groupe_id
 * @param int    $matiere_id
 * @param int    $parcours_id   inutile si recherche pour un ajout, mais id à éviter si recherche pour une modification
 * @return int
 */
public static function DB_tester_parcours( $parcours_code , $page_ref , $groupe_id , $parcours_id=FALSE )
{
  $DB_SQL = 'SELECT livret_parcours_id ';
  $DB_SQL.= 'FROM sacoche_livret_parcours ';
  $DB_SQL.= 'WHERE livret_parcours_type_code=:parcours_code AND livret_page_ref=:page_ref AND groupe_id=:groupe_id ';
  $DB_SQL.= ($parcours_id) ? 'AND livret_parcours_id!=:parcours_id ' : '' ;
  $DB_SQL.= 'LIMIT 1'; // utile
  $DB_VAR = array(
    ':parcours_code' => $parcours_code,
    ':page_ref'      => $page_ref,
    ':groupe_id'     => $groupe_id,
    ':parcours_id'   => $parcours_id,
  );
  return (int)DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * ajouter_parcours
 *
 * @param string $parcours_code
 * @param string $page_ref
 * @param int    $groupe_id
 * @param int    $prof_id
 * @return int
 */
public static function DB_ajouter_parcours( $parcours_code , $page_ref , $groupe_id , $prof_id )
{
  $DB_SQL = 'INSERT INTO sacoche_livret_parcours( livret_parcours_type_code, livret_page_ref, groupe_id, prof_id) ';
  $DB_SQL.= 'VALUES                             (            :parcours_code,       :page_ref,:groupe_id,:prof_id)';
  $DB_VAR = array(
    ':parcours_code' => $parcours_code,
    ':page_ref'      => $page_ref,
    ':groupe_id'     => $groupe_id,
    ':prof_id'       => $prof_id,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return DB::getLastOid(SACOCHE_STRUCTURE_BD_NAME);
}

/**
 * modifier_parcours
 *
 * @param int    $parcours_id
 * @param string $parcours_code
 * @param string $page_ref
 * @param int    $groupe_id
 * @param int    $prof_id
 * @return void
 */
public static function DB_modifier_parcours( $parcours_id , $parcours_code , $page_ref , $groupe_id , $prof_id )
{
  $DB_SQL = 'UPDATE sacoche_livret_parcours ';
  $DB_SQL.= 'SET livret_parcours_type_code=:parcours_code, livret_page_ref=:page_ref, groupe_id=:groupe_id, prof_id=:prof_id ';
  $DB_SQL.= 'WHERE livret_parcours_id=:parcours_id ';
  $DB_VAR = array(
    ':parcours_id'   => $parcours_id,
    ':parcours_code' => $parcours_code,
    ':page_ref'      => $page_ref,
    ':groupe_id'     => $groupe_id,
    ':prof_id'       => $prof_id,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * supprimer_parcours
 *
 * @param int    $parcours_id
 * @return void
 */
public static function DB_supprimer_parcours( $parcours_id )
{
  $DB_SQL = 'DELETE FROM sacoche_livret_parcours ';
  $DB_SQL.= 'WHERE livret_parcours_id=:parcours_id ';
  $DB_VAR = array( ':parcours_id' => $parcours_id );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Modalités d'accompagnement
// ////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * tester_modaccomp
 *
 * @param string $modaccomp_code
 * @return bool
 */
public static function DB_tester_modaccomp( $modaccomp_code )
{
  $DB_SQL = 'SELECT 1 ';
  $DB_SQL.= 'FROM sacoche_livret_modaccomp ';
  $DB_SQL.= 'WHERE livret_modaccomp_code=:modaccomp_code ';
  $DB_VAR = array( ':modaccomp_code' => $modaccomp_code );
  return (bool) DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_eleve_modaccomp
 *
 * @param void
 * @return array
 */
public static function DB_lister_eleve_modaccomp()
{
  $DB_SQL = 'SELECT user_id, user_nom, user_prenom, groupe_nom, livret_modaccomp_code, info_complement ';
  $DB_SQL.= 'FROM sacoche_livret_jointure_modaccomp_eleve ';
  $DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_livret_jointure_modaccomp_eleve.eleve_id = sacoche_user.user_id ';
  $DB_SQL.= 'LEFT JOIN sacoche_groupe ON sacoche_user.eleve_classe_id = sacoche_groupe.groupe_id ';
  $DB_SQL.= 'WHERE user_sortie_date>NOW() AND groupe_id IS NOT NULL ';
  $DB_SQL.= 'ORDER BY groupe_nom ASC, user_nom ASC, user_prenom ASC, livret_modaccomp_code ASC ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * modifier_eleve_modaccomp
 *
 * @param string $modaccomp_code
 * @param int    $eleve_id
 * @param string $info_complement
 * @return void
 */
public static function DB_modifier_eleve_modaccomp( $modaccomp_code , $eleve_id , $info_complement )
{
  $DB_SQL = 'INSERT INTO sacoche_livret_jointure_modaccomp_eleve ( livret_modaccomp_code , eleve_id , info_complement) ';
  $DB_SQL.= 'VALUES                                              (       :modaccomp_code ,:eleve_id ,:info_complement)';
  $DB_SQL.= 'ON DUPLICATE KEY UPDATE info_complement=:info_complement ';
  $DB_VAR = array(
    ':modaccomp_code'  => $modaccomp_code,
    ':eleve_id'        => $eleve_id,
    ':info_complement' => $info_complement,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * supprimer_eleve_modaccomp
 *
 * @param string $modaccomp_code
 * @param int    $eleve_id
 * @return void
 */
public static function DB_supprimer_eleve_modaccomp( $modaccomp_code , $eleve_id )
{
  $DB_SQL = 'DELETE FROM sacoche_livret_jointure_modaccomp_eleve ';
  $DB_SQL.= 'WHERE livret_modaccomp_code=:modaccomp_code AND eleve_id=:eleve_id ';
  $DB_VAR = array(
    ':modaccomp_code' => $modaccomp_code,
    ':eleve_id'       => $eleve_id,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Options de formulaires
// ////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Retourner un tableau [valeur texte] des modalités d'accompagnement
 *
 * @param void
 * @return array
 */
public static function DB_OPT_modaccomp()
{
  $DB_SQL = 'SELECT livret_modaccomp_code AS valeur, CONCAT( livret_modaccomp_code ," : " , livret_modaccomp_nom ) AS texte ';
  $DB_SQL.= 'FROM sacoche_livret_modaccomp ';
  $DB_SQL.= 'ORDER BY livret_modaccomp_code ASC ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Retourner un tableau [valeur texte] des classes associées à une page du livret
 *
 * @param string   $page_ref
 * @return array
 */
public static function DB_OPT_groupes_for_page( $page_ref )
{
  $DB_SQL = 'SELECT groupe_id AS valeur, groupe_nom AS texte ';
  $DB_SQL.= 'FROM sacoche_livret_jointure_groupe ';
  $DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING(niveau_id) ';
  $DB_SQL.= 'WHERE livret_page_ref=:page_ref ';
  $DB_SQL.= 'GROUP BY groupe_id ';
  $DB_SQL.= 'ORDER BY niveau_id ASC, groupe_nom ASC ';
  $DB_VAR = array( ':page_ref' => $page_ref );
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return !empty($DB_TAB) ? $DB_TAB : 'Aucune classe trouvée.' ;
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Nettoyage
// ////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * supprimer_jointure_referentiel_obsolete
 *
 * Lors de la suppression de référentiels ou d'éléments de référentiels, la table sacoche_livret_jointure_referentiel n'est pas nettoyée car c'est un peu pénible, on le fait donc ici.
 *
 * @param void
 * @return void
 */
public static function DB_supprimer_jointure_referentiel_obsolete()
{
  // Les liaisons aux éléments de référentiels
  $tab_element = array(
    'matiere' => 'sacoche_matiere',
    'domaine' => 'sacoche_referentiel_domaine',
    'theme'   => 'sacoche_referentiel_theme',
    'item'    => 'sacoche_referentiel_item',
    'user'    => "sacoche_user",
  );
  foreach($tab_element as $element => $table)
  {
    $DB_SQL = 'DELETE sacoche_livret_jointure_referentiel ';
    $DB_SQL.= 'FROM sacoche_livret_jointure_referentiel ';
    $DB_SQL.= 'LEFT JOIN sacoche_livret_page ON sacoche_livret_jointure_referentiel.livret_rubrique_type = sacoche_livret_page.livret_page_rubrique_type ';
    $DB_SQL.= 'LEFT JOIN '.$table.' ON sacoche_livret_jointure_referentiel.element_id = '.$table.'.'.$element.'_id ';
    $DB_SQL.= 'WHERE livret_page_rubrique_join="'.$element.'" AND '.$table.'.'.$element.'_id IS NULL ';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  }
  // Les liaisons aux matières du Livret Scolaire
  $DB_SQL = 'DELETE sacoche_livret_jointure_referentiel ';
  $DB_SQL.= 'FROM sacoche_livret_jointure_referentiel ';
  $DB_SQL.= 'LEFT JOIN sacoche_livret_matiere ON sacoche_livret_jointure_referentiel.livret_rubrique_ou_matiere_id = sacoche_livret_matiere.livret_matiere_id ';
  $DB_SQL.= 'WHERE livret_rubrique_type IN ("c3_matiere","c4_matiere") AND sacoche_livret_matiere.livret_matiere_id IS NULL ';
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * supprimer_bilans_officiels
 *
 * @param void
 * @return void
 */
public static function DB_vider_livret()
{
  DB::query(SACOCHE_STRUCTURE_BD_NAME , 'TRUNCATE sacoche_livret_ap'       , NULL);
  DB::query(SACOCHE_STRUCTURE_BD_NAME , 'TRUNCATE sacoche_livret_epi'      , NULL);
  DB::query(SACOCHE_STRUCTURE_BD_NAME , 'TRUNCATE sacoche_livret_parcours' , NULL);
  DB::query(SACOCHE_STRUCTURE_BD_NAME , 'TRUNCATE sacoche_livret_jointure_epi_prof'        , NULL);
  DB::query(SACOCHE_STRUCTURE_BD_NAME , 'TRUNCATE sacoche_livret_jointure_modaccomp_eleve' , NULL);
  // TODO : A AJOUTER LE MOMENT VENU...
  // DB::query(SACOCHE_STRUCTURE_BD_NAME , 'TRUNCATE sacoche_livret_saisie' , NULL);
}

}
?>