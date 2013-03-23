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
$TITRE = "Bilan chronologique";

if(in_array($_SESSION['USER_PROFIL_TYPE'],array('parent','eleve')))
{
  if( !test_user_droit_specifique($_SESSION['DROIT_RELEVE_ETAT_ACQUISITION']) )
  {
    echo'<p class="danger">Vous n\'êtes pas habilité à accéder à cette fonctionnalité !<p>';
    echo'<div class="astuce">En effet, les administrateurs n\'ont pas autorisé que vous accédiez aux états d\'acquisitions&hellip;<div>';
    return; // Ne pas exécuter la suite de ce fichier inclus.
  }
  if( !test_user_droit_specifique($_SESSION['DROIT_RELEVE_MOYENNE_SCORE']) && !test_user_droit_specifique($_SESSION['DROIT_RELEVE_POURCENTAGE_ACQUIS']) )
  {
    echo'<p class="danger">Vous n\'êtes pas habilité à accéder à cette fonctionnalité !<p>';
    echo'<div class="astuce">En effet, les administrateurs n\'ont pas autorisé que vous accédiez aux moyennes des scores ni aux pourcentages d\'items acquis&hellip;<div>';
    return; // Ne pas exécuter la suite de ce fichier inclus.
  }
}

// L'élève ne choisit évidemment pas sa classe ni son nom, mais on construit qd même les formulaires, on les remplit et on les cache (permet un code unique et une transmission des infos en ajax comme pour les autres profils).
Form::load_choix_memo();
$check_moyenne_scores     = (Form::$tab_choix['indicateur']=='moyenne_scores')     ? ' checked' : '' ;
$check_pourcentage_acquis = (Form::$tab_choix['indicateur']=='pourcentage_acquis') ? ' checked' : '' ;
$check_conversion_sur_20  = (Form::$tab_choix['conversion_sur_20'])                ? ' checked' : '' ;
$class_conversion_sur_20  = ($check_moyenne_scores || $check_pourcentage_acquis)   ? 'show' : 'hide' ;
if(in_array($_SESSION['USER_PROFIL_TYPE'],array('parent','eleve')))
{
  // Une éventuelle restriction d'accès doit surcharger toute mémorisation antérieure de formulaire
  $check_moyenne_scores     = test_user_droit_specifique($_SESSION['DROIT_RELEVE_MOYENNE_SCORE'])      ? $check_moyenne_scores     : '' ;
  $check_pourcentage_acquis = test_user_droit_specifique($_SESSION['DROIT_RELEVE_POURCENTAGE_ACQUIS']) ? $check_pourcentage_acquis : '' ;
  $check_conversion_sur_20  = test_user_droit_specifique($_SESSION['DROIT_RELEVE_CONVERSION_SUR_20'])  ? $check_conversion_sur_20  : '' ;
  $class_conversion_sur_20  = ($check_moyenne_scores || $check_pourcentage_acquis)                     ? 'show' : 'hide' ;
  $moyenne_scores     = test_user_droit_specifique($_SESSION['DROIT_RELEVE_MOYENNE_SCORE'])      ? '<label for="f_indicateur_MS"><input type="radio" id="f_indicateur_MS" name="f_indicateur" value="moyenne_scores"'.$check_moyenne_scores.' /> Moyenne des scores</label>'                                                     : '<del>Moyenne des scores</del>' ;
  $pourcentage_acquis = test_user_droit_specifique($_SESSION['DROIT_RELEVE_POURCENTAGE_ACQUIS']) ? '<label for="f_indicateur_PA"><input type="radio" id="f_indicateur_PA" name="f_indicateur" value="pourcentage_acquis"'.$check_pourcentage_acquis.' /> Pourcentage d\'items acquis</label>'                                    : '<del>Pourcentage d\'items acquis</del>' ;
  $conversion_sur_20  = test_user_droit_specifique($_SESSION['DROIT_RELEVE_CONVERSION_SUR_20'])  ? '<label for="f_conversion_sur_20" class="'.$class_conversion_sur_20.'"><input type="checkbox" id="f_conversion_sur_20" name="f_conversion_sur_20" value="1"'.$check_conversion_sur_20.' /> Conversion en note sur 20</label>' : '<del>Conversion en note sur 20</del>' ;
}
else
{
  $moyenne_scores     = '<label for="f_indicateur_MS"><input type="radio" id="f_indicateur_MS" name="f_indicateur" value="moyenne_scores"'.$check_moyenne_scores.' /> Moyenne des scores</label>';
  $pourcentage_acquis = '<label for="f_indicateur_PA"><input type="radio" id="f_indicateur_PA" name="f_indicateur" value="pourcentage_acquis"'.$check_pourcentage_acquis.' /> Pourcentage d\'items acquis</label>';
  $conversion_sur_20  = '<label for="f_conversion_sur_20" class="'.$class_conversion_sur_20.'"><input type="checkbox" id="f_conversion_sur_20" name="f_conversion_sur_20" value="1"'.$check_conversion_sur_20.' /> Conversion en note sur 20</label>';
}
$check_retro_auto         = (Form::$tab_choix['retroactif']=='auto')     ? ' checked' : '' ;
$check_retro_non          = (Form::$tab_choix['retroactif']=='non')      ? ' checked' : '' ;
$check_retro_oui          = (Form::$tab_choix['retroactif']=='oui')      ? ' checked' : '' ;
$check_only_socle         = (Form::$tab_choix['only_socle'])             ? ' checked' : '' ;
$bouton_modifier_matieres = '';
$separateur_check_matieres = '<br />';

if($_SESSION['USER_PROFIL_TYPE']=='directeur')
{
  $tab_groupes  = DB_STRUCTURE_COMMUN::DB_OPT_classes_groupes_etabl();
  $tab_matieres = 'Choisir d\'abord un groupe ci-dessus...'; // maj en ajax suivant le choix du groupe
  $of_g = ''; $sel_g = FALSE; $class_form_eleve = 'show'; $class_form_periode = 'hide'; $class_navig_eleve = 'show';
  $select_eleve = '<option></option>'; // maj en ajax suivant le choix du groupe
}
if($_SESSION['USER_PROFIL_TYPE']=='professeur')
{
  $tab_groupes  = ($_SESSION['USER_JOIN_GROUPES']=='config') ? DB_STRUCTURE_COMMUN::DB_OPT_groupes_professeur($_SESSION['USER_ID']) : DB_STRUCTURE_COMMUN::DB_OPT_classes_groupes_etabl() ;
  $tab_matieres = DB_STRUCTURE_COMMUN::DB_OPT_matieres_professeur($_SESSION['USER_ID']);
  $of_g = ''; $sel_g = FALSE; $class_form_eleve = 'show'; $class_form_periode = 'hide'; $class_navig_eleve = 'show';
  $select_eleve = '<option></option>'; // maj en ajax suivant le choix du groupe
  $bouton_modifier_matieres = '<br />&nbsp;<button id="modifier_matiere" type="button" class="form_ajouter">&plusmn;</button>';
  $separateur_check_matieres = '';

}
if( ($_SESSION['USER_PROFIL_TYPE']=='parent') && ($_SESSION['NB_ENFANTS']!=1) )
{
  $tab_groupes  = $_SESSION['OPT_PARENT_CLASSES'];
  $tab_matieres = DB_STRUCTURE_COMMUN::DB_OPT_matieres_etabl();
  $of_g = ''; $sel_g = FALSE; $class_form_eleve = 'show'; $class_form_periode = 'hide'; $class_navig_eleve = 'hide';
  $select_eleve = '<option></option>'; // maj en ajax suivant le choix du groupe
}
if( ($_SESSION['USER_PROFIL_TYPE']=='parent') && ($_SESSION['NB_ENFANTS']==1) )
{
  $tab_groupes  = array(0=>array('valeur'=>$_SESSION['ELEVE_CLASSE_ID'],'texte'=>$_SESSION['ELEVE_CLASSE_NOM'],'optgroup'=>'classe'));
  $tab_matieres = DB_STRUCTURE_COMMUN::DB_OPT_matieres_eleve($_SESSION['OPT_PARENT_ENFANTS'][0]['valeur']);
  $of_g = FALSE; $sel_g = TRUE; $class_form_eleve = 'hide'; $class_form_periode = 'show'; $class_navig_eleve = 'hide';
  $select_eleve = '<option value="'.$_SESSION['OPT_PARENT_ENFANTS'][0]['valeur'].'" selected>'.html($_SESSION['OPT_PARENT_ENFANTS'][0]['texte']).'</option>';
}
if($_SESSION['USER_PROFIL_TYPE']=='eleve')
{
  $tab_groupes  = array(0=>array('valeur'=>$_SESSION['ELEVE_CLASSE_ID'],'texte'=>$_SESSION['ELEVE_CLASSE_NOM'],'optgroup'=>'classe'));
  $tab_matieres = DB_STRUCTURE_COMMUN::DB_OPT_matieres_eleve($_SESSION['USER_ID']);
  $of_g = FALSE; $sel_g = TRUE; $class_form_eleve = 'hide'; $class_form_periode = 'show'; $class_navig_eleve = 'hide';
  $select_eleve = '<option value="'.$_SESSION['USER_ID'].'" selected>'.html($_SESSION['USER_NOM'].' '.$_SESSION['USER_PRENOM']).'</option>';
}
$tab_periodes = DB_STRUCTURE_COMMUN::DB_OPT_periodes_etabl();

$select_groupe  = Form::afficher_select($tab_groupes  , 'f_groupe'  /*select_nom*/ ,                   $of_g /*option_first*/ , $sel_g /*selection*/ , 'regroupements' /*optgroup*/ );
$select_matiere = Form::afficher_select($tab_matieres , 'f_matiere' /*select_nom*/ ,                   FALSE /*option_first*/ , TRUE   /*selection*/ ,              '' /*optgroup*/ , TRUE /*multiple*/);
$select_periode = Form::afficher_select($tab_periodes , 'f_periode' /*select_nom*/ , 'periode_personnalisee' /*option_first*/ , FALSE  /*selection*/ ,              '' /*optgroup*/);

// Fabrication du tableau javascript "tab_groupe_periode" pour les jointures groupes/périodes
list( $tab_groupe_periode_js ) = Form::fabriquer_tab_js_jointure_groupe( $tab_groupes , TRUE /*return_jointure_periode*/ , FALSE /*return_jointure_niveau*/ );
?>

<script type="text/javascript">
  var date_mysql="<?php echo TODAY_MYSQL ?>";
  <?php echo $tab_groupe_periode_js ?> 
</script>

<div><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=releves_bilans__bilan_chronologique">DOC : Bilan chronologique.</a></span></div>

<hr />

<form action="#" method="post" id="form_select"><fieldset>
  <p>
    <label class="tab">Indicateur :</label><?php echo $moyenne_scores.'&nbsp;&nbsp;&nbsp;'.$pourcentage_acquis.'&nbsp;&nbsp;&nbsp;'.$conversion_sur_20 ?>
  </p>
  <p class="<?php echo $class_form_eleve ?>">
    <label class="tab" for="f_groupe">Classe / groupe :</label><?php echo $select_groupe ?><label id="ajax_maj">&nbsp;</label><br />
    <label class="tab" for="f_eleve">Élève :</label><select id="f_eleve" name="f_eleve"><?php echo $select_eleve ?></select>
  </p>
  <p id="zone_periodes" class="<?php echo $class_form_periode ?>">
    <label class="tab" for="f_periode"><img alt="" src="./_img/bulle_aide.png" title="Les items pris en compte sont ceux qui sont évalués<br />au moins une fois sur cette période." /> Période :</label><?php echo $select_periode ?>
    <span id="dates_perso" class="show">
      du <input id="f_date_debut" name="f_date_debut" size="9" type="text" value="<?php echo jour_debut_annee_scolaire('french') ?>" /><q class="date_calendrier" title="Cliquez sur cette image pour importer une date depuis un calendrier !"></q>
      au <input id="f_date_fin" name="f_date_fin" size="9" type="text" value="<?php echo TODAY_FR ?>" /><q class="date_calendrier" title="Cliquez sur cette image pour importer une date depuis un calendrier !"></q>
    </span><br />
    <span class="radio"><img alt="" src="./_img/bulle_aide.png" title="Le bilan peut être établi uniquement sur la période considérée<br />ou en tenant compte d'évaluations antérieures des items concernés.<br />En automatique, les paramètres enregistrés pour chaque référentiel s'appliquent." /> Prise en compte des évaluations antérieures :</span>
      <label for="f_retro_auto"><input type="radio" id="f_retro_auto" name="f_retroactif" value="auto"<?php echo $check_retro_auto ?> /> automatique (selon référentiels)</label>&nbsp;&nbsp;&nbsp;
      <label for="f_retro_non"><input type="radio" id="f_retro_non" name="f_retroactif" value="non"<?php echo $check_retro_non ?> /> non</label>&nbsp;&nbsp;&nbsp;
      <label for="f_retro_oui"><input type="radio" id="f_retro_oui" name="f_retroactif" value="oui"<?php echo $check_retro_oui ?> /> oui</label>
  </p>
  <p>
    <label class="tab" for="f_matiere">Matière(s) :</label><span id="f_matiere" class="select_multiple"><?php echo $select_matiere ?></span><span class="check_multiple"><q class="cocher_tout" title="Tout cocher."></q><?php echo $separateur_check_matieres ?><q class="cocher_rien" title="Tout décocher."></q><?php echo $bouton_modifier_matieres ?></span>
  </p>
  <div class="toggle">
    <span class="tab"></span><a href="#" class="puce_plus toggle">Afficher plus d'options</a>
  </div>
  <div class="toggle hide">
    <span class="tab"></span><a href="#" class="puce_moins toggle">Afficher moins d'options</a><br />
    <label class="tab">Restriction :</label><label for="f_restriction"><input type="checkbox" id="f_restriction" name="f_restriction" value="1"<?php echo $check_only_socle ?> /> Uniquement les items liés au socle</label>
  </div>
  <p><span class="tab"></span><button id="bouton_valider" type="submit" class="generer">Générer.</button><label id="ajax_msg">&nbsp;</label></p>
</fieldset></form>

<div id="bilan" class="hide">
  <div><b id="report_titre"></b>&nbsp;&nbsp;&nbsp;<span id="navigation_eleve" class="<?php echo $class_navig_eleve ?>"><button class="go_premier" type="button" id="go_premier_eleve">Premier</button> <button class="go_precedent" type="button" id="go_precedent_eleve">Précédent</button> <select class="b" name="go_selection" id="go_selection_eleve"><option></option></select> <button class="go_suivant" type="button" id="go_suivant_eleve">Suivant</button> <button class="go_dernier" type="button" id="go_dernier_eleve">Dernier</button></span>&nbsp;&nbsp;&nbsp;<button class="retourner" type="button" id="fermer_zone_bilan">Retour</button></div>
  <div id="div_graphique"></div>
</div>

