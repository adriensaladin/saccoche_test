<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010
 * 
 * ****************************************************************************************************
 * SACoche <http://competences.sesamath.net> - Suivi d'Acquisitions de Compétences
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

$action = (isset($_POST['f_action'])) ? clean_texte($_POST['f_action']) : '';
$id     = (isset($_POST['f_id']))     ? clean_entier($_POST['f_id'])    : 0;
$ordre  = (isset($_POST['f_ordre']))  ? clean_entier($_POST['f_ordre']) : 0;
$nom    = (isset($_POST['f_nom']))    ? clean_texte($_POST['f_nom'])    : '';

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Ajouter une nouvelle zone / Dupliquer une pédiode existante
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
if( (($action=='ajouter')||($action=='dupliquer')) && $ordre )
{
	// Vérifier que le nom de la zone est disponible
	if( DB_tester_zone_nom($nom) )
	{
		exit('Erreur : nom de zone déjà existant !');
	}
	// Insérer l'enregistrement
	$geo_id = DB_ajouter_zone($ordre,$nom);
	// Afficher le retour
	echo'<tr id="id_'.$geo_id.'" class="new">';
	echo	'<td>'.$geo_id.'</td>';
	echo	'<td>'.$ordre.'</td>';
	echo	'<td>'.html($nom).'</td>';
	echo	'<td class="nu">';
	echo		'<q class="modifier" title="Modifier cette zone."></q>';
	echo		'<q class="dupliquer" title="Dupliquer cette zone."></q>';
	echo		'<q class="supprimer" title="Supprimer cette zone."></q>';
	echo	'</td>';
	echo'</tr>';
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Modifier une zone existante
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
else if( ($action=='modifier') && $id && $ordre && $nom )
{
	// Vérifier que le nom de la zone est disponible
	if( DB_tester_zone_nom($nom,$id) )
	{
		exit('Erreur : nom de zone déjà existant !');
	}
	// Mettre à jour l'enregistrement
	DB_modifier_zone($id,$ordre,$nom);
	// Afficher le retour
	echo'<td>'.$ordre.'</td>';
	echo'<td>'.html($nom).'</td>';
	echo'<td class="nu">';
	echo	'<q class="modifier" title="Modifier cette zone."></q>';
	echo	'<q class="dupliquer" title="Dupliquer cette zone."></q>';
	// La zone d'id 1 ne peut être supprimée, c'est la zone par défaut.
	echo ($id!=1) ? '<q class="supprimer" title="Supprimer cette zone."></q>' : '<q class="supprimer_non" title="La zone par défaut ne peut pas être supprimée."></q>' ;
	echo'</td>';
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Supprimer une zone existante
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
else if( ($action=='supprimer') && ($id>1) )
{
	// Effacer l'enregistrement
	DB_supprimer_zone($id);
	// Afficher le retour
	echo'<td>ok</td>';
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
