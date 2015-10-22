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
if(($_SESSION['SESAMATH_ID']==ID_DEMO)&&($_POST['action']!='calculer')){exit('Action désactivée pour la démo...');}

$action = (isset($_POST['action'])) ? $_POST['action'] : '';

// Valeur d'un code (sur 100)
$pb_note = FALSE;
$note_valeur = array();
foreach( $_SESSION['NOTE_ACTIF'] as $note_id )
{
  if(isset($_POST['N'.$note_id]))
  {
    $note_valeur[$note_id] = Clean::entier($_POST['N'.$note_id]);
  }
  else
  {
    $pb_note = TRUE;
  }
}

// Seuils d'acquisition (de 0 à 100)
$pb_acquis = FALSE;
$acquis_seuil = array();
foreach( $_SESSION['ACQUIS'] as $acquis_id => $tab_acquis_info )
{
  if( isset($_POST['A'.$acquis_id.'min']) && isset($_POST['A'.$acquis_id.'max']) )
  {
    $acquis_seuil[$acquis_id] = array(
      'SEUIL_MIN' => Clean::entier($_POST['A'.$acquis_id.'min']),
      'SEUIL_MAX' => Clean::entier($_POST['A'.$acquis_id.'max']),
    );
  }
  else
  {
    $pb_acquis = TRUE;
  }
}

// Méthode de calcul
$methode    = (isset($_POST['f_methode']))    ? Clean::calcul_methode($_POST['f_methode'])        : NULL ;
$limite     = (isset($_POST['f_limite']))     ? Clean::calcul_limite($_POST['f_limite'],$methode) : NULL ;
$retroactif = (isset($_POST['f_retroactif'])) ? Clean::calcul_retroactif($_POST['f_retroactif'])  : NULL ;

// Vérification des données transmises
if( $pb_note || $pb_acquis || is_null($methode) || is_null($limite) || is_null($retroactif) )
{
  exit('Erreur avec les données transmises !');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Simuler avec des paramètres donnés
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='calculer')
{
  $type_calcul = (in_array($methode,array('geometrique','arithmetique','classique'))) ? 'moyenne' : 'bestof' ;
  $nb_devoirs_total = 4;
  $nb_lignes_total = pow($_SESSION['NOMBRE_CODES_NOTATION'],$nb_devoirs_total);
  $tab_lignes = array();
  $tab_lignes[1] = '';
  $tab_lignes = array_pad($tab_lignes,$nb_lignes_total,'');
  for($nb_devoirs=1;$nb_devoirs<=$nb_devoirs_total;$nb_devoirs++)
  {
    $nb_cas = pow($_SESSION['NOMBRE_CODES_NOTATION'],$nb_devoirs);
    for($cas=0;$cas<$nb_cas;$cas++)
    {
      // Initialisation
      if($type_calcul=='moyenne')
      {
        $somme_point = 0;
        $somme_coef = 0;
        $coef = 1;
      }
      elseif($type_calcul=='bestof')
      {
        $tab_notes = array();
        $nb_best = (int)substr($methode,-1);
      }
      $masque = sprintf('%0'.$nb_devoirs.'u',base_convert($cas,10,$_SESSION['NOMBRE_CODES_NOTATION']));
      // Pour chaque devoir (note)...
      for($num_devoir=1;$num_devoir<=$nb_devoirs;$num_devoir++)
      {
        $code = $_SESSION['NOTE_ACTIF'][$masque{$num_devoir-1}];
        $tab_lignes[$cas] .= '<td><img alt="" src="'.$_SESSION['NOTE'][$code]['FICHIER'].'" /></td>';
        // Si on prend ce devoir en compte
        if( ($limite==0) || ($nb_devoirs-$num_devoir<$limite) )
        {
          if($type_calcul=='moyenne')
          {
            $somme_point += $note_valeur[$code]*$coef;
            $somme_coef += $coef;
            // Calcul du coef de l'éventuel devoir suivant
            $coef = ($methode=='geometrique') ? $coef*2 : ( ($methode=='arithmetique') ? $coef+1 : 1 ) ;
          }
          elseif($type_calcul=='bestof')
          {
            $tab_notes[] = $note_valeur[$code];
          }
        }
      }
      // Calcul final du score
      if($type_calcul=='moyenne')
      {
        $score = round( $somme_point/$somme_coef , 0 );
      }
      elseif($type_calcul=='bestof')
      {
        rsort($tab_notes);
        $tab_notes = array_slice( $tab_notes , 0 , $nb_best );
        $score = round( array_sum($tab_notes)/count($tab_notes) , 0 );
      }
      // Ligne retournée
      $bg = 'A'.determiner_etat_acquisition( $score , $acquis_seuil );
      $tab_lignes[$cas] .= '<td class="'.$bg.'">'.$score.'</td>';
      if( ($cas==0) && ($nb_devoirs!=$nb_devoirs_total) )
      {
        $tab_lignes[$cas] .= '<td rowspan="'.$nb_lignes_total.'"></td>';
      }
    }
  }

  // Cette fin serait à adapter en cas de modification de $nb_devoirs_total...
  $nb_lignes_3_devoirs = pow($_SESSION['NOMBRE_CODES_NOTATION'],3);
  $nb_lignes_2_devoirs = pow($_SESSION['NOMBRE_CODES_NOTATION'],2);
  foreach($tab_lignes as $cas => $ligne)
  {
    $nb_td_manquant = 14 - substr_count($ligne,'<td');
    echo'<tr>';
    if($nb_td_manquant>0)
    {
          if($cas>=$nb_lignes_3_devoirs) {$nb_td_manquant+=2;}
      elseif($cas>=$nb_lignes_2_devoirs) {$nb_td_manquant+=1;}
      echo'<td colspan="'.$nb_td_manquant.'"></td>';
    }
    echo $ligne;
    echo'</tr>';
  }
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là !
// ////////////////////////////////////////////////////////////////////////////////////////////////////

exit('Erreur avec les données transmises !');

?>
