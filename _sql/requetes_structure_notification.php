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

// Ces méthodes ne concernent qu'une base STRUCTURE.
// Ces méthodes concernent des actions en lien avec les notifications.

class DB_STRUCTURE_NOTIFICATION extends DB
{

/**
 * Lister les destinataires de notifications
 *
 * @param string   $abonnement_ref
 * @param string   $listing_id   facultatif : liste d'id utilisateurs soit dans laquelle piocher soit à éviter
 * @param bool     $oui_ou_non   facultatif : si le paramètre précédent est renseigné, alors TRUE pour piocher dedans ou FALSE pour piocher ailleurs
 * @return array
 */
public static function DB_lister_destinataires( $abonnement_ref , $listing_id=NULL , $oui_ou_non=TRUE )
{
  $not = ($oui_ou_non) ? '' : 'NOT ' ;
  $where_user_id = ($listing_id) ? 'AND user_id '.$not.'IN('.$listing_id.') ' : '' ;
  $DB_SQL = 'SELECT user_id, user_nom, user_prenom, user_email, jointure_mode ';
  $DB_SQL.= 'FROM sacoche_jointure_user_abonnement ';
  $DB_SQL.= 'LEFT JOIN sacoche_user USING (user_id) ';
  $DB_SQL.= 'WHERE abonnement_ref=:abonnement_ref AND user_sortie_date>NOW() '.$where_user_id;
  $DB_VAR = array( ':abonnement_ref' => $abonnement_ref );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Lister les abonnements pour un profil donné ainsi que les jointures avec un utilisateur
 *
 * @param string   $profil_type
 * @param int      $user_id
 * @return array
 */
public static function DB_lister_abonnements_profil( $profil_type , $user_id )
{
  $DB_SQL = 'SELECT abonnement_ref, abonnement_obligatoire, abonnement_courriel_only, abonnement_descriptif, jointure_mode ';
  $DB_SQL.= 'FROM sacoche_abonnement ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_user_abonnement USING (abonnement_ref) ';
  $DB_SQL.= 'WHERE abonnement_profils LIKE "%'.$profil_type.'%" AND (user_id=:user_id OR user_id IS NULL) ';
  $DB_SQL.= 'ORDER BY abonnement_objet ASC ';
  $DB_VAR = array( ':user_id' => $user_id );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Lister les notifications qu'un utilisateur peut consulter
 *
 * @param int   $user_id
 * @return array
 */
public static function DB_lister_notifications_consultables_for_user( $user_id )
{
  $DB_SQL = 'SELECT notification_id, notification_statut, notification_date, notification_contenu, abonnement_objet ';
  $DB_SQL.= 'FROM sacoche_notification ';
  $DB_SQL.= 'LEFT JOIN sacoche_abonnement USING (abonnement_ref) ';
  $DB_SQL.= 'WHERE user_id=:user_id AND notification_statut IN("consultable","consultée","envoyée") ';
  $DB_SQL.= 'ORDER BY notification_date DESC ';
  $DB_VAR = array( ':user_id' => $user_id );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Compter le nombre de notifications qu'un utilisateur n'a pas consulté
 *
 * @param int   $user_id
 * @return int
 */
public static function DB_compter_notifications_non_vues( $user_id )
{
  $DB_SQL = 'SELECT COUNT(*) AS nombre ';
  $DB_SQL.= 'FROM sacoche_notification ';
  $DB_SQL.= 'WHERE user_id=:user_id AND notification_statut IN("consultable") ';
  $DB_VAR = array( ':user_id' => $user_id );
  return DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Ajouter un abonnement d'un user à une notification
 *
 * @param int      $user_id
 * @param string   $abonnement_ref
 * @param string   $jointure_mode
 * @return void
 */
public static function DB_ajouter_abonnement( $user_id , $abonnement_ref , $jointure_mode )
{
  $DB_SQL = 'INSERT INTO sacoche_jointure_user_abonnement(user_id, abonnement_ref, jointure_mode) ';
  $DB_SQL.= 'VALUES(                                     :user_id,:abonnement_ref,:jointure_mode)';
  $DB_VAR = array(
    ':user_id'        => $user_id,
    ':abonnement_ref' => $abonnement_ref,
    ':jointure_mode'  => $jointure_mode,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Ajouter une notification
 *
 * @param int      $user_id
 * @param string   $abonnement_ref
 * @param string   $notification_attente_id   éventuellement NULL
 * @param string   $notification_statut
 * @param string   $notification_date         éventuellement NULL
 * @param string   $notification_contenu
 * @return void
 */
public static function DB_ajouter_log( $user_id , $abonnement_ref , $notification_attente_id , $notification_statut , $notification_date , $notification_contenu )
{
  $indice_date = ($notification_date==NULL) ? 'NOW()' : ':notification_date' ;
  $DB_SQL = 'INSERT INTO sacoche_notification( user_id, abonnement_ref, notification_attente_id, notification_statut, notification_date, notification_contenu) ';
  $DB_SQL.= 'VALUES                          (:user_id,:abonnement_ref,:notification_attente_id,:notification_statut,'. $indice_date .',:notification_contenu)';
  $DB_VAR = array(
    ':user_id'                 => $user_id,
    ':abonnement_ref'          => $abonnement_ref,
    ':notification_attente_id' => $notification_attente_id,
    ':notification_statut'     => $notification_statut,
    ':notification_date'       => $notification_date,
    ':notification_contenu'    => $notification_contenu,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return DB::getLastOid(SACOCHE_STRUCTURE_BD_NAME);
}

/**
 * Modifier le statut d'une notification
 *
 * @param int      $notification_id
 * @param int      $user_id
 * @return int
 */
public static function DB_modifier_statut( $notification_id , $user_id )
{
  $DB_SQL = 'UPDATE sacoche_notification ';
  $DB_SQL.= 'SET notification_statut=:notification_statut ';
  $DB_SQL.= 'WHERE notification_id=:notification_id AND user_id=:user_id ';
  $DB_VAR = array(
    ':notification_id'     => $notification_id,
    ':user_id'             => $user_id,
    ':notification_statut' => 'consultée',
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return DB::rowCount(SACOCHE_STRUCTURE_BD_NAME);
}

/**
 * Modifier un abonnement d'un user à une notification
 *
 * @param int      $user_id
 * @param string   $abonnement_ref
 * @param string   $jointure_mode
 * @return void
 */
public static function DB_modifier_abonnement( $user_id , $abonnement_ref , $jointure_mode )
{
  $DB_SQL = 'UPDATE sacoche_jointure_user_abonnement ';
  $DB_SQL.= 'SET jointure_mode=:jointure_mode ';
  $DB_SQL.= 'WHERE user_id=:user_id AND abonnement_ref=:abonnement_ref ';
  $DB_VAR = array(
    ':user_id'        => $user_id,
    ':abonnement_ref' => $abonnement_ref,
    ':jointure_mode'  => $jointure_mode,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Supprimer un abonnement d'un user à une notification
 *
 * @param int      $user_id
 * @param string   $abonnement_ref
 * @return void
 */
public static function DB_supprimer_abonnement( $user_id , $abonnement_ref )
{
  $DB_SQL = 'DELETE FROM sacoche_jointure_user_abonnement ';
  $DB_SQL.= 'WHERE user_id=:user_id AND abonnement_ref=:abonnement_ref ';
  $DB_VAR = array(
    ':user_id'        => $user_id,
    ':abonnement_ref' => $abonnement_ref,
  );
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

}
?>