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

$action     = (isset($_POST['f_action'])) ? clean_texte($_POST['f_action'])  : '';
$groupe_id  = (isset($_POST['f_id']))     ? clean_entier($_POST['f_id'])     : 0;
$niveau     = (isset($_POST['f_niveau'])) ? clean_entier($_POST['f_niveau']) : 0;
$groupe_nom = (isset($_POST['f_nom']))    ? clean_texte($_POST['f_nom'])     : '';

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Ajouter un nouveau groupe de besoin
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
if( ($action=='ajouter') && $niveau && $groupe_nom )
{
	// Vérifier que le nom du groupe est disponible
	if( DB_STRUCTURE_tester_groupe_nom($groupe_nom) )
	{
		exit('Erreur : nom de groupe déjà existant !');
	}
	// Insérer l'enregistrement
	$groupe_id = DB_STRUCTURE_ajouter_groupe('besoin','',$groupe_nom,$niveau);
	// Y associer le prof, en responsable du groupe
	DB_STRUCTURE_modifier_liaison_user_groupe($_SESSION['USER_ID'],'professeur',$groupe_id,'besoin',true);
	DB_STRUCTURE_modifier_liaison_professeur_principal($_SESSION['USER_ID'],$groupe_id,true);
	// Afficher le retour
	echo'<tr id="id_'.$groupe_id.'" class="new">';
	echo	'<td>{{NIVEAU_NOM}}</td>';
	echo	'<td>'.html($groupe_nom).'</td>';
	echo	'<td class="nu">';
	echo		'<q class="modifier" title="Modifier ce groupe de besoin."></q>';
	echo		'<q class="supprimer" title="Supprimer ce groupe de besoin."></q>';
	echo	'</td>';
	echo'</tr>';
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Modifier un groupe de besoin existant
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
else if( ($action=='modifier') && $groupe_id && $niveau && $groupe_nom )
{
	// Vérifier que le nom du groupe est disponible
	if( DB_STRUCTURE_tester_groupe_nom($groupe_nom,$groupe_id) )
	{
		exit('Erreur : nom de groupe de besoin déjà existant !');
	}
	// Mettre à jour l'enregistrement
	DB_STRUCTURE_modifier_groupe($groupe_id,'',$groupe_nom,$niveau);
	// Afficher le retour
	echo'<td>{{NIVEAU_NOM}}</td>';
	echo'<td>'.html($groupe_nom).'</td>';
	echo'<td class="nu">';
	echo	'<q class="modifier" title="Modifier ce groupe de besoin."></q>';
	echo	'<q class="supprimer" title="Supprimer ce groupe de besoin."></q>';
	echo'</td>';
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Supprimer un groupe de besoin existant
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
else if( ($action=='supprimer') && $groupe_id )
{
	// Effacer l'enregistrement
	DB_STRUCTURE_supprimer_groupe($groupe_id,'besoin');
	// Afficher le retour
	echo'<td>ok</td>';
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
