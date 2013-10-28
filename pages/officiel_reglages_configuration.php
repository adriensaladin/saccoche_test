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
$TITRE = "Configuration des bilans officiels";

$select_releve_appreciation_rubrique   = Form::afficher_select(Form::$tab_select_appreciation , 'f_releve_appreciation_rubrique'   /*select_nom*/ , FALSE /*option_first*/ , $_SESSION['OFFICIEL']['RELEVE_APPRECIATION_RUBRIQUE']   /*selection*/ , '' /*optgroup*/);
$select_releve_appreciation_generale   = Form::afficher_select(Form::$tab_select_appreciation , 'f_releve_appreciation_generale'   /*select_nom*/ , FALSE /*option_first*/ , $_SESSION['OFFICIEL']['RELEVE_APPRECIATION_GENERALE']   /*selection*/ , '' /*optgroup*/);
$select_releve_cases_nb                = Form::afficher_select(Form::$tab_select_cases_nb     , 'f_releve_cases_nb'                /*select_nom*/ , FALSE /*option_first*/ , $_SESSION['OFFICIEL']['RELEVE_CASES_NB']                /*selection*/ , '' /*optgroup*/);
$select_releve_couleur                 = Form::afficher_select(Form::$tab_select_couleur      , 'f_releve_couleur'                 /*select_nom*/ , FALSE /*option_first*/ , $_SESSION['OFFICIEL']['RELEVE_COULEUR']                 /*selection*/ , '' /*optgroup*/);
$select_releve_legende                 = Form::afficher_select(Form::$tab_select_legende      , 'f_releve_legende'                 /*select_nom*/ , FALSE /*option_first*/ , $_SESSION['OFFICIEL']['RELEVE_LEGENDE']                 /*selection*/ , '' /*optgroup*/);

$select_bulletin_appreciation_rubrique = Form::afficher_select(Form::$tab_select_appreciation , 'f_bulletin_appreciation_rubrique' /*select_nom*/ , FALSE /*option_first*/ , $_SESSION['OFFICIEL']['BULLETIN_APPRECIATION_RUBRIQUE'] /*selection*/ , '' /*optgroup*/);
$select_bulletin_appreciation_generale = Form::afficher_select(Form::$tab_select_appreciation , 'f_bulletin_appreciation_generale' /*select_nom*/ , FALSE /*option_first*/ , $_SESSION['OFFICIEL']['BULLETIN_APPRECIATION_GENERALE'] /*selection*/ , '' /*optgroup*/);
$select_bulletin_couleur               = Form::afficher_select(Form::$tab_select_couleur      , 'f_bulletin_couleur'               /*select_nom*/ , FALSE /*option_first*/ , $_SESSION['OFFICIEL']['BULLETIN_COULEUR']               /*selection*/ , '' /*optgroup*/);
$select_bulletin_legende               = Form::afficher_select(Form::$tab_select_legende      , 'f_bulletin_legende'               /*select_nom*/ , FALSE /*option_first*/ , $_SESSION['OFFICIEL']['BULLETIN_LEGENDE']               /*selection*/ , '' /*optgroup*/);

$select_socle_appreciation_rubrique    = Form::afficher_select(Form::$tab_select_appreciation , 'f_socle_appreciation_rubrique'    /*select_nom*/ , FALSE /*option_first*/ , $_SESSION['OFFICIEL']['SOCLE_APPRECIATION_RUBRIQUE']    /*selection*/ , '' /*optgroup*/);
$select_socle_appreciation_generale    = Form::afficher_select(Form::$tab_select_appreciation , 'f_socle_appreciation_generale'    /*select_nom*/ , FALSE /*option_first*/ , $_SESSION['OFFICIEL']['SOCLE_APPRECIATION_GENERALE']    /*selection*/ , '' /*optgroup*/);
$select_socle_couleur                  = Form::afficher_select(Form::$tab_select_couleur      , 'f_socle_couleur'                  /*select_nom*/ , FALSE /*option_first*/ , $_SESSION['OFFICIEL']['SOCLE_COULEUR']                  /*selection*/ , '' /*optgroup*/);
$select_socle_legende                  = Form::afficher_select(Form::$tab_select_legende      , 'f_socle_legende'                  /*select_nom*/ , FALSE /*option_first*/ , $_SESSION['OFFICIEL']['SOCLE_LEGENDE']                  /*selection*/ , '' /*optgroup*/);

$check_releve_ligne_supplementaire   =  $_SESSION['OFFICIEL']['RELEVE_LIGNE_SUPPLEMENTAIRE']    ? ' checked' : '' ;
$check_releve_assiduite              =  $_SESSION['OFFICIEL']['RELEVE_ASSIDUITE']               ? ' checked' : '' ;
$check_releve_only_socle             =  $_SESSION['OFFICIEL']['RELEVE_ONLY_SOCLE']              ? ' checked' : '' ;
$check_releve_retroactif_auto        = ($_SESSION['OFFICIEL']['RELEVE_RETROACTIF']=='auto')     ? ' checked' : '' ;
$check_releve_retroactif_non         = ($_SESSION['OFFICIEL']['RELEVE_RETROACTIF']=='non')      ? ' checked' : '' ;
$check_releve_retroactif_oui         = ($_SESSION['OFFICIEL']['RELEVE_RETROACTIF']=='oui')      ? ' checked' : '' ;
$check_releve_etat_acquisition       =  $_SESSION['OFFICIEL']['RELEVE_ETAT_ACQUISITION']        ? ' checked' : '' ;
$check_releve_moyenne_scores         =  $_SESSION['OFFICIEL']['RELEVE_MOYENNE_SCORES']          ? ' checked' : '' ;
$check_releve_pourcentage_acquis     =  $_SESSION['OFFICIEL']['RELEVE_POURCENTAGE_ACQUIS']      ? ' checked' : '' ;
$check_releve_conversion_sur_20      =  $_SESSION['OFFICIEL']['RELEVE_CONVERSION_SUR_20']       ? ' checked' : '' ;
$check_releve_aff_coef               =  $_SESSION['OFFICIEL']['RELEVE_AFF_COEF']                ? ' checked' : '' ;
$check_releve_aff_socle              =  $_SESSION['OFFICIEL']['RELEVE_AFF_SOCLE']               ? ' checked' : '' ;
$check_releve_aff_domaine            =  $_SESSION['OFFICIEL']['RELEVE_AFF_DOMAINE']             ? ' checked' : '' ;
$check_releve_aff_theme              =  $_SESSION['OFFICIEL']['RELEVE_AFF_THEME']               ? ' checked' : '' ;

$check_bulletin_ligne_supplementaire =  $_SESSION['OFFICIEL']['BULLETIN_LIGNE_SUPPLEMENTAIRE']  ? ' checked' : '' ;
$check_bulletin_assiduite            =  $_SESSION['OFFICIEL']['BULLETIN_ASSIDUITE']             ? ' checked' : '' ;
$check_bulletin_retroactif_auto      = ($_SESSION['OFFICIEL']['BULLETIN_RETROACTIF']=='auto')   ? ' checked' : '' ;
$check_bulletin_retroactif_non       = ($_SESSION['OFFICIEL']['BULLETIN_RETROACTIF']=='non')    ? ' checked' : '' ;
$check_bulletin_retroactif_oui       = ($_SESSION['OFFICIEL']['BULLETIN_RETROACTIF']=='oui')    ? ' checked' : '' ;
$check_bulletin_only_socle           =  $_SESSION['OFFICIEL']['BULLETIN_ONLY_SOCLE']            ? ' checked' : '' ;
$check_bulletin_fusion_niveaux       =  $_SESSION['OFFICIEL']['BULLETIN_FUSION_NIVEAUX']        ? ' checked' : '' ;
$check_bulletin_barre_acquisitions   =  $_SESSION['OFFICIEL']['BULLETIN_BARRE_ACQUISITIONS']    ? ' checked' : '' ;
$check_bulletin_acquis_texte_nombre  =  $_SESSION['OFFICIEL']['BULLETIN_ACQUIS_TEXTE_NOMBRE']   ? ' checked' : '' ;
$check_bulletin_acquis_texte_code    =  $_SESSION['OFFICIEL']['BULLETIN_ACQUIS_TEXTE_CODE']     ? ' checked' : '' ;
$check_bulletin_moyenne_scores       =  $_SESSION['OFFICIEL']['BULLETIN_MOYENNE_SCORES']        ? ' checked' : '' ;
$check_bulletin_conversion_sur_20    =  $_SESSION['OFFICIEL']['BULLETIN_CONVERSION_SUR_20']     ? ' checked' : '' ;
$check_bulletin_pourcentage          = !$_SESSION['OFFICIEL']['BULLETIN_CONVERSION_SUR_20']     ? ' checked' : '' ;
$check_bulletin_moyenne_classe       =  $_SESSION['OFFICIEL']['BULLETIN_MOYENNE_CLASSE']        ? ' checked' : '' ;
$check_bulletin_moyenne_generale     =  $_SESSION['OFFICIEL']['BULLETIN_MOYENNE_GENERALE']      ? ' checked' : '' ;

$check_socle_ligne_supplementaire    =  $_SESSION['OFFICIEL']['SOCLE_LIGNE_SUPPLEMENTAIRE']     ? ' checked' : '' ;
$check_socle_assiduite               =  $_SESSION['OFFICIEL']['SOCLE_ASSIDUITE']                ? ' checked' : '' ;
$check_socle_only_presence           =  $_SESSION['OFFICIEL']['SOCLE_ONLY_PRESENCE']            ? ' checked' : '' ;
$check_socle_pourcentage_acquis      =  $_SESSION['OFFICIEL']['SOCLE_POURCENTAGE_ACQUIS']       ? ' checked' : '' ;
$check_socle_etat_validation         =  $_SESSION['OFFICIEL']['SOCLE_ETAT_VALIDATION']          ? ' checked' : '' ;

$class_input_releve_ligne_factice          = !$_SESSION['OFFICIEL']['RELEVE_LIGNE_SUPPLEMENTAIRE']              ? 'show' : 'hide' ;
$class_input_releve_ligne_supplementaire   =  $_SESSION['OFFICIEL']['RELEVE_LIGNE_SUPPLEMENTAIRE']              ? 'show' : 'hide' ;
$class_input_bulletin_ligne_factice        = !$_SESSION['OFFICIEL']['BULLETIN_LIGNE_SUPPLEMENTAIRE']            ? 'show' : 'hide' ;
$class_input_bulletin_ligne_supplementaire =  $_SESSION['OFFICIEL']['BULLETIN_LIGNE_SUPPLEMENTAIRE']            ? 'show' : 'hide' ;
$class_input_socle_ligne_factice           = !$_SESSION['OFFICIEL']['SOCLE_LIGNE_SUPPLEMENTAIRE']               ? 'show' : 'hide' ;
$class_input_socle_ligne_supplementaire    =  $_SESSION['OFFICIEL']['SOCLE_LIGNE_SUPPLEMENTAIRE']               ? 'show' : 'hide' ;
$class_span_bulletin_moyennes              =  $_SESSION['OFFICIEL']['BULLETIN_MOYENNE_SCORES']                  ? 'show' : 'hide' ;
$class_span_bulletin_moyenne_generale      =  $_SESSION['OFFICIEL']['BULLETIN_APPRECIATION_GENERALE']           ? 'show' : 'hide' ;
$class_span_releve_etat_acquisition        = ($check_releve_etat_acquisition)                                   ? 'show' : 'hide' ;
$class_label_releve_conversion_sur_20      = ($check_releve_moyenne_scores || $check_releve_pourcentage_acquis) ? 'show' : 'hide' ;

?>

<div><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=releves_bilans__reglages_syntheses_bilans#toggle_officiel_configuration">DOC : Réglages synthèses &amp; bilans &rarr; Configuration des bilans officiels</a></span></div>

<hr />

<h2>Relevé d'évaluations</h2>

<form action="#" method="post" id="form_releve">
  <p>
    <label class="tab">Appr. matière :</label><?php echo $select_releve_appreciation_rubrique ?><br />
    <label class="tab">Appr. générale :</label><?php echo $select_releve_appreciation_generale ?><br />
    <label class="tab">Ligne additionnelle :</label><input type="checkbox" id="f_releve_check_supplementaire" name="f_releve_check_supplementaire" value="1"<?php echo $check_releve_ligne_supplementaire ?> /> <input id="f_releve_ligne_factice" name="f_releve_ligne_factice" type="text" size="10" value="Sans objet." class="<?php echo $class_input_releve_ligne_factice ?>" disabled /><input id="f_releve_ligne_supplementaire" name="f_releve_ligne_supplementaire" type="text" size="120" maxlength="255" value="<?php echo html($_SESSION['OFFICIEL']['RELEVE_LIGNE_SUPPLEMENTAIRE']) ?>" class="<?php echo $class_input_releve_ligne_supplementaire ?>" /><br />
    <label class="tab">Assiduité :</label><label for="f_releve_assiduite"><input type="checkbox" id="f_releve_assiduite" name="f_releve_assiduite" value="1"<?php echo $check_releve_assiduite ?> /> Reporter le nombre d'absences et de retards</label><br />
    <span class="radio">Prise en compte des évaluations antérieures :</span>
      <label for="f_releve_retroactif_auto"><input type="radio" id="f_releve_retroactif_auto" name="f_releve_retroactif" value="auto"<?php echo $check_releve_retroactif_auto ?> /> automatique (selon référentiels)</label>&nbsp;&nbsp;&nbsp;
      <label for="f_releve_retroactif_non"><input type="radio" id="f_releve_retroactif_non" name="f_releve_retroactif" value="non"<?php echo $check_releve_retroactif_non ?> /> non</label>&nbsp;&nbsp;&nbsp;
      <label for="f_releve_retroactif_oui"><input type="radio" id="f_releve_retroactif_oui" name="f_releve_retroactif" value="oui"<?php echo $check_releve_retroactif_oui ?> /> oui</label><br />
    <label class="tab">Restriction :</label><label for="f_releve_only_socle"><input type="checkbox" id="f_releve_only_socle" name="f_releve_only_socle" value="1"<?php echo $check_releve_only_socle ?> /> Uniquement les items liés au socle</label><br />
    <label class="tab">Indications :</label><?php echo $select_releve_cases_nb ?> d'évaluation&nbsp;&nbsp;&nbsp;<label for="f_releve_etat_acquisition"><input type="checkbox" id="f_releve_etat_acquisition" name="f_releve_etat_acquisition" value="1"<?php echo $check_releve_etat_acquisition ?> /> Colonne état d'acquisition</label><span id="span_releve_etat_acquisition" class="<?php echo $class_span_releve_etat_acquisition ?>">&nbsp;&nbsp;&nbsp;<label for="f_releve_moyenne_scores"><input type="checkbox" id="f_releve_moyenne_scores" name="f_releve_moyenne_scores" value="1"<?php echo $check_releve_moyenne_scores ?> /> Ligne moyenne des scores</label>&nbsp;&nbsp;&nbsp;<label for="f_releve_pourcentage_acquis"><input type="checkbox" id="f_releve_pourcentage_acquis" name="f_releve_pourcentage_acquis" value="1"<?php echo $check_releve_pourcentage_acquis ?> /> Ligne pourcentage d'items acquis</label>&nbsp;&nbsp;&nbsp;<label for="f_releve_conversion_sur_20" class="<?php echo $class_label_releve_conversion_sur_20 ?>"><input type="checkbox" id="f_releve_conversion_sur_20" name="f_releve_conversion_sur_20" value="1"<?php echo $check_releve_conversion_sur_20 ?> /> Conversion en note sur 20</label></span><br />
    <label class="tab">Infos items :</label><label for="f_releve_aff_coef"><input type="checkbox" id="f_releve_aff_coef" name="f_releve_aff_coef" value="1"<?php echo $check_releve_aff_coef ?> /> Coefficients</label>&nbsp;&nbsp;&nbsp;<label for="f_releve_aff_socle"><input type="checkbox" id="f_releve_aff_socle" name="f_releve_aff_socle" value="1"<?php echo $check_releve_aff_socle ?> /> Appartenance au socle</label>&nbsp;&nbsp;&nbsp;<label for="f_releve_aff_domaine"><input type="checkbox" id="f_releve_aff_domaine" name="f_releve_aff_domaine" value="1"<?php echo $check_releve_aff_domaine ?> /> Domaines</label>&nbsp;&nbsp;&nbsp;<label for="f_releve_aff_theme"><input type="checkbox" id="f_releve_aff_theme" name="f_releve_aff_theme" value="1"<?php echo $check_releve_aff_theme ?> /> Thèmes</label><br />
    <label class="tab">Impression :</label><?php echo $select_releve_couleur ?> <?php echo $select_releve_legende ?>
  </p>
  <p>
    <span class="tab"></span><button id="bouton_valider_releve" type="button" class="parametre">Enregister.</button><label id="ajax_msg_releve">&nbsp;</label>
  </p>
</form>

<hr />

<h2>Bulletin scolaire</h2>

<form action="#" method="post" id="form_bulletin">
  <p>
    <label class="tab">Appr. matière :</label><?php echo $select_bulletin_appreciation_rubrique ?><br />
    <label class="tab">Appr. générale :</label><?php echo $select_bulletin_appreciation_generale ?><br />
    <label class="tab">Ligne additionnelle :</label><input type="checkbox" id="f_bulletin_check_supplementaire" name="f_bulletin_check_supplementaire" value="1"<?php echo $check_bulletin_ligne_supplementaire ?> /> <input id="f_bulletin_ligne_factice" name="f_bulletin_ligne_factice" type="text" size="10" value="Sans objet." class="<?php echo $class_input_bulletin_ligne_factice ?>" disabled /><input id="f_bulletin_ligne_supplementaire" name="f_bulletin_ligne_supplementaire" type="text" size="120" maxlength="255" value="<?php echo html($_SESSION['OFFICIEL']['BULLETIN_LIGNE_SUPPLEMENTAIRE']) ?>" class="<?php echo $class_input_bulletin_ligne_supplementaire ?>" /><br />
    <label class="tab">Assiduité :</label><label for="f_bulletin_assiduite"><input type="checkbox" id="f_bulletin_assiduite" name="f_bulletin_assiduite" value="1"<?php echo $check_bulletin_assiduite ?> /> Reporter le nombre d'absences et de retards</label><br />
    <span class="radio">Prise en compte des évaluations antérieures :</span>
      <label for="f_bulletin_retroactif_auto"><input type="radio" id="f_bulletin_retroactif_auto" name="f_bulletin_retroactif" value="auto"<?php echo $check_bulletin_retroactif_auto ?> /> automatique (selon référentiels)</label>&nbsp;&nbsp;&nbsp;
      <label for="f_bulletin_retroactif_non"><input type="radio" id="f_bulletin_retroactif_non" name="f_bulletin_retroactif" value="non"<?php echo $check_bulletin_retroactif_non ?> /> non</label>&nbsp;&nbsp;&nbsp;
      <label for="f_bulletin_retroactif_oui"><input type="radio" id="f_bulletin_retroactif_oui" name="f_bulletin_retroactif" value="oui"<?php echo $check_bulletin_retroactif_oui ?> /> oui</label><br />
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
        <label for="f_bulletin_moyenne_generale"><input type="checkbox" id="f_bulletin_moyenne_generale" name="f_bulletin_moyenne_generale" value="1"<?php echo $check_bulletin_moyenne_generale ?> /> Moyenne générale</label>
      </span>
    </span><br />
    <label class="tab">Impression :</label><?php echo $select_bulletin_couleur ?> <?php echo $select_bulletin_legende ?>
  </p>
  <p>
    <span class="tab"></span><button id="bouton_valider_bulletin" type="button" class="parametre">Enregister.</button><label id="ajax_msg_bulletin">&nbsp;</label>
  </p>
</form>

<hr />

<h2>État de maîtrise du socle</h2>

<form action="#" method="post" id="form_socle">
  <p>
    <label class="tab">Appr. compétence :</label><?php echo $select_socle_appreciation_rubrique ?><br />
    <label class="tab">Appr. générale :</label><?php echo $select_socle_appreciation_generale ?><br />
    <label class="tab">Ligne additionnelle :</label><input type="checkbox" id="f_socle_check_supplementaire" name="f_socle_check_supplementaire" value="1"<?php echo $check_socle_ligne_supplementaire ?> /> <input id="f_socle_ligne_factice" name="f_socle_ligne_factice" type="text" size="10" value="Sans objet." class="<?php echo $class_input_socle_ligne_factice ?>" disabled /><input id="f_socle_ligne_supplementaire" name="f_socle_ligne_supplementaire" type="text" size="120" maxlength="255" value="<?php echo html($_SESSION['OFFICIEL']['SOCLE_LIGNE_SUPPLEMENTAIRE']) ?>" class="<?php echo $class_input_socle_ligne_supplementaire ?>" /><br />
    <label class="tab">Assiduité :</label><label for="f_socle_assiduite"><input type="checkbox" id="f_socle_assiduite" name="f_socle_assiduite" value="1"<?php echo $check_socle_assiduite ?> /> Reporter le nombre d'absences et de retards</label><br />
    <label class="tab">Restriction :</label><label for="f_socle_only_presence"><input type="checkbox" id="f_socle_only_presence" name="f_socle_only_presence" value="1"<?php echo $check_socle_only_presence ?> /> Uniquement les éléments ayant fait l'objet d'une évaluation ou d'une validation</label><br />
    <label class="tab">Indications :</label><label for="f_socle_pourcentage_acquis"><input type="checkbox" id="f_socle_pourcentage_acquis" name="f_socle_pourcentage_acquis" value="1"<?php echo $check_socle_pourcentage_acquis ?> /> Pourcentage d'items acquis</label>&nbsp;&nbsp;&nbsp;<label for="f_socle_etat_validation"><input type="checkbox" id="f_socle_etat_validation" name="f_socle_etat_validation" value="1"<?php echo $check_socle_etat_validation ?> /> État de validation</label><br />
    <label class="tab">Impression :</label><?php echo $select_socle_couleur ?> <?php echo $select_socle_legende ?>
  </p>
  <p>
    <span class="tab"></span><button id="bouton_valider_socle" type="button" class="parametre">Enregister.</button><label id="ajax_msg_socle">&nbsp;</label>
  </p>
</form>

<hr />
