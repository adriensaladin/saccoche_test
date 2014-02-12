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
$TITRE = "Demandes d'évaluations formulées";

// Lister le nb de demandes d'évaluations autorisées suivant les matières
$infobulle = '';
$DB_TAB = DB_STRUCTURE_COMMUN::DB_OPT_matieres_professeur($_SESSION['USER_ID']);
if(!is_array($DB_TAB))
{
  $infobulle .= $DB_TAB;
}
else
{
  foreach($DB_TAB as $key => $DB_ROW)
  {
    $infobulle .= $DB_ROW['texte'].' : '.$DB_ROW['info'].'<br />';
  }
}

// Dates par défaut
$date_autoeval = date('d/m/Y',mktime(0,0,0,date('m'),date('d')+7,date('Y'))); // 1 semaine après

// Javascript
$GLOBALS['HEAD']['js']['inline'][] = 'var input_date     = "'.TODAY_FR.'";';
$GLOBALS['HEAD']['js']['inline'][] = 'var input_autoeval = "'.$date_autoeval.'";';
?>

<ul class="puce">
  <li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=environnement_generalites__demandes_evaluations">DOC : Demandes d'évaluations.</a></span></li>
  <li><span class="astuce">Tenez-vous au courant des demandes grace à <a class="lien_ext" href="<?php echo RSS::url_prof($_SESSION['USER_ID']); ?>"><span class="rss">un flux RSS dédié</span></a> !</span></li>
  <li><span class="astuce"><a title="<?php echo $infobulle ?>" href="#">Nombre de demandes autorisées par matière.</a></span></li>
</ul>

<hr />

<?php
// Fabrication des éléments select du formulaire

$tab_matieres   = DB_STRUCTURE_COMMUN::DB_OPT_matieres_professeur($_SESSION['USER_ID']) ;
$tab_groupes    = ($_SESSION['USER_JOIN_GROUPES']=='config') ? DB_STRUCTURE_COMMUN::DB_OPT_groupes_professeur($_SESSION['USER_ID']) : DB_STRUCTURE_COMMUN::DB_OPT_classes_groupes_etabl() ;
$select_matiere = Form::afficher_select($tab_matieres , 'f_matiere' /*select_nom*/ ,    'toutes_matieres' /*option_first*/ , FALSE /*selection*/ ,              '' /*optgroup*/);
$select_groupe  = Form::afficher_select($tab_groupes  , 'f_groupe'  /*select_nom*/ , 'tous_regroupements' /*option_first*/ , FALSE /*selection*/ , 'regroupements' /*optgroup*/);
?>

<form action="#" method="post" id="form_prechoix"><fieldset>
  <label class="tab" for="f_matiere">Matière :</label><?php echo $select_matiere ?><input type="hidden" id="f_matiere_nom" name="f_matiere_nom" value="" /><br />
  <label class="tab" for="f_groupe">Classe / groupe :</label><?php echo $select_groupe ?><input type="hidden" id="f_groupe_id" name="f_groupe_id" value="" /><input type="hidden" id="f_groupe_type" name="f_groupe_type" value="" /><input type="hidden" id="f_groupe_nom" name="f_groupe_nom" value="" /><br />
  <span class="tab"></span><input type="hidden" name="f_action" value="Afficher_demandes" /><button id="actualiser" type="submit" class="actualiser">Actualiser l'affichage.</button><label id="ajax_msg_prechoix">&nbsp;</label>
</fieldset></form>

<form action="#" method="post" id="form_gestion" class="hide">
  <hr />
  <table class="bilan_synthese" style="float:right;margin-left:1em;margin-right:1ex">
    <thead><tr><th>élève(s) sans demande</th></tr></thead>
    <tbody><tr id="tr_sans"><td class="nu"></td></tr></tbody>
  </table>
  <table id="table_action" class="form hsort t9">
    <thead>
      <tr>
        <th class="nu"><q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q></th>
        <th>Matière</th>
        <th>Item</th>
        <th>Popularité</th>
        <th>Classe / Groupe</th>
        <th>Élève</th>
        <th>Score</th>
        <th>Date</th>
        <th>Statut</th>
        <th>Messages</th>
      </tr>
    </thead>
    <tbody>
      <tr><td class="nu" colspan="10"></td></tr>
    </tbody>
  </table>
  <hr />
  <ul class="puce">
    <li><a id="voir_messages" href="#"><span class="file file_htm">Voir tous les messages à la fois.</span></a></li>
    <li><a id="export_fichier" href=""><span class="file file_txt">Récupérer / Manipuler les informations (fichier <em>csv</em> pour tableur).</span></a></li>
  </ul>
  <hr />
  <div id="zone_actions" class="hide">
    <h2>Avec les demandes cochées :<input type="hidden" id="ids" name="ids" value="" /></h2>
    <fieldset>
      <label class="tab" for="f_quoi">Action :</label><select id="f_quoi" name="f_quoi"><option value=""></option><option value="creer">Créer une nouvelle évaluation.</option><option value="completer">Compléter une évaluation existante.</option><option value="changer">Changer le statut pour "évaluation en préparation".</option><option value="retirer">Retirer de la liste des demandes.</option></select>
    </fieldset>
    <fieldset id="step_qui" class="hide">
      <label class="tab" for="f_qui">Élève(s) :</label><select id="f_qui" name="f_qui"><option value="select">Élèves sélectionnés</option><option value="groupe"></option></select>
    </fieldset>
    <fieldset id="step_creer" class="hide">
      <label class="tab" for="f_date">Date devoir :</label><input id="f_date" name="f_date" size="8" type="text" value="<?php echo TODAY_FR ?>" /><q class="date_calendrier" title="Cliquer sur cette image pour importer une date depuis un calendrier !"></q><br />
      <label class="tab" for="f_date_visible">Date visible :</label><input id="box_date" type="checkbox" checked /> <span>identique</span><span class="hide"><input id="f_date_visible" name="f_date_visible" size="8" type="text" value="<?php echo TODAY_FR ?>" /><q class="date_calendrier" title="Cliquer sur cette image pour importer une date depuis un calendrier !"></q></span><br />
      <label class="tab" for="f_date_autoeval">Fin auto-éval. :</label><input id="box_autoeval" type="checkbox" checked /> <span>sans objet</span><span class="hide"><input id="f_date_autoeval" name="f_date_autoeval" size="8" type="text" value="00/00/0000" /><q class="date_calendrier" title="Cliquer sur cette image pour importer une date depuis un calendrier !"></q></span><br />
      <label class="tab" for="f_info">Description :</label><input id="f_info" name="f_info" size="30" type="text" value="" />
    </fieldset>
    <fieldset id="step_completer" class="hide">
      <label class="tab" for="f_devoir">Évaluation :</label><select id="f_devoir" name="f_devoir"><option></option></select><label id="ajax_maj1">&nbsp;</label>
    </fieldset>
    <fieldset id="step_suite" class="hide">
      <label class="tab" for="f_suite">Suite :</label><select id="f_suite" name="f_suite"><option value="changer">Changer ensuite le statut pour "évaluation en préparation".</option><option value="retirer">Retirer ensuite de la liste des demandes.</option></select>
    </fieldset>
    <fieldset id="step_message" class="hide">
      <label class="tab" for="f_message">Message <img alt="" src="./_img/bulle_aide.png" title="facultatif" /> :</label><textarea id="f_message" name="f_message" rows="3" cols="75"></textarea><br />
      <span class="tab"></span><label id="f_message_reste"></label>
    </fieldset>
    <p id="step_valider" class="hide">
      <input type="hidden" id="f_groupe_id2" name="f_groupe_id" value="" /><input type="hidden" id="f_groupe_type2" name="f_groupe_type" value="" />
      <span class="tab"></span><button id="bouton_valider" type="submit" class="valider">Valider.</button><label id="ajax_msg_gestion">&nbsp;</label>
    </p>
  </div>
</form>
<div id="zone_messages" class="hide"></div>
