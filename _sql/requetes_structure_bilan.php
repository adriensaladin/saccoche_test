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
// Ces méthodes ne concernent que les bilans (génération de relevés, synthèses, ...).

class DB_STRUCTURE_BILAN extends DB
{

/**
 * recuperer_niveau_groupes
 *
 * @param string   $listing_groupe_id   id des groupes séparés par des virgules
 * @return array
 */
public function DB_recuperer_niveau_groupes($listing_groupe_id)
{
	$DB_SQL = 'SELECT groupe_id, niveau_id, niveau_nom ';
	$DB_SQL.= 'FROM sacoche_groupe ';
	$DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
	$DB_SQL.= 'WHERE groupe_id IN('.$listing_groupe_id.') ';
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * recuperer_arborescence_selection
 * Retourner l'arborescence des items travaillés et des matières concernées par des élèves selectionnés, pour les items choisis !
 * Appelé par [ releve_items_selection.ajax.php ]
 *
 * @param string $liste_eleve_id  id des élèves séparés par des virgules
 * @param string $liste_item_id   id des items séparés par des virgules
 * @param string $date_mysql_debut
 * @param string $date_mysql_fin
 * @return array
 */
public function DB_recuperer_arborescence_selection($liste_eleve_id,$liste_item_id,$date_mysql_debut,$date_mysql_fin)
{
	$DB_SQL = 'SELECT item_id , ';
	$DB_SQL.= 'CONCAT(matiere_ref,".",niveau_ref,".",domaine_ref,theme_ordre,item_ordre) AS item_ref , ';
	$DB_SQL.= 'item_nom , item_coef , item_cart , entree_id AS item_socle , item_lien , ';
	$DB_SQL.= 'matiere_id , matiere_nom , ';
	$DB_SQL.= 'referentiel_calcul_methode AS calcul_methode , referentiel_calcul_limite AS calcul_limite ';
	$DB_SQL.= 'FROM sacoche_saisie ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (item_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (theme_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (domaine_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel USING (matiere_id,niveau_id) ';
	$DB_SQL.= 'WHERE eleve_id IN('.$liste_eleve_id.') AND item_id IN('.$liste_item_id.') AND saisie_date>=:date_debut AND saisie_date<=:date_fin ';
	$DB_SQL.= 'ORDER BY matiere_ordre ASC, matiere_nom ASC, niveau_ordre ASC, domaine_ordre ASC, theme_ordre ASC, item_ordre ASC';
	$DB_VAR = array(':date_debut'=>$date_mysql_debut,':date_fin'=>$date_mysql_fin);
	$DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR , TRUE);
	$tab_matiere = array();
	foreach($DB_TAB as $item_id => $tab)
	{
		foreach($tab as $key => $DB_ROW)
		{
			unset($DB_TAB[$item_id][$key]['matiere_id'],$DB_TAB[$item_id][$key]['matiere_nom']);
		}
		$tab_matiere[$DB_ROW['matiere_id']] = $DB_ROW['matiere_nom'];
	}
	return array($DB_TAB,$tab_matiere);
}

/**
 * recuperer_arborescence_bilan
 * Retourner l'arborescence des items travaillés par des élèves donnés (ou un seul), pour une matière donnée (ou toutes), durant une période donnée
 * Appelé par [ releve_items_matiere.ajax.php ] [ releve_items_multimatiere.ajax.php ]
 *
 * @param string $liste_eleve_id   id des élèves séparés par des virgules ; il peut n'y avoir qu'un id, en particulier si c'est un élève qui demande un bilan
 * @param int    $matiere_id       id de la matière ; FALSE pour toutes les matières
 * @param bool   $only_socle       1 pour ne retourner que les items reliés au socle, 0 sinon
 * @param string $date_mysql_debut
 * @param string $date_mysql_fin
 * @return array
 */
public function DB_recuperer_arborescence_bilan($liste_eleve_id,$matiere_id,$only_socle,$date_mysql_debut,$date_mysql_fin)
{
	$where_eleve      = (strpos($liste_eleve_id,',')) ? 'eleve_id IN('.$liste_eleve_id.') '    : 'eleve_id='.$liste_eleve_id.' ' ; // Pour IN(...) NE PAS passer la liste dans $DB_VAR sinon elle est convertie en nb entier
	$where_matiere    = ($matiere_id)                 ? 'AND matiere_id=:matiere '             : 'AND matiere_active=1 ' ;
	$where_niveau     = 'AND niveau_id IN('.$_SESSION['CYCLES'].','.$_SESSION['NIVEAUX'].') ' ;
	$where_socle      = ($only_socle)                 ? 'AND entree_id !=0 '                   : '' ;
	$where_date_debut = ($date_mysql_debut)           ? 'AND saisie_date>=:date_debut '        : '';
	$where_date_fin   = ($date_mysql_fin)             ? 'AND saisie_date<=:date_fin '          : '';
	$order_matiere    = (!$matiere_id)                ? 'matiere_ordre ASC, matiere_nom ASC, ' : '' ;
	$DB_SQL = 'SELECT item_id , ';
	$DB_SQL.= 'CONCAT(matiere_ref,".",niveau_ref,".",domaine_ref,theme_ordre,item_ordre) AS item_ref , ';
	$DB_SQL.= 'item_nom , item_coef , item_cart , entree_id AS item_socle , item_lien , ';
	$DB_SQL.= (!$matiere_id) ? 'matiere_id , matiere_nom , ' : '' ;
	$DB_SQL.= 'referentiel_calcul_methode AS calcul_methode , referentiel_calcul_limite AS calcul_limite ';
	$DB_SQL.= 'FROM sacoche_saisie ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (item_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (theme_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (domaine_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel USING (matiere_id,niveau_id) ';
	$DB_SQL.= 'WHERE '.$where_eleve.$where_matiere.$where_niveau.$where_socle.$where_date_debut.$where_date_fin;
	$DB_SQL.= 'GROUP BY item_id ';
	$DB_SQL.= 'ORDER BY '.$order_matiere.'niveau_ordre ASC, domaine_ordre ASC, theme_ordre ASC, item_ordre ASC';
	$DB_VAR = array(':matiere'=>$matiere_id,':date_debut'=>$date_mysql_debut,':date_fin'=>$date_mysql_fin);
	$DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR , TRUE);
	if($matiere_id)
	{
		return $DB_TAB;
	}
	else
	{
		// Traiter le résultat de la requête pour en extraire un sous-tableau $tab_matiere
		$tab_matiere = array();
		foreach($DB_TAB as $item_id => $tab)
		{
			foreach($tab as $key => $DB_ROW)
			{
				$tab_matiere[$DB_ROW['matiere_id']] = $DB_ROW['matiere_nom'];
				unset($DB_TAB[$item_id][$key]['matiere_id'],$DB_TAB[$item_id][$key]['matiere_nom']);
			}
		}
		return array($DB_TAB,$tab_matiere);
	}
}

/**
 * recuperer_arborescence_synthese
 * Retourner l'arborescence des items travaillés par des élèves selectionnés, durant la période choisie => pour la synthèse matière ou multi-matières
 * Appelé par [ releve_synthese_matiere.ajax.php ] [ releve_synthese_multimatiere.ajax.php ]
 *
 * @param string $liste_eleve_id   id des élèves séparés par des virgules ; il peut n'y avoir qu'un id, en particulier si c'est un élève qui demande un bilan
 * @param int    $matiere_id       id de la matière ; FALSE pour toutes les matières
 * @param int    $only_socle       1 pour ne retourner que les items reliés au socle, 0 sinon
 * @param int    $only_niveau      0 pour tous les niveaux, autre pour un niveau donné
 * @param string $mode_synthese    'predefini' ou 'domaine' ou 'theme'
 * @param string $date_mysql_debut
 * @param string $date_mysql_fin
 * @return array
 */
public function DB_recuperer_arborescence_synthese($liste_eleve_id,$matiere_id,$only_socle,$only_niveau,$mode_synthese='predefini',$date_mysql_debut,$date_mysql_fin)
{
	$select_matiere    = (!$matiere_id)                ? 'matiere_id , matiere_nom , '                          : '' ;
	$select_synthese   = ($mode_synthese=='predefini') ? ', referentiel_mode_synthese AS mode_synthese '        : '' ;
	$where_eleve       = (strpos($liste_eleve_id,',')) ? 'eleve_id IN('.$liste_eleve_id.') '                    : 'eleve_id='.$liste_eleve_id.' ' ; // Pour IN(...) NE PAS passer la liste dans $DB_VAR sinon elle est convertie en nb entier
	$where_matiere     = ($matiere_id)                 ? 'AND matiere_id=:matiere '                             : 'AND matiere_active=1 ' ;
	$where_socle       = ($only_socle)                 ? 'AND entree_id!=0 '                                    : '' ;
	$where_niveau      = ($only_niveau)                ? 'AND niveau_id='.$only_niveau.' '                      : 'AND niveau_id IN('.$_SESSION['CYCLES'].','.$_SESSION['NIVEAUX'].') ' ;
	$where_date_debut  = ($date_mysql_debut)           ? 'AND saisie_date>=:date_debut '                        : '';
	$where_date_fin    = ($date_mysql_fin)             ? 'AND saisie_date<=:date_fin '                          : '';
	$where_synthese    = ($mode_synthese=='predefini') ? 'AND referentiel_mode_synthese IN("domaine","theme") ' : '';
	$order_matiere     = (!$matiere_id)                ? 'matiere_ordre ASC, matiere_nom ASC, '                 : '' ;
	$DB_SQL = 'SELECT item_id , ';
	$DB_SQL.= 'CONCAT(matiere_ref,".",niveau_ref,".",domaine_ref,theme_ordre,item_ordre) AS item_ref , ';
	$DB_SQL.= 'item_nom , item_coef , item_cart , entree_id AS item_socle , item_lien , ';
	$DB_SQL.= 'theme_id , theme_nom , ';
	$DB_SQL.= 'domaine_id , domaine_nom , ';
	$DB_SQL.= $select_matiere;
	$DB_SQL.= 'niveau_nom , ';
	$DB_SQL.= 'referentiel_calcul_methode AS calcul_methode , referentiel_calcul_limite AS calcul_limite '.$select_synthese;
	$DB_SQL.= 'FROM sacoche_saisie ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (item_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (theme_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (domaine_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel USING (matiere_id,niveau_id) ';
	$DB_SQL.= 'WHERE '.$where_eleve.$where_matiere.$where_socle.$where_niveau.$where_date_debut.$where_date_fin.$where_synthese;
	$DB_SQL.= 'GROUP BY item_id ';
	$DB_SQL.= 'ORDER BY '.$order_matiere.'niveau_ordre ASC, domaine_ordre ASC, theme_ordre ASC, item_ordre ASC';
	$DB_VAR = array(':matiere'=>$matiere_id,':date_debut'=>$date_mysql_debut,':date_fin'=>$date_mysql_fin);
	$DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR , TRUE);
	// Traiter le résultat de la requête pour en extraire des sous-tableaux $tab_synthese et éventuellement $tab_matiere
	$tab_synthese = array();
	$tab_matiere  = array();
	foreach($DB_TAB as $item_id => $tab)
	{
		foreach($tab as $key => $DB_ROW)
		{
			if(!$matiere_id)
			{
				$tab_matiere[$DB_ROW['matiere_id']] = $DB_ROW['matiere_nom'];
				unset($DB_TAB[$item_id][$key]['matiere_nom']);
			}
			if($mode_synthese=='predefini')
			{
				$tab_synthese[$DB_ROW['mode_synthese'].'_'.$DB_ROW[$DB_ROW['mode_synthese'].'_id']] = $DB_ROW['niveau_nom'].' - '.$DB_ROW[$DB_ROW['mode_synthese'].'_nom'] ;
				$DB_TAB[$item_id][$key]['synthese_ref'] = $DB_ROW['mode_synthese'].'_'.$DB_ROW[$DB_ROW['mode_synthese'].'_id'];
				unset($DB_TAB[$item_id][$key]['mode_synthese']);
			}
			else
			{
				$tab_synthese[$mode_synthese.'_'.$DB_ROW[$mode_synthese.'_id']] = $DB_ROW['niveau_nom'].' - '.$DB_ROW[$mode_synthese.'_nom'] ;
				$DB_TAB[$item_id][$key]['synthese_ref'] = $mode_synthese.'_'.$DB_ROW[$mode_synthese.'_id'];
			}
			unset($DB_TAB[$item_id][$key]['niveau_nom'],$DB_TAB[$item_id][$key]['domaine_id'],$DB_TAB[$item_id][$key]['domaine_nom'],$DB_TAB[$item_id][$key]['theme_id'],$DB_TAB[$item_id][$key]['theme_nom']);
		}
	}
	if($matiere_id)
	{
		return array($DB_TAB,$tab_synthese);
	}
	else
	{
		return array($DB_TAB,$tab_synthese,$tab_matiere);
	}
}

/**
 * lister_date_last_eleves_items
 * Retourner, pour des élèves et les items donnés, la date de la dernière évaluation (pour vérifier qu'il faut bien prendre l'item en compte)
 *
 * @param string $liste_eleve_id  id des élèves séparés par des virgules
 * @param string $liste_item_id   id des items séparés par des virgules
 * @return array
 */
public function DB_lister_date_last_eleves_items($liste_eleve_id,$liste_item_id)
{
	$DB_SQL = 'SELECT eleve_id , item_id , MAX(saisie_date) AS date_last ';
	$DB_SQL.= 'FROM sacoche_saisie ';
	$DB_SQL.= 'WHERE eleve_id IN('.$liste_eleve_id.') AND item_id IN('.$liste_item_id.') AND saisie_note!="REQ" ';
	$DB_SQL.= 'GROUP BY eleve_id, item_id ';
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * lister_result_eleves_matiere
 * Retourner les résultats pour des élèves donnés, pour des items donnés d'une matiere donnée, sur une période donnée
 *
 * @param string $liste_eleve_id  id des élèves séparés par des virgules
 * @param string $liste_item_id   id des items séparés par des virgules
 * @param string $date_mysql_debut
 * @param string $date_mysql_fin
 * @param string $user_profil
 * @return array
 */
public function DB_lister_result_eleves_matiere($liste_eleve_id,$liste_item_id,$date_mysql_debut,$date_mysql_fin,$user_profil)
{
	$sql_debut = ($date_mysql_debut)     ? 'AND saisie_date>=:date_debut '   : '';
	$sql_fin   = ($date_mysql_fin)       ? 'AND saisie_date<=:date_fin '     : '';
	$sql_view  = ($user_profil=='eleve') ? 'AND saisie_visible_date<=NOW() ' : '';
	$DB_SQL = 'SELECT eleve_id , item_id , ';
	$DB_SQL.= 'saisie_note AS note , saisie_date AS date , saisie_info AS info ';
	$DB_SQL.= 'FROM sacoche_saisie ';
	$DB_SQL.= 'LEFT JOIN sacoche_devoir USING (devoir_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (item_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (theme_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (domaine_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
	$DB_SQL.= 'WHERE eleve_id IN('.$liste_eleve_id.') AND item_id IN('.$liste_item_id.') AND niveau_id IN('.$_SESSION['CYCLES'].','.$_SESSION['NIVEAUX'].') AND saisie_note!="REQ" '.$sql_debut.$sql_fin.$sql_view;
	$DB_SQL.= 'ORDER BY niveau_ordre ASC, domaine_ordre ASC, theme_ordre ASC, item_ordre ASC, saisie_date ASC';
	$DB_VAR = array(':date_debut'=>$date_mysql_debut,':date_fin'=>$date_mysql_fin);
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_result_eleves_matieres
 * Retourner les résultats pour des élèves donnés, pour des items donnés de plusieurs matieres, sur une période donnée
 *
 * @param string $liste_eleve_id  id des élèves séparés par des virgules
 * @param string $liste_item_id   id des items séparés par des virgules
 * @param string $date_mysql_debut
 * @param string $date_mysql_fin
 * @param string $user_profil
 * @return array
 */
public function DB_lister_result_eleves_matieres($liste_eleve_id,$liste_item_id,$date_mysql_debut,$date_mysql_fin,$user_profil)
{
	$sql_debut = ($date_mysql_debut)     ? 'AND saisie_date>=:date_debut '   : '';
	$sql_fin   = ($date_mysql_fin)       ? 'AND saisie_date<=:date_fin '     : '';
	$sql_view  = ($user_profil=='eleve') ? 'AND saisie_visible_date<=NOW() ' : '';
	$DB_SQL = 'SELECT eleve_id , matiere_id , item_id , ';
	$DB_SQL.= 'saisie_note AS note , saisie_date AS date , saisie_info AS info ';
	$DB_SQL.= 'FROM sacoche_saisie ';
	$DB_SQL.= 'LEFT JOIN sacoche_devoir USING (devoir_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (item_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (theme_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (domaine_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
	$DB_SQL.= 'WHERE eleve_id IN('.$liste_eleve_id.') AND item_id IN('.$liste_item_id.') AND niveau_id IN('.$_SESSION['CYCLES'].','.$_SESSION['NIVEAUX'].') AND saisie_note!="REQ" '.$sql_debut.$sql_fin.$sql_view;
	$DB_SQL.= 'ORDER BY matiere_ordre ASC, niveau_ordre ASC, domaine_ordre ASC, theme_ordre ASC, item_ordre ASC, saisie_date ASC';
	$DB_VAR = array(':date_debut'=>$date_mysql_debut,':date_fin'=>$date_mysql_fin);
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_result_eleves_palier_sans_infos_items
 * Retourner les résultats pour des élèves donnés, pour des entrées du socle données d'un certain palier
 * Les informations concernant les items sont collectés dans un second temps sinon on peut dépasser une capacité memory_limit de 32Mo.
 *
 * @param string $liste_eleve_id   id des élèves séparés par des virgules
 * @param string $liste_entree_id  id des entrées séparées par des virgules
 * @param string $user_profil
 * @return array
 */
public function DB_lister_result_eleves_palier_sans_infos_items($liste_eleve_id,$liste_entree_id,$user_profil)
{
	$sql_view  = ($user_profil=='eleve') ? 'AND saisie_visible_date<=NOW() ' : '';
	$DB_SQL = 'SELECT eleve_id , entree_id AS socle_id , item_id , saisie_note AS note , ';
	$DB_SQL.= 'matiere_id '; // Besoin s'il faut filtrer à une langue précise pour la compétence 2
	$DB_SQL.= 'FROM sacoche_saisie ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (item_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (theme_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (domaine_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
	$DB_SQL.= 'WHERE eleve_id IN('.$liste_eleve_id.') AND entree_id IN('.$liste_entree_id.') AND niveau_id IN('.$_SESSION['CYCLES'].','.$_SESSION['NIVEAUX'].') AND saisie_note!="REQ" '.$sql_view;
	$DB_SQL.= 'ORDER BY matiere_nom ASC, niveau_ordre ASC, domaine_ordre ASC, theme_ordre ASC, item_ordre ASC, saisie_date ASC';
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * lister_eleves_cibles
 *
 * @param string   $listing_eleve_id   id des élèves séparés par des virgules
 * @param bool     $with_gepi
 * @param bool     $with_langue
 * @return array|string                le tableau est de la forme [i] => array('eleve_id'=>...,'eleve_nom'=>...,'eleve_prenom'=>...,'eleve_id_gepi'=>...,'eleve_langue'=>...);
 */
public function DB_lister_eleves_cibles($listing_eleve_id,$with_gepi,$with_langue)
{
	$DB_SQL = 'SELECT user_id AS eleve_id , user_nom AS eleve_nom , user_prenom AS eleve_prenom ';
	$DB_SQL.= ($with_gepi)   ? ', user_id_gepi AS eleve_id_gepi ' : '' ;
	$DB_SQL.= ($with_langue) ? ', eleve_langue ' : '' ;
	$DB_SQL.= 'FROM sacoche_user ';
	$DB_SQL.= 'WHERE user_id IN('.$listing_eleve_id.') AND user_profil=:profil ';
	$DB_SQL.= 'ORDER BY user_nom ASC, user_prenom ASC';
	$DB_VAR = array(':profil'=>'eleve');
	$DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	return count($DB_TAB) ? $DB_TAB : 'Aucun élève trouvé correspondant aux identifiants transmis !' ;
}

/**
 * compter_modes_synthese_inconnu
 *
 * @param void
 * @return int
 */
public function DB_compter_modes_synthese_inconnu()
{
	$DB_SQL = 'SELECT COUNT(*) AS nombre ';
	$DB_SQL.= 'FROM sacoche_referentiel ';
	$DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
	$DB_SQL.= 'WHERE referentiel_mode_synthese=:mode_inconnu AND matiere_active=1 ';
	$DB_VAR = array(':mode_inconnu'=>'inconnu');
	return DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

}
?>