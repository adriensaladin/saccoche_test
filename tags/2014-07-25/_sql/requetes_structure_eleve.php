<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010-2014
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
// Ces méthodes ne concernent que les élèves (et les parents).

class DB_STRUCTURE_ELEVE extends DB
{

/**
 * compter_demandes_evaluation
 *
 * @param int  $eleve_id
 * @return array
 */
public static function DB_compter_demandes_evaluation($eleve_id)
{
  $DB_SQL = 'SELECT demande_statut, COUNT(demande_id) AS nombre ';
  $DB_SQL.= 'FROM sacoche_demande ';
  $DB_SQL.= 'WHERE eleve_id=:eleve_id ';
  $DB_SQL.= 'GROUP BY demande_statut ';
  $DB_VAR = array(':eleve_id'=>$eleve_id);
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR , TRUE , TRUE);
}

/**
 * Récupérer le nombre de demandes d'évaluations autorisées par matière
 *
 * @param int   $matiere_id
 * @return int
 */
public static function DB_recuperer_demandes_autorisees_matiere($matiere_id)
{
  $DB_SQL = 'SELECT matiere_nb_demandes ';
  $DB_SQL.= 'FROM sacoche_matiere ';
  $DB_SQL.= 'WHERE matiere_id=:matiere_id ';
  $DB_VAR = array(':matiere_id'=>$matiere_id);
  return DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Récupérer les informations relatives à un item donné
 *
 * @param int   $item_id
 * @return array
 */
public static function DB_recuperer_item_infos($item_id)
{
  $DB_SQL = 'SELECT item_nom, item_cart, ';
  $DB_SQL.= 'CONCAT(matiere_ref,".",niveau_ref,".",domaine_ref,theme_ordre,item_ordre) AS item_ref ';
  $DB_SQL.= 'FROM sacoche_referentiel_item ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (theme_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (domaine_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $DB_SQL.= 'WHERE item_id=:item_id ';
  $DB_VAR = array(':item_id'=>$item_id);
  return DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Lister les évaluations concernant un élève sur une période donnée
 *
 * @param int    $devoir_id
 * @return array
 */
public static function DB_recuperer_devoir_infos($devoir_id)
{
  $DB_SQL = 'SELECT prof_id, devoir_date, devoir_info, devoir_visible_date, devoir_autoeval_date, devoir_partage ';
  $DB_SQL.= 'FROM sacoche_devoir ';
  $DB_SQL.= 'WHERE devoir_id=:devoir_id ';
  $DB_VAR = array(':devoir_id'=>$devoir_id);
  return DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Retourner une liste de professeurs attachés à un élève identifié et une matière donnée.
 *
 * @param int $eleve_id
 * @param int $matiere_id
 * @return array
 */
public static function DB_recuperer_professeurs_eleve_matiere($eleve_id,$matiere_id)
{
  // Lever si besoin une limitation de GROUP_CONCAT (group_concat_max_len est par défaut limité à une chaine de 1024 caractères) ; éviter plus de 8096 (http://www.glpi-project.org/forum/viewtopic.php?id=23767).
  DB::query(SACOCHE_STRUCTURE_BD_NAME , 'SET group_concat_max_len = 8096');
  // On connait la classe ($_SESSION['ELEVE_CLASSE_ID']), donc on commence par récupérer les groupes éventuels associés à l'élève
  $DB_SQL = 'SELECT GROUP_CONCAT(DISTINCT groupe_id SEPARATOR ",") AS sacoche_liste_groupe_id ';
  $DB_SQL.= 'FROM sacoche_jointure_user_groupe ';
  $DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
  $DB_SQL.= 'WHERE user_id=:user_id AND groupe_type=:type2 ';
  $DB_SQL.= 'GROUP BY user_id ';
  $DB_VAR = array(':user_id'=>$eleve_id,':type2'=>'groupe');
  $liste_groupe_id = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  if( (!$_SESSION['ELEVE_CLASSE_ID']) && (!$liste_groupe_id) )
  {
    // élève sans classe et sans groupe
    return FALSE;
  }
  if(!$liste_groupe_id)
  {
    $liste_groupes = $_SESSION['ELEVE_CLASSE_ID'];
  }
  elseif(!$_SESSION['ELEVE_CLASSE_ID'])
  {
    $liste_groupes = $liste_groupe_id;
  }
  else
  {
    $liste_groupes = $_SESSION['ELEVE_CLASSE_ID'].','.$liste_groupe_id;
  }
  // Maintenant qu'on a la matière et la classe / les groupes, on cherche les profs à la fois dans sacoche_jointure_user_matiere et sacoche_jointure_user_groupe .
  // On part de sacoche_jointure_user_matiere qui ne contient que des profs.
  $DB_SQL = 'SELECT DISTINCT(user_id), user_nom, user_prenom ';
  $DB_SQL.= 'FROM sacoche_jointure_user_matiere ';
  $DB_SQL.= 'LEFT JOIN sacoche_user USING (user_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_user_groupe USING (user_id) ';
  $DB_SQL.= 'WHERE matiere_id=:matiere_id AND groupe_id IN('.$liste_groupes.') AND user_sortie_date>NOW() ';
  $DB_SQL.= 'ORDER BY user_nom ASC, user_prenom ASC';
  $DB_VAR = array(':matiere_id'=>$matiere_id);
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Lister les classes des élèves associés à un parent
 *
 * @param int $parent_id
 * @return array
 */
public static function DB_lister_classes_parent($parent_id)
{
  $DB_SQL = 'SELECT groupe_id, groupe_nom, groupe_type ';
  $DB_SQL.= 'FROM sacoche_jointure_parent_eleve ';
  $DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_jointure_parent_eleve.eleve_id=sacoche_user.user_id ';
  $DB_SQL.= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_user_groupe ON eleve_classe_id=groupe_id ';
  $DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $DB_SQL.= 'WHERE parent_id=:parent_id AND user_profil_type=:profil_type AND user_sortie_date>NOW() ';
  $DB_SQL.= 'GROUP BY groupe_id '; // si plusieurs enfants dans la même classe
  $DB_SQL.= 'ORDER BY groupe_type ASC, niveau_ordre ASC, groupe_nom ASC';
  $DB_VAR = array(':parent_id'=>$parent_id,':profil_type'=>'eleve');
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Retourner les résultats pour un élève, pour des items donnés
 *
 * @param int    $eleve_id
 * @param string $liste_item_id   id des items séparés par des virgules
 * @param string $user_profil_type
 * @return array
 */
public static function DB_lister_result_eleve_items($eleve_id,$liste_item_id,$user_profil_type)
{
  // Cette fonction peut être appelée avec un autre profil.
  $sql_view = ( ($user_profil_type=='eleve') || ($user_profil_type=='parent') ) ? 'AND saisie_visible_date<=NOW() ' : '' ;
  $DB_SQL = 'SELECT item_id, saisie_note AS note ';
  $DB_SQL.= 'FROM sacoche_saisie ';
  $DB_SQL.= 'WHERE eleve_id=:eleve_id AND item_id IN('.$liste_item_id.') AND saisie_note!="REQ" '.$sql_view;
  $DB_SQL.= 'ORDER BY saisie_date ASC, devoir_id ASC '; // ordre sur devoir_id ajouté à cause des items évalués plusieurs fois le même jour
  $DB_VAR = array(':eleve_id'=>$eleve_id);
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Lister les demandes d'évaluation d'un élève donné
 *
 * @param int    $eleve_id   id de l'élève
 * @return array
 */
public static function DB_lister_demandes_eleve($eleve_id)
{
  $DB_SQL = 'SELECT sacoche_demande.*, ';
  $DB_SQL.= 'CONCAT(niveau_ref,".",domaine_ref,theme_ordre,item_ordre) AS item_ref , ';
  $DB_SQL.= 'item_id , item_nom , item_lien , sacoche_matiere.matiere_id AS matiere_id  , matiere_nom , ';
  $DB_SQL.= 'prof_id , user_nom , user_prenom ';
  $DB_SQL.= 'FROM sacoche_demande ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (item_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (theme_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (domaine_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_matiere ON sacoche_referentiel_domaine.matiere_id=sacoche_matiere.matiere_id ';
  $DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_demande.prof_id=sacoche_user.user_id ';
  $DB_SQL.= 'WHERE eleve_id=:eleve_id ';
  $DB_SQL.= 'ORDER BY sacoche_demande.matiere_id ASC, niveau_ref ASC, domaine_ref ASC, theme_ordre ASC, item_ordre ASC';
  $DB_VAR = array(':eleve_id'=>$eleve_id);
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Récupérer la classe d'un élève
 *
 * @param int   $eleve_id
 * @return int
 */
public static function DB_recuperer_classe_eleve($eleve_id)
{
  $DB_SQL = 'SELECT eleve_classe_id ';
  $DB_SQL.= 'FROM sacoche_user ';
  $DB_SQL.= 'WHERE user_id=:user_id ';
  $DB_VAR = array(':user_id'=>$eleve_id);
  return DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Lister les évaluations concernant la classe ou les groupes d'un élève sur une période donnée
 *
 * @param int    $eleve_id
 * @param int    $classe_id   id de la classe de l'élève ; en effet sacoche_jointure_user_groupe ne contient que les liens aux groupes, donc il faut tester aussi la classe
 * @param string $date_debut_mysql
 * @param string $date_fin_mysql
 * @param string $user_profil_type
 * @return array
 */
public static function DB_lister_devoirs_groupes_eleve($eleve_id,$classe_id,$date_debut_mysql,$date_fin_mysql,$user_profil_type)
{
  // Cette fonction peut être appelée avec un autre profil.
  $sql_view = ( ($user_profil_type=='eleve') || ($user_profil_type=='parent') ) ? 'AND devoir_visible_date<=NOW() ' : '' ;
  $where_classe = ($classe_id) ? 'sacoche_devoir.groupe_id='.$classe_id.' OR ' : '';
  $DB_SQL = 'SELECT sacoche_devoir.* , sacoche_user.user_nom AS prof_nom , sacoche_user.user_prenom AS prof_prenom ';
  $DB_SQL.= 'FROM sacoche_devoir ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_user_groupe USING (groupe_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_devoir.prof_id=sacoche_user.user_id ';
  $DB_SQL.= 'WHERE ('.$where_classe.'sacoche_jointure_user_groupe.user_id=:eleve_id) ';
  $DB_SQL.= 'AND devoir_date>="'.$date_debut_mysql.'" AND devoir_date<="'.$date_fin_mysql.'" '.$sql_view ;
  $DB_SQL.= 'GROUP BY devoir_id ';
  $DB_SQL.= 'ORDER BY devoir_date DESC, devoir_id DESC '; // ordre sur devoir_id ajouté pour conserver une logique à l'affichage en cas de plusieurs devoirs effectués le même jour
  $DB_VAR = array(':eleve_id'=>$eleve_id);
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Lister les évaluations concernant un élève donné sur les derniers jours
 *
 * @param int    $eleve_id
 * @param int    $nb_jours
 * @return array
 */
public static function DB_lister_derniers_devoirs_eleve_avec_notes_saisies($eleve_id,$nb_jours)
{
  $sql_view = 'AND devoir_visible_date<=NOW() '; // Cette fonction n'est appelée qu'avec un profil élève ou parent
  $DB_SQL = 'SELECT devoir_id , devoir_date , devoir_info , sacoche_user.user_nom AS prof_nom , sacoche_user.user_prenom AS prof_prenom ';
  $DB_SQL.= 'FROM sacoche_saisie ';
  $DB_SQL.= 'LEFT JOIN sacoche_devoir USING (devoir_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_devoir.prof_id=sacoche_user.user_id ';
  $DB_SQL.= 'WHERE eleve_id=:eleve_id AND saisie_note!="REQ" ';
  $DB_SQL.= 'AND DATE_ADD(devoir_date,INTERVAL :nb_jours DAY)>NOW() '.$sql_view ;
  $DB_SQL.= 'GROUP BY devoir_id ';
  $DB_SQL.= 'ORDER BY devoir_date DESC, devoir_id DESC '; // ordre sur devoir_id ajouté pour conserver une logique à l'affichage en cas de plusieurs devoirs effectués le même jour
  $DB_VAR = array(':eleve_id'=>$eleve_id,':nb_jours'=>$nb_jours);
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Lister les évaluations concernant un élève donné comportant uen auto-évaluation en cours
 *
 * @param int    $eleve_id
 * @param int    $classe_id   id de la classe de l'élève ; en effet sacoche_jointure_user_groupe ne contient que les liens aux groupes, donc il faut tester aussi la classe
 * @return array
 */
public static function DB_lister_devoirs_eleve_avec_autoevaluation_en_cours($eleve_id,$classe_id)
{
  $sql_view = 'AND devoir_visible_date<=NOW() '; // Cette fonction n'est appelée qu'avec un profil élève ou parent
  $where_classe = ($classe_id) ? 'sacoche_devoir.groupe_id='.$classe_id.' OR ' : '';
  $DB_SQL = 'SELECT devoir_id , devoir_date , devoir_info , sacoche_user.user_nom AS prof_nom , sacoche_user.user_prenom AS prof_prenom ';
  $DB_SQL.= 'FROM  sacoche_devoir ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_user_groupe USING (groupe_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_devoir.prof_id=sacoche_user.user_id ';
  $DB_SQL.= 'WHERE ('.$where_classe.'sacoche_jointure_user_groupe.user_id=:eleve_id) ';
  $DB_SQL.= 'AND devoir_autoeval_date IS NOT NULL AND devoir_autoeval_date >= NOW() '.$sql_view ;
  $DB_SQL.= 'GROUP BY devoir_id ';
  $DB_SQL.= 'ORDER BY devoir_date DESC, devoir_id DESC '; // ordre sur devoir_id ajouté pour conserver une logique à l'affichage en cas de plusieurs devoirs effectués le même jour
  $DB_VAR = array(':eleve_id'=>$eleve_id);
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Lister les évaluations concernant un élève sur les derniers jours
 *
 * @param int    $eleve_id
 * @param int    $nb_jours
 * @return array
 */
public static function DB_lister_derniers_resultats_eleve($eleve_id,$nb_jours)
{
  $sql_view = 'AND saisie_visible_date<=NOW() '; // Cette fonction n'est appelée qu'avec un profil élève ou parent
  $DB_SQL = 'SELECT item_id , item_nom , saisie_date , saisie_note , ';
  $DB_SQL.= 'CONCAT(matiere_ref,".",niveau_ref,".",domaine_ref,theme_ordre,item_ordre) AS item_ref , ';
  $DB_SQL.= 'matiere_id , niveau_id , matiere_nom ';
  $DB_SQL.= 'FROM sacoche_saisie ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (item_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (theme_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (domaine_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $DB_SQL.= 'WHERE eleve_id=:eleve_id ';
  $DB_SQL.= 'AND DATE_ADD(saisie_date,INTERVAL :nb_jours DAY)>NOW() '.$sql_view ;
  // Pas de 'GROUP BY item_id ' car le regroupement est effectué avant le tri par date
  $DB_SQL.= 'ORDER BY saisie_date DESC, devoir_id DESC '; // ordre sur devoir_id ajouté pour conserver une logique à l'affichage en cas de plusieurs devoirs effectués le même jour
  $DB_VAR = array(':eleve_id'=>$eleve_id,':nb_jours'=>$nb_jours);
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR , TRUE); // TRUE permet d'avoir item_id en clef et, pour un item qui ressortirait plusieurs fois, d'avoir la dernière saisie en [item_id][0]
}

/**
 * Retourner les items d'un devoir et des informations supplémentaires ; les clefs du tableau sont les item_id car on en a besoin
 *
 * @param int  $devoir_id
 * @return array
 */
public static function DB_lister_items_devoir_avec_infos_pour_eleves($devoir_id)
{
  $DB_SQL = 'SELECT item_id, item_nom, entree_id, ';
  $DB_SQL.= 'item_cart, item_lien, matiere_id, referentiel_calcul_methode, referentiel_calcul_limite, referentiel_calcul_retroactif, ';
  $DB_SQL.= 'CONCAT(matiere_ref,".",niveau_ref,".",domaine_ref,theme_ordre,item_ordre) AS item_ref ';
  $DB_SQL.= 'FROM sacoche_jointure_devoir_item ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (item_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (theme_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (domaine_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel USING (matiere_id,niveau_id) ';
  $DB_SQL.= 'WHERE devoir_id=:devoir_id ';
  $DB_SQL.= 'ORDER BY jointure_ordre ASC, matiere_ref ASC, niveau_ordre ASC, domaine_ordre ASC, theme_ordre ASC, item_ordre ASC';
  $DB_VAR = array(':devoir_id'=>$devoir_id);
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR , TRUE);
}

/**
 * Retourner les notes obtenues à un élève à un devoir
 *
 * @param int   $devoir_id
 * @param int   $eleve_id
 * @param string $user_profil_type
 * @param bool  $with_REQ   // Avec ou sans les repères de demandes d'évaluations
 * @return array
 */
public static function DB_lister_saisies_devoir_eleve($devoir_id,$eleve_id,$user_profil_type,$with_REQ)
{
  // Cette fonction peut être appelée avec un autre profil.
  $sql_view = ( ($user_profil_type=='eleve') || ($user_profil_type=='parent') ) ? 'AND saisie_visible_date<=NOW() ' : '' ;
  $req_view = ($with_REQ) ? '' : 'AND saisie_note!="REQ" ' ;
  $DB_SQL = 'SELECT item_id, saisie_note ';
  $DB_SQL.= 'FROM sacoche_saisie ';
  $DB_SQL.= 'WHERE devoir_id=:devoir_id AND eleve_id=:eleve_id '.$sql_view.$req_view;
  $DB_VAR = array(':devoir_id'=>$devoir_id,':eleve_id'=>$eleve_id);
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Compter le nombre de demandes d'évaluations déjà formulées par un élève pour chaque matière
 *
 * @param int   $eleve_id
 * @param int   $matiere_id
 * @return int
 */
public static function DB_compter_demandes_formulees_eleve_matiere($eleve_id,$matiere_id)
{
  $DB_SQL = 'SELECT COUNT(*) AS nombre ';
  $DB_SQL.= 'FROM sacoche_demande ';
  $DB_SQL.= 'WHERE eleve_id=:eleve_id AND matiere_id=:matiere_id ';
  $DB_SQL.= 'GROUP BY matiere_id';
  $DB_VAR = array(':eleve_id'=>$eleve_id,':matiere_id'=>$matiere_id);
  return DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Tester si un élève a déjà formulé une demande d'évaluation pour un item donné
 *
 * @param int    $eleve_id
 * @param int    $matiere_id
 * @param int    $item_id
 * @return int
 */
public static function DB_tester_demande_existante($eleve_id,$matiere_id,$item_id)
{
  $DB_SQL = 'SELECT demande_id ';
  $DB_SQL.= 'FROM sacoche_demande ';
  $DB_SQL.= 'WHERE eleve_id=:eleve_id AND matiere_id=:matiere_id AND item_id=:item_id ';
  $DB_VAR = array(':eleve_id'=>$eleve_id,':matiere_id'=>$matiere_id,':item_id'=>$item_id);
  return (int)DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Ajouter une demande d'évaluation
 *
 * @param int      $eleve_id
 * @param int      $matiere_id
 * @param int      $item_id
 * @param int      $prof_id
 * @param int|null $demande_score
 * @param string   $demande_statut
 * @param string   $message
 * @return int
 */
public static function DB_ajouter_demande($eleve_id,$matiere_id,$item_id,$prof_id,$demande_score,$demande_statut,$message)
{
  $demande_messages = ($message) ? afficher_identite_initiale($_SESSION['USER_NOM'],FALSE,$_SESSION['USER_PRENOM'],TRUE)."\r\n".$message : '' ;
  $DB_SQL = 'INSERT INTO sacoche_demande(eleve_id,matiere_id,item_id,prof_id,demande_date,demande_score,demande_statut,demande_messages) ';
  $DB_SQL.= 'VALUES(:eleve_id,:matiere_id,:item_id,:prof_id,NOW(),:demande_score,:demande_statut,:demande_messages)';
  $DB_VAR = array(':eleve_id'=>$eleve_id,':matiere_id'=>$matiere_id,':item_id'=>$item_id,':prof_id'=>$prof_id,':demande_score'=>$demande_score,':demande_statut'=>$demande_statut,':demande_messages'=>$demande_messages);
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return DB::getLastOid(SACOCHE_STRUCTURE_BD_NAME);
}

/**
 * supprimer_demande_precise
 *
 * @param int   $demande_id
 * @return void
 */
public static function DB_supprimer_demande_precise($demande_id)
{
  $DB_SQL = 'DELETE FROM sacoche_demande ';
  $DB_SQL.= 'WHERE demande_id=:demande_id ';
  $DB_VAR = array(':demande_id'=>$demande_id);
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

}
?>