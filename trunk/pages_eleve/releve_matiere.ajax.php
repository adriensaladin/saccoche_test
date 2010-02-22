<?php
/**
 * @version $Id: releve_matiere.ajax.php 8 2009-10-30 20:56:02Z thomas $
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
if($_SESSION['STRUCTURE_ID']==ID_DEMO) {}

$orientation    = (isset($_POST['f_orientation'])) ? clean_texte($_POST['f_orientation']) : '';
$marge_min      = (isset($_POST['f_marge_min']))   ? clean_texte($_POST['f_marge_min'])   : '';
$couleur        = (isset($_POST['f_couleur']))     ? clean_texte($_POST['f_couleur'])     : '';
$cases_nb       = (isset($_POST['f_cases_nb']))    ? clean_entier($_POST['f_cases_nb'])   : 0;
$cases_largeur  = (isset($_POST['f_cases_larg']))  ? clean_entier($_POST['f_cases_larg']) : 0;
$cases_hauteur  = (isset($_POST['f_cases_haut']))  ? clean_entier($_POST['f_cases_haut']) : 0;
$periode_id     = (isset($_POST['f_periode']))     ? clean_entier($_POST['f_periode'])    : 0;
$date_debut     = (isset($_POST['f_date_debut']))  ? clean_texte($_POST['f_date_debut'])  : '';
$date_fin       = (isset($_POST['f_date_fin']))    ? clean_texte($_POST['f_date_fin'])    : '';
$retroactif     = (isset($_POST['f_retroactif']))  ? clean_texte($_POST['f_retroactif'])  : '';
$matiere_id     = (isset($_POST['f_matiere']))     ? clean_entier($_POST['f_matiere'])    : 0;
$matiere_nom    = (isset($_POST['f_matiere_nom'])) ? clean_texte($_POST['f_matiere_nom']) : '';
$aff_coef       = (isset($_POST['f_coef']))        ? 1                                    : 0;
$aff_socle      = (isset($_POST['f_socle']))       ? 1                                    : 0;
$aff_lien       = (isset($_POST['f_lien']))        ? 1                                    : 0;
$aff_bilan_ms   = (mb_substr_count($_SESSION['ELEVE_OPTIONS'],'ms')) ? 1                  : 0;	// pas true / false car utilisé dans un calcul
$aff_bilan_pv   = (mb_substr_count($_SESSION['ELEVE_OPTIONS'],'pv')) ? 1                  : 0;	// pas true / false car utilisé dans un calcul
$aff_conv_sur20 = false;
$groupe_id      = $_SESSION['ELEVE_CLASSE_ID'];
$groupe_nom     = $_SESSION['ELEVE_CLASSE_NOM'];
$tab_eleve[]    = $_SESSION['USER_ID'];
$tab_type[]     = 'individuel';
$format         = 'matiere';

save_cookie_select($_SESSION['STRUCTURE_ID'],$_SESSION['USER_ID']);

// ...
// ...
$liste_eleve   = $_SESSION['USER_ID'];

if( $orientation && $marge_min && $couleur && $cases_nb && $cases_largeur && $cases_hauteur && ( $periode_id || ($date_debut && $date_fin) ) && $retroactif && $matiere_id && $groupe_id && $groupe_nom && count($tab_eleve) && count($tab_type) )
{

	// Période concernée
	if($periode_id==0)
	{
		$date_mysql_debut = convert_date_french_to_mysql($date_debut);
		$date_mysql_fin   = convert_date_french_to_mysql($date_fin);
	}
	else
	{
		$DB_SQL = 'SELECT livret_periode_date_debut , livret_periode_date_fin FROM livret_jointure_groupe_periode ';
		$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_groupe_id=:groupe_id AND livret_periode_id=:periode_id ';
		$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':groupe_id'=>$groupe_id,':periode_id'=>$periode_id);
		$DB_ROW = DB::queryRow(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
		if(!count($DB_ROW))
		{
			exit('La classe et la période ne sont pas reliées !');
		}
		$date_mysql_debut = $DB_ROW['livret_periode_date_debut'];
		$date_mysql_fin   = $DB_ROW['livret_periode_date_fin'];
		$date_debut = convert_date_mysql_to_french($date_mysql_debut);
		$date_fin   = convert_date_mysql_to_french($date_mysql_fin);
	}
	if($date_mysql_debut>$date_mysql_fin)
	{
		exit('La date de début est postérieure à la date de fin !');
	}

	$tab_competence = array();	// [competence_id] => array(competence_ref,competence_nom,competence_coef,competence_socle,competence_lien,calcul_methode,calcul_limite);
	$tab_liste_comp = array();	// [i] => competence_id
	$tab_eleve      = array();	// [i] => array(eleve_id,eleve_nom,eleve_prenom)
	$tab_matiere    = array();	// [matiere_id] => matiere_nom
	$tab_eval       = array();	// [eleve_id][matiere_id][competence_id][devoir] => array(note,date,info) On utilise un tableau multidimensionnel vu qu'on ne sait pas à l'avance combien il y a d'évaluations pour un item donné.

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	// Récupération de la liste des items travaillés durant la période choisie, pour la matière selectionnée
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	$tab_competence = select_arborescence_eleve_periode_matiere($_SESSION['USER_ID'],$matiere_id,$date_mysql_debut,$date_mysql_fin);
	$competence_nb = count($tab_competence);
	if(!$competence_nb)
	{
		exit('Aucun item n\'a été évalué durant cette période pour cette matière !');
	}
	$tab_liste_comp = array_keys($tab_competence);
	$liste_comp = implode(',',$tab_liste_comp);

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	// Récupération de la liste des matières travaillées
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	$tab_matiere[$matiere_id] = $matiere_nom;

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	// Récupération de la liste des élèves
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	$tab_eleve = array(0 => array('eleve_id'=>$_SESSION['USER_ID'],'eleve_nom'=>$_SESSION['USER_NOM'],'eleve_prenom'=>$_SESSION['USER_PRENOM'],'eleve_id_gepi'=>$_SESSION['USER_ID_GEPI']) );
	// ...
	// ...
	// ...
	// ...
	$eleve_nb = 1;

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	// Récupération de la liste des résultats des évaluations associées à ces items, pour la matière selectionnée, sur la période sélectionnée
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	$date_mysql_debut = ($retroactif=='non') ? $date_mysql_debut : false;
	$DB_TAB = select_result_eleve($_SESSION['USER_ID'] , $liste_comp , $date_mysql_debut , $date_mysql_fin);
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_eval[$_SESSION['USER_ID']][$matiere_id][$DB_ROW['competence_id']][] = array('note'=>$DB_ROW['note'],'date'=>$DB_ROW['date'],'info'=>$DB_ROW['info']);
	}

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	// INCLUSION DU CODE COMMUN À PLUSIEURS PAGES
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	require('./_inc/code_releve_competence.php');

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	// On retourne les résultats
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	echo'<p><label class="alerte"><a class="lien_ext" href="'.$dossier.$fichier_lien.'_individuel.pdf">Téléchargez le relevé individuel au format PDF (imprimable).</a></label></p>';
	echo $releve_html_individuel;
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
