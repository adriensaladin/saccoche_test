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
$TITRE = html(Lang::_("Export de données"));

Form::load_choix_memo();
if($_SESSION['USER_PROFIL_TYPE']=='professeur')
{
  $tab_matieres = DB_STRUCTURE_COMMUN::DB_OPT_matieres_professeur($_SESSION['USER_ID']);
  $tab_groupes  = ($_SESSION['USER_JOIN_GROUPES']=='config') ? DB_STRUCTURE_COMMUN::DB_OPT_groupes_professeur($_SESSION['USER_ID']) : DB_STRUCTURE_COMMUN::DB_OPT_classes_groupes_etabl() ;
  $tab_paliers  = DB_STRUCTURE_COMMUN::DB_OPT_paliers_etabl();
  $of_p = (count($tab_paliers)<2) ? FALSE : '' ;
  // Javascript
  Layout::add( 'js_inline_before' , 'var date_mysql  = "'.TODAY_MYSQL.'";' );
  // Fabrication du tableau javascript "tab_groupe_periode" pour les jointures groupes/périodes
  HtmlForm::fabriquer_tab_js_jointure_groupe( $tab_groupes , TRUE /*tab_groupe_periode*/ , FALSE /*tab_groupe_niveau*/ );
}
if($_SESSION['USER_PROFIL_TYPE']=='directeur')
{
  $tab_matieres = DB_STRUCTURE_COMMUN::DB_OPT_matieres_etabl();
  $tab_groupes  = DB_STRUCTURE_COMMUN::DB_OPT_classes_groupes_etabl();
  $tab_paliers  = DB_STRUCTURE_COMMUN::DB_OPT_paliers_etabl();
  $of_p = (count($tab_paliers)<2) ? FALSE : '' ;
}
if($_SESSION['USER_PROFIL_TYPE']=='administrateur')
{
  $tab_matieres = array();
  $tab_groupes  = DB_STRUCTURE_COMMUN::DB_OPT_regroupements_etabl( FALSE /*sans*/ );
  $tab_paliers  = array();
  $of_p = FALSE;
}
$tab_cycles   = DB_STRUCTURE_COMMUN::DB_OPT_socle2016_cycles( FALSE /*only_used*/ );
$tab_periodes = DB_STRUCTURE_COMMUN::DB_OPT_periodes_etabl();

$select_matiere = HtmlForm::afficher_select($tab_matieres , 'f_matiere' /*select_nom*/ ,                      '' /*option_first*/ , Form::$tab_choix['matiere_id'] /*selection*/ ,              '' /*optgroup*/ );
$select_groupe  = HtmlForm::afficher_select($tab_groupes  , 'f_groupe'  /*select_nom*/ ,                      '' /*option_first*/ , FALSE                          /*selection*/ , 'regroupements' /*optgroup*/ );
$select_palier  = HtmlForm::afficher_select($tab_paliers  , 'f_palier'  /*select_nom*/ ,                   $of_p /*option_first*/ , Form::$tab_choix['palier_id']  /*selection*/ ,              '' /*optgroup*/ );
$select_cycle   = HtmlForm::afficher_select($tab_cycles   , 'f_cycle'   /*select_nom*/ ,                      '' /*option_first*/ , Form::$tab_choix['cycle_id']   /*selection*/ ,              '' /*optgroup*/ );
$select_periode = HtmlForm::afficher_select($tab_periodes , 'f_periode' /*select_nom*/ , 'periode_personnalisee' /*option_first*/ , FALSE                          /*selection*/ ,              '' /*optgroup*/ );


if($_SESSION['USER_PROFIL_TYPE']!='administrateur')
{
  $option_devoirs_commentaires = ($_SESSION['USER_PROFIL_TYPE']=='professeur') ? '<option value="devoirs_commentaires">liste de mes commentaires écrits aux évaluations</option>' : '' ;
  $select_type = $option_devoirs_commentaires
                .'<option value="listing_eleves">liste des élèves par classe / groupe</option>'
                .'<option value="listing_matiere">liste des items par matière</option>'
                .'<option value="item_matiere_usage">utilisation des items par matière</option>'
                .'<option value="arbre_matiere">arborescence des items par matière</option>'
                .'<option value="arbre_socle">arborescence des items du socle</option>'
                .'<option value="jointure_socle_matiere">liens socle &amp; matières</option>'
                .'<option value="jointure_socle2016_matiere">liens socle 2016 &amp; matières</option>';
}
else
{
  $select_type = '<option value="infos_eleves">informations élèves</option>'
                .'<option value="infos_parents">informations responsables légaux</option>'
                .'<option value="infos_professeurs">informations professeurs et personnels</option>';
}
?>

<div><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_professeur__export_listings">DOC : Export de données.</a></span></div>

<hr />

<form action="#" method="post" id="form_export"><fieldset>
  <p><label class="tab" for="f_type">Type de données :</label><select id="f_type" name="f_type"><option value="">&nbsp;</option><?php echo $select_type ?></select></p>
  <div id="div_groupe" class="hide"><label class="tab" for="f_groupe">Classe / groupe :</label><?php echo $select_groupe ?><input type="hidden" id="f_groupe_type" name="f_groupe_type" value="" /><input type="hidden" id="f_groupe_nom" name="f_groupe_nom" value="" /><input type="hidden" id="f_groupe_id" name="f_groupe_id" value="" /></div>
  <div id="div_periode" class="hide"><label class="tab" for="f_periode">Période :</label><?php echo $select_periode ?><input type="hidden" id="f_periode_nom" name="f_periode_nom" value="" />
    <span id="dates_perso" class="show">
      du <input id="f_date_debut" name="f_date_debut" size="9" type="text" value="<?php echo To::jour_debut_annee_scolaire('french') ?>" /><q class="date_calendrier" title="Cliquer sur cette image pour importer une date depuis un calendrier !"></q>
      au <input id="f_date_fin" name="f_date_fin" size="9" type="text" value="<?php echo TODAY_FR ?>" /><q class="date_calendrier" title="Cliquer sur cette image pour importer une date depuis un calendrier !"></q>
    </span>
  </div>
  <div id="div_matiere" class="hide"><label class="tab" for="f_matiere">Matière :</label><?php echo $select_matiere ?><input type="hidden" id="f_matiere_nom" name="f_matiere_nom" value="" /></div>
  <div id="div_palier" class="hide"><label class="tab" for="f_palier">Palier :</label><?php echo $select_palier ?><input type="hidden" id="f_palier_nom" name="f_palier_nom" value="" /></div>
  <div id="div_cycle" class="hide"><label class="tab" for="f_cycle">Cycle :</label><?php echo $select_cycle ?><input type="hidden" id="f_cycle_nom" name="f_cycle_nom" value="" /></div>
  <p id="p_submit" class="hide"><span class="tab"></span><button id="bouton_exporter" type="submit" class="fichier_export">Générer le listing de données</button><label id="ajax_msg">&nbsp;</label></p>
</fieldset></form>

