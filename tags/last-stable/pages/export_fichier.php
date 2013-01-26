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
$TITRE = "Export de données";

Form::load_choix_memo();
if($_SESSION['USER_PROFIL_TYPE']=='professeur')
{
  $tab_matieres = DB_STRUCTURE_COMMUN::DB_OPT_matieres_professeur($_SESSION['USER_ID']);
  $tab_groupes  = ($_SESSION['USER_JOIN_GROUPES']=='config') ? DB_STRUCTURE_COMMUN::DB_OPT_groupes_professeur($_SESSION['USER_ID']) : DB_STRUCTURE_COMMUN::DB_OPT_classes_groupes_etabl() ;
  $tab_paliers  = DB_STRUCTURE_COMMUN::DB_OPT_paliers_etabl();
  $of_p = (count($tab_paliers)<2) ? 'non' : 'oui' ;
}
if($_SESSION['USER_PROFIL_TYPE']=='directeur')
{
  $tab_matieres = DB_STRUCTURE_COMMUN::DB_OPT_matieres_etabl();
  $tab_groupes  = DB_STRUCTURE_COMMUN::DB_OPT_classes_groupes_etabl();
  $tab_paliers  = DB_STRUCTURE_COMMUN::DB_OPT_paliers_etabl();
  $of_p = (count($tab_paliers)<2) ? 'non' : 'oui' ;
}
if($_SESSION['USER_PROFIL_TYPE']=='administrateur')
{
  $tab_matieres = array();
  $tab_groupes  = DB_STRUCTURE_COMMUN::DB_OPT_regroupements_etabl(FALSE/*sans*/);
  $tab_paliers  = array();
  $of_p = 'non';
}

$select_matiere = Form::afficher_select($tab_matieres , $select_nom='f_matiere' , $option_first='oui' , $selection=Form::$tab_choix['matiere_id'] , $optgroup='non');
$select_groupe  = Form::afficher_select($tab_groupes  , $select_nom='f_groupe'  , $option_first='oui' , $selection=FALSE                          , $optgroup='oui');
$select_palier  = Form::afficher_select($tab_paliers  , $select_nom='f_palier'  , $option_first=$of_p , $selection=Form::$tab_choix['palier_id']  , $optgroup='non');

$select_type = ($_SESSION['USER_PROFIL_TYPE']!='administrateur')
             ? '<option value="listing_eleves">listes des élèves par classe</option><option value="listing_matiere">listes des items par matière</option><option value="arbre_matiere">arborescence des items par matière</option><option value="arbre_socle">arborescence des items du socle</option><option value="jointure_socle_matiere">liens socle &amp; matières</option>'
             : '<option value="infos_eleves">informations élèves</option><option value="infos_parents">informations responsables légaux</option><option value="infos_professeurs">informations professeurs et personnels</option>'
             ;
?>

<div><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_professeur__export_listings">DOC : Export de données.</a></span></div>

<hr />

<form action="#" method="post" id="form_export"><fieldset>
  <p><label class="tab" for="f_type">Type de données :</label><select id="f_type" name="f_type"><option value=""></option><?php echo $select_type ?></select></p>
  <div id="div_groupe" class="hide"><label class="tab" for="f_groupe">Classe / groupe :</label><?php echo $select_groupe ?><input type="hidden" id="f_groupe_type" name="f_groupe_type" value="" /><input type="hidden" id="f_groupe_nom" name="f_groupe_nom" value="" /><input type="hidden" id="f_groupe_id" name="f_groupe_id" value="" /></div>
  <div id="div_matiere" class="hide"><label class="tab" for="f_matiere">Matière :</label><?php echo $select_matiere ?><input type="hidden" id="f_matiere_nom" name="f_matiere_nom" value="" /></div>
  <div id="div_palier" class="hide"><label class="tab" for="f_palier">Palier :</label><?php echo $select_palier ?><input type="hidden" id="f_palier_nom" name="f_palier_nom" value="" /></div>
  <p id="p_submit" class="hide"><span class="tab"></span><button id="bouton_exporter" type="submit" class="fichier_export">Générer le listing de données</button><label id="ajax_msg">&nbsp;</label></p>
</fieldset></form>

