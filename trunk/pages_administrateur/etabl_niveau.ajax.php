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

$action = (isset($_POST['f_action'])) ? clean_texte($_POST['f_action']) : '';

$tab_id = (isset($_POST['tab_id']))   ? array_map('clean_entier',explode(',',$_POST['tab_id'])) : array() ;
function positif($n) {return($n);}
$tab_id = array_filter($tab_id,'positif');
sort($tab_id);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Choix de niveaux (excepté les niveaux transversaux liés aux paliers)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
if( ($action=='Choisir') && (count(array_intersect($GLOBALS['TAB_ID_NIVEAUX_PALIERS'],$tab_id))==0) )
{
	$listing_niveaux = implode(',',$tab_id);
	DB_modifier_niveaux($_SESSION['STRUCTURE_ID'],$listing_niveaux);
	// ne pas oublier de mettre aussi à jour la session
	$_SESSION['NIVEAUX'] = $listing_niveaux;
	echo'ok';
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
