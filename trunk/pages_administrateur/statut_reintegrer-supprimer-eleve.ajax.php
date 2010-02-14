<?php
/**
 * @version $Id: statut_reintegrer-supprimer-eleve.ajax.php 8 2009-10-30 20:56:02Z thomas $
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
if(($_SESSION['STRUCTURE_ID']==ID_DEMO)&&($_GET['action']!='initialiser')){exit('Action désactivée pour la démo...');}

$action = (isset($_GET['action'])) ? $_GET['action'] : '';
$tab_select_users   = (isset($_POST['select_users']))   ? array_map('clean_entier',explode(',',$_POST['select_users']))   : array() ;

function positif($n) {return($n);}
$tab_select_users   = array_filter($tab_select_users,'positif');
$nb = count($tab_select_users);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Réintégrer des élèves
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='reintegrer') && $nb )
{
	foreach($tab_select_users as $user_id)
	{
		// Mettre à jour l'enregistrement
		$DB_SQL = 'UPDATE livret_user ';
		$DB_SQL.= 'SET livret_user_statut=:statut ';
		$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_id=:user_id ';
		$DB_SQL.= 'LIMIT 1';
		$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':user_id'=>$user_id,':statut'=>1);
		DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	}
	// Afficher le retour
	$s = ($nb>1) ? 's' : '';
	echo'<hr />'.$nb.' élève'.$s.' réintégré'.$s.'.';
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Supprimer des élèves
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

elseif( ($action=='supprimer') && $nb )
{
	foreach($tab_select_users as $user_id)
	{
		// Effacer l'enregistrement
		$DB_SQL = 'DELETE FROM livret_user ';
		$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_id=:user_id ';
		$DB_SQL.= 'LIMIT 1';
		$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':user_id'=>$user_id);
		DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
		$DB_SQL = 'DELETE FROM livret_jointure_user_groupe ';
		$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_id=:user_id';
		$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':user_id'=>$user_id);
		DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
		$DB_SQL = 'DELETE FROM livret_saisie ';
		$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_eleve_id=:user_id';
		$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':user_id'=>$user_id);
		DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	}
	// Afficher le retour
	$s = ($nb>1) ? 's' : '';
	echo'<hr />'.$nb.' élève'.$s.' supprimé'.$s.'.';
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
