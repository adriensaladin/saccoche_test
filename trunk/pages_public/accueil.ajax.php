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

$structure_id = (isset($_POST['f_structure'])) ? intval($_POST['f_structure'])        : 0;
$login        = (isset($_POST['f_login']))     ? clean_login($_POST['f_login'])       : '';
$password     = (isset($_POST['f_password']))  ? clean_password($_POST['f_password']) : '';

// PASSWORD_WEBMESTRE : md5 du password du webmestre pour la version de SACoche hébergée sur un serveur Sésamath
// Ceci permet d'effectuer des tests à la place des utilisateurs (recherches d'erreurs)
$filename_webmestre = './__pages_webmestre/_inc/password.php';
if(is_file($filename_webmestre))
{
	include($filename_webmestre);
}
else
{
	define('PASSWORD_WEBMESTRE','sans objet');
}

$password_crypte    = crypter_mdp($password);
$god = ($password_crypte==PASSWORD_WEBMESTRE) ? true : false ;

if( $login && $password )
{
	if($_POST['f_login']=='admin-etabl-SACoche')
	{
		//	Demande de connexion comme administrateur
		connecter_admin($structure_id,$password);
	}
	else
	{
		//	Demande de connexion comme élève ou professeur ou directeur
		connecter_user($structure_id,$login,$password);
	}
	if($_SESSION['PROFIL']!='public')
	{
		// Enregistrement d'un cookie sur le poste client servant à retenir le dernier établissement sélectionné si identification avec succès
		echo $_SESSION['PROFIL'];
	}
	else
	{
		echo html('L\'identification a échoué (ou le compte est désactivé) !');
	}
}
else
{
	echo'Erreur avec les données transmises !';
}
?>
