<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010
 *
 * ****************************************************************************************************
 * SACoche <http://sacoche.sesamath.net> - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath <http://www.sesamath.net> - Tous droits réservés.
 * Logiciel placé sous la licence libre GPL 3 <http://www.rodage.org/gpl-3.0.fr.html>.
 * ****************************************************************************************************
 *
 * Ce fichier est une partie de SACoche.
 *
 * SACoche est un logiciel libre ; vous pouvez le redistribuer ou le modifier suivant les termes 
 * de la “GNU General Public License” telle que publiée par la Free Software Foundation :
 * soit la version 3 de cette licence, soit (à votre gré) toute version ultérieure.
 *
 * SACoche est distribué dans l’espoir qu’il vous sera utile, mais SANS AUCUNE GARANTIE :
 * sans même la garantie implicite de COMMERCIALISABILITÉ ni d’ADÉQUATION À UN OBJECTIF PARTICULIER.
 * Consultez la Licence Générale Publique GNU pour plus de détails.
 *
 * Vous devriez avoir reçu une copie de la Licence Générale Publique GNU avec SACoche ;
 * si ce n’est pas le cas, consultez : <http://www.gnu.org/licenses/>.
 *
 */
 
// Extension de classe qui étend DB (pour permettre l'autoload)

// Ces méthodes ne concernent qu'une base STRUCTURE.
// Ces méthodes ne concernent que le webmestre.

class DB_STRUCTURE_WEBMESTRE extends DB
{

/**
 * Retourner au webmestre les statistiques d'un établissement (mono ou multi structures)
 *
 * @param void
 * @return array($prof_nb,$prof_use,$eleve_nb,$eleve_use,$score_nb,$connexion_nom)
 */
public static function DB_recuperer_statistiques()
{
  // La révision du 30 mars 2012 a fusionné les champs "user_statut" et "user_statut_date" en "user_sortie_date".
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , 'SHOW COLUMNS FROM sacoche_user LIKE "user_sortie_date"' , NULL);
  $test_sortie = (!empty($DB_TAB)) ? 'user_sortie_date>NOW()' : 'user_statut=1' ;
  // La révision du 5 janvier 2013 a modifié le champ "user_profil" en "user_profil_sigle".
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , 'SHOW COLUMNS FROM sacoche_user LIKE "user_profil_sigle"' , NULL);
  $champ_profil = (!empty($DB_TAB)) ? 'user_profil_type' : 'user_profil' ;
  $left_join    = (!empty($DB_TAB)) ? 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ' : '' ;
  // nb professeurs enregistrés ; nb élèves enregistrés
  $DB_SQL = 'SELECT '.$champ_profil.', COUNT(*) AS nombre ';
  $DB_SQL.= 'FROM sacoche_user ';
  $DB_SQL.= $left_join;
  $DB_SQL.= 'WHERE '.$test_sortie.' ';
  $DB_SQL.= 'GROUP BY '.$champ_profil;
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL , TRUE , TRUE);
  $prof_nb  = (isset($DB_TAB['professeur'])) ? $DB_TAB['professeur']['nombre'] : 0 ;
  $eleve_nb = (isset($DB_TAB['eleve']))      ? $DB_TAB['eleve']['nombre']      : 0 ;
  // nb professeurs connectés ; nb élèves connectés
  $DB_SQL = 'SELECT '.$champ_profil.', COUNT(*) AS nombre ';
  $DB_SQL.= 'FROM sacoche_user ';
  $DB_SQL.= $left_join;
  $DB_SQL.= 'WHERE '.$test_sortie.' AND user_connexion_date>DATE_SUB(NOW(),INTERVAL 6 MONTH) ';
  $DB_SQL.= 'GROUP BY '.$champ_profil;
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL , TRUE , TRUE);
  $prof_use  = (isset($DB_TAB['professeur'])) ? $DB_TAB['professeur']['nombre'] : 0 ;
  $eleve_use = (isset($DB_TAB['eleve']))      ? $DB_TAB['eleve']['nombre']      : 0 ;
  // nb notes saisies
  $DB_SQL = 'SELECT COUNT(*) AS nombre ';
  $DB_SQL.= 'FROM sacoche_saisie';
  $DB_ROW = DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  $score_nb = $DB_ROW['nombre'];
  // info de connexion
  $DB_SQL = 'SELECT parametre_valeur ';
  $DB_SQL.= 'FROM sacoche_parametre ';
  $DB_SQL.= 'WHERE parametre_nom ="connexion_nom" ';
  $connexion_nom = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  // Retour
  return array($prof_nb,$prof_use,$eleve_nb,$eleve_use,$score_nb,$connexion_nom);
}

/**
 * Retourner au webmestre l'identité d'un administrateur (mono ou multi structures)
 *
 * @param int   $admin_id
 * @return array
 */
public static function DB_recuperer_admin_identite($admin_id)
{
  $DB_SQL = 'SELECT user_nom, user_prenom, user_login ';
  $DB_SQL.= 'FROM sacoche_user ';
  $DB_SQL.= 'WHERE user_id=:admin_id ';
  $DB_VAR = array(':admin_id'=>$admin_id);
  return DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Modifier le mdp d'un administrateur
 *
 * @param int     $admin_id
 * @param string  $password_crypte
 * @return void
 */
public static function DB_modifier_admin_mdp($admin_id,$password_crypte)
{
  $DB_SQL = 'UPDATE sacoche_user ';
  $DB_SQL.= 'SET user_password=:password_crypte ';
  $DB_SQL.= 'WHERE user_id=:user_id ';
  $DB_VAR = array(':user_id'=>$admin_id,':password_crypte'=>$password_crypte);
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Supprimer les tables d'une installation mono-structure (mais pas la base elle-même, au cas où elle serait partagée avec autre chose)
 *
 * @param void
 * @return void
 */
public static function DB_supprimer_tables_structure()
{
  $tab_tables = array();
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME,'SHOW TABLE STATUS LIKE "sacoche_%"');
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_tables[] = $DB_ROW['Name'];
  }
  DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DROP TABLE '.implode(', ',$tab_tables) );
}

/**
 * Retourner un tableau [valeur texte] des administrateurs (forcément actuels) de l'établissement
 *
 * @param void
 * @return array|string
 */
public static function DB_OPT_administrateurs_etabl()
{
  // La révision du 5 janvier 2013 a modifié le champ "user_profil" en "user_profil_sigle".
  $DB_Test = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , 'SHOW COLUMNS FROM sacoche_user LIKE "user_profil_sigle"' , NULL);
  $champ_profil = !empty($DB_Test) ? 'user_profil_type' : 'user_profil' ;
  $left_join    = !empty($DB_Test) ? 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ' : '' ;
  // La révision du 22 février 2013 a ajouté le champ "user_email".
  $DB_Test = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , 'SHOW COLUMNS FROM sacoche_user LIKE "user_email"' , NULL);
  $select_texte = !empty($DB_Test) ? 'CONCAT(user_nom," ",user_prenom," (",user_email,")")' : 'CONCAT(user_nom," ",user_prenom)' ;
  // Passons à la requête en question
  $DB_SQL = 'SELECT user_id AS valeur, '.$select_texte.' AS texte ';
  $DB_SQL.= 'FROM sacoche_user ';
  $DB_SQL.= $left_join;
  $DB_SQL.= 'WHERE '.$champ_profil.'=:profil '; // AND user_sortie_date>NOW() est inutile pour les admins, et évite une erreur qd cette fonction est appelée via un webmestre multi-structures alors que la base de l'établ n'est pas à jour
  $DB_SQL.= 'ORDER BY user_nom ASC, user_prenom ASC';
  $DB_VAR = array(':profil'=>'administrateur');
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return !empty($DB_TAB) ? $DB_TAB : 'Aucun administrateur enregistré !' ;
}

}
?>