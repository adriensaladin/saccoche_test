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
    DB::query(SACOCHE_STRUCTURE_BD_NAME , ' UPDATE sacoche_jointure_groupe_periode SET officiel_releve="5complet"    WHERE officiel_releve="4complet" '  );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , ' UPDATE sacoche_jointure_groupe_periode SET officiel_releve="4synthese"   WHERE officiel_releve="3synthese" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , ' UPDATE sacoche_jointure_groupe_periode SET officiel_bulletin="5complet"  WHERE officiel_bulletin="4complet" '  );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , ' UPDATE sacoche_jointure_groupe_periode SET officiel_bulletin="4synthese" WHERE officiel_bulletin="3synthese" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , ' UPDATE sacoche_jointure_groupe_periode SET officiel_palier1="5complet"   WHERE officiel_palier1="4complet" '  );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , ' UPDATE sacoche_jointure_groupe_periode SET officiel_palier1="4synthese"  WHERE officiel_palier1="3synthese" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , ' UPDATE sacoche_jointure_groupe_periode SET officiel_palier2="5complet"   WHERE officiel_palier2="4complet" '  );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , ' UPDATE sacoche_jointure_groupe_periode SET officiel_palier2="4synthese"  WHERE officiel_palier2="3synthese" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , ' UPDATE sacoche_jointure_groupe_periode SET officiel_palier3="5complet"   WHERE officiel_palier3="4complet" '  );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , ' UPDATE sacoche_jointure_groupe_periode SET officiel_palier3="4synthese"  WHERE officiel_palier3="3synthese" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , ' UPDATE sacoche_groupe SET fiche_brevet="5complet"  WHERE fiche_brevet="4complet" '  );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , ' UPDATE sacoche_groupe SET fiche_brevet="4synthese" WHERE fiche_brevet="3synthese" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , ' ALTER TABLE sacoche_jointure_groupe_periode CHANGE officiel_releve officiel_releve ENUM("","1vide","2rubrique","3mixte","4synthese","5complet") CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "", CHANGE officiel_bulletin officiel_bulletin ENUM("","1vide","2rubrique","3mixte","4synthese","5complet") CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "", CHANGE officiel_palier1 officiel_palier1 ENUM("","1vide","2rubrique","3mixte","4synthese","5complet") CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "", CHANGE officiel_palier2 officiel_palier2 ENUM("","1vide","2rubrique","3mixte","4synthese","5complet") CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "", CHANGE officiel_palier3 officiel_palier3 ENUM("","1vide","2rubrique","3mixte","4synthese","5complet") CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , ' ALTER TABLE sacoche_groupe CHANGE fiche_brevet fiche_brevet ENUM( "","1vide","2rubrique","3mixte","4synthese","5complet" ) COLLATE utf8_unicode_ci NOT NULL DEFAULT "" ' );
    // réordonner la table sacoche_parametre (ligne à déplacer vers la dernière MAJ lors d'ajout dans sacoche_parametre)
    // DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_parametre ORDER BY parametre_nom' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2015-03-24 => 2015-04-22
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2015-03-24')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2015-04-22';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // niveaux ajoutés
    if(empty($reload_sacoche_niveau))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 100, 0,  1, 140, "CAP", "", "Cycle CAP") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 110, 0,  1, 150, "BEP", "", "Cycle BEP") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 120, 0,  1, 160, "PRO", "", "Cycle Bac Pro") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_niveau VALUES ( 140, 0,  1, 180, "BTS", "", "Cycle BTS") ' );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2015-04-22 => 2015-05-12
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2015-04-22')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2015-05-12';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // Ajout de familles de matières et modification d'un champ
    if(empty($reload_sacoche_matiere_famille))
    {
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_matiere_famille CHANGE matiere_famille_nom matiere_famille_nom VARCHAR(55) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "" ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere_famille VALUES ( 46, 3, "Métiers d\'art (suite)") ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere_famille VALUES ( 65, 3, "Disciplines professionnelles de l\'enseignement agricole") ' );
    }
    // Intégration de nouvelles matières 2013 / 2014 / 2015.
    if(empty($reload_sacoche_matiere))
    {
      // Problème de la matière 601, EIST ("Enseignement intégré de science et technologie"),
      // qui en juillet 2012 avait été créée en attendant (en vain) que la matière apparaisse officiellement,
      // et maintenant on a besoin de son id (alors on lui attribue l'id 600).
      $id_avant = 601;
      $id_apres = 600;
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_id = '.$id_apres.' WHERE matiere_id = '.$id_avant.' ' );
      DB_STRUCTURE_ADMINISTRATEUR::DB_deplacer_referentiel_matiere($id_avant,$id_apres);
      SACocheLog::ajouter('Déplacement des référentiels d\'une matière ('.$id_avant.' to '.$id_apres.').');
      // nouvelles matières
      $insert = '
      (  75, 0, 0, 100, 0, 255, "ACIND", "Activités inter-disciplinaires"),
      (  76, 0, 0, 100, 0, 255, "ACTPR", "Activités de projet"),
      (  77, 0, 0, 100, 0, 255, "CERPR", "Certification professionnelle"),
      (  78, 0, 0, 100, 0, 255, "AAEPR", "Accès autonomie équipements professionnels"),
      (  79, 0, 0, 100, 0, 255, "APDPR", "Approche pluridisciplinaire & dimension professionnelle"),
      (  96, 0, 0, 100, 0, 255, "COMPR", "Connaissance des milieux professionnels"),
      (  97, 0, 0, 100, 0, 255, "PROPR", "Projet professionnel"),
      ( 203, 0, 0,   2, 0, 255, "LCALA", "Langues et cultures de l\'antiquité latine"),
      ( 204, 0, 0,   2, 0, 255, "LCAGR", "Langues et cultures de l\'antiquité grecque"),
      ( 436, 0, 0,   4, 0, 255, "HGGMC", "Histoire, géographie & géopolitique du monde contemporain"),
      ( 437, 0, 0,   4, 0, 255, "HI-GE", "Histoire-géographie"),
      ( 438, 0, 1,   4, 0, 255, "EMC"  , "Enseignement moral et civique"),
      ( 523, 0, 0,   5, 0, 255, "ESHMC", "Économie, sociologie & histoire du monde contemporain"),
      ( 601, 0, 0,   6, 0, 255, "PCAPP", "Physique et chimie appliquées"),
      ( 604, 0, 0,   6, 0, 255, "CPIND", "Chimie et physique industrielles"),
      ( 661, 0, 0,   6, 0, 255, "CSCTE", "Cadre scientifique et technologique"),
      ( 686, 0, 0,   6, 0, 255, "MASPC", "Mathématiques sciences physiques & chimiques"),
      ( 687, 0, 0,   6, 0, 255, "SCIIN", "Sciences industrielles de l\'ingénieur"),
      ( 710, 0, 0,   7, 0, 255, "ENPRO", "Enseignement professionnel"),
      ( 740, 0, 0,   7, 0, 255, "TPROF", "Technologies professionnelles"),
      ( 741, 0, 0,   7, 0, 255, "ETP"  , "Enseignements techniques et professionnels"),
      ( 742, 0, 0,   7, 0, 255, "TTPRO", "Technologie & techniques professionnelles"),
      (1138, 0, 0,  11, 0, 255, "ETARC", "Étude architecturale"),
      (1139, 0, 0,  11, 0, 255, "ETPRP", "Étude et préparation de projet"),
      (1607, 0, 0,  16, 0, 255, "GENCH", "Génie chimique"),
      (2080, 0, 0,  20, 0, 255, "EPCAR", "Étude des produits carrossés"),
      (2081, 0, 0,  20, 0, 255, "CPCAR", "Conception des produits carrossés"),
      (2082, 0, 0,  20, 0, 255, "RPCAR", "Réalisation des produits carrossés"),
      (2130, 0, 0,  21, 0, 255, "MCMAT", "Modélisation comportement des matériels"),
      (2131, 0, 0,  21, 0, 255, "TIMAT", "Technologie & intervention sur matériels"),
      (2132, 0, 0,  21, 0, 255, "EPSYS", "Étude pluritechnologique des systèmes"),
      (2133, 0, 0,  21, 0, 255, "ORMAT", "Organisation de la maintenance"),
      (2134, 0, 0,  21, 0, 255, "TMCPR", "Technique de maintenance conduite prévention"),
      (2218, 0, 0,  22, 0, 255, "ELCOM", "Électronique et communications"),
      (2421, 0, 0,  24, 0, 255, "INFRE", "Informatique et réseaux"),
      (2798, 0, 0,  27, 0, 255, "HASCT", "Histoire de l\'art des sciences & techniques"),
      (3090, 0, 0,  30, 0, 255, "ACMAE", "Agronomie & connaissance milieu agroéquipement"),
      (3091, 0, 0,  30, 0, 255, "STSYS", "Sciences et technologie des systèmes"),
      (3092, 0, 0,  30, 0, 255, "SQSER", "Syst.qual.sécur.envir. resp.sociale & devel.durable"),
      (3093, 0, 0,  30, 0, 255, "BMEAP", "Biologie microbiologie & écologie appliquée"),
      (3245, 0, 0,  32, 0, 255, "SMVSM", "Sc. matière et vie et sciences médicales"),
      (3246, 0, 0,  32, 0, 255, "IMDTR", "Sc. & techn., fond. méth. imagerie médicale"),
      (3247, 0, 0,  32, 0, 255, "IIMDT", "Sc. & techn., intervention en imagerie médicale"),
      (3248, 0, 0,  32, 0, 255, "OUTMT", "Outils et méthodes de travail"),
      (3249, 0, 0,  32, 0, 255, "INSPP", "Intégration savoirs & posture professionnelle"),
      (3308, 0, 0,  33, 0, 255, "CMOTE", "Conception et moe de techniques cosmet."),
      (3309, 0, 0,  33, 0, 255, "ENEST", "Environnement esthétique"),
      (3310, 0, 0,  33, 0, 255, "PRCOS", "Le produit cosmétique"),
      (3311, 0, 0,  33, 0, 255, "APECP", "Actions professionnelles (esthétique cosmétique parfumerie)"),
      (3312, 0, 0,  33, 0, 255, "TPPLU", "Travaux pratiques pluridimensionnels"),
      (3313, 0, 0,  33, 0, 255, "EFPRC", "Efficacite des produits cosmétiques"),
      (3314, 0, 0,  33, 0, 255, "COELP", "Conception, élaboration, production"),
      (3315, 0, 0,  33, 0, 255, "TECHC", "Techniques cosmétiques"),
      (3316, 0, 0,  33, 0, 255, "FPCCO", "Fondement physico-chimiques cosmétologie"),
      (3317, 0, 0,  33, 0, 255, "COSAP", "Cosmétologie appliquée"),
      (3465, 0, 0,  34, 0, 255, "MANEC", "Management de l\'entité commerciale"),
      (3466, 0, 0,  34, 0, 255, "VPSCP", "Mise en valeur prod. et serv. et comm. publiciaire"),
      (3467, 0, 0,  34, 0, 255, "TNERC", "Technique de négociation relation client"),
      (3468, 0, 0,  34, 0, 255, "TECOM", "Technologies commerciales"),
      (3469, 0, 0,  34, 0, 255, "IMSMA", "Image et mise en scène de la marque"),
      (3470, 0, 0,  34, 0, 255, "DSACC", "Développement & suivi de l\'activité commerciale"),
      (3471, 0, 0,  34, 0, 255, "MAGRH", "Management gestion des ressources humaines"),
      (3472, 0, 0,  34, 0, 255, "MERMA", "Mercatique (marketing)"),
      (3662, 0, 0,  36, 0, 255, "ETUOS", "Environnement de travail : outil stratégique"),
      (3663, 0, 0,  36, 0, 255, "EVENP", "Évolution de l\'environnement professionnel"),
      (3664, 0, 0,  36, 0, 255, "DRECO", "Document. règlement. expert. cosmetovig."),
      (3665, 0, 0,  36, 0, 255, "EEJME", "Environnement économ., juridique & manager. édition"),
      (3666, 0, 0,  36, 0, 255, "EEJOB", "Environnement économ., juridique & organis. activité bancaire"),
      (3667, 0, 0,  36, 0, 255, "ENVPR", "Environnement professionnel"),
      (3704, 0, 0,  37, 0, 255, "ECOSI", "Enseignement commun (si)"),
      (3732, 0, 0,  37, 0, 255, "SIGET", "Systèmes d\'information de gestion"),
      (3733, 0, 0,  37, 0, 255, "SYSIG", "Système d\'information de gestion"),
      (3734, 0, 0,  37, 0, 255, "SISR" , "Solutions d’infrastructure, systèmes et réseaux"),
      (3735, 0, 0,  37, 0, 255, "SLAM" , "Solutions logicielles et applications métiers"),
      (3736, 0, 0,  37, 0, 255, "PRPEN", "Projets personnalisés encadrés"),
      (3832, 0, 0,  38, 0, 255, "CEJUM", "Culture économique, juridique et manageriale"),
      (3833, 0, 0,  38, 0, 255, "ECOGE", "Économie-gestion"),
      (3834, 0, 0,  38, 0, 255, "EC-DR", "Économie-droit"),
      (3930, 0, 0,  39, 0, 255, "P1P2" , "P1 plus P2"),
      (3931, 0, 0,  39, 0, 255, "P3P4" , "P3 plus P4"),
      (3932, 0, 0,  39, 0, 255, "P5P6" , "P5 plus P6"),
      (3933, 0, 0,  39, 0, 255, "P7-"  , "P7"),
      (3934, 0, 0,  39, 0, 255, "ATEPR", "Ateliers professionnels"),
      (3935, 0, 0,  39, 0, 255, "MOPAP", "Module optionnel d\'approfondissement"),
      (3936, 0, 0,  39, 0, 255, "AREID", "Accès ressources informatiques & documentaires"),
      (4061, 0, 0,  40, 0, 255, "ECGEH", "Économie et gestion hôteliere"),
      (4062, 0, 0,  40, 0, 255, "PRSTC", "Projet sthr (sciences & technologies culinaires)"),
      (4063, 0, 0,  40, 0, 255, "PRSTS", "Projet sthr (sciences & technologies des services)"),
      (4064, 0, 0,  40, 0, 255, "SCTES", "Sciences et technologies des services"),
      (4065, 0, 0,  40, 0, 255, "STECU", "Sciences et technologies culinaires"),
      (4066, 0, 0,  40, 0, 255, "ESALE", "Enseignement scientifique alimentation-environnement"),
      (4159, 0, 0,  41, 0, 255, "MEMOC", "Méthodes et moyens de communication"),
      (4160, 0, 0,  41, 0, 255, "PROCC", "Promotion et communication commerciale"),
      (4161, 0, 0,  41, 0, 255, "TEFAP", "Technique de formation, d\'animation de promotion"),
      (4162, 0, 0,  41, 0, 255, "ERPED", "Étude & réalisation de projets d\'édition"),
      (4163, 0, 0,  41, 0, 255, "CTMAN", "Communication & techniques de management"),
      (4164, 0, 0,  41, 0, 255, "CTECO", "Communication technique et commerciale"),
      (4165, 0, 0,  41, 0, 255, "OAEXC", "Outils analyse expression et communication"),
      (4166, 0, 0,  41, 0, 255, "RHCOM", "Ressources humaines et communication"),
      (4355, 0, 0,  43, 0, 255, "GESFI", "Gestion et finance"),
      (4356, 0, 0,  43, 0, 255, "EGAAE", "Économie-gestion appliquée agroéquipement"),
      (4357, 0, 0,  43, 0, 255, "GEDAC", "Gestion économique & développement de l\'activité"),
      (4358, 0, 0,  43, 0, 255, "ORMEO", "Organisation et mise en œuvre"),
      (4359, 0, 0,  43, 0, 255, "MASCG", "Management et sciences de gestion"),
      (4360, 0, 0,  43, 0, 255, "GESMG", "Gestion et management"),
      (4510, 0, 0,  45, 0, 255, "PRCRA", "Pratiques créatives et artistiques"),
      (4511, 0, 0,  45, 0, 255, "RDPRO", "Recherche et démarche de projet"),
      (4512, 0, 0,  45, 0, 255, "DESTC", "Design, sciences & technologies contemporaines"),
      (4513, 0, 0,  45, 0, 255, "INEXP", "Investigation, exploitation, projection"),
      (4514, 0, 0,  45, 0, 255, "MEDIA", "Médiation"),
      (4601, 0, 0,  46, 0, 255, "CULTA", "Cultures artistiques"),
      (4602, 0, 0,  46, 0, 255, "TEMEO", "Technique et mise en œuvre"),
      (4603, 0, 0,  46, 0, 255, "CAUDA", "Culture audiovisuelle et artistique"),
      (6510, 0, 0,  65, 0, 255, "ETP-A", "Enseignement technologique et professionnel"),
      (6520, 0, 0,  65, 0, 255, "SCTCA", "Sciences et techniques"),
      (6530, 0, 0,  65, 0, 255, "PPROA", "Pratiques professionnelles"),
      (6540, 0, 0,  65, 0, 255, "PROPA", "Projet professionnel"),
      (6550, 0, 0,  65, 0, 255, "FRDOA", "Français Documentation"),
      (6551, 0, 0,  65, 0, 255, "6951A", "Français Philosophie"),
      (6552, 0, 0,  65, 0, 255, "MAINA", "Mathématiques Informatique")';
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES '.$insert );
    }
    // renommage du champ [user_id] de la table [sacoche_selection_item] en [proprio_id]
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_selection_item CHANGE user_id proprio_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0 ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_selection_item DROP INDEX user_id, ADD INDEX proprio_id (proprio_id) ' );
    // nouvelle table [sacoche_jointure_selection_prof]
    $reload_sacoche_jointure_selection_prof = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_jointure_selection_prof.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2015-05-12 => 2015-05-19
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2015-05-12')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2015-05-19';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // nouvelle table [sacoche_user_switch]
    $reload_sacoche_user_switch = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_user_switch.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2015-05-19 => 2015-05-27
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2015-05-19')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2015-05-27';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // nouvelle table [sacoche_jointure_selection_item]
    $reload_sacoche_jointure_selection_item = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_jointure_selection_item.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    // remplissage de la table
    $DB_SQL = 'SELECT selection_item_id , selection_item_liste ';
    $DB_SQL.= 'FROM sacoche_selection_item ';
    $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL );
    if(!empty($DB_TAB))
    {
      $DB_SQL = 'INSERT INTO sacoche_jointure_selection_item(selection_item_id, item_id) VALUES(:selection_item_id,:item_id)';
      $DB_VAR = array();
      foreach($DB_TAB as $DB_ROW)
      {
        $DB_VAR[':selection_item_id'] = $DB_ROW['selection_item_id'];
        $tab_item = explode( ',' , substr($DB_ROW['selection_item_liste'],1,-1) );
        foreach($tab_item as $item_id)
        {
          $DB_VAR[':item_id'] = $item_id;
          DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
        }
      }
    }
    // suppression du champ [selection_item_liste] de la table [sacoche_selection_item]
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_selection_item DROP selection_item_liste' );
    // renommage de 2 niveaux
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET niveau_nom = "Première STD2A / STI2D / STL / ST2S / STMG"  WHERE niveau_id = 73 ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET niveau_nom = "Terminale STD2A / STI2D / STL / ST2S / STMG" WHERE niveau_id = 79 ' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2015-05-27 => 2015-06-09
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2015-05-27')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2015-06-09';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // correction d'un identifiant d'un item du socle (erreur en place depuis des années et découverte seulement maintenant...)
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_socle_entree         SET entree_id = 2453  WHERE entree_id = 2451 ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_jointure_user_entree SET entree_id = 2453  WHERE entree_id = 2451 ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_referentiel_item     SET entree_id = 2453  WHERE entree_id = 2451 ' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2015-06-09 => 2015-07-03
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2015-06-09')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2015-07-03';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    if(empty($reload_sacoche_matiere))
    {
      // Matières renommées
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_nom = "Accès en autonomie au laboratoire informatique"           WHERE matiere_id = 24 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_nom = "Accès autonomie laboratoire informatique & communication" WHERE matiere_id = 65 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_nom = "Physique (laboratoire industriel et recherche)"           WHERE matiere_id = 626 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_nom = "Chimie (laboratoire industriel et recherche)"             WHERE matiere_id = 632 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_nom = "Sciences physiques et chimiques en laboratoire"           WHERE matiere_id = 697 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_nom = "Innovation technologique et éco-conception"               WHERE matiere_id = 736 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_nom = "Accompagnement éducatif - pratique art.culturel"          WHERE matiere_id = 998 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_nom = "Énergies et environnement"                                WHERE matiere_id = 1817 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_nom = "Bio-informatique et informatique de laboratoire"          WHERE matiere_id = 2419 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_nom = "Économie sociale familiale"                               WHERE matiere_id = 6301 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_nom = "Sciences et technologies des équipements"                 WHERE matiere_id = 6402 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_nom = "Sciences et techniques aquacoles"                         WHERE matiere_id = 6602 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_nom = "Sciences et techniques horticoles"                        WHERE matiere_id = 6603 ' );
      // dont les anciens piliers du socle
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_nom = "P1 Maîtrise de la langue française"          WHERE matiere_id = 9901 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_nom = "P2 Pratique d\'une langue vivante étrangère" WHERE matiere_id = 9902 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_nom = "P3 Principaux éléments de mathématiques"     WHERE matiere_id = 9903 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_nom = "P3 Culture scientifique et technologique"    WHERE matiere_id = 9904 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_nom = "P4 Maîtrise des TICE"                        WHERE matiere_id = 9905 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_nom = "P5 Culture humaniste"                        WHERE matiere_id = 9906 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_nom = "P6 Compétences sociales et civiques"         WHERE matiere_id = 9907 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_nom = "P7 Autonomie et initiative"                  WHERE matiere_id = 9908 ' );
      // Références renommées
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "EQUIA" WHERE matiere_id = 6001 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "HIEQA" WHERE matiere_id = 6002 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "MAREA" WHERE matiere_id = 6003 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "ZOOHA" WHERE matiere_id = 6004 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "TCMRA" WHERE matiere_id = 6101 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "TECOA" WHERE matiere_id = 6102 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "TANIA" WHERE matiere_id = 6201 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "ESF-A" WHERE matiere_id = 6301 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "AEN-A" WHERE matiere_id = 6302 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "TFORA" WHERE matiere_id = 6303 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "APAYA" WHERE matiere_id = 6304 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "MACHA" WHERE matiere_id = 6401 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "STEQA" WHERE matiere_id = 6402 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "AEQUA" WHERE matiere_id = 6403 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "EQAGA" WHERE matiere_id = 6404 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "EQHYA" WHERE matiere_id = 6405 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "AGROA" WHERE matiere_id = 6601 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "STAQA" WHERE matiere_id = 6602 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "STHOA" WHERE matiere_id = 6603 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "OENOA" WHERE matiere_id = 6604 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "VITIA" WHERE matiere_id = 6605 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "VIOEA" WHERE matiere_id = 6606 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "ZOOTA" WHERE matiere_id = 6607 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "GPIAA" WHERE matiere_id = 6801 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "GALIM" WHERE matiere_id = 6802 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "GINDA" WHERE matiere_id = 6803 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "APLUA" WHERE matiere_id = 6901 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "AIF-A" WHERE matiere_id = 6902 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "AIM-A" WHERE matiere_id = 6903 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "ARTSA" WHERE matiere_id = 6904 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "BCHIA" WHERE matiere_id = 6905 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "BCHMA" WHERE matiere_id = 6906 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "BIOLA" WHERE matiere_id = 6907 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "BANIA" WHERE matiere_id = 6908 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "BVEGA" WHERE matiere_id = 6909 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "BECOA" WHERE matiere_id = 6910 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "CHIMA" WHERE matiere_id = 6911 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "CPTBA" WHERE matiere_id = 6912 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "DOCUA" WHERE matiere_id = 6913 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "EPS-A" WHERE matiere_id = 6914 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "ECOLA" WHERE matiere_id = 6915 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "EENTA" WHERE matiere_id = 6916 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "EDRTA" WHERE matiere_id = 6917 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "ECJSA" WHERE matiere_id = 6918 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "ESOCA" WHERE matiere_id = 6919 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "FRANA" WHERE matiere_id = 6920 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "GEO-A" WHERE matiere_id = 6921 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "GESTA" WHERE matiere_id = 6922 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "GRECA" WHERE matiere_id = 6923 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "HVC-A" WHERE matiere_id = 6924 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "HGECA" WHERE matiere_id = 6925 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "HIGEA" WHERE matiere_id = 6926 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "HPS-A" WHERE matiere_id = 6927 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "INFOA" WHERE matiere_id = 6928 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "LATIA" WHERE matiere_id = 6929 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "MATHA" WHERE matiere_id = 6930 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "MRCAA" WHERE matiere_id = 6931 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "MBIOA" WHERE matiere_id = 6932 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "PHILA" WHERE matiere_id = 6933 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "PHYSA" WHERE matiere_id = 6934 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "P-APA" WHERE matiere_id = 6935 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "P-CHA" WHERE matiere_id = 6936 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "6937A" WHERE matiere_id = 6937 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "PSC-A" WHERE matiere_id = 6938 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "PSE-A" WHERE matiere_id = 6939 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "SESGA" WHERE matiere_id = 6940 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "SES-A" WHERE matiere_id = 6941 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "SEH-A" WHERE matiere_id = 6942 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "SECOA" WHERE matiere_id = 6943 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "SBURA" WHERE matiere_id = 6944 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "STATA" WHERE matiere_id = 6945 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "SVT-A" WHERE matiere_id = 6946 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "TCMUA" WHERE matiere_id = 6947 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "TDOCA" WHERE matiere_id = 6948 ' );
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_ref = "TIM-A" WHERE matiere_id = 6949 ' );
      // Ajout de matières enseignées
      $insert = '
        ( 121, 0, 0,   1, 0, 255, "SCHSD", "Sciences humaines, sociales et droit"),
        ( 122, 0, 0,   1, 0, 255, "SHPAD", "Sciences humaines et philosophie générale de l\'art et du design"),
        (1754, 0, 0,  17, 0, 255, "GENCI", "Génie civil"),
        (3225, 0, 0,  32, 0, 255, "EMEDS", "Enseignement médical et scientifique"),
        (3658, 0, 0,  36, 0, 255, "DRAPP", "Droit approfondi"),
        (4058, 0, 0,  40, 0, 255, "SAAHE", "Sciences appliquées alimentation hygiène environnement"),
        (4067, 0, 0,  40, 0, 255, "MAEHR", "Management d\'une entreprise d\'hôtellerie-restauration"),
        (4361, 0, 0,  43, 0, 255, "GEAPP", "Gestion approfondie"),
        (6406, 0, 0,  64, 0, 255, "STAEQ", "Sciences et techniques des agroéquipements"),
        (6608, 0, 0,  66, 0, 255, "TAQUA", "Technologie aquacole") ';
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES '.$insert );
      // dont les nouveaux domaines du socle
      $insert = '
        (9931, 0, 1,  99, 0, 255, "D1"   , "D1 Les langages pour penser et communiquer"),
        (9932, 0, 1,  99, 0, 255, "D2"   , "D2 Les méthodes et outils pour apprendre"),
        (9933, 0, 1,  99, 0, 255, "D3"   , "D3 La formation de la personne et du citoyen"),
        (9934, 0, 1,  99, 0, 255, "D4"   , "D4 Les systèmes naturels et les systèmes techniques"),
        (9935, 0, 1,  99, 0, 255, "D5"   , "D5 Les représentations du monde et l\'activité humaine") ';
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT INTO sacoche_matiere VALUES '.$insert );
      // Cas de 3 matières ajoutées le mois dernier mais dont l'indexation a changé (ou alors je m'étais trompé).
      $tab_modif_id = array(
        6550 => 6950,
        6551 => 6951,
        6552 => 6952,
      );
      foreach($tab_modif_id as $id_avant => $id_apres)
      {
        DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_matiere SET matiere_id = '.$id_apres.', matiere_famille_id=69 WHERE matiere_id = '.$id_avant.' ' );
        DB_STRUCTURE_ADMINISTRATEUR::DB_deplacer_referentiel_matiere($id_avant,$id_apres);
        SACocheLog::ajouter('Déplacement des référentiels d\'une matière ('.$id_avant.' to '.$id_apres.').');
      }
      // réordonner la table sacoche_matiere (ligne à déplacer vers la dernière MAJ lors d'ajouts dans sacoche_matiere)
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_matiere ORDER BY matiere_id' );
    }
    if(empty($reload_sacoche_matiere_famille))
    {
      // Ajout de familles de matières
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'INSERT sacoche_matiere_famille VALUES ( 47, 2, "Activités non spécialisées (suite)") , ( 48, 2, "Sciences (suite)") ');
      // réordonner la table sacoche_matiere_famille (ligne à déplacer vers la dernière MAJ lors d'ajouts dans sacoche_matiere_famille)
      DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_matiere_famille ORDER BY matiere_famille_id' );
    }
    if(empty($reload_sacoche_niveau_famille))
    {
      // nouvelle table niveau_famille
      $reload_sacoche_niveau_famille = TRUE;
      $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_niveau_famille.sql');
      DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes ); // Attention, sur certains LCS ça bloque au dela de 40 instructions MySQL (mais un INSERT multiple avec des milliers de lignes ne pose pas de pb).
      DB::close(SACOCHE_STRUCTURE_BD_NAME);
    }
    if(empty($reload_sacoche_niveau))
    {
      // correspondance anciens -> nouveaux niveaux, trié dans un ordre évitant tout conflit récursif
      $tab_id_niveaux = array(
        133 => 371002 ,
        132 => 371001 ,
        131 => 370000 ,
        155 => 350002 ,
        154 => 350001 ,
        153 => 316002 ,
        152 => 316001 ,
        151 => 315000 ,
        126 => 312003 ,
        125 => 312002 ,
        124 => 312001 ,
        123 => 311002 ,
        122 => 311001 ,
        121 => 310000 ,
        119 => 254002 ,
        118 => 254001 ,
        117 => 253000 ,
        143 => 251002 ,
        142 => 251001 ,
        141 => 250000 ,
        116 => 247003 ,
        115 => 247002 ,
        114 => 247001 ,
        113 => 246002 ,
        112 => 246001 ,
        111 => 245000 ,
        103 => 244002 ,
        102 => 244001 ,
        101 => 243000 ,
         96 => 242003 ,
         95 => 242002 ,
         94 => 242001 ,
         93 => 241002 ,
         92 => 241001 ,
         91 => 240000 ,
         78 => 232000 ,
         77 => 231000 ,
         82 => 224000 ,
         76 => 223000 ,
         81 => 222000 ,
         75 => 221000 ,
         72 => 220000 ,
         80 => 214000 ,
         74 => 213000 ,
         79 => 212000 ,
         73 => 211000 ,
         71 => 210000 ,
         67 => 202006 ,
         66 => 202005 ,
         65 => 202001 ,
         69 => 202000 ,
         64 => 201006 ,
         63 => 201005 ,
         62 => 201001 ,
         68 => 201000 ,
         61 => 200001 ,
         44 => 167001 ,
         43 => 166001 ,
         42 => 165001 ,
         41 => 164001 ,
         55 => 115001 ,
         54 => 114001 ,
         53 => 113001 ,
         52 => 112001 ,
         51 => 110001 ,
         38 => 106001 ,
         37 => 105001 ,
         36 => 104001 ,
         35 => 103003 ,
         34 => 102004 ,
         33 => 102001 ,
         32 => 101001 ,
         31 => 100003 ,
         21 =>  62001 ,
         20 =>  61001 ,
         19 =>  60002 ,
         18 =>  30002 ,
         17 =>  30001 ,
         16 =>  20002 ,
         15 =>  20001 ,
         14 =>  10001 ,
         13 =>   1033 ,
         12 =>   1032 ,
         11 =>   1031 ,
         10 =>   1011 ,
        215 =>    305 ,
        214 =>    304 ,
        213 =>    303 ,
        212 =>    302 ,
        211 =>    301 ,
        206 =>    206 ,
        205 =>    205 ,
        204 =>    204 ,
        203 =>    203 ,
        202 =>    202 ,
        201 =>    201 ,
        140 =>     32 ,
        120 =>     26 ,
        110 =>     25 ,
        100 =>     24 ,
          4 =>     20 ,
          6 =>     16 ,
          3 =>     10 ,
          2 =>      3 ,
          1 =>      2 ,
          5 =>      1 ,
      );
      // tables dont il faut adapter champ et valeurs
      $tab_tables = array( 'sacoche_groupe' , 'sacoche_referentiel' , 'sacoche_referentiel_domaine');
      $niveau_partage_max_avant = 215;
      $niveau_partage_max_apres = 999999;
      $niveau_partage_max_diff  = $niveau_partage_max_apres - $niveau_partage_max_avant;
      foreach($tab_tables as $table_nom)
      {
        DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE '.$table_nom.' CHANGE niveau_id niveau_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0 ' );
        DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE '.$table_nom.' SET niveau_id = niveau_id + '.$niveau_partage_max_diff.' WHERE niveau_id > '.$niveau_partage_max_avant );
        $DB_SQL = 'SELECT DISTINCT niveau_id FROM '.$table_nom.' WHERE niveau_id <= '.$niveau_partage_max_avant;
        $DB_COL = DB::queryCol(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL);
        if(!empty($DB_COL))
        {
          foreach($tab_id_niveaux as $niveau_id_avant => $niveau_id_apres)
          {
            if(in_array($niveau_id_avant,$DB_COL))
            {
              DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE '.$table_nom.' SET niveau_id = '.$niveau_id_apres.' WHERE niveau_id = '.$niveau_id_avant );
            }
          }
        }
      }
      // mémoriser les niveaux spécifiques et les niveaux actifs
      $DB_SQL = 'SELECT niveau_id FROM sacoche_niveau WHERE niveau_actif = 1 AND niveau_id <= '.$niveau_partage_max_avant;
      $DB_COL_actifs = DB::queryCol(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL);
      $DB_SQL = 'SELECT niveau_id , niveau_ref , niveau_nom FROM sacoche_niveau WHERE niveau_id > '.$niveau_partage_max_avant;
      $DB_TAB_persos = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL);
      // nouvelle table des niveaux (passage de 106 à 2035 niveaux !)
      $reload_sacoche_niveau = TRUE;
      $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_niveau.sql');
      DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes ); // Attention, sur certains LCS ça bloque au dela de 40 instructions MySQL (mais un INSERT multiple avec des milliers de lignes ne pose pas de pb).
      DB::close(SACOCHE_STRUCTURE_BD_NAME);
      // remise en place des niveaux spécifiques et des niveaux actifs
      if(!empty($DB_COL_actifs))
      {
        foreach($DB_COL_actifs as $niveau_id_avant)
        {
          $niveau_id_apres = $tab_id_niveaux[$niveau_id_avant];
          DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_niveau SET niveau_actif = 1 WHERE niveau_id = '.$niveau_id_apres );
        }
      }
      if(!empty($DB_TAB_persos))
      {
        $DB_SQL = 'INSERT INTO sacoche_niveau(niveau_id, niveau_actif, niveau_famille_id, niveau_ordre, niveau_ref, code_mef, niveau_nom) ';
        $DB_SQL.= 'VALUES(                   :niveau_id,:niveau_actif,:niveau_famille_id,:niveau_ordre,:niveau_ref,:code_mef,:niveau_nom)';
        foreach($DB_TAB_persos as $DB_ROW)
        {
          $DB_VAR = array(
            ':niveau_id'         => $DB_ROW['niveau_id'] + $niveau_partage_max_diff,
            ':niveau_actif'      => 1,
            ':niveau_famille_id' => 0,
            ':niveau_ordre'      => 999,
            ':niveau_ref'        => $DB_ROW['niveau_ref'],
            ':code_mef'          => "",
            ':niveau_nom'        => $DB_ROW['niveau_nom'],
          );
          DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
        }
      }
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2015-07-03 => 2015-08-16
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($version_base_structure_actuelle=='2015-07-03')
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2015-08-16';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // Modification champ sacoche_user
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user CHANGE user_param_accueil user_param_accueil VARCHAR(127) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT "user,alert,messages,previsions,resultats,faiblesses,reussites,demandes,saisies,officiel,socle,help,ecolo" ' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_user SET user_param_accueil=CONCAT(user_param_accueil,",previsions") ' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// MAJ 2015-08-16 => 2015-08-22
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($version_base_structure_actuelle=='2015-08-16') || ($version_base_structure_actuelle=='2015-08-17') ) // Le numéro dans le fichier VERSION_BASE_STRUCTURE ne correspondait pas à la valeur de ce fichier.
{
  if($version_base_structure_actuelle==DB_STRUCTURE_MAJ_BASE::DB_version_base())
  {
    $version_base_structure_actuelle = '2015-08-22';
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_base_structure_actuelle.'" WHERE parametre_nom="version_base"' );
    // Substitution du champ [message_destinataires] de la table [sacoche_message] par une table de jointure [sacoche_jointure_message_destinataire]
    # On charge la nouvelle table
    $reload_sacoche_jointure_message_destinataire = TRUE;
    $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.'sacoche_jointure_message_destinataire.sql');
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
    DB::close(SACOCHE_STRUCTURE_BD_NAME);
    # On en profite pour supprimer les notifications périmées qui passaient par [sacoche_message] jusqu'en mars 2015.
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_message WHERE DATE_ADD(message_fin_date,INTERVAL 3 MONTH)<NOW() AND message_contenu LIKE "Fiches brevet - %"' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_message WHERE DATE_ADD(message_fin_date,INTERVAL 3 MONTH)<NOW() AND message_contenu LIKE "Relevé d\'évaluations - %"' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_message WHERE DATE_ADD(message_fin_date,INTERVAL 3 MONTH)<NOW() AND message_contenu LIKE "Bulletin scolaire - %"' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_message WHERE DATE_ADD(message_fin_date,INTERVAL 3 MONTH)<NOW() AND message_contenu LIKE "Maîtrise du palier 1 - %"' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_message WHERE DATE_ADD(message_fin_date,INTERVAL 3 MONTH)<NOW() AND message_contenu LIKE "Maîtrise du palier 2 - %"' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_message WHERE DATE_ADD(message_fin_date,INTERVAL 3 MONTH)<NOW() AND message_contenu LIKE "Maîtrise du palier 3 - %"' );
    // D'autres entrées trouvées, probable reliquat d'un bug...
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_message WHERE DATE_ADD(message_fin_date,INTERVAL 3 MONTH)<NOW() AND message_contenu LIKE "ches brevet - %"' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_message WHERE DATE_ADD(message_fin_date,INTERVAL 3 MONTH)<NOW() AND message_contenu LIKE "levé d\'évaluations - %"' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_message WHERE DATE_ADD(message_fin_date,INTERVAL 3 MONTH)<NOW() AND message_contenu LIKE "lletin scolaire - %"' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_message WHERE DATE_ADD(message_fin_date,INTERVAL 3 MONTH)<NOW() AND message_contenu LIKE "îtrise du palier 1 - %"' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_message WHERE DATE_ADD(message_fin_date,INTERVAL 3 MONTH)<NOW() AND message_contenu LIKE "îtrise du palier 2 - %"' );
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DELETE FROM sacoche_message WHERE DATE_ADD(message_fin_date,INTERVAL 3 MONTH)<NOW() AND message_contenu LIKE "îtrise du palier 3 - %"' );
    # On récupère les destinataires des messages en cours, ainsi que leur profil, afin de les y transposer
    $DB_SQL = 'SELECT message_id, message_destinataires FROM sacoche_message ';
    $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
    if(!empty($DB_TAB))
    {
      $tab_user_all = array();
      $tab_user_message = array();
      foreach($DB_TAB as $DB_ROW)
      {
        $tab_user_message[$DB_ROW['message_id']] = explode(',',mb_substr($DB_ROW['message_destinataires'],1,-1));
        $tab_user_all = array_merge($tab_user_all, $tab_user_message[$DB_ROW['message_id']]);
      }
      $tab_user_all = array_unique($tab_user_all);
      $listing_user_id = implode(',',$tab_user_all);
      $DB_SQL = 'SELECT user_id, user_profil_type FROM sacoche_user LEFT JOIN sacoche_user_profil USING (user_profil_sigle) WHERE user_id IN('.$listing_user_id.') ';
      $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL, TRUE, TRUE);
      $DB_SQL = 'INSERT INTO sacoche_jointure_message_destinataire( message_id, user_profil_type, destinataire_type, destinataire_id) ';
      $DB_SQL.= 'VALUES(                                           :message_id,:user_profil_type,:destinataire_type,:destinataire_id)';
      foreach($tab_user_message as $message_id => $tab_user)
      {
        foreach($tab_user as $user_id)
        {
          if(isset($DB_TAB[$user_id]))
          {
            $DB_VAR = array(
              ':message_id'        => $message_id,
              ':user_profil_type'  => $DB_TAB[$user_id]['user_profil_type'],
              ':destinataire_type' => 'user',
              ':destinataire_id'   => $user_id,
            );
            DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
          }
        }
      }
    }
    DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_message DROP message_destinataires ' );
  }
}

?>
