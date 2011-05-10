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
$TITRE = "Grille d'items d'un référentiel";
$VERSION_JS_FILE += 3;
?>

<?php
// Fabrication des éléments select du formulaire
// L'élève ne choisit évidemment pas sa classe ni nom nom, mais on construit qd même les formulaires, on les remplit et on les cache (permet un code unique et une transmission des infos en ajax comme pour les autres profils).
$tab_cookie = load_cookie_select('grille_referentiel');
if($_SESSION['USER_PROFIL']=='directeur')
{
	$tab_matieres = DB_STRUCTURE_OPT_matieres_etabl($_SESSION['MATIERES'],$transversal=true);
	$tab_niveaux  = DB_STRUCTURE_OPT_niveaux_etabl($_SESSION['NIVEAUX'],$_SESSION['CYCLES']);
	$tab_groupes  = DB_STRUCTURE_OPT_classes_groupes_etabl();
	$of_m = 'oui'; $of_g = 'val'; $sel_g = false; $og_g = 'oui'; $class_form_eleve = 'show'; $sel_n = false;
	$select_eleves = '<option></option>'; // maj en ajax suivant le choix du groupe
	$check_option_lien = '';
}
if($_SESSION['USER_PROFIL']=='professeur')
{
	$tab_matieres = DB_STRUCTURE_OPT_matieres_professeur($_SESSION['MATIERES'],$_SESSION['USER_ID']);
	$tab_niveaux  = DB_STRUCTURE_OPT_niveaux_etabl($_SESSION['NIVEAUX'],$_SESSION['CYCLES']);
	$tab_groupes  = DB_STRUCTURE_OPT_groupes_professeur($_SESSION['USER_ID']);
	$of_m = 'non'; $of_g = 'val'; $sel_g = false; $og_g = 'oui'; $class_form_eleve = 'show'; $sel_n = false;
	$select_eleves = '<option></option>'; // maj en ajax suivant le choix du groupe
	$check_option_lien = '';
}
if($_SESSION['USER_PROFIL']=='eleve')
{
	$tab_matieres = DB_STRUCTURE_OPT_matieres_eleve($_SESSION['MATIERES'],$_SESSION['USER_ID']);
	$tab_niveaux  = DB_STRUCTURE_OPT_niveaux_eleve($_SESSION['NIVEAUX'],$_SESSION['CYCLES'],$_SESSION['ELEVE_CLASSE_ID']);
	$tab_groupes  = array(0=>array('valeur'=>$_SESSION['ELEVE_CLASSE_ID'],'texte'=>$_SESSION['ELEVE_CLASSE_NOM']));
	$of_m = 'oui'; $of_g = 'non'; $sel_g = true;  $og_g = 'non'; $class_form_eleve = 'hide'; $sel_n = 'val';
	$select_eleves = '<option value="'.$_SESSION['USER_ID'].'" selected>'.html($_SESSION['USER_NOM'].' '.$_SESSION['USER_PRENOM']).'</option>';
	$check_option_lien = ' checked';
}

$select_matiere     = afficher_select($tab_matieres           , $select_nom='f_matiere'     , $option_first=$of_m , $selection=false                        , $optgroup='non');
$select_niveau      = afficher_select($tab_niveaux            , $select_nom='f_niveau'      , $option_first='oui' , $selection=$sel_n                       , $optgroup='non');
$select_groupe      = afficher_select($tab_groupes            , $select_nom='f_groupe'      , $option_first=$of_g , $selection=$sel_g                       , $optgroup=$og_g);
$select_orientation = afficher_select($tab_select_orientation , $select_nom='f_orientation' , $option_first='non' , $selection=$tab_cookie['orientation']   , $optgroup='non');
$select_marge_min   = afficher_select($tab_select_marge_min   , $select_nom='f_marge_min'   , $option_first='non' , $selection=$tab_cookie['marge_min']     , $optgroup='non');
$select_couleur     = afficher_select($tab_select_couleur     , $select_nom='f_couleur'     , $option_first='non' , $selection=$tab_cookie['couleur']       , $optgroup='non');
$select_legende     = afficher_select($tab_select_legende     , $select_nom='f_legende'     , $option_first='non' , $selection=$tab_cookie['legende']       , $optgroup='non');
$select_cases_nb    = afficher_select($tab_select_cases_nb    , $select_nom='f_cases_nb'    , $option_first='non' , $selection=$tab_cookie['cases_nb']      , $optgroup='non');
$select_cases_larg  = afficher_select($tab_select_cases_size  , $select_nom='f_cases_larg'  , $option_first='non' , $selection=$tab_cookie['cases_largeur'] , $optgroup='non');
$select_remplissage = afficher_select($tab_select_remplissage , $select_nom='f_remplissage' , $option_first='non' , $selection='plein'                      , $optgroup='non');
?>

<script type="text/javascript">
	var id_matiere_transversale   = "<?php echo ID_MATIERE_TRANSVERSALE ?>";
	var listing_id_niveaux_cycles = "<?php echo LISTING_ID_NIVEAUX_CYCLES ?>";
</script>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=releves_bilans__releve_grille_referentiel">DOC : Grille d'items d'un référentiel.</a></span></p>

<form id="form_select" action=""><fieldset>
	<label class="tab" for="f_matiere">Matière :</label><?php echo $select_matiere ?><input type="hidden" id="f_matiere_nom" name="f_matiere_nom" value="" /><br />
	<label class="tab" for="f_niveau">Niveau :</label><?php echo $select_niveau ?><input type="hidden" id="f_niveau_nom" name="f_niveau_nom" value="" /><p />
	<div class="<?php echo $class_form_eleve ?>">
		<label class="tab" for="f_groupe">Élève(s) :</label><?php echo $select_groupe ?><label id="ajax_maj">&nbsp;</label><br />
		<span class="tab"></span><select id="f_eleve" name="f_eleve[]" multiple size="9"><?php echo $select_eleves ?></select><input type="hidden" id="eleves" name="eleves" value="" /><p />
	</div>
	<div class="toggle">
		<span class="tab"></span><a href="#" class="puce_plus toggle">Afficher plus d'options</a>
	</div>
	<div class="toggle hide">
		<span class="tab"></span><a href="#" class="puce_moins toggle">Afficher moins d'options</a><br />
		<label class="tab" for="f_restriction">Restriction :</label><input type="checkbox" id="f_restriction" name="f_restriction" value="1" /> <label for="f_restriction">Uniquement les items liés du socle</label><br />
		<label class="tab" for="f_options">Indications :</label><input type="checkbox" id="f_coef" name="f_coef" value="1" /> <label for="f_coef">Coefficients</label>&nbsp;&nbsp;&nbsp;<input type="checkbox" id="f_socle" name="f_socle" value="1" checked /> <label for="f_socle">Socle</label>&nbsp;&nbsp;&nbsp;<input type="checkbox" id="f_lien" name="f_lien" value="1"<?php echo $check_option_lien ?> /> <label for="f_lien">Liens de remédiation</label><br />
		<label class="tab" for="f_cases_nb">Évaluations :</label><?php echo $select_cases_nb ?> de largeur <?php echo $select_cases_larg ?> <?php echo $select_remplissage ?><br />
		<label class="tab" for="f_impression"><img alt="" src="./_img/bulle_aide.png" title="Pour le format pdf." /> Impression :</label><?php echo $select_orientation ?> <?php echo $select_couleur ?> <?php echo $select_legende ?> </label><?php echo $select_marge_min ?><p />
	</div>
	<span class="tab"></span><button id="bouton_valider" type="submit"><img alt="" src="./_img/bouton/generer.png" /> Générer.</button><label id="ajax_msg">&nbsp;</label><br />
</fieldset></form>

<hr />

<div id="bilan">
</div>

