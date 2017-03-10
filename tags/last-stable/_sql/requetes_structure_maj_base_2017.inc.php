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

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2017-02-10 => 2017-03-01
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2017-02-10')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2017-03-01';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout d'une colonne à [sacoche_referentiel]
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_referentiel ADD referentiel_mode_livret ENUM("domaine","theme","item") CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "domaine" AFTER referentiel_mode_synthese' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel SET referentiel_mode_livret = "theme" WHERE referentiel_mode_synthese = "theme" ' );
    // ajout clefs à [sacoche_livret_saisie]
    if(empty($reload_sacoche_livret_saisie))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_livret_saisie ADD INDEX rubrique (rubrique_type, rubrique_id) ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_livret_saisie ADD INDEX cible (cible_nature, cible_id) ' );
    }
    // ajout de paramètres
    $droit_gerer_referentiel = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="droit_gerer_referentiel"' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("droit_gerer_mode_synthese"    , "'.$droit_gerer_referentiel.'")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("droit_gerer_livret_elements"  , "'.$droit_gerer_referentiel.'")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("droit_gerer_livret_epi"       , "ENS,DOC,EDU,ONLY_PP")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("droit_gerer_livret_ap"        , "ENS,DOC,EDU,ONLY_PP")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("droit_gerer_livret_parcours"  , "ENS,DOC,EDU,ONLY_PP")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("droit_gerer_livret_modaccomp" , "ENS,DOC,EDU,ONLY_PP")' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("droit_gerer_livret_enscompl"  , "")' );
    // ceux-ci ont été oubliés dans le remplissage de la table lors de maj précédentes !
    $droit_socle_proposition_positionnement = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="droit_socle_proposition_positionnement"' );
    if(is_null($droit_socle_proposition_positionnement))
    {
      $droit_socle_pourcentage_acquis = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="droit_socle_pourcentage_acquis"' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("droit_socle_proposition_positionnement" , "'.$droit_socle_pourcentage_acquis.'")' );
    }
    $droit_voir_score_maitrise = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="droit_voir_score_maitrise"' );
    if(is_null($droit_voir_score_maitrise))
    {
      $droit = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom = "droit_voir_score_bilan" ');
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("droit_voir_score_maitrise" , "'.$droit.'")' );
    }
    $droit_socle_prevision_points_brevet = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="droit_socle_prevision_points_brevet"' );
    if(is_null($droit_socle_prevision_points_brevet))
    {
      $droit = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom = "droit_socle_proposition_positionnement" ');
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("droit_socle_prevision_points_brevet" , "'.$droit.'")' );
    }
    $droit_officiel_livret_positionner_socle = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="droit_officiel_livret_positionner_socle"' );
    if(is_null($droit_officiel_livret_positionner_socle))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("droit_officiel_livret_positionner_socle" , "DIR,ENS")' );
    }
    // réordonner la table sacoche_parametre (ligne à déplacer vers la dernière MAJ lors d'ajout dans sacoche_parametre)
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_parametre ORDER BY parametre_nom' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2017-03-01 => 2017-03-06
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2017-03-01')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2017-03-06';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // STSWeb 11.1.0 de janvier 2017 : passage du code groupe à 20 caractères + ajout d'un nouveau type de personnel "dir" et modification du format des nom d'usage et patronymique et du prénom
    // SIÈCLE BEE 17.2.1.0 du 15/05/2017 : agrandissement des lignes d'adresse des élèves et des responsables de 32 caractères à 38 caractères. -> RAS car déjà à 50 dans SACoche
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_groupe CHANGE groupe_ref groupe_ref VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "Passage de 8 à 20 caractères pour les groupes dans SIÈCLE BEE (mais pas pour les classes)." ' );
    // correction d'un positionnement modifié sur certaines installations suite à un bug
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_livret_page SET livret_page_colonne = "reussite" WHERE livret_page_ref = "cycle1" ' );
    // ajout d'une ligne à [sacoche_siecle_import]
    if(empty($reload_sacoche_siecle_import))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_siecle_import VALUES ("Onde" , NULL, NULL, "") ' );
    }
    // ajout de colonne oublié dans la définition de la table lors d'une maj précédente !
    $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , 'SHOW COLUMNS FROM sacoche_referentiel LIKE "referentiel_mode_livret" ');
    if(empty($DB_TAB))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_referentiel ADD referentiel_mode_livret ENUM("domaine","theme","item") CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "domaine" AFTER referentiel_mode_synthese' );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// NE PAS OUBLIER de modifier aussi le nécessaire dans ./_sql/structure/ en fonction des évolutions !!!
// ////////////////////////////////////////////////////////////////////////////////////////////////////

?>
