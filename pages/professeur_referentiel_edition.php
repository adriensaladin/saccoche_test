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
$TITRE = html(Lang::_("Modifier le contenu des référentiels"));

if(!test_user_droit_specifique( $_SESSION['DROIT_GERER_REFERENTIEL'] , NULL /*matiere_coord_or_groupe_pp_connu*/ , 0 /*matiere_id_or_groupe_id_a_tester*/ ))
{
  echo'<p class="danger">Vous n\'êtes pas habilité à accéder à cette fonctionnalité !</p>'.NL;
  echo'<div class="astuce">Profils autorisés (par les administrateurs) :</div>'.NL;
  echo afficher_profils_droit_specifique($_SESSION['DROIT_GERER_REFERENTIEL'],'li');
  return; // Ne pas exécuter la suite de ce fichier inclus.
}
?>

<ul class="puce">
  <li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=referentiels_socle__referentiel_modifier_contenu">DOC : Modifier le contenu des référentiels.</a></span></li>
  <li><span class="astuce">Pour mettre à jour sur le serveur communautaire un référentiel modifié, utiliser la page [<a href="./index.php?page=professeur_referentiel&amp;section=gestion">créer / paramétrer les référentiels</a>].</span></li>
  <li><span class="danger">Retirer des items supprime les résultats associés de tous les élèves !</span></li>
</ul>

<hr />

<form action="#" method="post" id="zone_choix_referentiel" onsubmit="return false;">
<?php
$texte_profil = afficher_profils_droit_specifique($_SESSION['DROIT_GERER_REFERENTIEL'],'br');
// On récupère la liste des référentiels des matières auxquelles le professeur est rattaché, et s'il en est coordonnateur
$DB_TAB = DB_STRUCTURE_PROFESSEUR::DB_lister_matieres_niveaux_referentiels_professeur($_SESSION['USER_ID']);
if(empty($DB_TAB))
{
  echo'<ul class="puce">'.NL;
  echo'<li><span class="danger">Aucun référentiel présent parmi les matières qui vous sont rattachées !</span></li>'.NL;
  echo'<li><span class="astuce">Commencer par <a href="./index.php?page=professeur_referentiel&amp;section=gestion">créer ou importer un référentiel</a>.</span></li>'.NL;
  echo'</ul>'.NL;
}
else
{
  // On récupère les données
  $tab_matiere = array();
  $tab_matiere_droit = array();
  foreach($DB_TAB as $DB_ROW)
  {
    if(!isset($tab_matiere[$DB_ROW['matiere_id']]))
    {
      $matiere_droit = test_user_droit_specifique( $_SESSION['DROIT_GERER_REFERENTIEL'] , $DB_ROW['jointure_coord'] /*matiere_coord_or_groupe_pp_connu*/ );
      $icone_action  = ($matiere_droit) ? '<q class="modifier" title="Modifier les référentiels de cette matière."></q>' : '<q class="modifier_non" title="Droit d\'accès :<br />'.$texte_profil.'."></q>' ;
      $tab_matiere[$DB_ROW['matiere_id']] = array( 
        'matiere_nom' => html($DB_ROW['matiere_nom']) ,
        'matiere_col' => '<td class="nu" id="td_'.$DB_ROW['matiere_id'].'">'.$icone_action.'</td>' ,
        'niveau_nb'=>1
      );
      if($matiere_droit)
      {
        $tab_matiere_droit[] = $DB_ROW['matiere_id'];
      }
    }
    else
    {
      $tab_matiere[$DB_ROW['matiere_id']]['niveau_nb']++;
    }
  }
  // On construit et affiche le tableau résultant
  echo'<h2>Éditer les référentiels en détail</h2>'.NL;
  echo'<table class="vm_nug"><thead>'.NL.'<tr><th>Matière</th><th>Niveau(x)</th><th class="nu"></th></tr>'.NL.'</thead><tbody>'.NL;
  foreach($tab_matiere as $matiere_id => $tab)
  {
    $x = ($tab_matiere[$matiere_id]['niveau_nb'])>1 ? 'x' : '';
    echo'<tr><td>'.$tab['matiere_nom'].'</td><td>'.$tab_matiere[$matiere_id]['niveau_nb'].' niveau'.$x.'</td>'.$tab['matiere_col'].'</tr>'.NL;
  }
  echo'</tbody></table>'.NL;
  echo'<hr /><h2>Manipulations complémentaires</h2>'.NL;
  if(count($tab_matiere_droit))
  {
    Layout::add( 'js_inline_before' , 'var listing_id_matieres_autorisees = ",'.implode(',',$tab_matiere_droit).',";' );
    echo'<p>'.NL;
    echo'<select id="select_action_groupe_choix" name="select_action_groupe">'
          .'<option value=""></option>'
          .'<option value="modifier_coefficient">Modifier tous les coefficients des items</option>'
          .'<option value="modifier_panier">Modifier toutes les autorisations de demandes d\'évaluation</option>'
          .'<option value="deplacer_domaine">Déplacer tout le domaine</option>'
          .'<option value="deplacer_theme">Déplacer tout le thème</option>'
        .'</select>'.NL;
    echo'<select id="select_action_groupe_modifier_objet" name="select_action_groupe_modifier_objet" class="hide">'
          .'<option value=""></option>'
          .'<option value="referentiel">du référentiel</option>'
          .'<option value="domaine">du domaine</option>'
          .'<option value="theme">du thème</option>'
        .'</select>'.NL;
    echo'<select id="select_action_groupe_modifier_id" name="select_action_groupe_modifier_id" class="hide"></select>'.NL;
    echo'<select id="select_action_groupe_modifier_coef" name="select_action_groupe_modifier_coef" class="hide">'
          .'<option value=""></option>'
          .'<option value="0">à 0</option>'
          .'<option value="1">à 1</option>'
          .'<option value="2">à 2</option>'
          .'<option value="3">à 3</option>'
          .'<option value="4">à 4</option>'
          .'<option value="5">à 5</option>'
          .'<option value="6">à 6</option>'
          .'<option value="7">à 7</option>'
          .'<option value="8">à 8</option>'
          .'<option value="9">à 9</option>'
          .'<option value="10">à 10</option>'
          .'<option value="11">à 11</option>'
          .'<option value="12">à 12</option>'
          .'<option value="13">à 13</option>'
          .'<option value="14">à 14</option>'
          .'<option value="15">à 15</option>'
          .'<option value="16">à 16</option>'
          .'<option value="17">à 17</option>'
          .'<option value="18">à 18</option>'
          .'<option value="19">à 19</option>'
          .'<option value="20">à 20</option>'
        .'</select>'.NL;
    echo'<select id="select_action_groupe_modifier_cart" name="select_action_groupe_modifier_cart" class="hide">'
          .'<option value=""></option>'
          .'<option value="1">à "oui"</option>'
          .'<option value="0">à "non"</option>'
        .'</select>'.NL;
    echo'<select id="select_action_groupe_deplacer_id_initial" name="select_action_groupe_deplacer_id_initial" class="hide"></select>'.NL;
    echo'<select id="select_action_deplacer_explication" name="select_action_deplacer_explication" class="hide">'
          .'<option value="deplacer_domaine">vers le référentiel (d\'une autre matière)</option>'
          .'<option value="deplacer_theme">vers le domaine (d\'une autre matière)</option>'
        .'</select>'.NL;
    echo'<select id="select_action_groupe_deplacer_id_final" name="select_action_groupe_deplacer_id_final" class="hide"></select>'.NL;
    echo'</p>'.NL;
    echo'<p><span class="tab"></span><button id="bouton_valider_groupe" type="button" class="valider" disabled>Valider cette action</button><label id="ajax_msg_groupe">&nbsp;</label></p>';
    echo'<p id="groupe_modifier_avertissement" class="hide">'.NL;
    echo'<span class="danger">Cette opération n\'est pas anodine, utilisez-là en période creuse et après avoir demandé à un administrateur de sauvegarder la base.</span><br />';
    echo'<span class="astuce">Un administrateur a aussi la possibilité de convertir tous les référentiels d\'une matière vers une nouvelle matière (<span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_administrateur__gestion_matieres#toggle_deplacer_referentiels">DOC.</a></span>).</span>';
    echo'</p>'.NL;
  }
  else
  {
    Layout::add( 'js_inline_before' , 'var listing_id_matieres_autorisees = "";' );
    echo'<p class="astuce">Droit d\'accès :<br />'.$texte_profil.'.</p>'.NL;
  }
}
?>
</form>

<form action="#" method="post" id="zone_elaboration_referentiel" onsubmit="return false;" class="arbre_dynamique">
</form>

<div id="zone_socle_item" class="arbre_dynamique hide">
  <h2>Relation au socle commun</h2>
  <form action="#" method="post">
    <p>
      <label class="tab">Item disciplinaire :</label><span class="f_nom i"></span><br />
      <label class="tab">Socle commun :</label>Cocher ci-dessous (<span class="astuce">cliquer sur un intitulé pour déployer son contenu</span>).<br />
      <span class="tab"></span><button id="choisir_socle_valider" type="button" class="valider">Valider le choix effectué.</button> <button id="choisir_socle_annuler" type="button" class="annuler">Annuler.</button>
    </p>
    <ul class="ul_n1 p"><li class="li_n3"><label for="socle_0"><input id="socle_0" name="f_socle" type="radio" value="0" /> Hors-socle.</label></li></ul>
    <?php
    // Affichage de la liste des items du socle pour chaque palier
    $DB_TAB = DB_STRUCTURE_COMMUN::DB_recuperer_arborescence_palier();
    if(!empty($DB_TAB))
    {
      echo HtmlArborescence::afficher_socle_from_SQL( $DB_TAB , TRUE /*dynamique*/ , FALSE /*reference*/ , TRUE /*aff_input*/ , FALSE /*ids*/ );
    }
    else
    {
      echo'<span class="danger"> Aucun palier du socle n\'est associé à l\'établissement ! L\'administrateur doit préalablement choisir les paliers évalués...</span>'.NL;
    }
    ?>
  </form>
</div>

