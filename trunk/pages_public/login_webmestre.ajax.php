<?php
/**
 * @version $Id: login_webmestre.ajax.php 7 2009-10-30 20:50:17Z thomas $
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2009
 * 
 * ****************************************************************************************************
 * SACoche [http://competences.sesamath.net] - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath [http://www.sesamath.net]
 * Distribution sous licence libre prévue pour l'été 2010.
 * ****************************************************************************************************
 * 
 * Connexion du webmestre pour accéder à la gestion des établissements de SACoche (serveur Sésamath uniquement).
 * 
 */

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}

$password  = (isset($_POST['f_password']))  ? clean_password($_POST['f_password']) : '';

// PASSWORD_WEBMESTRE : md5 du password du webmestre pour la version de SACoche hébergée sur un serveur Sésamath
$filename_webmestre = './__pages_webmestre/_inc/password.php';
include($filename_webmestre);

$password_crypte = md5('grain_de_sel'.$password);
$god = ($password_crypte==PASSWORD_WEBMESTRE) ? true : false ;

if($god)
{
	$_SESSION['GOD']              = $god;
	$_SESSION['PROFIL']           = 'webmestre';
	$_SESSION['STRUCTURE_ID']     = 0;
	$_SESSION['STRUCTURE']        = 'Administration du site';
	$_SESSION['USER_ID']          = 0;
	$_SESSION['USER_NOM']         = 'CRESPIN';
	$_SESSION['USER_PRENOM']      = 'Thomas';
	$_SESSION['USER_DESCR']       = '[webmestre] Thomas CRESPIN';
	$_SESSION['SSO']              = 'normal';
	$_SESSION['DUREE_INACTIVITE'] = 30;
	echo'webmestre';
}
else
{
	echo'Mot de passe incorrect !';
}
?>
