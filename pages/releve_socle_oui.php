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
$VERSION_JS_FILE += 1;
?>

<?php
$socle_PA = '<label for="f_socle_PA"><input type="checkbox" id="f_socle_PA" name="f_socle_PA" value="1" checked="checked" /> Pourcentage d\'items acquis</label>';
$socle_EV = '<label for="f_socle_EV"><input type="checkbox" id="f_socle_EV" name="f_socle_EV" value="1" checked="checked" /> États de validation</label>';
// Fabrication des éléments select du formulaire
$tab_paliers = DB_STRUCTURE_OPT_paliers_etabl($_SESSION['PALIERS']);
if($_SESSION['USER_PROFIL']=='directeur')
{
	$tab_groupes  = DB_STRUCTURE_OPT_classes_groupes_etabl();
	$select_eleves = '<option></option>'; // maj en ajax suivant le choix du groupe
	$of_g = 'val'; $sel_g = false; $og_g = 'oui'; $class_form_eleve = 'show'; $class_option_groupe = 'hide';
}
elseif($_SESSION['USER_PROFIL']=='professeur')
{
	$tab_groupes  = DB_STRUCTURE_OPT_groupes_professeur($_SESSION['USER_ID']);
	$select_eleves = '<option></option>'; // maj en ajax suivant le choix du groupe
	$of_g = 'val'; $sel_g = false; $og_g = 'oui'; $class_form_eleve = 'show'; $class_option_groupe = 'hide';
}
elseif($_SESSION['USER_PROFIL']=='eleve')
{
	$tab_groupes  = array(0=>array('valeur'=>$_SESSION['ELEVE_CLASSE_ID'],'texte'=>$_SESSION['ELEVE_CLASSE_NOM']));
	$select_eleves = '<option value="'.$_SESSION['USER_ID'].'" selected="selected">'.html($_SESSION['USER_NOM'].' '.$_SESSION['USER_PRENOM']).'</option>';
	$of_g = 'non'; $sel_g = true;  $og_g = 'non'; $class_form_eleve = 'hide'; $class_option_groupe = 'true';
	$socle_PA = (mb_substr_count($_SESSION['DROIT_ELEVE_SOCLE'],'SoclePourcentageAcquis')) ? $socle_PA : '<del>Pourcentage d\'items acquis</del>' ;
	$socle_EV = (mb_substr_count($_SESSION['DROIT_ELEVE_SOCLE'],'SocleEtatValidation'))    ? $socle_EV : '<del>États de validation</del>' ;
}

$select_palier = afficher_select($tab_paliers , $select_nom='f_palier' , $option_first='non' , $selection=false  , $optgroup='non');
$select_groupe = afficher_select($tab_groupes , $select_nom='f_groupe' , $option_first=$of_g , $selection=$sel_g , $optgroup=$og_g);
?>

<p class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=releves_bilans__releve_socle">DOC : État de maîtrise du socle commun.</a></p>

<form id="form_select" action=""><fieldset>
	<label class="tab" for="f_palier">Palier :</label><?php echo $select_palier ?><input type="hidden" id="f_palier_nom" name="f_palier_nom" value="" /><p />
	<div class="<?php echo $class_form_eleve ?>">
		<label class="tab" for="f_groupe">Élève(s) :</label><?php echo $select_groupe ?><label id="ajax_maj">&nbsp;</label><br />
		<span class="tab"></span><select id="f_eleve" name="f_eleve[]" multiple="multiple" size="9"><?php echo $select_eleves ?></select><input type="hidden" id="eleves" name="eleves" value="" /><p />
	</div>
	<p id="option_groupe" class="<?php echo $class_option_groupe ?>"><label class="tab" for="f_remplissage">Indications :</label><?php echo $socle_PA.'&nbsp;&nbsp;&nbsp;'.$socle_EV ?></p>
	<span class="tab"></span><button id="bouton_valider" type="submit"><img alt="" src="./_img/bouton/generer.png" /> Générer.</button><label id="ajax_msg">&nbsp;</label><br />
</fieldset></form>

<hr />

<div id="bilan">
</div>

