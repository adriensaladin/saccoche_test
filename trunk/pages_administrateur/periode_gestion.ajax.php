<?php
/**
 * @version $Id: periode_gestion.ajax.php 8 2009-10-30 20:56:02Z thomas $
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
$id     = (isset($_POST['f_id']))     ? clean_entier($_POST['f_id'])    : 0;
$nom    = (isset($_POST['f_nom']))    ? clean_texte($_POST['f_nom'])    : '';
$ordre  = (isset($_POST['f_ordre']))  ? clean_entier($_POST['f_ordre']) : 0;

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Ajouter une nouvelle période / Dupliquer une pédiode existante
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
if( (($action=='ajouter')||($action=='dupliquer')) && $nom && $ordre )
{
	// Vérifier que le nom de la période est disponible
	$DB_SQL = 'SELECT livret_periode_id FROM livret_periode ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_periode_nom=:nom ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':nom'=>$nom);
	$DB_ROW = DB::queryRow(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	if(count($DB_ROW))
	{
		exit('Erreur : nom de période déjà existant !');
	}
	// Insérer l'enregistrement
	$DB_SQL = 'INSERT INTO livret_periode(livret_structure_id,livret_periode_nom,livret_periode_ordre) ';
	$DB_SQL.= 'VALUES(:structure_id,:nom,:ordre)';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':nom'=>$nom,':ordre'=>$ordre);
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	$id = DB::getLastOid(SACOCHE_BD_NAME);
	// Afficher le retour
	echo'<tr id="id_'.$id.'" class="new">';
	echo	'<td>'.$ordre.'</td>';
	echo	'<td>'.html($nom).'</td>';
	echo	'<td class="nu">';
	echo		'<q class="modifier" title="Modifier cette période."></q>';
	echo		'<q class="dupliquer" title="Dupliquer cette période."></q>';
	echo		'<q class="supprimer" title="Supprimer cette période."></q>';
	echo	'</td>';
	echo'</tr>';
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Modifier une période existante
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
else if( ($action=='modifier') && $id && $nom && $ordre )
{
	// Vérifier que le nom de la période est disponible
	$DB_SQL = 'SELECT livret_periode_id FROM livret_periode ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_periode_nom=:nom AND livret_periode_id!=:id ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':nom'=>$nom,':id'=>$id);
	$DB_ROW = DB::queryRow(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	if(count($DB_ROW))
	{
		exit('Erreur : nom de période déjà existant !');
	}
	// Mettre à jour l'enregistrement
	$DB_SQL = 'UPDATE livret_periode ';
	$DB_SQL.= 'SET livret_periode_nom=:nom,livret_periode_ordre=:ordre ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_periode_id=:id ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':nom'=>$nom,':ordre'=>$ordre,':id'=>$id);
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	// Afficher le retour
	echo'<td>'.$ordre.'</td>';
	echo'<td>'.html($nom).'</td>';
	echo'<td class="nu">';
	echo	'<q class="modifier" title="Modifier cette période."></q>';
	echo	'<q class="dupliquer" title="Dupliquer cette période."></q>';
	echo	'<q class="supprimer" title="Supprimer cette période."></q>';
	echo'</td>';
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Supprimer une période existante
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
else if( ($action=='supprimer') && $id )
{
	// Effacer l'enregistrement
	$DB_SQL = 'DELETE FROM livret_periode ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_periode_id=:id ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':id'=>$id);
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	// Effacer les jointures avec les classes
	$DB_SQL = 'DELETE FROM livret_jointure_groupe_periode ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_periode_id=:id ';
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
