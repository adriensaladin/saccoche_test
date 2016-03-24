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

?>
