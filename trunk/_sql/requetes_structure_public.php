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
 * @param string $mode_connection   'normal' | 'cas' | 'shibboleth' | 'siecle' | 'vecteur_parent' | 'gepi' | ...
 * @param string $user_identifiant
 * @param string $parent_nom      facultatif, seulement pour $mode_connection = 'vecteur_parent'
 * @param string $parent_prenom   facultatif, seulement pour $mode_connection = 'vecteur_parent'
 * @return array
 */
public static function DB_recuperer_donnees_utilisateur($mode_connection,$user_identifiant,$parent_nom='',$parent_prenom='')
{
  switch($mode_connection)
  {
    case 'normal'         : $champ = 'user_login';     break;
    case 'cas'            : $champ = 'user_id_ent';    break;
    case 'shibboleth'     : $champ = 'user_id_ent';    break;
    case 'siecle'         : $champ = 'user_sconet_id'; break;
    case 'vecteur_parent' : $champ = 'user_id';        break; // C'est le user_sconet_id de l'élève qui est transmis, mais le user_id du parent trouvé qui est finalement utilisé dans la requête.
    case 'gepi'           : $champ = 'user_id_gepi';   break;
  }
  if($mode_connection=='vecteur_parent')
  {
    // On cherche le parent à partir de l'Id Sconet de l'enfant
    // LIKE utilisé pour la restriction sur nom / prénom afin d'essayer d'éviter des pbs potentiels de prénoms composés ou de noms patronymiques non uniformisés...
    $DB_SQL = 'SELECT parent.user_id ';
    $DB_SQL.= 'FROM sacoche_user AS eleve ';
    $DB_SQL.= 'LEFT JOIN sacoche_jointure_parent_eleve ON eleve.user_id=sacoche_jointure_parent_eleve.eleve_id ';
    $DB_SQL.= 'LEFT JOIN sacoche_user AS parent ON sacoche_jointure_parent_eleve.parent_id=parent.user_id ';
    $DB_SQL.= 'WHERE eleve.user_sconet_id=:eleve_sconet_id AND parent.user_nom LIKE :parent_nom_like AND parent.user_prenom LIKE :parent_prenom_like ';
    $DB_VAR = array( ':eleve_sconet_id'=>$user_identifiant , ':parent_nom_like'=>'%'.$parent_nom.'%' , ':parent_prenom_like'=>'%'.$parent_prenom.'%' );
    $user_identifiant = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
    if(empty($user_identifiant)) return NULL;
  }
  $DB_SQL = 'SELECT sacoche_user.*, sacoche_user_profil.*, sacoche_groupe.groupe_nom, ';
  $DB_SQL.= 'TIME_TO_SEC(TIMEDIFF(NOW(),sacoche_user.user_tentative_date)) AS delai_tentative_secondes, '; // TIMEDIFF() est plafonné à 839h, soit ~35j, mais peu importe ici.
  $DB_SQL.= 'TIME_TO_SEC(TIMEDIFF(NOW(),sacoche_user.user_connexion_date)) AS delai_connexion_secondes  '; // TIMEDIFF() est plafonné à 839h, soit ~35j, mais peu importe ici.
  $DB_SQL.= 'FROM sacoche_user ';
  $DB_SQL.= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
  $DB_SQL.= 'LEFT JOIN sacoche_groupe ON sacoche_user.eleve_classe_id=sacoche_groupe.groupe_id ';
  $DB_SQL.= 'WHERE '.$champ.'=:identifiant ';
  // LIMIT 1 a priori pas utile, et de surcroît queryRow ne renverra qu'une ligne
  $DB_VAR = array(':identifiant'=>$user_identifiant);
  return DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Retourner la version de la base de l'établissement
 *
 * @param void
 * @return string
 */
public static function DB_version_base()
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
public static function DB_lister_parametres($listing_param='')
{
  $DB_SQL = 'SELECT parametre_nom, parametre_valeur ';
  $DB_SQL.= 'FROM sacoche_parametre ';
  $DB_SQL.= ($listing_param) ? 'WHERE parametre_nom IN('.$listing_param.') ' : '' ;
  // Pas de queryRow prévu car toujours au moins 2 paramètres demandés jusqu'à maintenant.
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Modifier la date de connexion ou de tentative de connexion
 *
 * @param string  $champ   'connexion' | 'tentative'
 * @param int     $user_id
 * @return void
 */
public static function DB_enregistrer_date($champ,$user_id)
{
  $DB_SQL = 'UPDATE sacoche_user ';
  $DB_SQL.= 'SET user_'.$champ.'_date=NOW() ';
  $DB_SQL.= 'WHERE user_id=:user_id ';
  $DB_VAR = array(':user_id'=>$user_id);
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

}
?>