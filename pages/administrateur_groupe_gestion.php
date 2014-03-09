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
$TITRE = "Gérer les groupes";

// Javascript
$GLOBALS['HEAD']['js']['inline'][] = 'var tab_niveau_ordre = new Array();';

$select_niveau = '<option value=""></option>';

$DB_TAB = DB_STRUCTURE_COMMUN::DB_lister_niveaux_etablissement(FALSE /*with_specifiques*/);
if(!empty($DB_TAB))
{
  foreach($DB_TAB as $DB_ROW)
  {
    $select_niveau .= '<option value="'.$DB_ROW['niveau_id'].'">'.html($DB_ROW['niveau_nom']).'</option>';
    $GLOBALS['HEAD']['js']['inline'][] = 'tab_niveau_ordre["'.html($DB_ROW['niveau_nom']).'"]="'.sprintf("%02u",$DB_ROW['niveau_ordre']).'";';
  }
}
else
{
  $select_niveau .= '<option value="" disabled>Aucun niveau de classe n\'est choisi pour l\'établissement !</option>';
}

// Javascript
$GLOBALS['HEAD']['js']['inline'][] = '// <![CDATA[';
$GLOBALS['HEAD']['js']['inline'][] = 'var select_niveau="'.str_replace('"','\"',$select_niveau).'";';
$GLOBALS['HEAD']['js']['inline'][] = '// ]]>';
?>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_groupes">DOC : Gestion des groupes</a></span></p>

<hr />

<table id="table_action" class="form hsort">
  <thead>
    <tr>
      <th>Niveau</th>
      <th>Référence</th>
      <th>Nom complet</th>
      <th class="nu"><q class="ajouter" title="Ajouter un groupe."></q></th>
    </tr>
  </thead>
  <tbody>
    <?php
    // Lister les groupes avec les niveaux
    $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_groupes_avec_niveaux();
    if(!empty($DB_TAB))
    {
      foreach($DB_TAB as $DB_ROW)
      {
        // Afficher une ligne du tableau
        echo'<tr id="id_'.$DB_ROW['groupe_id'].'">';
        echo  '<td><i>'.sprintf("%02u",$DB_ROW['niveau_ordre']).'</i>'.html($DB_ROW['niveau_nom']).'</td>';
        echo  '<td>'.html($DB_ROW['groupe_ref']).'</td>';
        echo  '<td>'.html($DB_ROW['groupe_nom']).'</td>';
        echo  '<td class="nu">';
        echo    '<q class="modifier" title="Modifier ce groupe."></q>';
        echo    '<q class="supprimer" title="Supprimer ce groupe."></q>';
        echo  '</td>';
        echo'</tr>'.NL;
      }
    }
    else
    {
      echo'<tr><td class="nu" colspan="4"></td></tr>'.NL;
    }
    ?>
  </tbody>
</table>

<form action="#" method="post" id="form_gestion" class="hide">
  <h2>Ajouter | Modifier | Supprimer un groupe</h2>
  <div id="gestion_edit">
    <p>
      <label class="tab" for="f_niveau">Niveau :</label><select id="f_niveau" name="f_niveau"><?php echo $select_niveau ?></select><br />
      <label class="tab" for="f_ref">Référence :</label><input id="f_ref" name="f_ref" type="text" value="" size="10" maxlength="8" /><br />
      <label class="tab" for="f_nom">Nom complet :</label><input id="f_nom" name="f_nom" type="text" value="" size="20" maxlength="20" />
    </p>
  </div>
  <div id="gestion_delete">
    <p class="danger">Les associations des élèves, des professeurs, et les évaluations seront perdues !</p>
    <p>Confirmez-vous la suppression du groupe &laquo;&nbsp;<b id="gestion_delete_identite"></b>&nbsp;&raquo; ?</p>
  </div>
  <p>
    <label class="tab"></label><input id="f_action" name="f_action" type="hidden" value="" /><input id="f_id" name="f_id" type="hidden" value="" /><button id="bouton_valider" type="button" class="valider">Valider.</button> <button id="bouton_annuler" type="button" class="annuler">Annuler.</button><label id="ajax_msg_gestion">&nbsp;</label>
  </p>
</form>
