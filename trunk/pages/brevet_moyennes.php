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
$TITRE = "Étape n°3 - Calculer et contrôler les notes pour chaque élève";
?>

<p>
  <span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=releves_bilans__notanet_fiches_brevet#toggle_etape3_notes">DOC : Notanet &amp; Fiches brevet &rarr; Enregistrement des notes</a></span><br />
  <span class="danger">Cette étape doit être effectuée lorsque tous les bulletins sont clos en saisis (moyennes définitives), ou le plus tard possible si utilisation de moyennes annuelles.</span>
</p>
<hr />

<?php
// Lister les séries de Brevet en place
$DB_TAB = DB_STRUCTURE_BREVET::DB_lister_brevet_series_etablissement();
if(empty($DB_TAB))
{
  echo'<p class="danger">Aucun élève n\'est associé à une série du brevet !<p>'.NL;
  echo'<div class="astuce"><a href="./index.php?page=brevet&amp;section=series">Effectuer l\'étape n°1.</a><div>'.NL;
  return; // Ne pas exécuter la suite de ce fichier inclus.
}
$tab_brevet_series = array();
foreach($DB_TAB as $DB_ROW)
{
  $tab_brevet_series[$DB_ROW['brevet_serie_ref']] = html($DB_ROW['brevet_serie_nom']);
}

// Vérifier que les séries de Brevet sont configurées
$DB_TAB = DB_STRUCTURE_BREVET::DB_lister_brevet_series_etablissement_non_configurees();
if(count($DB_TAB))
{
  foreach($DB_TAB as $DB_ROW)
  {
    echo'<p class="danger">'.html($DB_ROW['brevet_serie_nom']).' &rarr; non configurée !<p>'.NL;
  }
  echo'<div class="astuce"><a href="./index.php?page=brevet&amp;section=epreuves">Effectuer l\'étape n°2</a> ou <a href="./index.php?page=brevet&amp;section=series">Rectifier l\'étape n°1.</a><div>'.NL;
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

//
// Affichage du bilan des affectations des series aux élèves avec indicateur de moyennes enregistrées
//

$tab_niveau_groupe = array();
$tab_user          = array();
$tab_select        = array();
$tab_niveau_groupe[0][0] = 'sans classe';
$tab_user[0]             = '';
$tab_select[0]           = '';

// Récupérer la liste des classes
$DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_classes_avec_niveaux($niveau_ordre='DESC');
foreach($DB_TAB as $DB_ROW)
{
  $tab_niveau_groupe[$DB_ROW['niveau_id']][$DB_ROW['groupe_id']] = html($DB_ROW['groupe_nom']);
  $tab_user[  $DB_ROW['groupe_id']] = '';
  $tab_select[$DB_ROW['groupe_id']] = '';
}
// Récupérer la liste des élèves, avec classe, série de brevet, et récupération du total des points (si existant)
$DB_TAB = DB_STRUCTURE_BREVET::DB_lister_brevet_eleves_avec_serie_et_total();
foreach($DB_TAB as $DB_ROW)
{
  $class = ($DB_ROW['saisie_note']===NULL) ? 'erreur' : 'valide' ;
  $tab_user[  $DB_ROW['eleve_classe_id']] .= '<a id="m_'.$DB_ROW['eleve_classe_id'].'_'.$DB_ROW['user_id'].'_'.$DB_ROW['eleve_brevet_serie'].'" href="#"><img src="./_img/brevet/serie_'.$DB_ROW['eleve_brevet_serie'].'.png" alt="" title="'.$tab_brevet_series[$DB_ROW['eleve_brevet_serie']].'" /><label class="'.$class.'"></label> '.html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']).'</a><br />';
  $tab_select[$DB_ROW['eleve_classe_id']] .= '<option value="'.$DB_ROW['eleve_classe_id'].'_'.$DB_ROW['user_id'].'_'.$DB_ROW['eleve_brevet_serie'].'">'.html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']).'</option>';
}
// Assemblage du tableau résultant
$TH = array();
$TB = array();
$TF = array();
$select_eleve = '';
foreach($tab_niveau_groupe as $niveau_id => $tab_groupe)
{
  $TH[$niveau_id] = '';
  $TB[$niveau_id] = '';
  $TF[$niveau_id] = '';
  foreach($tab_groupe as $groupe_id => $groupe_nom)
  {
    if($tab_user[$groupe_id])
    {
      $nb = mb_substr_count($tab_user[$groupe_id],'<br />','UTF-8');
      $s = ($nb>1) ? 's' : '' ;
      $TH[$niveau_id] .= '<th>'.$groupe_nom.'</th>';
      $TB[$niveau_id] .= '<td>'.mb_substr($tab_user[$groupe_id],0,-6,'UTF-8').'</td>';
      $TF[$niveau_id] .= '<td>'.$nb.' élève'.$s.'</td>';
      $select_eleve .= str_replace( '">' , '">'.$groupe_nom.' - ' , $tab_select[$groupe_id] );
    }
  }
}
$tables_affectations = '';
foreach($tab_niveau_groupe as $niveau_id => $tab_groupe)
{
  if(mb_strlen($TB[$niveau_id])>9)
  {
    $tables_affectations .= '<table class="affectation">';
    $tables_affectations .= '<thead><tr>'.$TH[$niveau_id].'</tr></thead>';
    $tables_affectations .= '<tbody><tr>'.$TB[$niveau_id].'</tr></tbody>';
    $tables_affectations .= '<tfoot><tr>'.$TF[$niveau_id].'</tr></tfoot>';
    $tables_affectations .= '</table>';
  }
}
?>

<div id="table_accueil">
  <p class="astuce">Cliquer sur un nom d'élève.</p>
  <?php echo $tables_affectations ?>
</div>
<div id="zone_action_eleve" class="hide">
  <form action="#" method="post" id="form_choix_eleve">
    <div>
      <button id="go_premier_eleve" type="button" class="go_premier">Premier</button>
      <button id="go_precedent_eleve" type="button" class="go_precedent">Précédent</button>
      <select id="go_selection_eleve" name="go_selection" class="b"><?php echo $select_eleve ?></select>
      <button id="valider_notes" type="button" class="valider">Valider</button>
      <button id="go_suivant_eleve" type="button" class="go_suivant">Suivant</button>
      <button id="go_dernier_eleve" type="button" class="go_dernier">Dernier</button>&nbsp;&nbsp;&nbsp;
      <button id="fermer_zone_action_eleve" type="button" class="retourner">Retour</button><label id="ajax_msg">&nbsp;</label>
    </div>
  </form>
  <hr />
  <form action="#" method="post" id="zone_resultat_eleve" onsubmit="return false">
    <h4>Nom de la série</h4>
    <table class="vm_nug notanet">
      <thead>
        <tr><th>Épreuve</th><th>Référentiel(s)</th><th>Note proposée</th><th>Note enregistrée</th></tr>
      </thead>
      <tbody>
        <tr><td colspan="4"></td></tr>
      </tbody>
    </table>
  </form>
</div>