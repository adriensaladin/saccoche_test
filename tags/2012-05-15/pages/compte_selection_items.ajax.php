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

$action        = (isset($_POST['f_action']))  ? clean_texte($_POST['f_action'])  : '';
$selection_id  = (isset($_POST['f_id']))      ? clean_entier($_POST['f_id'])     : 0;
$selection_nom = (isset($_POST['f_nom']))     ? clean_texte($_POST['f_nom'])     : '';
$origine       = (isset($_POST['f_origine'])) ? clean_texte($_POST['f_origine']) : '';

// Contrôler la liste des items transmis
$tab_items = (isset($_POST['f_compet_liste'])) ? explode('_',$_POST['f_compet_liste']) : array() ;
$tab_items = array_map('clean_entier',$tab_items);
$tab_items = array_filter($tab_items,'positif');
$nb_items = count($tab_items);

//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	Ajouter une nouvelle sélection d'items
//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='ajouter') && $selection_nom && $nb_items && $origine )
{
	// Vérifier que le nom de la sélection d'items est disponible
	if( DB_STRUCTURE_PROFESSEUR::DB_tester_selection_items_nom($_SESSION['USER_ID'],$selection_nom) )
	{
		exit('Erreur : nom de la sélection d\'items déjà pris !');
	}
	// Insérer l'enregistrement ; y associe automatiquement le prof, en responsable du groupe
	$selection_id = DB_STRUCTURE_PROFESSEUR::DB_ajouter_selection_items($_SESSION['USER_ID'],$selection_nom,$tab_items);
	// Afficher le retour
	if($origine==$PAGE)
	{
		$items_texte  = ($nb_items>1) ? $nb_items.' items' : '1 item' ;
		echo'<tr id="id_'.$selection_id.'" class="new">';
		echo	'<td>'.html($selection_nom).'</td>';
		echo	'<td>'.$items_texte.'</td>';
		echo	'<td class="nu">';
		echo		'<q class="modifier" title="Modifier cette sélection d\'items."></q>';
		echo		'<q class="supprimer" title="Supprimer cette sélection d\'items."></q>';
		echo	'</td>';
		echo'</tr>';
		echo'<SCRIPT>';
		echo'tab_items["'.$selection_id.'"]="'.implode('_',$tab_items).'";';
	}
	else
	{
		echo'<option value="'.implode('_',$tab_items).'">'.html($selection_nom).'</option>';
	}
	exit();
}

//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	Modifier une sélection d'items existante
//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='modifier') && $selection_id && $selection_nom && $nb_items )
{
	// Vérifier que le nom de la sélection d'items est disponible
	if( DB_STRUCTURE_PROFESSEUR::DB_tester_selection_items_nom($_SESSION['USER_ID'],$selection_nom,$selection_id) )
	{
		exit('Erreur : nom de sélection d\'items déjà existant !');
	}
	// Mettre à jour l'enregistrement
	DB_STRUCTURE_PROFESSEUR::DB_modifier_selection_items($selection_id,$selection_nom,$tab_items);
	// Afficher le retour
	$items_texte  = ($nb_items>1) ? $nb_items.' items' : '1 item' ;
	echo'<td>'.html($selection_nom).'</td>';
	echo'<td>'.$items_texte.'</td>';
	echo'<td class="nu">';
	echo	'<q class="modifier" title="Modifier cette sélection d\'items."></q>';
	echo	'<q class="supprimer" title="Supprimer cette sélection d\'items."></q>';
	echo'</td>';
	echo'<SCRIPT>';
	echo'tab_items["'.$selection_id.'"]="'.implode('_',$tab_items).'";';
	exit();
}

//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	Supprimer une sélection d'items existante
//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='supprimer') && $selection_id )
{
	// Effacer l'enregistrement
	DB_STRUCTURE_PROFESSEUR::DB_supprimer_selection_items($selection_id);
	// Afficher le retour
	exit('<td>ok</td>');
}

//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	On ne devrait pas en arriver là !
//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

exit('Erreur avec les données transmises !');

?>
