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
$TITRE = "Export arborescence des items par matière";
?>

<?php
// Fabrication des éléments select du formulaire
$select_matiere = afficher_select(DB_OPT_matieres_professeur($_SESSION['STRUCTURE_ID'],$_SESSION['USER_ID']) , $select_nom='f_matiere' , $option_first='non' , $selection=false , $optgroup='non');
?>

<div class="hc"><span class="manuel"><a class="pop_up" href="./aide.php?fichier=export_listings">DOC : Export listings.</a></span></div>

<form action="" id="form_export"><fieldset>
	<label class="tab" for="f_matiere">Matière :</label><?php echo $select_matiere ?><input type="hidden" id="f_matiere_nom" name="f_matiere_nom" value="" /><br />
	<span class="tab"></span><input id="f_submit" type="submit" value="Valider." /><label id="ajax_msg">&nbsp;</label><br />
</fieldset></form>

<hr />

<div id="bilan">
</div>
