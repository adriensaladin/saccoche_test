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
$TITRE = html(Lang::_("Configuration des bilans officiels"));

$select_releve_appreciation_rubrique_longueur = HtmlForm::afficher_select(Form::$tab_select_appreciation , 'f_releve_appreciation_rubrique_longueur' /*select_nom*/ , FALSE /*option_first*/ , $_SESSION['OFFICIEL']['RELEVE_APPRECIATION_RUBRIQUE_LONGUEUR'] /*selection*/ , '' /*optgroup*/ );
$select_releve_appreciation_generale_longueur = HtmlForm::afficher_select(Form::$tab_select_appreciation , 'f_releve_appreciation_generale_longueur' /*select_nom*/ , FALSE /*option_first*/ , $_SESSION['OFFICIEL']['RELEVE_APPRECIATION_GENERALE_LONGUEUR'] /*selection*/ , '' /*optgroup*/ );
$select_releve_only_etat                      = HtmlForm::afficher_select(Form::$tab_select_only_etat    , 'f_releve_only_etat'                      /*select_nom*/ , FALSE /*option_first*/ , $_SESSION['OFFICIEL']['RELEVE_ONLY_ETAT']                      /*selection*/ , '' /*optgroup*/ );
$select_releve_cases_nb                       = HtmlForm::afficher_select(Form::$tab_select_cases_nb     , 'f_releve_cases_nb'                       /*select_nom*/ , FALSE /*option_first*/ , $_SESSION['OFFICIEL']['RELEVE_CASES_NB']                       /*selection*/ , '' /*optgroup*/ );
$select_releve_couleur                        = HtmlForm::afficher_select(Form::$tab_select_couleur      , 'f_releve_couleur'                        /*select_nom*/ , FALSE /*option_first*/ , $_SESSION['OFFICIEL']['RELEVE_COULEUR']                        /*selection*/ , '' /*optgroup*/ );
$select_releve_fond                           = HtmlForm::afficher_select(Form::$tab_select_fond         , 'f_releve_fond'                           /*select_nom*/ , FALSE /*option_first*/ , $_SESSION['OFFICIEL']['RELEVE_FOND']                           /*selection*/ , '' /*optgroup*/ );
$select_releve_legende                        = HtmlForm::afficher_select(Form::$tab_select_legende      , 'f_releve_legende'                        /*select_nom*/ , FALSE /*option_first*/ , $_SESSION['OFFICIEL']['RELEVE_LEGENDE']                        /*selection*/ , '' /*optgroup*/ );
$select_releve_pages_nb                       = HtmlForm::afficher_select(Form::$tab_select_pages_nb     , 'f_releve_pages_nb'                       /*select_nom*/ , FALSE /*option_first*/ , $_SESSION['OFFICIEL']['RELEVE_PAGES_NB']                       /*selection*/ , '' /*optgroup*/ );

$select_bulletin_appreciation_rubrique_longueur = HtmlForm::afficher_select(Form::$tab_select_appreciation , 'f_bulletin_appreciation_rubrique_longueur' /*select_nom*/ , FALSE /*option_first*/ , $_SESSION['OFFICIEL']['BULLETIN_APPRECIATION_RUBRIQUE_LONGUEUR'] /*selection*/ , '' /*optgroup*/ );
$select_bulletin_appreciation_generale_longueur = HtmlForm::afficher_select(Form::$tab_select_appreciation , 'f_bulletin_appreciation_generale_longueur' /*select_nom*/ , FALSE /*option_first*/ , $_SESSION['OFFICIEL']['BULLETIN_APPRECIATION_GENERALE_LONGUEUR'] /*selection*/ , '' /*optgroup*/ );
$select_bulletin_couleur                        = HtmlForm::afficher_select(Form::$tab_select_couleur      , 'f_bulletin_couleur'                        /*select_nom*/ , FALSE /*option_first*/ , $_SESSION['OFFICIEL']['BULLETIN_COULEUR']                        /*selection*/ , '' /*optgroup*/ );
$select_bulletin_fond                           = HtmlForm::afficher_select(Form::$tab_select_fond         , 'f_bulletin_fond'                           /*select_nom*/ , FALSE /*option_first*/ , $_SESSION['OFFICIEL']['BULLETIN_FOND']                           /*selection*/ , '' /*optgroup*/ );
$select_bulletin_legende                        = HtmlForm::afficher_select(Form::$tab_select_legende      , 'f_bulletin_legende'                        /*select_nom*/ , FALSE /*option_first*/ , $_SESSION['OFFICIEL']['BULLETIN_LEGENDE']                        /*selection*/ , '' /*optgroup*/ );

$select_livret_appreciation_rubrique_longueur = HtmlForm::afficher_select(Form::$tab_select_appreciation , 'f_livret_appreciation_rubrique_longueur' /*select_nom*/ , FALSE /*option_first*/ , $_SESSION['OFFICIEL']['LIVRET_APPRECIATION_RUBRIQUE_LONGUEUR'] /*selection*/ , '' /*optgroup*/ );
$select_livret_appreciation_generale_longueur = HtmlForm::afficher_select(Form::$tab_select_appreciation , 'f_livret_appreciation_generale_longueur' /*select_nom*/ , FALSE /*option_first*/ , $_SESSION['OFFICIEL']['LIVRET_APPRECIATION_GENERALE_LONGUEUR'] /*selection*/ , '' /*optgroup*/ );
$select_livret_couleur                        = HtmlForm::afficher_select(Form::$tab_select_couleur      , 'f_livret_couleur'                        /*select_nom*/ , FALSE /*option_first*/ , $_SESSION['OFFICIEL']['LIVRET_COULEUR']                        /*selection*/ , '' /*optgroup*/ );
$select_livret_fond                           = HtmlForm::afficher_select(Form::$tab_select_fond         , 'f_livret_fond'                           /*select_nom*/ , FALSE /*option_first*/ , $_SESSION['OFFICIEL']['LIVRET_FOND']                           /*selection*/ , '' /*optgroup*/ );
$select_livret_import_bulletin_notes          = HtmlForm::afficher_select(Form::$tab_select_import_notes , 'f_livret_import_bulletin_notes'          /*select_nom*/ , FALSE /*option_first*/ , $_SESSION['OFFICIEL']['LIVRET_IMPORT_BULLETIN_NOTES']          /*selection*/ , '' /*optgroup*/ );

$check_releve_appreciation_rubrique_report =  $_SESSION['OFFICIEL']['RELEVE_APPRECIATION_RUBRIQUE_REPORT'] ? ' checked' : '' ;
$check_releve_appreciation_generale_report =  $_SESSION['OFFICIEL']['RELEVE_APPRECIATION_GENERALE_REPORT'] ? ' checked' : '' ;
$check_releve_ligne_supplementaire         =  $_SESSION['OFFICIEL']['RELEVE_LIGNE_SUPPLEMENTAIRE']         ? ' checked' : '' ;
$check_releve_assiduite                    =  $_SESSION['OFFICIEL']['RELEVE_ASSIDUITE']                    ? ' checked' : '' ;
$check_releve_prof_principal               =  $_SESSION['OFFICIEL']['RELEVE_PROF_PRINCIPAL']               ? ' checked' : '' ;
$check_releve_only_socle                   =  $_SESSION['OFFICIEL']['RELEVE_ONLY_SOCLE']                   ? ' checked' : '' ;
$check_releve_retroactif_auto              = ($_SESSION['OFFICIEL']['RELEVE_RETROACTIF']=='auto')          ? ' checked' : '' ;
$check_releve_retroactif_non               = ($_SESSION['OFFICIEL']['RELEVE_RETROACTIF']=='non')           ? ' checked' : '' ;
$check_releve_retroactif_oui               = ($_SESSION['OFFICIEL']['RELEVE_RETROACTIF']=='oui')           ? ' checked' : '' ;
$check_releve_retroactif_annuel            = ($_SESSION['OFFICIEL']['RELEVE_RETROACTIF']=='annuel')        ? ' checked' : '' ;
$check_releve_etat_acquisition             =  $_SESSION['OFFICIEL']['RELEVE_ETAT_ACQUISITION']             ? ' checked' : '' ;
$check_releve_moyenne_scores               =  $_SESSION['OFFICIEL']['RELEVE_MOYENNE_SCORES']               ? ' checked' : '' ;
$check_releve_pourcentage_acquis           =  $_SESSION['OFFICIEL']['RELEVE_POURCENTAGE_ACQUIS']           ? ' checked' : '' ;
$check_releve_conversion_sur_20            =  $_SESSION['OFFICIEL']['RELEVE_CONVERSION_SUR_20']            ? ' checked' : '' ;
$check_releve_aff_reference                =  $_SESSION['OFFICIEL']['RELEVE_AFF_REFERENCE']                ? ' checked' : '' ;
$check_releve_aff_coef                     =  $_SESSION['OFFICIEL']['RELEVE_AFF_COEF']                     ? ' checked' : '' ;
$check_releve_aff_socle                    =  $_SESSION['OFFICIEL']['RELEVE_AFF_SOCLE']                    ? ' checked' : '' ;
$check_releve_aff_domaine                  =  $_SESSION['OFFICIEL']['RELEVE_AFF_DOMAINE']                  ? ' checked' : '' ;
$check_releve_aff_theme                    =  $_SESSION['OFFICIEL']['RELEVE_AFF_THEME']                    ? ' checked' : '' ;

$check_bulletin_appreciation_rubrique_report =  $_SESSION['OFFICIEL']['BULLETIN_APPRECIATION_RUBRIQUE_REPORT'] ? ' checked' : '' ;
$check_bulletin_appreciation_generale_report =  $_SESSION['OFFICIEL']['BULLETIN_APPRECIATION_GENERALE_REPORT'] ? ' checked' : '' ;
$check_bulletin_ligne_supplementaire         =  $_SESSION['OFFICIEL']['BULLETIN_LIGNE_SUPPLEMENTAIRE']         ? ' checked' : '' ;
$check_bulletin_assiduite                    =  $_SESSION['OFFICIEL']['BULLETIN_ASSIDUITE']                    ? ' checked' : '' ;
$check_bulletin_prof_principal               =  $_SESSION['OFFICIEL']['BULLETIN_PROF_PRINCIPAL']               ? ' checked' : '' ;
$check_bulletin_retroactif_auto              = ($_SESSION['OFFICIEL']['BULLETIN_RETROACTIF']=='auto')          ? ' checked' : '' ;
$check_bulletin_retroactif_non               = ($_SESSION['OFFICIEL']['BULLETIN_RETROACTIF']=='non')           ? ' checked' : '' ;
$check_bulletin_retroactif_oui               = ($_SESSION['OFFICIEL']['BULLETIN_RETROACTIF']=='oui')           ? ' checked' : '' ;
$check_bulletin_retroactif_annuel            = ($_SESSION['OFFICIEL']['BULLETIN_RETROACTIF']=='annuel')        ? ' checked' : '' ;
$check_bulletin_only_socle                   =  $_SESSION['OFFICIEL']['BULLETIN_ONLY_SOCLE']                   ? ' checked' : '' ;
$check_bulletin_fusion_niveaux               =  $_SESSION['OFFICIEL']['BULLETIN_FUSION_NIVEAUX']               ? ' checked' : '' ;
$check_bulletin_barre_acquisitions           =  $_SESSION['OFFICIEL']['BULLETIN_BARRE_ACQUISITIONS']           ? ' checked' : '' ;
$check_bulletin_acquis_texte_nombre          =  $_SESSION['OFFICIEL']['BULLETIN_ACQUIS_TEXTE_NOMBRE']          ? ' checked' : '' ;
$check_bulletin_acquis_texte_code            =  $_SESSION['OFFICIEL']['BULLETIN_ACQUIS_TEXTE_CODE']            ? ' checked' : '' ;
$check_bulletin_moyenne_scores               =  $_SESSION['OFFICIEL']['BULLETIN_MOYENNE_SCORES']               ? ' checked' : '' ;
$check_bulletin_conversion_sur_20            =  $_SESSION['OFFICIEL']['BULLETIN_CONVERSION_SUR_20']            ? ' checked' : '' ;
$check_bulletin_pourcentage                  = !$_SESSION['OFFICIEL']['BULLETIN_CONVERSION_SUR_20']            ? ' checked' : '' ;
$check_bulletin_moyenne_classe               =  $_SESSION['OFFICIEL']['BULLETIN_MOYENNE_CLASSE']               ? ' checked' : '' ;
$check_bulletin_moyenne_generale             =  $_SESSION['OFFICIEL']['BULLETIN_MOYENNE_GENERALE']             ? ' checked' : '' ;

$check_livret_retroactif_auto              = ($_SESSION['OFFICIEL']['LIVRET_RETROACTIF']=='auto')          ? ' checked' : '' ;
$check_livret_retroactif_non               = ($_SESSION['OFFICIEL']['LIVRET_RETROACTIF']=='non')           ? ' checked' : '' ;
$check_livret_retroactif_oui               = ($_SESSION['OFFICIEL']['LIVRET_RETROACTIF']=='oui')           ? ' checked' : '' ;
$check_livret_retroactif_annuel            = ($_SESSION['OFFICIEL']['LIVRET_RETROACTIF']=='annuel')        ? ' checked' : '' ;
$check_livret_only_socle                   =  $_SESSION['OFFICIEL']['LIVRET_ONLY_SOCLE']                   ? ' checked' : '' ;

$class_span_releve_appreciation_rubrique_report   = $_SESSION['OFFICIEL']['RELEVE_APPRECIATION_RUBRIQUE_LONGUEUR']   ? 'show' : 'hide' ;
$class_span_releve_appreciation_rubrique_modele   = $_SESSION['OFFICIEL']['RELEVE_APPRECIATION_RUBRIQUE_REPORT']     ? 'show' : 'hide' ;
$class_span_releve_appreciation_generale_report   = $_SESSION['OFFICIEL']['RELEVE_APPRECIATION_GENERALE_LONGUEUR']   ? 'show' : 'hide' ;
$class_span_releve_appreciation_generale_modele   = $_SESSION['OFFICIEL']['RELEVE_APPRECIATION_GENERALE_REPORT']     ? 'show' : 'hide' ;
$class_span_bulletin_appreciation_rubrique_report = $_SESSION['OFFICIEL']['BULLETIN_APPRECIATION_RUBRIQUE_LONGUEUR'] ? 'show' : 'hide' ;
$class_span_bulletin_appreciation_rubrique_modele = $_SESSION['OFFICIEL']['BULLETIN_APPRECIATION_RUBRIQUE_REPORT']   ? 'show' : 'hide' ;
$class_span_bulletin_appreciation_generale_report = $_SESSION['OFFICIEL']['BULLETIN_APPRECIATION_GENERALE_LONGUEUR'] ? 'show' : 'hide' ;
$class_span_bulletin_appreciation_generale_modele = $_SESSION['OFFICIEL']['BULLETIN_APPRECIATION_GENERALE_REPORT']   ? 'show' : 'hide' ;

$class_input_releve_ligne_factice          = !$_SESSION['OFFICIEL']['RELEVE_LIGNE_SUPPLEMENTAIRE']   ? 'show' : 'hide' ;
$class_input_releve_ligne_supplementaire   =  $_SESSION['OFFICIEL']['RELEVE_LIGNE_SUPPLEMENTAIRE']   ? 'show' : 'hide' ;
$class_input_bulletin_ligne_factice        = !$_SESSION['OFFICIEL']['BULLETIN_LIGNE_SUPPLEMENTAIRE'] ? 'show' : 'hide' ;
$class_input_bulletin_ligne_supplementaire =  $_SESSION['OFFICIEL']['BULLETIN_LIGNE_SUPPLEMENTAIRE'] ? 'show' : 'hide' ;

$class_span_bulletin_moyennes              =  $_SESSION['OFFICIEL']['BULLETIN_MOYENNE_SCORES']                  ? 'show' : 'hide' ;
$class_span_bulletin_moyenne_generale      =  $_SESSION['OFFICIEL']['BULLETIN_APPRECIATION_GENERALE_LONGUEUR']  ? 'show' : 'hide' ;
$class_span_releve_etat_acquisition        = ($check_releve_etat_acquisition)                                   ? 'show' : 'hide' ;
$class_label_releve_conversion_sur_20      = ($check_releve_moyenne_scores || $check_releve_pourcentage_acquis) ? 'show' : 'hide' ;

if(!$_SESSION['OFFICIEL']['BULLETIN_MOYENNE_EXCEPTION_MATIERES'])
{
  $matiere_nombre = 'Sans exception (toutes matières avec moyennes)';
}
else
{
  $nombre = substr_count($_SESSION['OFFICIEL']['BULLETIN_MOYENNE_EXCEPTION_MATIERES'],',') + 1 ;
  $matiere_nombre = ($nombre==1) ? 'Une exception (matière sans moyenne)' : ' '.$nombre.' exceptions (matières sans moyennes)' ;
}
$matiere_liste = str_replace( ',' , '_' , $_SESSION['OFFICIEL']['BULLETIN_MOYENNE_EXCEPTION_MATIERES'] );

// Limitation LSUN : appréciation matière non vide et max 600
$tab_bad = array('value="0"'         ,'value="700"'         ,'value="800"'         ,'value="900"'         ,'value="999"'         );
$tab_bon = array('value="0" disabled','value="700" disabled','value="800" disabled','value="900" disabled','value="999" disabled');
$select_livret_appreciation_rubrique_longueur = str_replace( $tab_bad , $tab_bon , $select_livret_appreciation_rubrique_longueur );
// Limitation LSUN : appréciation synthèse non vide et max 1000
$tab_bad = array('value="0"'         );
$tab_bon = array('value="0" disabled');
$select_livret_appreciation_generale_longueur = str_replace( $tab_bad , $tab_bon , $select_livret_appreciation_generale_longueur );
?>

<div><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=releves_bilans__reglages_syntheses_bilans#toggle_officiel_configuration">DOC : Réglages synthèses &amp; bilans &rarr; Configuration des bilans officiels</a></span></div>

<hr />

<h2>Relevé d'évaluations</h2>

<form action="#" method="post" id="form_releve">
  <p>
    <label class="tab">Appr. matière :</label><?php echo $select_releve_appreciation_rubrique_longueur ?>
    <span id="span_releve_appreciation_rubrique_report" class="<?php echo $class_span_releve_appreciation_rubrique_report ?>">
      <label for="f_releve_appreciation_rubrique_report"><input type="checkbox" id="f_releve_appreciation_rubrique_report" name="f_releve_appreciation_rubrique_report" value="1"<?php echo $check_releve_appreciation_rubrique_report ?> /> à préremplir avec &hellip;</label>
      <span id="span_releve_appreciation_rubrique_modele" class="<?php echo $class_span_releve_appreciation_rubrique_modele ?>">
        <textarea id="f_releve_appreciation_rubrique_modele" name="f_releve_appreciation_rubrique_modele" rows="3" cols="50" maxlength="255"><?php echo html($_SESSION['OFFICIEL']['RELEVE_APPRECIATION_RUBRIQUE_MODELE']); ?></textarea>
      </span>
    </span><br />
    <label class="tab">Appr. générale :</label><?php echo $select_releve_appreciation_generale_longueur ?>
    <span id="span_releve_appreciation_generale_report" class="<?php echo $class_span_releve_appreciation_generale_report ?>">
      <label for="f_releve_appreciation_generale_report"><input type="checkbox" id="f_releve_appreciation_generale_report" name="f_releve_appreciation_generale_report" value="1"<?php echo $check_releve_appreciation_generale_report ?> /> à préremplir avec &hellip;</label>
      <span id="span_releve_appreciation_generale_modele" class="<?php echo $class_span_releve_appreciation_generale_modele ?>">
        <textarea id="f_releve_appreciation_generale_modele" name="f_releve_appreciation_generale_modele" rows="3" cols="50" maxlength="255"><?php echo html($_SESSION['OFFICIEL']['RELEVE_APPRECIATION_GENERALE_MODELE']); ?></textarea>
      </span>
    </span><br />
    <label class="tab">Ligne additionnelle :</label><input type="checkbox" id="f_releve_check_supplementaire" name="f_releve_check_supplementaire" value="1"<?php echo $check_releve_ligne_supplementaire ?> /> <input id="f_releve_ligne_factice" name="f_releve_ligne_factice" type="text" size="10" value="Sans objet." class="<?php echo $class_input_releve_ligne_factice ?>" disabled /><input id="f_releve_ligne_supplementaire" name="f_releve_ligne_supplementaire" type="text" size="120" maxlength="255" value="<?php echo html($_SESSION['OFFICIEL']['RELEVE_LIGNE_SUPPLEMENTAIRE']) ?>" class="<?php echo $class_input_releve_ligne_supplementaire ?>" /><br />
    <label class="tab">Assiduité :</label><label for="f_releve_assiduite"><input type="checkbox" id="f_releve_assiduite" name="f_releve_assiduite" value="1"<?php echo $check_releve_assiduite ?> /> Reporter le nombre d'absences et de retards</label><br />
    <label class="tab">Prof. Principal :</label><label for="f_releve_prof_principal"><input type="checkbox" id="f_releve_prof_principal" name="f_releve_prof_principal" value="1"<?php echo $check_releve_prof_principal ?> /> Indiquer le ou les professeurs principaux de la classe</label><br />
    <span class="radio">Prise en compte des évaluations antérieures :</span>
      <label for="f_releve_retroactif_auto"><input type="radio" id="f_releve_retroactif_auto" name="f_releve_retroactif" value="auto"<?php echo $check_releve_retroactif_auto ?> /> automatique (selon référentiels)</label>&nbsp;&nbsp;&nbsp;
      <label for="f_releve_retroactif_non"><input type="radio" id="f_releve_retroactif_non" name="f_releve_retroactif" value="non"<?php echo $check_releve_retroactif_non ?> /> non</label>&nbsp;&nbsp;&nbsp;
      <label for="f_releve_retroactif_oui"><input type="radio" id="f_releve_retroactif_oui" name="f_releve_retroactif" value="oui"<?php echo $check_releve_retroactif_oui ?> /> oui (sans limite)</label>&nbsp;&nbsp;&nbsp;
      <label for="f_releve_retroactif_annuel"><input type="radio" id="f_releve_retroactif_annuel" name="f_releve_retroactif" value="annuel"<?php echo $check_releve_retroactif_annuel ?> /> de l'année scolaire</label><br />
    <label class="tab">Restrictions :</label><?php echo $select_releve_only_etat ?><br />
    <span class="tab"></span><label for="f_releve_only_socle"><input type="checkbox" id="f_releve_only_socle" name="f_releve_only_socle" value="1"<?php echo $check_releve_only_socle ?> /> Uniquement les items liés au socle</label><br />
    <label class="tab">Indications :</label><?php echo $select_releve_cases_nb ?> d'évaluation&nbsp;&nbsp;&nbsp;<label for="f_releve_etat_acquisition"><input type="checkbox" id="f_releve_etat_acquisition" name="f_releve_etat_acquisition" value="1"<?php echo $check_releve_etat_acquisition ?> /> Colonne état d'acquisition</label><span id="span_releve_etat_acquisition" class="<?php echo $class_span_releve_etat_acquisition ?>">&nbsp;&nbsp;&nbsp;<label for="f_releve_moyenne_scores"><input type="checkbox" id="f_releve_moyenne_scores" name="f_releve_moyenne_scores" value="1"<?php echo $check_releve_moyenne_scores ?> /> Ligne moyenne des scores</label>&nbsp;&nbsp;&nbsp;<label for="f_releve_pourcentage_acquis"><input type="checkbox" id="f_releve_pourcentage_acquis" name="f_releve_pourcentage_acquis" value="1"<?php echo $check_releve_pourcentage_acquis ?> /> Ligne pourcentage d'items acquis</label>&nbsp;&nbsp;&nbsp;<label for="f_releve_conversion_sur_20" class="<?php echo $class_label_releve_conversion_sur_20 ?>"><input type="checkbox" id="f_releve_conversion_sur_20" name="f_releve_conversion_sur_20" value="1"<?php echo $check_releve_conversion_sur_20 ?> /> Conversion en note sur 20</label></span><br />
    <label class="tab">Infos items :</label><label for="f_releve_aff_reference"><input type="checkbox" id="f_releve_aff_reference" name="f_releve_aff_reference" value="1"<?php echo $check_releve_aff_reference ?> /> Références</label>&nbsp;&nbsp;&nbsp;<label for="f_releve_aff_coef"><input type="checkbox" id="f_releve_aff_coef" name="f_releve_aff_coef" value="1"<?php echo $check_releve_aff_coef ?> /> Coefficients</label>&nbsp;&nbsp;&nbsp;<label for="f_releve_aff_socle"><input type="checkbox" id="f_releve_aff_socle" name="f_releve_aff_socle" value="1"<?php echo $check_releve_aff_socle ?> /> Appartenance au socle</label>&nbsp;&nbsp;&nbsp;<label for="f_releve_aff_domaine"><input type="checkbox" id="f_releve_aff_domaine" name="f_releve_aff_domaine" value="1"<?php echo $check_releve_aff_domaine ?> /> Domaines</label>&nbsp;&nbsp;&nbsp;<label for="f_releve_aff_theme"><input type="checkbox" id="f_releve_aff_theme" name="f_releve_aff_theme" value="1"<?php echo $check_releve_aff_theme ?> /> Thèmes</label><br />
    <label class="tab">Impression :</label><?php echo $select_releve_couleur ?> <?php echo $select_releve_fond ?> <?php echo $select_releve_legende ?> <?php echo $select_releve_pages_nb ?>
  </p>
  <p>
    <span class="tab"></span><button id="bouton_valider_releve" type="button" class="parametre">Enregistrer.</button><label id="ajax_msg_releve">&nbsp;</label>
  </p>
</form>

<hr />

<h2>Bulletin scolaire</h2>

<form action="#" method="post" id="form_bulletin">
  <p>
    <label class="tab">Appr. matière :</label><?php echo $select_bulletin_appreciation_rubrique_longueur ?>
    <span id="span_bulletin_appreciation_rubrique_report" class="<?php echo $class_span_bulletin_appreciation_rubrique_report ?>">
      <label for="f_bulletin_appreciation_rubrique_report"><input type="checkbox" id="f_bulletin_appreciation_rubrique_report" name="f_bulletin_appreciation_rubrique_report" value="1"<?php echo $check_bulletin_appreciation_rubrique_report ?> /> à préremplir avec &hellip;</label>
      <span id="span_bulletin_appreciation_rubrique_modele" class="<?php echo $class_span_bulletin_appreciation_rubrique_modele ?>">
        <textarea id="f_bulletin_appreciation_rubrique_modele" name="f_bulletin_appreciation_rubrique_modele" rows="3" cols="50" maxlength="255"><?php echo html($_SESSION['OFFICIEL']['BULLETIN_APPRECIATION_RUBRIQUE_MODELE']); ?></textarea>
      </span>
    </span><br />
    <label class="tab">Appr. générale :</label><?php echo $select_bulletin_appreciation_generale_longueur ?>
    <span id="span_bulletin_appreciation_generale_report" class="<?php echo $class_span_bulletin_appreciation_generale_report ?>">
      <label for="f_bulletin_appreciation_generale_report"><input type="checkbox" id="f_bulletin_appreciation_generale_report" name="f_bulletin_appreciation_generale_report" value="1"<?php echo $check_bulletin_appreciation_generale_report ?> /> à préremplir avec &hellip;</label>
      <span id="span_bulletin_appreciation_generale_modele" class="<?php echo $class_span_bulletin_appreciation_generale_modele ?>">
        <textarea id="f_bulletin_appreciation_generale_modele" name="f_bulletin_appreciation_generale_modele" rows="3" cols="50" maxlength="255"><?php echo html($_SESSION['OFFICIEL']['BULLETIN_APPRECIATION_GENERALE_MODELE']); ?></textarea>
      </span>
    </span><br />
    <label class="tab">Ligne additionnelle :</label><input type="checkbox" id="f_bulletin_check_supplementaire" name="f_bulletin_check_supplementaire" value="1"<?php echo $check_bulletin_ligne_supplementaire ?> /> <input id="f_bulletin_ligne_factice" name="f_bulletin_ligne_factice" type="text" size="10" value="Sans objet." class="<?php echo $class_input_bulletin_ligne_factice ?>" disabled /><input id="f_bulletin_ligne_supplementaire" name="f_bulletin_ligne_supplementaire" type="text" size="120" maxlength="255" value="<?php echo html($_SESSION['OFFICIEL']['BULLETIN_LIGNE_SUPPLEMENTAIRE']) ?>" class="<?php echo $class_input_bulletin_ligne_supplementaire ?>" /><br />
    <label class="tab">Assiduité :</label><label for="f_bulletin_assiduite"><input type="checkbox" id="f_bulletin_assiduite" name="f_bulletin_assiduite" value="1"<?php echo $check_bulletin_assiduite ?> /> Reporter le nombre d'absences et de retards</label><br />
    <label class="tab">Prof. Principal :</label><label for="f_bulletin_prof_principal"><input type="checkbox" id="f_bulletin_prof_principal" name="f_bulletin_prof_principal" value="1"<?php echo $check_bulletin_prof_principal ?> /> Indiquer le ou les professeurs principaux de la classe</label><br />
    <span class="radio">Prise en compte des évaluations antérieures :</span>
      <label for="f_bulletin_retroactif_auto"><input type="radio" id="f_bulletin_retroactif_auto" name="f_bulletin_retroactif" value="auto"<?php echo $check_bulletin_retroactif_auto ?> /> automatique (selon référentiels)</label>&nbsp;&nbsp;&nbsp;
      <label for="f_bulletin_retroactif_non"><input type="radio" id="f_bulletin_retroactif_non" name="f_bulletin_retroactif" value="non"<?php echo $check_bulletin_retroactif_non ?> /> non</label>&nbsp;&nbsp;&nbsp;
      <label for="f_bulletin_retroactif_oui"><input type="radio" id="f_bulletin_retroactif_oui" name="f_bulletin_retroactif" value="oui"<?php echo $check_bulletin_retroactif_oui ?> /> oui</label>&nbsp;&nbsp;&nbsp;
      <label for="f_bulletin_retroactif_annuel"><input type="radio" id="f_bulletin_retroactif_annuel" name="f_bulletin_retroactif" value="annuel"<?php echo $check_bulletin_retroactif_annuel ?> /> de l'année scolaire</label><br />
    <label class="tab">Restriction :</label><label for="f_bulletin_only_socle"><input type="checkbox" id="f_bulletin_only_socle" name="f_bulletin_only_socle" value="1"<?php echo $check_bulletin_only_socle ?> /> Uniquement les items liés au socle</label><br />
    <label class="tab">Mode de synthèse :</label><label for="f_bulletin_fusion_niveaux"><input type="checkbox" id="f_bulletin_fusion_niveaux" name="f_bulletin_fusion_niveaux" value="1"<?php echo $check_bulletin_fusion_niveaux ?> /> Ne pas indiquer le niveau et fusionner les synthèses de même intitulé</label><br />
    <label class="tab">Acquisitions :</label><label for="f_bulletin_barre_acquisitions"><input type="checkbox" id="f_bulletin_barre_acquisitions" name="f_bulletin_barre_acquisitions" value="1"<?php echo $check_bulletin_barre_acquisitions ?> /> Barre avec le total des états acquisitions par matière</label>
    &nbsp;&nbsp;&nbsp;<label for="f_bulletin_acquis_texte_nombre"><input type="checkbox" id="f_bulletin_acquis_texte_nombre" name="f_bulletin_acquis_texte_nombre" value="1"<?php echo $check_bulletin_acquis_texte_nombre ?> /> Écrire le nombre d'items par catégorie</label>
    &nbsp;&nbsp;&nbsp;<label for="f_bulletin_acquis_texte_code"><input type="checkbox" id="f_bulletin_acquis_texte_code" name="f_bulletin_acquis_texte_code" value="1"<?php echo $check_bulletin_acquis_texte_code ?> /> Écrire la nature des catégories</label><br />
    <label class="tab">Moyennes :</label><label for="f_bulletin_moyenne_scores"><input type="checkbox" id="f_bulletin_moyenne_scores" name="f_bulletin_moyenne_scores" value="1"<?php echo $check_bulletin_moyenne_scores ?> /> Moyenne des scores</label>
    <span id="span_moyennes" class="<?php echo $class_span_bulletin_moyennes ?>">
      [ <label for="f_bulletin_conversion_sur_20"><input type="radio" id="f_bulletin_conversion_sur_20" name="f_bulletin_conversion_sur_20" value="1"<?php echo $check_bulletin_conversion_sur_20 ?> /> en note sur 20</label> | <label for="f_bulletin_pourcentage"><input type="radio" id="f_bulletin_pourcentage" name="f_bulletin_conversion_sur_20" value="0"<?php echo $check_bulletin_pourcentage ?> /> en pourcentage</label> ]&nbsp;&nbsp;&nbsp;
      <label for="f_bulletin_moyenne_classe"><input type="checkbox" id="f_bulletin_moyenne_classe" name="f_bulletin_moyenne_classe" value="1"<?php echo $check_bulletin_moyenne_classe ?> /> Moyenne de la classe</label>&nbsp;&nbsp;&nbsp;
      <span id="span_moyenne_generale" class="<?php echo $class_span_bulletin_moyenne_generale ?>">
        <label for="f_bulletin_moyenne_generale"><input type="checkbox" id="f_bulletin_moyenne_generale" name="f_bulletin_moyenne_generale" value="1"<?php echo $check_bulletin_moyenne_generale ?> /> Moyenne générale</label>&nbsp;&nbsp;&nbsp;
      </span><br />
      <span class="tab"></span><input id="f_matiere_nombre" name="f_matiere_nombre" size="40" type="text" value="<?php echo $matiere_nombre ?>" readonly /><input id="f_matiere_liste" name="f_matiere_liste" type="text" value="<?php echo $matiere_liste ?>" class="invisible" /><q class="choisir_compet" title="Voir ou choisir les matieres sans moyennes."></q>
    </span><br />
    <label class="tab">Impression :</label><?php echo $select_bulletin_couleur ?> <?php echo $select_bulletin_fond ?> <?php echo $select_bulletin_legende ?>
  </p>
  <p>
    <span class="tab"></span><button id="bouton_valider_bulletin" type="button" class="parametre">Enregistrer.</button><label id="ajax_msg_bulletin">&nbsp;</label>
  </p>
</form>

<hr />

<h2>Livret Scolaire</h2>

<form action="#" method="post" id="form_livret">
  <p>
    <label class="tab">Appr. matière :</label><?php echo $select_livret_appreciation_rubrique_longueur ?><br />
    <label class="tab">Appr. générale :</label><?php echo $select_livret_appreciation_generale_longueur ?><br />
    <label class="tab">Impression :</label><?php echo $select_livret_couleur ?> <?php echo $select_livret_fond ?>
    <h3>Si récupération possible depuis un bulletin scolaire</h3>
    <label class="tab">Positionnement :</label><?php echo $select_livret_import_bulletin_notes ?>
    <h3>Si récupération impossible depuis un bulletin scolaire</h3>
    <span class="radio">Prise en compte des évaluations antérieures :</span>
      <label for="f_livret_retroactif_auto"><input type="radio" id="f_livret_retroactif_auto" name="f_livret_retroactif" value="auto"<?php echo $check_livret_retroactif_auto ?> /> automatique (selon référentiels)</label>&nbsp;&nbsp;&nbsp;
      <label for="f_livret_retroactif_non"><input type="radio" id="f_livret_retroactif_non" name="f_livret_retroactif" value="non"<?php echo $check_livret_retroactif_non ?> /> non</label>&nbsp;&nbsp;&nbsp;
      <label for="f_livret_retroactif_oui"><input type="radio" id="f_livret_retroactif_oui" name="f_livret_retroactif" value="oui"<?php echo $check_livret_retroactif_oui ?> /> oui</label>&nbsp;&nbsp;&nbsp;
      <label for="f_livret_retroactif_annuel"><input type="radio" id="f_livret_retroactif_annuel" name="f_livret_retroactif" value="annuel"<?php echo $check_livret_retroactif_annuel ?> /> de l'année scolaire</label><br />
    <label class="tab">Restriction :</label><label for="f_livret_only_socle"><input type="checkbox" id="f_livret_only_socle" name="f_livret_only_socle" value="1"<?php echo $check_livret_only_socle ?> /> Uniquement les items liés au socle</label>
  </p>
  <p>
    <span class="tab"></span><button id="bouton_valider_livret" type="button" class="parametre">Enregistrer.</button><label id="ajax_msg_livret">&nbsp;</label>
  </p>
</form>

<hr />

<form action="#" method="post" id="zone_matieres" class="hide">
  <h3>Matieres sans moyennes</h3>
  <?php echo HtmlForm::afficher_checkbox_matieres() ?>
  <div style="clear:both"><button id="valider_matieres" type="button" class="valider">Valider la sélection</button>&nbsp;&nbsp;&nbsp;<button id="annuler_matieres" type="button" class="annuler">Annuler / Retour</button></div>
</form>
