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
// MAJ 2014-12-28 => 2015-01-21
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2014-12-28')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2015-01-21';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // paramètres mal retirés dans la mise à jour 2012-05-01
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_parametre WHERE parametre_nom IN ( "bulletin_item_appreciation_matiere_presence","bulletin_item_appreciation_matiere_longueur","bulletin_item_appreciation_generale_presence","bulletin_item_pourcentage_acquis_presence","bulletin_item_pourcentage_acquis_modifiable","bulletin_item_pourcentage_acquis_classe","bulletin_item_note_moyenne_score_presence","bulletin_item_note_moyenne_score_modifiable","bulletin_item_note_moyenne_score_classe","bulletin_socle_pourcentage_acquis_presence","bulletin_socle_etat_validation_presence","bulletin_socle_appreciation_generale_presence" )' );
    // modification du champ [user_langue] de la table [sacoche_user]
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user CHANGE user_langue user_langue VARCHAR(6) COLLATE utf8_unicode_ci NOT NULL DEFAULT "" ' );
    // ajout du champ [user_email_origine] à la table [sacoche_user]
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user ADD user_email_origine ENUM("","user","admin") COLLATE utf8_unicode_ci NOT NULL DEFAULT "" AFTER user_email ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_user SET user_email_origine="user" WHERE user_email!="" ' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2015-01-21 => 2015-02-03
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($version_base_structure_actuelle=='2015-01-21') || ($version_base_structure_actuelle=='2015-01-20') ) // un fichier indiquait un numéro de base erroné...
{
  if( (DB_STRUCTURE_MAJ_BASE::DB_version_base()=='2015-01-21') || (DB_STRUCTURE_MAJ_BASE::DB_version_base()=='2015-01-20') ) // du coup j'adapte aussi ce test-ci...
  {
    $version_base_structure_actuelle = '2015-02-03';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // modification d'un paramètre
    $officiel_infos_etablissement = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , 'SELECT parametre_valeur FROM sacoche_parametre WHERE parametre_nom="officiel_infos_etablissement" ' );
    $officiel_infos_etablissement = ($officiel_infos_etablissement) ? 'denomination,'.$officiel_infos_etablissement : 'denomination' ;
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$officiel_infos_etablissement.'" WHERE parametre_nom="officiel_infos_etablissement"' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2015-02-03 => 2015-02-17
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2015-02-03')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2015-02-17';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // nouvelle table [sacoche_abonnement]
    $reload_sacoche_abonnement = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_abonnement.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // nouvelle table [sacoche_jointure_user_abonnement]
    $reload_sacoche_jointure_user_abonnement = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_jointure_user_abonnement.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // nouvelle table [sacoche_notification]
    $reload_sacoche_notification = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_notification.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // Pour les admins, abonnement obligatoire aux contacts effectués depuis la page d'authentification
    $DB_SQL = 'SELECT user_id FROM sacoche_user ';
    $DB_SQL.= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
    $DB_SQL.= 'WHERE user_profil_type="administrateur" ';
    $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL );
    if(!empty($DB_TAB))
    {
      $DB_SQL = 'INSERT INTO sacoche_jointure_user_abonnement(user_id, abonnement_ref, jointure_mode) VALUES(:user_id,:abonnement_ref,:jointure_mode)';
      foreach($DB_TAB as $DB_ROW)
      {
        $DB_VAR = array(
          ':user_id'        => $DB_ROW['user_id'],
          ':abonnement_ref' => 'contact_externe',
          ':jointure_mode'  => 'accueil',
        );
        DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
      }
    }
    // Pour les professeurs et directeurs, abonnement obligatoire aux signalements d'un souci pour une appréciation d'un bilan officiel
    $DB_SQL = 'SELECT user_id FROM sacoche_user ';
    $DB_SQL.= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
    $DB_SQL.= 'WHERE user_profil_type IN("professeur","directeur") ';
    $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL );
    if(!empty($DB_TAB))
    {
      $DB_SQL = 'INSERT INTO sacoche_jointure_user_abonnement(user_id, abonnement_ref, jointure_mode) VALUES(:user_id,:abonnement_ref,:jointure_mode)';
      foreach($DB_TAB as $DB_ROW)
      {
        $DB_VAR = array(
          ':user_id'        => $DB_ROW['user_id'],
          ':abonnement_ref' => 'bilan_officiel_appreciation',
          ':jointure_mode'  => 'accueil',
        );
        DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
      }
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2015-02-17 => 2015-02-18
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2015-02-17')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2015-02-18';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // La table [sacoche_notification] peut ne pas avoir été créée à cause de la directive DEFAULT CURRENT_TIMESTAMP qui ne passe pas partout pour un champ DATETIME
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_notification.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2015-02-18 => 2015-02-22
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2015-02-18')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2015-02-22';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // suppression du champ [user_tentative_date] de la table [sacoche_user]
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user DROP user_tentative_date ' );
    // réordonner la table sacoche_parametre (ligne à déplacer vers la dernière MAJ lors d'ajout dans sacoche_parametre)
    // DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_parametre ORDER BY parametre_nom' );
  }
}

?>
