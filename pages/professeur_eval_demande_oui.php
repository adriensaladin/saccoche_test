<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010
 * 
 * ****************************************************************************************************
 * SACoche <http://sacoche.sesamath.net> - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath <http://www.sesamath.net> - Tous droits réservés.
 * Logiciel placé sous la licence libre GPL 3 <http://www.rodage.org/gpl-3.0.fr.html>.
 * ****************************************************************************************************
 * 
 * Ce fichier est une partie de SACoche.
 * 
 * SACoche est un logiciel libre ; vous pouvez le redistribuer ou le modifier suivant les termes 
 * de la “GNU General Public License” telle que publiée par la Free Software Foundation :
 * soit la version 3 de cette licence, soit (à votre gré) toute version ultérieure.
 * 
 * SACoche est distribué dans l’espoir qu’il vous sera utile, mais SANS AUCUNE GARANTIE :
 * sans même la garantie implicite de COMMERCIALISABILITÉ ni d’ADÉQUATION À UN OBJECTIF PARTICULIER.
 * Consultez la Licence Générale Publique GNU pour plus de détails.
 * 
 * Vous devriez avoir reçu une copie de la Licence Générale Publique GNU avec SACoche ;
 * si ce n’est pas le cas, consultez : <http://www.gnu.org/licenses/>.
 * 
 */

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}
?>

<ul class="puce">
	<li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=environnement_generalites__demandes_evaluations">DOC : Demandes d'évaluations.</a></span></li>
	<li><span class="astuce">Les élèves peuvent formuler au maximum <?php echo $_SESSION['ELEVE_DEMANDES'] ?> demande<?php echo ($_SESSION['ELEVE_DEMANDES']>1) ? 's' : '' ; ?> par matière.</span></li>
</ul>

<hr />

<?php
// Fabrication des éléments select du formulaire
$select_matiere = afficher_select(DB_STRUCTURE_OPT_matieres_professeur($_SESSION['USER_ID']) , $select_nom='f_matiere' , $option_first='non' , $selection=false , $optgroup='non');
$select_groupe  = afficher_select(DB_STRUCTURE_OPT_groupes_professeur($_SESSION['USER_ID'])  , $select_nom='f_groupe'  , $option_first='oui' , $selection=false , $optgroup='oui');
?>

<form action="" id="form0"><fieldset>
	<label class="tab" for="f_matiere">Matière :</label><?php echo $select_matiere ?><input type="hidden" id="f_matiere_nom" name="f_matiere_nom" value="" /><br />
	<label class="tab" for="f_groupe">Classe / groupe :</label><?php echo $select_groupe ?><input type="hidden" id="f_groupe_id" name="f_groupe_id" value="" /><input type="hidden" id="f_groupe_type" name="f_groupe_type" value="" /><input type="hidden" id="f_groupe_nom" name="f_groupe_nom" value="" /><br />
	<span class="tab"></span><input type="hidden" name="f_action" value="Afficher_demandes" /><button id="actualiser" type="submit"><img alt="" src="./_img/bouton/actualiser.png" /> Actualiser l'affichage.</button><label id="ajax_msg0">&nbsp;</label>
</fieldset></form>

<form action="" id="form1" class="hide">
	<hr />
	<table class="bilan_synthese" style="float:right;margin-left:1em;margin-right:1ex">
		<thead><tr><th>élève(s) sans demande</th></tr></thead>
		<tbody><tr id="tr_sans"><td class="nu"></td></tr></tbody>
	</table>
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
			<tr><td class="nu" colspan="9"></td></tr>
		</tbody>
	</table>
	<div id="zone_actions" class="hide">
		<div class="ti">
			<a href="#zone_actions" id="all_check">[ Tout cocher. ]</a>
			<a href="#zone_actions" id="all_uncheck">[ Tout décocher. ]</a>
		</div>
		<h2>Avec les demandes cochées :<input type="hidden" id="ids" name="ids" value="" /></h2>
		<fieldset>
			<label class="tab" for="f_quoi">Action :</label><select id="f_quoi" name="f_quoi"><option value=""></option><option value="creer">Créer une nouvelle évaluation.</option><option value="completer">Compléter une évaluation existante.</option><option value="changer">Changer le statut pour "évaluation en préparation".</option><option value="retirer">Retirer de la liste des demandes.</option></select>
		</fieldset>
		<fieldset id="step_qui" class="hide">
			<label class="tab" for="f_qui">Élève(s) :</label><select id="f_qui" name="f_qui"><option value="select">Élèves sélectionnés</option><option value="groupe"></option></select>
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
			<span class="tab"></span><button id="bouton_valider" type="submit"><img alt="" src="./_img/bouton/valider.png" /> Valider.</button><label id="ajax_msg1">&nbsp;</label>
		</p>
	</div>
</form>
