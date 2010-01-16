<?php
/**
 * @version $Id: etabl_resilier.ajax.php 8 2009-10-30 20:56:02Z thomas $
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

$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID']);

$DB_SQL = 'DELETE FROM livret_structure WHERE livret_structure_id=:structure_id';
DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);

$DB_SQL = 'DELETE FROM livret_groupe WHERE livret_structure_id=:structure_id';
DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);

$DB_SQL = 'DELETE FROM livret_matiere WHERE livret_matiere_structure_id=:structure_id';
DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);

$DB_SQL = 'DELETE FROM livret_user WHERE livret_structure_id=:structure_id';
DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);

$DB_SQL = 'DELETE FROM livret_jointure_user_groupe WHERE livret_structure_id=:structure_id';
DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);

$DB_SQL = 'DELETE FROM livret_jointure_user_matiere WHERE livret_structure_id=:structure_id';
DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);

$DB_SQL = 'DELETE FROM livret_competence_domaine WHERE livret_structure_id=:structure_id';
DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);

$DB_SQL = 'DELETE FROM livret_competence_theme WHERE livret_structure_id=:structure_id';
DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);

$DB_SQL = 'DELETE FROM livret_competence_item WHERE livret_structure_id=:structure_id';
DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);

$DB_SQL = 'DELETE FROM livret_evaluation WHERE livret_structure_id=:structure_id';
DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);

$DB_SQL = 'DELETE FROM livret_jointure_user_competence WHERE livret_structure_id=:structure_id';
DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);

$DB_SQL = 'DELETE FROM livret_jointure_evaluation_competence WHERE livret_structure_id=:structure_id';
DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);

echo'ok';
?>
