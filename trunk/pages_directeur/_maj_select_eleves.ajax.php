<?php
/**
 * @version $Id: _maj_select_eleves.ajax.php 8 2009-10-30 20:56:02Z thomas $
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

$groupe = (isset($_POST['f_groupe'])) ? clean_entier($_POST['f_groupe']) : 0;
$type   = (isset($_POST['f_type']))   ? clean_texte($_POST['f_type'])    : '';
$statut = (isset($_POST['f_statut'])) ? clean_entier($_POST['f_statut']) : 0;

$tab_types = array('Classes'=>'classe' , 'Groupes'=>'groupe' , 'Besoins'=>'groupe');

if( (!$groupe) || (!isset($tab_types[$type])) )
{
	exit('Erreur avec les données transmises !');
}

$type = $tab_types[$type];

$DB_TAB = eleves_regroupement($type,$groupe,$statut);

echo afficher_select($DB_TAB , $select_nom=false , $option_first='non' , $selection=true , $optgroup='non');
?>
