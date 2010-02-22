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

$password_ancien  = (isset($_POST['f_password0'])) ? clean_password($_POST['f_password0']) : '';
$password_nouveau = (isset($_POST['f_password1'])) ? clean_password($_POST['f_password1']) : '';

if( $password_ancien && $password_nouveau )
{
	echo DB_changer_son_mdp($_SESSION['STRUCTURE_ID'],$_SESSION['USER_ID'],$_SESSION['PROFIL'],$password_ancien,$password_nouveau);
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
