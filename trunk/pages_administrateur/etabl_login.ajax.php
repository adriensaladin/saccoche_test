<?php
/**
 * @version $Id: etabl_login.ajax.php 8 2009-10-30 20:56:02Z thomas $
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

$f_login_professeur = (isset($_POST['f_login_professeur'])) ? clean_texte($_POST['f_login_professeur']) : '';
$f_login_eleve      = (isset($_POST['f_login_eleve']))      ? clean_texte($_POST['f_login_eleve'])      : '';

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Format des noms d'utilisateurs
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( $f_login_professeur && $f_login_eleve )
{
	$test_professeur = (preg_match("#^p+[._-]?n+$#", $f_login_professeur)) ? 'prenom-puis-nom' : false ;
	$test_professeur = (preg_match("#^n+[._-]?p+$#", $f_login_professeur)) ? 'nom-puis-prenom' : $test_professeur ;
	$test_eleve      = (preg_match("#^p+[._-]?n+$#", $f_login_eleve))      ? 'prenom-puis-nom' : false ;
	$test_eleve      = (preg_match("#^n+[._-]?p+$#", $f_login_eleve))      ? 'nom-puis-prenom' : $test_eleve ;
	if( $test_professeur && $test_eleve )
	{
		$DB_SQL = 'UPDATE livret_structure ';
		$DB_SQL.= 'SET livret_structure_modele_professeur=:modele_professeur, livret_structure_modele_eleve=:modele_eleve ';
		$DB_SQL.= 'WHERE livret_structure_id=:structure_id ';
		$DB_SQL.= 'LIMIT 1';
		$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':modele_professeur'=>$f_login_professeur,':modele_eleve'=>$f_login_eleve);
		DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
		// ne pas oublier de mettre à jour la session aussi
		$_SESSION['MODELE_PROF']  = $f_login_professeur;
		$_SESSION['MODELE_ELEVE'] = $f_login_eleve;
		echo'ok';
	}
	else
	{
		echo'Erreur avec les données transmises !';
	}
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
