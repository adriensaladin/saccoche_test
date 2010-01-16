<?php
/**
 * @version $Id: etabl_options-eleve.ajax.php 8 2009-10-30 20:56:02Z thomas $
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

$f_eleve_options = (isset($_POST['f_eleve_options'])) ? clean_texte($_POST['f_eleve_options']) : 'erreur';

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Options de l'environnement élève
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if($f_eleve_options=='')
{
	$test_options = true;
}
else
{
	$nettoyage = str_replace( array('ms','pv','as') , '*' , $f_eleve_options );
	$nettoyage = str_replace( '*,' , '' , $nettoyage.',' );
	$test_options = ($nettoyage=='') ? true : false;
}

if($test_options)
{
	modifier_eleve_options_structure($_SESSION['STRUCTURE_ID'],$f_eleve_options);
	// ne pas oublier de mettre aussi à jour la session
	$_SESSION['ELEVE_OPTIONS'] = $f_eleve_options;
	echo'ok';
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
