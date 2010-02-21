<?php
/**
 * @version $Id: fonction_requetes_administration.php 8 2009-10-30 20:56:02Z thomas $
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
//	Gérer une demande de connexion comme administrateur
//	[./pages_public/accueil.ajax.php]
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

function connecter_admin($structure_id,$password)
{
	$password_crypte = crypter_mdp($password);
	$god = ($password_crypte==PASSWORD_WEBMESTRE) ? true : false ;
	$DB_SQL = 'SELECT * FROM livret_structure ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND (admin_password=:password_crypte OR :password_crypte=:password_webmestre) ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':structure_id'=>$structure_id,':password_crypte'=>$password_crypte,':password_webmestre'=>PASSWORD_WEBMESTRE);
	$DB_ROW = DB::queryRow(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	if(count($DB_ROW))
	{
		$_SESSION['GOD']              = $god;
		$_SESSION['PROFIL']           = 'administrateur';
		$_SESSION['STRUCTURE_ID']     = (int) $DB_ROW['livret_structure_id'];
		$_SESSION['STRUCTURE_UAI']    = $DB_ROW['structure_uai'];
		$_SESSION['STRUCTURE']        = $DB_ROW['structure_type_ref'].' '.$DB_ROW['structure_nom'];
		$_SESSION['USER_ID']          = 0;
		$_SESSION['USER_NOM']         = $DB_ROW['admin_nom'];
		$_SESSION['USER_PRENOM']      = $DB_ROW['admin_prenom'];
		$_SESSION['USER_LOGIN']       = 'admin';
		$_SESSION['USER_DESCR']       = '[administrateur] '.$DB_ROW['admin_prenom'].' '.$DB_ROW['admin_nom'];
		$_SESSION['USER_ID_ENT']      = '';
		$_SESSION['USER_ID_GEPI']     = '';
		$_SESSION['SSO']              = $DB_ROW['livret_structure_sso'];
		$_SESSION['MODELE_PROF']      = $DB_ROW['livret_structure_modele_professeur']; // utile uniquement aux admins
		$_SESSION['MODELE_ELEVE']     = $DB_ROW['livret_structure_modele_eleve']; // utile uniquement aux admins
		$_SESSION['MATIERES']         = $DB_ROW['livret_structure_matieres'];
		$_SESSION['NIVEAUX']          = $DB_ROW['livret_structure_niveaux'];
		$_SESSION['PALIERS']          = $DB_ROW['livret_structure_paliers'];
		$_SESSION['ELEVE_OPTIONS']    = $DB_ROW['livret_structure_eleve_options'];
		$_SESSION['ELEVE_DEMANDES']   = $DB_ROW['livret_structure_eleve_demandes'];
		$_SESSION['DUREE_INACTIVITE'] = $DB_ROW['livret_structure_duree_inactivite'];
		$_SESSION['CALCUL_VALEUR']['RR'] = $DB_ROW['livret_structure_calcul_valeur_RR'];
		$_SESSION['CALCUL_VALEUR']['R']  = $DB_ROW['livret_structure_calcul_valeur_R'];
		$_SESSION['CALCUL_VALEUR']['V']  = $DB_ROW['livret_structure_calcul_valeur_V'];
		$_SESSION['CALCUL_VALEUR']['VV'] = $DB_ROW['livret_structure_calcul_valeur_VV'];
		$_SESSION['CALCUL_SEUIL']['R']   = $DB_ROW['livret_structure_calcul_seuil_R'];
		$_SESSION['CALCUL_SEUIL']['V']   = $DB_ROW['livret_structure_calcul_seuil_V'];
		$_SESSION['CALCUL_METHODE']      = $DB_ROW['livret_structure_calcul_methode'];
		$_SESSION['CALCUL_LIMITE']       = $DB_ROW['livret_structure_calcul_limite'];
		setcookie('competences-etablissement',$structure_id,time()+60*60*24*365,'/');
	}
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Gérer une demande de connexion comme professeur ou élève
//	[./pages_public/accueil.ajax.php] [./pages_public/login_SSO.php]
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

function connecter_user($structure_id,$login,$password,$sso=false)
{
	if($sso)
	{
		$god = false ;
		$DB_SQL = 'SELECT livret_structure.*,livret_user.*,livret_groupe.livret_groupe_nom FROM livret_structure ';
		$DB_SQL.= 'LEFT JOIN livret_user USING (livret_structure_id) ';
		$DB_SQL.= 'LEFT JOIN livret_groupe ON livret_user.livret_eleve_classe_id=livret_groupe.livret_groupe_id AND livret_user.livret_structure_id=livret_groupe.livret_structure_id ';
		$DB_SQL.= 'WHERE livret_structure.livret_structure_id=:structure_id AND livret_user_id_ent=:id_ent AND livret_structure_sso=:sso AND livret_user_statut=:statut ';
		$DB_SQL.= 'LIMIT 1';
		$DB_VAR = array(':structure_id'=>$structure_id,':id_ent'=>$login,':sso'=>$sso,':statut'=>1);
	}
	else
	{
		$password_crypte = crypter_mdp($password);
		$god = ($password_crypte==PASSWORD_WEBMESTRE) ? true : false ;
		$DB_SQL = 'SELECT livret_structure.*,livret_user.*,livret_groupe.livret_groupe_nom FROM livret_structure ';
		$DB_SQL.= 'LEFT JOIN livret_user USING (livret_structure_id) ';
		$DB_SQL.= 'LEFT JOIN livret_groupe ON livret_user.livret_eleve_classe_id=livret_groupe.livret_groupe_id AND livret_user.livret_structure_id=livret_groupe.livret_structure_id ';
		$DB_SQL.= 'WHERE livret_structure.livret_structure_id=:structure_id AND livret_user_login=:login AND (livret_user_password=:password_crypte OR :password_crypte=:password_webmestre) AND livret_user_statut=:statut ';
		$DB_SQL.= 'LIMIT 1';
		$DB_VAR = array(':structure_id'=>$structure_id,':login'=>$login,':password_crypte'=>$password_crypte,':password_webmestre'=>PASSWORD_WEBMESTRE,':statut'=>1);
	}
	$DB_ROW = DB::queryRow(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	if(count($DB_ROW))
	{
		$_SESSION['GOD']              = $god;
		$_SESSION['PROFIL']           = $DB_ROW['livret_user_profil'];
		$_SESSION['STRUCTURE_ID']     = (int) $DB_ROW['livret_structure_id'];
		$_SESSION['STRUCTURE_UAI']    = $DB_ROW['structure_uai'];
		$_SESSION['STRUCTURE']        = $DB_ROW['structure_type_ref'].' '.$DB_ROW['structure_nom'];
		$_SESSION['USER_ID']          = (int) $DB_ROW['livret_user_id'];
		$_SESSION['USER_NOM']         = $DB_ROW['livret_user_nom'];
		$_SESSION['USER_PRENOM']      = $DB_ROW['livret_user_prenom'];
		$_SESSION['USER_LOGIN']       = $DB_ROW['livret_user_login'];
		$_SESSION['USER_DESCR']       = '['.$DB_ROW['livret_user_profil'].'] '.$DB_ROW['livret_user_prenom'].' '.$DB_ROW['livret_user_nom'];
		$_SESSION['USER_ID_ENT']      = $DB_ROW['livret_user_id_ent'];
		$_SESSION['USER_ID_GEPI']     = $DB_ROW['livret_user_id_gepi'];
		$_SESSION['SSO']              = $DB_ROW['livret_structure_sso'];
		$_SESSION['MODELE_PROF']      = $DB_ROW['livret_structure_modele_professeur'];
		$_SESSION['MODELE_ELEVE']     = $DB_ROW['livret_structure_modele_eleve'];
		$_SESSION['MATIERES']         = $DB_ROW['livret_structure_matieres'];
		$_SESSION['NIVEAUX']          = $DB_ROW['livret_structure_niveaux'];
		$_SESSION['PALIERS']          = $DB_ROW['livret_structure_paliers'];
		$_SESSION['ELEVE_OPTIONS']    = $DB_ROW['livret_structure_eleve_options'];
		$_SESSION['ELEVE_DEMANDES']   = $DB_ROW['livret_structure_eleve_demandes'];
		$_SESSION['DUREE_INACTIVITE'] = $DB_ROW['livret_structure_duree_inactivite'];
		$_SESSION['ELEVE_CLASSE_ID']  = (int) $DB_ROW['livret_eleve_classe_id'];	// inutile pour les admins
		$_SESSION['ELEVE_CLASSE_NOM'] = $DB_ROW['livret_groupe_nom'];	// inutile pour les admins
		$_SESSION['CALCUL_VALEUR']['RR'] = $DB_ROW['livret_structure_calcul_valeur_RR'];
		$_SESSION['CALCUL_VALEUR']['R']  = $DB_ROW['livret_structure_calcul_valeur_R'];
		$_SESSION['CALCUL_VALEUR']['V']  = $DB_ROW['livret_structure_calcul_valeur_V'];
		$_SESSION['CALCUL_VALEUR']['VV'] = $DB_ROW['livret_structure_calcul_valeur_VV'];
		$_SESSION['CALCUL_SEUIL']['R']   = $DB_ROW['livret_structure_calcul_seuil_R'];
		$_SESSION['CALCUL_SEUIL']['V']   = $DB_ROW['livret_structure_calcul_seuil_V'];
		$_SESSION['CALCUL_METHODE']      = $DB_ROW['livret_structure_calcul_methode'];
		$_SESSION['CALCUL_LIMITE']       = $DB_ROW['livret_structure_calcul_limite'];
		setcookie('competences-etablissement',$structure_id,time()+60*60*24*365,'/');
	}
}

/**
 * fabriquer_login
 * 
 * @param string $prenom
 * @param string $nom
 * @param string $profil   'eleve' ou 'professeur' (ou 'directeur')
 * @return string
 */

function fabriquer_login($prenom,$nom,$profil)
{
	$modele = ($profil=='eleve') ? $_SESSION['MODELE_ELEVE'] : $_SESSION['MODELE_PROF'] ;
	$login_prenom = mb_substr( clean_login($prenom) , 0 , mb_substr_count($modele,'p') );
	$login_nom    = mb_substr( clean_login($nom)    , 0 , mb_substr_count($modele,'n') );
	$login_separe = str_replace(array('p','n'),'',$modele);
	$login = ($modele{0}=='p') ? $login_prenom.$login_separe.$login_nom : $login_nom.$login_separe.$login_prenom ;
	return $login;
}

/**
 * fabriquer_mdp
 * 
 * @param void
 * @return string
 */

function fabriquer_mdp()
{
	// e enlevé sinon un tableur peut interpréter le mot de passe comme un nombre avec exposant ; hijklmoquvw retirés aussi pour éviter tout risque de confusion
	return mb_substr(str_shuffle('23456789abcdfgnprstxyz'),0,6);
}

/**
 * crypter_mdp
 * 
 * @param string $password
 * @return string
 */

function crypter_mdp($password)
{
	return md5('grain_de_sel'.$password);
}

?>