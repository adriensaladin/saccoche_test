<?php
/**
 * @version $Id: referentiel_view.ajax.php 8 2009-10-30 20:56:02Z thomas $
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
if($_SESSION['STRUCTURE_ID']==ID_DEMO) {}

$ref = (isset($_POST['ref'])) ? $_POST['ref'] : '';

if(mb_substr_count($ref,'_')!=2)
{
	exit('Erreur avec les données transmises !');
}

list($action,$matiere_id,$niveau_id) = explode('_',$ref);
$matiere_id  = clean_entier($matiere_id);
$niveau_id   = clean_entier($niveau_id);

if( ($action=='Voir') && $matiere_id && $niveau_id )
{
	// Affichage du bilan de la liste des items pour la matière et le niveau sélectionnés
	$DB_TAB = DB_select_arborescence($_SESSION['STRUCTURE_ID'],$prof_id=0,$matiere_id,$niveau_id,$socle_nom=true);
	echo afficher_arborescence($DB_TAB,$dynamique=false,$reference=false,$aff_coef='image',$aff_socle='image',$aff_lien='image',$aff_input=false);
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
