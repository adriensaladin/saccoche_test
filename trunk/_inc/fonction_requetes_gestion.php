<?php
/**
 * @version $Id$
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
 * DB_lister_matieres_partagees_SACoche
 * 
 * @param void
 * @return array
 */

function DB_lister_matieres_partagees_SACoche()
{
	$DB_SQL = 'SELECT * FROM livret_matiere ';
	$DB_SQL.= 'WHERE livret_matiere_structure_id=0 ';
	$DB_SQL.= 'ORDER BY livret_matiere_nom ASC';
	return DB::queryTab(SACOCHE_BD_NAME , $DB_SQL);
}

/**
 * DB_lister_paliers_SACoche
 * 
 * @param void
 * @return array
 */

function DB_lister_paliers_SACoche()
{
	$DB_SQL = 'SELECT * FROM livret_socle_palier ';
	$DB_SQL.= 'ORDER BY livret_palier_ordre ASC';
	return DB::queryTab(SACOCHE_BD_NAME , $DB_SQL);
}

/**
 * DB_lister_niveaux_SACoche
 * 
 * @param void
 * @return array
 */

function DB_lister_niveaux_SACoche()
{
	$DB_SQL = 'SELECT * FROM livret_niveau ';
	$DB_SQL.= 'ORDER BY livret_niveau_ordre ASC';
	return DB::queryTab(SACOCHE_BD_NAME , $DB_SQL);
}

/**
 * DB_lister_niveaux
 * 
 * @param string $listing_niveaux id des niveaux séparés par des virgules
 * @return array
 */

function DB_lister_niveaux($listing_niveaux)
{
	$DB_SQL = 'SELECT * FROM livret_niveau ';
	$DB_SQL.= 'WHERE livret_niveau_id IN('.$listing_niveaux.') ';
	$DB_SQL.= 'ORDER BY livret_niveau_ordre ASC';
	return DB::queryTab(SACOCHE_BD_NAME , $DB_SQL);
}

/**
 * DB_lister_matieres_specifiques
 * 
 * @param int    $structure_id
 * @return array
 */

function DB_lister_matieres_specifiques($structure_id)
{
	$DB_SQL = 'SELECT * FROM livret_matiere ';
	$DB_SQL.= 'WHERE livret_matiere_structure_id=:structure_id ';
	$DB_SQL.= 'ORDER BY livret_matiere_nom ASC';
	$DB_VAR = array(':structure_id'=>$structure_id);
	return DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_lister_classes
 * 
 * @param int    $structure_id
 * @return array
 */

function DB_lister_classes($structure_id)
{
	$DB_SQL = 'SELECT * FROM livret_groupe ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_groupe_type=:type ';
	$DB_SQL.= 'ORDER BY livret_groupe_ref ASC';
	$DB_VAR = array(':structure_id'=>$structure_id,':type'=>'classe');
	return DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_lister_classes_avec_niveaux
 * 
 * @param int    $structure_id
 * @return array
 */

function DB_lister_classes_avec_niveaux($structure_id)
{
	$DB_SQL = 'SELECT * FROM livret_groupe ';
	$DB_SQL.= 'LEFT JOIN livret_niveau USING (livret_niveau_id) ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_groupe_type=:type ';
	$DB_SQL.= 'ORDER BY livret_niveau_ordre ASC, livret_groupe_ref ASC';
	$DB_VAR = array(':structure_id'=>$structure_id,':type'=>'classe');
	return DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_lister_eleves_avec_classe
 * 
 * @param int    $structure_id
 * @return array
 */

function DB_lister_eleves_avec_classe($structure_id)
{
	$DB_SQL = 'SELECT * FROM livret_user ';
	$DB_SQL.= 'LEFT JOIN livret_groupe ON livret_user.livret_eleve_classe_id=livret_groupe.livret_groupe_id ';
	$DB_SQL.= 'WHERE livret_user.livret_structure_id=:structure_id AND livret_user_profil=:profil ';
	$DB_SQL.= 'ORDER BY livret_user_nom ASC, livret_user_prenom ASC';
	$DB_VAR = array(':structure_id'=>$structure_id,':profil'=>'eleve');
	return DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_lister_eleves_tri_statut_classe
 * 
 * @param int    $structure_id
 * @return array
 */

function DB_lister_eleves_tri_statut_classe($structure_id)
{
	$DB_SQL = 'SELECT * FROM livret_user ';
	$DB_SQL.= 'LEFT JOIN livret_groupe ON livret_user.livret_eleve_classe_id=livret_groupe.livret_groupe_id ';
	$DB_SQL.= 'LEFT JOIN livret_niveau USING (livret_niveau_id) ';
	$DB_SQL.= 'WHERE livret_user.livret_structure_id=:structure_id AND livret_user_profil=:profil ';
	$DB_SQL.= 'ORDER BY livret_user_statut DESC, livret_niveau_ordre ASC, livret_groupe_ref ASC, livret_user_nom ASC, livret_user_prenom ASC';
	$DB_VAR = array(':structure_id'=>$structure_id,':profil'=>'eleve');
	return $DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_lister_professeurs_directeurs
 * 
 * @param int    $structure_id
 * @return array
 */

function DB_lister_professeurs_directeurs($structure_id)
{
	$DB_SQL = 'SELECT * FROM livret_user ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_profil IN(:profil1,:profil2) ';
	$DB_SQL.= 'ORDER BY livret_user_nom ASC, livret_user_prenom ASC';
	$DB_VAR = array(':structure_id'=>$structure_id,':profil1'=>'professeur',':profil2'=>'directeur');
	return DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_lister_professeurs_directeurs_tri_statut
 * 
 * @param int    $structure_id
 * @return array
 */

function DB_lister_professeurs_directeurs_tri_statut($structure_id)
{
	$DB_SQL = 'SELECT * FROM livret_user ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_profil IN(:profil1,:profil2) ';
	$DB_SQL.= 'ORDER BY livret_user_statut DESC, livret_user_nom ASC, livret_user_prenom ASC';
	$DB_VAR = array(':structure_id'=>$structure_id,':profil1'=>'professeur',':profil2'=>'directeur');
	return DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_compter_eleves_suivant_statut
 * 
 * @param int      $structure_id
 * @return array   [0]=>nb actifs , [1]=>nb inactifs
 */

function DB_compter_eleves_suivant_statut($structure_id)
{
	$DB_SQL = 'SELECT livret_user_statut, COUNT(*) AS nombre FROM livret_user ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_profil=:profil ';
	$DB_SQL.= 'GROUP BY livret_user_statut';
	$DB_VAR = array(':structure_id'=>$structure_id,':profil'=>'eleve');
	$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR , TRUE);
	$nb_actif   = ( (count($DB_TAB)) && (isset($DB_TAB[1])) ) ? $DB_TAB[1][0]['nombre'] : 0 ;
	$nb_inactif = ( (count($DB_TAB)) && (isset($DB_TAB[0])) ) ? $DB_TAB[0][0]['nombre'] : 0 ;
	return array($nb_actif,$nb_inactif);
}

/**
 * DB_compter_professeurs_directeurs_suivant_statut
 * 
 * @param int      $structure_id
 * @return array   [0]=>nb actifs , [1]=>nb inactifs
 */

function DB_compter_professeurs_directeurs_suivant_statut($structure_id)
{
	$DB_SQL = 'SELECT livret_user_statut, COUNT(*) AS nombre FROM livret_user ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_profil IN(:profil1,:profil2) ';
	$DB_SQL.= 'GROUP BY livret_user_statut';
	$DB_VAR = array(':structure_id'=>$structure_id,':profil1'=>'professeur',':profil2'=>'directeur');
	$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR , TRUE);
	$nb_actif   = ( (count($DB_TAB)) && (isset($DB_TAB[1])) ) ? $DB_TAB[1][0]['nombre'] : 0 ;
	$nb_inactif = ( (count($DB_TAB)) && (isset($DB_TAB[0])) ) ? $DB_TAB[0][0]['nombre'] : 0 ;
	return array($nb_actif,$nb_inactif);
}

/**
 * DB_tester_matiere_reference
 * 
 * @param int    $structure_id
 * @param string $matiere_ref
 * @param int    $matiere_id    inutile si recherche pour un ajout, mais id à éviter si recherche pour une modification
 * @return int
 */

function DB_tester_matiere_reference($structure_id,$matiere_ref,$matiere_id=false)
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
	DB::queryRow(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	return DB::rowCount(SACOCHE_BD_NAME) ;
}

/**
 * DB_tester_classe_reference
 * 
 * @param int    $structure_id
 * @param string $groupe_ref
 * @param int    $groupe_id    inutile si recherche pour un ajout, mais id à éviter si recherche pour une modification
 * @return int
 */

function DB_tester_classe_reference($structure_id,$groupe_ref,$groupe_id=false)
{
	$DB_SQL = 'SELECT livret_groupe_id FROM livret_groupe ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_groupe_ref=:groupe_ref ';
	$DB_VAR = array(':structure_id'=>$structure_id,':groupe_ref'=>$groupe_ref);
	if($groupe_id)
	{
		$DB_SQL.= 'AND livret_groupe_id!=:groupe_id ';
		$DB_VAR[':groupe_id'] = $groupe_id;
	}
	$DB_SQL.= 'LIMIT 1';
	DB::queryRow(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	return DB::rowCount(SACOCHE_BD_NAME) ;
}

/**
 * DB_tester_groupe_nom
 * 
 * @param int    $structure_id
 * @param string $groupe_nom
 * @param int    $groupe_id    inutile si recherche pour un ajout, mais id à éviter si recherche pour une modification
 * @return int
 */

function DB_tester_groupe_nom($structure_id,$groupe_nom,$groupe_id=false)
{
	$DB_SQL = 'SELECT livret_groupe_id FROM livret_groupe ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_groupe_type=:groupe_type AND livret_groupe_nom=:groupe_nom ';
	$DB_VAR = array(':structure_id'=>$structure_id,':groupe_type'=>'groupe',':groupe_nom'=>$groupe_nom);
	if($groupe_id)
	{
		$DB_SQL.= 'AND livret_groupe_id!=:groupe_id ';
		$DB_VAR[':groupe_id'] = $groupe_id;
	}
	$DB_SQL.= 'LIMIT 1';
	DB::queryRow(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	return DB::rowCount(SACOCHE_BD_NAME) ;
}

/**
 * DB_tester_periode_nom
 * 
 * @param int    $structure_id
 * @param string $periode_nom
 * @param int    $periode_id    inutile si recherche pour un ajout, mais id à éviter si recherche pour une modification
 * @return int
 */

function DB_tester_periode_nom($structure_id,$periode_nom,$periode_id=false)
{
	$DB_SQL = 'SELECT livret_periode_id FROM livret_periode ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_periode_nom=:periode_nom ';
	$DB_VAR = array(':structure_id'=>$structure_id,':periode_nom'=>$periode_nom);
	if($periode_id)
	{
		$DB_SQL.= 'AND livret_periode_id!=:periode_id ';
		$DB_VAR[':periode_id'] = $periode_id;
	}
	$DB_SQL.= 'LIMIT 1';
	DB::queryRow(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	return DB::rowCount(SACOCHE_BD_NAME) ;
}

/**
 * DB_tester_utilisateur_idENT (parmi tout le personnel de l'établissement, sauf éventuellement l'utilisateur concerné)
 * 
 * @param int    $structure_id
 * @param string $user_id_ent
 * @param int    $user_id       inutile si recherche pour un ajout, mais id à éviter si recherche pour une modification
 * @return int
 */

function DB_tester_utilisateur_idENT($structure_id,$user_id_ent,$user_id=false)
{
	$DB_SQL = 'SELECT livret_user_id FROM livret_user ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_id_ent=:user_id_ent ';
	$DB_VAR = array(':structure_id'=>$structure_id,':user_id_ent'=>$user_id_ent);
	$DB_ROW = DB::queryRow(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	if($user_id)
	{
		$DB_SQL.= 'AND livret_user_id!=:user_id ';
		$DB_VAR[':user_id'] = $user_id;
	}
	$DB_SQL.= 'LIMIT 1';
	DB::queryRow(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	return DB::rowCount(SACOCHE_BD_NAME) ;
}

/**
 * DB_tester_utilisateur_idGepi (parmi tout le personnel de l'établissement, sauf éventuellement l'utilisateur concerné)
 * 
 * @param int    $structure_id
 * @param string $user_id_gepi
 * @param int    $user_id       inutile si recherche pour un ajout, mais id à éviter si recherche pour une modification
 * @return int
 */

function DB_tester_utilisateur_idGepi($structure_id,$user_id_gepi,$user_id=false)
{
	$DB_SQL = 'SELECT livret_user_id FROM livret_user ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_id_gepi=:user_id_gepi ';
	$DB_VAR = array(':structure_id'=>$structure_id,':user_id_gepi'=>$user_id_gepi);
	$DB_ROW = DB::queryRow(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	if($user_id)
	{
		$DB_SQL.= 'AND livret_user_id!=:user_id ';
		$DB_VAR[':user_id'] = $user_id;
	}
	$DB_SQL.= 'LIMIT 1';
	DB::queryRow(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	return DB::rowCount(SACOCHE_BD_NAME) ;
}

/**
 * DB_tester_utilisateur_numSconet (parmi tout le personnel de l'établissement de même profil, sauf éventuellement l'utilisateur concerné)
 * 
 * @param int    $structure_id
 * @param int    $user_num_sconet
 * @param string $user_profil
 * @param int    $user_id       inutile si recherche pour un ajout, mais id à éviter si recherche pour une modification
 * @return int
 */

function DB_tester_utilisateur_numSconet($structure_id,$user_num_sconet,$user_profil,$user_id=false)
{
	$DB_SQL = 'SELECT livret_user_id FROM livret_user ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_num_sconet=:user_num_sconet AND livret_user_profil=:user_profil ';
	$DB_VAR = array(':structure_id'=>$structure_id,':user_num_sconet'=>$user_num_sconet,':user_profil'=>$user_profil);
	$DB_ROW = DB::queryRow(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	if($user_id)
	{
		$DB_SQL.= 'AND livret_user_id!=:user_id ';
		$DB_VAR[':user_id'] = $user_id;
	}
	$DB_SQL.= 'LIMIT 1';
	DB::queryRow(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	return DB::rowCount(SACOCHE_BD_NAME) ;
}

/**
 * DB_tester_utilisateur_reference (parmi tout le personnel de l'établissement de même profil, sauf éventuellement l'utilisateur concerné)
 * 
 * @param int    $structure_id
 * @param string $user_reference
 * @param string $user_profil
 * @param int    $user_id       inutile si recherche pour un ajout, mais id à éviter si recherche pour une modification
 * @return int
 */

function DB_tester_utilisateur_reference($structure_id,$user_reference,$user_profil,$user_id=false)
{
	$DB_SQL = 'SELECT livret_user_id FROM livret_user ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_reference=:user_reference AND livret_user_profil=:user_profil ';
	$DB_VAR = array(':structure_id'=>$structure_id,':user_reference'=>$user_reference,':user_profil'=>$user_profil);
	$DB_ROW = DB::queryRow(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	if($user_id)
	{
		$DB_SQL.= 'AND livret_user_id!=:user_id ';
		$DB_VAR[':user_id'] = $user_id;
	}
	$DB_SQL.= 'LIMIT 1';
	DB::queryRow(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	return DB::rowCount(SACOCHE_BD_NAME) ;
}

/**
 * DB_tester_login (parmi tout le personnel de l'établissement)
 * 
 * @param int    $structure_id
 * @param string $user_login
 * @param int    $user_id     inutile si recherche pour un ajout, mais id à éviter si recherche pour une modification
 * @return int
 */

function DB_tester_login($structure_id,$user_login,$user_id=false)
{
	$DB_SQL = 'SELECT livret_user_id FROM livret_user ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_login=:user_login ';
	$DB_VAR = array(':structure_id'=>$structure_id,':user_login'=>$user_login);
	$DB_ROW = DB::queryRow(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	if($user_id)
	{
		$DB_SQL.= 'AND livret_user_id!=:user_id ';
		$DB_VAR[':user_id'] = $user_id;
	}
	$DB_SQL.= 'LIMIT 1';
	DB::queryRow(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	return DB::rowCount(SACOCHE_BD_NAME) ;
}

/**
 * DB_rechercher_login_disponible (parmi tout le personnel de l'établissement)
 * 
 * @param int    $structure_id
 * @param string $login
 * @return string
 */

function DB_rechercher_login_disponible($structure_id,$login)
{
	$nb_chiffres = 20-mb_strlen($login);
	$max_result = 0;
	do
	{
		$login = mb_substr($login,0,20-$nb_chiffres);
		$DB_SQL = 'SELECT livret_user_login FROM livret_user ';
		$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_login LIKE :user_login';
		$DB_VAR = array(':structure_id'=>$structure_id,':user_login'=>$login.'%');
		$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR , 'livret_user_login');
		$max_result += pow(10,$nb_chiffres);
	}
	while (count($DB_TAB)>=$max_result);
	$j=0;
	do
	{
		$j++;
	}
	while (array_key_exists($login.$j,$DB_TAB));
	return $login.$j ;
}

/**
 * DB_ajouter_matiere_specifique
 * 
 * @param int    $structure_id
 * @param string $matiere_ref
 * @param string $matiere_nom
 * @return int
 */

function DB_ajouter_matiere_specifique($structure_id,$matiere_ref,$matiere_nom)
{
	$DB_SQL = 'INSERT INTO livret_matiere(livret_matiere_structure_id,livret_matiere_ref,livret_matiere_nom) ';
	$DB_SQL.= 'VALUES(:structure_id,:matiere_ref,:matiere_nom)';
	$DB_VAR = array(':structure_id'=>$structure_id,':matiere_ref'=>$matiere_ref,':matiere_nom'=>$matiere_nom);
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	return DB::getLastOid(SACOCHE_BD_NAME);
}

/**
 * DB_ajouter_groupe
 * 
 * @param int    $structure_id
 * @param string $groupe_type      'classe' ou 'groupe' ou 'besoin' ou 'eval'
 * @param int    $groupe_prof_id   id du prof dans le cas d'un groupe de besoin ou pour une évaluation (0 sinon)
 * @param string $groupe_ref
 * @param string $groupe_nom
 * @param int    $niveau_id
 * @return int
 */

function DB_ajouter_groupe($structure_id,$groupe_type,$groupe_prof_id,$groupe_ref,$groupe_nom,$niveau_id)
{
	$DB_SQL = 'INSERT INTO livret_groupe(livret_structure_id,livret_groupe_type,livret_groupe_prof_id,livret_groupe_ref,livret_groupe_nom,livret_niveau_id) ';
	$DB_SQL.= 'VALUES(:structure_id,:groupe_type,:groupe_prof_id,:groupe_ref,:groupe_nom,:niveau_id)';
	$DB_VAR = array(':structure_id'=>$structure_id,':groupe_type'=>$groupe_type,':groupe_prof_id'=>$groupe_prof_id,':groupe_ref'=>$groupe_ref,':groupe_nom'=>$groupe_nom,':niveau_id'=>$niveau_id);
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	return DB::getLastOid(SACOCHE_BD_NAME);
}

/**
 * DB_ajouter_periode
 * 
 * @param int    $structure_id
 * @param string $periode_nom
 * @param int    $periode_ordre
 * @return int
 */

function DB_ajouter_periode($structure_id,$periode_nom,$periode_ordre)
{
	$DB_SQL = 'INSERT INTO livret_periode(livret_structure_id,livret_periode_nom,livret_periode_ordre) ';
	$DB_SQL.= 'VALUES(:structure_id,:periode_nom,:periode_ordre)';
	$DB_VAR = array(':structure_id'=>$structure_id,':periode_nom'=>$periode_nom,':periode_ordre'=>$periode_ordre);
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	return DB::getLastOid(SACOCHE_BD_NAME);
}

/**
 * DB_ajouter_utilisateur
 * 
 * @param int    $structure_id
 * @param string $user_num_sconet
 * @param string $user_reference
 * @param string $user_profil
 * @param string $user_nom
 * @param string $user_prenom
 * @param string $user_login
 * @param string $user_password
 * @param int    $eleve_classe_id   facultatif, 0 si pas de classe ou profil non élève
 * @param string $user_id_ent       facultatif
 * @param string $user_id_gepi      facultatif
 * @return int
 */

function DB_ajouter_utilisateur($structure_id,$user_num_sconet,$user_reference,$user_profil,$user_nom,$user_prenom,$user_login,$user_password,$eleve_classe_id=0,$user_id_ent='',$user_id_gepi='')
{
	$password_crypte = crypter_mdp($user_password);
	$DB_SQL = 'INSERT INTO livret_user(livret_structure_id,livret_user_num_sconet,livret_user_reference,livret_user_profil,livret_user_nom,livret_user_prenom,livret_user_login,livret_user_password,livret_eleve_classe_id,livret_user_id_ent,livret_user_id_gepi) ';
	$DB_SQL.= 'VALUES(:structure_id,:user_num_sconet,:user_reference,:user_profil,:user_nom,:user_prenom,:user_login,:password_crypte,:eleve_classe_id,:user_id_ent,:user_id_gepi)';
	$DB_VAR = array(':structure_id'=>$structure_id,':user_num_sconet'=>$user_num_sconet,':user_reference'=>$user_reference,':user_profil'=>$user_profil,':user_nom'=>$user_nom,':user_prenom'=>$user_prenom,':user_login'=>$user_login,':password_crypte'=>$password_crypte,':eleve_classe_id'=>$eleve_classe_id,':user_id_ent'=>$user_id_ent,':user_id_gepi'=>$user_id_gepi);
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	$user_id = DB::getLastOid(SACOCHE_BD_NAME);
	// Pour un professeur, l'affecter obligatoirement à la matière transversale
	if($user_profil=='professeur')
	{
		$DB_SQL = 'INSERT INTO livret_jointure_user_matiere (livret_structure_id ,livret_user_id ,livret_matiere_id,livret_jointure_coord) ';
		$DB_SQL.= 'VALUES(:structure_id,:user_id,:matiere_id,:jointure_coord)';
		$DB_VAR = array(':structure_id'=>$structure_id,':user_id'=>$user_id,':matiere_id'=>ID_MATIERE_TRANSVERSALE,':jointure_coord'=>0);
		DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	}
	return $user_id;
}

/**
 * DB_modifier_matieres_partagees
 * 
 * @param int    $structure_id
 * @param string $listing_matieres id des matières séparés par des virgules
 * @return void
 */

function DB_modifier_matieres_partagees($structure_id,$listing_matieres)
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
 * DB_modifier_niveaux
 * 
 * @param int    $structure_id
 * @param string $listing_niveaux id des niveaux séparés par des virgules
 * @return void
 */

function DB_modifier_niveaux($structure_id,$listing_niveaux)
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
 * DB_modifier_paliers
 * 
 * @param int    $structure_id
 * @param string $listing_paliers id des paliers séparés par des virgules
 * @return void
 */

function DB_modifier_paliers($structure_id,$listing_paliers)
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
 * DB_modifier_format_login
 * 
 * @param int    $structure_id
 * @param string $modele_professeur
 * @param string $modele_eleve
 * @return void
 */

function DB_modifier_format_login($structure_id,$modele_professeur,$modele_eleve)
{
	$DB_SQL = 'UPDATE livret_structure ';
	$DB_SQL.= 'SET livret_structure_modele_professeur=:modele_professeur, livret_structure_modele_eleve=:modele_eleve ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':structure_id'=>$structure_id,':modele_professeur'=>$modele_professeur,':modele_eleve'=>$modele_eleve);
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_modifier_mode_connexion
 * 
 * @param int    $structure_id
 * @param string $mode_connexion
 * @return void
 */

function DB_modifier_mode_connexion($structure_id,$mode_connexion)
{
	$DB_SQL = 'UPDATE livret_structure ';
	$DB_SQL.= 'SET livret_structure_sso=:mode_connexion ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':structure_id'=>$structure_id,':mode_connexion'=>$mode_connexion);
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_modifier_duree_inactivite
 * 
 * @param int $structure_id
 * @param int $delai
 * @return void
 */

function DB_modifier_duree_inactivite($structure_id,$delai)
{

	$DB_SQL = 'UPDATE livret_structure ';
	$DB_SQL.= 'SET livret_structure_duree_inactivite=:delai ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':structure_id'=>$structure_id,':delai'=>$delai);
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_modifier_eleve_options
 * 
 * @param int    $structure_id
 * @param string $eleve_options
 * @return void
 */

function DB_modifier_eleve_options($structure_id,$eleve_options)
{
	$DB_SQL = 'UPDATE livret_structure ';
	$DB_SQL.= 'SET livret_structure_eleve_options=:eleve_options ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':structure_id'=>$structure_id,':eleve_options'=>$eleve_options);
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_modifier_utilisateur_statut
 * 
 * @param int $structure_id
 * @param int $user_id
 * @param int $user_statut   0 pour desactiver , 1 pour réintégrer
 * @return void
 */

function DB_modifier_utilisateur_statut($structure_id,$user_id,$user_statut)
{
	$DB_SQL = 'UPDATE livret_user ';
	$DB_SQL.= 'SET livret_user_statut=:user_statut ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_id=:user_id ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':structure_id'=>$structure_id,':user_id'=>$user_id,':user_statut'=>$user_statut);
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_modifier_utilisateur_identifiants
 * 
 * @param int         $structure_id
 * @param int         $user_id
 * @param string|bool $user_login      false pour ne pas le modifier
 * @param string|bool $user_password   false pour ne pas le modifier
 * @return void
 */

function DB_modifier_utilisateur_identifiants($structure_id,$user_id,$user_login,$user_password)
{
	$virgule = '';
	$DB_SQL = 'UPDATE livret_user ';
	$DB_SQL.= 'SET ';
	$DB_VAR = array(':structure_id'=>$structure_id,':user_id'=>$user_id);
	if($user_login)
	{
		$DB_SQL.= 'livret_user_login=:user_login ';
		$DB_VAR[':user_login'] = $user_login;
		$virgule = ', ';
	}
	if($user_password)
	{
		$password_crypte = crypter_mdp($user_password);
		$DB_SQL.= $virgule.'livret_user_password=:password_crypte ';
		$DB_VAR[':password_crypte'] = $password_crypte;
		$virgule = ', ';
	}
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_id=:user_id ';
	$DB_SQL.= 'LIMIT 1';
	if($virgule!='')
	{
		DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	}
}

/**
 * DB_modifier_utilisateur ; on ne touche pas à 'user_profil' ni 'eleve_classe_id'
 * 
 * @param int          $structure_id
 * @param int          $user_id
 * @param string       $user_num_sconet
 * @param string       $user_reference
 * @param string       $user_nom
 * @param string       $user_prenom
 * @param string       $user_login
 * @param string|false $user_password   false pour ne pas le modifier
 * @param string       $user_id_ent
 * @param string       $user_id_gepi
 * @return void
 */

function DB_modifier_utilisateur($structure_id,$user_id,$user_num_sconet,$user_reference,$user_nom,$user_prenom,$user_login,$user_password,$user_id_ent,$user_id_gepi)
{
	$DB_VAR = array(':structure_id'=>$structure_id,':user_id'=>$user_id,':user_num_sconet'=>$user_num_sconet,':user_reference'=>$user_reference,':user_nom'=>$user_nom,':user_prenom'=>$user_prenom,':user_login'=>$user_login,':user_id_ent'=>$user_id_ent,':user_id_gepi'=>$user_id_gepi);
	if($user_password)
	{
		$password_crypte = crypter_mdp($user_password);
		$set_password = ',livret_user_password=:password_crypte';
		$DB_VAR[':password_crypte'] = $password_crypte;
		$virgule = ', ';
	}
	else
	{
		$set_password = '';
	}
	$DB_SQL = 'UPDATE livret_user ';
	$DB_SQL.= 'SET livret_user_num_sconet=:user_num_sconet,livret_user_reference=:user_reference,livret_user_nom=:user_nom,livret_user_prenom=:user_prenom,livret_user_login=:user_login'.$set_password.',livret_user_id_ent=:user_id_ent,livret_user_id_gepi=:user_id_gepi ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_id=:user_id ';
	$DB_SQL.= 'LIMIT 1';
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_modifier_matiere_specifique
 * 
 * @param int    $structure_id
 * @param int    $matiere_id
 * @param string $matiere_ref
 * @param string $matiere_nom
 * @return void
 */

function DB_modifier_matiere_specifique($structure_id,$matiere_id,$matiere_ref,$matiere_nom)
{
	$DB_SQL = 'UPDATE livret_matiere ';
	$DB_SQL.= 'SET livret_matiere_ref=:matiere_ref,livret_matiere_nom=:matiere_nom ';
	$DB_SQL.= 'WHERE livret_matiere_structure_id=:structure_id AND livret_matiere_id=:matiere_id ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':structure_id'=>$structure_id,':matiere_id'=>$matiere_id,':matiere_ref'=>$matiere_ref,':matiere_nom'=>$matiere_nom);
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_modifier_groupe ; on ne touche pas à 'livret_groupe_type' ni à 'livret_groupe_prof_id'
 * 
 * @param int    $structure_id
 * @param int    $groupe_id
 * @param string $groupe_ref
 * @param string $groupe_nom
 * @param int    $niveau_id
 * @return void
 */

function DB_modifier_groupe($structure_id,$groupe_id,$groupe_ref,$groupe_nom,$niveau_id)
{
	$DB_SQL = 'UPDATE livret_groupe ';
	$DB_SQL.= 'SET livret_groupe_ref=:groupe_ref,livret_groupe_nom=:groupe_nom,livret_niveau_id=:niveau_id ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_groupe_id=:groupe_id ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':structure_id'=>$structure_id,':groupe_id'=>$groupe_id,':groupe_ref'=>$groupe_ref,':groupe_nom'=>$groupe_nom,':niveau_id'=>$niveau_id);
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_modifier_liaison_user_groupe
 * 
 * @param int    $structure_id
 * @param int    $user_id
 * @param string $user_profil   'eleve' ou 'professeur'
 * @param int    $groupe_id
 * @param string $groupe_type   'classe' ou 'groupe' ou 'besoin' ou 'eval'
 * @param bool   $etat          'true' pour ajouter/modifier une liaison ; 'false' pour retirer une liaison
 * @return void
 */

function DB_modifier_liaison_user_groupe($structure_id,$user_id,$user_profil,$groupe_id,$groupe_type,$etat)
{
	// Dans le cas d'un élève et d'une classe, ce n'est pas dans la table de jointure mais dans la table user que ça se passe
	if( ($user_profil=='eleve') && ($groupe_type=='classe') )
	{
		if(!$etat)
		{
			$groupe_id = 0; // normalement c'est déjà transmis à 0 mais bon...
		}
		$DB_SQL = 'UPDATE livret_user ';
		$DB_SQL.= 'SET livret_eleve_classe_id=:groupe_id ';
		$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_id=:user_id ';
		$DB_SQL.= 'LIMIT 1';
	}
	else
	{
		if($etat)
		{
			$DB_SQL = 'REPLACE INTO livret_jointure_user_groupe (livret_structure_id,livret_user_id,livret_groupe_id) ';
			$DB_SQL.= 'VALUES(:structure_id,:user_id,:groupe_id)';
		}
		else
		{
			$DB_SQL = 'DELETE FROM livret_jointure_user_groupe ';
			$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_id=:user_id AND livret_groupe_id=:groupe_id ';
			$DB_SQL.= 'LIMIT 1';
		}
	}
	$DB_VAR = array(':structure_id'=>$structure_id,':user_id'=>$user_id,':groupe_id'=>$groupe_id);
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_modifier_liaison_professeur_coordonnateur
 * 
 * @param int    $structure_id
 * @param int    $user_id
 * @param int    $matiere_id
 * @param bool   $etat          'true' pour ajouter/modifier une liaison ; 'false' pour retirer une liaison
 * @return void
 */

function DB_modifier_liaison_professeur_coordonnateur($structure_id,$user_id,$matiere_id,$etat)
{
	$coord = ($etat) ? 1 : 0 ;
	$DB_SQL = 'UPDATE livret_jointure_user_matiere SET livret_jointure_coord=:coord ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_id=:user_id AND livret_matiere_id=:matiere_id ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':structure_id'=>$structure_id,':user_id'=>$user_id,':matiere_id'=>$matiere_id,':coord'=>$coord);
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_modifier_liaison_professeur_principal
 * 
 * @param int    $structure_id
 * @param int    $user_id
 * @param int    $groupe_id
 * @param bool   $etat          'true' pour ajouter/modifier une liaison ; 'false' pour retirer une liaison
 * @return void
 */

function DB_modifier_liaison_professeur_principal($structure_id,$user_id,$groupe_id,$etat)
{
	$pp = ($etat) ? 1 : 0 ;
	$DB_SQL = 'UPDATE livret_jointure_user_groupe SET livret_jointure_pp=:pp ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_id=:user_id AND livret_groupe_id=:groupe_id ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':structure_id'=>$structure_id,':user_id'=>$user_id,':groupe_id'=>$groupe_id,':pp'=>$pp);
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_modifier_liaison_professeur_matiere
 * 
 * @param int    $structure_id
 * @param int    $user_id
 * @param int    $matiere_id
 * @param bool   $etat          'true' pour ajouter/modifier une liaison ; 'false' pour retirer une liaison
 * @return void
 */

function DB_modifier_liaison_professeur_matiere($structure_id,$user_id,$matiere_id,$etat)
{
	if($etat)
	{
		// On ne peut pas utiliser REPLACE car on ne sait pas quelle est la valeur de livret_jointure_coord
		$DB_SQL = 'SELECT livret_structure_id FROM livret_jointure_user_matiere ';
		$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_id=:user_id AND livret_matiere_id=:matiere_id ';
		$DB_SQL.= 'LIMIT 1';
		$DB_VAR = array(':structure_id'=>$structure_id,':user_id'=>$user_id,':matiere_id'=>$matiere_id);
		DB::queryRow(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
		if(!DB::rowCount(SACOCHE_BD_NAME))
		{
			$DB_SQL = 'INSERT INTO livret_jointure_user_matiere (livret_structure_id,livret_user_id,livret_matiere_id,livret_jointure_coord) ';
			$DB_SQL.= 'VALUES(:structure_id,:user_id,:matiere_id,:coord)';
			$DB_VAR = array(':structure_id'=>$structure_id,':user_id'=>$user_id,':matiere_id'=>$matiere_id,':coord'=>0);
			DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
		}
	}
	else
	{
		$DB_SQL = 'DELETE FROM livret_jointure_user_matiere ';
		$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_id=:user_id AND livret_matiere_id=:matiere_id ';
		$DB_SQL.= 'LIMIT 1';
		$DB_VAR = array(':structure_id'=>$structure_id,':user_id'=>$user_id,':matiere_id'=>$matiere_id);
		DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	}
}

/**
 * DB_modifier_liaison_groupe_periode
 * 
 * @param int    $structure_id
 * @param int    $groupe_id
 * @param int    $periode_id
 * @param bool   $etat               'true' pour ajouter/modifier une liaison ; 'false' pour retirer une liaison
 * @param string $date_debut_mysql   date de début au format mysql (facultatif : obligatoire uniquement si $etat=true)
 * @param string $date_fin_mysql     date de fin au format mysql (facultatif : obligatoire uniquement si $etat=true)
 * @return void
 */

function DB_modifier_liaison_groupe_periode($structure_id,$groupe_id,$periode_id,$etat,$date_debut_mysql='',$date_fin_mysql='')
{
	if($etat)
	{
		$DB_SQL = 'REPLACE INTO livret_jointure_groupe_periode (livret_structure_id,livret_groupe_id,livret_periode_id,livret_periode_date_debut,livret_periode_date_fin) ';
		$DB_SQL.= 'VALUES(:structure_id,:groupe_id,:periode_id,:date_debut,:date_fin)';
		$DB_VAR = array(':structure_id'=>$structure_id,':groupe_id'=>$groupe_id,':periode_id'=>$periode_id,':date_debut'=>$date_debut_mysql,':date_fin'=>$date_fin_mysql);
	}
	else
	{
		$DB_SQL = 'DELETE FROM livret_jointure_groupe_periode ';
		$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_groupe_id=:groupe_id AND livret_periode_id=:periode_id ';
		$DB_SQL.= 'LIMIT 1';
		$DB_VAR = array(':structure_id'=>$structure_id,':groupe_id'=>$groupe_id,':periode_id'=>$periode_id);
	}
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_modifier_periode
 * 
 * @param int    $structure_id
 * @param int    $periode_id
 * @param string $periode_nom
 * @param int    $periode_ordre
 * @return void
 */

function DB_modifier_periode($structure_id,$periode_id,$periode_nom,$periode_ordre)
{
	$DB_SQL = 'UPDATE livret_periode ';
	$DB_SQL.= 'SET livret_periode_nom=:periode_nom,livret_periode_ordre=:periode_ordre ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_periode_id=:periode_id ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':structure_id'=>$structure_id,':periode_id'=>$periode_id,':periode_nom'=>$periode_nom,':periode_ordre'=>$periode_ordre);
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_supprimer_matiere_specifique
 * 
 * @param int $structure_id
 * @param int $matiere_id
 * @return void
 */

function DB_supprimer_matiere_specifique($structure_id,$matiere_id)
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
	DB_supprimer_referentiel_matiere_niveau($structure_id,$matiere_id);
}

/**
 * DB_supprimer_groupe
 * 
 * @param int    $structure_id
 * @param int    $groupe_id
 * @param string $groupe_type   valeur parmi 'classe' ; 'groupe' ; 'besoin' ; 'eval'
 * @return void
 */

function DB_supprimer_groupe($structure_id,$groupe_id,$groupe_type)
{
	$DB_SQL = 'DELETE FROM livret_groupe ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_groupe_id=:groupe_id ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':structure_id'=>$structure_id,':groupe_id'=>$groupe_id);
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	// Il faut aussi supprimer les jointures avec les utilisateurs
	$DB_SQL = 'DELETE FROM livret_jointure_user_groupe ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_groupe_id=:groupe_id';
	$DB_VAR = array(':structure_id'=>$structure_id,':groupe_id'=>$groupe_id);
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	// Il faut aussi supprimer les jointures avec les périodes
	if( ($groupe_type=='classe') || ($groupe_type=='groupe') )
	{
		$DB_SQL = 'DELETE FROM livret_jointure_groupe_periode ';
		$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_groupe_id=:groupe_id';
		$DB_VAR = array(':structure_id'=>$structure_id,':groupe_id'=>$groupe_id);
		DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	}
	// Sans oublier le champ pour les évaluations portant sur un groupe
	$DB_SQL = 'UPDATE livret_evaluation ';
	$DB_SQL.= 'SET livret_groupe_id=0 ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_groupe_id=:groupe_id';
	$DB_VAR = array(':structure_id'=>$structure_id,':groupe_id'=>$groupe_id);
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	// Sans oublier le champ pour les affectations des élèves dans une classe
	if($groupe_type=='classe')
	{
		$DB_SQL = 'UPDATE livret_user ';
		$DB_SQL.= 'SET livret_eleve_classe_id=0 ';
		$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_eleve_classe_id=:groupe_id';
		$DB_VAR = array(':structure_id'=>$structure_id,':groupe_id'=>$groupe_id);
		DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	}
}

/**
 * DB_supprimer_periode
 * 
 * @param int $structure_id
 * @param int $periode_id
 * @return void
 */

function DB_supprimer_periode($structure_id,$periode_id)
{
	$DB_SQL = 'DELETE FROM livret_periode ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_periode_id=:periode_id ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':structure_id'=>$structure_id,':periode_id'=>$periode_id);
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	// Il faut aussi supprimer les jointures avec les classes
	$DB_SQL = 'DELETE FROM livret_jointure_groupe_periode ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_periode_id=:periode_id ';
	$DB_VAR = array(':structure_id'=>$structure_id,':periode_id'=>$periode_id);
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * DB_supprimer_referentiel_matiere_niveau
 * 
 * @param int $structure_id
 * @param int $matiere_id
 * @param int $niveau_id    facultatif : si non fourni, tous les niveaux seront concernés
 * @return void
 */

function DB_supprimer_referentiel_matiere_niveau($structure_id,$matiere_id,$niveau_id=false)
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
 * DB_select_arborescence_palier
 * 
 * @param int    $palier_id   facultatif : si non fourni, tous les paliers seront concernés
 * @return array
 */

function DB_select_arborescence_palier($palier_id=false)
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
 * DB_changer_son_mdp
 * Remarque : cette fonction n'est pas appelée pour un professeur ou un élève si le mode de connexion est SSO
 * 
 * @param int    $structure_id
 * @param int    $user_id
 * @param string $user_profil
 * @param string $password_ancien
 * @param string $password_nouveau
 * @return string   'ok' ou 'Le mot de passe actuel est incorrect !'
 */

function DB_changer_son_mdp($structure_id,$user_id,$user_profil,$password_ancien,$password_nouveau)
{
	// Tester si l'ancien mot de passe correspond à celui enregistré
	$password_ancien_crypte = crypter_mdp($password_ancien);
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
	DB::queryRow(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	if(!DB::rowCount(SACOCHE_BD_NAME))
	{
		return 'Le mot de passe actuel est incorrect !';
	}
	// Remplacer par le nouveau mot de passe
	$password_nouveau_crypte = crypter_mdp($password_nouveau);
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

/**
 * DB_supprimer_structure
 * 
 * @param int $structure_id
 * @return void
 */

function DB_supprimer_structure($structure_id)
{
	$tab_sql = array();
	$tab_sql[] = 'DELETE FROM livret_competence_domaine             WHERE livret_structure_id=:structure_id';
	$tab_sql[] = 'DELETE FROM livret_competence_item                WHERE livret_structure_id=:structure_id';
	$tab_sql[] = 'DELETE FROM livret_competence_theme               WHERE livret_structure_id=:structure_id';
	$tab_sql[] = 'DELETE FROM livret_evaluation                     WHERE livret_structure_id=:structure_id';
	$tab_sql[] = 'DELETE FROM livret_groupe                         WHERE livret_structure_id=:structure_id';
	$tab_sql[] = 'DELETE FROM livret_jointure_evaluation_competence WHERE livret_structure_id=:structure_id';
	$tab_sql[] = 'DELETE FROM livret_jointure_groupe_periode        WHERE livret_structure_id=:structure_id';
	$tab_sql[] = 'DELETE FROM livret_jointure_user_competence       WHERE livret_structure_id=:structure_id';
	$tab_sql[] = 'DELETE FROM livret_jointure_user_groupe           WHERE livret_structure_id=:structure_id';
	$tab_sql[] = 'DELETE FROM livret_jointure_user_matiere          WHERE livret_structure_id=:structure_id';
	$tab_sql[] = 'DELETE FROM livret_matiere                        WHERE livret_matiere_structure_id=:structure_id';
	$tab_sql[] = 'DELETE FROM livret_periode                        WHERE livret_structure_id=:structure_id';
	$tab_sql[] = 'DELETE FROM livret_referentiel                    WHERE livret_structure_id=:structure_id';
	$tab_sql[] = 'DELETE FROM livret_structure                      WHERE livret_structure_id=:structure_id';
	$tab_sql[] = 'DELETE FROM livret_user                           WHERE livret_structure_id=:structure_id';
	$DB_VAR = array(':structure_id'=>$structure_id);
	foreach($tab_sql as $DB_SQL)
	{
		DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	}
}

?>