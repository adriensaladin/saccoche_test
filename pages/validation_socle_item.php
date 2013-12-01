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

if(!test_user_droit_specifique( $_SESSION['DROIT_VALIDATION_ENTREE'] , NULL /*matiere_coord_or_groupe_pp_connu*/ , 0 /*matiere_id_or_groupe_id_a_tester*/ ))
{
  echo'<p class="danger">Vous n\'êtes pas habilité à accéder à cette fonctionnalité !</p>'.NL;
  echo'<div class="astuce">Profils autorisés (par les administrateurs) :</div>'.NL;
  echo afficher_profils_droit_specifique($_SESSION['DROIT_VALIDATION_ENTREE'],'li');
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

// Remarque : on ne peut être pp que d'une classe, pas d'un groupe, donc si seuls les PP ont un accès parmi les profs, ils ne peuvent trier les élèves que par classes

Form::load_choix_memo();
$check_mode_auto   = (Form::$tab_choix['mode']=='auto')   ? ' checked' : '' ;
$check_mode_manuel = (Form::$tab_choix['mode']=='manuel') ? ' checked' : '' ;
$class_div_matiere = (Form::$tab_choix['mode']=='manuel') ? 'show'     : 'hide' ;

if($_SESSION['USER_PROFIL_TYPE']=='directeur')
{
  $tab_groupes = DB_STRUCTURE_COMMUN::DB_OPT_classes_groupes_etabl();
  $of_g = '';
}
elseif($_SESSION['USER_PROFIL_TYPE']=='professeur')
{
  if(test_droit_specifique_restreint($_SESSION['DROIT_VALIDATION_ENTREE'],'ONLY_PP'))
  {
    $tab_groupes = DB_STRUCTURE_COMMUN::DB_OPT_classes_prof_principal($_SESSION['USER_ID']);
    $of_g = FALSE;
  }
  else
  {
    $tab_groupes = ($_SESSION['USER_JOIN_GROUPES']=='config') ? DB_STRUCTURE_COMMUN::DB_OPT_groupes_professeur($_SESSION['USER_ID']) : DB_STRUCTURE_COMMUN::DB_OPT_classes_groupes_etabl() ;
    $of_g = '';
  }
}
$tab_paliers  = DB_STRUCTURE_COMMUN::DB_OPT_paliers_etabl();
$tab_matieres = DB_STRUCTURE_COMMUN::DB_OPT_matieres_etabl();
$of_p = (count($tab_paliers)<2) ? FALSE : '' ;

$select_palier  = Form::afficher_select($tab_paliers  , 'f_palier'  /*select_nom*/ , $of_p /*option_first*/ , Form::$tab_choix['palier_id'] /*selection*/ ,              '' /*optgroup*/);
$select_groupe  = Form::afficher_select($tab_groupes  , 'f_groupe'  /*select_nom*/ , $of_g /*option_first*/ , FALSE                         /*selection*/ , 'regroupements' /*optgroup*/);
$select_matiere = Form::afficher_select($tab_matieres , 'f_matiere' /*select_nom*/ , FALSE /*option_first*/ , TRUE                          /*selection*/ ,              '' /*optgroup*/ , TRUE /*multiple*/);

// Javascript
$GLOBALS['HEAD']['js']['inline'][] = 'var seuil_R = parseInt("'.$_SESSION['CALCUL_SEUIL']['R'].'",10);';
$GLOBALS['HEAD']['js']['inline'][] = 'var seuil_V = parseInt("'.$_SESSION['CALCUL_SEUIL']['V'].'",10);';
?>

<ul class="puce">
  <li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=referentiels_socle__socle_valider_item">DOC : Valider des items du socle.</a></span></li>
</ul>

<hr />

<form action="#" method="post" id="zone_choix"><fieldset>
  <p>
    <label class="tab" for="f_palier">Palier :</label><?php echo $select_palier ?><label id="ajax_maj_pilier">&nbsp;</label><br />
    <label class="tab" for="f_pilier">Compétence :</label><select id="f_pilier" name="f_pilier" class="hide"><option></option></select><label id="ajax_maj_domaine">&nbsp;</label><br />
    <span id="bloc_domaine" class="hide"><label class="tab" for="f_domaine">Domaine(s) :</label><span id="f_domaine" class="select_multiple"></span><span class="check_multiple"><q class="cocher_tout" title="Tout cocher."></q><br /><q class="cocher_rien" title="Tout décocher."></q></span></span>
  </p>
  <p>
    <label class="tab" for="f_groupe">Classe / groupe :</label><?php echo $select_groupe ?><input type="hidden" id="f_groupe_type" name="f_groupe_type" value="" /><label id="ajax_maj_eleve">&nbsp;</label><br />
    <span id="bloc_eleve" class="hide"><label class="tab" for="f_eleve">Élève(s) :</label><span id="f_eleve" class="select_multiple"></span><span class="check_multiple"><q class="cocher_tout" title="Tout cocher."></q><br /><q class="cocher_rien" title="Tout décocher."></q></span></span>
  </p>
  <label class="tab">Items récoltés :</label><label for="f_mode_auto"><input type="radio" id="f_mode_auto" name="f_mode" value="auto"<?php echo $check_mode_auto ?> /> Automatique (recommandé) <img alt="" src="./_img/bulle_aide.png" title="Items de tous les référentiels de langue, sauf pour la compétence 2 où on ne prend que les items des référentiels de la langue associée à l'élève." /></label>&nbsp;&nbsp;&nbsp;<label for="f_mode_manuel"><input type="radio" id="f_mode_manuel" name="f_mode" value="manuel"<?php echo $check_mode_manuel ?> /> Sélection manuelle <img alt="" src="./_img/bulle_aide.png" title="Pour choisir les matières des référentiels dont les items collectés sont issus." /></label>
  <div id="div_matiere" class="<?php echo $class_div_matiere ?>"><span class="tab"></span><span id="f_matiere" class="select_multiple"><?php echo $select_matiere ?></span><span class="check_multiple"><q class="cocher_tout" title="Tout cocher."></q><br /><q class="cocher_rien" title="Tout décocher."></q></span></div>
  <p><span class="tab"></span><input type="hidden" id="f_memo_matieres" value="" /><input type="hidden" name="f_action" value="Afficher_bilan" /><button id="Afficher_validation" type="submit" class="valider">Afficher le tableau des validations.</button><label id="ajax_msg_choix">&nbsp;</label></p>
</fieldset></form>

<form action="#" method="post" id="zone_validation" class="hide">
  <table id="tableau_validation">
    <tbody><tr><td></td></tr></tbody>
  </table>
</form>

<div id="zone_information" class="hide" style="height:25ex">
  <h4>Aide à la décision : bilan des évaluations associées à un item du socle <span id="span_restriction"></span></h4>
  <ul class="puce">
    <li><span id="identite" class="socle_info eleve"></span></li>
    <li><span id="entree" class="socle_info socle_n3"></span></li>
    <li><span id="stats" class="socle_info stats"></span><label id="ajax_msg_information"></label></li>
  </ul>
  <div id="items">
  </div>
</div>

<div id="zone_confirmer_fermer_validation" class="hide">
  <p class="danger">Des modifications ont été effectuées, mais n'ont pas été enregistrées.</p>
  <p>Confirmez-vous vouloir quitter l'interface de saisie ?</p>
  <p>
    <button id="confirmer_fermer_zone_validation" type="button" class="valider">Oui, je ne veux pas enregistrer</button>
    <button id="annuler_fermer_zone_validation" type="button" class="annuler">Non, je reste sur l'interface</button>
  </p>
</div>
