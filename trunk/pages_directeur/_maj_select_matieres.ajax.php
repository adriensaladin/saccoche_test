<?php
/**
 * @version $Id: _maj_select_matieres.ajax.php 8 2009-10-30 20:56:02Z thomas $
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2009
 * 
 * ****************************************************************************************************
 * SACoche [http://competences.sesamath.net] - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath [http://www.sesamath.net]
 * Distribution sous licence libre prévue pour l'été 2010.
 * ****************************************************************************************************
 * 
 * Mettre à jour l'élément de formulaire "f_eleve" et le renvoyer en HTML
 * 
 */

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}
if($_SESSION['STRUCTURE_ID']==ID_DEMO) {}

$groupe  = (isset($_POST['f_groupe']))  ? clean_entier($_POST['f_groupe'])  : 0;
$matiere = (isset($_POST['f_matiere'])) ? clean_entier($_POST['f_matiere']) : 0;

if(!$groupe)
{
	exit('Erreur avec les données transmises !');
}

echo afficher_select(matieres_groupe($groupe) , $select_nom=false , $option_first='oui' , $selection=$matiere , $optgroup='non');

?>
