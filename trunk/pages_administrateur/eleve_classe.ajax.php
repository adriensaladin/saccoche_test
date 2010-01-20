<?php
/**
 * @version $Id: eleve_classe.ajax.php 8 2009-10-30 20:56:02Z thomas $
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
$tab_select_eleves  = (isset($_POST['select_eleves']))  ? array_map('clean_entier',explode(',',$_POST['select_eleves']))  : array() ;
$tab_select_classes = (isset($_POST['select_classes'])) ? array_map('clean_entier',explode(',',$_POST['select_classes'])) : array() ;

function positif($n) {return($n);}
$tab_select_eleves  = array_filter($tab_select_eleves,'positif');
$tab_select_classes = array_filter($tab_select_classes,'positif');

// Ajouter des élèves à des classes
if($action=='ajouter')
{
	$classe_id = current($tab_select_classes); // un élève ne peut être affecté qu'à 1 seule classe : inutile de toutes les passer en revue
	foreach($tab_select_eleves as $user_id)
	{
		DB_modifier_liaison_eleve_classe($_SESSION['STRUCTURE_ID'],$user_id,$classe_id,true)
	}
}

// Retirer des élèves à des classes
elseif($action=='retirer')
{
	// pas besoin de passer les classes en revue : il suffit de mettre $classe_id à 0
	foreach($tab_select_eleves as $user_id)
	{
		DB_modifier_liaison_eleve_classe($_SESSION['STRUCTURE_ID'],$user_id,0,false)
	}
}

// Affichage du bilan des affectations des élèves dans les classes ; en deux requêtes pour récupérer les élèves sans classes et les classes sans élèves
$tab_niveau_groupe = array();
$tab_user          = array();
$tab_niveau_groupe[0][0] = '<i>sans classe</i>';
$tab_user[0]             = '';
// Récupérer la liste des classes
$DB_SQL = 'SELECT * FROM livret_groupe ';
$DB_SQL.= 'LEFT JOIN livret_niveau USING (livret_niveau_id) ';
$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_groupe_type=:type ';
$DB_SQL.= 'ORDER BY livret_niveau_ordre ASC, livret_groupe_ref ASC';
$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':type'=>'classe');
$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
foreach($DB_TAB as $key => $DB_ROW)
{
	$tab_niveau_groupe[$DB_ROW['livret_niveau_id']][$DB_ROW['livret_groupe_id']] = html($DB_ROW['livret_groupe_nom']);
	$tab_user[$DB_ROW['livret_groupe_id']] = '';
}
// Récupérer la liste des élèves / classes
$DB_SQL = 'SELECT * FROM livret_user ';
$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_profil=:profil AND livret_user_statut=:statut ';
$DB_SQL.= 'ORDER BY livret_user_nom ASC, livret_user_prenom ASC';
$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':profil'=>'eleve',':statut'=>1);
$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
foreach($DB_TAB as $key => $DB_ROW)
{
	$tab_user[$DB_ROW['livret_eleve_classe_id']]  .= html($DB_ROW['livret_user_nom'].' '.$DB_ROW['livret_user_prenom']).'<br />';
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
