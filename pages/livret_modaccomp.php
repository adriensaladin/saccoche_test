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
$TITRE = html(Lang::_("Livret Scolaire")).' &rarr; '.html(Lang::_("Modalités d'accompagnement"));

// Fabrication des éléments select du formulaire
$tab_groupes   = DB_STRUCTURE_COMMUN::DB_OPT_classes_groupes_etabl();
$tab_modaccomp = DB_STRUCTURE_LIVRET::DB_OPT_modaccomp();

$select_eleve     = HtmlForm::afficher_select($tab_groupes   , 'select_groupe' /*select_nom*/ , '' /*option_first*/ , FALSE /*selection*/ , 'regroupements' /*optgroup*/ );
$select_modaccomp = HtmlForm::afficher_select($tab_modaccomp , 'f_modaccomp'   /*select_nom*/ , '' /*option_first*/ , FALSE /*selection*/ ,              '' /*optgroup*/ );
?>

<ul class="puce">
  <li>Selon les spécifications qui nous ont été transmises, les <b>modalités d'accompagnement</b> dont bénéficient les élèves sont à renseigner pour le livret scolaire.</li>
  <li>Dans sa forme, nous ne savons pas encore où cela y apparaitra.</li>
  <li>Documents et informations ministérielles : <span class="manuel"><a class="pop_up" href="http://eduscol.education.fr/cid99430/l-accompagnement-personnalise-rentree-2016.html">PAI - PPS - PAP - PPRE</a></span> - <span class="manuel"><a class="pop_up" href="http://eduscol.education.fr/cid86144/plan-d-accompagnement-personnalise.html">PAP</a></span> - <span class="manuel"><a class="pop_up" href="http://eduscol.education.fr/pid23273/programme-personnalise-de-reussite-educative.html">PPRE</a></span> - <span class="manuel"><a class="pop_up" href="http://eduscol.education.fr/cid46765/sections-d-enseignement-general-et-professionnel-adapte.html">SEGPA</a></span> - <span class="manuel"><a class="pop_up" href="http://eduscol.education.fr/cid53163/les-unites-localisees-pour-l-inclusion-scolaire-ulis.html">ULIS</a></span> - <span class="manuel"><a class="pop_up" href="http://eduscol.education.fr/pid28783/scolariser-les-eleves-allophones-et-les-enfants-des-familles-itinerantes.html">UPE2A</a></span></li>
  <li><span class="astuce">Concernant les <b>PPRE</b>, un complément d'information de leur contenu est attendu.</span></li>
</ul>

<hr />

<form action="#" method="post" id="form_select">
  <table><tr>
    <td class="nu" style="width:25em">
      <b>Élèves :</b><span class="check_multiple"><q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q><q class="cocher_inverse" title="Tout échanger."></q></span><br />
      <?php echo $select_eleve ?><br />
      <span id="f_eleve" class="select_multiple"></span>
    </td>
    <td class="nu" style="width:20em">
      <b>Modalité d'accompagnement :</b><br />
      <?php echo $select_modaccomp ?>
      <p id="p_commentaire" class="hide">
        <b>Commentaire :</b><br />
        <input id="input_commentaire" name="f_commentaire" type="text" value="" size="50" maxlength="127" />
      </p>
    </td>
    <td class="nu" style="width:25em">
      <button id="bouton_associer" type="button" class="parametre">Effectuer ces associations.</button>
      <p><label id="ajax_msg">&nbsp;</label></p>
    </td>
  </tr></table>
</form>

<hr />

<table id="table_action" class="form hsort">
  <thead>
    <tr>
      <th>Classe</th>
      <th>Élève</th>
      <th>Dispositif</th>
      <th>Commentaire</th>
      <th class="nu"></th>
    </tr>
  </thead>
  <tbody>
    <tr class="vide"><td class="nu" colspan="4"></td><td class="nu"></td></tr>
  </tbody>
</table>

<form action="#" method="post" id="form_gestion" class="hide">
  <h2>Modifier le descriptif du PPRE</h2>
  <p>
    <label class="tab">Classe :</label><b id="b_classe"></b><br />
    <label class="tab">Élève :</label><b id="b_eleve"></b><br />
    <label class="tab" for="f_commentaire">Commentaire :</label><input id="f_commentaire" name="f_commentaire" type="text" value="" size="50" maxlength="127" />
  </p>
  <p>
    <span class="tab"></span><input id="f_action" name="f_action" type="hidden" value="modifier_commentaire" /><input id="ppre_eleve" name="f_eleve" type="hidden" value="" /><button id="bouton_valider" type="button" class="valider">Valider.</button> <button id="bouton_annuler" type="button" class="annuler">Annuler.</button><label id="ajax_msg_gestion">&nbsp;</label>
  </p>
</form>

