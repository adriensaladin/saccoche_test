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
if($_SESSION['SESAMATH_ID']==ID_DEMO){exit('Action désactivée pour la démo...');}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Un élève confirme l'ajout d'une demande d'évaluation
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// Récupérer et vérifier les données transmises
$matiere_id = (isset($_POST['matiere_id'])) ? Clean::entier($_POST['matiere_id']) : 0;
$item_id    = (isset($_POST['item_id']))    ? Clean::entier($_POST['item_id'])    : 0;
$score      = (isset($_POST['score']))      ? Clean::entier($_POST['score'])      : -2; // normalement entier entre 0 et 100 ou -1 si non évalué
$message    = (isset($_POST['message']))    ? Clean::texte($_POST['message'])     : '' ;
if( ($matiere_id==0) || ($item_id==0) || ($score==-2) )
{
  exit('Erreur avec les données transmises !');
}

// Vérifier que les demandes sont autorisées pour cette matière
$nb_demandes_autorisees = DB_STRUCTURE_ELEVE::DB_recuperer_demandes_autorisees_matiere($matiere_id);
if(!$nb_demandes_autorisees)
{
  exit('<label class="erreur">Vous ne pouvez pas formuler de demandes pour les items cette matière.</label>');
}

// Vérifier qu'il reste des demandes disponibles pour l'élève et la matière concernés
$nb_demandes_formulees = DB_STRUCTURE_ELEVE::DB_compter_demandes_formulees_eleve_matiere($_SESSION['USER_ID'],$matiere_id);
$nb_demandes_possibles = max( 0 , $nb_demandes_autorisees - $nb_demandes_formulees ) ;
if(!$nb_demandes_possibles)
{
  $reponse = ($nb_demandes_formulees>1) ? '<label class="erreur">Vous avez déjà formulé les '.$nb_demandes_formulees.' demandes autorisées pour cette matière.</label><br /><a href="./index.php?page=evaluation_demande_eleve">Veuillez en supprimer avant d\'en ajouter d\'autres !</a>' : 'Vous avez déjà formulé la demande autorisée pour cette matière.<br /><a href="./index.php?page=evaluation_demande_eleve">Veuillez la supprimer avant d\'en demander une autre !</a>' ;
  exit($reponse);
}

// Vérifier que cet item n'est pas déjà en attente d'évaluation pour cet élève
if( DB_STRUCTURE_ELEVE::DB_tester_demande_existante($_SESSION['USER_ID'],$matiere_id,$item_id) )
{
  exit('<label class="erreur">Cette demande est déjà enregistrée !</label>');
}

// Vérifier que cet item n'est pas interdit à la sollitation ; récupérer au passage sa référence et son nom
$DB_ROW = DB_STRUCTURE_ELEVE::DB_recuperer_item_infos($item_id);
if($DB_ROW['item_cart']==0)
{
  exit('<label class="erreur">La demande de cet item est interdite !</label>');
}

// Enregistrement de la demande
$score = ($score!=-1) ? $score : NULL ;
$demande_id = DB_STRUCTURE_ELEVE::DB_ajouter_demande($_SESSION['USER_ID'],$matiere_id,$item_id,$score,$statut='eleve',$message);

// Ajout aux flux RSS des profs concernés
$titre = 'Demande ajoutée par '.$_SESSION['USER_NOM'].' '.$_SESSION['USER_PRENOM']{0}.'.';
$texte = $_SESSION['USER_PRENOM'].' '.$_SESSION['USER_NOM'].' ajoute la demande '.$DB_ROW['item_ref'].' "'.$DB_ROW['item_nom'].'"';
$texte.= ($message) ? ' avec ce message : '."\r\n".$message : '' ;
$guid  = 'demande_'.$demande_id.'_add';
// On récupère les profs...
$DB_COL = DB_STRUCTURE_ELEVE::DB_recuperer_professeurs_eleve_matiere($_SESSION['USER_ID'],$matiere_id);
foreach($DB_COL as $prof_id)
{
  RSS::modifier_fichier_prof($prof_id,$titre,$texte,$guid);
}

// Affichage du retour
$nb_demandes_formulees++;
$nb_demandes_possibles--;
$s = ($nb_demandes_possibles>1) ? 's' : '' ;
echo'<label class="valide">Votre demande a été ajoutée.</label><br />';
echo($nb_demandes_possibles==0) ? 'Vous ne pouvez plus formuler d\'autres demandes pour cette matière.' : 'Vous pouvez encore formuler '.$nb_demandes_possibles.' demande'.$s.' pour cette matière.' ;
exit();

?>
