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
$TITRE = "Recherche ciblée";

// Fabrication des éléments select du formulaire
$tab_groupes = ($_SESSION['USER_PROFIL']=='professeur') ? DB_STRUCTURE_COMMUN::DB_OPT_groupes_professeur($_SESSION['USER_ID']) : DB_STRUCTURE_COMMUN::DB_OPT_regroupements_etabl(FALSE/*sans*/) ;
$select_groupe = Form::afficher_select($tab_groupes , $select_nom='f_groupe' , $option_first='oui' , $selection=false , $optgroup='oui');

Form::$tab_select_optgroup = array( 1=>'item(s) matière(s)' , 2=>'item du socle' , 3=>'compétence du socle' );
$select_critere_objet = Form::afficher_select(Form::$tab_select_recherche_objet , $select_nom='f_critere_objet' , $option_first='oui' , $selection=false , $optgroup='oui');

$select_critere_seuil_acquis = '<option value="NA" selected>'.html($_SESSION['ACQUIS_LEGENDE']['NA']).'</option><option value="VA">'.html($_SESSION['ACQUIS_LEGENDE']['VA']).'</option><option value="A">'.html($_SESSION['ACQUIS_LEGENDE']['A']).'</option><option value="X">Indéterminé</option>';
$select_critere_seuil_valide = '<option value="0" selected>Invalidé</option><option value="1">Validé</option><option value="2">Non renseigné</option>';

$select_selection_items = Form::afficher_select(DB_STRUCTURE_COMMUN::DB_OPT_selection_items($_SESSION['USER_ID']) , $select_nom='f_selection_items' , $option_first='oui' , $selection=false , $optgroup='non');

$select_matiere = Form::afficher_select(DB_STRUCTURE_COMMUN::DB_OPT_matieres_etabl()  , $select_nom=false             , $option_first='non' , $selection=true  , $optgroup='non');
$select_piliers = Form::afficher_select(DB_STRUCTURE_COMMUN::DB_OPT_paliers_piliers() , $select_nom='f_select_pilier' , $option_first='oui' , $selection=false , $optgroup='oui');

?>

<div><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=releves_bilans__releve_recherche">DOC : Recherche ciblée.</a></span></div>

<hr />

<form action="#" method="post" id="form_select"><fieldset>
	<p><label class="tab" for="f_groupe">Élèves :</label><?php echo $select_groupe ?><input type="hidden" id="f_groupe_id" name="f_groupe_id" value="" /><input type="hidden" id="f_groupe_type" name="f_groupe_type" value="" /><input type="hidden" id="f_groupe_nom" name="f_groupe_nom" value="" /></p>
	<label class="tab" for="f_critere_objet">Critère observé :</label><?php echo $select_critere_objet ?><br />
	<span id="span_matiere_items" class="hide">
		<label class="tab">Item(s) matière(s) :</label><input id="f_matiere_items_nombre" name="f_matiere_items_nombre" size="10" type="text" value="" readonly /><input id="f_matiere_items_liste" name="f_matiere_items_liste" type="hidden" value="" /><q class="choisir_compet" title="Voir ou choisir les items."></q><br />
	</span>
	<span id="span_socle_item" class="hide">
		<label class="tab">Item du socle :</label><input id="f_socle_item_nom" name="f_socle_item_nom" size="90" maxlength="256" type="text" value="" readonly /><input id="f_socle_item_id" name="f_socle_item_id" type="hidden" value="0" /><q class="choisir_compet" title="Sélectionner un item du socle commun."></q><br />
	</span>
	<span id="span_socle_pilier" class="hide">
		<label class="tab" for="f_select_pilier">Compétence (socle) :</label><?php echo $select_piliers ?><br />
	</span>
	<div id="div_matiere_items_bilanMS" class="hide">
		<label class="tab"><img alt="" src="./_img/bulle_aide.png" title="La question se pose notamment dans le cas d'items issus de référentiels de plusieurs matières." /> Coefficients :</label><label for="f_with_coef"><input type="checkbox" id="f_with_coef" name="f_with_coef" value="1" checked /> Prise en compte des coefficients</label><br />
	</div>
	<div id="div_socle_item_pourcentage" class="hide">
		<label class="tab">Items récoltés :</label><label for="f_mode_auto"><input type="radio" id="f_mode_auto" name="f_mode" value="auto" checked /> Automatique (recommandé) <img alt="" src="./_img/bulle_aide.png" title="Items de tous les référentiels de langue, sauf pour la compétence 2 où on ne prend que les items des référentiels de la langue associée à l'élève." /></label>&nbsp;&nbsp;&nbsp;<label for="f_mode_manuel"><input type="radio" id="f_mode_manuel" name="f_mode" value="manuel" /> Sélection manuelle <img alt="" src="./_img/bulle_aide.png" title="Pour choisir les matières des référentiels dont les items collectés sont issus." /></label>
		<div id="div_matiere" class="hide"><span class="tab"></span><select id="f_matiere" name="f_matiere[]" multiple size="5"><?php echo $select_matiere ?></select></div>
	</div>
	<span id="span_acquisition" class="hide">
		<label class="tab"><img alt="" src="./_img/bulle_aide.png" title="Utiliser la touche &laquo;&nbsp;Ctrl&nbsp;&raquo; pour une sélection multiple." /> État(s) :</label><select id="f_critere_seuil_acquis" name="f_critere_seuil_acquis[]" multiple size="4"><?php echo $select_critere_seuil_acquis ?></select><br />
	</span>
	<span id="span_validation" class="hide">
		<label class="tab"><img alt="" src="./_img/bulle_aide.png" title="Utiliser la touche &laquo;&nbsp;Ctrl&nbsp;&raquo; pour une sélection multiple." /> État(s) :</label><select id="f_critere_seuil_valide" name="f_critere_seuil_valide[]" multiple size="3"><?php echo $select_critere_seuil_valide ?></select><br />
	</span>
	<p><span class="tab"></span><button id="bouton_valider" type="submit" class="rechercher">Rechercher.</button><label id="ajax_msg">&nbsp;</label></p>
</fieldset></form>

<form action="#" method="post" id="zone_matieres_items" class="arbre_dynamique arbre_check hide">
	<div><label class="tab">Déployer / contracter</label><a href="m1" class="all_extend"><img alt="m1" src="./_img/deploy_m1.gif" /></a> <a href="m2" class="all_extend"><img alt="m2" src="./_img/deploy_m2.gif" /></a> <a href="n1" class="all_extend"><img alt="n1" src="./_img/deploy_n1.gif" /></a> <a href="n2" class="all_extend"><img alt="n2" src="./_img/deploy_n2.gif" /></a> <a href="n3" class="all_extend"><img alt="n3" src="./_img/deploy_n3.gif" /></a></div>
	<p>Cocher ci-dessous (<span class="astuce">cliquer sur un intitulé pour déployer son contenu</span>) :</p>
	<?php
	// Affichage de la liste des items pour toutes les matières d'un professeur ou toutes les matières de l'établissement si directeur, sur tous les niveaux
	$user_id = ($_SESSION['USER_PROFIL']=='professeur') ? $_SESSION['USER_ID'] : 0 ;
	$DB_TAB = DB_STRUCTURE_COMMUN::DB_recuperer_arborescence($user_id,$matiere_id=0,$niveau_id=0,$only_socle=false,$only_item=false,$socle_nom=false);
	echo afficher_arborescence_matiere_from_SQL($DB_TAB,$dynamique=true,$reference=true,$aff_coef=false,$aff_cart=false,$aff_socle='texte',$aff_lien=false,$aff_input=true);
	?>
	<p><span class="tab"></span><button id="valider_matieres_items" type="button" class="valider">Valider la sélection</button>&nbsp;&nbsp;&nbsp;<button id="annuler_matieres_items" type="button" class="annuler">Annuler / Retour</button></p>
	<hr />
	<p>
		<label class="tab" for="f_selection_items"><img alt="" src="./_img/bulle_aide.png" title="Pour choisir un regroupement d'items mémorisé." /> Initialisation</label><?php echo $select_selection_items ?><br />
		<label class="tab" for="f_liste_items_nom"><img alt="" src="./_img/bulle_aide.png" title="Pour enregistrer le groupe d'items cochés." /> Mémorisation</label><input id="f_liste_items_nom" name="f_liste_items_nom" size="30" type="text" value="" maxlength="60" /> <button id="f_enregistrer_items" type="button" class="fichier_export">Enregistrer</button><label id="ajax_msg_memo">&nbsp;</label>
	</p>
</form>

<form action="#" method="post" id="zone_socle_item" class="arbre_dynamique hide">
	<p>Cocher ci-dessous (<span class="astuce">cliquer sur un intitulé pour déployer son contenu</span>) :</p>
	<?php
	// Affichage de la liste des items du socle pour chaque palier
	$DB_TAB = DB_STRUCTURE_COMMUN::DB_recuperer_arborescence_palier();
	if(!empty($DB_TAB))
	{
		echo afficher_arborescence_socle_from_SQL($DB_TAB,$dynamique=true,$reference=false,$aff_input=true,$ids=false);
	}
	else
	{
		echo'<span class="danger"> Aucun palier du socle n\'est associé à l\'établissement ! L\'administrateur doit préalablement choisir les paliers évalués...</span>';
	}
	?>
	<p><span class="tab"></span><button id="valider_socle_item" type="button" class="valider">Valider le choix effectué</button>&nbsp;&nbsp;&nbsp;<button id="annuler_socle_item" type="button" class="annuler">Annuler / Retour</button></p>
</form>

<div id="bilan"></div>
