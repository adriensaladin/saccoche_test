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
$TITRE = "Gérer ses regroupements d'items";

require(CHEMIN_DOSSIER_INCLUDE.'fonction_affichage_sections_communes.php');

// Javascript
$GLOBALS['HEAD']['js']['inline'][] = 'var tab_items = new Array();';
?>

<ul class="puce">
  <li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_professeur__gestion_regroupements_items">DOC : Gestion des regroupements d'items.</a></span></li>
</ul>

<hr />

<table id="table_action" class="form hsort">
  <thead>
    <tr>
      <th>Nom</th>
      <th>Items</th>
      <th class="nu"><q class="ajouter" title="Ajouter un regroupement d'items."></q></th>
    </tr>
  </thead>
  <tbody>
    <?php
    $DB_TAB = DB_STRUCTURE_PROFESSEUR::DB_lister_selection_items($_SESSION['USER_ID']);
    if(!empty($DB_TAB))
    {
      foreach($DB_TAB as $DB_ROW)
      {
        $items_liste  = str_replace(',','_',mb_substr($DB_ROW['selection_item_liste'],1,-1));
        $items_nombre = (mb_substr_count($DB_ROW['selection_item_liste'],',')-1);
        $items_texte  = ($items_nombre>1) ? $items_nombre.' items' : '1 item' ;
        // Afficher une ligne du tableau
        echo'<tr id="id_'.$DB_ROW['selection_item_id'].'">';
        echo  '<td>'.$DB_ROW['selection_item_nom'].'</td>';
        echo  '<td>'.$items_texte.'</td>';
        echo  '<td class="nu">';
        echo    '<q class="modifier" title="Modifier cette sélection d\'items."></q>';
        echo    '<q class="supprimer" title="Supprimer cette sélection d\'items."></q>';
        echo  '</td>';
        echo'</tr>'.NL;
        // Javascript
        $GLOBALS['HEAD']['js']['inline'][] = 'tab_items["'.$DB_ROW['selection_item_id'].'"]="'.$items_liste.'";';
      }
    }
    else
    {
      echo'<tr><td class="nu" colspan="3"></td></tr>'.NL;
    }
    ?>
  </tbody>
</table>

<form action="#" method="post" id="form_gestion" class="hide">
  <h2>Ajouter | Modifier un regroupements d'items</h2>
  <div id="gestion_edit">
    <p>
      <label class="tab" for="f_nom">Nom :</label><input id="f_nom" name="f_nom" type="text" value="" size="50" maxlength="60" />
    </p>
    <p>
      <label class="tab" for="f_compet_nombre">Items :</label><input id="f_compet_nombre" name="f_compet_nombre" size="10" type="text" value="" readonly /><input id="f_compet_liste" name="f_compet_liste" type="hidden" value="" /><q class="choisir_compet" title="Voir ou choisir les items."></q>
    </p>
  </div>
  <div id="gestion_delete">
    <p>Confirmez-vous la suppression du regroupements d'items &laquo;&nbsp;<b id="gestion_delete_identite"></b>&nbsp;&raquo; ?</p>
  </div>
  <p>
    <label class="tab"></label><input id="f_action" name="f_action" type="hidden" value="" /><input id="f_id" name="f_id" type="hidden" value="" /><input id="f_origine" name="f_origine" type="hidden" value="<?php echo $PAGE ?>" /><button id="bouton_valider" type="button" class="valider">Valider.</button> <button id="bouton_annuler" type="button" class="annuler">Annuler.</button><label id="ajax_msg_gestion">&nbsp;</label>
  </p>
</form>

<form action="#" method="post" id="zone_matieres_items" class="arbre_dynamique arbre_check hide">
  <div>Tout déployer / contracter :<q class="deployer_m1"></q><q class="deployer_m2"></q><q class="deployer_n1"></q><q class="deployer_n2"></q><q class="deployer_n3"></q></div>
  <p>Cocher ci-dessous (<span class="astuce">cliquer sur un intitulé pour déployer son contenu</span>) :</p>
  <?php
  // Affichage de la liste des items pour toutes les matières d'un professeur ou toutes les matières de l'établissement si directeur, sur tous les niveaux
  $user_id = ($_SESSION['USER_PROFIL_TYPE']=='professeur') ? $_SESSION['USER_ID'] : 0 ;
  $DB_TAB = DB_STRUCTURE_COMMUN::DB_recuperer_arborescence( $user_id , 0 /*matiere_id*/ , 0 /*niveau_id*/ , FALSE /*only_socle*/ , FALSE /*only_item*/ , FALSE /*socle_nom*/ );
  if(empty($DB_TAB))
  {
    $phrase_debut =  ($_SESSION['USER_PROFIL_TYPE']=='professeur') ? 'Vous n\'êtes rattaché à' : 'L\'établissement n\'a mis en place' ;
    echo'<p class="danger">'.$phrase_debut.' aucune matière, ou des matières ne comportant aucun référentiel !</p>' ;
  }
  else
  {
    $arborescence = Html::afficher_arborescence_matiere_from_SQL( $DB_TAB , TRUE /*dynamique*/ , TRUE /*reference*/ , FALSE /*aff_coef*/ , FALSE /*aff_cart*/ , 'texte' /*aff_socle*/ , FALSE /*aff_lien*/ , TRUE /*aff_input*/ );
    $phrase_debut =  ($_SESSION['USER_PROFIL_TYPE']=='professeur') ? 'Vous êtes rattaché à' : 'L\'établissement a mis en place' ;
    echo strpos($arborescence,'<input') ? $arborescence : '<p class="danger">'.$phrase_debut.' des matières dont les référentiels ne comportent aucun item !</p>' ;
  }
  ?>
  <div><span class="tab"></span><button id="valider_compet" type="button" class="valider">Valider la sélection</button>&nbsp;&nbsp;&nbsp;<button id="annuler_compet" type="button" class="annuler">Annuler / Retour</button></div>
</form>
