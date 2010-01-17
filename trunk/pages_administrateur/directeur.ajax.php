<?php
/**
 * @version $Id: directeur.ajax.php 8 2009-10-30 20:56:02Z thomas $
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

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}
if($_SESSION['STRUCTURE_ID']==ID_DEMO) {exit('Action désactivée pour la démo...');}

$action     = (isset($_POST['f_action']))     ? clean_texte($_POST['f_action'])      : '';
$id         = (isset($_POST['f_id']))         ? clean_entier($_POST['f_id'])         : 0;
$id_ent     = (isset($_POST['f_id_ent']))     ? clean_texte($_POST['f_id_ent'])      : '';
$id_gepi    = (isset($_POST['f_id_gepi']))    ? clean_texte($_POST['f_id_gepi'])     : '';
$num_sconet = (isset($_POST['f_num_sconet'])) ? clean_entier($_POST['f_num_sconet']) : 0;
$reference  = (isset($_POST['f_reference']))  ? clean_ref($_POST['f_reference'])     : '';
$nom        = (isset($_POST['f_nom']))        ? clean_nom($_POST['f_nom'])           : '';
$prenom     = (isset($_POST['f_prenom']))     ? clean_prenom($_POST['f_prenom'])     : '';
$login      = (isset($_POST['f_login']))      ? clean_login($_POST['f_login'])       : '';
$password   = (isset($_POST['f_password']))   ? clean_entier($_POST['f_password'])   : 0;

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Ajouter un nouveau directeur
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
if( ($action=='ajouter') && $nom && $prenom )
{
	// Vérifier que l'identifiant ENT est disponible (parmi tout le personnel de l'établissement)
	if($id_ent)
	{
		$DB_SQL = 'SELECT livret_user_id FROM livret_user ';
		$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_id_ent=:id_ent ';
		$DB_SQL.= 'LIMIT 1';
		$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':id_ent'=>$id_ent);
		$DB_ROW = DB::queryRow(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
		if(count($DB_ROW))
		{
			exit('Erreur : identifiant ENT déjà utilisé !');
		}
	}
	// Vérifier que l'identifiant GEPI est disponible (parmi tout le personnel de l'établissement)
	if($id_gepi)
	{
		$DB_SQL = 'SELECT livret_user_id FROM livret_user ';
		$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_id_gepi=:id_gepi ';
		$DB_SQL.= 'LIMIT 1';
		$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':id_gepi'=>$id_gepi);
		$DB_ROW = DB::queryRow(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
		if(count($DB_ROW))
		{
			exit('Erreur : identifiant Gepi déjà utilisé !');
		}
	}
	// Vérifier que le n° sconet est disponible (parmi les directeurs de cet établissement)
	if($num_sconet)
	{
		$DB_SQL = 'SELECT livret_user_id FROM livret_user ';
		$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_num_sconet=:num_sconet AND livret_user_profil=:profil ';
		$DB_SQL.= 'LIMIT 1';
		$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':num_sconet'=>$num_sconet,':profil'=>'directeur');
		$DB_ROW = DB::queryRow(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
		if(count($DB_ROW))
		{
			exit('Erreur : n° sconet déjà utilisé !');
		}
	}
	// Vérifier que la référence est disponible (parmi les directeurs de cet établissement)
	if($reference)
	{
		$DB_SQL = 'SELECT livret_user_id FROM livret_user ';
		$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_reference=:reference AND livret_user_profil=:profil ';
		$DB_SQL.= 'LIMIT 1';
		$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':reference'=>$reference,':profil'=>'directeur');
		$DB_ROW = DB::queryRow(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
		if(count($DB_ROW))
		{
			exit('Erreur : référence déjà utilisée !');
		}
	}
	// Construire le login
	$login_prenom = mb_substr( clean_login($prenom) , 0 , mb_substr_count($_SESSION['MODELE_PROF'],'p') );
	$login_nom    = mb_substr( clean_login($nom)    , 0 , mb_substr_count($_SESSION['MODELE_PROF'],'n') );
	$login_separe = str_replace(array('p','n'),'',$_SESSION['MODELE_PROF']);
	$login = ($_SESSION['MODELE_PROF']{0}=='p') ? $login_prenom.$login_separe.$login_nom : $login_nom.$login_separe.$login_prenom ;
	// Puis tester le login (parmi tout le personnel de l'établissement)
	$DB_SQL = 'SELECT livret_user_id FROM livret_user ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_login=:login ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':login'=>$login);
	$DB_ROW = DB::queryRow(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	if(count($DB_ROW))
	{
		// Login pris : en chercher un autre en remplaçant la fin par des chiffres si besoin
		
		$nb_chiffres = 20-mb_strlen($login);
		$max_result = 0;
		do
		{
			$login = mb_substr($login,0,20-$nb_chiffres,'UTF-8');
			$DB_SQL = 'SELECT livret_user_login FROM livret_user ';
			$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_login LIKE :login';
			$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':login'=>$login.'%');
			$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR , 'livret_user_login');
			$max_result += pow(10,$nb_chiffres);
		}
		while (count($DB_TAB)>=$max_result);
		$i=0;
		do
		{
			$i++;
		}
		while (array_key_exists($login.$i,$DB_TAB));
		$login .= $i;
	}
	// Construire le mot de passe
	$password = fabriquer_mdp();
	$password_crypte = crypter_mdp($password);
	// Insérer l'enregistrement
	$DB_SQL = 'INSERT INTO livret_user(livret_structure_id,livret_user_num_sconet,livret_user_reference,livret_user_profil,livret_user_nom,livret_user_prenom,livret_user_login,livret_user_password,livret_eleve_classe_id,livret_user_id_ent,livret_user_id_gepi) ';
	$DB_SQL.= 'VALUES(:structure_id,:num_sconet,:reference,:profil,:nom,:prenom,:login,:password,:classe,:id_ent,:id_gepi)';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':num_sconet'=>$num_sconet,':reference'=>$reference,':profil'=>'directeur',':nom'=>$nom,':prenom'=>$prenom,':login'=>$login,':password'=>$password_crypte,':classe'=>0,':id_ent'=>$id_ent,':id_gepi'=>$id_gepi);
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	$id = DB::getLastOid(SACOCHE_BD_NAME);
	// Afficher le retour
	echo'<tr id="id_'.$id.'" class="new">';
	echo	'<td>'.html($id_ent).'</td>';
	echo	'<td>'.html($id_gepi).'</td>';
	echo	'<td>'.html($num_sconet).'</td>';
	echo	'<td>'.html($reference).'</td>';
	echo	'<td>'.html($nom).'</td>';
	echo	'<td>'.html($prenom).'</td>';
	echo	'<td class="new">'.html($login).' <img alt="" title="Pensez à relever le login généré !"  src="./_img/bulle_aide.png" /></td>';
	echo	'<td class="new">'.html($password).' <img alt="" title="Pensez à relever le mot de passe !" src="./_img/bulle_aide.png" /></td>';
	echo	'<td class="nu">';
	echo		'<q class="modifier" title="Modifier ce directeur."></q>';
	echo		'<q class="desactiver" title="Enlever ce directeur."></q>';
	echo	'</td>';
	echo'</tr>';
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Modifier un directeur existant
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
else if( ($action=='modifier') && $id && $nom && $prenom && $login )
{
	// Vérifier que l'identifiant ENT est disponible (parmi tout le personnel de l'établissement)
	if($id_ent)
	{
		$DB_SQL = 'SELECT livret_user_id FROM livret_user ';
		$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_id_ent=:id_ent AND livret_user_id!=:id ';
		$DB_SQL.= 'LIMIT 1';
		$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':id_ent'=>$id_ent,':id'=>$id);
		$DB_ROW = DB::queryRow(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
		if(count($DB_ROW))
		{
			exit('Erreur : identifiant ENT déjà utilisé !');
		}
	}
	// Vérifier que l'identifiant Gepi est disponible (parmi tout le personnel de l'établissement)
	if($id_gepi)
	{
		$DB_SQL = 'SELECT livret_user_id FROM livret_user ';
		$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_id_gepi=:id_gepi AND livret_user_id!=:id ';
		$DB_SQL.= 'LIMIT 1';
		$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':id_gepi'=>$id_gepi,':id'=>$id);
		$DB_ROW = DB::queryRow(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
		if(count($DB_ROW))
		{
			exit('Erreur : identifiant Gepi déjà utilisé !');
		}
	}
	// Vérifier que le n° sconet est disponible (parmi les directeurs de cet établissement)
	if($num_sconet)
	{
		$DB_SQL = 'SELECT livret_user_id FROM livret_user ';
		$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_num_sconet=:num_sconet AND livret_user_profil=:profil AND livret_user_id!=:id ';
		$DB_SQL.= 'LIMIT 1';
		$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':num_sconet'=>$num_sconet,':profil'=>'directeur',':id'=>$id);
		$DB_ROW = DB::queryRow(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
		if(count($DB_ROW))
		{
			exit('Erreur : n° sconet déjà utilisé !');
		}
	}
	// Vérifier que la référence est disponible (parmi les directeurs de cet établissement)
	if($reference)
	{
		$DB_SQL = 'SELECT livret_user_id FROM livret_user ';
		$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_reference=:reference AND livret_user_profil=:profil AND livret_user_id!=:id ';
		$DB_SQL.= 'LIMIT 1';
		$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':reference'=>$reference,':profil'=>'directeur',':id'=>$id);
		$DB_ROW = DB::queryRow(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
		if(count($DB_ROW))
		{
			exit('Erreur : référence déjà utilisée !');
		}
	}
	// Vérifier que le login du directeur est disponible (parmi tout le personnel de l'établissement)
	$DB_SQL = 'SELECT livret_user_id FROM livret_user ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_login=:login AND livret_user_id!=:id ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':login'=>$login,':id'=>$id);
	$DB_ROW = DB::queryRow(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	if(count($DB_ROW))
	{
		exit('Erreur : login déjà existant !');
	}
	if(!$password)
	{
		// Mettre à jour l'enregistrement sans génération d'un nouveau mot de passe
		$DB_SQL = 'UPDATE livret_user ';
		$DB_SQL.= 'SET livret_user_num_sconet=:num_sconet,livret_user_reference=:reference,livret_user_nom=:nom,livret_user_prenom=:prenom,livret_user_login=:login,livret_user_id_ent=:id_ent,livret_user_id_gepi=:id_gepi ';
		$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_id=:id ';
		$DB_SQL.= 'LIMIT 1';
		$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':num_sconet'=>$num_sconet,':reference'=>$reference,':nom'=>$nom,':prenom'=>$prenom,':login'=>$login,':id_ent'=>$id_ent,':id_gepi'=>$id_gepi,':id'=>$id);
		DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	}
	else
	{
		// Mettre à jour l'enregistrement avec génération d'un nouveau mot de passe
		$password = fabriquer_mdp();
		$password_crypte = crypter_mdp($password);
		$DB_SQL = 'UPDATE livret_user ';
		$DB_SQL.= 'SET livret_user_num_sconet=:num_sconet,livret_user_reference=:reference,livret_user_nom=:nom,livret_user_prenom=:prenom,livret_user_login=:login,livret_user_password=:password_crypte,livret_user_id_ent=:id_ent,livret_user_id_gepi=:id_gepi ';
		$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_id=:id ';
		$DB_SQL.= 'LIMIT 1';
		$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':num_sconet'=>$num_sconet,':reference'=>$reference,':nom'=>$nom,':prenom'=>$prenom,':login'=>$login,':password_crypte'=>$password_crypte,':id_ent'=>$id_ent,':id_gepi'=>$id_gepi,':id'=>$id);
		DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	}
	// Afficher le retour
	echo'<td>'.html($id_ent).'</td>';
	echo'<td>'.html($id_gepi).'</td>';
	echo'<td>'.html($num_sconet).'</td>';
	echo'<td>'.html($reference).'</td>';
	echo'<td>'.html($nom).'</td>';
	echo'<td>'.html($prenom).'</td>';
	echo'<td>'.html($login).'</td>';
	echo (!$password) ? '<td class="i">champ crypté</td>' : '<td class="new">'.html($password).' <img alt="" src="./_img/bulle_aide.png" title="Pensez à relever le mot de passe !" /></td>' ;
	echo'<td class="nu">';
	echo	'<q class="modifier" title="Modifier ce directeur."></q>';
	echo	'<q class="desactiver" title="Enlever ce directeur."></q>';
	echo'</td>';
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Désactiver un directeur existant
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
else if( ($action=='desactiver') && $id )
{
	// Mettre à jour l'enregistrement
	$DB_SQL = 'UPDATE livret_user ';
	$DB_SQL.= 'SET livret_user_statut=:statut ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_id=:id ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':id'=>$id,':statut'=>0);
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	// Afficher le retour
	echo'<td>ok</td>';
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
