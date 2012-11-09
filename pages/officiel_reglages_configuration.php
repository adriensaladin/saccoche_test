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

$select_releve_appreciation_rubrique   = Form::afficher_select(Form::$tab_select_appreciation , $select_nom='f_releve_appreciation_rubrique'   , $option_first='non' , $selection=$_SESSION['OFFICIEL']['RELEVE_APPRECIATION_RUBRIQUE']   , $optgroup='non');
$select_releve_appreciation_generale   = Form::afficher_select(Form::$tab_select_appreciation , $select_nom='f_releve_appreciation_generale'   , $option_first='non' , $selection=$_SESSION['OFFICIEL']['RELEVE_APPRECIATION_GENERALE']   , $optgroup='non');
$select_releve_cases_nb                = Form::afficher_select(Form::$tab_select_cases_nb     , $select_nom='f_releve_cases_nb'                , $option_first='non' , $selection=$_SESSION['OFFICIEL']['RELEVE_CASES_NB']                , $optgroup='non');
$select_releve_couleur                 = Form::afficher_select(Form::$tab_select_couleur      , $select_nom='f_releve_couleur'                 , $option_first='non' , $selection=$_SESSION['OFFICIEL']['RELEVE_COULEUR']                 , $optgroup='non');
$select_releve_legende                 = Form::afficher_select(Form::$tab_select_legende      , $select_nom='f_releve_legende'                 , $option_first='non' , $selection=$_SESSION['OFFICIEL']['RELEVE_LEGENDE']                 , $optgroup='non');

$select_bulletin_appreciation_rubrique = Form::afficher_select(Form::$tab_select_appreciation , $select_nom='f_bulletin_appreciation_rubrique' , $option_first='non' , $selection=$_SESSION['OFFICIEL']['BULLETIN_APPRECIATION_RUBRIQUE'] , $optgroup='non');
$select_bulletin_appreciation_generale = Form::afficher_select(Form::$tab_select_appreciation , $select_nom='f_bulletin_appreciation_generale' , $option_first='non' , $selection=$_SESSION['OFFICIEL']['BULLETIN_APPRECIATION_GENERALE'] , $optgroup='non');
$select_bulletin_couleur               = Form::afficher_select(Form::$tab_select_couleur      , $select_nom='f_bulletin_couleur'               , $option_first='non' , $selection=$_SESSION['OFFICIEL']['BULLETIN_COULEUR']               , $optgroup='non');
$select_bulletin_legende               = Form::afficher_select(Form::$tab_select_legende      , $select_nom='f_bulletin_legende'               , $option_first='non' , $selection=$_SESSION['OFFICIEL']['BULLETIN_LEGENDE']               , $optgroup='non');

$select_socle_appreciation_rubrique    = Form::afficher_select(Form::$tab_select_appreciation , $select_nom='f_socle_appreciation_rubrique'    , $option_first='non' , $selection=$_SESSION['OFFICIEL']['SOCLE_APPRECIATION_RUBRIQUE']    , $optgroup='non');
$select_socle_appreciation_generale    = Form::afficher_select(Form::$tab_select_appreciation , $select_nom='f_socle_appreciation_generale'    , $option_first='non' , $selection=$_SESSION['OFFICIEL']['SOCLE_APPRECIATION_GENERALE']    , $optgroup='non');
$select_socle_couleur                  = Form::afficher_select(Form::$tab_select_couleur      , $select_nom='f_socle_couleur'                  , $option_first='non' , $selection=$_SESSION['OFFICIEL']['SOCLE_COULEUR']                  , $optgroup='non');
$select_socle_legende                  = Form::afficher_select(Form::$tab_select_legende      , $select_nom='f_socle_legende'                  , $option_first='non' , $selection=$_SESSION['OFFICIEL']['SOCLE_LEGENDE']                  , $optgroup='non');

$check_releve_moyenne_scores     = ($_SESSION['OFFICIEL']['RELEVE_MOYENNE_SCORES'])          ? ' checked' : '' ;
$check_releve_pourcentage_acquis = ($_SESSION['OFFICIEL']['RELEVE_POURCENTAGE_ACQUIS'])      ? ' checked' : '' ;
$check_releve_aff_coef           = ($_SESSION['OFFICIEL']['RELEVE_AFF_COEF'])                ? ' checked' : '' ;
$check_releve_aff_socle          = ($_SESSION['OFFICIEL']['RELEVE_AFF_SOCLE'])               ? ' checked' : '' ;
$check_releve_aff_domaine        = ($_SESSION['OFFICIEL']['RELEVE_AFF_DOMAINE'])             ? ' checked' : '' ;
$check_releve_aff_theme          = ($_SESSION['OFFICIEL']['RELEVE_AFF_THEME'])               ? ' checked' : '' ;

$check_bulletin_moyenne_scores   = ($_SESSION['OFFICIEL']['BULLETIN_MOYENNE_SCORES'])        ? ' checked' : '' ;
$check_bulletin_note_sur_20      = ($_SESSION['OFFICIEL']['BULLETIN_NOTE_SUR_20'])           ? ' checked' : '' ;
$check_bulletin_pourcentage      = (!$_SESSION['OFFICIEL']['BULLETIN_NOTE_SUR_20'])          ? ' checked' : '' ;
$check_bulletin_moyenne_classe   = ($_SESSION['OFFICIEL']['BULLETIN_MOYENNE_CLASSE'])        ? ' checked' : '' ;
$check_bulletin_moyenne_generale = ($_SESSION['OFFICIEL']['BULLETIN_MOYENNE_GENERALE'])      ? ' checked' : '' ;

$check_socle_only_presence       = ($_SESSION['OFFICIEL']['SOCLE_ONLY_PRESENCE'])            ? ' checked' : '' ;
$check_socle_pourcentage_acquis  = ($_SESSION['OFFICIEL']['SOCLE_POURCENTAGE_ACQUIS'])       ? ' checked' : '' ;
$check_socle_etat_validation     = ($_SESSION['OFFICIEL']['SOCLE_ETAT_VALIDATION'])          ? ' checked' : '' ;

$class_span_moyennes             = ($_SESSION['OFFICIEL']['BULLETIN_MOYENNE_SCORES'])        ? 'show'     : 'hide' ;
$class_span_moyenne_generale     = ($_SESSION['OFFICIEL']['BULLETIN_APPRECIATION_GENERALE']) ? 'show'     : 'hide' ;

?>

<div><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=releves_bilans__reglages_syntheses_bilans#toggle_officiel_configuration">DOC : Réglages synthèses &amp; bilans &rarr; Configuration des bilans officiels</a></span></div>

<hr />

<h2>Relevé d'évaluations</h2>

<form action="#" method="post" id="form_releve">
	<p>
		<label class="tab">Appr. matière :</label><?php echo $select_releve_appreciation_rubrique ?><br />
		<label class="tab">Appr. générale :</label><?php echo $select_releve_appreciation_generale ?><br />
		<label class="tab">Lignes en option :</label><label for="f_releve_moyenne_scores"><input type="checkbox" id="f_releve_moyenne_scores" name="f_releve_moyenne_scores" value="1"<?php echo $check_releve_moyenne_scores ?> /> Moyenne des scores</label>&nbsp;&nbsp;&nbsp;<label for="f_releve_pourcentage_acquis"><input type="checkbox" id="f_releve_pourcentage_acquis" name="f_releve_pourcentage_acquis" value="1"<?php echo $check_releve_pourcentage_acquis ?> /> Pourcentage d'items acquis</label><br />
		<label class="tab">Indications :</label><?php echo $select_releve_cases_nb ?>&nbsp;&nbsp;&nbsp;<label for="f_releve_aff_coef"><input type="checkbox" id="f_releve_aff_coef" name="f_releve_aff_coef" value="1"<?php echo $check_releve_aff_coef ?> /> Coefficients</label>&nbsp;&nbsp;&nbsp;<label for="f_releve_aff_socle"><input type="checkbox" id="f_releve_aff_socle" name="f_releve_aff_socle" value="1"<?php echo $check_releve_aff_socle ?> /> Appartenance au socle</label>&nbsp;&nbsp;&nbsp;<label for="f_releve_aff_domaine"><input type="checkbox" id="f_releve_aff_domaine" name="f_releve_aff_domaine" value="1"<?php echo $check_releve_aff_domaine ?> /> Domaines</label>&nbsp;&nbsp;&nbsp;<label for="f_releve_aff_theme"><input type="checkbox" id="f_releve_aff_theme" name="f_releve_aff_theme" value="1"<?php echo $check_releve_aff_theme ?> /> Thèmes</label><br />
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
		<label class="tab">Indications :</label>
		<label for="f_bulletin_moyenne_scores"><input type="checkbox" id="f_bulletin_moyenne_scores" name="f_bulletin_moyenne_scores" value="1"<?php echo $check_bulletin_moyenne_scores ?> /> Moyenne des scores</label>
		<span id="span_moyennes" class="<?php echo $class_span_moyennes ?>">
			[ <label for="f_bulletin_note_sur_20"><input type="radio" id="f_bulletin_note_sur_20" name="f_bulletin_note_sur_20" value="1"<?php echo $check_bulletin_note_sur_20 ?> /> en note sur 20</label> | <label for="f_bulletin_pourcentage"><input type="radio" id="f_bulletin_pourcentage" name="f_bulletin_note_sur_20" value="0"<?php echo $check_bulletin_pourcentage ?> /> en pourcentage</label> ]&nbsp;&nbsp;&nbsp;
			<label for="f_bulletin_moyenne_classe"><input type="checkbox" id="f_bulletin_moyenne_classe" name="f_bulletin_moyenne_classe" value="1"<?php echo $check_bulletin_moyenne_classe ?> /> Moyenne de la classe</label>&nbsp;&nbsp;&nbsp;
			<span id="span_moyenne_generale" class="<?php echo $class_span_moyenne_generale ?>">
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
		<label class="tab">Restriction :</label><label for="f_socle_only_presence"><input type="checkbox" id="f_socle_only_presence" name="f_socle_only_presence" value="1"<?php echo $check_socle_only_presence ?> /> Uniquement les éléments ayant fait l'objet d'une évaluation ou d'une validation</label><br />
		<label class="tab">Indications :</label><label for="f_socle_pourcentage_acquis"><input type="checkbox" id="f_socle_pourcentage_acquis" name="f_socle_pourcentage_acquis" value="1"<?php echo $check_socle_pourcentage_acquis ?> /> Pourcentage d'items acquis</label>&nbsp;&nbsp;&nbsp;<label for="f_socle_etat_validation"><input type="checkbox" id="f_socle_etat_validation" name="f_socle_etat_validation" value="1"<?php echo $check_socle_etat_validation ?> /> État de validation</label><br />
		<label class="tab">Impression :</label><?php echo $select_socle_couleur ?> <?php echo $select_socle_legende ?>
	</p>
	<p>
		<span class="tab"></span><button id="bouton_valider_socle" type="button" class="parametre">Enregister.</button><label id="ajax_msg_socle">&nbsp;</label>
	</p>
</form>

<hr />
