<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010
 * 
 * ****************************************************************************************************
 * SACoche <http://sacoche.sesamath.net> - Suivi d'Acquisitions de Compétences
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
if($_SESSION['SESAMATH_ID']==ID_DEMO) {}

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
$aff_coef       = (isset($_POST['f_coef']))                        ? 1                                    : 0;
$aff_socle      = (isset($_POST['f_socle']))                       ? 1                                    : 0;
$aff_lien       = (isset($_POST['f_lien']))                        ? 1                                    : 0;
$aff_bilan_MS   = (isset($_POST['f_bilan_MS']))                    ? 1                                    : 0;
$aff_bilan_PA   = (isset($_POST['f_bilan_PA']))                    ? 1                                    : 0;
$aff_conv_sur20 = (isset($_POST['f_conv_sur20']))                  ? 1                                    : 0;
$groupe_id      = (isset($_POST['f_groupe']))                      ? clean_entier($_POST['f_groupe'])     : 0;
$groupe_nom     = (isset($_POST['f_groupe_nom']))                  ? clean_texte($_POST['f_groupe_nom'])  : '';
$tab_eleve      = (isset($_POST['eleves']))                        ? array_map('clean_entier',explode(',',$_POST['eleves'])) : array() ;
$tab_type       = (isset($_POST['types']))                         ? array_map('clean_texte',explode(',',$_POST['types']))   : array() ;
$format         = 'selection';

save_cookie_select($_SESSION['BASE'],$_SESSION['USER_ID']);

$tab_eleve     = array_filter($tab_eleve,'positif');
$liste_eleve   = implode(',',$tab_eleve);

if( $orientation && $marge_min && $couleur && $cases_nb && $cases_largeur && $cases_hauteur && $date_debut && $date_fin && $retroactif && $matiere_id && $groupe_id && $groupe_nom && count($tab_eleve) && count($tab_type) )
{

	ajouter_log_PHP( $log_objet='Demande de bilan' , $log_contenu=serialize($_POST) , $log_fichier=__FILE__ , $log_ligne=__LINE__ , $only_sesamath=true );

	// $tab_date = explode('/',$date_debut);
	$date_mysql_debut = false;
	// $tab_date = explode('/',$date_fin);
	$date_mysql_fin = false;

	$tab_item = array();	// [item_id] => array(item_ref,item_nom,item_coef,item_cart,item_socle,item_lien,calcul_methode,calcul_limite);
	$tab_liste_item = array();	// [i] => item_id
	$tab_eleve      = array();	// [i] => array(eleve_id,eleve_nom,eleve_prenom)
	$tab_matiere    = array();	// [matiere_id] => matiere_nom
	$tab_eval       = array();	// [eleve_id][matiere_id][item_id][devoir] => array(note,date,info) On utilise un tableau multidimensionnel vu qu'on ne sait pas à l'avance combien il y a d'évaluations pour un élève et un item donnés.

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	// Récupération de la liste des items travaillés durant la période choisie, pour la matière et les élèves selectionnés
	// Récupération de la liste des matières travaillées
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	$tab_compet_liste = (isset($_POST['f_compet_liste'])) ? explode('_',$_POST['f_compet_liste']) : array() ;
	$tab_compet_liste = array_map('clean_entier',$tab_compet_liste);
	$liste_compet = implode(',',$tab_compet_liste);
	list($tab_item,$tab_matiere) = DB_STRUCTURE_recuperer_arborescence_selection($liste_eleve,$liste_compet);
	// $tab_matiere déjà renseigné à la requête précédente.

	$item_nb = count($tab_item);
	if(!$item_nb)
	{
		exit('Aucun item sélectionné n\'a été évalué pour ces élèves !');
	}
	$tab_liste_item = array_keys($tab_item);
	$liste_comp = implode(',',$tab_liste_item);

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	// Récupération de la liste des élèves
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	$tab_eleve = DB_STRUCTURE_lister_eleves_cibles($liste_eleve);
	if(!is_array($tab_eleve))
	{
		exit('Aucun élève trouvé correspondant aux identifiants transmis !');
	}
	$eleve_nb = count($tab_eleve);

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	// Récupération de la liste des résultats des évaluations associées à ces items donnés d'une matiere donnée, pour les élèves selectionnés, sur la période sélectionnée
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	$date_mysql_debut = ($retroactif=='non') ? $date_mysql_debut : false;
	$DB_TAB = DB_STRUCTURE_lister_result_eleves_matieres($liste_eleve , $liste_comp , $date_mysql_debut , $date_mysql_fin);
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_eval[$DB_ROW['eleve_id']][$DB_ROW['matiere_id']][$DB_ROW['item_id']][] = array('note'=>$DB_ROW['note'],'date'=>$DB_ROW['date'],'info'=>$DB_ROW['info']);
	}

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	// INCLUSION DU CODE COMMUN À PLUSIEURS PAGES
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	require('./_inc/code_releve_bilan_item.php');

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
