<?php
/**
 * @version $Id: groupe_eleve.php 8 2009-10-30 20:56:02Z thomas $
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
$TITRE = "Affecter les élèves aux groupes de besoin";
?>

<?php
// Fabrication des éléments select du formulaire
$select_groupe        = afficher_select(DB_OPT_groupes_professeur($_SESSION['STRUCTURE_ID'],$_SESSION['USER_ID']) , $select_nom=false , $option_first='oui' , $selection=false , $optgroup='oui');
$select_groupe_besoin = afficher_select(DB_OPT_besoins_professeur($_SESSION['STRUCTURE_ID'],$_SESSION['USER_ID']) , $select_nom=false , $option_first='non' , $selection=false , $optgroup='non');
?>

<span class="manuel"><a class="pop_up" href="./aide.php?fichier=gestion_groupes_besoin">DOC : Gestion des groupes de besoin.</a></span>
<div class="danger">La composition d'un groupe de besoin déjà utilisé lors d'une évaluation ne devrait pas être modifiée (sinon vous n'aurez plus accès à certaines saisies) !</div>

<hr />

<form action="">
	<table><tr>
		<td class="nu" style="width:25em">
			<b>Liste des élèves :</b><br />
			<select id="f_groupe" name="f_groupe" class="t8"><?php echo $select_groupe ?></select><br />
			<select id="select_users" name="select_users[]" multiple="multiple" size="8" class="t8"><option value=""></option></select>
		</td>
		<td class="nu" style="width:20em">
			<b>Liste des groupes de besoin :</b><br />
			<select id="select_groupes" name="select_groupes[]" multiple="multiple" size="10" class="t8"><?php echo $select_groupe_besoin; ?></select>
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
