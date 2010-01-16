<?php
/**
 * @version $Id: date_periode.ajax.php 8 2009-10-30 20:56:02Z thomas $
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

$champ = (isset($_GET['champ'])) ? $_GET['champ'] : '' ;
$debut = strpos($champ,'debut');
$fin   = strpos($champ,'fin');

$tab_periodes = periodes_etabl();
$calendrier_affichage = '';
if(is_array($tab_periodes))
{
	foreach($tab_periodes as $key => $tab_infos)
	{
		list($periode_debut,$periode_fin) = explode(' ',$tab_infos['optgroup']);
		$calendrier_affichage .= $debut ? '<a class="actu" href="'.convert_date_mysql_to_french($periode_debut).'">'.html($tab_infos['texte']).' [ debut ]</a><br />' : '' ;
		$calendrier_affichage .= $fin   ? '<a class="actu" href="'.convert_date_mysql_to_french($periode_fin).'">'.html($tab_infos['texte']).' [ fin ]</a><br />' : '' ;
	}
}
else
{
	$calendrier_affichage .= $tab_periodes;
}
echo'<h5>Périodes</h5>';
echo'<form id="form_calque" action="">';
echo'	<h6>Cliquer sur un lien :</h6>';
echo'	<p>'.$calendrier_affichage.'</p>';
echo'	<div><input class="but" type="button" name="fermer" value="Annuler et Fermer" /></div>';
echo'</form>';

?>
