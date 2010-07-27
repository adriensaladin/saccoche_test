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
$TITRE = "Validation des items du socle";
$VERSION_JS_FILE += 0;
?>

<?php
// Fabrication des éléments select du formulaire
$tab_paliers = DB_STRUCTURE_OPT_paliers_etabl($_SESSION['PALIERS']);
if($_SESSION['USER_PROFIL']=='directeur')
{
	$tab_groupes = DB_STRUCTURE_OPT_classes_groupes_etabl();
}
if($_SESSION['USER_PROFIL']=='professeur')
{
	$tab_groupes = DB_STRUCTURE_OPT_groupes_professeur($_SESSION['USER_ID']);
}

$select_palier = afficher_select($tab_paliers , $select_nom='f_palier' , $option_first='non' , $selection=false , $optgroup='non');
$select_groupe = afficher_select($tab_groupes , $select_nom='f_groupe' , $option_first='oui' , $selection=false , $optgroup='oui');
?>

<div class="hc"><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=validation_socle__???">DOC : Validation des items du socle.</a></span></div>

<form action="" id="zone_choix"><fieldset>
	<label class="tab" for="f_palier">Palier :</label><?php echo $select_palier ?><br />
	<label class="tab" for="f_groupe">Classe / groupe :</label><?php echo $select_groupe ?><input type="hidden" id="f_groupe_type" name="f_groupe_type" value="" /><br />
	<span class="tab"></span><input type="hidden" name="f_action" value="Afficher_bilan" /><button id="actualiser" type="submit"><img alt="" src="./_img/bouton/actualiser.png" /> Afficher les états de validations.</button><label id="ajax_msg_choix">&nbsp;</label>
</fieldset></form>

<hr />

<form action="" id="zone_bilan">
</form>

<form action="" id="zone_validation" class="hide">
	<p><span class="tab"></span><button id="fermer_zone_validation" type="button"><img alt="" src="./_img/bouton/retourner.png" /> Annuler / Retour</button>&nbsp;&nbsp;&nbsp;<label id="ajax_msg_validation"></label></p>
	<fieldset id="fieldset_validation">
		<label class="tab" for="f_identite">Élève :</label><em id="identite">NOM Prénom</em><br />
		<label class="tab" for="f_validation">Validation :</label>
		<label for="v2" class="o">&nbsp;<input id="v2" name="f_vi" value="v2" type="radio"> NSP&nbsp;</label>&nbsp;
		<label for="v1" class="v">&nbsp;<input id="v1" name="f_vi" value="v1" type="radio"> OUI&nbsp;</label>&nbsp;
		<label for="v0" class="r">&nbsp;<input id="v0" name="f_vi" value="v0" type="radio"> NON&nbsp;</label>&nbsp;
		<button id="Enregistrer_validation" type="button"><img alt="" src="./_img/bouton/valider.png" /> Enregistrer puis...</button>
		<select id="f_ensuite" name="f_ensuite">
			<option value="retour_menu">retourner à la page de synthèse</option>
			<option value="next_user">passer à l'élève suivant</option>
			<option value="next_item">passer à l'item suivant</option>
			<option value="prev_user">passer à l'élève précédent</option>
			<option value="prev_item">passer à l'item précédent</option>
		</select>
		<input type="hidden" id="next_user" value="0" />
		<input type="hidden" id="next_item" value="0" />
		<input type="hidden" id="prev_user" value="0" />
		<input type="hidden" id="prev_item" value="0" /><p />
		<label class="tab" for="f_socle">Entrée du socle :</label><span id="P0" class="span_n1"></span><br /><span id="S0" class="span_n2"></span><br /><span id="E0" class="span_n3"></span><p />
		<label class="tab" for="f_indication">Item évalués :</label><span id="stats"></span>
		<div id="items">
		</div>
	</fieldset>
</form>

<div id="zone_paliers" class="hide">
	<?php
	// Récupérer l'arborescence de tous les paliers du socle pour s'en servir ensuite
	$DB_TAB = DB_STRUCTURE_recuperer_arborescence_palier($palier_id=false);
	echo afficher_arborescence_socle_from_SQL($DB_TAB,$dynamique=false,$reference=false,$aff_input=false,$ids=true);
	?>
</div>

