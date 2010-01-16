<?php
/**
 * @version $Id: etabl_niveau.ajax.php 8 2009-10-30 20:56:02Z thomas $
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
if( ($action=='Choisir') && (!in_array(96,$tab_id)) && (!in_array(97,$tab_id)) && (!in_array(98,$tab_id)) && (!in_array(99,$tab_id)) )
{
	$listing_niveaux = implode(',',$tab_id);
	modifier_niveaux_structure($_SESSION['STRUCTURE_ID'],$listing_niveaux);
	$_SESSION['NIVEAUX'] = $listing_niveaux;
	echo'ok';
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
