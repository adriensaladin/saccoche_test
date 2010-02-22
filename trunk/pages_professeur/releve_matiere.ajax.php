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
$aff_coef       = (isset($_POST['f_coef']))        ? true                                 : false;
$aff_socle      = (isset($_POST['f_socle']))       ? true                                 : false;
$aff_lien       = (isset($_POST['f_lien']))        ? true                                 : false;
$aff_bilan_ms   = (isset($_POST['f_bilan_ms']))    ? 1                                    : 0;	// pas true / false car utilisé dans un calcul
$aff_bilan_pv   = (isset($_POST['f_bilan_pv']))    ? 1                                    : 0;	// pas true / false car utilisé dans un calcul
$aff_conv_sur20 = (isset($_POST['f_conv_sur20']))  ? true                                 : false;
$groupe_id      = (isset($_POST['f_groupe']))      ? clean_entier($_POST['f_groupe'])     : 0;
$groupe_nom     = (isset($_POST['f_groupe_nom']))  ? clean_texte($_POST['f_groupe_nom'])  : '';
$tab_eleve      = (isset($_POST['eleves']))        ? array_map('clean_entier',explode(',',$_POST['eleves'])) : array() ;
$tab_type       = (isset($_POST['types']))         ? array_map('clean_texte',explode(',',$_POST['types']))   : array() ;
$format         = 'matiere';

save_cookie_select($_SESSION['STRUCTURE_ID'],$_SESSION['USER_ID']);

function positif($n) {return($n);}
$tab_eleve     = array_filter($tab_eleve,'positif');
$liste_eleve   = implode(',',$tab_eleve);

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
	$tab_eleve      = array();	// [i] => array(eleve_id,eleve_nom,eleve_prenom,eleve_id_gepi)
	$tab_matiere    = array();	// [matiere_id] => matiere_nom
	$tab_eval       = array();	// [eleve_id][matiere_id][competence_id][devoir] => array(note,date,info) On utilise un tableau multidimensionnel vu qu'on ne sait pas à l'avance combien il y a d'évaluations pour un élève et un item donnés.

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	// Récupération de la liste des items travaillés durant la période choisie, pour la matière et les élèves selectionnés
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	$tab_competence = select_arborescence_eleves_periode_matiere($liste_eleve,$matiere_id,$date_mysql_debut,$date_mysql_fin);
	$competence_nb = count($tab_competence);
	if(!$competence_nb)
	{
		exit('Aucun item n\'a été évalué durant cette période pour cette matière et ces élèves !');
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
	$tab_eleve = DB_lister_eleves_donnes($_SESSION['STRUCTURE_ID'],$liste_eleve);
	if(!is_array($tab_eleve))
	{
		exit('Aucun élève trouvé correspondant aux identifiants transmis !');
	}
	$eleve_nb = count($tab_eleve);

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	// Récupération de la liste des résultats des évaluations associées à ces items donnés d'une matiere donnée, pour les élèves selectionnés, sur la période sélectionnée
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	$date_mysql_debut = ($retroactif=='non') ? $date_mysql_debut : false;
	$DB_TAB = select_result_eleves_matiere($liste_eleve , $liste_comp , $date_mysql_debut , $date_mysql_fin);
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_eval[$DB_ROW['eleve_id']][$matiere_id][$DB_ROW['competence_id']][] = array('note'=>$DB_ROW['note'],'date'=>$DB_ROW['date'],'info'=>$DB_ROW['info']);
	}

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	// INCLUSION DU CODE COMMUN À PLUSIEURS PAGES
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	require('./_inc/code_releve_competence.php');

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	// On retourne les résultats
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	if(in_array('bulletin',$tab_type))
	{
		echo'<ul class="puce">';
		echo'<li><a class="lien_ext" href="./releve-html.php?fichier='.$fichier_lien.'_bulletin">Bulletin au format HTML (tableaux triables, liens...).</a></li>';
		echo'<li><a class="lien_ext" href="'.$dossier.$fichier_lien.'_bulletin.csv">Bulletin au format CSV importable dans GEPI.</a></li>';
		echo'</ul><p />';
	}
	if(in_array('synthese',$tab_type))
	{
		echo'<ul class="puce">';
		echo'<li><a class="lien_ext" href="./releve-html.php?fichier='.$fichier_lien.'_synthese">Synthèse collective au format HTML (tableaux triables, liens...).</a></li>';
		echo'<li><a class="lien_ext" href="'.$dossier.$fichier_lien.'_synthese.pdf">Synthèse collective au format PDF (imprimable).</a></li>';
		echo'</ul><p />';
	}
	if(in_array('individuel',$tab_type))
	{
		echo'<ul class="puce">';
		echo'<li><a class="lien_ext" href="./releve-html.php?fichier='.$fichier_lien.'_individuel">Relevé individuel au format HTML (tableaux triables, liens...).</a></li>';
		echo'<li><a class="lien_ext" href="'.$dossier.$fichier_lien.'_individuel.pdf">Relevé individuel au format PDF (imprimable).</a></li>';
		echo'</ul><p />';
	}
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
