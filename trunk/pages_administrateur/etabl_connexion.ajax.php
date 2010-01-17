<?php
/**
 * @version $Id: etabl_connexion.ajax.php 8 2009-10-30 20:56:02Z thomas $
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

$f_mode_connexion = (isset($_POST['f_mode_connexion'])) ? clean_texte($_POST['f_mode_connexion']) : '';

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Mode de connexion (normal, SSO...)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

require_once('./_inc/tableau_sso.php');	// Charge $tab_sso['nom'] = array('txt'=>'...' , 'doc'=>'...');

if(isset($tab_sso[$f_mode_connexion]))
{
	DB_modifier_mode_connexion($_SESSION['STRUCTURE_ID'],$f_mode_connexion);
	// ne pas oublier de mettre aussi à jour la session
	// normalement faudrait pas car connecté avec l'ancien mode, mais sinon pb d'initalisation du focmulaire
	$_SESSION['SSO']  = $f_mode_connexion;
	echo'ok';
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
