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
// MAJ 2017-03-06 => 2017-03-15
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2017-03-06')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2017-03-15';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // nouvelle table [sacoche_livret_element_cycle]
    $reload_sacoche_livret_element_cycle = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_element_cycle.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // nouvelle table [sacoche_livret_element_domaine]
    $reload_sacoche_livret_element_domaine = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_element_domaine.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // nouvelle table [sacoche_livret_element_niveau]
    $reload_sacoche_livret_element_niveau = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_element_niveau.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // nouvelle table [sacoche_livret_element_theme]
    $reload_sacoche_livret_element_theme = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_element_theme.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // nouvelle table [sacoche_livret_element_item]
    $reload_sacoche_livret_element_item = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_element_item.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // ajout de paramètres
    $officiel_bulletin_appreciation_rubrique_longueur = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="officiel_bulletin_appreciation_rubrique_longueur"' );
    $officiel_bulletin_appreciation_rubrique_longueur = min( max( $officiel_bulletin_appreciation_rubrique_longueur , 300 ) , 600 );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("officiel_livret_appreciation_rubrique_longueur" , "'.$officiel_bulletin_appreciation_rubrique_longueur.'")' );
    $officiel_bulletin_appreciation_generale_longueur = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="officiel_bulletin_appreciation_generale_longueur"' );
    $officiel_bulletin_appreciation_generale_longueur = max( $officiel_bulletin_appreciation_generale_longueur , 300 );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ("officiel_livret_appreciation_generale_longueur" , "'.$officiel_bulletin_appreciation_generale_longueur.'")' );
    // réordonner la table sacoche_parametre (ligne à déplacer vers la dernière MAJ lors d'ajout dans sacoche_parametre)
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_parametre ORDER BY parametre_nom' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2017-03-15 => 2017-03-23
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2017-03-15')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2017-03-23';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout d'une colonne à sacoche_officiel_saisie en espérant arriver un jour à l'exploiter...
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_officiel_saisie ADD groupe_id MEDIUMINT UNSIGNED NOT NULL DEFAULT 0 COMMENT "pour une appréciation sur un groupe, précise le groupe" AFTER eleve_ou_classe_id , ADD INDEX ( groupe_id ) ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_officiel_saisie DROP PRIMARY KEY, ADD PRIMARY KEY (eleve_ou_classe_id, groupe_id, officiel_type, periode_id, rubrique_id, prof_id, saisie_type)  ' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2017-03-23 => 2017-03-30
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2017-03-23')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2017-03-30';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // nouvelle table [sacoche_livret_jointure_parcours_prof]
    $reload_sacoche_livret_jointure_parcours_prof = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_jointure_parcours_prof.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // suppression d'un champ de [sacoche_livret_parcours] + déplacement du nom du prof de [sacoche_livret_parcours] vers [sacoche_livret_jointure_parcours_prof]
    if(empty($reload_sacoche_livret_parcours))
    {
      $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , 'SELECT livret_parcours_id, prof_id FROM sacoche_livret_parcours');
      foreach($DB_TAB as $DB_ROW)
      {
        DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_livret_jointure_parcours_prof VALUES ( '.$DB_ROW['livret_parcours_id'].' , '.$DB_ROW['prof_id'].' )' );
      }
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_livret_parcours DROP INDEX prof_id ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_livret_parcours DROP prof_id ' );
    }
    if(empty($reload_sacoche_matiere_famille))
    {
      // Ajout de familles de matières
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT sacoche_matiere_famille VALUES ( 96, 4, "Spécialités de baccalauréat professionnel (suite)") ');
      // réordonner la table sacoche_matiere_famille (ligne à déplacer vers la dernière MAJ lors d'ajouts dans sacoche_matiere_famille)
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_matiere_famille ORDER BY matiere_famille_id' );
    }
    if(empty($reload_sacoche_matiere))
    {
      // Matières renommées
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_nom = "Indonésien-malais" WHERE matiere_id = 9342 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_nom = "Peul"              WHERE matiere_id = 9344 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "BPAMS", matiere_nom = "Artisanat et métiers d\'art - option mét. enseigne signalétique" WHERE matiere_id = 9713 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "BPTDM", matiere_nom = "Traitements des matériaux"                                       WHERE matiere_id = 9784 ' );
      // Ajout de matières
      $insert = '
        ( 237, 0, 0, 0,   2, 0, 255, "023700", "LCUCO", "Langue, culture et communication"),
        ( 743, 0, 0, 0,   7, 0, 255, "074300", "TECNO", "Technologies"),
        (3473, 0, 0, 0,  34, 0, 255, "347300", "VDECO", "Vente et développement commercial"),
        (4167, 0, 0, 0,  41, 0, 255, "416700", "RCLSI", "Relation client sinistres"),
        (4212, 0, 0, 0,  42, 0, 255, "421200", "CUPRA", "Culture professionnelle appliquée"),
        (4213, 0, 0, 0,  42, 0, 255, "421300", "GESIN", "Gestion des sinistres"),
        (4704, 0, 0, 0,  47, 0, 255, "470400", "PAREX", "Parcours d\'excellence"),
        (9601, 0, 0, 0,  96, 0, 255,       "", "BPAAV", "Aéronautique - option avionique"),
        (9602, 0, 0, 0,  96, 0, 255,       "", "BPAST", "Aéronautique - option structure"),
        (9603, 0, 0, 0,  96, 0, 255,       "", "BPASY", "Aéronautique - option systèmes"),
        (9604, 0, 0, 0,  96, 0, 255,       "", "BPERA", "Étude et réalisation d\'agencement"),
        (9605, 0, 0, 0,  96, 0, 255,       "", "BPMCS", "Métiers du cuir option sellerie garnissage"),
        (9606, 0, 0, 0,  96, 0, 255,       "", "BPMAP", "Métiers et arts de la pierre"),
        (9607, 0, 0, 0,  96, 0, 255,       "", "BPAMV", "Artisanat et métiers d\'art - option verrerie scient. et techn."),
        (9608, 0, 0, 0,  96, 0, 255,       "", "BPEEC", "Métiers de l\'électricité et de ses environnements connectés"),
        (9609, 0, 0, 0,  96, 0, 255,       "", "BPSNA", "Systèmes numériques A - Sûreté et sécu. infra., hab. et tert."),
        (9610, 0, 0, 0,  96, 0, 255,       "", "BPSNB", "Systèmes numériques B - Audiovisuels, réseau et équip. domest."),
        (9611, 0, 0, 0,  96, 0, 255,       "", "BPSNC", "Systèmes numériques C - Réseaux inform. et syst. communicants"),
        (9612, 0, 0, 0,  96, 0, 255,       "", "BPTAO", "Technicien en appareillage orthopédique") ';
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES '.$insert );
      // Déplacement de l'EIST
      $id_avant = 600;
      $id_apres = 4802;
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_id = '.$id_apres.', matiere_usuelle = 1, matiere_famille_id = 48, matiere_code = "480200" WHERE matiere_id = '.$id_avant.' ' );
      DB_STRUCTURE_MATIERE::DB_deplacer_referentiel_matiere($id_avant,$id_apres);
      SACocheLog::ajouter('Déplacement des référentiels d\'une matière ('.$id_avant.' to '.$id_apres.').');
      // réordonner la table sacoche_matiere (ligne à déplacer vers la dernière MAJ lors d'ajout dans sacoche_matiere)
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_matiere ORDER BY matiere_id' );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2017-03-30 => 2017-04-03
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2017-03-30')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2017-04-03';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // oubli de modif de table suite à la maj 2016-09-23
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel SET referentiel_calcul_methode="frequencemax" WHERE referentiel_calcul_methode=""' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_referentiel CHANGE referentiel_calcul_methode referentiel_calcul_methode ENUM("geometrique","arithmetique","classique","bestof1","bestof2","bestof3","frequencemin","frequencemax") CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "geometrique" COMMENT "Coefficients en progression géométrique, arithmetique, ou moyenne classique non pondérée, ou conservation des meilleurs scores, ou de la plus fréquente. Valeur surclassant la configuration par défaut." ' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2017-04-03 => 2017-04-18
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2017-04-03')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2017-04-18';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    if(empty($reload_sacoche_livret_export))
    {
      // Modification de clef après retrait de doublons qui pourraient poser pb
      $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , 'SELECT user_id,livret_page_periodicite,jointure_periode, COUNT(*) AS nombre FROM sacoche_livret_export GROUP BY user_id,livret_page_periodicite,jointure_periode HAVING nombre>1');
      foreach($DB_TAB as $DB_ROW)
      {
        DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_livret_export WHERE user_id='.$DB_ROW['user_id'].' AND livret_page_periodicite="'.$DB_ROW['livret_page_periodicite'].'" AND jointure_periode="'.$DB_ROW['jointure_periode'].'" ' );
      }
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_livret_export DROP INDEX export_id ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_livret_export ADD UNIQUE export_id ( user_id , livret_page_periodicite , jointure_periode )' );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2017-04-18 => 2017-04-29
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2017-04-18')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2017-04-29';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // Ajout de familles de niveaux
    if(empty($reload_sacoche_niveau_famille))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT sacoche_niveau_famille VALUES (900, 3,  7, "LMD (Diplôme d\'enseignement supérieur)") ');
      // réordonner la table sacoche_niveau (ligne à déplacer vers la dernière MAJ lors d'ajout dans sacoche_niveau)
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_niveau_famille ORDER BY niveau_famille_id' );
    }
    // Ajout de niveaux
    if(empty($reload_sacoche_niveau))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 900001, 0, 0, 900, 900, "L1", "", "Licence, 1ère année") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 900002, 0, 0, 900, 900, "L2", "", "Licence, 2ème année") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 900003, 0, 0, 900, 900, "L3", "", "Licence, 3ème année") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 900011, 0, 0, 900, 900, "M1", "", "Master, 1ère année") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 900012, 0, 0, 900, 900, "M2", "", "Master, 2ème année") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 900021, 0, 0, 900, 900, "D1", "", "Doctorat, 1ère année") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 900022, 0, 0, 900, 900, "D2", "", "Doctorat, 2ème année") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 900023, 0, 0, 900, 900, "D3", "", "Doctorat, 3ème année") ' );
      // réordonner la table sacoche_niveau (ligne à déplacer vers la dernière MAJ lors d'ajout dans sacoche_niveau)
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_niveau ORDER BY niveau_id' );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// NE PAS OUBLIER de modifier aussi le nécessaire dans ./_sql/structure/ en fonction des évolutions !!!
// ////////////////////////////////////////////////////////////////////////////////////////////////////

?>
