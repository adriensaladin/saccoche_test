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
$TITRE = "Export arborescence des items du socle";
?>

<?php
// Fabrication des éléments select du formulaire
$select_palier = afficher_select(DB_OPT_paliers_etabl($_SESSION['PALIERS']) , $select_nom='f_palier' , $option_first='non' , $selection=false , $optgroup='non');
?>

<div class="hc"><span class="manuel"><a class="pop_up" href="./aide.php?fichier=export_listings">DOC : Export listings.</a></span></div>

<form action="" id="form_export"><fieldset>
	<label class="tab" for="f_palier">Palier :</label><?php echo $select_palier ?><input type="hidden" id="f_palier_nom" name="f_palier_nom" value="" /><br />
	<span class="tab"></span><input id="f_submit" type="submit" value="Valider." /><label id="ajax_msg">&nbsp;</label><br />
</fieldset></form>

<hr />

<div id="bilan">
</div>
