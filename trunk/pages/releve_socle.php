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
$TITRE = "Relevé de maîtrise du socle";

if( !in_array($_SESSION['USER_PROFIL_TYPE'],array('professeur','directeur')) && !test_user_droit_specifique($_SESSION['DROIT_SOCLE_ACCES']) )
{
  echo'<p class="danger">Vous n\'êtes pas habilité à accéder à cette fonctionnalité !<p>';
  echo'<div class="astuce">Profils autorisés (par les administrateurs) en complément des professeurs et directeurs :<div>';
  echo afficher_profils_droit_specifique($_SESSION['DROIT_SOCLE_ACCES'],'li');
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

Form::load_choix_memo();
$check_only_presence = (Form::$tab_choix['only_presence']) ? ' checked' : '' ;
$check_aff_coef      = (Form::$tab_choix['aff_coef'])      ? ' checked' : '' ;
$check_aff_socle     = (Form::$tab_choix['aff_socle'])     ? ' checked' : '' ;
$check_aff_lien      = (Form::$tab_choix['aff_lien'])      ? ' checked' : '' ;
$check_aff_start     = (Form::$tab_choix['aff_start'])     ? ' checked' : '' ;
$check_socle_PA      = (Form::$tab_choix['aff_socle_PA'])  ? ' checked' : '' ;
$check_socle_EV      = (Form::$tab_choix['aff_socle_EV'])  ? ' checked' : '' ;
if(in_array($_SESSION['USER_PROFIL_TYPE'],array('parent','eleve')))
{
  // Une éventuelle restriction d'accès doit surcharger toute mémorisation antérieure de formulaire
  $check_socle_PA = test_user_droit_specifique($_SESSION['DROIT_SOCLE_POURCENTAGE_ACQUIS']) ? ' checked' : '' ;
  $check_socle_EV = test_user_droit_specifique($_SESSION['DROIT_SOCLE_ETAT_VALIDATION'])    ? ' checked' : '' ;
}
$check_mode_auto   = (Form::$tab_choix['mode']=='auto')   ? ' checked' : '' ;
$check_mode_manuel = (Form::$tab_choix['mode']=='manuel') ? ' checked' : '' ;
$class_div_matiere = (Form::$tab_choix['mode']=='manuel') ? 'show'     : 'hide' ;
$socle_PA = '<label for="f_socle_PA"><input type="checkbox" id="f_socle_PA" name="f_socle_PA" value="1"'.$check_socle_PA.' /> Pourcentage d\'items acquis</label>';
$socle_EV = '<label for="f_socle_EV"><input type="checkbox" id="f_socle_EV" name="f_socle_EV" value="1"'.$check_socle_EV.' /> État de validation</label>';
if($_SESSION['USER_PROFIL_TYPE']=='directeur')
{
  $tab_groupes  = DB_STRUCTURE_COMMUN::DB_OPT_classes_groupes_etabl();
  Form::$tab_select_option_first = array(0,'Fiche générique');
  $of_g = 'val'; $sel_g = FALSE; $og_g = 'oui'; $class_form_eleve = 'show'; $class_option_groupe = 'hide'; $class_option_mode = 'show';
  $select_eleves = '<span id="f_eleve" class="select_multiple"></span><span class="check_multiple"><input name="leurre" type="image" alt="leurre" src="./_img/auto.gif" /><input name="all_check" type="image" alt="Tout cocher." src="./_img/all_check.gif" title="Tout cocher." /><br /><input name="all_uncheck" type="image" alt="Tout décocher." src="./_img/all_uncheck.gif" title="Tout décocher." /></span>'; // maj en ajax suivant le choix du groupe
  $is_select_multiple = 1;
}
elseif($_SESSION['USER_PROFIL_TYPE']=='professeur')
{
  $tab_groupes  = ($_SESSION['USER_JOIN_GROUPES']=='config') ? DB_STRUCTURE_COMMUN::DB_OPT_groupes_professeur($_SESSION['USER_ID']) : DB_STRUCTURE_COMMUN::DB_OPT_classes_groupes_etabl() ;
  Form::$tab_select_option_first = array(0,'Fiche générique');
  $of_g = 'val'; $sel_g = FALSE; $og_g = 'oui'; $class_form_eleve = 'show'; $class_option_groupe = 'hide'; $class_option_mode = 'show';
  $select_eleves = '<span id="f_eleve" class="select_multiple"></span><span class="check_multiple"><input name="leurre" type="image" alt="leurre" src="./_img/auto.gif" /><input name="all_check" type="image" alt="Tout cocher." src="./_img/all_check.gif" title="Tout cocher." /><br /><input name="all_uncheck" type="image" alt="Tout décocher." src="./_img/all_uncheck.gif" title="Tout décocher." /></span>'; // maj en ajax suivant le choix du groupe
  $is_select_multiple = 1;
}

if( ($_SESSION['USER_PROFIL_TYPE']=='parent') && ($_SESSION['NB_ENFANTS']!=1) )
{
  $tab_groupes  = $_SESSION['OPT_PARENT_CLASSES']; Form::$tab_select_optgroup = array('classe'=>'Classes');
  $of_g = 'oui'; $sel_g = FALSE; $og_g = 'oui'; $class_form_eleve = 'show'; $class_option_groupe = 'hide'; $class_option_mode = 'hide';
  $select_eleves = '<select id="f_eleve" name="f_eleve[]"><option></option></select>'; // maj en ajax suivant le choix du groupe
  $is_select_multiple = 0; // volontaire
  $socle_PA = test_user_droit_specifique($_SESSION['DROIT_SOCLE_POURCENTAGE_ACQUIS']) ? $socle_PA : '<del>Pourcentage d\'items acquis</del>' ;
  $socle_EV = test_user_droit_specifique($_SESSION['DROIT_SOCLE_ETAT_VALIDATION'])    ? $socle_EV : '<del>État de validation</del>' ;
}
if( ($_SESSION['USER_PROFIL_TYPE']=='parent') && ($_SESSION['NB_ENFANTS']==1) )
{
  $tab_groupes  = array(0=>array('valeur'=>$_SESSION['ELEVE_CLASSE_ID'],'texte'=>$_SESSION['ELEVE_CLASSE_NOM'],'optgroup'=>'classe')); Form::$tab_select_optgroup = array('classe'=>'Classes');
  $of_g = 'non'; $sel_g = TRUE;  $og_g = 'non'; $class_form_eleve = 'hide'; $class_option_groupe = 'show'; $class_option_mode = 'hide';
  $select_eleves = '<select id="f_eleve" name="f_eleve[]"><option value="'.$_SESSION['OPT_PARENT_ENFANTS'][0]['valeur'].'" selected>'.html($_SESSION['OPT_PARENT_ENFANTS'][0]['texte']).'</option></select>';
  $is_select_multiple = 0;
  $socle_PA = test_user_droit_specifique($_SESSION['DROIT_SOCLE_POURCENTAGE_ACQUIS']) ? $socle_PA : '<del>Pourcentage d\'items acquis</del>' ;
  $socle_EV = test_user_droit_specifique($_SESSION['DROIT_SOCLE_ETAT_VALIDATION'])    ? $socle_EV : '<del>État de validation</del>' ;
}

elseif($_SESSION['USER_PROFIL_TYPE']=='eleve')
{
  $tab_groupes  = array(0=>array('valeur'=>$_SESSION['ELEVE_CLASSE_ID'],'texte'=>$_SESSION['ELEVE_CLASSE_NOM']));
  $of_g = 'non'; $sel_g = TRUE;  $og_g = 'non'; $class_form_eleve = 'hide'; $class_option_groupe = 'show'; $class_option_mode = 'hide';
  $select_eleves = '<select id="f_eleve" name="f_eleve[]"><option value="'.$_SESSION['USER_ID'].'" selected>'.html($_SESSION['USER_NOM'].' '.$_SESSION['USER_PRENOM']).'</option></select>';
  $is_select_multiple = 0;
  $socle_PA = test_user_droit_specifique($_SESSION['DROIT_SOCLE_POURCENTAGE_ACQUIS']) ? $socle_PA : '<del>Pourcentage d\'items acquis</del>' ;
  $socle_EV = test_user_droit_specifique($_SESSION['DROIT_SOCLE_ETAT_VALIDATION'])    ? $socle_EV : '<del>État de validation</del>' ;
}
$tab_paliers  = DB_STRUCTURE_COMMUN::DB_OPT_paliers_etabl();
$tab_matieres = DB_STRUCTURE_COMMUN::DB_OPT_matieres_etabl();
$of_p = (count($tab_paliers)<2) ? 'non' : 'oui' ;

$select_palier    = Form::afficher_select($tab_paliers                , $select_nom='f_palier'    , $option_first=$of_p , $selection=Form::$tab_choix['palier_id'] , $optgroup='non');
$select_groupe    = Form::afficher_select($tab_groupes                , $select_nom='f_groupe'    , $option_first=$of_g , $selection=$sel_g                        , $optgroup=$og_g);
$select_matiere   = Form::afficher_select($tab_matieres               , $select_nom='f_matiere'   , $option_first='non' , $selection=TRUE                          , $optgroup='non' , TRUE /*multiple*/);
$select_marge_min = Form::afficher_select(Form::$tab_select_marge_min , $select_nom='f_marge_min' , $option_first='non' , $selection=Form::$tab_choix['marge_min'] , $optgroup='non');
$select_couleur   = Form::afficher_select(Form::$tab_select_couleur   , $select_nom='f_couleur'   , $option_first='non' , $selection=Form::$tab_choix['couleur']   , $optgroup='non');
$select_legende   = Form::afficher_select(Form::$tab_select_legende   , $select_nom='f_legende'   , $option_first='non' , $selection=Form::$tab_choix['legende']   , $optgroup='non');
?>

<script type="text/javascript">
  var is_multiple = <?php echo $is_select_multiple ?>;
</script>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=releves_bilans__releve_socle">DOC : Relevé de maîtrise du socle.</a></span></p>

<form action="#" method="post" id="form_select"><fieldset>
  <label class="tab" for="f_palier">Palier :</label><?php echo $select_palier ?><input type="hidden" id="f_palier_nom" name="f_palier_nom" value="" /><label id="ajax_maj_pilier">&nbsp;</label><br />
  <label class="tab" for="f_pilier">Compétence(s) :</label><span id="f_pilier" class="select_multiple"></span><span class="check_multiple"><input name="leurre" type="image" alt="leurre" src="./_img/auto.gif" /><input name="all_check" type="image" alt="Tout cocher." src="./_img/all_check.gif" title="Tout cocher." /><br /><input name="all_uncheck" type="image" alt="Tout décocher." src="./_img/all_uncheck.gif" title="Tout décocher." /></span>
  <p class="<?php echo $class_form_eleve ?>">
    <label class="tab" for="f_groupe">Classe / groupe :</label><?php echo $select_groupe ?><input type="hidden" id="f_groupe_nom" name="f_groupe_nom" value="" /><label id="ajax_maj">&nbsp;</label><br />
    <span id="bloc_eleve" class="hide"><label class="tab" for="f_eleve">Élève(s) :</label><?php echo $select_eleves ?></span>
  </p>
  <div id="option_groupe" class="<?php echo $class_option_groupe ?>">
    <label class="tab">Restriction :</label><label for="f_only_presence"><input type="checkbox" id="f_only_presence" name="f_only_presence" value="1"<?php echo $check_only_presence ?> /> Uniquement les éléments ayant fait l'objet d'une évaluation ou d'une validation</label><br />
    <label class="tab"><img alt="" src="./_img/bulle_aide.png" title="Pour le format html, le détail des items peut être affiché." /> Infos items :</label><label for="f_coef"><input type="checkbox" id="f_coef" name="f_coef" value="1"<?php echo $check_aff_coef ?> /> Coefficients</label>&nbsp;&nbsp;&nbsp;<label for="f_socle"><input type="checkbox" id="f_socle" name="f_socle" value="1"<?php echo $check_aff_socle ?> /> Appartenance au socle</label>&nbsp;&nbsp;&nbsp;<label for="f_lien"><input type="checkbox" id="f_lien" name="f_lien" value="1"<?php echo $check_aff_lien ?> /> Liens (ressources pour travailler)</label>&nbsp;&nbsp;&nbsp;<label for="f_start"><input type="checkbox" id="f_start" name="f_start" value="1"<?php echo $check_aff_start ?> /> Détails affichés par défaut</label><br />
    <label class="tab">Indications :</label><?php echo $socle_PA.'&nbsp;&nbsp;&nbsp;'.$socle_EV ?>
    <div id="option_mode" class="<?php echo $class_option_mode ?>">
      <label class="tab">Items récoltés :</label><label for="f_mode_auto"><input type="radio" id="f_mode_auto" name="f_mode" value="auto"<?php echo $check_mode_auto ?> /> Automatique (recommandé) <img alt="" src="./_img/bulle_aide.png" title="Items de tous les référentiels de langue, sauf pour la compétence 2 où on ne prend que les items des référentiels de la langue associée à l'élève." /></label>&nbsp;&nbsp;&nbsp;<label for="f_mode_manuel"><input type="radio" id="f_mode_manuel" name="f_mode" value="manuel"<?php echo $check_mode_manuel ?> /> Sélection manuelle <img alt="" src="./_img/bulle_aide.png" title="Pour choisir les matières des référentiels dont les items collectés sont issus." /></label>
      <div id="div_matiere" class="<?php echo $class_div_matiere ?>"><span class="tab"></span><span id="f_matiere" class="select_multiple"><?php echo $select_matiere ?></span><span class="check_multiple"><input name="leurre" type="image" alt="leurre" src="./_img/auto.gif" /><input name="all_check" type="image" alt="Tout cocher." src="./_img/all_check.gif" title="Tout cocher." /><br /><input name="all_uncheck" type="image" alt="Tout décocher." src="./_img/all_uncheck.gif" title="Tout décocher." /></span></div>
    </div>
  </div>
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
