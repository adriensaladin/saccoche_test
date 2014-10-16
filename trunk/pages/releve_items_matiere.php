<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010-2014
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
$TITRE = "Relevé d'items d'une matière";

// L'élève ne choisit évidemment pas sa classe ni son nom, mais on construit qd même les formulaires, on les remplit et on les cache (permet un code unique et une transmission des infos en ajax comme pour les autres profils).
// L'élève ne choisit pas non plus son type de bilan (=>individuel), et les options du relevé sont prédéfinies.
Form::load_choix_memo();
$check_type_individuel    = (Form::$tab_choix['type_individuel'])        ? ' checked' : '' ;
$class_form_individuel    = (Form::$tab_choix['type_individuel'])        ? 'show'     : 'hide' ;
$check_type_synthese      = (Form::$tab_choix['type_synthese'])          ? ' checked' : '' ;
$class_form_synthese      = (Form::$tab_choix['type_synthese'])          ? 'show'     : 'hide' ;
$check_type_bulletin      = (Form::$tab_choix['type_bulletin'])          ? ' checked' : '' ;
$check_etat_acquisition   = (Form::$tab_choix['aff_etat_acquisition'])   ? ' checked' : '' ;
$check_moyenne_score      = (Form::$tab_choix['aff_moyenne_scores'])     ? ' checked' : '' ;
$check_pourcentage_acquis = (Form::$tab_choix['aff_pourcentage_acquis']) ? ' checked' : '' ;
$check_conversion_sur_20  = (Form::$tab_choix['conversion_sur_20'])      ? ' checked' : '' ;

// Réception d'id transmis via un lien en page d'accueil.
$auto_voir_releve       = isset($_GET['matiere_id']) ? 'true'                             : 'false' ;
$auto_select_matiere_id = isset($_GET['matiere_id']) ? Clean::entier($_GET['matiere_id']) : Form::$tab_choix['matiere_id'] ;
$auto_select_eleve_num  = isset($_GET['eleve_num'])  ? Clean::entier($_GET['eleve_num'])  : FALSE ;
$auto_highlight_item_id = isset($_GET['item_id'])    ? Clean::entier($_GET['item_id'])    : 0 ;

$auto_select_eleve_id  = ( ($auto_select_eleve_num!==FALSE) && !empty($_SESSION['OPT_PARENT_ENFANTS'][$auto_select_eleve_num]) ) ? $_SESSION['OPT_PARENT_ENFANTS'][$auto_select_eleve_num]['valeur']    : 0 ;
$auto_select_classe_id = ( ($auto_select_eleve_num!==FALSE) && !empty($_SESSION['OPT_PARENT_ENFANTS'][$auto_select_eleve_num]) ) ? $_SESSION['OPT_PARENT_ENFANTS'][$auto_select_eleve_num]['classe_id'] : FALSE ;

if(in_array($_SESSION['USER_PROFIL_TYPE'],array('parent','eleve')))
{
  // Une éventuelle restriction d'accès doit surcharger toute mémorisation antérieure de formulaire
  $check_etat_acquisition   = test_user_droit_specifique($_SESSION['DROIT_RELEVE_ETAT_ACQUISITION'])   ? ' checked' : '' ;
  $check_moyenne_score      = test_user_droit_specifique($_SESSION['DROIT_RELEVE_MOYENNE_SCORE'])      ? ' checked' : '' ;
  $check_pourcentage_acquis = test_user_droit_specifique($_SESSION['DROIT_RELEVE_POURCENTAGE_ACQUIS']) ? ' checked' : '' ;
  $check_conversion_sur_20  = test_user_droit_specifique($_SESSION['DROIT_RELEVE_CONVERSION_SUR_20'])  ? ' checked' : '' ;
}
$class_etat_acquisition   = ($check_etat_acquisition)                           ? 'show' : 'hide' ;
$class_conversion_sur_20  = ($check_moyenne_score || $check_pourcentage_acquis) ? 'show' : 'hide' ;
$check_retroactif_auto    = (Form::$tab_choix['retroactif']=='auto')     ? ' checked' : '' ;
$check_retroactif_non     = (Form::$tab_choix['retroactif']=='non')      ? ' checked' : '' ;
$check_retroactif_oui     = (Form::$tab_choix['retroactif']=='oui')      ? ' checked' : '' ;
$check_retroactif_annuel  = (Form::$tab_choix['retroactif']=='annuel')   ? ' checked' : '' ;
$check_only_socle         = (Form::$tab_choix['only_socle'])             ? ' checked' : '' ;
$check_aff_coef           = (Form::$tab_choix['aff_coef'])               ? ' checked' : '' ;
$check_aff_socle          = (Form::$tab_choix['aff_socle'])              ? ' checked' : '' ;
$check_aff_lien           = (Form::$tab_choix['aff_lien'])               ? ' checked' : '' ;
$check_aff_domaine        = (Form::$tab_choix['aff_domaine'])            ? ' checked' : '' ;
$check_aff_theme          = (Form::$tab_choix['aff_theme'])              ? ' checked' : '' ;
$bouton_modifier_matieres = '';
if($_SESSION['USER_PROFIL_TYPE']=='directeur')
{
  $tab_groupes  = DB_STRUCTURE_COMMUN::DB_OPT_classes_groupes_etabl();
  $tab_matieres = 'Choisir d\'abord un groupe ci-dessus...'; // maj en ajax suivant le choix du groupe
  $of_g = ''; $sel_g = FALSE; $class_form_type = 'show'; $class_form_eleve = 'show'; $class_form_periode = 'hide';
  $select_eleves = '<span id="f_eleve" class="select_multiple"></span><span class="check_multiple"><q class="cocher_tout" title="Tout cocher."></q><br /><q class="cocher_rien" title="Tout décocher."></q></span>'; // maj en ajax suivant le choix du groupe
  $is_select_multiple = 1;
}
if($_SESSION['USER_PROFIL_TYPE']=='professeur')
{
  $tab_groupes  = ($_SESSION['USER_JOIN_GROUPES']=='config') ? DB_STRUCTURE_COMMUN::DB_OPT_groupes_professeur($_SESSION['USER_ID']) : DB_STRUCTURE_COMMUN::DB_OPT_classes_groupes_etabl() ;
  $tab_matieres = DB_STRUCTURE_COMMUN::DB_OPT_matieres_professeur($_SESSION['USER_ID']);
  $of_g = ''; $sel_g = FALSE; $class_form_type = 'show'; $class_form_eleve = 'show'; $class_form_periode = 'hide';
  $select_eleves = '<span id="f_eleve" class="select_multiple"></span><span class="check_multiple"><q class="cocher_tout" title="Tout cocher."></q><br /><q class="cocher_rien" title="Tout décocher."></q></span>'; // maj en ajax suivant le choix du groupe
  $is_select_multiple = 1;
  $bouton_modifier_matieres = '<button id="modifier_matiere" type="button" class="form_ajouter">&plusmn;</button>';
}
if( ($_SESSION['USER_PROFIL_TYPE']=='parent') && ($_SESSION['NB_ENFANTS']!=1) )
{
  $tab_groupes  = $_SESSION['OPT_PARENT_CLASSES'];
  $tab_matieres = DB_STRUCTURE_COMMUN::DB_OPT_matieres_etabl();
  $of_g = ''; $sel_g = $auto_select_classe_id; $class_form_type = 'hide'; $class_form_eleve = 'show'; $class_form_periode = ($auto_select_eleve_num!==FALSE) ? 'show' : 'hide' ;
  $select_eleves = '<select id="f_eleve" name="f_eleve[]"><option></option></select>'; // maj en ajax suivant le choix du groupe
  $is_select_multiple = 0; // volontaire
}
if( ($_SESSION['USER_PROFIL_TYPE']=='parent') && ($_SESSION['NB_ENFANTS']==1) )
{
  $tab_groupes  = array(0=>array('valeur'=>$_SESSION['ELEVE_CLASSE_ID'],'texte'=>$_SESSION['ELEVE_CLASSE_NOM'],'optgroup'=>'classe'));
  $tab_matieres = DB_STRUCTURE_COMMUN::DB_OPT_matieres_eleve($_SESSION['OPT_PARENT_ENFANTS'][0]['valeur']);
  $of_g = FALSE; $sel_g = TRUE; $class_form_type = 'hide'; $class_form_eleve = 'hide'; $class_form_periode = 'show';
  $select_eleves = '<select id="f_eleve" name="f_eleve[]"><option value="'.$_SESSION['OPT_PARENT_ENFANTS'][0]['valeur'].'" selected>'.html($_SESSION['OPT_PARENT_ENFANTS'][0]['texte']).'</option></select>';
  $is_select_multiple = 0;
}
if($_SESSION['USER_PROFIL_TYPE']=='eleve')
{
  $tab_groupes  = array(0=>array('valeur'=>$_SESSION['ELEVE_CLASSE_ID'],'texte'=>$_SESSION['ELEVE_CLASSE_NOM'],'optgroup'=>'classe'));
  $tab_matieres = DB_STRUCTURE_COMMUN::DB_OPT_matieres_eleve($_SESSION['USER_ID']);
  $of_g = FALSE; $sel_g = TRUE; $class_form_type = 'hide'; $class_form_eleve = 'hide'; $class_form_periode = 'show';
  $select_eleves = '<select id="f_eleve" name="f_eleve[]"><option value="'.$_SESSION['USER_ID'].'" selected>'.html($_SESSION['USER_NOM'].' '.$_SESSION['USER_PRENOM']).'</option></select>';
  $is_select_multiple = 0;
}
$tab_periodes = DB_STRUCTURE_COMMUN::DB_OPT_periodes_etabl();

$select_individuel_format = Form::afficher_select(Form::$tab_select_individuel_format , 'f_individuel_format' /*select_nom*/ ,                   FALSE /*option_first*/ , Form::$tab_choix['releve_individuel_format'] /*selection*/ ,              '' /*optgroup*/);
$select_synthese_format   = Form::afficher_select(Form::$tab_select_synthese_format   , 'f_synthese_format'   /*select_nom*/ ,                   FALSE /*option_first*/ , Form::$tab_choix['tableau_synthese_format']  /*selection*/ ,              '' /*optgroup*/);
$select_tri_mode          = Form::afficher_select(Form::$tab_select_tri_mode          , 'f_tri_mode'          /*select_nom*/ ,                   FALSE /*option_first*/ , Form::$tab_choix['tableau_tri_mode']         /*selection*/ ,              '' /*optgroup*/);
$select_groupe            = Form::afficher_select($tab_groupes                        , 'f_groupe'            /*select_nom*/ ,                   $of_g /*option_first*/ , $sel_g                                       /*selection*/ , 'regroupements' /*optgroup*/);
$select_eleves_ordre      = Form::afficher_select(Form::$tab_select_eleves_ordre      , 'f_eleves_ordre'      /*select_nom*/ ,                   FALSE /*option_first*/ , Form::$tab_choix['eleves_ordre']             /*selection*/ ,              '' /*optgroup*/);
$select_matiere           = Form::afficher_select($tab_matieres                       , 'f_matiere'           /*select_nom*/ ,                      '' /*option_first*/ , $auto_select_matiere_id                      /*selection*/ ,              '' /*optgroup*/);
$select_periode           = Form::afficher_select($tab_periodes                       , 'f_periode'           /*select_nom*/ , 'periode_personnalisee' /*option_first*/ , FALSE                                        /*selection*/ ,              '' /*optgroup*/);
$select_orientation       = Form::afficher_select(Form::$tab_select_orientation       , 'f_orientation'       /*select_nom*/ ,                   FALSE /*option_first*/ , Form::$tab_choix['orientation']              /*selection*/ ,              '' /*optgroup*/);
$select_marge_min         = Form::afficher_select(Form::$tab_select_marge_min         , 'f_marge_min'         /*select_nom*/ ,                   FALSE /*option_first*/ , Form::$tab_choix['marge_min']                /*selection*/ ,              '' /*optgroup*/);
$select_pages_nb          = Form::afficher_select(Form::$tab_select_pages_nb          , 'f_pages_nb'          /*select_nom*/ ,                   FALSE /*option_first*/ , Form::$tab_choix['pages_nb']                 /*selection*/ ,              '' /*optgroup*/);
$select_couleur           = Form::afficher_select(Form::$tab_select_couleur           , 'f_couleur'           /*select_nom*/ ,                   FALSE /*option_first*/ , Form::$tab_choix['couleur']                  /*selection*/ ,              '' /*optgroup*/);
$select_legende           = Form::afficher_select(Form::$tab_select_legende           , 'f_legende'           /*select_nom*/ ,                   FALSE /*option_first*/ , Form::$tab_choix['legende']                  /*selection*/ ,              '' /*optgroup*/);
$select_cases_nb          = Form::afficher_select(Form::$tab_select_cases_nb          , 'f_cases_nb'          /*select_nom*/ ,                   FALSE /*option_first*/ , Form::$tab_choix['cases_nb']                 /*selection*/ ,              '' /*optgroup*/);
$select_cases_larg        = Form::afficher_select(Form::$tab_select_cases_size        , 'f_cases_larg'        /*select_nom*/ ,                   FALSE /*option_first*/ , Form::$tab_choix['cases_largeur']            /*selection*/ ,              '' /*optgroup*/);

// Javascript
Layout::add( 'js_inline_before' , 'var date_mysql  = "'.TODAY_MYSQL.'";' );
Layout::add( 'js_inline_before' , 'var is_multiple = '.$is_select_multiple.';' );
Layout::add( 'js_inline_before' , 'var auto_voir_releve     = '.$auto_voir_releve.';' );
Layout::add( 'js_inline_before' , 'var auto_select_eleve_id = '.$auto_select_eleve_id.';' );

// Fabrication du tableau javascript "tab_groupe_periode" pour les jointures groupes/périodes
Form::fabriquer_tab_js_jointure_groupe( $tab_groupes , TRUE /*tab_groupe_periode*/ , FALSE /*tab_groupe_niveau*/ );
?>

<div><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=releves_bilans__releve_items_matiere">DOC : Relevé d'items d'une matière.</a></span></div>
<div class="<?php echo $class_form_type ?>"><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=releves_bilans__transfert_bulletin_SACoche_Gepi">DOC : Transfert du bulletin de SACoche vers GEPI.</a></span></div>
<div class="<?php echo $class_form_type ?>"><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=releves_bilans__transfert_note_SACoche_Gepi">DOC : Transfert d'une note bilan de SACoche vers GEPI.</a></span></div>

<hr />

<form action="#" method="post" id="form_select"><fieldset>
  <p class="<?php echo $class_form_type ?>">
    <label class="tab">Type de document :</label><label for="f_type_individuel"><input type="checkbox" id="f_type_individuel" name="f_type[]" value="individuel"<?php echo $check_type_individuel ?> /> Relevé individuel</label>&nbsp;&nbsp;&nbsp;<label for="f_type_synthese"><input type="checkbox" id="f_type_synthese" name="f_type[]" value="synthese"<?php echo $check_type_synthese ?> /> Synthèse collective</label>&nbsp;&nbsp;&nbsp;<label for="f_type_bulletin"><input type="checkbox" id="f_type_bulletin" name="f_type[]" value="bulletin"<?php echo $check_type_bulletin ?> /> Bulletin (moyenne &amp; appréciation)</label><br />
    <span id="options_individuel" class="<?php echo $class_form_individuel ?>">
      <label class="tab"><img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Pour le relévé individuel, une colonne et deux lignes de synthèse peuvent être ajoutées.<br />Dans ce cas, une note sur 20 peut aussi être affichée." /> Opt. relevé :</label><?php if(!in_array($_SESSION['USER_PROFIL_TYPE'],array('parent','eleve'))) { echo $select_individuel_format.' avec '.$select_cases_nb.' d\'évaluation<br /><span class="tab"></span>'; } else { echo'<input type="hidden" id="f_individuel_format" name="f_individuel_format" value="'.Form::$tab_choix['releve_individuel_format'].'" />'; } ?><label for="f_etat_acquisition"><input type="checkbox" id="f_etat_acquisition" name="f_etat_acquisition" value="1"<?php echo $check_etat_acquisition ?> /> Colonne état d'acquisition</label><span id="span_etat_acquisition" class="<?php echo $class_etat_acquisition ?>">&nbsp;&nbsp;&nbsp;<label for="f_moyenne_scores"><input type="checkbox" id="f_moyenne_scores" name="f_moyenne_scores" value="1"<?php echo $check_moyenne_score ?> /> Ligne moyenne des scores</label>&nbsp;&nbsp;&nbsp;<label for="f_pourcentage_acquis"><input type="checkbox" id="f_pourcentage_acquis" name="f_pourcentage_acquis" value="1"<?php echo $check_pourcentage_acquis ?> /> Ligne pourcentage d'items acquis</label>&nbsp;&nbsp;&nbsp;<label for="f_conversion_sur_20" class="<?php echo $class_conversion_sur_20 ?>"><input type="checkbox" id="f_conversion_sur_20" name="f_conversion_sur_20" value="1"<?php echo $check_conversion_sur_20 ?> /> Conversion en note sur 20</label></span><br />
    </span>
    <span id="options_synthese" class="<?php echo $class_form_synthese ?>">
      <label class="tab"><img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Paramétrage du tableau de synthèse." /> Opt. synthèse :</label><?php echo $select_synthese_format ?> <?php echo $select_tri_mode ?><br />
    </span>
  </p>
  <p class="<?php echo $class_form_eleve ?>">
    <label class="tab" for="f_groupe">Classe / groupe :</label><?php echo $select_groupe ?><input type="hidden" id="f_groupe_type" name="f_groupe_type" value="" /><input type="hidden" id="f_groupe_nom" name="f_groupe_nom" value="" /> <span id="bloc_ordre" class="hide"><?php echo $select_eleves_ordre ?></span><label id="ajax_maj">&nbsp;</label><br />
    <span id="bloc_eleve" class="hide"><label class="tab" for="f_eleve">Élève(s) :</label><?php echo $select_eleves ?></span>
  </p>
  <p id="zone_periodes" class="<?php echo $class_form_periode ?>">
    <label class="tab" for="f_periode"><img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Les items pris en compte sont ceux qui sont évalués<br />au moins une fois sur cette période." /> Période :</label><?php echo $select_periode ?>
    <span id="dates_perso" class="show">
      du <input id="f_date_debut" name="f_date_debut" size="9" type="text" value="<?php echo jour_debut_annee_scolaire('french') ?>" /><q class="date_calendrier" title="Cliquer sur cette image pour importer une date depuis un calendrier !"></q>
      au <input id="f_date_fin" name="f_date_fin" size="9" type="text" value="<?php echo TODAY_FR ?>" /><q class="date_calendrier" title="Cliquer sur cette image pour importer une date depuis un calendrier !"></q>
    </span><br />
    <span class="radio"><img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Le bilan peut être établi uniquement sur la période considérée<br />ou en tenant compte d'évaluations antérieures des items concernés.<br />En automatique, les paramètres enregistrés pour chaque référentiel s'appliquent." /> Prise en compte des évaluations antérieures :</span>
      <label for="f_retroactif_auto"><input type="radio" id="f_retroactif_auto" name="f_retroactif" value="auto"<?php echo $check_retroactif_auto ?> /> automatique (selon référentiels)</label>&nbsp;&nbsp;&nbsp;
      <label for="f_retroactif_non"><input type="radio" id="f_retroactif_non" name="f_retroactif" value="non"<?php echo $check_retroactif_non ?> /> non</label>&nbsp;&nbsp;&nbsp;
      <label for="f_retroactif_oui"><input type="radio" id="f_retroactif_oui" name="f_retroactif" value="oui"<?php echo $check_retroactif_oui ?> /> oui (sans limite)</label>&nbsp;&nbsp;&nbsp;
      <label for="f_retroactif_annuel"><input type="radio" id="f_retroactif_annuel" name="f_retroactif" value="annuel"<?php echo $check_retroactif_annuel ?> /> de l'année scolaire
  </p>
  <p>
    <label class="tab" for="f_matiere">Matière :</label><?php echo $select_matiere ?><?php echo $bouton_modifier_matieres ?><input type="hidden" id="f_matiere_nom" name="f_matiere_nom" value="" />
  </p>
  <div class="toggle">
    <span class="tab"></span><a href="#" class="puce_plus toggle">Afficher plus d'options</a>
  </div>
  <div class="toggle hide">
    <span class="tab"></span><a href="#" class="puce_moins toggle">Afficher moins d'options</a><br />
    <label class="tab">Restriction :</label><label for="f_restriction"><input type="checkbox" id="f_restriction" name="f_restriction" value="1"<?php echo $check_only_socle ?> /> Uniquement les items liés au socle</label><br />
    <label class="tab"><img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Pour le relévé individuel, les paramètres des items peuvent être affichés." /> Infos items :</label><label for="f_coef"><input type="checkbox" id="f_coef" name="f_coef" value="1"<?php echo $check_aff_coef ?> /> Coefficients</label>&nbsp;&nbsp;&nbsp;<label for="f_socle"><input type="checkbox" id="f_socle" name="f_socle" value="1"<?php echo $check_aff_socle ?> /> Appartenance au socle</label>&nbsp;&nbsp;&nbsp;<label for="f_lien"><input type="checkbox" id="f_lien" name="f_lien" value="1"<?php echo $check_aff_lien ?> /> Liens (ressources pour travailler)</label>&nbsp;&nbsp;&nbsp;<label for="f_domaine"><input type="checkbox" id="f_domaine" name="f_domaine" value="1"<?php echo $check_aff_domaine ?> /> Domaines</label>&nbsp;&nbsp;&nbsp;<label for="f_theme"><input type="checkbox" id="f_theme" name="f_theme" value="1"<?php echo $check_aff_theme ?> /> Thèmes</label><br />
    <label class="tab"><img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Pour le format pdf." /> Impression :</label><?php echo $select_orientation ?> <?php echo $select_couleur ?> <?php echo $select_legende ?> <?php echo $select_marge_min ?> <?php echo $select_pages_nb ?><br />
    <label class="tab">Évaluations :</label><?php echo(in_array($_SESSION['USER_PROFIL_TYPE'],array('parent','eleve'))) ? $select_cases_nb : 'cases' ; ?> de largeur <?php echo $select_cases_larg ?>
  </div>
  <p><span class="tab"></span><input type="hidden" id="f_highlight_id" name="f_highlight_id" value="<?php echo $auto_highlight_item_id ?>" /><button id="bouton_valider" type="submit" class="generer">Générer.</button><label id="ajax_msg">&nbsp;</label></p>
</fieldset></form>

<div id="bilan"></div>

