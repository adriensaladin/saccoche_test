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
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_message CHANGE message_debut_date message_debut_date DATE DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL." ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_message CHANGE message_fin_date   message_fin_date   DATE DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL." ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_notification CHANGE notification_date notification_date DATETIME DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL." ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_officiel_fichier CHANGE fichier_date_generation fichier_date_generation DATE DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL." ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_saisie CHANGE saisie_date         saisie_date         DATE DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL." ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_saisie CHANGE saisie_visible_date saisie_visible_date DATE DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL." ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_devoir CHANGE devoir_date         devoir_date         DATE DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL." ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_devoir CHANGE devoir_visible_date devoir_visible_date DATE DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL." ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_referentiel CHANGE referentiel_partage_date referentiel_partage_date DATE DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL." ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel SET referentiel_partage_date=NULL WHERE referentiel_partage_date="0000-00-00"' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user CHANGE user_sortie_date user_sortie_date DATE DEFAULT NULL ' );
    // sacoche_user.sql
    // SELECT NULL > NOW() AS test_sup , NULL < NOW() AS test_inf
    // SELECT NULL > DATE_SUB(NOW(),INTERVAL 3 YEAR) AS test_sup , NULL < DATE_SUB(NOW(),INTERVAL 3 YEAR) AS test_inf
    // NOT NULL DEFAULT "9999-12-31"
    // SORTIE_DEFAUT_MYSQL
  }
}

?>
