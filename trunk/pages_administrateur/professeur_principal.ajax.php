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
if($_SESSION['STRUCTURE_ID']==ID_DEMO) {exit('Action désactivée pour la démo...');}

$action = (isset($_POST['f_action'])) ? clean_texte($_POST['f_action']) : '';
$tab_id = (isset($_POST['tab_id']))   ? explode(',',$_POST['tab_id'])   : array() ;

if($action=='Indiquer')
{
	// Il faut comparer avec le contenu de la base pour ne mettre à jour que ce dont il y a besoin
	$tab_ajouter = array();
	$tab_retirer = array();

	// On récupère les données transmises dans $tab_ajouter
	foreach($tab_id as $ids)
	{
		$tab = explode('x',$ids);
		if(count($tab)==2)
		{
			$groupe_id     = clean_entier($tab[0]);
			$professeur_id = clean_entier($tab[1]);
			if( $groupe_id && $professeur_id )
			{
				$tab_ajouter[$groupe_id.'x'.$professeur_id] = true;
			}
		}
	}

	// On récupère le contenu de la base déjà enregistré pour le comparer ; il faut éviter les professeurs désactivés
	$DB_TAB = DB_lister_jointure_professeurs_principaux();
	foreach($DB_TAB as $DB_ROW)
	{
		$key = $DB_ROW['groupe_id'].'x'.$DB_ROW['user_id'];
		if(isset($tab_ajouter[$key]))
		{
			// valeur dans la base et dans le post : ne rien changer (ne pas l'ajouter)
			unset($tab_ajouter[$key]);
		}
		else
		{
			// valeur dans la base mais pas dans le post
			$tab_retirer[$key] = true;
		}
	}

	// Il n'y a plus qu'à mettre à jour la base
	if( count($tab_ajouter) || count($tab_retirer) )
	{
		foreach($tab_ajouter as $key => $true)
		{
			list($groupe_id,$professeur_id) = explode('x',$key);
			DB_modifier_liaison_professeur_principal($professeur_id,$groupe_id,true);
		}
		foreach($tab_retirer as $key => $true)
		{
			list($groupe_id,$professeur_id) = explode('x',$key);
			DB_modifier_liaison_professeur_principal($professeur_id,$groupe_id,false);
		}
		echo'ok';
	}
	else
	{
		echo'Aucune modification détectée !';
	}
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
