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
	$password_crypte = md5('grain_de_sel'.$password);
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
		$_SESSION['MATIERES']         = $DB_ROW['livret_structure_matieres'];
		$_SESSION['NIVEAUX']          = $DB_ROW['livret_structure_niveaux'];
		$_SESSION['PALIERS']          = $DB_ROW['livret_structure_paliers'];
		$_SESSION['ELEVE_OPTIONS']    = $DB_ROW['livret_structure_eleve_options'];
		$_SESSION['DUREE_INACTIVITE'] = $DB_ROW['livret_structure_duree_inactivite'];
		eval('$_SESSION[\'PARAM_CALCUL\']='.$DB_ROW['livret_structure_param_calcul'].';');

		$_SESSION['MODELE_PROF']   = $DB_ROW['livret_structure_modele_professeur'];
		$_SESSION['MODELE_ELEVE']  = $DB_ROW['livret_structure_modele_eleve'];
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
		$password_crypte = md5('grain_de_sel'.$password);
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
		$_SESSION['MATIERES']         = $DB_ROW['livret_structure_matieres'];
		$_SESSION['NIVEAUX']          = $DB_ROW['livret_structure_niveaux'];
		$_SESSION['PALIERS']          = $DB_ROW['livret_structure_paliers'];
		$_SESSION['ELEVE_OPTIONS']    = $DB_ROW['livret_structure_eleve_options'];
		$_SESSION['DUREE_INACTIVITE'] = $DB_ROW['livret_structure_duree_inactivite'];
		eval('$_SESSION[\'PARAM_CALCUL\']='.$DB_ROW['livret_structure_param_calcul'].';');

		$_SESSION['ELEVE_CLASSE_ID']  = (int) $DB_ROW['livret_eleve_classe_id'];
		$_SESSION['ELEVE_CLASSE_NOM'] = $DB_ROW['livret_groupe_nom'];
		setcookie('competences-etablissement',$structure_id,time()+60*60*24*365,'/');
	}
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Gérer une demande de changement de mot de passe (administrateur, professeur ou élève)
//	[./pages_administrateur/password.ajax.php] [./pages_eleve/password.ajax.php] [./pages_professeur/password.ajax.php]
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

function changer_mdp($password_ancien,$password_nouveau)
{
	// N'a pas lieu d'être en dehors d'un mode de connexion normal pour un professeur ou un élève (normalement la méthode n'est même pas appelée dans ce cas, mais bon)
	if( ($_SESSION['PROFIL'] != 'administrateur') && ($_SESSION['SSO'] != 'normal') )
	{
		return 'Demande incompatible avec le mode de connexion actuel !';
	}
	// Tester si l'ancien mot de passe correspond à celui enregistré
	$password_ancien_crypte = md5('grain_de_sel'.$password_ancien);
	if($_SESSION['PROFIL'] != 'administrateur')
	{
		$DB_SQL = 'SELECT livret_user_id FROM livret_user ';
		$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_id=:user_id AND livret_user_password=:password_crypte ';
		$DB_SQL.= 'LIMIT 1';
		$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':user_id'=>$_SESSION['USER_ID'],':password_crypte'=>$password_ancien_crypte);
	}
	else
	{
		$DB_SQL = 'SELECT livret_structure_id FROM livret_structure ';
		$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND admin_password=:password_crypte ';
		$DB_SQL.= 'LIMIT 1';
		$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':password_crypte'=>$password_ancien_crypte);
	}
	$DB_ROW = DB::queryRow(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	if(!count($DB_ROW))
	{
		return 'Le mot de passe actuel est incorrect !';
	}
	// Remplacer par le nouveau mot de passe
	$password_nouveau_crypte = md5('grain_de_sel'.$password_nouveau);
	if($_SESSION['PROFIL'] != 'administrateur')
	{
		$DB_SQL = 'UPDATE livret_user ';
		$DB_SQL.= 'SET livret_user_password=:password_crypte ';
		$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_id=:user_id ';
		$DB_SQL.= 'LIMIT 1';
		$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':user_id'=>$_SESSION['USER_ID'],':password_crypte'=>$password_nouveau_crypte);
	}
	else
	{
		$DB_SQL = 'UPDATE livret_structure ';
		$DB_SQL.= 'SET admin_password=:password_crypte ';
		$DB_SQL.= 'WHERE livret_structure_id=:structure_id ';
		$DB_SQL.= 'LIMIT 1';
		$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':password_crypte'=>$password_nouveau_crypte);
	}
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	return 'ok';
}

?>