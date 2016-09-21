<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2009-2015
 * 
 * ****************************************************************************************************
 * SACoche <http://sacoche.sesamath.net> - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath <http://www.sesamath.net> - Tous droits réservés.
 * Logiciel placé sous la licence libre Affero GPL 3 <https://www.gnu.org/licenses/agpl-3.0.html>.
 * ****************************************************************************************************
 * 
 * Ce fichier est une partie de SACoche.
 * 
 * SACoche est un logiciel libre ; vous pouvez le redistribuer ou le modifier suivant les termes 
 * de la “GNU Affero General Public License” telle que publiée par la Free Software Foundation :
 * soit la version 3 de cette licence, soit (à votre gré) toute version ultérieure.
 * 
 * SACoche est distribué dans l’espoir qu’il vous sera utile, mais SANS AUCUNE GARANTIE :
 * sans même la garantie implicite de COMMERCIALISABILITÉ ni d’ADÉQUATION À UN OBJECTIF PARTICULIER.
 * Consultez la Licence Publique Générale GNU Affero pour plus de détails.
 * 
 * Vous devriez avoir reçu une copie de la Licence Publique Générale GNU Affero avec SACoche ;
 * si ce n’est pas le cas, consultez : <http://www.gnu.org/licenses/>.
 * 
 */

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}
$TITRE = html(Lang::_("Synthèse de maîtrise du socle"));
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
$of_p = (count($tab_paliers)<2) ? FALSE : '' ;

$select_matiere      = HtmlForm::afficher_select($tab_matieres                  , 'f_matiere'      /*select_nom*/ , FALSE /*option_first*/ , TRUE                             /*selection*/ ,              '' /*optgroup*/ , TRUE /*multiple*/);
$select_palier       = HtmlForm::afficher_select($tab_paliers                   , 'f_palier'       /*select_nom*/ , $of_p /*option_first*/ , Form::$tab_choix['palier_id']    /*selection*/ ,              '' /*optgroup*/ );
$select_groupe       = HtmlForm::afficher_select($tab_groupes                   , 'f_groupe'       /*select_nom*/ ,    '' /*option_first*/ , FALSE                            /*selection*/ , 'regroupements' /*optgroup*/ );
$select_eleves_ordre = HtmlForm::afficher_select(Form::$tab_select_eleves_ordre , 'f_eleves_ordre' /*select_nom*/ , FALSE /*option_first*/ , Form::$tab_choix['eleves_ordre'] /*selection*/ ,              '' /*optgroup*/ );
$select_marge_min    = HtmlForm::afficher_select(Form::$tab_select_marge_min    , 'f_marge_min'    /*select_nom*/ , FALSE /*option_first*/ , Form::$tab_choix['marge_min']    /*selection*/ ,              '' /*optgroup*/ );
$select_couleur      = HtmlForm::afficher_select(Form::$tab_select_couleur      , 'f_couleur'      /*select_nom*/ , FALSE /*option_first*/ , Form::$tab_choix['couleur']      /*selection*/ ,              '' /*optgroup*/ );
$select_fond         = HtmlForm::afficher_select(Form::$tab_select_fond         , 'f_fond'         /*select_nom*/ , FALSE /*option_first*/ , Form::$tab_choix['fond']         /*selection*/ ,              '' /*optgroup*/ );
$select_legende      = HtmlForm::afficher_select(Form::$tab_select_legende      , 'f_legende'      /*select_nom*/ , FALSE /*option_first*/ , Form::$tab_choix['legende']      /*selection*/ ,              '' /*optgroup*/ );
?>

<p class="probleme">
  Cette section concerne le socle commun sur la période 2006-2016.<br />
  Elle est laissée à disposition des établissements à l'étrangers n'ayant pas encore appliqué la réforme.<br />
  Pour les autres, utiliser le menu [ Maîtrise du socle (2016) ] ci-dessus.
</p>

<div><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=releves_bilans__synthese_socle">DOC : Synthèse de maîtrise du socle.</a></span></div>
<hr />

<form action="#" method="post" id="form_select"><fieldset>
  <label class="tab">Type de synthèse :</label><label for="f_type_pourcentage"><input type="radio" id="f_type_pourcentage" name="f_type" value="pourcentage"<?php echo $check_type_pourcentage ?> /> Pourcentage d'items disciplinaires acquis</label>&nbsp;&nbsp;&nbsp;<label for="f_type_validation"><input type="radio" id="f_type_validation" name="f_type" value="validation"<?php echo $check_type_validation ?> /> Validation des items et des compétences du socle</label><br />
  <div id="option_mode" class="<?php echo $class_div_option ?>">
    <label class="tab">Items récoltés :</label><label for="f_mode_auto"><input type="radio" id="f_mode_auto" name="f_mode" value="auto"<?php echo $check_mode_auto ?> /> Automatique (recommandé) <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Items de tous les référentiels de langue, sauf pour la compétence 2 où on ne prend que les items des référentiels de la langue associée à l'élève." /></label>&nbsp;&nbsp;&nbsp;<label for="f_mode_manuel"><input type="radio" id="f_mode_manuel" name="f_mode" value="manuel"<?php echo $check_mode_manuel ?> /> Sélection manuelle <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Pour choisir les matières des référentiels dont les items collectés sont issus." /></label>
    <div id="div_matiere" class="<?php echo $class_div_matiere ?>"><span class="tab"></span><span id="f_matiere" class="select_multiple"><?php echo $select_matiere ?></span><span class="check_multiple"><q class="cocher_tout" title="Tout cocher."></q><br /><q class="cocher_rien" title="Tout décocher."></q></span></div>
  </div>
  <p>
    <label class="tab" for="f_palier">Palier :</label><?php echo $select_palier ?><input type="hidden" id="f_palier_nom" name="f_palier_nom" value="" /><label id="ajax_maj_pilier">&nbsp;</label><br />
    <label class="tab" for="f_pilier">Compétence(s) :</label><span id="f_pilier" class="select_multiple"></span><span class="check_multiple"><q class="cocher_tout" title="Tout cocher."></q><br /><q class="cocher_rien" title="Tout décocher."></q></span>
  </p>
  <label class="tab" for="f_groupe">Classe / groupe :</label><?php echo $select_groupe ?><input type="hidden" id="f_groupe_type" name="f_groupe_type" value="" /><input type="hidden" id="f_groupe_nom" name="f_groupe_nom" value="" /> <span id="bloc_ordre" class="hide"><?php echo $select_eleves_ordre ?></span><label id="ajax_maj_eleve">&nbsp;</label><br />
  <span id="bloc_eleve" class="hide"><label class="tab" for="f_eleve">Élève(s) :</label><span id="f_eleve" class="select_multiple"></span><span class="check_multiple"><q class="cocher_tout" title="Tout cocher."></q><br /><q class="cocher_rien" title="Tout décocher."></q></span></span>
  <div class="toggle">
    <span class="tab"></span><a href="#" class="puce_plus toggle">Afficher plus d'options</a>
  </div>
  <div class="toggle hide">
    <span class="tab"></span><a href="#" class="puce_moins toggle">Afficher moins d'options</a><br />
    <label class="tab"><img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Pour le format PDF." /> Impression :</label><?php echo $select_couleur ?> <?php echo $select_fond ?> <?php echo $select_legende ?> <?php echo $select_marge_min ?>
  </div>
  <p><span class="tab"></span><button id="bouton_valider" type="submit" class="generer">Générer.</button><label id="ajax_msg">&nbsp;</label></p>
</fieldset></form>

<div id="bilan">
</div>
