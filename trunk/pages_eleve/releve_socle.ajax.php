<?php
/**
 * @version $Id: releve_socle.ajax.php 8 2009-10-30 20:56:02Z thomas $
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2009
 * 
 * ****************************************************************************************************
 * SACoche [http://socles.sesamath.net] - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath [http://www.sesamath.net]
 * Distribution sous licence libre prévue pour l'été 2010.
 * ****************************************************************************************************
 * 
 */

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}
if($_SESSION['STRUCTURE_ID']==ID_DEMO) {}

$detail       = (isset($_POST['f_detail']))      ? clean_texte($_POST['f_detail'])      : '';
$palier_id    = (isset($_POST['f_palier']))      ? clean_entier($_POST['f_palier'])     : 0;
$palier_nom   = (isset($_POST['f_palier_nom']))  ? clean_texte($_POST['f_palier_nom'])  : '';
$remplissage  = (isset($_POST['f_remplissage'])) ? clean_texte($_POST['f_remplissage']) : '';
$groupe_id     = true;
$tab_eleve[]   = $_SESSION['USER_ID'];

// ...
// ...
$liste_eleve   = $_SESSION['USER_ID'];

$test_affichage_scores = ($remplissage=='plein') ? true : false;

if( $detail && $palier_id && $palier_nom && $remplissage )
{

	$tab_pilier     = array();	// [pilier_id] => array(pilier_nom,pilier_nb_lignes);
	$tab_section    = array();	// [pilier_id][section_id] => section_nom;
	$tab_socle      = array();	// [section_id][socle_id] => socle_nom;
	$tab_liste_item = array();	// [i] => socle_id
	$tab_eleve      = array();	// [i] => array(eleve_id,eleve_nom,eleve_prenom)
	$tab_eval       = array();	// [eleve_id][socle_id][competence_id] => note
	$tab_competence = array();	// [competence_id] => array(competence_ref,competence_nom,calcul_methode,calcul_limite);

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	// Récupération de la liste des items pour la matière et le niveau sélectionné
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	$DB_TAB = DB_select_arborescence_palier($palier_id);
	if(count($DB_TAB))
	{
		$pilier_id  = 0;
		$section_id = 0;
		$socle_id   = 0;
		foreach($DB_TAB as $key => $DB_ROW)
		{
			if( (!is_null($DB_ROW['livret_pilier_id'])) && ($DB_ROW['livret_pilier_id']!=$pilier_id) )
			{
				$pilier_id  = $DB_ROW['livret_pilier_id'];
				$tab_pilier[$pilier_id] = array('pilier_nom'=>$DB_ROW['livret_pilier_nom'],'pilier_nb_lignes'=>1);
			}
			if( (!is_null($DB_ROW['livret_section_id'])) && ($DB_ROW['livret_section_id']!=$section_id) )
			{
				$section_id  = $DB_ROW['livret_section_id'];
				$tab_section[$pilier_id][$section_id] = $DB_ROW['livret_section_nom'];
				$tab_pilier[$pilier_id]['pilier_nb_lignes']++;
			}
			if( (!is_null($DB_ROW['livret_socle_id'])) && ($DB_ROW['livret_socle_id']!=$socle_id) )
			{
				$socle_id = $DB_ROW['livret_socle_id'];
				$tab_socle[$section_id][$socle_id] = $DB_ROW['livret_socle_nom'];
				$tab_pilier[$pilier_id]['pilier_nb_lignes']++;
				$tab_liste_item[] = $socle_id;
			}
		}
		$liste_item = implode(',',$tab_liste_item);
	}
	else
	{
		exit('Aucun item référencé pour ce palier du socle commun !');
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
	if($test_affichage_scores)
	{
		$DB_TAB = select_result_eleves_palier($_SESSION['USER_ID'] , $liste_item , $date_debut=false , $date_fin=false);
		foreach($DB_TAB as $key => $DB_ROW)
		{
			$tab_eval[$_SESSION['USER_ID']][$DB_ROW['socle_id']][$DB_ROW['competence_id']][] = $DB_ROW['note'];
			$tab_competence[$DB_ROW['competence_id']] = array('competence_ref'=>$DB_ROW['competence_ref'],'competence_nom'=>$DB_ROW['competence_nom'],'matiere_id'=>$DB_ROW['livret_matiere_id'],'calcul_methode'=>$DB_ROW['calcul_methode'],'calcul_limite'=>$DB_ROW['calcul_limite']);
		}
	}

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	// INCLUSION DU CODE COMMUN À PLUSIEURS PAGES
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	require('./_inc/code_releve_socle.php');

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	// On retourne les résultats
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	echo'<p><label class="alerte"><a class="lien_ext" href="'.$dossier.$fichier_lien.'.pdf">Téléchargez au format PDF l\'attestation de maîtrise du socle commun (selon les options choisies).</a></label></p>';
	echo $releve_html;
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
