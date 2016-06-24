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
$TITRE = html(Lang::_("Livret Scolaire")).' &rarr; '.html(Lang::_("Enseignements Pratiques Interdisciplinaires"));
?>

<ul class="puce">
  <li><span class="astuce">Les <b>Enseignements Pratiques Interdisciplinaires</b> mis en place à compter de la rentrée 2016 concernent les <b>élèves du Cycle 4 (5e 4e 3e)</b>.</span></li>
  <li><span class="manuel"><a class="pop_up" href="http://eduscol.education.fr/cid99750/epi.html">Documents et informations ministérielles.</a></span></li>
  <li>Dans sa forme, le livret scolaire impose de rattacher plusieurs couples { matière / enseignant } pour un projet mené sur une classe.</li>
</ul>

<hr />

<?php
$page_ordre_longueur = 3;
$page_ordre_format   = '%0'.$page_ordre_longueur.'u';

// Javascript
Layout::add( 'js_inline_before' , 'var tab_page_ordre = new Array();' );

$select_page  = '<option value="">&nbsp;</option>';
$select_theme = '<option value="">&nbsp;</option>';

// Formulaire select_page avec ordres associés, si au moins une classe est associée à la page
$DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_pages_for_dispositif( 'epi' );

if(empty($DB_TAB))
{
  echo'<p class="danger">Aucune classe n\'est associée à une page du livret concernée par ce dispositif !<br />Si besoin, commencez par <a href="./index.php?page=livret&amp;section=classes">associer les classes au livret scolaire</a>.</p>'.NL;
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

foreach($DB_TAB as $DB_ROW)
{
  $select_page .= '<option value="'.$DB_ROW['livret_page_ref'].'">'.html($DB_ROW['livret_page_moment']).'</option>';
  Layout::add( 'js_inline_before' , 'tab_page_ordre["'.html($DB_ROW['livret_page_moment']).'"]="'.sprintf($page_ordre_format,$DB_ROW['livret_page_ordre']).'";' );
}

// On liste les thèmes des epis
$DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_epi_theme();

foreach($DB_TAB as $DB_ROW)
{
  $select_theme .= '<option value="'.$DB_ROW['livret_epi_theme_code'].'">'.html($DB_ROW['livret_epi_theme_nom']).'</option>';
}

?>

<table id="table_action" class="form hsort">
  <thead>
    <tr>
      <th>Moment</th>
      <th>Classe</th>
      <th>Thème</th>
      <th>Matière / Professeur</th>
      <th>Titre</th>
      <th class="nu"><q class="ajouter" title="Ajouter un enseignement pratique interdisciplinaire."></q></th>
    </tr>
  </thead>
  <tbody>
    <?php
    // Lister les enseignements pratiques interdisciplinaires
    $DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_epi();
    if(!empty($DB_TAB))
    {
      foreach($DB_TAB as $DB_ROW)
      {
        // Afficher une ligne du tableau
        echo'<tr id="id_'.$DB_ROW['livret_epi_id'].'">';
        echo  '<td data-id="'.$DB_ROW['livret_page_ref'].'"><i>'.sprintf($page_ordre_format,$DB_ROW['livret_page_ordre']).'</i>'.html($DB_ROW['livret_page_moment']).'</td>';
        echo  '<td data-id="'.$DB_ROW['groupe_id'].'">'.html($DB_ROW['groupe_nom']).'</td>';
        echo  '<td data-id="'.$DB_ROW['livret_epi_theme_code'].'">'.html($DB_ROW['livret_epi_theme_nom']).'</td>';
        echo  '<td data-id="'.$DB_ROW['matiere_prof_id'].'">'.str_replace('§BR§','<br />',html($DB_ROW['matiere_prof_texte'])).'</td>';
        echo  '<td>'.html($DB_ROW['livret_epi_titre']).'</td>';
        echo  '<td class="nu">';
        echo    '<q class="modifier" title="Modifier cet E.P.I."></q>';
        echo    '<q class="dupliquer" title="Dupliquer cet E.P.I."></q>';
        echo    '<q class="supprimer" title="Supprimer cet E.P.I."></q>';
        echo  '</td>';
        echo'</tr>'.NL;
      }
    }
    else
    {
      echo'<tr class="vide"><td class="nu" colspan="5">Cliquer sur l\'icône ci-dessus (symbole "+" dans un rond vert) pour ajouter un enseignement pratique interdisciplinaire.</td><td class="nu"></td></tr>'.NL;
    }
    ?>
  </tbody>
</table>

<form action="#" method="post" id="form_gestion" class="hide">
  <h2><span id="gestion_titre_action">Ajouter | Modifier | Dupliquer | Supprimer</span> un enseignement pratique interdisciplinaire</h2>
  <div id="gestion_edit">
    <p>
      <label class="tab" for="f_page">Moment :</label><select id="f_page" name="f_page"><?php echo $select_page ?></select><br />
      <label class="tab" for="f_groupe">Classe :</label><select id="f_groupe" name="f_groupe"><option></option></select><br />
      <label class="tab" for="f_theme">Thème :</label><select id="f_theme" name="f_theme"><?php echo $select_theme ?></select><br />
      <label class="tab" for="f_titre">Titre :</label><input id="f_titre" name="f_titre" type="text" value="" size="40" maxlength="50" /><br />
      <label class="tab" for="f_nombre">Nombre disciplines :</label><select id="f_nombre" name="f_nombre"><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option></select>
    </p>
    <p id="join_1" class="hide">
      <label class="tab" for="f_matiere_1">Matière 1 :</label><select id="f_matiere_1" name="f_matiere_1"><option></option></select><br />
      <label class="tab" for="f_prof_1">Professeur 1 :</label><select id="f_prof_1" name="f_prof_1"><option></option></select>
    </p>
    <p id="join_2" class="hide">
      <label class="tab" for="f_matiere_2">Matière 2 :</label><select id="f_matiere_2" name="f_matiere_2"><option></option></select><br />
      <label class="tab" for="f_prof_2">Professeur 2 :</label><select id="f_prof_2" name="f_prof_2"><option></option></select>
    </p>
    <p id="join_3" class="hide">
      <label class="tab" for="f_matiere_3">Matière 3 :</label><select id="f_matiere_3" name="f_matiere_3"><option></option></select><br />
      <label class="tab" for="f_prof_3">Professeur 3 :</label><select id="f_prof_3" name="f_prof_3"><option></option></select>
    </p>
    <p id="join_4" class="hide">
      <label class="tab" for="f_matiere_4">Matière 4 :</label><select id="f_matiere_4" name="f_matiere_4"><option></option></select><br />
      <label class="tab" for="f_prof_4">Professeur 4 :</label><select id="f_prof_4" name="f_prof_4"><option></option></select>
    </p>
    <p id="join_5" class="hide">
      <label class="tab" for="f_matiere_5">Matière 5 :</label><select id="f_matiere_5" name="f_matiere_5"><option></option></select><br />
      <label class="tab" for="f_prof_5">Professeur 5 :</label><select id="f_prof_5" name="f_prof_5"><option></option></select>
    </p>
    <p class="astuce">Le projet réalisé est renseigné ultérieurement via le commentaire sur la classe.</p>
  </div>
  <div id="gestion_delete">
    <p>Confirmez-vous la suppression de l'E.P.I. &laquo;&nbsp;<b id="gestion_delete_identite"></b>&nbsp;&raquo; ?</p>
  </div>
  <p>
    <span class="tab"></span><input id="f_action" name="f_action" type="hidden" value="" /><input id="f_id" name="f_id" type="hidden" value="" /><button id="bouton_valider" type="button" class="valider">Valider.</button> <button id="bouton_annuler" type="button" class="annuler">Annuler.</button><label id="ajax_msg_gestion">&nbsp;</label>
  </p>
</form>
