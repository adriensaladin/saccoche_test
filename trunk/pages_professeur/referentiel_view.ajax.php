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
if($_SESSION['STRUCTURE_ID']==ID_DEMO) {}

$ids = (isset($_POST['ids'])) ? $_POST['ids'] : '';

if(mb_substr_count($ids,'_')!=2)
{
	exit('Erreur avec les données transmises !');
}

list($prefixe,$matiere_id,$niveau_id) = explode('_',$ids);
$matiere_id  = clean_entier($matiere_id);
$niveau_id   = clean_entier($niveau_id);

if( $matiere_id && $niveau_id )
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
