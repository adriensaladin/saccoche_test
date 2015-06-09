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
$TITRE = html(Lang::_("Relevé d'items sélectionnés"));
?>

<?php
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
$check_with_coef          = (Form::$tab_choix['with_coef'])              ? ' checked' : '' ;
$class_form_with_coef     = ($check_type_synthese || $check_type_bulletin || ($check_type_individuel && $check_moyenne_score) ) ? 'show' : 'hide' ;
// Ci-après sans objet car cette page n'est proposée qu'aux professeurs et directeurs.
/*
if(in_array($_SESSION['USER_PROFIL_TYPE'],array('parent','eleve')))
{
  // Une éventuelle restriction d'accès doit surcharger toute mémorisation antérieure de formulaire
  $check_etat_acquisition   = test_user_droit_specifique($_SESSION['DROIT_RELEVE_ETAT_ACQUISITION'])   ? ' checked' : '' ;
  $check_moyenne_score      = test_user_droit_specifique($_SESSION['DROIT_RELEVE_MOYENNE_SCORE'])      ? ' checked' : '' ;
  $check_pourcentage_acquis = test_user_droit_specifique($_SESSION['DROIT_RELEVE_POURCENTAGE_ACQUIS']) ? ' checked' : '' ;
  $check_conversion_sur_20  = test_user_droit_specifique($_SESSION['DROIT_RELEVE_CONVERSION_SUR_20'])  ? ' checked' : '' ;
}
*/
$class_etat_acquisition   = ($check_etat_acquisition)                           ? 'show' : 'hide' ;
$class_conversion_sur_20  = ($check_moyenne_score || $check_pourcentage_acquis) ? 'show' : 'hide' ;
$check_retroactif_auto    = (Form::$tab_choix['retroactif']=='auto')     ? ' checked' : '' ;
$check_retroactif_non     = (Form::$tab_choix['retroactif']=='non')      ? ' checked' : '' ;
$check_retroactif_oui     = (Form::$tab_choix['retroactif']=='oui')      ? ' checked' : '' ;
$check_retroactif_annuel  = (Form::$tab_choix['retroactif']=='annuel')   ? ' checked' : '' ;
$check_aff_coef           = (Form::$tab_choix['aff_coef'])               ? ' checked' : '' ;
$check_aff_socle          = (Form::$tab_choix['aff_socle'])              ? ' checked' : '' ;
$check_aff_lien           = (Form::$tab_choix['aff_lien'])               ? ' checked' : '' ;
$check_aff_domaine        = (Form::$tab_choix['aff_domaine'])            ? ' checked' : '' ;
$check_aff_theme          = (Form::$tab_choix['aff_theme'])              ? ' checked' : '' ;
$check_repeter_entete     = (Form::$tab_choix['repeter_entete'])         ? ' checked' : '' ;
$tab_groupes   = ($_SESSION['USER_JOIN_GROUPES']=='config') ? DB_STRUCTURE_COMMUN::DB_OPT_groupes_professeur($_SESSION['USER_ID']) : DB_STRUCTURE_COMMUN::DB_OPT_classes_groupes_etabl() ;
$tab_periodes  = DB_STRUCTURE_COMMUN::DB_OPT_periodes_etabl();

$select_individuel_format = HtmlForm::afficher_select(Form::$tab_select_individuel_format , 'f_individuel_format' /*select_nom*/ ,                   FALSE /*option_first*/ , Form::$tab_choix['releve_individuel_format'] /*selection*/ ,              '' /*optgroup*/);
$select_synthese_format   = HtmlForm::afficher_select(Form::$tab_select_synthese_format   , 'f_synthese_format'   /*select_nom*/ ,                   FALSE /*option_first*/ , Form::$tab_choix['tableau_synthese_format']  /*selection*/ ,              '' /*optgroup*/);
$select_tri_mode          = HtmlForm::afficher_select(Form::$tab_select_tri_mode          , 'f_tri_mode'          /*select_nom*/ ,                   FALSE /*option_first*/ , Form::$tab_choix['tableau_tri_mode']         /*selection*/ ,              '' /*optgroup*/);
$select_groupe            = HtmlForm::afficher_select($tab_groupes                        , 'f_groupe'            /*select_nom*/ ,                      '' /*option_first*/ , FALSE                                        /*selection*/ , 'regroupements' /*optgroup*/);
$select_eleves_ordre      = HtmlForm::afficher_select(Form::$tab_select_eleves_ordre      , 'f_eleves_ordre'      /*select_nom*/ ,                   FALSE /*option_first*/ , Form::$tab_choix['eleves_ordre']             /*selection*/ ,              '' /*optgroup*/);
$select_periode           = HtmlForm::afficher_select($tab_periodes                       , 'f_periode'           /*select_nom*/ , 'periode_personnalisee' /*option_first*/ , FALSE                                        /*selection*/ ,              '' /*optgroup*/);
$select_orientation       = HtmlForm::afficher_select(Form::$tab_select_orientation       , 'f_orientation'       /*select_nom*/ ,                   FALSE /*option_first*/ , Form::$tab_choix['orientation']              /*selection*/ ,              '' /*optgroup*/);
$select_marge_min         = HtmlForm::afficher_select(Form::$tab_select_marge_min         , 'f_marge_min'         /*select_nom*/ ,                   FALSE /*option_first*/ , Form::$tab_choix['marge_min']                /*selection*/ ,              '' /*optgroup*/);
$select_pages_nb          = HtmlForm::afficher_select(Form::$tab_select_pages_nb          , 'f_pages_nb'          /*select_nom*/ ,                   FALSE /*option_first*/ , Form::$tab_choix['pages_nb']                 /*selection*/ ,              '' /*optgroup*/);
$select_couleur           = HtmlForm::afficher_select(Form::$tab_select_couleur           , 'f_couleur'           /*select_nom*/ ,                   FALSE /*option_first*/ , Form::$tab_choix['couleur']                  /*selection*/ ,              '' /*optgroup*/);
$select_fond              = HtmlForm::afficher_select(Form::$tab_select_fond              , 'f_fond'              /*select_nom*/ ,                   FALSE /*option_first*/ , Form::$tab_choix['fond']                     /*selection*/ ,              '' /*optgroup*/);
$select_legende           = HtmlForm::afficher_select(Form::$tab_select_legende           , 'f_legende'           /*select_nom*/ ,                   FALSE /*option_first*/ , Form::$tab_choix['legende']                  /*selection*/ ,              '' /*optgroup*/);
$select_cases_nb          = HtmlForm::afficher_select(Form::$tab_select_cases_nb          , 'f_cases_nb'          /*select_nom*/ ,                   FALSE /*option_first*/ , Form::$tab_choix['cases_nb']                 /*selection*/ ,              '' /*optgroup*/);
$select_cases_larg        = HtmlForm::afficher_select(Form::$tab_select_cases_size        , 'f_cases_larg'        /*select_nom*/ ,                   FALSE /*option_first*/ , Form::$tab_choix['cases_largeur']            /*selection*/ ,              '' /*optgroup*/);

$select_selection_items = HtmlForm::afficher_select(DB_STRUCTURE_COMMUN::DB_OPT_selection_items($_SESSION['USER_ID']) , 'f_selection_items' , '' /*option_first*/ , FALSE /*selection*/ , '' /*optgroup*/);

// Javascript
Layout::add( 'js_inline_before' , 'var date_mysql = "'.TODAY_MYSQL.'";' );
// Fabrication du tableau javascript "tab_groupe_periode" pour les jointures groupes/périodes
HtmlForm::fabriquer_tab_js_jointure_groupe( $tab_groupes , TRUE /*tab_groupe_periode*/ , FALSE /*tab_groupe_niveau*/ );
?>

<div><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=releves_bilans__releve_items_selection">DOC : Relevé d'items sélectionnés.</a></span></div>

<hr />

<form action="#" method="post" id="form_select"><fieldset>
  <label class="tab">Type de document :</label><label for="f_type_individuel"><input type="checkbox" id="f_type_individuel" name="f_type[]" value="individuel"<?php echo $check_type_individuel ?> /> Relevé individuel</label>&nbsp;&nbsp;&nbsp;<label for="f_type_synthese"><input type="checkbox" id="f_type_synthese" name="f_type[]" value="synthese"<?php echo $check_type_synthese ?> /> Synthèse collective</label>&nbsp;&nbsp;&nbsp;<label for="f_type_bulletin"><input type="checkbox" id="f_type_bulletin" name="f_type[]" value="bulletin"<?php echo $check_type_bulletin ?> /> Bulletin (moyenne &amp; appréciation)</label><br />
  <span id="options_individuel" class="<?php echo $class_form_individuel ?>">
    <label class="tab"><img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Pour le relévé individuel, une colonne et deux lignes de synthèse peuvent être ajoutées.<br />Dans ce cas, une note sur 20 peut aussi être affichée." /> Opt. relevé :</label><?php echo $select_individuel_format ?> avec <?php echo $select_cases_nb ?> d'évaluation<br /><span class="tab"></span><label for="f_etat_acquisition"><input type="checkbox" id="f_etat_acquisition" name="f_etat_acquisition" value="1"<?php echo $check_etat_acquisition ?> /> Colonne état d'acquisition</label><span id="span_etat_acquisition" class="<?php echo $class_etat_acquisition ?>">&nbsp;&nbsp;&nbsp;<label for="f_moyenne_scores"><input type="checkbox" id="f_moyenne_scores" name="f_moyenne_scores" value="1"<?php echo $check_moyenne_score ?> /> Ligne moyenne des scores</label>&nbsp;&nbsp;&nbsp;<label for="f_pourcentage_acquis"><input type="checkbox" id="f_pourcentage_acquis" name="f_pourcentage_acquis" value="1"<?php echo $check_pourcentage_acquis ?> /> Ligne pourcentage d'items acquis</label>&nbsp;&nbsp;&nbsp;<label for="f_conversion_sur_20" class="<?php echo $class_conversion_sur_20 ?>"><input type="checkbox" id="f_conversion_sur_20" name="f_conversion_sur_20" value="1"<?php echo $check_conversion_sur_20 ?> /> Conversion en note sur 20</label></span><br />
  </span>
  <span id="options_synthese" class="<?php echo $class_form_synthese ?>">
    <label class="tab"><img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Paramétrage du tableau de synthèse." /> Opt. synthèse :</label><?php echo $select_synthese_format ?> <?php echo $select_tri_mode ?><br />
    <span class="tab"></span><label for="f_repeter_entete"><input type="checkbox" id="f_repeter_entete" name="f_repeter_entete" value="1"<?php echo $check_repeter_entete ?> /> Répéter les entêtes de lignes et de colonnes (grand tableau, format <em>html</em>)</label><br />
  </span>
  <span id="option_with_coef" class="<?php echo $class_form_with_coef ?>">
    <label class="tab"><img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Concerne le relevé individuel avec moyenne des scores, la synthèse collective, la moyenne d'un bulletin.<br />La question se pose notamment dans le cas d'items issus de référentiels de plusieurs matières." /> Coefficients :</label><label for="f_with_coef"><input type="checkbox" id="f_with_coef" name="f_with_coef" value="1"<?php echo $check_with_coef ?> /> Prise en compte des coefficients</label><br />
  </span>
  <p>
    <label class="tab">Items :</label><input id="f_compet_nombre" name="f_compet_nombre" size="10" type="text" value="aucun" readonly /><input id="f_compet_liste" name="f_compet_liste" type="text" value="" class="invisible" /><q class="choisir_compet" title="Voir ou choisir les items."></q>
  </p>
  <label class="tab" for="f_groupe">Classe / groupe :</label><?php echo $select_groupe ?><input type="hidden" id="f_groupe_type" name="f_groupe_type" value="" /><input type="hidden" id="f_groupe_nom" name="f_groupe_nom" value="" /> <span id="bloc_ordre" class="hide"><?php echo $select_eleves_ordre ?></span><label id="ajax_maj">&nbsp;</label><br />
  <span id="bloc_eleve" class="hide"><label class="tab" for="f_eleve">Élève(s) :</label><span id="f_eleve" class="select_multiple"></span><span class="check_multiple"><q class="cocher_tout" title="Tout cocher."></q><br /><q class="cocher_rien" title="Tout décocher."></q></span></span>
  <p id="zone_periodes" class="hide">
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
  <div class="toggle">
    <span class="tab"></span><a href="#" class="puce_plus toggle">Afficher plus d'options</a>
  </div>
  <div class="toggle hide">
    <span class="tab"></span><a href="#" class="puce_moins toggle">Afficher moins d'options</a><br />
    <label class="tab"><img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Pour le relévé individuel, les paramètres des items peuvent être affichés." /> Infos items :</label><label for="f_coef"><input type="checkbox" id="f_coef" name="f_coef" value="1"<?php echo $check_aff_coef ?> /> Coefficients</label>&nbsp;&nbsp;&nbsp;<label for="f_socle"><input type="checkbox" id="f_socle" name="f_socle" value="1"<?php echo $check_aff_socle ?> /> Appartenance au socle</label>&nbsp;&nbsp;&nbsp;<label for="f_lien"><input type="checkbox" id="f_lien" name="f_lien" value="1"<?php echo $check_aff_lien ?> /> Liens (ressources pour travailler)</label>&nbsp;&nbsp;&nbsp;<label for="f_domaine"><input type="checkbox" id="f_domaine" name="f_domaine" value="1"<?php echo $check_aff_domaine ?> /> Domaines</label>&nbsp;&nbsp;&nbsp;<label for="f_theme"><input type="checkbox" id="f_theme" name="f_theme" value="1"<?php echo $check_aff_theme ?> /> Thèmes</label><br />
    <label class="tab"><img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Pour le format PDF." /> Impression :</label><?php echo $select_orientation ?> <?php echo $select_couleur ?> <?php echo $select_fond ?> <?php echo $select_legende ?> <?php echo $select_marge_min ?> <?php echo $select_pages_nb ?><br />
    <label class="tab">Évaluations :</label>cases de largeur <?php echo $select_cases_larg ?>
  </div>
  <p><span class="tab"></span><button id="bouton_valider" type="submit" class="generer">Générer.</button><label id="ajax_msg">&nbsp;</label></p>
</fieldset></form>

<form action="#" method="post" id="zone_matieres_items" class="arbre_dynamique arbre_check hide">
  <div>Tout déployer / contracter :<q class="deployer_m1"></q><q class="deployer_m2"></q><q class="deployer_n1"></q><q class="deployer_n2"></q><q class="deployer_n3"></q></div>
  <p>Cocher ci-dessous (<span class="astuce">cliquer sur un intitulé pour déployer son contenu</span>) :</p>
  <?php
  // Affichage de la liste des items pour toutes les matières d'un professeur ou toutes les matières de l'établissement si directeur ou PP, sur tous les niveaux
  $user_id = ( ($_SESSION['USER_PROFIL_TYPE']=='professeur') && !DB_STRUCTURE_PROFESSEUR::DB_tester_prof_principal($_SESSION['USER_ID'],0) ) ? $_SESSION['USER_ID'] : 0 ;
  $DB_TAB = DB_STRUCTURE_COMMUN::DB_recuperer_arborescence( $user_id , 0 /*matiere_id*/ , 0 /*niveau_id*/, FALSE /*only_socle*/ , FALSE /*only_item*/ , FALSE /*socle_nom*/ );
  if(empty($DB_TAB))
  {
    echo'<p class="danger">Vous n\'êtes rattaché à aucune matière, ou des matières ne comportant aucun référentiel !</p>' ;
  }
  else
  {
    $arborescence = HtmlArborescence::afficher_matiere_from_SQL( $DB_TAB , TRUE /*dynamique*/ , TRUE /*reference*/ , FALSE /*aff_coef*/ , FALSE /*aff_cart*/ , 'texte' /*aff_socle*/ , FALSE /*aff_lien*/ , TRUE /*aff_input*/ );
    echo strpos($arborescence,'<input') ? $arborescence : '<p class="danger">Vous êtes rattaché à des matières dont les référentiels ne comportent aucun item !</p>' ;
  }
  ?>
  <p><span class="tab"></span><button id="valider_compet" type="button" class="valider">Valider la sélection</button>&nbsp;&nbsp;&nbsp;<button id="annuler_compet" type="button" class="annuler">Annuler / Retour</button></p>
  <hr />
  <p>
    <label class="tab" for="f_selection_items"><img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Pour choisir un regroupement d'items mémorisé." /> Initialisation</label><?php echo $select_selection_items ?><br />
    <label class="tab" for="f_liste_items_nom"><img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Pour enregistrer le groupe d'items cochés." /> Mémorisation</label><input id="f_liste_items_nom" name="f_liste_items_nom" size="30" type="text" value="" maxlength="60" /> <button id="f_enregistrer_items" type="button" class="fichier_export">Enregistrer</button><label id="ajax_msg_memo">&nbsp;</label>
  </p>
</form>

<div id="bilan"></div>
