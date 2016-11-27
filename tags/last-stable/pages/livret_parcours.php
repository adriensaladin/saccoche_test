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
$TITRE = html(Lang::_("Livret Scolaire")).' &rarr; '.html(Lang::_("Parcours"));

// On liste les types de parcours
$DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_parcours_type();

// On récupère l'éventuel parcours transmis et on vérifie sa validité
$parcours      = isset($_GET['code'])             ? Clean::ref($_GET['code']) : '' ;
$parcours_code = isset($DB_TAB['PAR_'.$parcours]) ? 'PAR_'.$parcours          : '' ;

// On complète le Sous-Menu d'en-tête
$SOUS_MENU .= '<br />';
foreach($DB_TAB as $key => $TAB)
{
  $class = ($key==$parcours_code) ? ' class="actif"' : '' ;
  $SOUS_MENU .= '<a'.$class.' href="./index.php?page=livret&amp;section=parcours&amp;code='.Clean::lower(substr($key,4)).'">'.html($TAB[0]['livret_parcours_type_nom']).'</a>'.NL;
}

if(!$parcours_code)
{
  echo'<ul class="puce">';
  echo'  <li><span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=officiel__livret_scolaire_administration#toggle_parcours">DOC : Administration du Livret Scolaire &rarr; Parcours</a></span></li>';
  echo'</ul>';
  echo'<p>Choisir un type de parcours :</p>'.NL;
  echo'<ul class="puce">'.NL;
  foreach($DB_TAB as $key => $TAB)
  {
    echo'<li class="p"><a href="./index.php?page=livret&amp;section=parcours&amp;code='.Clean::lower(substr($key,42)).'">'.html($TAB[0]['livret_parcours_type_nom']).'</a></li>'.NL;
  }
  echo'</ul>'.NL;
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

// On met de côté les informations du parcours choisi
extract($DB_TAB[$parcours_code][0]); // $livret_parcours_type_nom

// On complète le titre de la page
$TITRE .= ' &rarr; '.html($livret_parcours_type_nom);

$txt_ecole = ($parcours_code!='PAR_AVN') ? 'de l\'École Élémentaire et' : '' ;

?>

<ul class="puce">
  <li><span class="astuce">Le <b><?php echo html($livret_parcours_type_nom) ?></b> concerne les <b>élèves <?php echo $txt_ecole ?> du Collège</b>.</span></li>
  <li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=officiel__livret_scolaire_administration#toggle_parcours">DOC : Administration du Livret Scolaire &rarr; Parcours</a></span></li>
</ul>

<hr />

<?php
$page_ordre_longueur = 3;
$page_ordre_format   = '%0'.$page_ordre_longueur.'u';

// Javascript
Layout::add( 'js_inline_before' , 'var tab_page_ordre = new Array();' );

$select_page = '<option value="">&nbsp;</option>';

// Formulaire select_page avec ordres associés, si au moins une classe est associée à la page
$DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_pages_for_dispositif( 'parcours' , $parcours_code );

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
?>

<table id="table_action" class="form hsort">
  <thead>
    <tr>
      <th>Moment</th>
      <th>Classe</th>
      <th>Professeur</th>
      <th class="nu"><q class="ajouter" title="Ajouter un parcours."></q></th>
    </tr>
  </thead>
  <tbody>
    <?php
    // Lister les parcours
    $DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_parcours($parcours_code);
    if(!empty($DB_TAB))
    {
      foreach($DB_TAB as $DB_ROW)
      {
        // Afficher une ligne du tableau
        echo'<tr id="id_'.$DB_ROW['livret_parcours_id'].'">';
        echo  '<td data-id="'.$DB_ROW['livret_page_ref'].'"><i>'.sprintf($page_ordre_format,$DB_ROW['livret_page_ordre']).'</i>'.html($DB_ROW['livret_page_moment']).'</td>';
        echo  '<td data-id="'.$DB_ROW['groupe_id'].'">'.html($DB_ROW['groupe_nom']).'</td>';
        echo  '<td data-id="'.$DB_ROW['prof_id'].'">'.html($DB_ROW['prof_nom'].' '.$DB_ROW['prof_prenom']).'</td>';
        echo  '<td class="nu">';
        echo    '<q class="modifier" title="Modifier ce parcours."></q>';
        echo    '<q class="dupliquer" title="Dupliquer ce parcours."></q>';
        echo    '<q class="supprimer" title="Supprimer ce parcours."></q>';
        echo  '</td>';
        echo'</tr>'.NL;
      }
    }
    else
    {
      echo'<tr class="vide"><td class="nu" colspan="3">Cliquer sur l\'icône ci-dessus (symbole "+" dans un rond vert) pour ajouter un parcours.</td><td class="nu"></td></tr>'.NL;
    }
    ?>
  </tbody>
</table>

<form action="#" method="post" id="form_gestion" class="hide">
  <h2><span id="gestion_titre_action">Ajouter | Modifier | Dupliquer | Supprimer</span> un <?php echo Clean::lower(html($livret_parcours_type_nom)) ?></h2>
  <div id="gestion_edit">
    <p>
      <label class="tab" for="f_page">Moment :</label><select id="f_page" name="f_page"><?php echo $select_page ?></select><br />
      <label class="tab" for="f_groupe">Classe :</label><select id="f_groupe" name="f_groupe"><option></option></select><br />
      <label class="tab" for="f_prof">Professeur <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Enseignant en charge d'indiquer une appréciation sur le livret." /> :</label><select id="f_prof" name="f_prof"><option></option></select><button id="modifier_prof" type="button" class="form_ajouter">&plusmn;</button>
    </p>
    <p class="astuce">Le projet mis en &oelig;uvre est renseigné ultérieurement via le commentaire sur la classe.</p>
  </div>
  <div id="gestion_delete">
    <p>Confirmez-vous la suppression du parcours &laquo;&nbsp;<b id="gestion_delete_identite"></b>&nbsp;&raquo; ?</p>
  </div>
  <p>
    <span class="tab"></span><input id="f_action" name="f_action" type="hidden" value="" /><input id="f_id" name="f_id" type="hidden" value="" /><input id="f_parcours" name="f_parcours" type="hidden" value="<?php echo $parcours_code ?>" /><button id="bouton_valider" type="button" class="valider">Valider.</button> <button id="bouton_annuler" type="button" class="annuler">Annuler.</button><label id="ajax_msg_gestion">&nbsp;</label>
  </p>
</form>
