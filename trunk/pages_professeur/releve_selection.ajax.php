<?php
/**
 * @version $Id: releve_selection.ajax.php 8 2009-10-30 20:56:02Z thomas $
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

$orientation    = (isset($_POST['f_orientation']))                 ? clean_texte($_POST['f_orientation']) : '';
$marge_min      = (isset($_POST['f_marge_min']))                   ? clean_texte($_POST['f_marge_min'])   : '';
$couleur        = (isset($_POST['f_couleur']))                     ? clean_texte($_POST['f_couleur'])     : '';
$cases_nb       = (isset($_POST['f_cases_nb']))                    ? clean_entier($_POST['f_cases_nb'])   : 0;
$cases_largeur  = (isset($_POST['f_cases_larg']))                  ? clean_entier($_POST['f_cases_larg']) : 0;
$cases_hauteur  = (isset($_POST['f_cases_haut']))                  ? clean_entier($_POST['f_cases_haut']) : 0;
$date_debut     = true;
$date_fin       = true;
$retroactif     = true;
$matiere_id     = true;
$matiere_nom    = '';
$aff_coef       = (isset($_POST['f_coef']))                        ? true                                 : false;
$aff_socle      = (isset($_POST['f_socle']))                       ? true                                 : false;
$aff_lien       = (isset($_POST['f_lien']))                        ? true                                 : false;
$aff_bilan_ms   = (isset($_POST['f_bilan_ms']))                    ? 1                                    : 0;	// pas true / false car utilisé dans un calcul
$aff_bilan_pv   = (isset($_POST['f_bilan_pv']))                    ? 1                                    : 0;	// pas true / false car utilisé dans un calcul
$aff_conv_sur20 = (isset($_POST['f_conv_sur20']))                  ? true                                 : false;
$groupe_id      = (isset($_POST['f_groupe']))                      ? clean_entier($_POST['f_groupe'])     : 0;
$groupe_nom     = (isset($_POST['f_groupe_nom']))                  ? clean_texte($_POST['f_groupe_nom'])  : '';
$tab_eleve      = (isset($_POST['eleves']))                        ? array_map('clean_entier',explode(',',$_POST['eleves'])) : array() ;
$tab_type       = (isset($_POST['types']))                         ? array_map('clean_texte',explode(',',$_POST['types']))   : array() ;
$format         = 'selection';

save_cookie_select($_SESSION['STRUCTURE_ID'],$_SESSION['USER_ID']);

function positif($n) {return($n);}
$tab_eleve     = array_filter($tab_eleve,'positif');
$liste_eleve   = implode(',',$tab_eleve);

if( $orientation && $marge_min && $couleur && $cases_nb && $cases_largeur && $cases_hauteur && $date_debut && $date_fin && $retroactif && $matiere_id && $groupe_id && $groupe_nom && count($tab_eleve) && count($tab_type) )
{

	// $tab_date = explode('/',$date_debut);
	$date_mysql_debut = false;
	// $tab_date = explode('/',$date_fin);
	$date_mysql_fin = false;

	$tab_competence = array();	// [competence_id] => array(competence_ref,competence_nom,competence_coef,competence_socle,competence_lien);
	$tab_liste_comp = array();	// [i] => competence_id
	$tab_eleve      = array();	// [i] => array(eleve_id,eleve_nom,eleve_prenom)
	$tab_matiere    = array();	// [matiere_id] => matiere_nom
	$tab_eval       = array();	// [eleve_id][matiere_id][competence_id][devoir] => array(note,date,info) On utilise un tableau multidimensionnel vu qu'on ne sait pas à l'avance combien il y a d'évaluations pour un élève et un item donnés.

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	// Récupération de la liste des items travaillés durant la période choisie, pour la matière et les élèves selectionnés
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	$tab_compet_liste = (isset($_POST['f_compet_liste'])) ? explode('_',$_POST['f_compet_liste']) : array() ;
	$tab_compet_liste = array_map('clean_entier',$tab_compet_liste);
	$liste_compet = implode(',',$tab_compet_liste);
	list($tab_competence,$tab_matiere) = select_arborescence_et_matieres_eleves_competence($liste_eleve,$liste_compet);
	$competence_nb = count($tab_competence);
	if(!$competence_nb)
	{
		exit('Aucun item sélectionné n\'a été évalué pour ces élèves !');
	}
	$tab_liste_comp = array_keys($tab_competence);
	$liste_comp = implode(',',$tab_liste_comp);

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	// Récupération de la liste des matières travaillées
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	// $tab_matiere déjà renseigné à la requête précédente.

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
	$DB_TAB = select_result_eleves_matieres($liste_eleve , $liste_comp , $date_mysql_debut , $date_mysql_fin);
	foreach($DB_TAB as $key => $DB_ROW)
	{
		$tab_eval[$DB_ROW['eleve_id']][$DB_ROW['matiere_id']][$DB_ROW['competence_id']][] = array('note'=>$DB_ROW['note'],'date'=>$DB_ROW['date'],'info'=>$DB_ROW['info']);
	}

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	// INCLUSION DU CODE COMMUN À PLUSIEURS PAGES
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	require('./_inc/code_releve_competence.php');

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	// On retourne les résultats
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
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
