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
		if( DB_tester_utilisateur_idENT($_SESSION['STRUCTURE_ID'],$id_ent) )
		{
			exit('Erreur : identifiant ENT déjà utilisé !');
		}
	}
	// Vérifier que l'identifiant GEPI est disponible (parmi tout le personnel de l'établissement)
	if($id_gepi)
	{
		if( DB_tester_utilisateur_idGepi($_SESSION['STRUCTURE_ID'],$id_gepi) )
		{
			exit('Erreur : identifiant Gepi déjà utilisé !');
		}
	}
	// Vérifier que le n° sconet est disponible (parmi les directeurs de cet établissement)
	if($num_sconet)
	{
		if( DB_tester_utilisateur_numSconet($_SESSION['STRUCTURE_ID'],$num_sconet,'directeur') )
		{
			exit('Erreur : n° sconet déjà utilisé !');
		}
	}
	// Vérifier que la référence est disponible (parmi les directeurs de cet établissement)
	if($reference)
	{
		if( DB_tester_utilisateur_reference($_SESSION['STRUCTURE_ID'],$reference,'directeur') )
		{
			exit('Erreur : référence déjà utilisée !');
		}
	}
	// Construire le login
	$login = fabriquer_login($prenom,$nom,'directeur');
	// Puis tester le login (parmi tout le personnel de l'établissement)
	if( DB_tester_login($_SESSION['STRUCTURE_ID'],$login) )
	{
		// Login pris : en chercher un autre en remplaçant la fin par des chiffres si besoin
		$login = DB_rechercher_login_disponible($_SESSION['STRUCTURE_ID'],$login);
	}
	// Construire le password
	$password = fabriquer_mdp();
	// Insérer l'enregistrement
	$user_id = DB_ajouter_utilisateur($_SESSION['STRUCTURE_ID'],$num_sconet,$reference,'directeur',$nom,$prenom,$login,$password,0,$id_ent,$id_gepi);
	// Afficher le retour
	echo'<tr id="id_'.$user_id.'" class="new">';
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
		if( DB_tester_utilisateur_idENT($_SESSION['STRUCTURE_ID'],$id_ent,$id) )
		{
			exit('Erreur : identifiant ENT déjà utilisé !');
		}
	}
	// Vérifier que l'identifiant GEPI est disponible (parmi tout le personnel de l'établissement)
	if($id_gepi)
	{
		if( DB_tester_utilisateur_idGepi($_SESSION['STRUCTURE_ID'],$id_gepi,$id) )
		{
			exit('Erreur : identifiant Gepi déjà utilisé !');
		}
	}
	// Vérifier que le n° sconet est disponible (parmi les directeurs de cet établissement)
	if($num_sconet)
	{
		if( DB_tester_utilisateur_numSconet($_SESSION['STRUCTURE_ID'],$num_sconet,'directeur',$id) )
		{
			exit('Erreur : n° sconet déjà utilisé !');
		}
	}
	// Vérifier que la référence est disponible (parmi les directeurs de cet établissement)
	if($reference)
	{
		if( DB_tester_utilisateur_reference($_SESSION['STRUCTURE_ID'],$reference,'directeur',$id) )
		{
			exit('Erreur : référence déjà utilisée !');
		}
	}
	// Vérifier que le login du directeur est disponible (parmi tout le personnel de l'établissement)
	if( DB_tester_login($_SESSION['STRUCTURE_ID'],$login,$id) )
	{
		exit('Erreur : login déjà existant !');
	}
	// Construire le password
	$password = $password ? fabriquer_mdp() : false; 
	// Mettre à jour l'enregistrement avec ou sans génération d'un nouveau mot de passe
	DB_modifier_utilisateur($_SESSION['STRUCTURE_ID'],$id,$num_sconet,$reference,$nom,$prenom,$login,$password,$id_ent,$id_gepi);
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
	DB_modifier_utilisateur_statut($_SESSION['STRUCTURE_ID'],$id,0);
	// Afficher le retour
	echo'<td>ok</td>';
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
