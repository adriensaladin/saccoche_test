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

if(!test_user_droit_specifique($_SESSION['DROIT_VOIR_GRILLES_ITEMS']))
{
  echo'<p class="danger">Vous n\'avez pas un profil autorisé pour accéder à cette fonctionnalité !<p>';
  echo'<div class="astuce">Profils autorisés (par les administrateurs) :<div>';
  echo afficher_profils_droit_specifique($_SESSION['DROIT_VOIR_GRILLES_ITEMS'],'li');
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

// L'élève ne choisit évidemment pas sa classe ni son nom, mais on construit qd même les formulaires, on les remplit et on les cache (permet un code unique et une transmission des infos en ajax comme pour les autres profils).
Form::load_choix_memo();
$check_type_generique  = (Form::$tab_choix['type_generique'])     ? ' checked' : '' ;
$check_type_individuel = (Form::$tab_choix['type_individuel'])    ? ' checked' : '' ;
$check_type_synthese   = (Form::$tab_choix['type_synthese'])      ? ' checked' : '' ;
$check_retro_auto      = (Form::$tab_choix['retroactif']=='auto') ? ' checked' : '' ;
$check_retro_non       = (Form::$tab_choix['retroactif']=='non')  ? ' checked' : '' ;
$check_retro_oui       = (Form::$tab_choix['retroactif']=='oui')  ? ' checked' : '' ;
$check_only_socle      = (Form::$tab_choix['only_socle'])         ? ' checked' : '' ;
$check_aff_coef        = (Form::$tab_choix['aff_coef'])           ? ' checked' : '' ;
$check_aff_socle       = (Form::$tab_choix['aff_socle'])          ? ' checked' : '' ;
$check_aff_lien        = (Form::$tab_choix['aff_lien'])           ? ' checked' : '' ;
$class_form_generique  = (Form::$tab_choix['type_generique'])     ? 'hide'     : 'show' ;
$class_form_individuel = (Form::$tab_choix['type_individuel'])    ? 'show'     : 'hide' ;
$class_form_synthese   = (Form::$tab_choix['type_synthese'])      ? 'show'     : 'hide' ;

$bouton_modifier_matieres = '';
if($_SESSION['USER_PROFIL_TYPE']=='directeur')
{
  $tab_matieres = DB_STRUCTURE_COMMUN::DB_OPT_matieres_etabl();
  $tab_groupes  = DB_STRUCTURE_COMMUN::DB_OPT_classes_groupes_etabl();
  $of_g = 'oui'; $sel_g = FALSE; $og_g = 'oui'; $class_form_type = 'show'; $class_form_eleve = $class_form_generique; $sel_n = FALSE;
  $multiple_eleve = ' multiple size="9"';
  $select_eleves = '<option></option>'; // maj en ajax suivant le choix du groupe
}
if($_SESSION['USER_PROFIL_TYPE']=='professeur')
{
  $tab_matieres = DB_STRUCTURE_COMMUN::DB_OPT_matieres_professeur($_SESSION['USER_ID']);
  $tab_groupes  = ($_SESSION['USER_JOIN_GROUPES']=='config') ? DB_STRUCTURE_COMMUN::DB_OPT_groupes_professeur($_SESSION['USER_ID']) : DB_STRUCTURE_COMMUN::DB_OPT_classes_groupes_etabl() ;
  $of_g = 'oui'; $sel_g = FALSE; $og_g = 'oui'; $class_form_type = 'show'; $class_form_eleve = $class_form_generique; $sel_n = FALSE;
  $multiple_eleve = ' multiple size="9"';
  $select_eleves = '<option></option>'; // maj en ajax suivant le choix du groupe
  $bouton_modifier_matieres = '<button id="modifier_matiere" type="button" class="form_ajouter">&plusmn;</button>';
}
if( ($_SESSION['USER_PROFIL_TYPE']=='parent') && ($_SESSION['NB_ENFANTS']!=1) )
{
  $tab_matieres = DB_STRUCTURE_COMMUN::DB_OPT_matieres_etabl();
  $tab_groupes  = $_SESSION['OPT_PARENT_CLASSES'];
  $of_g = 'oui'; $sel_g = FALSE; $og_g = 'non'; $class_form_type = 'hide'; $class_form_eleve = $class_form_generique; $sel_n = FALSE;
  $multiple_eleve = ''; // volontaire
  $select_eleves = '<option></option>'; // maj en ajax suivant le choix du groupe
}
if( ($_SESSION['USER_PROFIL_TYPE']=='parent') && ($_SESSION['NB_ENFANTS']==1) )
{
  $tab_matieres = DB_STRUCTURE_COMMUN::DB_OPT_matieres_eleve($_SESSION['OPT_PARENT_ENFANTS'][0]['valeur']);
  $tab_groupes  = array(0=>array('valeur'=>$_SESSION['ELEVE_CLASSE_ID'],'texte'=>$_SESSION['ELEVE_CLASSE_NOM']));
  $of_g = 'non'; $sel_g = TRUE;  $og_g = 'non'; $class_form_type = 'hide'; $class_form_eleve = 'hide'; $sel_n = 'val';
  $multiple_eleve = '';
  $select_eleves = '<option value="'.$_SESSION['OPT_PARENT_ENFANTS'][0]['valeur'].'" selected>'.html($_SESSION['OPT_PARENT_ENFANTS'][0]['texte']).'</option>';
}
if($_SESSION['USER_PROFIL_TYPE']=='eleve')
{
  $tab_matieres = DB_STRUCTURE_COMMUN::DB_OPT_matieres_eleve($_SESSION['USER_ID']);
  $tab_groupes  = array(0=>array('valeur'=>$_SESSION['ELEVE_CLASSE_ID'],'texte'=>$_SESSION['ELEVE_CLASSE_NOM']));
  $of_g = 'non'; $sel_g = TRUE;  $og_g = 'non'; $class_form_type = 'hide'; $class_form_eleve = 'hide'; $sel_n = 'val';
  $multiple_eleve = '';
  $select_eleves = '<option value="'.$_SESSION['USER_ID'].'" selected>'.html($_SESSION['USER_NOM'].' '.$_SESSION['USER_PRENOM']).'</option>';
}
$tab_periodes = DB_STRUCTURE_COMMUN::DB_OPT_periodes_etabl();

$select_tri_objet     = Form::afficher_select(Form::$tab_select_tri_objet     , $select_nom='f_tri_objet'     , $option_first='non' , $selection=Form::$tab_choix['tableau_tri_objet'] , $optgroup='non');
$select_tri_mode      = Form::afficher_select(Form::$tab_select_tri_mode      , $select_nom='f_tri_mode'      , $option_first='non' , $selection=Form::$tab_choix['tableau_tri_mode']  , $optgroup='non');
$select_remplissage   = Form::afficher_select(Form::$tab_select_remplissage   , $select_nom='f_remplissage'   , $option_first='non' , $selection=Form::$tab_choix['remplissage']       , $optgroup='non');
$select_colonne_bilan = Form::afficher_select(Form::$tab_select_colonne_bilan , $select_nom='f_colonne_bilan' , $option_first='non' , $selection=Form::$tab_choix['colonne_bilan']     , $optgroup='non');
$select_colonne_vide  = Form::afficher_select(Form::$tab_select_colonne_vide  , $select_nom='f_colonne_vide'  , $option_first='non' , $selection=Form::$tab_choix['colonne_vide']      , $optgroup='non');
$select_matiere       = Form::afficher_select($tab_matieres                   , $select_nom='f_matiere'       , $option_first='oui' , $selection=Form::$tab_choix['matiere_id']        , $optgroup='non');
$select_groupe        = Form::afficher_select($tab_groupes                    , $select_nom='f_groupe'        , $option_first=$of_g , $selection=$sel_g                                , $optgroup=$og_g);
$select_periode       = Form::afficher_select($tab_periodes                   , $select_nom='f_periode'       , $option_first='val' , $selection=FALSE                                 , $optgroup='non');
$select_orientation   = Form::afficher_select(Form::$tab_select_orientation   , $select_nom='f_orientation'   , $option_first='non' , $selection=Form::$tab_choix['orientation']       , $optgroup='non');
$select_marge_min     = Form::afficher_select(Form::$tab_select_marge_min     , $select_nom='f_marge_min'     , $option_first='non' , $selection=Form::$tab_choix['marge_min']         , $optgroup='non');
$select_couleur       = Form::afficher_select(Form::$tab_select_couleur       , $select_nom='f_couleur'       , $option_first='non' , $selection=Form::$tab_choix['couleur']           , $optgroup='non');
$select_legende       = Form::afficher_select(Form::$tab_select_legende       , $select_nom='f_legende'       , $option_first='non' , $selection=Form::$tab_choix['legende']           , $optgroup='non');
$select_cases_nb      = Form::afficher_select(Form::$tab_select_cases_nb      , $select_nom='f_cases_nb'      , $option_first='non' , $selection=Form::$tab_choix['cases_nb']          , $optgroup='non');
$select_cases_larg    = Form::afficher_select(Form::$tab_select_cases_size    , $select_nom='f_cases_larg'    , $option_first='non' , $selection=Form::$tab_choix['cases_largeur']     , $optgroup='non');

// Affichage ou non du formulaire de période
if($of_g == 'oui')
{
  $class_form_periode='hide';
}
elseif(Form::$tab_choix['type_generique'])
{
  $class_form_periode='hide';
}
elseif(Form::$tab_choix['type_synthese'])
{
  $class_form_periode='show';
}
elseif(Form::$tab_choix['type_individuel'])
{
  if( (Form::$tab_choix['remplissage']=='plein') || (Form::$tab_choix['colonne_bilan']=='oui') )
  {
    $class_form_periode='show';
  }
  else
  {
    $class_form_periode='hide';
  }
}
else
{
  $class_form_periode='hide';
}

// Fabrication du tableau javascript "tab_groupe_periode" pour les jointures groupes/périodes
list( $tab_groupe_periode_js ) = Form::fabriquer_tab_js_jointure_groupe( $tab_groupes , TRUE /*return_jointure_periode*/ , FALSE /*return_jointure_niveau*/ );
?>

<script type="text/javascript">
  var date_mysql="<?php echo TODAY_MYSQL ?>";
  var periode_requise = <?php echo ($class_form_periode=='show') ? 'true' : 'false' ?>;
  <?php echo $tab_groupe_periode_js ?> 
</script>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=releves_bilans__releve_grille_referentiel">DOC : Grille d'items d'un référentiel.</a></span></p>

<form action="#" method="post" id="form_select"><fieldset>
  <p class="<?php echo $class_form_type ?>">
    <label class="tab">Type de document :</label><label for="f_type_generique"><input type="checkbox" id="f_type_generique" name="f_type[]" value="generique"<?php echo $check_type_generique ?> /> Fiche générique</label><span id="generique_non_1" class="<?php echo $class_form_generique ?>">&nbsp;&nbsp;&nbsp;<label for="f_type_individuel"><input type="checkbox" id="f_type_individuel" name="f_type[]" value="individuel"<?php echo $check_type_individuel ?> /> Relevé individuel</label>&nbsp;&nbsp;&nbsp;<label for="f_type_synthese"><input type="checkbox" id="f_type_synthese" name="f_type[]" value="synthese"<?php echo $check_type_synthese ?> /> Synthèse collective</label></span><br />
    <span id="generique_non_2" class="<?php echo $class_form_generique ?>">
      <span id="options_individuel" class="<?php echo $class_form_individuel ?>">
        <label class="tab"><img alt="" src="./_img/bulle_aide.png" title="Paramétrage du relevé individuel." /> Opt. relevé :</label><?php echo $select_remplissage ?> <?php echo $select_colonne_bilan ?> <?php echo $select_colonne_vide ?><br />
      </span>
      <span id="options_synthese" class="<?php echo $class_form_synthese ?>">
        <label class="tab"><img alt="" src="./_img/bulle_aide.png" title="Paramétrage du tableau de synthèse." /> Opt. synthèse :</label><?php echo $select_tri_objet ?> <?php echo $select_tri_mode ?><br />
      </span>
    </span>
  </p>
  <label class="tab" for="f_matiere">Matière :</label><?php echo $select_matiere ?><?php echo $bouton_modifier_matieres ?><input type="hidden" id="f_matiere_nom" name="f_matiere_nom" value="" /><label id="ajax_maj_matiere">&nbsp;</label><br />
  <label class="tab" for="f_niveau">Niveau :</label><select id="f_niveau" name="f_niveau"><option></option></select><input type="hidden" id="f_niveau_nom" name="f_niveau_nom" value="" />
  <p id="generique_non_3" class="<?php echo $class_form_eleve ?>">
    <label class="tab" for="f_groupe">Classe / groupe :</label><?php echo $select_groupe ?><input type="hidden" id="f_groupe_nom" name="f_groupe_nom" value="" /><label id="ajax_maj_groupe">&nbsp;</label><br />
    <label class="tab" for="f_eleve"><img alt="" src="./_img/bulle_aide.png" title="Utiliser la touche &laquo;&nbsp;Shift&nbsp;&raquo; pour une sélection multiple contiguë.<br />Utiliser la touche &laquo;&nbsp;Ctrl&nbsp;&raquo; pour une sélection multiple non contiguë." /> Élève(s) :</label><select id="f_eleve" name="f_eleve[]"<?php echo $multiple_eleve ?>><?php echo $select_eleves ?></select>
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
  <div class="toggle">
    <span class="tab"></span><a href="#" class="puce_plus toggle">Afficher plus d'options</a>
  </div>
  <div class="toggle hide">
    <span class="tab"></span><a href="#" class="puce_moins toggle">Afficher moins d'options</a><br />
    <label class="tab">Restriction :</label><label for="f_restriction"><input type="checkbox" id="f_restriction" name="f_restriction" value="1"<?php echo $check_only_socle ?> /> Uniquement les items liés du socle</label><br />
    <label class="tab">Indications :</label><label for="f_coef"><input type="checkbox" id="f_coef" name="f_coef" value="1"<?php echo $check_aff_coef ?> /> Coefficients</label>&nbsp;&nbsp;&nbsp;<label for="f_socle"><input type="checkbox" id="f_socle" name="f_socle" value="1"<?php echo $check_aff_socle ?> /> Appartenance au socle</label>&nbsp;&nbsp;&nbsp;<label for="f_lien"><input type="checkbox" id="f_lien" name="f_lien" value="1"<?php echo $check_aff_lien ?> /> Liens (ressources pour travailler)</label><br />
    <label class="tab"><img alt="" src="./_img/bulle_aide.png" title="Pour le format pdf." /> Impression :</label><?php echo $select_orientation ?> <?php echo $select_couleur ?> <?php echo $select_legende ?> <?php echo $select_marge_min ?><br />
    <label class="tab">Évaluations :</label><?php echo $select_cases_nb ?> de largeur <?php echo $select_cases_larg ?>
  </div>
  <p><span class="tab"></span><button id="bouton_valider" type="submit" class="generer">Générer.</button><label id="ajax_msg">&nbsp;</label></p>
</fieldset></form>

<div id="bilan"></div>
