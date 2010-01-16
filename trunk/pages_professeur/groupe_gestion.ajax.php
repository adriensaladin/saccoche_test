<?php
/**
 * @version $Id: groupe_gestion.ajax.php 8 2009-10-30 20:56:02Z thomas $
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

$action = (isset($_POST['f_action'])) ? clean_texte($_POST['f_action'])  : '';
$id     = (isset($_POST['f_id']))     ? clean_entier($_POST['f_id'])     : 0;
$niveau = (isset($_POST['f_niveau'])) ? clean_entier($_POST['f_niveau']) : 0;
$nom    = (isset($_POST['f_nom']))    ? clean_texte($_POST['f_nom'])     : '';

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Ajouter un nouveau groupe de besoin
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
if( ($action=='ajouter') && $niveau && $nom )
{
	// Vérifier que le nom du groupe est disponible
	$DB_SQL = 'SELECT livret_groupe_id FROM livret_groupe ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_groupe_type=:type AND livret_groupe_prof_id=:prof_id AND livret_groupe_nom=:nom AND livret_groupe_id!=:id ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':type'=>'besoin',':prof_id'=>$_SESSION['USER_ID'],':nom'=>$nom,':id'=>$id);
	$DB_ROW = DB::queryRow(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	if(count($DB_ROW))
	{
		exit('Erreur : nom de groupe de besoin déjà existant !');
	}
	// Insérer l'enregistrement
	$DB_SQL = 'INSERT INTO livret_groupe(livret_structure_id,livret_groupe_type,livret_groupe_prof_id,livret_groupe_ref,livret_groupe_nom,livret_niveau_id) ';
	$DB_SQL.= 'VALUES(:structure_id,:type,:prof_id,:ref,:nom,:niveau)';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':type'=>'besoin',':prof_id'=>$_SESSION['USER_ID'],':ref'=>'',':nom'=>$nom,':niveau'=>$niveau);
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	$id = DB::getLastOid(SACOCHE_BD_NAME);
	// Afficher le retour
	echo'<tr id="id_'.$id.'" class="new">';
	echo	'<td>{{NIVEAU_NOM}}</td>';
	echo	'<td>'.html($nom).'</td>';
	echo	'<td class="nu">';
	echo		'<q class="modifier" title="Modifier ce groupe de besoin."></q>';
	echo		'<q class="supprimer" title="Supprimer ce groupe de besoin."></q>';
	echo	'</td>';
	echo'</tr>';
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Modifier un groupe de besoin existant
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
else if( ($action=='modifier') && $id && $niveau && $nom )
{
	// Vérifier que le nom du groupe est disponible
	$DB_SQL = 'SELECT livret_groupe_id FROM livret_groupe ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_groupe_type=:type AND livret_groupe_prof_id=:prof_id AND livret_groupe_nom=:nom AND livret_groupe_id!=:id ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':type'=>'besoin',':prof_id'=>$_SESSION['USER_ID'],':nom'=>$nom,':id'=>$id);
	$DB_ROW = DB::queryRow(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	if(count($DB_ROW))
	{
		exit('Erreur : nom de groupe de besoin déjà existant !');
	}
	// Mettre à jour l'enregistrement
	$DB_SQL = 'UPDATE livret_groupe ';
	$DB_SQL.= 'SET livret_groupe_nom=:nom,livret_niveau_id=:niveau ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_groupe_id=:id AND livret_groupe_prof_id=:prof_id ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':nom'=>$nom,':niveau'=>$niveau,':id'=>$id,':prof_id'=>$_SESSION['USER_ID']);
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	// Afficher le retour
	echo'<td>{{NIVEAU_NOM}}</td>';
	echo'<td>'.html($nom).'</td>';
	echo'<td class="nu">';
	echo	'<q class="modifier" title="Modifier ce groupe de besoin."></q>';
	echo	'<q class="supprimer" title="Supprimer ce groupe de besoin."></q>';
	echo'</td>';
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Supprimer un groupe de besoin existant
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
else if( ($action=='supprimer') && $id )
{
	// Effacer l'enregistrement
	$DB_SQL = 'DELETE FROM livret_groupe ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_groupe_id=:id ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':id'=>$id);
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	$DB_SQL = 'DELETE FROM livret_jointure_user_groupe ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_groupe_id=:id';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':id'=>$id);
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	$DB_SQL = 'UPDATE livret_evaluation ';
	$DB_SQL.= 'SET livret_groupe_id=0 ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_groupe_id=:id';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':id'=>$id);
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	// Afficher le retour
	echo'<td>ok</td>';
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
