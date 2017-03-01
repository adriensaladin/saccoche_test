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
$TITRE = html(Lang::_("Livret Scolaire")).' &rarr; '.html(Lang::_("Enseignements de complément"));
?>

<ul class="puce">
  <li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=officiel__livret_scolaire_administration#toggle_enscompl">DOC : Administration du Livret Scolaire &rarr; Enseignements de complément</a></span></li>
  <li><span class="astuce">Ce menu ne sert que pour le <b>bilan de fin de cycle 4</b> (sans objet pour les <b>bilans périodiques</b> ou de fin d'autres cycles).</span></li>
</ul>

<hr />

<?php
// Fabrication des éléments select du formulaire
$tab_groupes  = DB_STRUCTURE_COMMUN::DB_OPT_classes_groupes_etabl();
$tab_enscompl = DB_STRUCTURE_LIVRET::DB_OPT_enscompl();

$select_eleve    = HtmlForm::afficher_select($tab_groupes  , 'select_groupe' /*select_nom*/ , ''    /*option_first*/ , FALSE /*selection*/ , 'regroupements' /*optgroup*/ );
$select_enscompl = HtmlForm::afficher_select($tab_enscompl , 'f_enscompl'    /*select_nom*/ , FALSE /*option_first*/ , FALSE /*selection*/ ,              '' /*optgroup*/ );

// Javascript
Layout::add( 'js_inline_before' , 'var tab_commentaire = new Array();' );
?>

<form action="#" method="post" id="form_select">
  <table><tr>
    <td class="nu" style="width:25em">
      <b>Élèves :</b><span class="check_multiple"><q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q><q class="cocher_inverse" title="Tout échanger."></q></span><br />
      <?php echo $select_eleve ?><br />
      <span id="f_eleve" class="select_multiple"></span>
    </td>
    <td class="nu" style="width:20em">
      <b>Enseignements de complément :</b><br />
      <?php echo $select_enscompl ?>
    </td>
    <td class="nu" style="width:25em">
      <button id="bouton_associer" type="button" class="parametre">Effectuer ces associations.</button>
      <p><label id="ajax_msg">&nbsp;</label></p>
    </td>
  </tr></table>
</form>

<hr />

<div id="bilan">
</div>
