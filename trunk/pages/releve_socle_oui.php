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

<?php
Formulaire::load_choix_memo();
$check_aff_coef    = (Formulaire::$tab_choix['aff_coef'])       ? ' checked' : '' ;
$check_aff_socle   = (Formulaire::$tab_choix['aff_socle'])      ? ' checked' : '' ;
$check_aff_lien    = (Formulaire::$tab_choix['aff_lien'])       ? ' checked' : '' ;
$check_socle_PA    = (Formulaire::$tab_choix['aff_socle_PA'])   ? ' checked' : '' ;
$check_socle_EV    = (Formulaire::$tab_choix['aff_socle_EV'])   ? ' checked' : '' ;
if(in_array($_SESSION['USER_PROFIL'],array('parent','eleve')))
{
	// Une éventuelle restriction d'accès doit surcharger toute mémorisation antérieure de formulaire
	$check_socle_PA = (mb_substr_count($_SESSION['DROIT_SOCLE_POURCENTAGE_ACQUIS'],$_SESSION['USER_PROFIL'])) ? ' checked' : '' ;
	$check_socle_EV = (mb_substr_count($_SESSION['DROIT_SOCLE_ETAT_VALIDATION']   ,$_SESSION['USER_PROFIL'])) ? ' checked' : '' ;
}
$check_mode_auto   = (Formulaire::$tab_choix['mode']=='auto')   ? ' checked' : '' ;
$check_mode_manuel = (Formulaire::$tab_choix['mode']=='manuel') ? ' checked' : '' ;
$class_div_matiere = (Formulaire::$tab_choix['mode']=='manuel') ? 'show'     : 'hide' ;
$socle_PA = '<label for="f_socle_PA"><input type="checkbox" id="f_socle_PA" name="f_socle_PA" value="1"'.$check_socle_PA.' /> Pourcentage d\'items acquis</label>';
$socle_EV = '<label for="f_socle_EV"><input type="checkbox" id="f_socle_EV" name="f_socle_EV" value="1"'.$check_socle_EV.' /> État de validation</label>';
if($_SESSION['USER_PROFIL']=='directeur')
{
	$tab_groupes  = DB_STRUCTURE_COMMUN::DB_OPT_classes_groupes_etabl();
	$multiple_eleve = ' multiple size="7"';
	$select_eleves = '<option></option>'; // maj en ajax suivant le choix du groupe
	$of_g = 'val'; $sel_g = false; $og_g = 'oui'; $class_form_eleve = 'show'; $class_option_groupe = 'hide'; $class_option_mode = 'show';
}
elseif($_SESSION['USER_PROFIL']=='professeur')
{
	$tab_groupes  = DB_STRUCTURE_COMMUN::DB_OPT_groupes_professeur($_SESSION['USER_ID']);
	$multiple_eleve = ' multiple size="7"';
	$select_eleves = '<option></option>'; // maj en ajax suivant le choix du groupe
	$of_g = 'val'; $sel_g = false; $og_g = 'oui'; $class_form_eleve = 'show'; $class_option_groupe = 'hide'; $class_option_mode = 'show';
}

if( ($_SESSION['USER_PROFIL']=='parent') && ($_SESSION['NB_ENFANTS']!=1) )
{
	$tab_groupes  = $_SESSION['OPT_PARENT_CLASSES']; Formulaire::$tab_select_optgroup = array('classe'=>'Classes');
	$multiple_eleve = ''; // volontaire
	$select_eleves = '<option></option>'; // maj en ajax suivant le choix du groupe
	$of_g = 'oui'; $sel_g = false; $og_g = 'oui'; $class_form_eleve = 'show'; $class_option_groupe = 'hide'; $class_option_mode = 'hide';
	$socle_PA = (mb_substr_count($_SESSION['DROIT_SOCLE_POURCENTAGE_ACQUIS'],$_SESSION['USER_PROFIL'])) ? $socle_PA : '<del>Pourcentage d\'items acquis</del>' ;
	$socle_EV = (mb_substr_count($_SESSION['DROIT_SOCLE_ETAT_VALIDATION'],$_SESSION['USER_PROFIL']))    ? $socle_EV : '<del>État de validation</del>' ;
}
if( ($_SESSION['USER_PROFIL']=='parent') && ($_SESSION['NB_ENFANTS']==1) )
{
	$tab_groupes  = array(0=>array('valeur'=>$_SESSION['ELEVE_CLASSE_ID'],'texte'=>$_SESSION['ELEVE_CLASSE_NOM'],'optgroup'=>'classe')); Formulaire::$tab_select_optgroup = array('classe'=>'Classes');
	$multiple_eleve = '';
	$select_eleves = '<option value="'.$_SESSION['OPT_PARENT_ENFANTS'][0]['valeur'].'" selected>'.html($_SESSION['OPT_PARENT_ENFANTS'][0]['texte']).'</option>';
	$of_g = 'non'; $sel_g = true;  $og_g = 'non'; $class_form_eleve = 'hide'; $class_option_groupe = 'show'; $class_option_mode = 'hide';
	$socle_PA = (mb_substr_count($_SESSION['DROIT_SOCLE_POURCENTAGE_ACQUIS'],$_SESSION['USER_PROFIL'])) ? $socle_PA : '<del>Pourcentage d\'items acquis</del>' ;
	$socle_EV = (mb_substr_count($_SESSION['DROIT_SOCLE_ETAT_VALIDATION'],$_SESSION['USER_PROFIL']))    ? $socle_EV : '<del>État de validation</del>' ;
}

elseif($_SESSION['USER_PROFIL']=='eleve')
{
	$tab_groupes  = array(0=>array('valeur'=>$_SESSION['ELEVE_CLASSE_ID'],'texte'=>$_SESSION['ELEVE_CLASSE_NOM']));
	$multiple_eleve = '';
	$select_eleves = '<option value="'.$_SESSION['USER_ID'].'" selected>'.html($_SESSION['USER_NOM'].' '.$_SESSION['USER_PRENOM']).'</option>';
	$of_g = 'non'; $sel_g = true;  $og_g = 'non'; $class_form_eleve = 'hide'; $class_option_groupe = 'show'; $class_option_mode = 'hide';
	$socle_PA = (mb_substr_count($_SESSION['DROIT_SOCLE_POURCENTAGE_ACQUIS'],$_SESSION['USER_PROFIL'])) ? $socle_PA : '<del>Pourcentage d\'items acquis</del>' ;
	$socle_EV = (mb_substr_count($_SESSION['DROIT_SOCLE_ETAT_VALIDATION'],$_SESSION['USER_PROFIL']))    ? $socle_EV : '<del>État de validation</del>' ;
}
$tab_paliers  = DB_STRUCTURE_COMMUN::DB_OPT_paliers_etabl($_SESSION['PALIERS']);
$tab_matieres = DB_STRUCTURE_COMMUN::DB_OPT_matieres_etabl($_SESSION['MATIERES'],$transversal=true);
$of_p = (count($tab_paliers)<2) ? 'non' : 'oui' ;

$select_palier  = Formulaire::afficher_select($tab_paliers  , $select_nom='f_palier' , $option_first=$of_p , $selection=Formulaire::$tab_choix['palier_id'] , $optgroup='non');
$select_groupe  = Formulaire::afficher_select($tab_groupes  , $select_nom='f_groupe' , $option_first=$of_g , $selection=$sel_g                               , $optgroup=$og_g);
$select_matiere = Formulaire::afficher_select($tab_matieres , $select_nom=false      , $option_first='non' , $selection=true                                 , $optgroup='non');
?>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=releves_bilans__releve_socle">DOC : Détail de maîtrise du socle.</a></span></p>

<form action="#" method="post" id="form_select"><fieldset>
	<label class="tab" for="f_palier">Palier :</label><?php echo $select_palier ?><input type="hidden" id="f_palier_nom" name="f_palier_nom" value="" /><label id="ajax_maj_pilier">&nbsp;</label><br />
	<label class="tab" for="f_pilier"><img alt="" src="./_img/bulle_aide.png" title="Utiliser la touche &laquo;&nbsp;Shift&nbsp;&raquo; pour une sélection multiple contiguë.<br />Utiliser la touche &laquo;&nbsp;Ctrl&nbsp;&raquo; pour une sélection multiple non contiguë." /> Compétence(s) :</label><select id="f_pilier" name="f_pilier" multiple size="7" class="hide"><option></option></select><input type="hidden" id="piliers" name="piliers" value="" />
	<p class="<?php echo $class_form_eleve ?>">
		<label class="tab" for="f_groupe">Classe / groupe :</label><?php echo $select_groupe ?><label id="ajax_maj">&nbsp;</label><br />
		<label class="tab" for="f_eleve"><img alt="" src="./_img/bulle_aide.png" title="Utiliser la touche &laquo;&nbsp;Shift&nbsp;&raquo; pour une sélection multiple contiguë.<br />Utiliser la touche &laquo;&nbsp;Ctrl&nbsp;&raquo; pour une sélection multiple non contiguë." /> Élève(s) :</label><select id="f_eleve" name="f_eleve[]"<?php echo $multiple_eleve ?>><?php echo $select_eleves ?></select><input type="hidden" id="eleves" name="eleves" value="" />
	</p>
	<div id="option_groupe" class="<?php echo $class_option_groupe ?>">
		<label class="tab"><img alt="" src="./_img/bulle_aide.png" title="Pour le format html, le détail des items peut être affiché." /> Infos items :</label><label for="f_coef"><input type="checkbox" id="f_coef" name="f_coef" value="1"<?php echo $check_aff_coef ?> /> Coefficients</label>&nbsp;&nbsp;&nbsp;<label for="f_socle"><input type="checkbox" id="f_socle" name="f_socle" value="1"<?php echo $check_aff_socle ?> /> Socle</label>&nbsp;&nbsp;&nbsp;<label for="f_lien"><input type="checkbox" id="f_lien" name="f_lien" value="1"<?php echo $check_aff_lien ?> /> Liens de remédiation</label><br />
		<label class="tab">Indications :</label><?php echo $socle_PA.'&nbsp;&nbsp;&nbsp;'.$socle_EV ?>
		<div id="option_mode" class="<?php echo $class_option_mode ?>">
			<label class="tab">Items récoltés :</label><label for="f_mode_auto"><input type="radio" id="f_mode_auto" name="f_mode" value="auto"<?php echo $check_mode_auto ?> /> Automatique (recommandé) <img alt="" src="./_img/bulle_aide.png" title="Items de tous les référentiels, sauf pour la compétence 2 où on ne prend que les items des référentiels de la langue associée à l'élève." /></label>&nbsp;&nbsp;&nbsp;<label for="f_mode_manuel"><input type="radio" id="f_mode_manuel" name="f_mode" value="manuel"<?php echo $check_mode_manuel ?> /> Sélection manuelle <img alt="" src="./_img/bulle_aide.png" title="Pour choisir les matières des référentiels dont les items collectés sont issus." /></label>
			<div id="div_matiere" class="<?php echo $class_div_matiere ?>"><span class="tab"></span><select id="f_matiere" name="f_matiere[]" multiple size="5"><?php echo $select_matiere ?></select><input type="hidden" id="matieres" name="matieres" value="" /></div>
		</div>
	</div>
	<p><span class="tab"></span><button id="bouton_valider" type="submit" class="generer">Générer.</button><label id="ajax_msg">&nbsp;</label></p>
</fieldset></form>

<div id="bilan">
</div>

