<?php
/**
 * @version $Id: periode_classe-groupe.ajax.php 8 2009-10-30 20:56:02Z thomas $
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

$action     = (isset($_GET['action']))        ? $_GET['action']                     : '';
$date_debut = (isset($_POST['f_date_debut'])) ? clean_texte($_POST['f_date_debut']) : '';
$date_fin   = (isset($_POST['f_date_fin']))   ? clean_texte($_POST['f_date_fin'])   : '';
$tab_select_periodes        = (isset($_POST['select_periodes']))        ? array_map('clean_entier',explode(',',$_POST['select_periodes']))        : array() ;
$tab_select_classes_groupes = (isset($_POST['select_classes_groupes'])) ? array_map('clean_entier',explode(',',$_POST['select_classes_groupes'])) : array() ;

function positif($n) {return($n);}
$tab_select_periodes        = array_filter($tab_select_periodes,'positif');
$tab_select_classes_groupes = array_filter($tab_select_classes_groupes,'positif');


//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Ajouter des périodes à des classes & groupes
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='ajouter') && $date_debut && $date_fin )
{
	// Formater les dates
	$date_debut_mysql = convert_date_french_to_mysql($date_debut);
	$date_fin_mysql   = convert_date_french_to_mysql($date_fin);
	// Vérifier que le date de début est antérieure à la date de fin
	if($date_debut_mysql>$date_fin_mysql)
	{
		exit('Erreur : la date de début est postérieure à la date de fin !');
	}
	foreach($tab_select_periodes as $periode_id)
	{
		foreach($tab_select_classes_groupes as $groupe_id)
		{
			DB_modifier_liaison_groupe_periode($_SESSION['STRUCTURE_ID'],$groupe_id,$periode_id,true,$date_debut_mysql,$date_fin_mysql);
		}
	}
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Retirer des périodes à des classes & groupes
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

elseif($action=='retirer')
{
	foreach($tab_select_periodes as $periode_id)
	{
		foreach($tab_select_classes_groupes as $groupe_id)
		{
			DB_modifier_liaison_groupe_periode($_SESSION['STRUCTURE_ID'],$groupe_id,$periode_id,false);
		}
	}
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Affichage du bilan des affectations des périodes aux classes & groupes ; en plusieurs requêtes pour récupérer les périodes sans classes-groupes et les classes-groupes sans périodes
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

echo'<hr />';
$tab_groupe    = array();
$tab_periode   = array();
$tab_jointure  = array();
$tab_graphique = array();
// Récupérer la liste des classes & groupes, dans l'ordre des niveaux
$DB_SQL = 'SELECT * FROM livret_groupe ';
$DB_SQL.= 'LEFT JOIN livret_niveau USING (livret_niveau_id) ';
$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_groupe_type IN (:type1,:type2) ';
$DB_SQL.= 'ORDER BY livret_niveau_ordre ASC, livret_groupe_type ASC, livret_groupe_nom ASC';
$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':type1'=>'classe',':type2'=>'groupe');
$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
if(!count($DB_TAB))
{
	exit('Aucune classe et aucun groupe ne sont enregistrés !');
}
foreach($DB_TAB as $DB_ROW)
{
	$tab_groupe[$DB_ROW['livret_groupe_id']]    = '<th>'.html($DB_ROW['livret_groupe_nom']).'</th>';
	$tab_graphique[$DB_ROW['livret_groupe_id']] = '';
}
// Récupérer la liste des périodes, dans l'ordre choisi par l'admin
$DB_SQL = 'SELECT * FROM livret_periode ';
$DB_SQL.= 'WHERE livret_structure_id=:structure_id ';
$DB_SQL.= 'ORDER BY livret_periode_ordre ASC';
$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID']);
$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
if(!count($DB_TAB))
{
	exit('Aucune période n\'est enregistrée !');
}
foreach($DB_TAB as $DB_ROW)
{
	$tab_periode[$DB_ROW['livret_periode_id']] = '<th>'.html($DB_ROW['livret_periode_nom']).'</th>';
}
// Récupérer l'amplitude complète sur l'ensemble des périodes
$DB_SQL = 'SELECT MIN(livret_periode_date_debut) AS tout_debut , MAX(livret_periode_date_fin) AS toute_fin FROM livret_jointure_groupe_periode ';
$DB_SQL.= 'WHERE livret_structure_id=:structure_id ';
$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID']);
$DB_ROW = DB::queryRow(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
if(!count($DB_ROW))
{
	// Aucune association n'est enregistrée
	$tout_debut = '2000-01-01';
	$toute_fin  = '2000-01-01';
	$nb_jours_total = 0;
}
else
{
	$tout_debut = $DB_ROW['tout_debut'];
	$toute_fin  = $DB_ROW['toute_fin'];
	$DB_SQL = 'SELECT DATEDIFF(DATE_ADD(:toute_fin,INTERVAL 1 DAY),:tout_debut) AS nb_jours_total ';	// On ajoute un jour pour dessiner les barres jusqu'au jour suivant (accessoirement, ça évite aussi une possible division par 0).
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':tout_debut'=>$tout_debut,':toute_fin'=>$toute_fin);
	$DB_ROW = DB::queryRow(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	$nb_jours_total = $DB_ROW['nb_jours_total'];
}
// Récupérer la liste des jointures, et le nécessaire pour établir les graphiques
$DB_SQL = 'SELECT * , ';
$DB_SQL.= 'DATEDIFF(livret_periode_date_debut,:tout_debut) AS position_jour_debut , DATEDIFF(livret_periode_date_fin,livret_periode_date_debut) AS nb_jour ';
$DB_SQL.= 'FROM livret_jointure_groupe_periode ';
$DB_SQL.= 'WHERE livret_structure_id=:structure_id ';
$DB_SQL.= 'ORDER BY livret_groupe_id ASC, livret_periode_date_debut ASC, livret_periode_date_fin ASC';
$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':tout_debut'=>$tout_debut);
$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
$memo_groupe_id = 0;
foreach($DB_TAB as $DB_ROW)
{
	$groupe_id = $DB_ROW['livret_groupe_id'];
	$date_affich_debut = convert_date_mysql_to_french($DB_ROW['livret_periode_date_debut']);
	$date_affich_fin   = convert_date_mysql_to_french($DB_ROW['livret_periode_date_fin']);
	$tab_jointure[$groupe_id][$DB_ROW['livret_periode_id']] = html($date_affich_debut).' ~ '.html($date_affich_fin).' <input type="image" src="./_img/date_add.png" title="Cliquer pour importer ces dates dans les champs." />';
	// graphique (début)
	if($memo_groupe_id!=$groupe_id)
	{
		$memo_position = 0;
		$memo_groupe_id = $groupe_id;
	}
	$margin_left = 100*round($DB_ROW['position_jour_debut'] / $nb_jours_total , 4);
	$width       = 100*round( ($DB_ROW['nb_jour']+1) / $nb_jours_total , 4);	// On ajoute un jour pour dessiner les barres jusqu'au jour suivant.
	if($memo_position+0.02<$margin_left) // Le 0.02 sert à éviter les erreurs d'arrondi et une erreur PHP style un test 12.34<12.34 qui renvoie vrai !
	{
		// Deux périodes ne sont pas consécutives
		$margin_left_erreur = $memo_position;
		$width_erreur = $margin_left - $memo_position;
		$tab_graphique[$groupe_id] .= '<div class="graph_erreur" style="margin-left:'.$margin_left_erreur.'%;width:'.$width_erreur.'%"></div>';
	}
	elseif($memo_position>$margin_left+0.02) // Le 0.02 sert à éviter les erreurs d'arrondi et une erreur PHP style un test 12.34<12.34 qui renvoie vrai !
	{
		// Deux périodes se chevauchent
		$margin_left_erreur = $margin_left;
		$width_erreur = $memo_position - $margin_left;
		$tab_graphique[$groupe_id] .= '<div class="graph_erreur" style="margin-left:'.$margin_left_erreur.'%;width:'.$width_erreur.'%"></div>';
	}
	$tab_graphique[$groupe_id] .= '<div class="graph_partie" style="margin-left:'.$margin_left.'%;width:'.$width.'%"></div>';
	$memo_position = $margin_left + $width;
	// graphique (fin)
}
// Fabrication du tableau résultant
foreach($tab_groupe as $groupe_id => $groupe_text)
{
	foreach($tab_periode as $periode_id => $periode_text)
	{
		$tab_groupe[$groupe_id] .= (isset($tab_jointure[$groupe_id][$periode_id])) ? '<td>'.$tab_jointure[$groupe_id][$periode_id].'</td>' : '<td class="hc">-</td>' ;
	}
	$tab_groupe[$groupe_id] .= '<td>'.$tab_graphique[$groupe_id].'</td>';
}
// Affichage du tableau résultant
echo'<table>';
echo'<thead><tr><td class="nu"></td>'.implode('',$tab_periode).'<td class="graph_total">Étendue du '.convert_date_mysql_to_french($tout_debut).' au '.convert_date_mysql_to_french($toute_fin).'.</td></tr></thead>';
echo'<tbody><tr>'.implode('</tr>'."\r\n".'<tr>',$tab_groupe).'</tr></tbody>';
echo'</table><p />';

?>
