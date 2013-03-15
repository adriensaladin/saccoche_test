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
$TITRE = "Affecter les élèves aux groupes";
?>

<?php
// Fabrication des éléments select du formulaire
$select_eleve  = Form::afficher_select(DB_STRUCTURE_COMMUN::DB_OPT_regroupements_etabl() , $select_nom=FALSE      , $option_first='oui' , $selection=FALSE , $optgroup='oui');
$select_groupe = Form::afficher_select(DB_STRUCTURE_COMMUN::DB_OPT_groupes_etabl()       , $select_nom='f_groupe' , $option_first='non' , $selection=FALSE , $optgroup='non' , $multiple=TRUE);
?>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_groupes">DOC : Gestion des groupes</a></span></p>

<hr />

<form action="#" method="post" id="form_select">
  <table><tr>
    <td class="nu" style="width:25em">
      <b>Élèves :</b> <span class="check_multiple"><input name="leurre" type="image" alt="leurre" src="./_img/auto.gif" /><input name="all_check" type="image" alt="Tout cocher." src="./_img/all_check.gif" title="Tout cocher." /> <input name="all_uncheck" type="image" alt="Tout décocher." src="./_img/all_uncheck.gif" title="Tout décocher." /></span><br />
      <select id="select_groupe" name="select_groupe"><?php echo $select_eleve ?></select><br />
      <span id="f_eleve" class="select_multiple"></span>
    </td>
    <td class="nu" style="width:20em">
      <b>Groupes :</b> <span class="check_multiple"><input name="leurre" type="image" alt="leurre" src="./_img/auto.gif" /><input name="all_check" type="image" alt="Tout cocher." src="./_img/all_check.gif" title="Tout cocher." /> <input name="all_uncheck" type="image" alt="Tout décocher." src="./_img/all_uncheck.gif" title="Tout décocher." /></span><br />
      <span id="f_groupe" class="select_multiple"><?php echo $select_groupe; ?></span>
    </td>
    <td class="nu" style="width:25em">
      <button id="ajouter" type="button" class="groupe_ajouter">Ajouter ces associations.</button><br />
      <button id="retirer" type="button" class="groupe_retirer">Retirer ces associations.</button>
      <p><label id="ajax_msg">&nbsp;</label></p>
    </td>
  </tr></table>
</form>

<div id="bilan">
</div>
