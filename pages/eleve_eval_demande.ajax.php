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
if($_SESSION['SESAMATH_ID']==ID_DEMO) {exit('Action désactivée pour la démo...');}

$action     = (isset($_POST['f_action']))     ? clean_texte($_POST['f_action'])      : '';
$demande_id = (isset($_POST['f_demande_id'])) ? clean_entier($_POST['f_demande_id']) : 0;
$item_id    = (isset($_POST['f_item_id']))    ? clean_entier($_POST['f_item_id'])    : 0;
$matiere_id = (isset($_POST['f_matiere_id'])) ? clean_entier($_POST['f_matiere_id']) : 0;

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Supprimer une demande
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='supprimer') && $demande_id && $item_id && $matiere_id )
{
	DB_STRUCTURE_ELEVE::DB_supprimer_demande_precise($demande_id);
	// Récupérer la référence et le nom de l'item
	$DB_ROW = DB_STRUCTURE_ELEVE::DB_recuperer_item_infos($item_id);
	// Ajout aux flux RSS des profs concernés
	$titre = 'Demande retirée par '.$_SESSION['USER_NOM'].' '.$_SESSION['USER_PRENOM']{0}.'.';
	$texte = $_SESSION['USER_PRENOM'].' '.$_SESSION['USER_NOM'].' retire sa demande '.$DB_ROW['item_ref'].' "'.$DB_ROW['item_nom'].'"';
	$guid  = 'demande_'.$demande_id.'_del';
	// On récupère les profs...
	$DB_COL = DB_STRUCTURE_ELEVE::DB_recuperer_professeurs_eleve_matiere($_SESSION['USER_ID'],$matiere_id);
	foreach($DB_COL as $prof_id)
	{
		Modifier_RSS(adresse_RSS($prof_id),$titre,$texte,$guid);
	}
	// Affichage du retour
	exit('ok');
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	On ne devrait pas en arriver là...
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

exit('Erreur avec les données transmises !');

?>
