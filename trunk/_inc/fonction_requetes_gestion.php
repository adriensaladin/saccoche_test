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
	return DB::queryTab(SACOCHE_BD_NAME , $DB_SQL);
}

/**
 * lister_paliers_SACoche
 * 
 * @param void
 * @return array
 */

function lister_paliers_SACoche()
{
	$DB_SQL = 'SELECT * FROM livret_socle_palier ';
	$DB_SQL.= 'ORDER BY livret_palier_ordre ASC';
	return DB::queryTab(SACOCHE_BD_NAME , $DB_SQL);
}

/**
 * lister_niveaux_SACoche
 * 
 * @param void
 * @return array
 */

function lister_niveaux_SACoche()
{
	$DB_SQL = 'SELECT * FROM livret_niveau ';
	$DB_SQL.= 'ORDER BY livret_niveau_ordre ASC';
	return DB::queryTab(SACOCHE_BD_NAME , $DB_SQL);
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
	return DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
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
	// On ne défait pas pour autant les liaisons avec les enseignants... simplement elles n'apparaitront plus dans les formulaires.
	// Idem pour les jointures avec les référentiels : ainsi les scores des élèves demeurent conservés.
}

/**
 * modifier_niveaux_structure
 * 
 * @param int    $structure_id
 * @param string $listing_niveaux id des niveaux séparés par des virgules
 * @return void
 */

function modifier_niveaux_structure($structure_id,$listing_niveaux)
{
	$DB_SQL = 'UPDATE livret_structure ';
	$DB_SQL.= 'SET livret_structure_niveaux="'.$listing_niveaux.'" ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':structure_id'=>$structure_id);
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	// On ne défait pas pour autant les liaisons avec les groupes... simplement ils n'apparaitront plus dans les formulaires.
	// Idem pour les jointures avec les référentiels : ainsi les scores des élèves demeurent conservés.
}

/**
 * modifier_paliers_structure
 * 
 * @param int    $structure_id
 * @param string $listing_paliers id des paliers séparés par des virgules
 * @return void
 */

function modifier_paliers_structure($structure_id,$listing_paliers)
{
	$DB_SQL = 'UPDATE livret_structure ';
	$DB_SQL.= 'SET livret_structure_paliers="'.$listing_paliers.'" ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':structure_id'=>$structure_id);
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	// On ne défait pas pour autant les jointures avec les référentiels : ainsi les scores des élèves demeurent conservés.
}

/**
 * modifier_format_login_structure
 * 
 * @param int    $structure_id
 * @param string $modele_professeur
 * @param string $modele_eleve
 * @return void
 */

function modifier_format_login_structure($structure_id,$modele_professeur,$modele_eleve)
{
	$DB_SQL = 'UPDATE livret_structure ';
	$DB_SQL.= 'SET livret_structure_modele_professeur=:modele_professeur, livret_structure_modele_eleve=:modele_eleve ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':structure_id'=>$structure_id,':modele_professeur'=>$modele_professeur,':modele_eleve'=>$modele_eleve);
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_mode_connexion_structure
 * 
 * @param int    $structure_id
 * @param string $mode_connexion
 * @return void
 */

function modifier_mode_connexion_structure($structure_id,$mode_connexion)
{
	$DB_SQL = 'UPDATE livret_structure ';
	$DB_SQL.= 'SET livret_structure_sso=:mode_connexion ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':structure_id'=>$structure_id,':mode_connexion'=>$mode_connexion);
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_duree_inactivite_structure
 * 
 * @param int $structure_id
 * @param int $delai
 * @return void
 */

function modifier_duree_inactivite_structure($structure_id,$delai)
{

	$DB_SQL = 'UPDATE livret_structure ';
	$DB_SQL.= 'SET livret_structure_duree_inactivite=:delai ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':structure_id'=>$structure_id,':delai'=>$delai);
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_eleve_options_structure
 * 
 * @param int    $structure_id
 * @param string $eleve_options
 * @return void
 */

function modifier_eleve_options_structure($structure_id,$eleve_options)
{
	$DB_SQL = 'UPDATE livret_structure ';
	$DB_SQL.= 'SET livret_structure_eleve_options=:eleve_options ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':structure_id'=>$structure_id,':eleve_options'=>$eleve_options);
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
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
	$DB_VAR = array(':structure_id'=>$structure_id,':matiere_ref'=>$matiere_ref);
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
	$DB_VAR = array(':structure_id'=>$structure_id,':matiere_ref'=>$matiere_ref,':matiere_nom'=>$matiere_nom);
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	return DB::getLastOid(SACOCHE_BD_NAME);
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
	$DB_VAR = array(':structure_id'=>$structure_id,':matiere_ref'=>$matiere_ref,':matiere_nom'=>$matiere_nom,':matiere_id'=>$matiere_id);
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
	$DB_VAR = array(':structure_id'=>$structure_id,':matiere_id'=>$matiere_id);
	if($niveau_id)
	{
		$DB_SQL.= 'AND livret_niveau_id=:niveau_id ';
		$DB_VAR[':niveau_id'] = $niveau_id;
	}
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * select_arborescence_palier
 * 
 * @param int    $palier_id   facultatif : si non fourni, tous les paliers seront concernés
 * @return array
 */

function select_arborescence_palier($palier_id=false)
{
	$DB_SQL = 'SELECT * FROM livret_socle_palier ';
	$DB_SQL.= 'LEFT JOIN livret_socle_pilier USING (livret_palier_id) ';
	$DB_SQL.= 'LEFT JOIN livret_socle_section USING (livret_pilier_id) ';
	$DB_SQL.= 'LEFT JOIN livret_socle_item USING (livret_section_id) ';
	$DB_VAR = array();
	if($palier_id)
	{
		$DB_SQL.= 'WHERE livret_palier_id=:palier_id ';
		$DB_VAR[':palier_id'] = $palier_id;
	}
	$DB_SQL.= 'ORDER BY livret_palier_ordre ASC, livret_pilier_ordre ASC, livret_section_ordre ASC, livret_socle_ordre ASC';
	return DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * changer_son_mdp
 * Remarque : cette fonction n'est pas appelée pour un professeur ou un élève si le mode de connexion est SSO
 * 
 * @param int    $structure_id
 * @param int    $user_id
 * @param string $user_profil
 * @param string $password_ancien
 * @param string $password_nouveau
 * @return string   'ok' ou 'Le mot de passe actuel est incorrect !'
 */

function changer_son_mdp($structure_id,$user_id,$user_profil,$password_ancien,$password_nouveau)
{
	// Tester si l'ancien mot de passe correspond à celui enregistré
	$password_ancien_crypte = md5('grain_de_sel'.$password_ancien);
	if($user_profil != 'administrateur')
	{
		$DB_SQL = 'SELECT livret_user_id FROM livret_user ';
		$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_id=:user_id AND livret_user_password=:password_crypte ';
		$DB_SQL.= 'LIMIT 1';
		$DB_VAR = array(':structure_id'=>$structure_id,':user_id'=>$user_id,':password_crypte'=>$password_ancien_crypte);
	}
	else
	{
		$DB_SQL = 'SELECT livret_structure_id FROM livret_structure ';
		$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND admin_password=:password_crypte ';
		$DB_SQL.= 'LIMIT 1';
		$DB_VAR = array(':structure_id'=>$structure_id,':password_crypte'=>$password_ancien_crypte);
	}
	$DB_ROW = DB::queryRow(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	if(!count($DB_ROW))
	{
		return 'Le mot de passe actuel est incorrect !';
	}
	// Remplacer par le nouveau mot de passe
	$password_nouveau_crypte = md5('grain_de_sel'.$password_nouveau);
	if($user_profil != 'administrateur')
	{
		$DB_SQL = 'UPDATE livret_user ';
		$DB_SQL.= 'SET livret_user_password=:password_crypte ';
		$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_id=:user_id ';
		$DB_SQL.= 'LIMIT 1';
		$DB_VAR = array(':structure_id'=>$structure_id,':user_id'=>$user_id,':password_crypte'=>$password_nouveau_crypte);
	}
	else
	{
		$DB_SQL = 'UPDATE livret_structure ';
		$DB_SQL.= 'SET admin_password=:password_crypte ';
		$DB_SQL.= 'WHERE livret_structure_id=:structure_id ';
		$DB_SQL.= 'LIMIT 1';
		$DB_VAR = array(':structure_id'=>$structure_id,':password_crypte'=>$password_nouveau_crypte);
	}
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	return 'ok';
}

?>