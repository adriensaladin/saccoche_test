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

$matiere_id    = (isset($_POST['f_matiere']))     ? clean_entier($_POST['f_matiere'])    : 0;
$niveau_id     = (isset($_POST['f_niveau']))      ? clean_entier($_POST['f_niveau'])     : 0;
$matiere_nom   = (isset($_POST['f_matiere_nom'])) ? clean_texte($_POST['f_matiere_nom']) : '';
$niveau_nom    = (isset($_POST['f_niveau_nom']))  ? clean_texte($_POST['f_niveau_nom'])  : '';
$remplissage   = (isset($_POST['f_remplissage'])) ? clean_texte($_POST['f_remplissage']) : '';
$orientation   = (isset($_POST['f_orientation'])) ? clean_texte($_POST['f_orientation']) : '';
$marge_min     = (isset($_POST['f_marge_min']))   ? clean_texte($_POST['f_marge_min'])   : '';
$couleur       = (isset($_POST['f_couleur']))     ? clean_texte($_POST['f_couleur'])     : '';
$cases_nb      = (isset($_POST['f_cases_nb']))    ? clean_entier($_POST['f_cases_nb'])   : 0;
$cases_largeur = (isset($_POST['f_cases_larg']))  ? clean_entier($_POST['f_cases_larg']) : 0;
$cases_hauteur = (isset($_POST['f_cases_haut']))  ? clean_entier($_POST['f_cases_haut']) : 0;
$aff_coef      = (isset($_POST['f_coef']))        ? 1                                    : 0;
$aff_socle     = (isset($_POST['f_socle']))       ? 1                                    : 0;
$aff_lien      = (isset($_POST['f_lien']))        ? 1                                    : 0;
$groupe_id     = true;
$tab_eleve[]   = $_SESSION['USER_ID'];

save_cookie_select($_SESSION['STRUCTURE_ID'],$_SESSION['USER_ID']);

// ...
// ...
$liste_eleve   = $_SESSION['USER_ID'];

if( $matiere_id && $niveau_id && $matiere_nom && $niveau_nom && $remplissage && $orientation && $marge_min && $couleur && $cases_nb && $cases_largeur && $cases_hauteur )
{

	$tab_domaine    = array();	// [domaine_id] => array(domaine_ref,domaine_nom,domaine_nb_lignes);
	$tab_theme      = array();	// [domaine_id][theme_id] => array(theme_ref,theme_nom,theme_nb_lignes);
	$tab_competence = array();	// [theme_id][competence_id] => array(competence_ref,competence_nom,competence_coef,competence_socle,competence_lien);
	$tab_liste_comp = array();	// [i] => competence_id
	$tab_eleve      = array();	// [i] => array(eleve_id,eleve_nom,eleve_prenom)
	$tab_eval       = array();	// [eleve_id][competence_id] => array(note,date,info)

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	// Récupération de la liste des items pour la matière et le niveau sélectionné
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	$DB_TAB = DB_select_arborescence($_SESSION['STRUCTURE_ID'],$prof_id=0,$matiere_id,$niveau_id,$socle_nom=false);
	if(count($DB_TAB))
	{
		$domaine_id    = 0;
		$theme_id      = 0;
		$competence_id = 0;
		foreach($DB_TAB as $DB_ROW)
		{
			if( (!is_null($DB_ROW['livret_domaine_id'])) && ($DB_ROW['livret_domaine_id']!=$domaine_id) )
			{
				$domaine_id  = $DB_ROW['livret_domaine_id'];
				$domaine_ref = $DB_ROW['livret_niveau_ref'].'.'.$DB_ROW['livret_domaine_ref'];
				$tab_domaine[$domaine_id] = array('domaine_ref'=>$domaine_ref,'domaine_nom'=>$DB_ROW['livret_domaine_nom'],'domaine_nb_lignes'=>2);
			}
			if( (!is_null($DB_ROW['livret_theme_id'])) && ($DB_ROW['livret_theme_id']!=$theme_id) )
			{
				$theme_id  = $DB_ROW['livret_theme_id'];
				$theme_ref = $DB_ROW['livret_niveau_ref'].'.'.$DB_ROW['livret_domaine_ref'].$DB_ROW['livret_theme_ordre'];
				$first_theme_of_domaine = (isset($tab_theme[$domaine_id])) ? false : true ;
				$tab_theme[$domaine_id][$theme_id] = array('theme_ref'=>$theme_ref,'theme_nom'=>$DB_ROW['livret_theme_nom'],'theme_nb_lignes'=>1);
			}
			if( (!is_null($DB_ROW['livret_competence_id'])) && ($DB_ROW['livret_competence_id']!=$competence_id) )
			{
				$competence_id = $DB_ROW['livret_competence_id'];
				$competence_ref = $DB_ROW['livret_niveau_ref'].'.'.$DB_ROW['livret_domaine_ref'].$DB_ROW['livret_theme_ordre'].$DB_ROW['livret_competence_ordre'];
				$tab_competence[$theme_id][$competence_id] = array('competence_ref'=>$competence_ref,'competence_nom'=>$DB_ROW['livret_competence_nom'],'competence_coef'=>$DB_ROW['livret_competence_coef'],'competence_socle'=>$DB_ROW['livret_socle_id'],'competence_lien'=>$DB_ROW['livret_competence_lien']);
				$tab_theme[$domaine_id][$theme_id]['theme_nb_lignes']++;
				if($first_theme_of_domaine)
				{
					$tab_domaine[$domaine_id]['domaine_nb_lignes']++;
				}
				$tab_liste_comp[] = $competence_id;
			}
		}
		$liste_comp = implode(',',$tab_liste_comp);
	}
	else
	{
		exit('Aucun item référencé pour cette matière et ce niveau !');
	}

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	// Récupération de la liste des élèves (si demandé)
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	$tab_eleve = array(0 => array('eleve_id'=>$_SESSION['USER_ID'],'eleve_login'=>$_SESSION['USER_LOGIN'],'eleve_nom'=>$_SESSION['USER_NOM'],'eleve_prenom'=>$_SESSION['USER_PRENOM']) );
	// ...
	// ...
	// ...
	// ...
	// ...
	// ...
	// ...

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	// Récupération de la liste des résultats (si demandé)
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	if($remplissage=='plein')
	{
		$DB_TAB = select_result_eleve($_SESSION['USER_ID'] , $liste_comp , $date_debut=false , $date_fin=false);
		foreach($DB_TAB as $DB_ROW)
		{
			$tab_eval[$_SESSION['USER_ID']][$DB_ROW['competence_id']][] = array('note'=>$DB_ROW['note'],'date'=>$DB_ROW['date'],'info'=>$DB_ROW['info']);
		}
	}
	// On tronque s'il y en a trop
	foreach($tab_eleve as $eleve_id)
	{
		foreach($tab_liste_comp as $competence_id)
		{
			$eval_nb = (isset($tab_eval[$_SESSION['USER_ID']][$competence_id])) ? count($tab_eval[$_SESSION['USER_ID']][$competence_id]) : 0;
			if($eval_nb>$cases_nb)
			{
				$tab_eval[$_SESSION['USER_ID']][$competence_id] = array_slice($tab_eval[$_SESSION['USER_ID']][$competence_id],$eval_nb-$cases_nb);
			}
		}
	}

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	// INCLUSION DU CODE COMMUN À PLUSIEURS PAGES
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	require('./_inc/code_releve_grille.php');

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	// On retourne les résultats
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	echo'<p><label class="alerte"><a class="lien_ext" href="'.$dossier.$fichier_lien.'.pdf">Téléchargez au format PDF le fichier généré avec la grille de compétences (selon les options choisies).</a></label></p>';
	echo $releve_html;
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
