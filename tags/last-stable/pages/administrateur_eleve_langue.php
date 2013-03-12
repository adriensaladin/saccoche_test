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
$TITRE = "Choisir la langue étrangère pour le socle commun";
?>

<?php
// Fabrication des éléments select du formulaire
require(CHEMIN_DOSSIER_INCLUDE.'tableau_langues.php');
$tab_groupes = DB_STRUCTURE_COMMUN::DB_OPT_regroupements_etabl();
$select_eleve  = Form::afficher_select($tab_groupes , $select_nom=FALSE , $option_first='oui' , $selection=FALSE , $optgroup='oui');
$select_langue = Form::afficher_select($tab_langues , $select_nom=FALSE , $option_first='oui' , $selection=FALSE , $optgroup='non');
?>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=referentiels_socle__socle_choisir_langue">DOC : Choisir la langue étrangère pour le socle commun</a></span></p>

<hr />

<form action="#" method="post" id="form_select">
  <table><tr>
    <td class="nu" style="width:25em">
      <b>Élèves :</b> <span class="check_multiple"><input name="leurre" type="image" alt="leurre" src="./_img/auto.gif" /><input name="all_check" type="image" alt="Tout cocher." src="./_img/all_check.gif" title="Tout cocher." /> <input name="all_uncheck" type="image" alt="Tout décocher." src="./_img/all_uncheck.gif" title="Tout décocher." /></span><br />
      <select id="select_groupe" name="select_groupe"><?php echo $select_eleve ?></select><br />
      <span id="f_eleve" class="select_multiple"></span>
    </td>
    <td class="nu" style="width:20em">
      <b>Langue :</b><br />
      <select id="f_langue" name="f_langue"><?php echo $select_langue; ?></select>
    </td>
    <td class="nu" style="width:25em">
      <button id="associer" type="button" class="parametre">Effectuer ces associations.</button>
      <p><label id="ajax_msg">&nbsp;</label></p>
    </td>
  </tr></table>
</form>

<div id="bilan">
</div>
