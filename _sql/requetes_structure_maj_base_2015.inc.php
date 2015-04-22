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
    $reload_sacoche_notification = TRUE;
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
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2015-02-22 => 2015-02-25
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2015-02-22')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2015-02-25';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // modif table [sacoche_notification]
    if(empty($reload_sacoche_notification))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_notification CHANGE notification_attente_id notification_attente_id MEDIUMINT(8) NULL DEFAULT NULL COMMENT "En cas de modification, pour retrouver une notification non encore envoyée ; passé à NULL une fois la notification envoyée." ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_notification ADD INDEX notification_statut(notification_statut) ' );
    }
     // modif table [sacoche_abonnement]
    if(empty($reload_sacoche_abonnement))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_abonnement VALUES( "fiche_brevet_statut", 0, 0, "professeur,directeur", "Fiche brevet, étape de saisie", "Ouverture d\'étape de saisie d\'une fiche brevet." )' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_abonnement SET abonnement_objet="Message d\'accueil" WHERE abonnement_ref="message_accueil" ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_abonnement SET abonnement_objet="Bilan officiel, étape de saisie", abonnement_descriptif="Ouverture d\'étape de saisie d\'un bilan officiel." WHERE abonnement_ref="bilan_officiel_statut" ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_abonnement SET abonnement_objet="Bilan officiel, erreur appréciation" WHERE abonnement_ref="bilan_officiel_appreciation" ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_abonnement SET abonnement_objet="Modification de référentiel" WHERE abonnement_ref="referentiel_edition" ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_abonnement SET abonnement_objet="Demande d\'évaluation formulée" WHERE abonnement_ref="demande_evaluation_eleve" ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_abonnement SET abonnement_objet="Auto-évaluation effectuée" WHERE abonnement_ref="devoir_autoevaluation_eleve" ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_abonnement SET abonnement_objet="Devoir partagé" WHERE abonnement_ref="devoir_prof_partage" ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_abonnement SET abonnement_objet="Devoir préparé" WHERE abonnement_ref="devoir_edition" ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_abonnement SET abonnement_objet="Saisie de résultats" WHERE abonnement_ref="devoir_saisie" ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_abonnement SET abonnement_objet="Demande d\'évaluation traitée" WHERE abonnement_ref="demande_evaluation_prof" ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_abonnement SET abonnement_objet="Bilan officiel disponible" WHERE abonnement_ref="bilan_officiel_visible" ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_abonnement SET abonnement_objet="Action sensible effectuée" WHERE abonnement_ref="action_sensible" ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_abonnement SET abonnement_objet="Action d\'administration" WHERE abonnement_ref="action_admin" ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_abonnement SET abonnement_objet="Contact externe" WHERE abonnement_ref="contact_externe" ' );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2015-02-25 => 2015-03-10
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2015-02-25')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2015-03-10';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // La table [sacoche_notification] peut ne pas avoir été créée à cause d'une virgule oubliée
    $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , 'SHOW TABLES FROM '.SACOCHE_STRUCTURE_BD_NAME.' LIKE "sacoche_notification"');
    if(empty($DB_TAB))
    {
      $reload_sacoche_notification = TRUE;
      $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_notification.sql');
      DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
      DB::close(SACOCHE_STRUCTURE_BD_NAME);
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2015-03-10 => 2015-03-13
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2015-03-10')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2015-03-13';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // Le renseignement de la description de l'évaluation était auparavant facultatif.
    // Il est devenu obligatoire depuis la version 2015-02-09.
    // Donc si une évaluation a été paramétrée antérieurement sans description, cela pose souci lors d'actions ultérieures sur cette évaluation.
    // La solution est d'ajouter la description manquante.
    // On s'y emploie automatiquement ici.
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_devoir SET devoir_info="sans titre" WHERE devoir_info="" ' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2015-03-13 => 2015-03-24
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2015-03-13')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2015-03-24';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // Modif champs type EUNM dans sacoche_jointure_groupe_periode et sacoche_groupe
    DB::query(SACOCHE_STRUCTURE_BD_NAME , ' ALTER TABLE sacoche_jointure_groupe_periode CHANGE officiel_releve officiel_releve ENUM("","1vide","2rubrique","3synthese","4complet","3mixte","4synthese","5complet") CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "", CHANGE officiel_bulletin officiel_bulletin ENUM("","1vide","2rubrique","3synthese","4complet","3mixte","4synthese","5complet") CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "", CHANGE officiel_palier1 officiel_palier1 ENUM("","1vide","2rubrique","3synthese","4complet","3mixte","4synthese","5complet") CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "", CHANGE officiel_palier2 officiel_palier2 ENUM("","1vide","2rubrique","3synthese","4complet","3mixte","4synthese","5complet") CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "", CHANGE officiel_palier3 officiel_palier3 ENUM("","1vide","2rubrique","3synthese","4complet","3mixte","4synthese","5complet") CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , ' ALTER TABLE sacoche_groupe CHANGE fiche_brevet fiche_brevet ENUM( "","1vide","2rubrique","3synthese","4complet","4synthese","5complet" ) COLLATE utf8_unicode_ci NOT NULL DEFAULT "" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , ' UPDATE sacoche_jointure_groupe_periode SET officiel_releve="5complet"  WHERE officiel_releve="4complet" '  );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , ' UPDATE sacoche_jointure_groupe_periode SET officiel_releve="4synthese" WHERE officiel_releve="3synthese" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , ' UPDATE sacoche_jointure_groupe_periode SET officiel_bulletin="5complet"  WHERE officiel_bulletin="4complet" '  );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , ' UPDATE sacoche_jointure_groupe_periode SET officiel_bulletin="4synthese" WHERE officiel_bulletin="3synthese" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , ' UPDATE sacoche_jointure_groupe_periode SET officiel_palier1="5complet"  WHERE officiel_palier1="4complet" '  );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , ' UPDATE sacoche_jointure_groupe_periode SET officiel_palier1="4synthese" WHERE officiel_palier1="3synthese" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , ' UPDATE sacoche_jointure_groupe_periode SET officiel_palier2="5complet"  WHERE officiel_palier2="4complet" '  );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , ' UPDATE sacoche_jointure_groupe_periode SET officiel_palier2="4synthese" WHERE officiel_palier2="3synthese" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , ' UPDATE sacoche_jointure_groupe_periode SET officiel_palier3="5complet"  WHERE officiel_palier3="4complet" '  );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , ' UPDATE sacoche_jointure_groupe_periode SET officiel_palier3="4synthese" WHERE officiel_palier3="3synthese" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , ' UPDATE sacoche_groupe SET fiche_brevet="5complet"  WHERE fiche_brevet="4complet" '  );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , ' UPDATE sacoche_groupe SET fiche_brevet="4synthese" WHERE fiche_brevet="3synthese" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , ' ALTER TABLE sacoche_jointure_groupe_periode CHANGE officiel_releve officiel_releve ENUM("","1vide","2rubrique","3mixte","4synthese","5complet") CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "", CHANGE officiel_bulletin officiel_bulletin ENUM("","1vide","2rubrique","3mixte","4synthese","5complet") CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "", CHANGE officiel_palier1 officiel_palier1 ENUM("","1vide","2rubrique","3mixte","4synthese","5complet") CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "", CHANGE officiel_palier2 officiel_palier2 ENUM("","1vide","2rubrique","3mixte","4synthese","5complet") CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "", CHANGE officiel_palier3 officiel_palier3 ENUM("","1vide","2rubrique","3mixte","4synthese","5complet") CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , ' ALTER TABLE sacoche_groupe CHANGE fiche_brevet fiche_brevet ENUM( "","1vide","2rubrique","3mixte","4synthese","5complet" ) COLLATE utf8_unicode_ci NOT NULL DEFAULT "" ' );
    // réordonner la table sacoche_parametre (ligne à déplacer vers la dernière MAJ lors d'ajout dans sacoche_parametre)
    // DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_parametre ORDER BY parametre_nom' );
  }
}

?>
