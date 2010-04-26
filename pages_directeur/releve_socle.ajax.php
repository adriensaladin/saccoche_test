<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010
 * 
 * ****************************************************************************************************
 * SACoche <http://competences.sesamath.net> - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath <http://www.sesamath.net> - Tous droits réservés.
 * Logiciel placé sous la licence libre GPL 3 <http://www.rodage.org/gpl-3.0.fr.html>.
 * ****************************************************************************************************
 * 
 * Ce fichier est une partie de SACoche.
 * 
 * SACoche est un logiciel libre ; vous pouvez le redistribuer ou le modifier suivant les termes 
 * de la “GNU General Public License” telle que publiée par la Free Software Foundation :
 * soit la version 3 de cette licence, soit (à votre gré) toute version ultérieure.
 * 
 * SACoche est distribué dans l’espoir qu’il vous sera utile, mais SANS AUCUNE GARANTIE :
 * sans même la garantie implicite de COMMERCIALISABILITÉ ni d’ADÉQUATION À UN OBJECTIF PARTICULIER.
 * Consultez la Licence Générale Publique GNU pour plus de détails.
 * 
 * Vous devriez avoir reçu une copie de la Licence Générale Publique GNU avec SACoche ;
 * si ce n’est pas le cas, consultez : <http://www.gnu.org/licenses/>.
 * 
 */

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}
if($_SESSION['STRUCTURE_ID']==ID_DEMO) {}

$detail       = (isset($_POST['f_detail']))      ? clean_texte($_POST['f_detail'])      : '';
$palier_id    = (isset($_POST['f_palier']))      ? clean_entier($_POST['f_palier'])     : 0;
$palier_nom   = (isset($_POST['f_palier_nom']))  ? clean_texte($_POST['f_palier_nom'])  : '';
$remplissage  = (isset($_POST['f_remplissage'])) ? clean_texte($_POST['f_remplissage']) : '';
$groupe_id    = (isset($_POST['f_groupe']))      ? clean_entier($_POST['f_groupe'])     : 0;
$tab_eleve_id = (isset($_POST['eleves']))        ? array_map('clean_entier',explode(',',$_POST['eleves'])) : array() ;

function positif($n) {return($n);}
$tab_eleve_id  = array_filter($tab_eleve_id,'positif');
$liste_eleve   = implode(',',$tab_eleve_id);

$test_affichage_scores = ($groupe_id && count($tab_eleve_id) && $remplissage=='plein') ? true : false;

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
		foreach($DB_TAB as $DB_ROW)
		{
			if( (!is_null($DB_ROW['pilier_id'])) && ($DB_ROW['pilier_id']!=$pilier_id) )
			{
				$pilier_id  = $DB_ROW['pilier_id'];
				$tab_pilier[$pilier_id] = array('pilier_nom'=>$DB_ROW['pilier_nom'],'pilier_nb_lignes'=>1);
			}
			if( (!is_null($DB_ROW['section_id'])) && ($DB_ROW['section_id']!=$section_id) )
			{
				$section_id  = $DB_ROW['section_id'];
				$tab_section[$pilier_id][$section_id] = $DB_ROW['section_nom'];
				$tab_pilier[$pilier_id]['pilier_nb_lignes']++;
			}
			if( (!is_null($DB_ROW['entree_id'])) && ($DB_ROW['entree_id']!=$socle_id) )
			{
				$socle_id = $DB_ROW['entree_id'];
				$tab_socle[$section_id][$socle_id] = $DB_ROW['entree_nom'];
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
	if($groupe_id && count($tab_eleve_id))
	{
		$tab_eleve = DB_lister_eleves_cibles($liste_eleve);
	}
	else
	{
		$tab_eleve[] = array('eleve_id'=>0,'eleve_nom'=>'','eleve_prenom'=>'');
	}

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	// Récupération de la liste des résultats (si demandé)
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	if($test_affichage_scores)
	{
		$DB_TAB = select_result_eleves_palier($liste_eleve , $liste_item , $date_debut=false , $date_fin=false);
		foreach($DB_TAB as $DB_ROW)
		{
			$tab_eval[$DB_ROW['eleve_id']][$DB_ROW['socle_id']][$DB_ROW['competence_id']][] = $DB_ROW['note'];
			$tab_competence[$DB_ROW['competence_id']] = array('competence_ref'=>$DB_ROW['competence_ref'],'competence_nom'=>$DB_ROW['competence_nom'],'matiere_id'=>$DB_ROW['matiere_id'],'calcul_methode'=>$DB_ROW['calcul_methode'],'calcul_limite'=>$DB_ROW['calcul_limite']);
		}
	}

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	// INCLUSION DU CODE COMMUN À PLUSIEURS PAGES
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	require('./_inc/code_releve_socle.php');

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	// On retourne les résultats
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	echo'<ul class="puce">';
	echo'<li><a class="lien_ext" href="./releve-html.php?fichier='.$fichier_lien.'">Attestation de maîtrise du socle commun au format HTML (bulles d\'information, détail...).</a></li>';
	echo'<li><a class="lien_ext" href="'.$dossier.$fichier_lien.'.pdf">Attestation de maîtrise du socle commun au format PDF (imprimable).</a></li>';
	echo'</ul><p />';
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
