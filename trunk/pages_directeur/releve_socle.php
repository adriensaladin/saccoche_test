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
$TITRE = "Attestation de maîtrise du socle";
?>

<?php
// Fabrication des éléments select du formulaire
$select_palier = afficher_select(DB_OPT_paliers_etabl($_SESSION['PALIERS'])              , $select_nom='f_palier' , $option_first='non' , $selection=false , $optgroup='non');
$select_groupe = afficher_select(DB_OPT_classes_groupes_etabl($_SESSION['STRUCTURE_ID']) , $select_nom='f_groupe' , $option_first='val' , $selection=false , $optgroup='oui');
?>

<p class="hc"><span class="manuel"><a class="pop_up" href="./aide.php?fichier=releve_socle">DOC : Attestation de maîtrise du socle commun.</a></span></p>

<form id="form_select" action=""><fieldset>
	<label class="tab" for="f_detail">Affichage :</label><label for="f_detail_complet"><input type="radio" id="f_detail_complet" name="f_detail" value="complet" checked="checked" /> Attestation complète</label>&nbsp;&nbsp;&nbsp;<label for="f_detail_extrait"><input type="radio" id="f_detail_extrait" name="f_detail" value="extrait" /> Uniquement les intitulés</label><p />
	<label class="tab" for="f_palier">Palier :</label><?php echo $select_palier ?><input type="hidden" id="f_palier_nom" name="f_palier_nom" value="" /><p />
	<label class="tab" for="f_groupe">Élève(s) :</label><?php echo $select_groupe ?><label id="ajax_maj">&nbsp;</label><br />
	<span id="option_groupe" class="hide">
		<label class="tab" for="f_remplissage">Remplissage :</label><select id="f_remplissage" name="f_remplissage"><option value="vide">attestation vierge de toute validation</option><option value="plein" selected="selected">attestation avec les états de validation</option></select><br />
	</span>
	<span class="tab"></span><select id="f_eleve" name="f_eleve[]" multiple="multiple" size="9"><option></option></select><input type="hidden" id="eleves" name="eleves" value="" /><p />
	<span class="tab"></span><input id="f_submit" type="submit" value="Valider." /><label id="ajax_msg">&nbsp;</label><br />
</fieldset></form>

<hr />

<div id="bilan">
</div>

