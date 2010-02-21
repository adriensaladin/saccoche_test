<?php
/**
 * @version $Id: eval_demande.ajax.php 8 2009-10-30 20:56:02Z thomas $
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
if(($_SESSION['STRUCTURE_ID']==ID_DEMO)&&($_POST['f_action']!='Afficher_demandes')){exit('Action désactivée pour la démo...');}

$action      = (isset($_POST['f_action']))      ? clean_texte($_POST['f_action'])      : '';
$matiere_id  = (isset($_POST['f_matiere']))     ? clean_entier($_POST['f_matiere'])    : 0;
$matiere_nom = (isset($_POST['f_matiere_nom'])) ? clean_texte($_POST['f_matiere_nom']) : '';
$groupe_id   = (isset($_POST['f_groupe']))      ? clean_entier($_POST['f_groupe'])     : 0;
$groupe_type = (isset($_POST['f_groupe_type'])) ? clean_texte($_POST['f_groupe_type']) : '';
$groupe_nom  = (isset($_POST['f_groupe_nom']))  ? clean_texte($_POST['f_groupe_nom'])  : '';

/*
function positif($n) {return($n);}
// Contrôler la liste des items transmis
$tab_id = (isset($_POST['tab_id'])) ? array_map('clean_entier',explode(',',$_POST['tab_id'])) : array() ;
$tab_id = array_filter($tab_id,'positif');
// Contrôler la liste des items transmis
$tab_competences = (isset($_POST['f_compet_liste'])) ? explode('_',$_POST['f_compet_liste']) : array() ;
$tab_competences = array_map('clean_entier',$tab_competences);
$tab_competences = array_filter($tab_competences,'positif');
$nb_competences = count($tab_competences);
*/

$tab_types = array('Classes'=>'classe' , 'Groupes'=>'groupe' , 'Besoins'=>'groupe');

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Afficher une liste de demandes
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
if( ($action=='Afficher_demandes') && $matiere_id && $matiere_nom && $groupe_id && (isset($tab_types[$groupe_type])) && $groupe_nom )
{
	$retour = '';
	// Récupérer la liste des élèves concernés
	$DB_TAB = DB_OPT_eleves_regroupement($_SESSION['STRUCTURE_ID'],$tab_types[$groupe_type],$groupe_id,$user_statut=1);
	if(!is_array($DB_TAB))
	{
		exit($DB_TAB);	// Erreur : aucun élève de ce regroupement n\'est enregistré !
	}
	$tab_eleves = array();
	foreach($DB_TAB as $key => $DB_ROW)
	{
		$tab_eleves[$DB_ROW['valeur']] = $DB_ROW['texte'];
	}
	$listing_user_id = implode(',', array_keys($tab_eleves) );
	// Lister les demandes
	$tab_demandes = array();
	$DB_SQL = 'SELECT livret_demande.*, ';
	$DB_SQL.= 'CONCAT(livret_niveau_ref,".",livret_domaine_ref,livret_theme_ordre,livret_competence_ordre) AS competence_ref , ';
	$DB_SQL.= 'livret_competence_nom, livret_user_nom, livret_user_prenom ';
	$DB_SQL.= 'FROM livret_demande ';
	$DB_SQL.= 'LEFT JOIN livret_competence_item USING (livret_structure_id,livret_competence_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_theme USING (livret_structure_id,livret_theme_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_domaine USING (livret_structure_id,livret_domaine_id) ';
	$DB_SQL.= 'LEFT JOIN livret_niveau USING (livret_niveau_id) ';
	$DB_SQL.= 'LEFT JOIN livret_user USING (livret_structure_id,livret_user_id) ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_id IN('.$listing_user_id.') AND livret_demande.livret_matiere_id=:matiere_id ';
	$DB_SQL.= 'ORDER BY livret_niveau_ref ASC, livret_domaine_ref ASC, livret_theme_ordre ASC, livret_competence_ordre ASC';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':matiere_id'=>$matiere_id);
	$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	if(!count($DB_TAB))
	{
		exit('Aucune demande n\'a été formulée pour ces élèves et cette matière !');
	}
	foreach($DB_TAB as $key => $DB_ROW)
	{
		$tab_demandes[] = $DB_ROW['livret_demande_id'] ;
		$score = ($DB_ROW['livret_demande_score']!==null) ? $DB_ROW['livret_demande_score'] : false ;
		$statut = ($DB_ROW['livret_demande_statut']=='eleve') ? 'demande non traitée' : 'évaluation en préparation' ;
		$class  = ($DB_ROW['livret_demande_statut']=='eleve') ? ' class="new"' : '' ;
		// Afficher une ligne du tableau
		$retour .= '<tr'.$class.'>';
		$retour .= '<td class="nu"><input type="checkbox" name="f_ids" value="'.$DB_ROW['livret_demande_id'].'x'.$DB_ROW['livret_user_id'].'x'.$DB_ROW['livret_competence_id'].'" /></td>';
		$retour .= '<td>'.html($matiere_nom).'</td>';
		$retour .= '<td>'.html($DB_ROW['competence_ref']).' <img alt="" src="./_img/bulle_aide.png" title="'.html($DB_ROW['livret_competence_nom']).'" /></td>';
		$retour .= '<td>$'.$DB_ROW['livret_demande_id'].'$</td>';
		$retour .= '<td>'.html($groupe_nom).'</td>';
		$retour .= '<td>'.html($tab_eleves[$DB_ROW['livret_user_id']]).'</td>';
		$retour .= affich_score_html($score,'score',$pourcent='');
		$retour .= '<td><i>'.html($DB_ROW['livret_demande_date']).'</i>'.convert_date_mysql_to_french($DB_ROW['livret_demande_date']).'</td>';
		$retour .= '<td>'.$statut.'</td>';
		$retour .= '</tr>';
	}
	// Calculer pour chaque item sa popularité (le nb de demandes pour les élèves affichés)
	$listing_demande_id = implode(',', $tab_demandes );
	$DB_SQL = 'SELECT livret_demande_id , COUNT(livret_demande_id) AS popularite ';
	$DB_SQL.= 'FROM livret_demande ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_demande_id IN('.$listing_demande_id.') AND livret_user_id IN('.$listing_user_id.') ';
	$DB_SQL.= 'GROUP BY livret_demande_id ';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID']);
	$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	$tab_bad = array();
	$tab_bon = array();
	foreach($DB_TAB as $key => $DB_ROW)
	{
		$s = ($DB_ROW['popularite']>1) ? 's' : '' ;
		$tab_bad[] = '$'.$DB_ROW['livret_demande_id'].'$';
		$tab_bon[] = '<i>'.sprintf("%02u",$DB_ROW['popularite']).'</i>'.$DB_ROW['popularite'].' demande'.$s;
	}
	echo str_replace($tab_bad,$tab_bon,$retour);
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
