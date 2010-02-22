<?php
/**
 * @version $Id: _maj_select_eval.ajax.php 8 2009-10-30 20:56:02Z thomas $
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2009
 * 
 * ****************************************************************************************************
 * SACoche [http://competences.sesamath.net] - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath [http://www.sesamath.net]
 * Distribution sous licence libre prévue pour l'été 2010.
 * ****************************************************************************************************
 * 
 * Mettre à jour l'élément de formulaire "f_devoir" et le renvoyer en HTML
 * 
 */

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}
if($_SESSION['STRUCTURE_ID']==ID_DEMO) {}

$eval_type = (isset($_POST['eval_type'])) ? clean_texte($_POST['eval_type'])  : '';	// 'groupe' ou 'select'
$groupe_id = (isset($_POST['groupe_id'])) ? clean_entier($_POST['groupe_id']) : 0;	// utile uniquement pour $eval_type='groupe'

$tab_types = array('groupe','select');

if( (!$groupe_id) || (!in_array($eval_type,$tab_types)) )
{
	exit('Erreur avec les données transmises !');
}
// Lister les évaluations dans le cas d'une classe ou d'un groupe ou d'un groupe de besoin / dans le cas d'un ensemble d'élèves sélectionnés
$DB_SQL = 'SELECT livret_devoir.* ';
$DB_SQL.= 'FROM livret_devoir ';
$DB_SQL.= 'LEFT JOIN livret_groupe USING (livret_structure_id,livret_groupe_id) ';
$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_prof_id=:prof_id ';
$DB_SQL.= ($eval_type=='groupe') ? 'AND livret_groupe_type!=:type4 AND livret_groupe_id=:groupe_id ' : 'AND livret_groupe_type=:type4 ' ;
$DB_SQL.= 'ORDER BY livret_devoir_date DESC ';
$DB_SQL.= 'LIMIT 20 ';
$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':prof_id'=>$_SESSION['USER_ID'],':groupe_id'=>$groupe_id,':type4'=>'eval');
$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
if(!count($DB_TAB))
{
	exit('<option value="" disabled="disabled">Aucun devoir n\'a été trouvé pour ce groupe d\'élèves !</option>');
}
foreach($DB_TAB as $DB_ROW)
{
	// Formater la date et la référence de l'évaluation
	$date_affich = convert_date_mysql_to_french($DB_ROW['livret_devoir_date']);
	echo'<option value="'.$DB_ROW['livret_devoir_id'].'">'.$date_affich.' | '.html($DB_ROW['livret_devoir_info']).'</option>';
}
?>
