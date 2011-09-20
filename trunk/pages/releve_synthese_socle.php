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
$TITRE = "Synthèse de maîtrise du socle";
$VERSION_JS_FILE += 6;
?>

<?php
// Fabrication des éléments select du formulaire
$tab_cookie   = load_cookie_select('palier');
$tab_matieres = DB_STRUCTURE_OPT_matieres_etabl($_SESSION['MATIERES'],$transversal=true);
$tab_paliers  = DB_STRUCTURE_OPT_paliers_etabl($_SESSION['PALIERS']);
if($_SESSION['USER_PROFIL']=='directeur')
{
	$tab_groupes = DB_STRUCTURE_OPT_classes_groupes_etabl();
}
elseif($_SESSION['USER_PROFIL']=='professeur')
{
	$tab_groupes = DB_STRUCTURE_OPT_groupes_professeur($_SESSION['USER_ID']);
}
else
{
	$tab_groupes = 'Vous n\'avez pas un profil autorisé pour accéder au formulaire !';
}
$of_p = (count($tab_paliers)<2) ? 'non' : 'oui' ;

$select_matiere = afficher_select($tab_matieres , $select_nom=false      , $option_first='non' , $selection=true                     , $optgroup='non');
$select_palier  = afficher_select($tab_paliers  , $select_nom='f_palier' , $option_first=$of_p , $selection=$tab_cookie['palier_id'] , $optgroup='non');
$select_groupe  = afficher_select($tab_groupes  , $select_nom='f_groupe' , $option_first='oui' , $selection=false                    , $optgroup='oui');
?>

<ul class="puce">
	<li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=releves_bilans__synthese_socle">DOC : Synthèse de maîtrise du socle.</a></span></li>
</ul>

<hr />

<form action="" method="post" id="form_select"><fieldset>
	<label class="tab" for="f_type">Type de synthèse :</label><label for="f_type_pourcentage"><input type="radio" id="f_type_pourcentage" name="f_type" value="pourcentage" /> Pourcentage d'items disciplinaires acquis</label>&nbsp;&nbsp;&nbsp;<label for="f_type_validation"><input type="radio" id="f_type_validation" name="f_type" value="validation" /> Validation des items et des compétences du socle</label><br />
	<div id="option_mode" class="hide">
		<label class="tab" for="f_mode">Items récoltés :</label><label for="f_mode_auto"><input type="radio" id="f_mode_auto" name="f_mode" value="auto" checked /> Automatique (recommandé) <img alt="" src="./_img/bulle_aide.png" title="Items de tous les référentiels, sauf pour la compétence 2 où on ne prend que les items des référentiels de la langue associée à l'élève." /></label>&nbsp;&nbsp;&nbsp;<label for="f_mode_manuel"><input type="radio" id="f_mode_manuel" name="f_mode" value="manuel" /> Sélection manuelle <img alt="" src="./_img/bulle_aide.png" title="Utiliser la touche &laquo&nbsp;Shift&nbsp;&raquo; pour une sélection multiple contiguë.<br />Utiliser la touche &laquo&nbsp;Ctrl&nbsp;&raquo; pour une sélection multiple non contiguë." /></label>
		<div id="div_matiere" class="hide"><span class="tab"></span><select id="f_matiere" name="f_matiere[]" multiple size="5"><?php echo $select_matiere ?></select><input type="hidden" id="matieres" name="matieres" value="" /></div>
	</div><p />
	<label class="tab" for="f_palier">Palier :</label><?php echo $select_palier ?><input type="hidden" id="f_palier_nom" name="f_palier_nom" value="" /><label id="ajax_maj_pilier">&nbsp;</label><br />
	<label class="tab" for="f_pilier"><img alt="" src="./_img/bulle_aide.png" title="Utiliser la touche &laquo&nbsp;Shift&nbsp;&raquo; pour une sélection multiple contiguë.<br />Utiliser la touche &laquo&nbsp;Ctrl&nbsp;&raquo; pour une sélection multiple non contiguë." /> Compétence(s) :</label><select id="f_pilier" name="f_pilier" multiple size="7" class="hide"><option></option></select><input type="hidden" id="piliers" name="piliers" value="" /><p />
	<label class="tab" for="f_groupe">Classe / groupe :</label><?php echo $select_groupe ?><input type="hidden" id="f_groupe_type" name="f_groupe_type" value="" /><input type="hidden" id="f_groupe_nom" name="f_groupe_nom" value="" /><label id="ajax_maj_eleve">&nbsp;</label><br />
	<label class="tab" for="f_eleve"><img alt="" src="./_img/bulle_aide.png" title="Utiliser la touche &laquo&nbsp;Shift&nbsp;&raquo; pour une sélection multiple contiguë.<br />Utiliser la touche &laquo&nbsp;Ctrl&nbsp;&raquo; pour une sélection multiple non contiguë." /> Élève(s) :</label><select id="f_eleve" name="f_eleve[]" multiple size="7" class="hide"><option></option></select><input type="hidden" id="eleves" name="eleves" value="" /><p />
	<span class="tab"></span><button id="bouton_valider" type="submit" class="hide"><img alt="" src="./_img/bouton/generer.png" /> Générer.</button><label id="ajax_msg">&nbsp;</label><br />
</fieldset></form>

<hr />

<div id="bilan">
</div>
