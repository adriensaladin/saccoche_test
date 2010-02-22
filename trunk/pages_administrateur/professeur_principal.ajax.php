<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2009
 * 
 * ****************************************************************************************************
 * SACoche [http://competences.sesamath.net] - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath [http://www.sesamath.net]
 * Distribution sous licence libre prévue pour l'été 2010.
 * ****************************************************************************************************
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
	$DB_SQL = 'SELECT livret_user_id,livret_groupe_id FROM livret_jointure_user_groupe ';
	$DB_SQL.= 'LEFT JOIN livret_user USING (livret_structure_id,livret_user_id) ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_jointure_pp=:pp AND livret_user_statut=:statut ';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':pp'=>1,':statut'=>1);
	$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	foreach($DB_TAB as $DB_ROW)
	{
		$key = $DB_ROW['livret_groupe_id'].'x'.$DB_ROW['livret_user_id'];
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
			DB_modifier_liaison_professeur_principal($_SESSION['STRUCTURE_ID'],$professeur_id,$groupe_id,true);
		}
		foreach($tab_retirer as $key => $true)
		{
			list($groupe_id,$professeur_id) = explode('x',$key);
			DB_modifier_liaison_professeur_principal($_SESSION['STRUCTURE_ID'],$professeur_id,$groupe_id,false);
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
