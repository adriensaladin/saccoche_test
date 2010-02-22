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
if(($_SESSION['STRUCTURE_ID']==ID_DEMO)&&($_GET['action']!='initialiser')){exit('Action désactivée pour la démo...');}

$action = (isset($_GET['action'])) ? $_GET['action'] : '';
$tab_select_users   = (isset($_POST['select_users']))   ? array_map('clean_entier',explode(',',$_POST['select_users']))   : array() ;
$tab_select_groupes = (isset($_POST['select_groupes'])) ? array_map('clean_entier',explode(',',$_POST['select_groupes'])) : array() ;

function positif($n) {return($n);}
$tab_select_users   = array_filter($tab_select_users,'positif');
$tab_select_groupes = array_filter($tab_select_groupes,'positif');

// Ajouter des élèves à des groupes de besoin
if($action=='ajouter')
{
	foreach($tab_select_users as $user_id)
	{
		foreach($tab_select_groupes as $groupe_id)
		{
			$DB_SQL = 'REPLACE INTO livret_jointure_user_groupe (livret_structure_id,livret_user_id,livret_groupe_id) ';
			$DB_SQL.= 'VALUES(:structure_id,:user_id,:groupe_id)';
			$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':user_id'=>$user_id,':groupe_id'=>$groupe_id);
			DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
		}
	}
}

// Retirer des élèves à des groupes de besoin
elseif($action=='retirer')
{
	foreach($tab_select_users as $user_id)
	{
		foreach($tab_select_groupes as $groupe_id)
		{
			$DB_SQL = 'DELETE FROM livret_jointure_user_groupe ';
			$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_id=:user_id AND livret_groupe_id=:groupe_id ';
			$DB_SQL.= 'LIMIT 1';
			$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':user_id'=>$user_id,':groupe_id'=>$groupe_id);
			DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
		}
	}
}

// Affichage du bilan des affectations des élèves dans les groupes de besoin ; en deux requêtes pour récupérer les élèves sans groupes de besoin et les groupes de besoin sans élèves
$tab_niveau_groupe = array();
$tab_user          = array();
// Récupérer la liste des groupes de besoin
$DB_SQL = 'SELECT * FROM livret_groupe ';
$DB_SQL.= 'LEFT JOIN livret_niveau USING (livret_niveau_id) ';
$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_groupe_prof_id=:user_id AND livret_groupe_type=:type ';
$DB_SQL.= 'ORDER BY livret_niveau_ordre ASC, livret_groupe_nom ASC';
$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':user_id'=>$_SESSION['USER_ID'],':type'=>'besoin');
$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
foreach($DB_TAB as $DB_ROW)
{
	$tab_niveau_groupe[$DB_ROW['livret_niveau_id']][$DB_ROW['livret_groupe_id']] = html($DB_ROW['livret_groupe_nom']);
	$tab_user[$DB_ROW['livret_groupe_id']] = '';
}
// Récupérer la liste des élèves / groupes de besoin
$DB_SQL = 'SELECT * FROM livret_user ';
$DB_SQL.= 'LEFT JOIN livret_jointure_user_groupe USING (livret_structure_id,livret_user_id) ';
$DB_SQL.= 'LEFT JOIN livret_groupe USING (livret_structure_id,livret_groupe_id) ';
$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_profil=:profil AND livret_user_statut=:statut AND livret_groupe_prof_id=:user_id AND livret_groupe_type=:type ';
$DB_SQL.= 'ORDER BY livret_user_nom ASC, livret_user_prenom ASC';
$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':profil'=>'eleve',':statut'=>1,':user_id'=>$_SESSION['USER_ID'],':type'=>'besoin');
$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
foreach($DB_TAB as $DB_ROW)
{
	$tab_user[$DB_ROW['livret_groupe_id']]  .= html($DB_ROW['livret_user_nom'].' '.$DB_ROW['livret_user_prenom']).'<br />';
}
// Assemblage du tableau résultant
$TH = array();
$TB = array();
$TF = array();
foreach($tab_niveau_groupe as $niveau_id => $tab_groupe)
{
	$TH[$niveau_id] = '';
	$TB[$niveau_id] = '';
	$TF[$niveau_id] = '';
	foreach($tab_groupe as $groupe_id => $groupe_nom)
	{
		$nb = mb_substr_count($tab_user[$groupe_id],'<br />','UTF-8');
		$s = ($nb>1) ? 's' : '' ;
		$TH[$niveau_id] .= '<th>'.$groupe_nom.'</th>';
		$TB[$niveau_id] .= '<td>'.mb_substr($tab_user[$groupe_id],0,-6,'UTF-8').'</td>';
		$TF[$niveau_id] .= '<td>'.$nb.' élève'.$s.'</td>';
	}
}
echo'<hr />';
foreach($tab_niveau_groupe as $niveau_id => $tab_groupe)
{
	echo'<table class="affectation">';
	echo'<thead><tr>'.$TH[$niveau_id].'</tr></thead>';
	echo'<tbody><tr>'.$TB[$niveau_id].'</tr></tbody>';
	echo'<tfoot><tr>'.$TF[$niveau_id].'</tr></tfoot>';
	echo'</table><p />';
}
?>
