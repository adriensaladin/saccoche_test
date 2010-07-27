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
$aff_bilan_ms   = (isset($_POST['f_bilan_ms']))    ? 1                                    : 0;	// en cas de manipulation type Firebug, peut être forcé pour l'élève avec (mb_substr_count($_SESSION['ELEVE_OPTIONS'],'ms'))
$aff_bilan_pv   = (isset($_POST['f_bilan_pv']))    ? 1                                    : 0;	// en cas de manipulation type Firebug, peut être forcé pour l'élève avec (mb_substr_count($_SESSION['ELEVE_OPTIONS'],'pv'))
$aff_conv_sur20 = (isset($_POST['f_conv_sur20']))  ? 1                                    : 0;	// en cas de manipulation type Firebug, peut être forcé pour l'élève à 0
$groupe_id      = (isset($_POST['f_groupe']))      ? clean_entier($_POST['f_groupe'])     : 0;	// en cas de manipulation type Firebug, peut être forcé pour l'élève à $_SESSION['ELEVE_CLASSE_ID']
$groupe_nom     = (isset($_POST['f_groupe_nom']))  ? clean_texte($_POST['f_groupe_nom'])  : '';	// en cas de manipulation type Firebug, peut être forcé pour l'élève à $_SESSION['ELEVE_CLASSE_NOM']
$tab_eleve      = (isset($_POST['eleves']))        ? array_map('clean_entier',explode(',',$_POST['eleves'])) : array() ;	// en cas de manipulation type Firebug, peut être forcé pour l'élève avec $_SESSION['USER_ID']
$tab_type       = (isset($_POST['types']))         ? array_map('clean_texte',explode(',',$_POST['types']))   : array() ;	// en cas de manipulation type Firebug, peut être forcé pour l'élève à 'individuel'
$format         = 'matiere';

save_cookie_select($_SESSION['BASE'],$_SESSION['USER_ID']);

function positif($n) {return $n;}
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
		$DB_ROW = DB_STRUCTURE_recuperer_dates_periode($groupe_id,$periode_id);
		if(!count($DB_ROW))
		{
			exit('La classe et la période ne sont pas reliées !');
		}
		$date_mysql_debut = $DB_ROW['jointure_date_debut'];
		$date_mysql_fin   = $DB_ROW['jointure_date_fin'];
		$date_debut = convert_date_mysql_to_french($date_mysql_debut);
		$date_fin   = convert_date_mysql_to_french($date_mysql_fin);
	}
	if($date_mysql_debut>$date_mysql_fin)
	{
		exit('La date de début est postérieure à la date de fin !');
	}

	$tab_item       = array();	// [item_id] => array(item_ref,item_nom,item_coef,item_socle,item_lien,calcul_methode,calcul_limite);
	$tab_liste_item = array();	// [i] => item_id
	$tab_eleve      = array();	// [i] => array(eleve_id,eleve_nom,eleve_prenom,eleve_id_gepi)
	$tab_matiere    = array();	// [matiere_id] => matiere_nom
	$tab_eval       = array();	// [eleve_id][matiere_id][item_id][devoir] => array(note,date,info) On utilise un tableau multidimensionnel vu qu'on ne sait pas à l'avance combien il y a d'évaluations pour un élève et un item donné.

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	// Récupération de la liste des items travaillés durant la période choisie, pour la matière et les élèves selectionnés
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	$tab_item = ($_SESSION['USER_PROFIL']=='eleve') ? DB_STRUCTURE_recuperer_arborescence_eleve_periode_matiere($_SESSION['USER_ID'],$matiere_id,$date_mysql_debut,$date_mysql_fin)
	                                                : DB_STRUCTURE_recuperer_arborescence_eleves_periode_matiere($liste_eleve,$matiere_id,$date_mysql_debut,$date_mysql_fin) ;
	$item_nb = count($tab_item);
	if(!$item_nb)
	{
		exit('Aucun item n\'a été évalué durant cette période pour cette matière et ces élèves !');
	}
	$tab_liste_item = array_keys($tab_item);
	$liste_item = implode(',',$tab_liste_item);

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	// Récupération de la liste des matières travaillées
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	$tab_matiere[$matiere_id] = $matiere_nom;

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	// Récupération de la liste des élèves
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	if($_SESSION['USER_PROFIL']=='eleve')
	{
		$tab_eleve[] = array('eleve_id'=>$_SESSION['USER_ID'],'eleve_nom'=>$_SESSION['USER_NOM'],'eleve_prenom'=>$_SESSION['USER_PRENOM'],'eleve_id_gepi'=>$_SESSION['USER_ID_GEPI']);
	}
	else
	{
		$tab_eleve = DB_STRUCTURE_lister_eleves_cibles($liste_eleve);
		if(!is_array($tab_eleve))
		{
			exit('Aucun élève trouvé correspondant aux identifiants transmis !');
		}
	}
	$eleve_nb = count($tab_eleve);

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	// Récupération de la liste des résultats des évaluations associées à ces items donnés d'une matiere donnée, pour les élèves selectionnés, sur la période sélectionnée
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	$date_mysql_debut = ($retroactif=='non') ? $date_mysql_debut : false;
	$DB_TAB = DB_STRUCTURE_lister_result_eleves_matiere($liste_eleve , $liste_item , $date_mysql_debut , $date_mysql_fin) ;
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_eval[$DB_ROW['eleve_id']][$matiere_id][$DB_ROW['item_id']][] = array('note'=>$DB_ROW['note'],'date'=>$DB_ROW['date'],'info'=>$DB_ROW['info']);
	}

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	// Elaboration du relevé de compétences, en HTML et PDF
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	require('./_inc/code_releve_competence.php');	// CODE COMMUN INCLUS POUR LES RELEVES MATIERE / MULTI-MATIERES / SELECTION

	// Affichage du résultat
	if($_SESSION['USER_PROFIL']=='eleve')
	{
		echo'<ul class="puce">';
		echo'<li><label class="alerte"><a class="lien_ext" href="'.$dossier.$fichier_lien.'_individuel.pdf">Téléchargez le relevé individuel au format PDF (imprimable).</a></label></li>';
		echo'</ul><p />';
		echo $releve_html_individuel;
	}
	else
	{
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

}

else
{
	echo'Erreur avec les données transmises !';
}
?>
