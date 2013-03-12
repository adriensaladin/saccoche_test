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
?>

<?php
Form::load_choix_memo();
$check_type_pourcentage = (Form::$tab_choix['type']=='pourcentage') ? ' checked' : '' ;
$check_type_validation  = (Form::$tab_choix['type']=='validation')  ? ' checked' : '' ;
$class_div_option       = (Form::$tab_choix['type']=='pourcentage') ? 'show'     : 'hide' ;
$check_mode_auto        = (Form::$tab_choix['mode']=='auto')        ? ' checked' : '' ;
$check_mode_manuel      = (Form::$tab_choix['mode']=='manuel')      ? ' checked' : '' ;
$class_div_matiere      = (Form::$tab_choix['mode']=='manuel')      ? 'show'     : 'hide' ;
if($_SESSION['USER_PROFIL_TYPE']=='directeur')
{
  $tab_groupes = DB_STRUCTURE_COMMUN::DB_OPT_classes_groupes_etabl();
}
elseif($_SESSION['USER_PROFIL_TYPE']=='professeur')
{
  $tab_groupes = ($_SESSION['USER_JOIN_GROUPES']=='config') ? DB_STRUCTURE_COMMUN::DB_OPT_groupes_professeur($_SESSION['USER_ID']) : DB_STRUCTURE_COMMUN::DB_OPT_classes_groupes_etabl() ;
}
else
{
  $tab_groupes = 'Vous n\'avez pas un profil autorisé pour accéder au formulaire !';
}
$tab_matieres = DB_STRUCTURE_COMMUN::DB_OPT_matieres_etabl();
$tab_paliers  = DB_STRUCTURE_COMMUN::DB_OPT_paliers_etabl();
$of_p = (count($tab_paliers)<2) ? 'non' : 'oui' ;

$select_matiere   = Form::afficher_select($tab_matieres               , $select_nom='f_matiere'   , $option_first='non' , $selection=TRUE                          , $optgroup='non' , TRUE /*multiple*/);
$select_palier    = Form::afficher_select($tab_paliers                , $select_nom='f_palier'    , $option_first=$of_p , $selection=Form::$tab_choix['palier_id'] , $optgroup='non');
$select_groupe    = Form::afficher_select($tab_groupes                , $select_nom='f_groupe'    , $option_first='oui' , $selection=FALSE                         , $optgroup='oui');
$select_marge_min = Form::afficher_select(Form::$tab_select_marge_min , $select_nom='f_marge_min' , $option_first='non' , $selection=Form::$tab_choix['marge_min'] , $optgroup='non');
$select_couleur   = Form::afficher_select(Form::$tab_select_couleur   , $select_nom='f_couleur'   , $option_first='non' , $selection=Form::$tab_choix['couleur']   , $optgroup='non');
$select_legende   = Form::afficher_select(Form::$tab_select_legende   , $select_nom='f_legende'   , $option_first='non' , $selection=Form::$tab_choix['legende']   , $optgroup='non');
?>

<ul class="puce">
  <li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=releves_bilans__synthese_socle">DOC : Synthèse de maîtrise du socle.</a></span></li>
</ul>

<hr />

<form action="#" method="post" id="form_select"><fieldset>
  <label class="tab">Type de synthèse :</label><label for="f_type_pourcentage"><input type="radio" id="f_type_pourcentage" name="f_type" value="pourcentage"<?php echo $check_type_pourcentage ?> /> Pourcentage d'items disciplinaires acquis</label>&nbsp;&nbsp;&nbsp;<label for="f_type_validation"><input type="radio" id="f_type_validation" name="f_type" value="validation"<?php echo $check_type_validation ?> /> Validation des items et des compétences du socle</label><br />
  <div id="option_mode" class="<?php echo $class_div_option ?>">
    <label class="tab">Items récoltés :</label><label for="f_mode_auto"><input type="radio" id="f_mode_auto" name="f_mode" value="auto"<?php echo $check_mode_auto ?> /> Automatique (recommandé) <img alt="" src="./_img/bulle_aide.png" title="Items de tous les référentiels de langue, sauf pour la compétence 2 où on ne prend que les items des référentiels de la langue associée à l'élève." /></label>&nbsp;&nbsp;&nbsp;<label for="f_mode_manuel"><input type="radio" id="f_mode_manuel" name="f_mode" value="manuel"<?php echo $check_mode_manuel ?> /> Sélection manuelle <img alt="" src="./_img/bulle_aide.png" title="Pour choisir les matières des référentiels dont les items collectés sont issus." /></label>
    <div id="div_matiere" class="<?php echo $class_div_matiere ?>"><span class="tab"></span><span id="f_matiere" class="select_multiple"><?php echo $select_matiere ?></span><span class="check_multiple"><input name="leurre" type="image" alt="leurre" src="./_img/auto.gif" /><input name="all_check" type="image" alt="Tout cocher." src="./_img/all_check.gif" title="Tout cocher." /><br /><input name="all_uncheck" type="image" alt="Tout décocher." src="./_img/all_uncheck.gif" title="Tout décocher." /></span></div>
  </div>
  <p>
    <label class="tab" for="f_palier">Palier :</label><?php echo $select_palier ?><input type="hidden" id="f_palier_nom" name="f_palier_nom" value="" /><label id="ajax_maj_pilier">&nbsp;</label><br />
    <label class="tab" for="f_pilier">Compétence(s) :</label><span id="f_pilier" class="select_multiple"></span><span class="check_multiple"><input name="leurre" type="image" alt="leurre" src="./_img/auto.gif" /><input name="all_check" type="image" alt="Tout cocher." src="./_img/all_check.gif" title="Tout cocher." /><br /><input name="all_uncheck" type="image" alt="Tout décocher." src="./_img/all_uncheck.gif" title="Tout décocher." /></span>
  </p>
  <label class="tab" for="f_groupe">Classe / groupe :</label><?php echo $select_groupe ?><input type="hidden" id="f_groupe_type" name="f_groupe_type" value="" /><input type="hidden" id="f_groupe_nom" name="f_groupe_nom" value="" /><label id="ajax_maj_eleve">&nbsp;</label><br />
  <span id="bloc_eleve" class="hide"><label class="tab" for="f_eleve">Élève(s) :</label><span id="f_eleve" class="select_multiple"></span><span class="check_multiple"><input name="leurre" type="image" alt="leurre" src="./_img/auto.gif" /><input name="all_check" type="image" alt="Tout cocher." src="./_img/all_check.gif" title="Tout cocher." /><br /><input name="all_uncheck" type="image" alt="Tout décocher." src="./_img/all_uncheck.gif" title="Tout décocher." /></span></span>
  <div class="toggle">
    <span class="tab"></span><a href="#" class="puce_plus toggle">Afficher plus d'options</a>
  </div>
  <div class="toggle hide">
    <span class="tab"></span><a href="#" class="puce_moins toggle">Afficher moins d'options</a><br />
    <label class="tab"><img alt="" src="./_img/bulle_aide.png" title="Pour le format pdf." /> Impression :</label><?php echo $select_couleur ?> <?php echo $select_legende ?> <?php echo $select_marge_min ?>
  </div>
  <p><span class="tab"></span><button id="bouton_valider" type="submit" class="generer">Générer.</button><label id="ajax_msg">&nbsp;</label></p>
</fieldset></form>

<div id="bilan">
</div>
