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
$VERSION_JS_FILE += 6;
?>

<?php
$socle_PA = '<label for="f_socle_PA"><input type="checkbox" id="f_socle_PA" name="f_socle_PA" value="1" checked /> Pourcentage d\'items acquis</label>';
$socle_EV = '<label for="f_socle_EV"><input type="checkbox" id="f_socle_EV" name="f_socle_EV" value="1" checked /> État de validation</label>';
// Fabrication des éléments select du formulaire
$tab_cookie   = load_cookie_select('palier');
$tab_paliers  = DB_STRUCTURE_OPT_paliers_etabl($_SESSION['PALIERS']);
$tab_matieres = DB_STRUCTURE_OPT_matieres_etabl($_SESSION['MATIERES'],$transversal=true);
if($_SESSION['USER_PROFIL']=='directeur')
{
	$tab_groupes  = DB_STRUCTURE_OPT_classes_groupes_etabl();
	$select_eleves = '<option></option>'; // maj en ajax suivant le choix du groupe
	$of_g = 'val'; $sel_g = false; $og_g = 'oui'; $class_form_eleve = 'show'; $class_option_groupe = 'hide'; $class_option_mode = 'show';
	$check_option_lien = '';
}
elseif($_SESSION['USER_PROFIL']=='professeur')
{
	$tab_groupes  = DB_STRUCTURE_OPT_groupes_professeur($_SESSION['USER_ID']);
	$select_eleves = '<option></option>'; // maj en ajax suivant le choix du groupe
	$of_g = 'val'; $sel_g = false; $og_g = 'oui'; $class_form_eleve = 'show'; $class_option_groupe = 'hide'; $class_option_mode = 'show';
	$check_option_lien = '';
}

if( ($_SESSION['USER_PROFIL']=='parent') && ($_SESSION['NB_ENFANTS']!=1) )
{
	$tab_groupes  = $_SESSION['OPT_PARENT_CLASSES']; $GLOBALS['tab_select_optgroup'] = array('classe'=>'Classes');
	$select_eleves = '<option></option>'; // maj en ajax suivant le choix du groupe
	$of_g = 'oui'; $sel_g = false; $og_g = 'oui'; $class_form_eleve = 'show'; $class_option_groupe = 'hide'; $class_option_mode = 'hide';
	$socle_PA = (mb_substr_count($_SESSION['DROIT_SOCLE_POURCENTAGE_ACQUIS'],$_SESSION['USER_PROFIL'])) ? $socle_PA : '<del>Pourcentage d\'items acquis</del>' ;
	$socle_EV = (mb_substr_count($_SESSION['DROIT_SOCLE_ETAT_VALIDATION'],$_SESSION['USER_PROFIL']))    ? $socle_EV : '<del>État de validation</del>' ;
	$check_option_lien = ' checked';
}
if( ($_SESSION['USER_PROFIL']=='parent') && ($_SESSION['NB_ENFANTS']==1) )
{
	$tab_groupes  = array(0=>array('valeur'=>$_SESSION['ELEVE_CLASSE_ID'],'texte'=>$_SESSION['ELEVE_CLASSE_NOM'],'optgroup'=>'classe')); $GLOBALS['tab_select_optgroup'] = array('classe'=>'Classes');
	$select_eleves = '<option value="'.$_SESSION['OPT_PARENT_ENFANTS'][0]['valeur'].'" selected>'.html($_SESSION['OPT_PARENT_ENFANTS'][0]['texte']).'</option>';
	$of_g = 'non'; $sel_g = true;  $og_g = 'non'; $class_form_eleve = 'hide'; $class_option_groupe = 'show'; $class_option_mode = 'hide';
	$socle_PA = (mb_substr_count($_SESSION['DROIT_SOCLE_POURCENTAGE_ACQUIS'],$_SESSION['USER_PROFIL'])) ? $socle_PA : '<del>Pourcentage d\'items acquis</del>' ;
	$socle_EV = (mb_substr_count($_SESSION['DROIT_SOCLE_ETAT_VALIDATION'],$_SESSION['USER_PROFIL']))    ? $socle_EV : '<del>État de validation</del>' ;
	$check_option_lien = ' checked';
}

elseif($_SESSION['USER_PROFIL']=='eleve')
{
	$tab_groupes  = array(0=>array('valeur'=>$_SESSION['ELEVE_CLASSE_ID'],'texte'=>$_SESSION['ELEVE_CLASSE_NOM']));
	$select_eleves = '<option value="'.$_SESSION['USER_ID'].'" selected>'.html($_SESSION['USER_NOM'].' '.$_SESSION['USER_PRENOM']).'</option>';
	$of_g = 'non'; $sel_g = true;  $og_g = 'non'; $class_form_eleve = 'hide'; $class_option_groupe = 'show'; $class_option_mode = 'hide';
	$socle_PA = (mb_substr_count($_SESSION['DROIT_SOCLE_POURCENTAGE_ACQUIS'],$_SESSION['USER_PROFIL'])) ? $socle_PA : '<del>Pourcentage d\'items acquis</del>' ;
	$socle_EV = (mb_substr_count($_SESSION['DROIT_SOCLE_ETAT_VALIDATION'],$_SESSION['USER_PROFIL']))    ? $socle_EV : '<del>État de validation</del>' ;
	$check_option_lien = ' checked';
}
$of_p = (count($tab_paliers)<2) ? 'non' : 'oui' ;

$select_palier  = afficher_select($tab_paliers  , $select_nom='f_palier' , $option_first=$of_p , $selection=$tab_cookie['palier_id'] , $optgroup='non');
$select_groupe  = afficher_select($tab_groupes  , $select_nom='f_groupe' , $option_first=$of_g , $selection=$sel_g                   , $optgroup=$og_g);
$select_matiere = afficher_select($tab_matieres , $select_nom=false      , $option_first='non' , $selection=true                     , $optgroup='non');
?>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=releves_bilans__releve_socle">DOC : Détail de maîtrise du socle.</a></span></p>

<form id="form_select" action=""><fieldset>
	<label class="tab" for="f_palier">Palier :</label><?php echo $select_palier ?><input type="hidden" id="f_palier_nom" name="f_palier_nom" value="" /><label id="ajax_maj_pilier">&nbsp;</label><br />
	<label class="tab" for="f_pilier"><img alt="" src="./_img/bulle_aide.png" title="Utiliser la touche &laquo&nbsp;Shift&nbsp;&raquo; pour une sélection multiple contiguë.<br />Utiliser la touche &laquo&nbsp;Ctrl&nbsp;&raquo; pour une sélection multiple non contiguë." /> Compétence(s) :</label><select id="f_pilier" name="f_pilier" multiple size="7" class="hide"><option></option></select><input type="hidden" id="piliers" name="piliers" value="" /><p />
	<div class="<?php echo $class_form_eleve ?>">
		<label class="tab" for="f_groupe">Classe / groupe :</label><?php echo $select_groupe ?><label id="ajax_maj">&nbsp;</label><br />
		<label class="tab" for="f_eleve"><img alt="" src="./_img/bulle_aide.png" title="Utiliser la touche &laquo&nbsp;Shift&nbsp;&raquo; pour une sélection multiple contiguë.<br />Utiliser la touche &laquo&nbsp;Ctrl&nbsp;&raquo; pour une sélection multiple non contiguë." /> Élève(s) :</label><select id="f_eleve" name="f_eleve[]" multiple size="7"><?php echo $select_eleves ?></select><input type="hidden" id="eleves" name="eleves" value="" /><p />
	</div>
	<div id="option_groupe" class="<?php echo $class_option_groupe ?>">
		<label class="tab" for="f_opt_html"><img alt="" src="./_img/bulle_aide.png" title="Pour le format html, le détail des items peut être affiché." /> Infos items :</label><label for="f_coef"><input type="checkbox" id="f_coef" name="f_coef" value="1" /> Coefficients</label>&nbsp;&nbsp;&nbsp;<label for="f_socle"><input type="checkbox" id="f_socle" name="f_socle" value="1" checked /> Socle</label>&nbsp;&nbsp;&nbsp;<label for="f_lien"><input type="checkbox" id="f_lien" name="f_lien" value="1"<?php echo $check_option_lien ?> /> Liens de remédiation</label><br />
		<label class="tab" for="f_remplissage">Indications :</label><?php echo $socle_PA.'&nbsp;&nbsp;&nbsp;'.$socle_EV ?>
		<div id="option_mode" class="<?php echo $class_option_mode ?>">
			<label class="tab" for="f_mode">Items récoltés :</label><label for="f_mode_auto"><input type="radio" id="f_mode_auto" name="f_mode" value="auto" checked /> Automatique (recommandé) <img alt="" src="./_img/bulle_aide.png" title="Items de tous les référentiels, sauf pour la compétence 2 où on ne prend que les items des référentiels de la langue associée à l'élève." /></label>&nbsp;&nbsp;&nbsp;<label for="f_mode_manuel"><input type="radio" id="f_mode_manuel" name="f_mode" value="manuel" /> Sélection manuelle <img alt="" src="./_img/bulle_aide.png" title="Utiliser la touche &laquo&nbsp;Shift&nbsp;&raquo; pour une sélection multiple contiguë.<br />Utiliser la touche &laquo&nbsp;Ctrl&nbsp;&raquo; pour une sélection multiple non contiguë." /></label>
			<div id="div_matiere" class="hide"><span class="tab"></span><select id="f_matiere" name="f_matiere[]" multiple size="5"><?php echo $select_matiere ?></select><input type="hidden" id="matieres" name="matieres" value="" /></div>
		</div>
	</div><p />
	<span class="tab"></span><button id="bouton_valider" type="submit"><img alt="" src="./_img/bouton/generer.png" /> Générer.</button><label id="ajax_msg">&nbsp;</label>
</fieldset></form>

<hr />

<div id="bilan">
</div>

