<?php
/**
 * @version $Id: etabl_palier.ajax.php 8 2009-10-30 20:56:02Z thomas $
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

$action = (isset($_POST['f_action'])) ? clean_texte($_POST['f_action']) : '';

$tab_id = (isset($_POST['tab_id']))   ? array_map('clean_entier',explode(',',$_POST['tab_id'])) : array() ;
function positif($n) {return($n);}
$tab_id = array_filter($tab_id,'positif');
sort($tab_id);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Choix de paliers du socle
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
if( ($action=='Choisir') && count($tab_id) )
{
	$listing_paliers = implode(',',$tab_id);
	DB_modifier_paliers($_SESSION['STRUCTURE_ID'],$listing_paliers);
	// ne pas oublier de mettre aussi à jour la session
	$_SESSION['PALIERS'] = $listing_paliers;
	echo'ok';
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
