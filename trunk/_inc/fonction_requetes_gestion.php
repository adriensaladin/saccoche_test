<?php
/**
 * @version $Id: fonction_requetes_gestion.php 8 2009-10-30 20:56:02Z thomas $
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

/**
 * lister_matieres_partagees_SACoche
 * 
 * @param void
 * @return array
 */

function lister_matieres_partagees_SACoche()
{
	$DB_SQL = 'SELECT * FROM livret_matiere ';
	$DB_SQL.= 'WHERE livret_matiere_structure_id=0 ';
	$DB_SQL.= 'ORDER BY livret_matiere_nom ASC';
	$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL);
	return $DB_TAB ;
}

/**
 * lister_matieres_specifiques_structure
 * 
 * @param int    $structure_id
 * @return array
 */

function lister_matieres_specifiques_structure($structure_id)
{
	$DB_SQL = 'SELECT * FROM livret_matiere ';
	$DB_SQL.= 'WHERE livret_matiere_structure_id=:structure_id ';
	$DB_SQL.= 'ORDER BY livret_matiere_nom ASC';
	$DB_VAR = array(':structure_id'=>$structure_id);
	$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	return $DB_TAB ;
}

/**
 * modifier_matieres_partagees_structure
 * 
 * @param int    $structure_id
 * @param string $listing_matieres id des matières séparés par des virgules
 * @return void
 */

function modifier_matieres_partagees_structure($structure_id,$listing_matieres)
{
	$DB_SQL = 'UPDATE livret_structure ';
	$DB_SQL.= 'SET livret_structure_matieres="'.$listing_matieres.'" ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':structure_id'=>$structure_id);
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	// On ne défait pas pour autant les liaisons avec les enseignants... simplement elles n'apparaitront plus.
	// Idem pour les jointures avec les référentiels : ainsi les scores des élèves demeurent conservés.
}

/**
 * chercher_reference_matiere_structure
 * 
 * @param int    $structure_id
 * @param string $matiere_ref
 * @param int    $matiere_id    inutile si recherche pour un ajout, mais id à éviter si recherche pour une modification
 * @return int
 */

function chercher_reference_matiere_structure($structure_id,$matiere_ref,$matiere_id=false)
{
	$DB_SQL = 'SELECT livret_matiere_id FROM livret_matiere ';
	$DB_SQL.= 'WHERE livret_matiere_ref=:matiere_ref AND livret_matiere_structure_id IN(0,:structure_id) ';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':matiere_ref'=>$matiere_ref);
	if($matiere_id)
	{
		$DB_SQL.= 'AND livret_matiere_id!=:matiere_id ';
		$DB_VAR[':matiere_id'] = $matiere_id;
	}
	$DB_SQL.= 'LIMIT 1';
	$DB_ROW = DB::queryRow(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	return count($DB_ROW) ;
}

/**
 * ajouter_matiere_specifique_structure
 * 
 * @param int    $structure_id
 * @param string $matiere_ref
 * @param string $matiere_nom
 * @return int
 */

function ajouter_matiere_specifique_structure($structure_id,$matiere_ref,$matiere_nom)
{
	$DB_SQL = 'INSERT INTO livret_matiere(livret_matiere_structure_id,livret_matiere_ref,livret_matiere_nom) ';
	$DB_SQL.= 'VALUES(:structure_id,:matiere_ref,:matiere_nom)';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':matiere_ref'=>$matiere_ref,':matiere_nom'=>$matiere_nom);
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	$id = DB::getLastOid(SACOCHE_BD_NAME);
	return $id ;
}

/**
 * modifier_matiere_specifique_structure
 * 
 * @param int    $structure_id
 * @param int    $matiere_id
 * @param string $matiere_ref
 * @param string $matiere_nom
 * @return void
 */

function modifier_matiere_specifique_structure($structure_id,$matiere_id,$matiere_ref,$matiere_nom)
{
	$DB_SQL = 'UPDATE livret_matiere ';
	$DB_SQL.= 'SET livret_matiere_ref=:matiere_ref,livret_matiere_nom=:matiere_nom ';
	$DB_SQL.= 'WHERE livret_matiere_structure_id=:structure_id AND livret_matiere_id=:matiere_id ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':matiere_ref'=>$matiere_ref,':matiere_nom'=>$matiere_nom,':matiere_id'=>$matiere_id);
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * supprimer_matiere_specifique_structure
 * 
 * @param int $structure_id
 * @param int $matiere_id
 * @return void
 */

function supprimer_matiere_specifique_structure($structure_id,$matiere_id)
{
	$DB_SQL = 'DELETE FROM livret_matiere ';
	$DB_SQL.= 'WHERE livret_matiere_structure_id=:structure_id AND livret_matiere_id=:matiere_id ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':structure_id'=>$structure_id,':matiere_id'=>$matiere_id);
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	// Il faut aussi supprimer les jointures avec les enseignants
	$DB_SQL = 'DELETE FROM livret_jointure_user_matiere ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_matiere_id=:matiere_id ';
	$DB_VAR = array(':structure_id'=>$structure_id,':matiere_id'=>$matiere_id);
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	// Il faut aussi supprimer les référentiels associés, et donc tous les scores associés (orphelins de la matière)
	supprimer_referentiel_structure_matiere_niveau($structure_id,$matiere_id);
}

/**
 * supprimer_referentiel_structure_matiere_niveau
 * 
 * @param int $structure_id
 * @param int $matiere_id
 * @param int $niveau_id    facultatif : si non fourni, tous les niveaux seront concernés
 * @return void
 */

function supprimer_referentiel_structure_matiere_niveau($structure_id,$matiere_id,$niveau_id=false)
{
	$DB_SQL = 'DELETE livret_referentiel,livret_competence_domaine, livret_competence_theme, livret_competence_item, livret_jointure_evaluation_competence, livret_jointure_user_competence FROM livret_referentiel ';
	$DB_SQL.= 'LEFT JOIN livret_competence_domaine USING (livret_structure_id,livret_matiere_id,livret_niveau_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_theme USING (livret_structure_id,livret_domaine_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_item USING (livret_structure_id,livret_theme_id) ';
	$DB_SQL.= 'LEFT JOIN livret_jointure_evaluation_competence USING (livret_structure_id,livret_competence_id) ';
	$DB_SQL.= 'LEFT JOIN livret_jointure_user_competence USING (livret_structure_id,livret_competence_id) ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_matiere_id=:matiere_id ';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':matiere_id'=>$matiere_id);
	if($niveau_id)
	{
		$DB_SQL.= 'AND livret_niveau_id=:niveau_id ';
		$DB_VAR[':niveau_id'] = $niveau_id;
	}
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
}

?>