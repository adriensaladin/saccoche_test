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
// MAJ 2015-12-16 => 2016-01-11
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2015-12-16')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2016-01-11';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // renommage du champ [domaine_ref] en [domaine_code] de la table [sacoche_referentiel_domaine]
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_referentiel_domaine CHANGE domaine_ref domaine_code CHAR(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT "" ' );
    // ajout de champs [domaine_ref] [theme_ref] [item_ref] [item_abbr] pour pouvoir imposer une référence
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_referentiel_domaine ADD domaine_ref VARCHAR(3) COLLATE utf8_unicode_ci NOT NULL DEFAULT "" AFTER domaine_code' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_referentiel_theme ADD theme_ref VARCHAR(3) COLLATE utf8_unicode_ci NOT NULL DEFAULT "" AFTER theme_ordre' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_referentiel_item ADD item_ref VARCHAR(3) COLLATE utf8_unicode_ci NOT NULL DEFAULT "" AFTER item_ordre' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_referentiel_item ADD item_abbr VARCHAR(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT "" AFTER item_nom' );
    // ajout d'un paramètre
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "officiel_releve_aff_reference" , "1" )' );
    // réordonner la table sacoche_parametre (ligne à déplacer vers la dernière MAJ lors d'ajout dans sacoche_parametre)
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_parametre ORDER BY parametre_nom' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2016-01-11 => 2016-02-01
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2016-01-11')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2016-02-01';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // Pour Notanet et les fiches brevet, remplacement de "Éducation civique" par "Enseignement moral et civique"
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_brevet_epreuve SET brevet_epreuve_nom="Enseignement moral et civique", brevet_epreuve_matieres_cibles="438,414,406,6926,421,6925" WHERE brevet_serie_ref="G" AND brevet_epreuve_code=122 ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_brevet_epreuve SET brevet_epreuve_nom="Histoire-géographie Enseignement moral et civique", brevet_epreuve_matieres_cibles="438,421,6925,406,6926,414" WHERE brevet_serie_ref="P" AND brevet_epreuve_code=121 ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_brevet_epreuve SET brevet_epreuve_nom="Histoire-géographie Enseignement moral et civique", brevet_epreuve_matieres_cibles="438,421,6925,406,6926,414" WHERE brevet_serie_ref="P-Agri" AND brevet_epreuve_code=121 ' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2016-02-01 => 2016-02-27
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2016-02-01')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2016-02-27';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // Modification des champs DATE car en mode SQL strict des valeurs telles 0000-00-00 ne sont pas tolérées
    if(empty($reload_sacoche_brevet_fichier))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_brevet_fichier CHANGE fichier_date fichier_date DATE DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL." ' );
    }
    if(empty($reload_sacoche_demande))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_demande CHANGE demande_date demande_date DATE DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL." ' );
    }
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_jointure_groupe_periode CHANGE jointure_date_debut jointure_date_debut DATE DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL." ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_jointure_groupe_periode CHANGE jointure_date_fin   jointure_date_fin   DATE DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL." ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_jointure_user_entree CHANGE validation_entree_date validation_entree_date DATE DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL." ' );
    if(empty($reload_sacoche_jointure_user_pilier))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_jointure_user_pilier CHANGE validation_pilier_date validation_pilier_date DATE DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL." ' );
    }
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_devoir CHANGE devoir_date devoir_date DATE DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL." , CHANGE devoir_visible_date devoir_visible_date DATE DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL." ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_message CHANGE message_debut_date message_debut_date DATE DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL." , CHANGE message_fin_date message_fin_date DATE DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL." ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_notification CHANGE notification_date notification_date DATETIME DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL." ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_officiel_fichier CHANGE fichier_date_generation fichier_date_generation DATE DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL." ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_referentiel CHANGE referentiel_partage_date referentiel_partage_date DATE DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL." ' );
    // Pour la table sacoche_saisie , un UPDATE quand il y a plus d'un million de lignes dépasse très largement le max_execution_time de PHP
    // Solution : on reporte via plusieurs appels ajax qui seront appelés depuis la page d'accueil du compte
    $nb_notes = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT COUNT(*) FROM sacoche_saisie ' );
    if($nb_notes<100000)
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_saisie CHANGE saisie_date saisie_date DATE DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL." , CHANGE saisie_visible_date saisie_visible_date DATE DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL." ' );
    }
    else
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'a" WHERE parametre_nom="version_base_maj_complementaire" AND parametre_valeur="" ' );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2016-02-27 => 2016-03-10
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2016-02-27')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2016-03-10';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // renommage du champ [item_abbr] en [item_abrev] de la table [sacoche_referentiel_item]
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_referentiel_item CHANGE item_abbr item_abrev VARCHAR(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT "" ' );
    // nouvelle table [sacoche_socle_cycle]
    $reload_sacoche_socle_cycle = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_socle_cycle.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // nouvelle table [sacoche_socle_domaine]
    $reload_sacoche_socle_domaine = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_socle_domaine.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // nouvelle table [sacoche_socle_composante]
    $reload_sacoche_socle_composante = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_socle_composante.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // nouvelle table [sacoche_jointure_referentiel_socle]
    $reload_sacoche_jointure_referentiel_socle = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_jointure_referentiel_socle.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2016-03-10 => 2016-03-22
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2016-03-10')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2016-03-22';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // ajout de 2 composante du socle table [sacoche_socle_composante]
    if(empty($reload_sacoche_socle_composante))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_socle_composante VALUES (44, 4, 4, NULL, "Connaissances à mobiliser"), (54, 5, 4, NULL, "Connaissances à mobiliser") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_socle_composante ORDER BY socle_composante_id' );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2016-03-22 => 2016-04-17
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2016-03-22')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2016-04-17';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // retrait de 2 paramètres
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_parametre WHERE parametre_nom IN ( "officiel_archive_retrait_tampon_signature","officiel_archive_ajout_message_copie" )' );
    // ajout d'un paramètre
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_parametre VALUES ( "etablissement_chef_id" , "0" )' );
    // réordonner la table sacoche_parametre (ligne à déplacer vers la dernière MAJ lors d'ajout dans sacoche_parametre)
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_parametre ORDER BY parametre_nom' );
    // ajout de champs à la table [sacoche_user]
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user ADD eleve_lv1 TINYINT(3) UNSIGNED NOT NULL DEFAULT 100 COMMENT "Langue vivante 1 pour le livret scolaire." AFTER eleve_langue' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user ADD eleve_lv2 TINYINT(3) UNSIGNED NOT NULL DEFAULT 100 COMMENT "Langue vivante 2 pour le livret scolaire." AFTER eleve_lv1' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user ADD eleve_uai_origine CHAR(8) COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "Pour un envoi de documents officiels à l\'établissement d\'origine." AFTER eleve_lv2' );
    // ajout d'un champ à la table [sacoche_periode]
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_periode ADD periode_lsun VARCHAR(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "T1 | T2 | T3 | S1 | S2 ; période officielle utilisable pour le livret scolaire." AFTER periode_nom' );
    // nouvelle table [sacoche_officiel_archive]
    $reload_sacoche_officiel_archive = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_officiel_archive.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // nouvelle table [sacoche_officiel_archive_image]
    $reload_sacoche_officiel_archive_image = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_officiel_archive_image.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // nouvelle table [sacoche_structure_origine]
    $reload_sacoche_structure_origine = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_structure_origine.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // ajout d'un champ (matiere_code) prérempli à la table sacoche_matiere
    if(empty($reload_sacoche_matiere))
    {
      // récupération des informations sur les matières
      $DB_TAB_communes    = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , 'SELECT matiere_id, matiere_nb_demandes, matiere_ordre FROM sacoche_matiere WHERE matiere_active=1 AND matiere_id<='.ID_MATIERE_PARTAGEE_MAX);
      $DB_TAB_specifiques = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , 'SELECT * FROM sacoche_matiere WHERE matiere_id>'.ID_MATIERE_PARTAGEE_MAX);
      // rechargement de la table sacoche_matiere
      $reload_sacoche_matiere = TRUE;
      $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_matiere.sql');
      DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes ); // Attention, sur certains LCS ça bloque au dela de 40 instructions MySQL (mais un INSERT multiple avec des milliers de lignes ne pose pas de pb).
      DB::close(SACOCHE_STRUCTURE_BD_NAME);
      // on remet en place les matières partagées
      foreach($DB_TAB_communes as $DB_ROW)
      {
        DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_active=1, matiere_nb_demandes='.$DB_ROW['matiere_nb_demandes'].', matiere_ordre='.$DB_ROW['matiere_ordre'].' WHERE matiere_id='.$DB_ROW['matiere_id'] );
      }
      // on remet en place les matières spécifiques
      foreach($DB_TAB_specifiques as $DB_ROW)
      {
        $DB_SQL = 'INSERT INTO sacoche_matiere(matiere_id, matiere_active, matiere_usuelle, matiere_famille_id, matiere_nb_demandes, matiere_ordre, matiere_code, matiere_ref, matiere_nom) ';
        $DB_SQL.= 'VALUES(:matiere_id, :matiere_active, 0, 0, :matiere_nb_demandes, :matiere_ordre, 0, :matiere_ref, :matiere_nom) ';
        $DB_VAR = array(
          ':matiere_id'          => $DB_ROW['matiere_id'],
          ':matiere_active'      => $DB_ROW['matiere_active'],
          ':matiere_nb_demandes' => $DB_ROW['matiere_nb_demandes'],
          ':matiere_ordre'       => $DB_ROW['matiere_ordre'],
          ':matiere_ref'         => $DB_ROW['matiere_ref'],
          ':matiere_nom'         => $DB_ROW['matiere_nom'],
        );
        DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
      }
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2016-04-17 => 2016-04-29
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2016-04-17')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2016-04-29';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // modification d'un champ et à jout d'une clef à la table [sacoche_periode]
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_periode CHANGE periode_lsun periode_livret ENUM("","T1","T2","T3","S1","S2") COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "Période officielle utilisable pour le livret scolaire." ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_periode ADD INDEX periode_livret (periode_livret) ' );
    // modification d'un champ de la table [sacoche_officiel_archive]
    if(empty($reload_sacoche_officiel_archive))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_officiel_archive CHANGE archive_type archive_type ENUM("sacoche","livret") CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "sacoche" ' );
    }
    // modification d'un champ de la table [sacoche_socle_composante]
    if(empty($reload_sacoche_socle_composante))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_socle_composante CHANGE socle_composante_ordre_lsun socle_composante_ordre_livret TINYINT(3) UNSIGNED NULL DEFAULT NULL ' );
    }
    // modification d'un champ de la table [sacoche_socle_domaine]
    if(empty($reload_sacoche_socle_domaine))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_socle_domaine CHANGE socle_domaine_ordre_lsun socle_domaine_ordre_livret TINYINT(3) UNSIGNED NULL DEFAULT NULL ' );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2016-04-29 => 2016-05-10
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2016-04-29')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2016-05-10';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // nouvelle table [sacoche_livret_ap]
    $reload_sacoche_livret_ap = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_ap.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // nouvelle table [sacoche_livret_epi]
    $reload_sacoche_livret_epi = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_epi.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // nouvelle table [sacoche_livret_epi_theme]
    $reload_sacoche_livret_epi_theme = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_epi_theme.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // nouvelle table [sacoche_livret_jointure_epi_prof]
    $reload_sacoche_livret_jointure_epi_prof = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_jointure_epi_prof.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // nouvelle table [sacoche_livret_jointure_modaccomp_eleve]
    $reload_sacoche_livret_jointure_modaccomp_eleve = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_jointure_modaccomp_eleve.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // nouvelle table [sacoche_livret_modaccomp]
    $reload_sacoche_livret_modaccomp = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_modaccomp.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // nouvelle table [sacoche_livret_parcours]
    $reload_sacoche_livret_parcours = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_parcours.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // nouvelle table [sacoche_livret_parcours_type]
    $reload_sacoche_livret_parcours_type = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_parcours_type.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // nouvelle table [sacoche_livret_page]
    $reload_sacoche_livret_page = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_page.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // nouvelle table [sacoche_livret_parametre_colonne]
    $reload_sacoche_livret_parametre_colonne = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_parametre_colonne.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // nouvelle table [sacoche_livret_parametre_rubrique]
    $reload_sacoche_livret_parametre_rubrique = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_parametre_rubrique.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // nouvelle table [sacoche_livret_jointure_groupe]
    $reload_sacoche_livret_jointure_groupe = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_jointure_groupe.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // nouvelle table [sacoche_livret_jointure_referentiel]
    $reload_sacoche_livret_jointure_referentiel = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_livret_jointure_referentiel.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
  }
}

?>
