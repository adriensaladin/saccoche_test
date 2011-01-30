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
$VERSION_JS_FILE += 1;
?>

<?php
// Fabrication des éléments select du formulaire
$tab_paliers = DB_STRUCTURE_OPT_paliers_etabl($_SESSION['PALIERS']);
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

$select_palier = afficher_select($tab_paliers , $select_nom='f_palier' , $option_first='non' , $selection=false , $optgroup='non');
$select_groupe = afficher_select($tab_groupes , $select_nom='f_groupe' , $option_first='oui' , $selection=false , $optgroup='oui');
?>

<ul class="puce">
	<li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=releves_bilans__synthese_socle">DOC : Synthèse de maîtrise du socle.</a></span></li>
</ul>

<hr />

<form action="" id="form_select"><fieldset>
	<label class="tab" for="f_type">Type de synthèse :</label><label for="f_type_pourcentage"><input type="radio" id="f_type_pourcentage" name="f_type" value="pourcentage" /> Pourcentage d'items disciplinaires acquis</label>&nbsp;&nbsp;&nbsp;<label for="f_type_validation"><input type="radio" id="f_type_validation" name="f_type" value="validation" /> Validation des items et des compétences du socle</label><p />
	<label class="tab" for="f_palier">Palier :</label><?php echo $select_palier ?><input type="hidden" id="f_palier_nom" name="f_palier_nom" value="" /><label id="ajax_maj_pilier">&nbsp;</label><br />
	<label class="tab" for="f_pilier">Compétence :</label><select id="f_pilier" name="f_pilier" class="hide"><option></option></select><p />
	<label class="tab" for="f_groupe">Classe / groupe :</label><?php echo $select_groupe ?><input type="hidden" id="f_groupe_type" name="f_groupe_type" value="" /><input type="hidden" id="f_groupe_nom" name="f_groupe_nom" value="" /><label id="ajax_maj_eleve">&nbsp;</label><br />
	<label class="tab" for="f_eleve">Élève(s) :</label><select id="f_eleve" name="f_eleve[]" multiple="multiple" size="9" class="hide"><option></option></select><input type="hidden" id="eleves" name="eleves" value="" /><p />
	<span class="tab"></span><button id="bouton_valider" type="submit" class="hide"><img alt="" src="./_img/bouton/generer.png" /> Générer.</button><label id="ajax_msg">&nbsp;</label><br />
</fieldset></form>

<hr />

<div id="bilan">
</div>
