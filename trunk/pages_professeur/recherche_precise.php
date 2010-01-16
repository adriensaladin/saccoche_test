<?php
/**
 * @version $Id: recherche_precise.php 8 2009-10-30 20:56:02Z thomas $
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
$TITRE = "Consulter les référentiels d'un établissement";
?>

<?php
// Fabrication des éléments select du formulaire
$select_etabl   = afficher_select(structures_partage() , $select_nom='f_etabl'   , $option_first='oui' , $selection=false , $optgroup='oui');
$select_matiere = afficher_select(matieres_communes()  , $select_nom='f_matiere' , $option_first='oui' , $selection=false , $optgroup='non');
?>

<form id="form_select" action="">
	<fieldset>
		<label class="tab" for="f_etabl">Établissement :</label><?php echo $select_etabl ?><br />
		<label class="tab" for="f_matiere">Matière <img alt="" src="./_img/bulle_aide.png" title="Seules les matières cochées par l'administrateur apparaissent." /> :</label><?php echo $select_matiere ?><br />
		<span class="tab"></span><input id="f_submit" type="button" value="Valider." /><label id="ajax_msg">&nbsp;</label>
	</fieldset>
</form>

<div id="zone_compet">
</div>



