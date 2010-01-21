<?php
/**
 * @version $Id: fonction_requetes_referentiel.php 8 2009-10-30 20:56:02Z thomas $
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2009
 * 
 * ****************************************************************************************************
 * SACoche [http://competences.sesamath.net] - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath [http://www.sesamath.net]
 * Distribution sous licence libre prévue pour l'été 2010.
 * ****************************************************************************************************
 * 
 */

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Retourner une liste de données d'élèves à partir de leurs id
//	[./pages_professeur/bilan_periode.ajax.php]
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

function select_eleves($liste_eleve_id)
{
	$DB_SQL = 'SELECT livret_user_id AS eleve_id , livret_user_nom AS eleve_nom , livret_user_prenom AS eleve_prenom , livret_user_id_gepi AS eleve_id_gepi FROM livret_user ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_id IN('.$liste_eleve_id.') AND livret_user_profil=:profil ';
	$DB_SQL.= 'ORDER BY livret_user_nom ASC, livret_user_prenom ASC';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':profil'=>'eleve');
	$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	return count($DB_TAB) ? $DB_TAB : 'Aucun élève trouvé correspondant aux identifiants transmis !' ;
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Retourner l'arborescence d'un référentiel pour une matière et un niveau donné
//	[./pages_eleve/grille_niveau.ajax.php]
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

function select_arborescence_matiere_niveau($matiere_id,$niveau_id)
{
	$DB_SQL = 'SELECT * FROM livret_referentiel ';
	$DB_SQL.= 'LEFT JOIN livret_matiere USING (livret_matiere_id) ';
	$DB_SQL.= 'LEFT JOIN livret_niveau USING (livret_niveau_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_domaine USING (livret_structure_id,livret_matiere_id,livret_niveau_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_theme USING (livret_structure_id,livret_domaine_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_item USING (livret_structure_id,livret_theme_id) ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_matiere_id=:matiere_id AND livret_niveau_id=:niveau_id ';
	$DB_SQL.= 'ORDER BY livret_domaine_ordre ASC, livret_theme_ordre ASC, livret_competence_ordre ASC';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':matiere_id'=>$matiere_id,':niveau_id'=>$niveau_id);
	return DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Retourner l'arborescence d'un référentiel pour une matière donnée, sur tous les niveaux
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

function select_arborescence_matiere($matiere_id)
{
	$DB_SQL = 'SELECT * FROM livret_referentiel ';
	$DB_SQL.= 'LEFT JOIN livret_matiere USING (livret_matiere_id) ';
	$DB_SQL.= 'LEFT JOIN livret_niveau USING (livret_niveau_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_domaine USING (livret_structure_id,livret_matiere_id,livret_niveau_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_theme USING (livret_structure_id,livret_domaine_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_item USING (livret_structure_id,livret_theme_id) ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_matiere_id=:matiere_id ';
	$DB_SQL.= 'ORDER BY livret_niveau_ordre ASC, livret_domaine_ordre ASC, livret_theme_ordre ASC, livret_competence_ordre ASC';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':matiere_id'=>$matiere_id);
	return DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Retourner l'arborescence d'un référentiel pour toutes les matières d'un professeur, sur tous les niveaux
//	[./pages_professeur/eval_groupe.php] [./pages_professeur/eval_select.php]
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

function select_arborescence_professeur($prof_id)
{
	$DB_SQL = 'SELECT * FROM livret_referentiel ';
	$DB_SQL.= 'LEFT JOIN livret_jointure_user_matiere USING (livret_structure_id,livret_matiere_id) ';
	$DB_SQL.= 'LEFT JOIN livret_matiere USING (livret_matiere_id) ';
	$DB_SQL.= 'LEFT JOIN livret_niveau USING (livret_niveau_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_domaine USING (livret_structure_id,livret_matiere_id,livret_niveau_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_theme USING (livret_structure_id,livret_domaine_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_item USING (livret_structure_id,livret_theme_id) ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_id=:user_id ';
	$DB_SQL.= 'ORDER BY livret_matiere_nom ASC, livret_niveau_ordre ASC, livret_domaine_ordre ASC, livret_theme_ordre ASC, livret_competence_ordre ASC';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':user_id'=>$prof_id);
	return DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Retourner l'arborescence des items travaillés par un élève pour la matière selectionnée, durant la période choisie
//	[./pages_eleve/bilan_periode.ajax.php]
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

function select_arborescence_eleve_periode_matiere($eleve_id,$matiere_id,$date_mysql_debut,$date_mysql_fin)
{
	$sql_debut = ($date_mysql_debut) ? 'AND livret_user_competence_date>=:date_debut ' : '';
	$sql_fin   = ($date_mysql_fin)   ? 'AND livret_user_competence_date<=:date_fin '   : '';
	$DB_SQL = 'SELECT livret_competence_id , ';
	$DB_SQL.= 'CONCAT(livret_matiere_ref,".",livret_niveau_ref,".",livret_domaine_ref,livret_theme_ordre,livret_competence_ordre) AS competence_ref , ';
	$DB_SQL.= 'livret_competence_nom AS competence_nom , livret_competence_coef AS competence_coef , livret_socle_id AS competence_socle , livret_competence_lien AS competence_lien ';
	$DB_SQL.= 'FROM livret_jointure_user_competence ';
	$DB_SQL.= 'LEFT JOIN livret_competence_item USING (livret_structure_id,livret_competence_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_theme USING (livret_structure_id,livret_theme_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_domaine USING (livret_structure_id,livret_domaine_id) ';
	$DB_SQL.= 'LEFT JOIN livret_matiere USING (livret_matiere_id) ';
	$DB_SQL.= 'LEFT JOIN livret_niveau USING (livret_niveau_id) ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_eleve_id=:eleve_id AND livret_matiere_id=:matiere '.$sql_debut.$sql_fin;
	$DB_SQL.= 'GROUP BY livret_competence_id ';
	$DB_SQL.= 'ORDER BY livret_matiere_nom ASC, livret_niveau_ordre ASC, livret_domaine_ordre ASC, livret_theme_ordre ASC, livret_competence_ordre ASC';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':eleve_id'=>$eleve_id,':matiere'=>$matiere_id,':date_debut'=>$date_mysql_debut,':date_fin'=>$date_mysql_fin);
	return DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR , TRUE);
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Retourner l'arborescence des items travaillés par des élèves selectionnés, pour la matière selectionnée, durant la période choisie
//	[./pages_professeur/bilan_periode.ajax.php]
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

function select_arborescence_eleves_periode_matiere($liste_eleve_id,$matiere_id,$date_mysql_debut,$date_mysql_fin)
{
	$sql_debut = ($date_mysql_debut) ? 'AND livret_user_competence_date>=:date_debut ' : '';
	$sql_fin   = ($date_mysql_fin)   ? 'AND livret_user_competence_date<=:date_fin '   : '';
	$DB_SQL = 'SELECT livret_competence_id , ';
	$DB_SQL.= 'CONCAT(livret_matiere_ref,".",livret_niveau_ref,".",livret_domaine_ref,livret_theme_ordre,livret_competence_ordre) AS competence_ref , ';
	$DB_SQL.= 'livret_competence_nom AS competence_nom , livret_competence_coef AS competence_coef , livret_socle_id AS competence_socle , livret_competence_lien AS competence_lien ';
	$DB_SQL.= 'FROM livret_jointure_user_competence ';
	$DB_SQL.= 'LEFT JOIN livret_competence_item USING (livret_structure_id,livret_competence_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_theme USING (livret_structure_id,livret_theme_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_domaine USING (livret_structure_id,livret_domaine_id) ';
	$DB_SQL.= 'LEFT JOIN livret_matiere USING (livret_matiere_id) ';
	$DB_SQL.= 'LEFT JOIN livret_niveau USING (livret_niveau_id) ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_eleve_id IN('.$liste_eleve_id.') AND livret_matiere_id=:matiere '.$sql_debut.$sql_fin;
	$DB_SQL.= 'GROUP BY livret_competence_id ';
	$DB_SQL.= 'ORDER BY livret_matiere_nom ASC, livret_niveau_ordre ASC, livret_domaine_ordre ASC, livret_theme_ordre ASC, livret_competence_ordre ASC';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':matiere'=>$matiere_id,':date_debut'=>$date_mysql_debut,':date_fin'=>$date_mysql_fin);
	return DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR , TRUE);
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Retourner l'arborescence des items travaillés et des matières concernées par des élèves selectionnés, durant la période choisie
//	[./pages_professeur/bilan_periode.ajax.php]
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

function select_arborescence_et_matieres_eleves_periode($liste_eleve_id,$date_mysql_debut,$date_mysql_fin)
{
	$sql_debut = ($date_mysql_debut) ? 'AND livret_user_competence_date>=:date_debut ' : '';
	$sql_fin   = ($date_mysql_fin)   ? 'AND livret_user_competence_date<=:date_fin '   : '';
	$DB_SQL = 'SELECT livret_competence_id , ';
	$DB_SQL.= 'CONCAT(livret_matiere_ref,".",livret_niveau_ref,".",livret_domaine_ref,livret_theme_ordre,livret_competence_ordre) AS competence_ref , ';
	$DB_SQL.= 'livret_competence_nom AS competence_nom , livret_competence_coef AS competence_coef , livret_socle_id AS competence_socle , livret_competence_lien AS competence_lien , ';
	$DB_SQL.= 'livret_matiere_id , livret_matiere_nom ';
	$DB_SQL.= 'FROM livret_jointure_user_competence ';
	$DB_SQL.= 'LEFT JOIN livret_competence_item USING (livret_structure_id,livret_competence_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_theme USING (livret_structure_id,livret_theme_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_domaine USING (livret_structure_id,livret_domaine_id) ';
	$DB_SQL.= 'LEFT JOIN livret_matiere USING (livret_matiere_id) ';
	$DB_SQL.= 'LEFT JOIN livret_niveau USING (livret_niveau_id) ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_eleve_id IN('.$liste_eleve_id.') '.$sql_debut.$sql_fin;
	$DB_SQL.= 'GROUP BY livret_competence_id ';
	$DB_SQL.= 'ORDER BY livret_matiere_nom ASC, livret_niveau_ordre ASC, livret_domaine_ordre ASC, livret_theme_ordre ASC, livret_competence_ordre ASC';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':date_debut'=>$date_mysql_debut,':date_fin'=>$date_mysql_fin);
	$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR , TRUE);
	$tab_matiere = array();
	foreach($DB_TAB as $competence_id => $tab)
	{
		foreach($tab as $key => $DB_ROW)
		{
			$tab_matiere[$DB_ROW['livret_matiere_id']] = $DB_ROW['livret_matiere_nom'];
			unset($DB_TAB[$competence_id][$key]['livret_matiere_id'],$DB_TAB[$competence_id][$key]['livret_matiere_nom']);
		}
	}
	return array($DB_TAB,$tab_matiere);
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Retourner l'arborescence des items travaillés et des matières concernées par des élèves selectionnés, pour les items choisis !
//	[./pages_professeur/releve_selection.ajax.php]
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

function select_arborescence_et_matieres_eleves_competence($liste_eleve_id,$liste_compet_id)
{
	$DB_SQL = 'SELECT livret_competence_id , ';
	$DB_SQL.= 'CONCAT(livret_matiere_ref,".",livret_niveau_ref,".",livret_domaine_ref,livret_theme_ordre,livret_competence_ordre) AS competence_ref , ';
	$DB_SQL.= 'livret_competence_nom AS competence_nom , livret_competence_coef AS competence_coef , livret_socle_id AS competence_socle , livret_competence_lien AS competence_lien , ';
	$DB_SQL.= 'livret_matiere_id , livret_matiere_nom ';
	$DB_SQL.= 'FROM livret_jointure_user_competence ';
	$DB_SQL.= 'LEFT JOIN livret_competence_item USING (livret_structure_id,livret_competence_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_theme USING (livret_structure_id,livret_theme_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_domaine USING (livret_structure_id,livret_domaine_id) ';
	$DB_SQL.= 'LEFT JOIN livret_matiere USING (livret_matiere_id) ';
	$DB_SQL.= 'LEFT JOIN livret_niveau USING (livret_niveau_id) ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_eleve_id IN('.$liste_eleve_id.') AND livret_competence_id IN('.$liste_compet_id.') ';
	$DB_SQL.= 'ORDER BY livret_matiere_nom ASC, livret_niveau_ordre ASC, livret_domaine_ordre ASC, livret_theme_ordre ASC, livret_competence_ordre ASC';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID']);
	$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR , TRUE);
	$tab_matiere = array();
	foreach($DB_TAB as $competence_id => $tab)
	{
		foreach($tab as $key => $DB_ROW)
		{
			unset($DB_TAB[$competence_id][$key]['livret_matiere_id'],$DB_TAB[$competence_id][$key]['livret_matiere_nom']);
		}
		$tab_matiere[$DB_ROW['livret_matiere_id']] = $DB_ROW['livret_matiere_nom'];
	}
	return array($DB_TAB,$tab_matiere);
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Retourner les résultats pour un élève donné, pour des items donnés, sur une période donnée
//	[./pages_eleve/grille_niveau.ajax.php] [./pages_eleve/bilan_periode.ajax.php]
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

function select_result_eleve($eleve_id,$liste_competence_id,$date_mysql_debut,$date_mysql_fin)
{
	$sql_debut = ($date_mysql_debut) ? 'AND livret_user_competence_date>=:date_debut ' : '';
	$sql_fin   = ($date_mysql_fin)   ? 'AND livret_user_competence_date<=:date_fin '   : '';
	$DB_SQL = 'SELECT livret_competence_id AS competence_id , ';
	$DB_SQL.= 'livret_user_competence_note AS note , livret_user_competence_date AS date , livret_evaluation_info AS info ';
	$DB_SQL.= 'FROM livret_jointure_user_competence ';
	$DB_SQL.= 'LEFT JOIN livret_evaluation USING (livret_structure_id,livret_evaluation_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_item USING (livret_structure_id,livret_competence_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_theme USING (livret_structure_id,livret_theme_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_domaine USING (livret_structure_id,livret_domaine_id) ';
	$DB_SQL.= 'LEFT JOIN livret_niveau USING (livret_niveau_id) ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_eleve_id=:eleve_id AND livret_competence_id IN('.$liste_competence_id.') '.$sql_debut.$sql_fin;
	$DB_SQL.= 'ORDER BY livret_niveau_ordre ASC, livret_domaine_ordre ASC, livret_theme_ordre ASC, livret_competence_ordre ASC, livret_user_competence_date ASC';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':eleve_id'=>$_SESSION['USER_ID'],':date_debut'=>$date_mysql_debut,':date_fin'=>$date_mysql_fin);
	return DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Retourner les résultats pour des élèves donnés, pour des items donnés d'une matiere donnée, sur une période donnée
//	[./pages_professeur/bilan_periode.ajax.php]
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

function select_result_eleves_matiere($liste_eleve_id,$liste_competence_id,$date_mysql_debut,$date_mysql_fin)
{
	$sql_debut = ($date_mysql_debut) ? 'AND livret_user_competence_date>=:date_debut ' : '';
	$sql_fin   = ($date_mysql_fin)   ? 'AND livret_user_competence_date<=:date_fin '   : '';
	$DB_SQL = 'SELECT livret_eleve_id AS eleve_id , livret_competence_id AS competence_id , ';
	$DB_SQL.= 'livret_user_competence_note AS note , livret_user_competence_date AS date , livret_evaluation_info AS info ';
	$DB_SQL.= 'FROM livret_jointure_user_competence ';
	$DB_SQL.= 'LEFT JOIN livret_evaluation USING (livret_structure_id,livret_evaluation_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_item USING (livret_structure_id,livret_competence_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_theme USING (livret_structure_id,livret_theme_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_domaine USING (livret_structure_id,livret_domaine_id) ';
	$DB_SQL.= 'LEFT JOIN livret_niveau USING (livret_niveau_id) ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_eleve_id IN('.$liste_eleve_id.') AND livret_competence_id IN('.$liste_competence_id.') '.$sql_debut.$sql_fin;
	$DB_SQL.= 'ORDER BY livret_niveau_ordre ASC, livret_domaine_ordre ASC, livret_theme_ordre ASC, livret_competence_ordre ASC, livret_user_competence_date ASC';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':date_debut'=>$date_mysql_debut,':date_fin'=>$date_mysql_fin);
	return DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Retourner les résultats pour des élèves donnés, pour des items donnés de plusieurs matieres, sur une période donnée
//	[./pages_professeur/bilan_pp_indiv.ajax.php]
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

function select_result_eleves_matieres($liste_eleve_id,$liste_competence_id,$date_mysql_debut,$date_mysql_fin)
{
	$sql_debut = ($date_mysql_debut) ? 'AND livret_user_competence_date>=:date_debut ' : '';
	$sql_fin   = ($date_mysql_fin)   ? 'AND livret_user_competence_date<=:date_fin '   : '';
	$DB_SQL = 'SELECT livret_eleve_id AS eleve_id , livret_matiere_id AS matiere_id , livret_competence_id AS competence_id , ';
	$DB_SQL.= 'livret_user_competence_note AS note , livret_user_competence_date AS date , livret_evaluation_info AS info ';
	$DB_SQL.= 'FROM livret_jointure_user_competence ';
	$DB_SQL.= 'LEFT JOIN livret_evaluation USING (livret_structure_id,livret_evaluation_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_item USING (livret_structure_id,livret_competence_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_theme USING (livret_structure_id,livret_theme_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_domaine USING (livret_structure_id,livret_domaine_id) ';
	$DB_SQL.= 'LEFT JOIN livret_matiere USING (livret_matiere_id) ';
	$DB_SQL.= 'LEFT JOIN livret_niveau USING (livret_niveau_id) ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_eleve_id IN('.$liste_eleve_id.') AND livret_competence_id IN('.$liste_competence_id.') '.$sql_debut.$sql_fin;
	$DB_SQL.= 'ORDER BY livret_niveau_ordre ASC, livret_domaine_ordre ASC, livret_theme_ordre ASC, livret_competence_ordre ASC, livret_user_competence_date ASC';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':date_debut'=>$date_mysql_debut,':date_fin'=>$date_mysql_fin);
	return DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Retourner les résultats pour des élèves donnés, pour des items du socle donnés d'un certain palier
//	[./pages_prof/releve_socle.ajax.php]
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

function select_result_eleves_palier($liste_eleve_id,$liste_item_id,$date_mysql_debut,$date_mysql_fin)
{
	$sql_debut = ($date_mysql_debut) ? 'AND livret_user_competence_date>=:date_debut ' : '';
	$sql_fin   = ($date_mysql_fin)   ? 'AND livret_user_competence_date<=:date_fin '   : '';
	$DB_SQL = 'SELECT livret_eleve_id AS eleve_id , livret_socle_id AS socle_id , livret_competence_id AS competence_id , ';
	$DB_SQL.= 'livret_user_competence_note AS note , livret_competence_nom AS competence_nom , ';
	$DB_SQL.= 'CONCAT(livret_matiere_ref,".",livret_niveau_ref,".",livret_domaine_ref,livret_theme_ordre,livret_competence_ordre) AS competence_ref ';
	$DB_SQL.= 'FROM livret_jointure_user_competence ';
	$DB_SQL.= 'LEFT JOIN livret_competence_item USING (livret_structure_id,livret_competence_id) ';
	$DB_SQL.= 'LEFT JOIN livret_socle_item USING (livret_socle_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_theme USING (livret_structure_id,livret_theme_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_domaine USING (livret_structure_id,livret_domaine_id) ';
	$DB_SQL.= 'LEFT JOIN livret_matiere USING (livret_matiere_id) ';
	$DB_SQL.= 'LEFT JOIN livret_niveau USING (livret_niveau_id) ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_eleve_id IN('.$liste_eleve_id.') AND livret_socle_id IN('.$liste_item_id.') '.$sql_debut.$sql_fin;
	$DB_SQL.= 'ORDER BY livret_matiere_nom ASC, livret_niveau_ordre ASC, livret_domaine_ordre ASC, livret_theme_ordre ASC, livret_competence_ordre ASC, livret_user_competence_date ASC';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':date_debut'=>$date_mysql_debut,':date_fin'=>$date_mysql_fin);
	return DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
}

?>