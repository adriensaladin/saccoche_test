<?php
/**
 * @version $Id: eval_demande_oui.php 8 2009-10-30 20:56:02Z thomas $
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
?>

<ul class="puce">
	<li><span class="manuel"><a class="pop_up" href="./aide.php?fichier=demandes_evaluations">DOC : Demandes d'évaluations.</a></span></li>
	<li><span class="astuce">Les élèves peuvent formuler au maximum <?php echo $_SESSION['ELEVE_DEMANDES'] ?> demande<?php echo ($_SESSION['ELEVE_DEMANDES']>1) ? 's' : '' ; ?> par matière.</span></li>
</ul>

<hr />

<?php
// Fabrication des éléments select du formulaire
$select_matiere = afficher_select(DB_OPT_matieres_professeur($_SESSION['STRUCTURE_ID'],$_SESSION['USER_ID']) , $select_nom='f_matiere' , $option_first='non' , $selection=false , $optgroup='non');
$select_groupe  = afficher_select(DB_OPT_groupes_professeur($_SESSION['STRUCTURE_ID'],$_SESSION['USER_ID'])  , $select_nom='f_groupe'  , $option_first='oui' , $selection=false , $optgroup='oui');
?>

<form action="" id="form0"><fieldset>
	<label class="tab" for="f_matiere">Matière :</label><?php echo $select_matiere ?><input type="hidden" id="f_matiere_nom" name="f_matiere_nom" value="" /><br />
	<label class="tab" for="f_groupe">Classe / groupe :</label><?php echo $select_groupe ?><input type="hidden" id="f_groupe_id" name="f_groupe_id" value="" /><input type="hidden" id="f_groupe_type" name="f_groupe_type" value="" /><input type="hidden" id="f_groupe_nom" name="f_groupe_nom" value="" /><br />
	<span class="tab"></span><input type="hidden" name="f_action" value="Afficher_demandes" /><input type="submit" value="Actualiser l'affichage." /><label id="ajax_msg0">&nbsp;</label>
</fieldset></form>

<form action="" id="form1">
	<hr />
	<table class="form">
		<thead>
			<tr>
				<th class="nu"></th>
				<th>Matière</th>
				<th>Item</th>
				<th>Popularité</th>
				<th>Classe / Groupe</th>
				<th>Élève</th>
				<th>Score</th>
				<th>Date</th>
				<th>Statut</th>
			</tr>
		</thead>
		<tbody>
		</tbody>
	</table>
	<div id="zone_actions" class="hide">
		<h2>Avec les demandes cochées :<input type="hidden" id="ids" name="ids" value="" /></h2>
		<fieldset>
			<label class="tab" for="f_quoi">Action :</label><select id="f_quoi" name="f_quoi"><option value=""></option><option value="creer">Créer une nouvelle évaluation.</option><option value="completer">Compléter une évaluation existante.</option><option value="changer">Changer le statut pour "évaluation en préparation".</option><option value="retirer">Retirer de la liste des demandes.</option></select>
		</fieldset>
		<fieldset id="step_qui" class="hide">
			<label class="tab" for="f_qui">Élève(s) :</label><select id="f_qui" name="f_qui"><option value="groupe"></option><option value="select">Élèves sélectionnés</option></select>
		</fieldset>
		<fieldset id="step_creer" class="hide">
			<label class="tab" for="f_date">Date :</label><input id="f_date" name="f_date" size="9" type="text" value="<?php echo date("d/m/Y") ?>" /><q class="date_calendrier" title="Cliquez sur cette image pour importer une date depuis un calendrier !"></q><br />
			<label class="tab" for="f_info">Description :</label><input id="f_info" name="f_info" size="30" type="text" value="" />
		</fieldset>
		<fieldset id="step_completer" class="hide">
			<label class="tab" for="f_devoir">Évaluation :</label><select id="f_devoir" name="f_devoir"><option></option></select><label id="ajax_maj1">&nbsp;</label>
		</fieldset>
		<fieldset id="step_suite" class="hide">
			<label class="tab" for="f_creer">Suite :</label><select id="f_suite" name="f_suite"><option value="changer">Changer ensuite le statut pour "évaluation en préparation".</option><option value="retirer">Retirer ensuite de la liste des demandes.</option></select>
		</fieldset>
		<p id="step_valider" class="hide">
			<input type="hidden" id="f_groupe_id2" name="f_groupe_id" value="" /><input type="hidden" id="f_groupe_type2" name="f_groupe_type" value="" />
			<span class="tab"></span><input type="submit" value="Valider." /><label id="ajax_msg1">&nbsp;</label>
		</p>
	</div>
</form>
