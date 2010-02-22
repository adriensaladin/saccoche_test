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
$TITRE = "Affecter les professeurs aux groupes";
?>

<?php
// Fabrication des éléments select du formulaire
$select_professeurs = afficher_select(DB_OPT_professeurs_etabl($_SESSION['STRUCTURE_ID']) , $select_nom=false , $option_first='non' , $selection=false , $optgroup='non');
$select_groupes     = afficher_select(DB_OPT_groupes_etabl($_SESSION['STRUCTURE_ID'])     , $select_nom=false , $option_first='non' , $selection=false , $optgroup='non');
?>

<p class="hc">
	<span class="manuel"><a class="pop_up" href="./aide.php?fichier=gestion_groupes">DOC : Gestion des groupes</a></span>
</p>

<hr />

<form action="">
	<table><tr>
		<td class="nu" style="width:25em">
			<b>Liste des professeurs :</b><br />
			<select id="select_professeurs" name="select_professeurs[]" multiple="multiple" size="10" class="t8"><?php echo $select_professeurs; ?></select>
		</td>
		<td class="nu" style="width:20em">
			<b>Liste des groupes :</b><br />
			<select id="select_groupes" name="select_groupes[]" multiple="multiple" size="10" class="t8"><?php echo $select_groupes; ?></select>
		</td>
		<td class="nu" style="width:25em">
			<p><span class="astuce">Utiliser "<i>Shift + clic</i>" ou "<i>Ctrl + clic</i>"<br />pour une sélection multiple.</span></p>
			<input id="ajouter" type="button" value="Ajouter" /> ces associations.<br />
			<input id="retirer" type="button" value="Retirer" /> ces associations.
			<p><label id="ajax_msg">&nbsp;</label></p>
		</td>
	</tr></table>
</form>

<div id="bilan">
</div>

