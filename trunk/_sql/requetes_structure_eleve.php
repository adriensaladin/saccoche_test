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
// Ces méthodes ne concernent que les élèves (et les parents).

class DB_STRUCTURE_ELEVE extends DB
{

/**
 * Récupérer le nombre de demandes d'évaluations autorisées par matière
 *
 * @param int   $matiere_id
 * @return int
 */
public function DB_recuperer_demandes_autorisees_matiere($matiere_id)
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
 * @return int
 */
public function DB_recuperer_item_infos($item_id)
{
	$DB_SQL = 'SELECT item_nom , item_cart , ';
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
 * Retourner une liste de professeurs attachés à un élève identifié et une matière donnée.
 *
 * @param int $eleve_id
 * @param int $matiere_id
 * @return array
 */
public function DB_recuperer_professeurs_eleve_matiere($eleve_id,$matiere_id)
{
	// On connait la classe ($_SESSION['ELEVE_CLASSE_ID']), donc on commence par récupérer les groupes éventuels associés à l'élève
	// DB::query(SACOCHE_STRUCTURE_BD_NAME , 'SET group_concat_max_len = ...'); // Pour lever si besoin une limitation de GROUP_CONCAT (group_concat_max_len est par défaut limité à une chaine de 1024 caractères).
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
	$DB_SQL = 'SELECT DISTINCT(user_id) ';
	$DB_SQL.= 'FROM sacoche_jointure_user_matiere ';
	$DB_SQL.= 'LEFT JOIN sacoche_user USING (user_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_jointure_user_groupe USING (user_id) ';
	$DB_SQL.= 'WHERE matiere_id=:matiere_id AND groupe_id IN('.$liste_groupes.') ';
	$DB_VAR = array(':matiere_id'=>$matiere_id);
	return DB::queryCol(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Lister les classes des élèves associés à un parent
 *
 * @param int $parent_id
 * @return array
 */
public function DB_lister_classes_parent($parent_id)
{
	$DB_SQL = 'SELECT groupe_id, groupe_nom, groupe_type ';
	$DB_SQL.= 'FROM sacoche_jointure_parent_eleve ';
	$DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_jointure_parent_eleve.eleve_id=sacoche_user.user_id ';
	$DB_SQL.= 'LEFT JOIN sacoche_jointure_user_groupe ON eleve_classe_id=groupe_id ';
	$DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
	$DB_SQL.= 'WHERE parent_id=:parent_id AND user_profil="eleve" AND user_statut=:statut ';
	$DB_SQL.= 'GROUP BY groupe_id '; // si plusieurs enfants dans la même classe
	$DB_SQL.= 'ORDER BY groupe_type ASC, niveau_ordre ASC, groupe_nom ASC';
	$DB_VAR = array(':parent_id'=>$parent_id,':statut'=>1);
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Retourner les résultats pour un élève, pour des items donnés
 *
 * @param int    $eleve_id
 * @param string $liste_item_id   id des items séparés par des virgules
 * @return array
 */
public function DB_lister_result_eleve_items($eleve_id,$liste_item_id)
{
	$DB_SQL = 'SELECT item_id , saisie_note AS note ';
	$DB_SQL.= 'FROM sacoche_saisie ';
	$DB_SQL.= 'WHERE eleve_id=:eleve_id AND item_id IN('.$liste_item_id.') AND saisie_note!="REQ" AND saisie_visible_date<=NOW() ';
	$DB_SQL.= 'ORDER BY saisie_date ASC ';
	$DB_VAR = array(':eleve_id'=>$eleve_id);
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Lister les demandes d'évaluation d'un élève donné
 *
 * @param int    $user_id   id de l'élève
 * @return array
 */
public function DB_lister_demandes_eleve($user_id)
{
	$DB_SQL = 'SELECT sacoche_demande.*, ';
	$DB_SQL.= 'CONCAT(niveau_ref,".",domaine_ref,theme_ordre,item_ordre) AS item_ref , ';
	$DB_SQL.= 'item_id , item_nom , item_lien , sacoche_matiere.matiere_id AS matiere_id  , matiere_nom ';
	$DB_SQL.= 'FROM sacoche_demande ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (item_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (theme_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (domaine_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_matiere ON sacoche_referentiel_domaine.matiere_id=sacoche_matiere.matiere_id ';
	$DB_SQL.= 'WHERE user_id=:user_id ';
	$DB_SQL.= 'ORDER BY sacoche_demande.matiere_id ASC, niveau_ref ASC, domaine_ref ASC, theme_ordre ASC, item_ordre ASC';
	$DB_VAR = array(':user_id'=>$user_id);
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Lister les évaluations concernant un élève sur une période donnée
 *
 * @param int    $eleve_id
 * @param int    $classe_id   id de la classe de l'élève ; en effet sacoche_jointure_user_groupe ne contient que les liens aux groupes, donc il faut tester aussi la classe
 * @param string $date_debut_mysql
 * @param string $date_fin_mysql
 * @return array
 */
public function DB_lister_devoirs_eleve($eleve_id,$classe_id,$date_debut_mysql,$date_fin_mysql)
{
	$where_classe = ($classe_id) ? 'sacoche_devoir.groupe_id='.$classe_id.' OR ' : '';
	$DB_SQL = 'SELECT sacoche_devoir.* , sacoche_user.user_nom AS prof_nom , sacoche_user.user_prenom AS prof_prenom ';
	$DB_SQL.= 'FROM sacoche_devoir ';
	$DB_SQL.= 'LEFT JOIN sacoche_jointure_user_groupe USING (groupe_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_devoir.prof_id=sacoche_user.user_id ';
	$DB_SQL.= 'WHERE ('.$where_classe.'sacoche_jointure_user_groupe.user_id=:eleve_id) ';
	$DB_SQL.= 'AND devoir_date>="'.$date_debut_mysql.'" AND devoir_date<="'.$date_fin_mysql.'" AND devoir_visible_date<=NOW() ' ;
	$DB_SQL.= 'GROUP BY devoir_id ';
	$DB_SQL.= 'ORDER BY devoir_date DESC ';
	$DB_VAR = array(':eleve_id'=>$eleve_id);
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Retourner les items d'un devoir et des informations supplémentaires ; les clefs du tableau sont les item_id car on en a besoin
 *
 * @param int  $devoir_id
 * @return array
 */
public function DB_lister_items_devoir_avec_infos_pour_eleves($devoir_id)
{
	$DB_SQL = 'SELECT item_id, item_nom, entree_id, ';
	$DB_SQL.= 'item_cart, item_lien, matiere_id, referentiel_calcul_methode, referentiel_calcul_limite, ';
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
 * @return array
 */
public function DB_lister_saisies_devoir_eleve($devoir_id,$eleve_id)
{
	$DB_SQL = 'SELECT item_id, saisie_note ';
	$DB_SQL.= 'FROM sacoche_saisie ';
	$DB_SQL.= 'WHERE devoir_id=:devoir_id AND eleve_id=:eleve_id AND saisie_note!="REQ" ';
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
public function DB_compter_demandes_formulees_eleve_matiere($eleve_id,$matiere_id)
{
	$DB_SQL = 'SELECT COUNT(*) AS nombre ';
	$DB_SQL.= 'FROM sacoche_demande ';
	$DB_SQL.= 'WHERE user_id=:eleve_id AND matiere_id=:matiere_id ';
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
public function DB_tester_demande_existante($eleve_id,$matiere_id,$item_id)
{
	$DB_SQL = 'SELECT demande_id ';
	$DB_SQL.= 'FROM sacoche_demande ';
	$DB_SQL.= 'WHERE user_id=:eleve_id AND matiere_id=:matiere_id AND item_id=:item_id ';
	// LIMIT 1 inutile
	$DB_VAR = array(':eleve_id'=>$eleve_id,':matiere_id'=>$matiere_id,':item_id'=>$item_id);
	return DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Ajouter une demande d'évaluation
 *
 * @param int      $eleve_id
 * @param int      $matiere_id
 * @param int      $item_id
 * @param string   $demande_date_mysql
 * @param int|null $demande_score
 * @param string   $demande_statut
 * @return int
 */
public function DB_ajouter_demande($eleve_id,$matiere_id,$item_id,$demande_date_mysql,$demande_score,$demande_statut)
{
	$DB_SQL = 'INSERT INTO sacoche_demande(user_id,matiere_id,item_id,demande_date,demande_score,demande_statut) ';
	$DB_SQL.= 'VALUES(:eleve_id,:matiere_id,:item_id,:demande_date,:demande_score,:demande_statut)';
	$DB_VAR = array(':eleve_id'=>$eleve_id,':matiere_id'=>$matiere_id,':item_id'=>$item_id,':demande_date'=>$demande_date_mysql,':demande_score'=>$demande_score,':demande_statut'=>$demande_statut);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	return DB::getLastOid(SACOCHE_STRUCTURE_BD_NAME);
}

/**
 * supprimer_demande_precise
 *
 * @param int   $demande_id
 * @return void
 */
public function DB_supprimer_demande_precise($demande_id)
{
	$DB_SQL = 'DELETE FROM sacoche_demande ';
	$DB_SQL.= 'WHERE demande_id=:demande_id ';
	$DB_VAR = array(':demande_id'=>$demande_id);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

}
?>