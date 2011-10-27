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
 * @return array($prof_nb,$prof_use,$eleve_nb,$eleve_use,$score_nb)
 */
public function DB_recuperer_statistiques()
{
	// nb professeurs enregistrés ; nb élèves enregistrés
	$DB_SQL = 'SELECT user_profil, COUNT(*) AS nombre ';
	$DB_SQL.= 'FROM sacoche_user ';
	$DB_SQL.= 'WHERE user_statut=1 ';
	$DB_SQL.= 'GROUP BY user_profil';
	$DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL , TRUE , TRUE);
	$prof_nb  = (isset($DB_TAB['professeur'])) ? $DB_TAB['professeur']['nombre'] : 0 ;
	$eleve_nb = (isset($DB_TAB['eleve']))      ? $DB_TAB['eleve']['nombre']      : 0 ;
	// nb professeurs connectés ; nb élèves connectés
	$DB_SQL = 'SELECT user_profil, COUNT(*) AS nombre ';
	$DB_SQL.= 'FROM sacoche_user ';
	$DB_SQL.= 'WHERE user_statut=1 AND user_connexion_date>DATE_SUB(NOW(),INTERVAL 6 MONTH) ';
	$DB_SQL.= 'GROUP BY user_profil';
	$DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL , TRUE , TRUE);
	$prof_use  = (isset($DB_TAB['professeur'])) ? $DB_TAB['professeur']['nombre'] : 0 ;
	$eleve_use = (isset($DB_TAB['eleve']))      ? $DB_TAB['eleve']['nombre']      : 0 ;
	// nb notes saisies
	$DB_SQL = 'SELECT COUNT(*) AS nombre ';
	$DB_SQL.= 'FROM sacoche_saisie';
	$DB_ROW = DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
	$score_nb = $DB_ROW['nombre'];
	// Retour
	return array($prof_nb,$prof_use,$eleve_nb,$eleve_use,$score_nb);
}

/**
 * Retourner au webmestre l'identité d'un administrateur (mono ou multi structures)
 *
 * @param int   $admin_id
 * @return array
 */
public function DB_recuperer_admin_identite($admin_id)
{
	$DB_SQL = 'SELECT user_nom,user_prenom,user_login ';
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
public function DB_modifier_admin_mdp($admin_id,$password_crypte)
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
public function DB_supprimer_tables_structure()
{
	$tab_tables = array();
	$DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME,'SHOW TABLE STATUS LIKE "sacoche_%"');
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_tables[] = $DB_ROW['Name'];
	}
	DB::query(SACOCHE_STRUCTURE_BD_NAME , 'DROP TABLE '.implode(', ',$tab_tables) );
}

}
?>