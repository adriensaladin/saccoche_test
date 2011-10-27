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
// Ces méthodes ne concernent que les utilisateurs non identifiés (sauf pour DB_version_base() lors de la MAJ d'une base après restauration).

class DB_STRUCTURE_PUBLIC extends DB
{

/**
 * Récuperer, à partir d'un identifiant, les données d'un utilisateur tentant de se connecter (le mdp est comparé ensuite)
 *
 * @param string $mode_connection   'normal' | 'cas' | 'gepi' | ...
 * @param string $login
 * @return array
 */
public function DB_recuperer_donnees_utilisateur($mode_connection,$login)
{
	switch($mode_connection)
	{
		case 'normal' : $champ = 'user_login';   break;
		case 'cas'    : $champ = 'user_id_ent';  break;
		case 'gepi'   : $champ = 'user_id_gepi'; break;
	}
	$DB_SQL = 'SELECT sacoche_user.*, sacoche_groupe.groupe_nom, ';
	$DB_SQL.= 'UNIX_TIMESTAMP(sacoche_user.user_tentative_date) AS tentative_unix ';
	$DB_SQL.= 'FROM sacoche_user ';
	$DB_SQL.= 'LEFT JOIN sacoche_groupe ON sacoche_user.eleve_classe_id=sacoche_groupe.groupe_id ';
	$DB_SQL.= 'WHERE '.$champ.'=:identifiant ';
	// LIMIT 1 a priori pas utile, et de surcroît queryRow ne renverra qu'une ligne
	$DB_VAR = array(':identifiant'=>$login);
	return DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Retourner la version de la base de l'établissement
 *
 * @param void
 * @return string
 */
public function DB_version_base()
{
	$DB_SQL = 'SELECT parametre_valeur ';
	$DB_SQL.= 'FROM sacoche_parametre ';
	$DB_SQL.= 'WHERE parametre_nom=:parametre_nom ';
	$DB_VAR = array(':parametre_nom'=>'version_base');
	return DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Lister des paramètres d'une structure (contenu de la table 'sacoche_parametre')
 *
 * @param string   $listing_param   nom des paramètres entourés de guillemets et séparés par des virgules (tout si rien de transmis)
 * @return array
 */
public function DB_lister_parametres($listing_param='')
{
	$DB_SQL = 'SELECT parametre_nom, parametre_valeur ';
	$DB_SQL.= 'FROM sacoche_parametre ';
	$DB_SQL.= ($listing_param) ? 'WHERE parametre_nom IN('.$listing_param.') ' : '' ;
	// Pas de queryRow prévu car toujours au moins 2 paramètres demandés jusqu'à maintenant.
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Modifier la date de connexion ou de tentative de connexion d'un utilisateur donné
 *
 * @param string  $champ   'connexion' ou 'tentative'
 * @param int     $user_id
 * @return void
 */
public function DB_modifier_date($champ,$user_id)
{
	$DB_SQL = 'UPDATE sacoche_user ';
	$DB_SQL.= 'SET user_'.$champ.'_date=NOW() ';
	$DB_SQL.= 'WHERE user_id=:user_id ';
	$DB_VAR = array(':user_id'=>$user_id);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

}
?>