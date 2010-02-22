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
$TITRE = "Réintégrer / supprimer élèves";
?>

<?php
// Fabrication des éléments select du formulaire
$select_f_groupes = afficher_select(DB_OPT_regroupements_etabl($_SESSION['STRUCTURE_ID']) , $select_nom=false , $option_first='oui' , $selection=false , $optgroup='oui');
?>

<ul class="puce">
	<li><span class="manuel"><a class="pop_up" href="./aide.php?fichier=gestion_statuts">DOC : Statuts : désactiver / réintégrer / supprimer</a></span></li>
	<li><span class="danger">Supprimer un compte élève est une action irréversible, effaçant en particulier tous les scores associés !</span></li>
</ul>

<hr />

<form action="">
	<table><tr>
		<td class="nu" style="width:25em">
			<b>Liste des élèves :</b><br />
			<select id="f_groupe" name="f_groupe"><?php echo $select_f_groupes ?></select><br />
			<select id="select_eleves" name="select_eleves[]" multiple="multiple" size="10" class="hide"><option value=""></option></select>
		</td>
		<td class="nu" style="width:25em">
			<p><span class="astuce">Utiliser "<i>Shift + clic</i>" ou "<i>Ctrl + clic</i>"<br />pour une sélection multiple.</span></p>
			<p><input id="reintegrer" type="button" value="Réintégrer" /> ces élèves.</p>
			<p><input id="supprimer" type="button" value="Supprimer définitivement" /> ces comptes élèves.</p>
		</td>
	</tr></table>
</form>
<hr />
<p class="hc"><label id="ajax_msg">&nbsp;</label></p>
<div id="ajax_retour" class="hc"></div>