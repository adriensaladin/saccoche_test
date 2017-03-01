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

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2016-12-31 => 2017-01-11
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2016-12-31')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2017-01-11';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // nouvelle table [sacoche_jointure_user_module]
    $reload_sacoche_jointure_user_module = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_jointure_user_module.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2017-01-11 => 2017-01-18
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2017-01-11')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2017-01-18';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // prise en compte d'un problème se produisant en cas de saisie manuelle d'éléments de programme travaillés avec plusieurs saut de lignes consécutifs intermédiaires.
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_livret_export SET export_contenu = REPLACE( export_contenu , ",\"ELd41d8cd98f00b204e9800998ecf8427e\":\"\"" , "" ) ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_livret_saisie SET saisie_valeur = REPLACE( saisie_valeur , ",\"\":5," , "," ) WHERE saisie_objet="elements" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_livret_saisie SET saisie_valeur = REPLACE( saisie_valeur , ",\"\":4," , "," ) WHERE saisie_objet="elements" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_livret_saisie SET saisie_valeur = REPLACE( saisie_valeur , ",\"\":3," , "," ) WHERE saisie_objet="elements" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_livret_saisie SET saisie_valeur = REPLACE( saisie_valeur , ",\"\":2," , "," ) WHERE saisie_objet="elements" ' );
    // correction d'un positionnement modifié sur certaines installations suite à un bug
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_livret_page SET livret_page_colonne = "maitrise" WHERE livret_page_periodicite="cycle" AND livret_page_ref != "cycle1" ' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2017-01-18 => 2017-01-22
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2017-01-18')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2017-01-22';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // nouvelle table [sacoche_livret_enscompl]
    $reload_sacoche_livret_enscompl = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_enscompl.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // nouvelle table [sacoche_livret_jointure_enscompl_eleve]
    $reload_sacoche_livret_jointure_enscompl_eleve = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_jointure_enscompl_eleve.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // erreur dans la définition d'une colonne de la table [sacoche_livret_jointure_referentiel]
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_livret_jointure_referentiel CHANGE livret_rubrique_ou_matiere_id livret_rubrique_ou_matiere_id SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0 ' );
    // retrait d'enregistrement d'infos de saisies de fin de cycle dans le livret scolaire au cas où (alors que l'accès n'était pas encore opérationnel)
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE sacoche_livret_saisie, sacoche_livret_saisie_jointure_prof, sacoche_livret_saisie_memo_detail FROM sacoche_livret_saisie LEFT JOIN sacoche_livret_saisie_jointure_prof USING (livret_saisie_id) LEFT JOIN sacoche_livret_saisie_memo_detail USING (livret_saisie_id) WHERE livret_page_periodicite="cycle" ' );
    // retrait éventuel de scories d'utilisateurs supprimés dans les saisies du Livret Scolaire
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE sacoche_livret_saisie, sacoche_livret_saisie_memo_detail, sacoche_livret_saisie_jointure_prof FROM sacoche_livret_saisie LEFT JOIN sacoche_livret_saisie_memo_detail USING (livret_saisie_id) LEFT JOIN sacoche_livret_saisie_jointure_prof USING (livret_saisie_id) LEFT JOIN sacoche_user ON sacoche_livret_saisie.prof_id=sacoche_user.user_id WHERE sacoche_livret_saisie.prof_id!=0 AND sacoche_user.user_id IS NULL' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE sacoche_livret_saisie_jointure_prof FROM sacoche_livret_saisie_jointure_prof LEFT JOIN sacoche_user ON sacoche_livret_saisie_jointure_prof.prof_id=sacoche_user.user_id WHERE sacoche_user.user_id IS NULL' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE sacoche_livret_saisie, sacoche_livret_saisie_memo_detail, sacoche_livret_saisie_jointure_prof FROM sacoche_livret_saisie LEFT JOIN sacoche_livret_saisie_memo_detail USING (livret_saisie_id) LEFT JOIN sacoche_livret_saisie_jointure_prof USING (livret_saisie_id) LEFT JOIN sacoche_user ON sacoche_livret_saisie.cible_id=sacoche_user.user_id WHERE cible_nature="eleve" AND sacoche_user.user_id IS NULL' );
    // ajout de paramètre
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("droit_officiel_livret_positionner_socle" , "DIR,ENS")' );
    // réordonner la table sacoche_parametre (ligne à déplacer vers la dernière MAJ lors d'ajout dans sacoche_parametre)
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_parametre ORDER BY parametre_nom' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2017-01-22 => 2017-02-10
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2017-01-22')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2017-02-10';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // modif légère format exports LSU archivés
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_livret_export SET export_contenu = REPLACE( export_contenu , "\"bilan\":{" , "\"bilan\":{\"type\":\"periode\"," ) ' );
    // nettoyage incomplet d'une révision précédente
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_livret_export SET export_contenu = REPLACE( export_contenu , ",\"ELd41d8cd98f00b204e9800998ecf8427e\"," , "," ) ' );
    // modif colonne de la table [sacoche_livret_jointure_groupe]
    if(empty($reload_sacoche_livret_jointure_groupe))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_livret_jointure_groupe CHANGE jointure_date_verrou jointure_date_verrou DATETIME NULL DEFAULT NULL ' );
    }
    // recharger [sacoche_livret_rubrique] (modifs de codes et d'indices de positionnement ou d'éléments travaillés)
    if(empty($reload_sacoche_livret_rubrique))
    {
      $reload_sacoche_livret_rubrique = TRUE;
      $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_rubrique.sql');
      DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
      DB::close(SACOCHE_STRUCTURE_BD_NAME);
    }
    // champ trouvé à "0000-00-00" au lieu de NULL sur une base
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_user SET user_naissance_date = NULL WHERE user_naissance_date < "1000-01-01" ' ); // Le test ="0000-00-00" plante sur une config MySQL stricte.
  }
}

?>
