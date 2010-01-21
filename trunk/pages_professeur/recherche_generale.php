<?php
/**
 * @version $Id: recherche_generale.php 8 2009-10-30 20:56:02Z thomas $
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
$TITRE = "Consulter des référentiels d'établissements";
?>

<?php
// Fabrication des éléments select du formulaire
$select_matiere = afficher_select(DB_OPT_matieres_communes($_SESSION['MATIERES']) , $select_nom='f_matiere' , $option_first='oui' , $selection=false , $optgroup='non');
$select_niveau  = afficher_select(DB_OPT_niveaux_etabl($_SESSION['NIVEAUX'])      , $select_nom='f_niveau'  , $option_first='oui' , $selection=false , $optgroup='non');
?>

<form id="form_select" action="">
	<fieldset>
		<label class="tab" for="f_matiere">Matière <img alt="" src="./_img/bulle_aide.png" title="Seules les matières cochées par l'administrateur apparaissent." /> :</label><?php echo $select_matiere ?><br />
		<label class="tab" for="f_niveau">Niveau <img alt="" src="./_img/bulle_aide.png" title="Seules les niveaux cochés par l'administrateur apparaissent." /> :</label><?php echo $select_niveau ?><br />
		<span class="tab"></span><input id="f_submit" type="button" value="Valider." /><label id="ajax_msg">&nbsp;</label>
	</fieldset>
</form>

<hr />

<div id="choisir_referentiel" class="hide">
	<h2>Liste des référentiels disponibles - <span id="mat_niv"></span></h2>
	<ul class="donneur link">
	</ul>
</div>

<div id="zone_compet" class="hide">
</div>



