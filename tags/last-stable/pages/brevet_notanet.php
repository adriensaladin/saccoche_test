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
$TITRE = "Étape n°4 - Générer le fichier d'export pour Notanet";

// Test présence INE
$nb_eleves_sans_INE = DB_STRUCTURE_BREVET::DB_compter_eleves_actuels_sans_INE();
$s = ($nb_eleves_sans_INE>1) ? 's' : '' ;
$msg_INE = (!$nb_eleves_sans_INE) ? '<label class="valide">Identifiants élèves présents.</label>' : '<label class="alerte">'.$nb_eleves_sans_INE.' élève'.$s.' (par forcément parmi ceux ci-dessous) trouvé'.$s.' dans la base sans Identifiant National Élève (INE).</label> <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_administrateur__import_users_sconet">DOC</a></span>' ;
?>

<p>
  <span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=releves_bilans__notanet_fiches_brevet#toggle_etape4_notanet">DOC : Notanet &amp; Fiches brevet &rarr; Export vers Notanet</a></span><br />
  <?php echo $msg_INE ?>
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
$tab_niveau_groupe[0][0] = 'sans classe';
$tab_user[0]             = '';

// Récupérer la liste des classes
$DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_classes_avec_niveaux($niveau_ordre='DESC');
foreach($DB_TAB as $DB_ROW)
{
  $tab_niveau_groupe[$DB_ROW['niveau_id']][$DB_ROW['groupe_id']] = html($DB_ROW['groupe_nom']);
  $tab_user[  $DB_ROW['groupe_id']] = '';
}
// Récupérer la liste des élèves, avec classe, série de brevet, et total des points (si existant)
$nb_eleves = 0;
$DB_TAB = DB_STRUCTURE_BREVET::DB_lister_brevet_eleves_avec_serie_et_total();
foreach($DB_TAB as $DB_ROW)
{
  if($DB_ROW['saisie_note']!==NULL)
  {
    $tab_user[$DB_ROW['eleve_classe_id']] .= '<label for="user_'.$DB_ROW['user_id'].'"><img src="./_img/brevet/serie_'.$DB_ROW['eleve_brevet_serie'].'.png" alt="" title="'.$tab_brevet_series[$DB_ROW['eleve_brevet_serie']].'" /> <input type="checkbox" id="user_'.$DB_ROW['user_id'].'" name="f_eleve" value="'.$DB_ROW['user_id'].'" checked /> '.html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']).'</label><br />';
    $nb_eleves++;
  }
  else
  {
    $tab_user[$DB_ROW['eleve_classe_id']] .= '<img src="./_img/brevet/serie_'.$DB_ROW['eleve_brevet_serie'].'.png" alt="" title="'.$tab_brevet_series[$DB_ROW['eleve_brevet_serie']].'" /> <input type="checkbox" id="user_'.$DB_ROW['user_id'].'" name="f_eleve" value="'.$DB_ROW['user_id'].'" disabled /> <del>'.html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']).'</del><br />';
  }
}
if(!$nb_eleves)
{
  echo'<p class="danger">Aucun élève n\'a de notes enregistrées pour le Brevet !<p>'.NL;
  echo'<div class="astuce"><a href="./index.php?page=brevet&amp;section=moyennes">Effectuer l\'étape n°3.</a><div>'.NL;
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

// Assemblage du tableau résultant
$TH = array();
$TB = array();
$TF = array();
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
      $TH[$niveau_id] .= '<th id="for_'.$groupe_id.'">'.$groupe_nom.'<q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q></th>';
      $TB[$niveau_id] .= '<td id="groupe_'.$groupe_id.'">'.mb_substr($tab_user[$groupe_id],0,-6,'UTF-8').'</td>';
      $TF[$niveau_id] .= '<td>'.$nb.' élève'.$s.'</td>';
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
  <?php echo $tables_affectations ?>
</div>

<form action="#" method="post">
  <p class="ti"><button type="button" id="export_notanet" class="fichier_export">Générer le fichier d'export vers Notanet.</button><label id="ajax_msg">&nbsp;</label></p>
</form>

<div id="ajax_info" class="hide">
  <h4>Fichier à importer dans Notanet</h4>
  <ul class="puce">
    <li><a id="lien_notanet" class="lien_ext" href=""><span class="file file_txt">Récupérer le fichier au format <em>txt</em>.</span></a></li>
  </ul>
  <p><label class="alerte">Pour des raisons de sécurité et de confidentialité, ce fichier sera effacé du serveur dans 1h.</label></p>
</div>
