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

$matiere_id = (isset($_GET['matiere_id'])) ? clean_entier($_GET['matiere_id']) : 0;
$item_id    = (isset($_GET['item_id']))    ? clean_entier($_GET['item_id'])    : 0;
$score      = (isset($_GET['score']))      ? clean_entier($_GET['score'])      : -2; // normalement entier entre 0 et 100 ou -1 si non évalué

function renvoyer_reponse($reponse)
{
	echo'<form action="#" method="post" id="form_calque">';
	echo'	<div style="float:right"><input id="fermer_calque" type="image" src="./_img/fermer.gif" name="fermer" value="Fermer" /></div>';
	echo'	<div>'.$reponse.'</div>';
	echo'</form>';
	exit();
}

// Un élève demande souhaite ajouter une demande d'évaluation.
if( ($matiere_id==0) || ($item_id==0) || ($score==-2) )
{
	renvoyer_reponse('Erreur avec les données transmises !');
}

// Vérifier que les demandes sont autorisées pour cette matière
$nb_demandes_autorisees = DB_STRUCTURE_recuperer_demandes_autorisees_matiere($matiere_id);
if(!$nb_demandes_autorisees)
{
	renvoyer_reponse('Vous ne pouvez pas formuler de demandes pour les items cette matière.');
}

// Vérifier qu'il reste des demandes disponibles pour l'élève et la matière concernés
$nb_demandes_formulees = DB_STRUCTURE_compter_demandes_formulees_eleve_matiere($_SESSION['USER_ID'],$matiere_id);
$nb_demandes_possibles = max( 0 , $nb_demandes_autorisees - $nb_demandes_formulees ) ;
if(!$nb_demandes_possibles)
{
	$reponse = ($nb_demandes_formulees>1) ? 'Vous avez déjà formulé les '.$nb_demandes_formulees.' demandes autorisées pour cette matière.<br /><a href="./index.php?page=eleve_eval_demande">Veuillez en supprimer pour en ajouter d\'autres !</a>' : 'Vous avez déjà formulé la demande autorisée pour cette matière.<br /><a href="./index.php?page=eleve_eval_demande">Veuillez la supprimer pour en demander une autre !</a>' ;
	renvoyer_reponse($reponse);
}

// Vérifier que cet item n'est pas déjà en attente d'évaluation pour cet élève
if( DB_STRUCTURE_tester_demande_existante($_SESSION['USER_ID'],$matiere_id,$item_id) )
{
	renvoyer_reponse('Cette demande est déjà enregistrée !');
}

// Vérifier que cet item n'est pas interdit à la sollitation ; récupérer au passage sa référence et son nom
$DB_ROW = DB_STRUCTURE_recuperer_item_infos($item_id);
if($DB_ROW['item_cart']==0)
{
	renvoyer_reponse('La demande de cet item est interdite !');
}

// C'est bon si on arrive jusque là... => Enregistrement
$score = ($score!=-1) ? $score : NULL ;
$date_mysql = date("Y-m-d");	// date_mysql de la forme aaaa-mm-jj
$demande_id = DB_STRUCTURE_ajouter_demande($_SESSION['USER_ID'],$matiere_id,$item_id,$date_mysql,$score,$statut='eleve');
// Ajout aux flux RSS des profs concernés
$titre = 'Demande ajoutée par '.$_SESSION['USER_NOM'].' '.$_SESSION['USER_PRENOM']{0}.'.';
$texte = $_SESSION['USER_PRENOM'].' '.$_SESSION['USER_NOM'].' demande à être évalué sur l\'item '.$DB_ROW['item_ref'].' intitulé "'.$DB_ROW['item_nom'].'"';
$guid  = 'demande_'.$demande_id.'_add';
// On récupère les profs...
$DB_COL = DB_STRUCTURE_recuperer_professeurs_eleve_matiere($_SESSION['USER_ID'],$matiere_id);
foreach($DB_COL as $prof_id)
{
	Modifier_RSS(adresse_RSS($prof_id),$titre,$texte,$guid);
}
// Affichage du retour
$nb_demandes_formulees++;
$nb_demandes_possibles--;
$s = ($nb_demandes_possibles>1) ? 's' : '' ;
$reponse  = 'Votre demande a été ajoutée.<br />';
$reponse .= ($nb_demandes_possibles==0) ? 'Vous ne pouvez plus formuler d\'autres demandes pour cette matière.' : 'Vous pouvez encore formuler '.$nb_demandes_possibles.' demande'.$s.' pour cette matière.' ;
renvoyer_reponse($reponse);

?>
