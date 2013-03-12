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
$TITRE = "Archives consultables des bilans officiels";

$tab_types = array
(
  'releve'   => array( 'droit'=>'RELEVE'   , 'titre'=>'Relevé d\'évaluations' ) ,
  'bulletin' => array( 'droit'=>'BULLETIN' , 'titre'=>'Bulletin scolaire'     ) ,
  'palier1'  => array( 'droit'=>'SOCLE'    , 'titre'=>'Maîtrise du palier 1'  ) ,
  'palier2'  => array( 'droit'=>'SOCLE'    , 'titre'=>'Maîtrise du palier 2'  ) ,
  'palier3'  => array( 'droit'=>'SOCLE'    , 'titre'=>'Maîtrise du palier 3'  )
);

$droit_voir_archives_pdf = FALSE;

foreach($tab_types as $BILAN_TYPE => $tab)
{
  $droit_voir_archives_pdf = $droit_voir_archives_pdf || test_user_droit_specifique($_SESSION['DROIT_OFFICIEL_'.$tab['droit'].'_VOIR_ARCHIVE']) ;
  if($BILAN_TYPE=='palier1') break; // car droit commun pour tous les paliers
}

if(!$droit_voir_archives_pdf)
{
  echo'<p class="danger">Vous n\'êtes pas habilité à accéder à cette fonctionnalité !<p>';
  echo'<p class="astuce">Profils autorisés (par les administrateurs) :<p>';
  foreach($tab_types as $BILAN_TYPE => $tab)
  {
    $titre = ($BILAN_TYPE!='palier1') ? $tab['titre'] : 'Maîtrise du socle' ;
    echo'<h4>'.$titre.'</h4>';
    echo afficher_profils_droit_specifique($_SESSION['DROIT_OFFICIEL_'.$tab['droit'].'_VOIR_ARCHIVE'],'li');
    if($BILAN_TYPE=='palier1') break; // car droit commun pour tous les paliers
  }
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

// Récupérer la liste des périodes, dans l'ordre choisi par l'admin.

$DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_periodes();
if(!empty($DB_TAB))
{
  $tab_thead = ($_SESSION['USER_PROFIL_TYPE']=='eleve') ? array(0=>'') : array(0=>'<th class="nu"></th>');
  $tab_tbody = array();
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_thead[$DB_ROW['periode_id']] = '<th class="hc">'.html($DB_ROW['periode_nom']).'</th>';
  }

  // identifiants élèves concernés
  $tab_eleve_id = array();
  if($_SESSION['USER_PROFIL_TYPE']=='eleve')
  {
    $tab_eleve_id[] = $_SESSION['USER_ID'];
    $tab_tbody[$_SESSION['USER_ID']][0] = '';
  }
  else
  {
    foreach($_SESSION['OPT_PARENT_ENFANTS'] as $tab)
    {
      $tab_eleve_id[] = $tab['valeur'];
      $tab_tbody[$tab['valeur']][0] = '<th>'.html($tab['texte']).'</th>';
    }
  }

  // lister les bilans officiels archivés de l'année courante
  $DB_TAB = DB_STRUCTURE_OFFICIEL::DB_lister_bilan_officiel_fichiers( '' /*BILAN_TYPE*/ , 0 /*periode_id*/ , $tab_eleve_id );
  $_SESSION['tmp_droit_voir_archive'] = array(); // marqueur mis en session pour vérifier que c'est bien cet utilisateur qui veut voir (et à donc le droit de voir) le fichier, car il n'y a pas d'autre vérification de droit ensuite
  foreach($DB_TAB as $DB_ROW)
  {
    if(test_user_droit_specifique($_SESSION['DROIT_OFFICIEL_'.$tab_types[$DB_ROW['officiel_type']]['droit'].'_VOIR_ARCHIVE']))
    {
      if(is_file(CHEMIN_DOSSIER_OFFICIEL.$_SESSION['BASE'].DS.fabriquer_nom_fichier_bilan_officiel( $DB_ROW['user_id'] , $DB_ROW['officiel_type'] , $DB_ROW['periode_id'] )))
      {
        $_SESSION['tmp_droit_voir_archive'][$DB_ROW['user_id'].$DB_ROW['officiel_type']] = TRUE; // marqueur mis en session pour vérifier que c'est bien cet utilisateur qui veut voir (et à donc le droit de voir) le fichier, car il n'y a pas d'autre vérification de droit ensuite
        $tab_tbody[$DB_ROW['user_id']][$DB_ROW['periode_id']][] = '<a href="releve_pdf.php?fichier='.$DB_ROW['user_id'].'_'.$DB_ROW['officiel_type'].'_'.$DB_ROW['periode_id'].'" class="lien_ext">'.$tab_types[$DB_ROW['officiel_type']]['titre'].'</a>' ;
      }
    }
  }

  // Assemblage et affichage du tableau.

  echo'<p class="astuce">Ces bilans ne sont que des copies partielles, laissées à disposition pour information jusqu\'à la fin de l\'année scolaire.<br /><span class="u">Seul le document original fait foi.</span></p>';
  echo'<table id="table_bilans"><thead><tr>'.implode('',$tab_thead).'</tr></thead><tbody>'."\r\n";
  unset($tab_thead[0]);
  foreach($tab_eleve_id as $eleve_id)
  {
    echo'<tr>'.$tab_tbody[$eleve_id][0] ;
    foreach($tab_thead as $periode_id => $th)
    {
      echo (isset($tab_tbody[$eleve_id][$periode_id])) ? '<td class="hc">'.implode('<br />',$tab_tbody[$eleve_id][$periode_id]).'</td>' : '<td class="hc">-</td>' ;
    }
      echo'</tr>'."\r\n";
  }
  echo'</tbody></table>';

}
else
{
  echo'<p><label class="erreur">Aucune période n\'a été configurée par les administrateurs !</label></p>';
}

?>
