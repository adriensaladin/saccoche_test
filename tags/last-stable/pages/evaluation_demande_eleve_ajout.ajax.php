<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010-2014
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
if($_SESSION['SESAMATH_ID']==ID_DEMO){exit('Action désactivée pour la démo...');}

$action     = (isset($_POST['f_action']))     ? Clean::texte($_POST['f_action'])      : '';
$matiere_id = (isset($_POST['f_matiere_id'])) ? Clean::entier($_POST['f_matiere_id']) : 0;
$item_id    = (isset($_POST['f_item_id']))    ? Clean::entier($_POST['f_item_id'])    : 0;
$prof_id    = (isset($_POST['f_prof_id']))    ? Clean::entier($_POST['f_prof_id'])    : -1;
$score      = (isset($_POST['f_score']))      ? Clean::entier($_POST['f_score'])      : -2; // normalement entier entre 0 et 100 ou -1 si non évalué
$message    = (isset($_POST['f_message']))    ? Clean::texte($_POST['f_message'])     : '' ;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Lister les profs associés à l'élève et à une matière
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='lister_profs') && $matiere_id )
{
  $DB_TAB = DB_STRUCTURE_DEMANDE::DB_recuperer_professeurs_eleve_matiere( $_SESSION['USER_ID'] , $_SESSION['ELEVE_CLASSE_ID'] , $matiere_id );
  if(empty($DB_TAB))
  {
    exit('Aucun de vos professeurs n\'étant rattaché à cette matière, personne ne pourrait traiter votre demande.');
  }
  else
  {
    $options = (count($DB_TAB)==1) ? '' : '<option value="0">Tous les enseignants concernés</option>' ;
    foreach($DB_TAB as $DB_ROW)
    {
      $options .= '<option value="'.$DB_ROW['user_id'].'">'.html(afficher_identite_initiale($DB_ROW['user_nom'],FALSE,$DB_ROW['user_prenom'],TRUE,$DB_ROW['user_genre'])).'</option>';
    }
    exit($options);
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Un élève confirme l'ajout d'une demande d'évaluation
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='confirmer_ajout') && $matiere_id && $item_id && ($prof_id!==-1) && ($score!==-2) )
{

  // Vérifier que les demandes sont autorisées pour cette matière
  $nb_demandes_autorisees = DB_STRUCTURE_DEMANDE::DB_recuperer_demandes_autorisees_matiere($matiere_id);
  if(!$nb_demandes_autorisees)
  {
    exit('<label class="erreur">Vous ne pouvez pas formuler de demandes pour les items cette matière.</label>');
  }

  // Vérifier qu'il reste des demandes disponibles pour l'élève et la matière concernés
  $nb_demandes_formulees = DB_STRUCTURE_DEMANDE::DB_compter_demandes_formulees_eleve_matiere($_SESSION['USER_ID'],$matiere_id);
  $nb_demandes_possibles = max( 0 , $nb_demandes_autorisees - $nb_demandes_formulees ) ;
  if(!$nb_demandes_possibles)
  {
    $reponse = ($nb_demandes_formulees>1) ? '<label class="erreur">Vous avez déjà formulé les '.$nb_demandes_formulees.' demandes autorisées pour cette matière.</label><br /><a href="./index.php?page=evaluation_demande_eleve">Veuillez en supprimer avant d\'en ajouter d\'autres !</a>' : 'Vous avez déjà formulé la demande autorisée pour cette matière.<br /><a href="./index.php?page=evaluation_demande_eleve">Veuillez la supprimer avant d\'en demander une autre !</a>' ;
    exit($reponse);
  }

  // Vérifier que cet item n'est pas déjà en attente d'évaluation pour cet élève
  if( DB_STRUCTURE_DEMANDE::DB_tester_demande_existante($_SESSION['USER_ID'],$matiere_id,$item_id) )
  {
    exit('<label class="erreur">Cette demande est déjà enregistrée !</label>');
  }

  // Vérifier que cet item n'est pas interdit à la sollitation ; récupérer au passage sa référence et son nom
  $DB_ROW = DB_STRUCTURE_DEMANDE::DB_recuperer_item_infos($item_id);
  if($DB_ROW['item_cart']==0)
  {
    exit('<label class="erreur">La demande de cet item est interdite !</label>');
  }

  // Enregistrement de la demande
  $score = ($score!=-1) ? $score : NULL ;
  $demande_id = DB_STRUCTURE_DEMANDE::DB_ajouter_demande( $_SESSION['USER_ID'] , $matiere_id , $item_id , $prof_id , $score , 'eleve' /*statut*/ , $message );

  // Ajout aux flux RSS des profs concernés
  $titre = 'Demande ajoutée par '.afficher_identite_initiale($_SESSION['USER_NOM'],FALSE,$_SESSION['USER_PRENOM'],TRUE);
  $texte = $_SESSION['USER_PRENOM'].' '.$_SESSION['USER_NOM'].' ajoute la demande '.$DB_ROW['item_ref'].' "'.$DB_ROW['item_nom'].'"';
  $texte.= ($message) ? ' avec ce message : '."\r\n".$message : '' ;
  $guid  = 'demande_'.$demande_id.'_add';
  if($prof_id)
  {
    RSS::modifier_fichier_prof($prof_id,$titre,$texte,$guid);
  }
  else
  {
    // On récupère les profs...
    $DB_TAB = DB_STRUCTURE_DEMANDE::DB_recuperer_professeurs_eleve_matiere( $_SESSION['USER_ID'] , $_SESSION['ELEVE_CLASSE_ID'] , $matiere_id );
    if(!empty($DB_TAB))
    {
      foreach($DB_TAB as $DB_ROW)
      {
        RSS::modifier_fichier_prof($DB_ROW['user_id'],$titre,$texte,$guid);
      }
    }
  }

  // Affichage du retour
  $nb_demandes_formulees++;
  $nb_demandes_possibles--;
  $s = ($nb_demandes_possibles>1) ? 's' : '' ;
  echo'<label class="valide">Votre demande a été ajoutée.</label><br />';
  echo($nb_demandes_possibles==0) ? 'Vous ne pouvez plus formuler d\'autres demandes pour cette matière.' : 'Vous pouvez encore formuler '.$nb_demandes_possibles.' demande'.$s.' pour cette matière.' ;
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

exit('Erreur avec les données transmises !');


?>
