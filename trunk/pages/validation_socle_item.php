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
$TITRE = "Valider les items du socle";
$VERSION_JS_FILE += 13;
// Remarque : on ne peut être pp que d'une classe, pas d'un groupe, donc si seuls les PP ont un accès par mis les profs, ils ne peuvent trier les élèves que par classes
?>

<?php
// Indication des profils ayant accès à cette page
require_once('./_inc/tableau_profils.php'); // Charge $tab_profil_libelle[$profil][court|long][1|2]
$tab_profils = array('directeur','professeur','profprincipal');
$str_objet = str_replace( array(',aucunprof','aucunprof,','aucunprof') , '' , $_SESSION['DROIT_VALIDATION_ENTREE'] );
foreach($tab_profils as $profil)
{
	$str_objet = str_replace($profil,$tab_profil_libelle[$profil]['long'][2],$str_objet);
}
$texte = ($str_objet=='') ? 'aucun' : ( (strpos($str_objet,',')===false) ? 'uniquement les '.$str_objet : str_replace(',',' + ',$str_objet) ) ;

// Fabrication des éléments select du formulaire
$tab_paliers  = DB_STRUCTURE_OPT_paliers_etabl($_SESSION['PALIERS']);
$tab_matieres = DB_STRUCTURE_OPT_matieres_etabl($_SESSION['MATIERES'],$transversal=true);
if( ($_SESSION['USER_PROFIL']=='directeur') && (strpos($_SESSION['DROIT_VALIDATION_ENTREE'],'directeur')!==false) )
{
	$tab_groupes = DB_STRUCTURE_OPT_classes_groupes_etabl();
	$of_g = 'oui'; $og_g = 'oui'; 
}
elseif( ($_SESSION['USER_PROFIL']=='professeur') && (strpos($_SESSION['DROIT_VALIDATION_ENTREE'],'professeur')!==false) )
{
	$tab_groupes = DB_STRUCTURE_OPT_groupes_professeur($_SESSION['USER_ID']);
	$of_g = 'oui'; $og_g = 'oui'; 
}
elseif( (DB_STRUCTURE_tester_prof_principal($_SESSION['USER_ID'])) && (strpos($_SESSION['DROIT_VALIDATION_ENTREE'],'profprincipal')!==false) )
{
	$tab_groupes = DB_STRUCTURE_OPT_classes_prof_principal($_SESSION['USER_ID']);
	$of_g = 'non'; $og_g = 'non'; 
}
else
{
	$tab_groupes = 'Vous n\'avez pas un profil autorisé pour accéder au formulaire !';
	$of_g = 'non'; $og_g = 'non'; 
}

$select_palier  = afficher_select($tab_paliers  , $select_nom='f_palier' , $option_first='non' , $selection=false , $optgroup='non');
$select_groupe  = afficher_select($tab_groupes  , $select_nom='f_groupe' , $option_first=$of_g , $selection=false , $optgroup=$og_g);
$select_matiere = afficher_select($tab_matieres , $select_nom=false      , $option_first='non' , $selection=true  , $optgroup='non');
?>

<script type="text/javascript">
	var seuil_R = parseInt("<?php echo $_SESSION['CALCUL_SEUIL']['R'] ?>");
	var seuil_V = parseInt("<?php echo $_SESSION['CALCUL_SEUIL']['V'] ?>");
</script>

<ul class="puce">
	<li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=referentiels_socle__socle_valider_item">DOC : Valider des items du socle.</a></span></li>
	<li><span class="astuce">Profils autorisés par les administrateurs : <span class="u"><?php echo $texte ?></span>.</span></li>
</ul>

<hr />

<form action="" id="zone_choix"><fieldset>
	<p>
		<label class="tab" for="f_palier">Palier :</label><?php echo $select_palier ?><label id="ajax_maj_pilier">&nbsp;</label><br />
		<label class="tab" for="f_pilier">Compétence :</label><select id="f_pilier" name="f_pilier" class="hide"><option></option></select><label id="ajax_maj_domaine">&nbsp;</label><br />
		<label class="tab" for="f_domaine"><img alt="" src="./_img/bulle_aide.png" title="Utiliser la touche &laquo&nbsp;Shift&nbsp;&raquo; pour une sélection multiple contiguë.<br />Utiliser la touche &laquo&nbsp;Ctrl&nbsp;&raquo; pour une sélection multiple non contiguë." /> Domaine(s) :</label><select id="f_domaine" name="f_domaine[]" multiple size="5" class="hide"><option></option></select><input type="hidden" id="domaines" name="domaines" value="" />
	</p>
	<p>
		<label class="tab" for="f_groupe">Classe / groupe :</label><?php echo $select_groupe ?><input type="hidden" id="f_groupe_type" name="f_groupe_type" value="" /><label id="ajax_maj_eleve">&nbsp;</label><br />
		<label class="tab" for="f_eleve"><img alt="" src="./_img/bulle_aide.png" title="Utiliser la touche &laquo&nbsp;Shift&nbsp;&raquo; pour une sélection multiple contiguë.<br />Utiliser la touche &laquo&nbsp;Ctrl&nbsp;&raquo; pour une sélection multiple non contiguë." /> Élève(s) :</label><select id="f_eleve" name="f_eleve[]" multiple size="9" class="hide"><option></option></select><input type="hidden" id="eleves" name="eleves" value="" />
	</p>
	<label class="tab" for="f_mode">Items récoltés :</label><label for="f_mode_auto"><input type="radio" id="f_mode_auto" name="f_mode" value="auto" checked /> Automatique (recommandé) <img alt="" src="./_img/bulle_aide.png" title="Items de tous les référentiels, sauf pour la compétence 2 où on ne prend que les items des référentiels de la langue associée à l'élève." /></label>&nbsp;&nbsp;&nbsp;<label for="f_mode_manuel"><input type="radio" id="f_mode_manuel" name="f_mode" value="manuel" /> Sélection manuelle <img alt="" src="./_img/bulle_aide.png" title="Utiliser la touche &laquo&nbsp;Shift&nbsp;&raquo; pour une sélection multiple contiguë.<br />Utiliser la touche &laquo&nbsp;Ctrl&nbsp;&raquo; pour une sélection multiple non contiguë." /></label>
	<div id="div_matiere" class="hide"><span class="tab"></span><select id="f_matiere" name="f_matiere[]" multiple size="5"><?php echo $select_matiere ?></select><input type="hidden" id="matieres" name="matieres" value="" /></div>
	<p><span class="tab"></span><input type="hidden" name="f_action" value="Afficher_bilan" /><button id="Afficher_validation" type="submit" class="hide"><img alt="" src="./_img/bouton/valider.png" /> Afficher le tableau des validations.</button><label id="ajax_msg_choix">&nbsp;</label></p>
</fieldset></form>

<form action="" id="zone_validation" class="hide">
	<table id="tableau_validation">
		<tbody><tr><td></td></tr></tbody>
	</table>
</form>

<div id="zone_information" class="hide" style="height:25ex">
	<h4>Aide à la décision : bilan des évaluations associées à un item du socle <span id="span_restriction"></span></h4>
	<ul class="puce">
		<li><img alt="" src="./_img/menu/profil_eleve.png" /> <span id="identite"></span></li>
		<li><img alt="" src="./_img/folder/folder_n3.png" /> <span id="entree"></span></li>
		<li><img alt="" src="./_img/bouton/stats.png" /> <span id="stats"></span><label id="ajax_msg_information"></label></li>
	</ul>
	<div id="items">
	</div>
</div>
