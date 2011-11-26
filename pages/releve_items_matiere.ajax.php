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

$orientation    = (isset($_POST['f_orientation'])) ? clean_texte($_POST['f_orientation'])  : '';
$couleur        = (isset($_POST['f_couleur']))     ? clean_texte($_POST['f_couleur'])      : '';
$legende        = (isset($_POST['f_legende']))     ? clean_texte($_POST['f_legende'])      : '';
$marge_min      = (isset($_POST['f_marge_min']))   ? clean_entier($_POST['f_marge_min'])   : 0;
$pages_nb       = (isset($_POST['f_pages_nb']))    ? clean_texte($_POST['f_pages_nb'])     : '';
$cases_nb       = (isset($_POST['f_cases_nb']))    ? clean_entier($_POST['f_cases_nb'])    : 0;
$cases_largeur  = (isset($_POST['f_cases_larg']))  ? clean_entier($_POST['f_cases_larg'])  : 0;
$periode_id     = (isset($_POST['f_periode']))     ? clean_entier($_POST['f_periode'])     : 0;
$date_debut     = (isset($_POST['f_date_debut']))  ? clean_texte($_POST['f_date_debut'])   : '';
$date_fin       = (isset($_POST['f_date_fin']))    ? clean_texte($_POST['f_date_fin'])     : '';
$retroactif     = (isset($_POST['f_retroactif']))  ? clean_texte($_POST['f_retroactif'])   : '';
$matiere_id     = (isset($_POST['f_matiere']))     ? clean_entier($_POST['f_matiere'])     : 0;
$matiere_nom    = (isset($_POST['f_matiere_nom'])) ? clean_texte($_POST['f_matiere_nom'])  : '';
$only_socle     = (isset($_POST['f_restriction'])) ? 1                                     : 0;
$aff_coef       = (isset($_POST['f_coef']))        ? 1                                     : 0;
$aff_socle      = (isset($_POST['f_socle']))       ? 1                                     : 0;
$aff_lien       = (isset($_POST['f_lien']))        ? 1                                     : 0;
$aff_bilan_MS   = (isset($_POST['f_bilan_MS']))    ? 1                                     : 0;
$aff_bilan_PA   = (isset($_POST['f_bilan_PA']))    ? 1                                     : 0;
$aff_conv_sur20 = (isset($_POST['f_conv_sur20']))  ? 1                                     : 0;
$groupe_id      = (isset($_POST['f_groupe']))      ? clean_entier($_POST['f_groupe'])      : 0;
$groupe_nom     = (isset($_POST['f_groupe_nom']))  ? clean_texte($_POST['f_groupe_nom'])   : '';
$tab_eleve      = (isset($_POST['eleves']))        ? array_map('clean_entier',explode(',',$_POST['eleves'])) : array() ;
$tab_type       = (isset($_POST['types']))         ? array_map('clean_texte',explode(',',$_POST['types']))   : array() ;
$format         = 'matiere';

// En cas de manipulation du formulaire (avec Firebug par exemple) ; on pourrait aussi vérifier pour un parent que c'est bien un de ses enfants...
if(in_array($_SESSION['USER_PROFIL'],array('parent','eleve')))
{
	$aff_bilan_MS   = (mb_substr_count($_SESSION['DROIT_BILAN_MOYENNE_SCORE']     ,$_SESSION['USER_PROFIL'])) ? 1 : 0 ;
	$aff_bilan_PA   = (mb_substr_count($_SESSION['DROIT_BILAN_POURCENTAGE_ACQUIS'],$_SESSION['USER_PROFIL'])) ? 1 : 0 ;
	$aff_conv_sur20 = (mb_substr_count($_SESSION['DROIT_BILAN_NOTE_SUR_VINGT']    ,$_SESSION['USER_PROFIL'])) ? 1 : 0 ;
	$tab_type       = array('individuel');
}
if($_SESSION['USER_PROFIL']=='eleve')
{
	$groupe_id  = $_SESSION['ELEVE_CLASSE_ID'];
	$groupe_nom = $_SESSION['ELEVE_CLASSE_NOM'];
	$tab_eleve  = array($_SESSION['USER_ID']);
}

$type_individuel = (in_array('individuel',$tab_type)) ? 1 : 0 ;
$type_synthese   = (in_array('synthese',$tab_type))   ? 1 : 0 ;
$type_bulletin   = (in_array('bulletin',$tab_type))   ? 1 : 0 ;

$tab_eleve     = array_filter($tab_eleve,'positif');
$liste_eleve   = implode(',',$tab_eleve);

if( $orientation && $couleur && $legende && $marge_min && $pages_nb && $cases_nb && $cases_largeur && ( $periode_id || ($date_debut && $date_fin) ) && $retroactif && $matiere_id && $matiere_nom && $groupe_id && $groupe_nom && count($tab_eleve) && count($tab_type) )
{

	Formulaire::save_choix('items_matiere');

	// Période concernée
	if($periode_id==0)
	{
		$date_mysql_debut = convert_date_french_to_mysql($date_debut);
		$date_mysql_fin   = convert_date_french_to_mysql($date_fin);
	}
	else
	{
		$DB_ROW = DB_STRUCTURE_COMMUN::DB_recuperer_dates_periode($groupe_id,$periode_id);
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

	$tab_item       = array();	// [item_id] => array(item_ref,item_nom,item_coef,item_cart,item_socle,item_lien,calcul_methode,calcul_limite);
	$tab_liste_item = array();	// [i] => item_id
	$tab_eleve      = array();	// [i] => array(eleve_id,eleve_nom,eleve_prenom,eleve_id_gepi)
	$tab_matiere    = array();	// [matiere_id] => matiere_nom
	$tab_eval       = array();	// [eleve_id][matiere_id][item_id][devoir] => array(note,date,info) On utilise un tableau multidimensionnel vu qu'on ne sait pas à l'avance combien il y a d'évaluations pour un élève et un item donné.
	$tab_matiere_for_item = array();	// [item_id] => matiere_id

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	// Récupération de la liste des items travaillés durant la période choisie, pour la matière et les élèves selectionnés
	// Récupération de la liste des matières travaillées
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	$tab_item = DB_STRUCTURE_BILAN::DB_recuperer_arborescence_bilan($liste_eleve,$matiere_id,$only_socle,$date_mysql_debut,$date_mysql_fin) ; // $liste_eleve ne vaut que $_SESSION['USER_ID'] si $_SESSION['USER_PROFIL']=='eleve'
	$tab_matiere[$matiere_id] = $matiere_nom;

	$item_nb = count($tab_item);
	if(!$item_nb)
	{
		exit('Aucun item n\'a été évalué durant cette période pour cette matière et ces élèves !');
	}
	$tab_liste_item = array_keys($tab_item);
	$liste_item = implode(',',$tab_liste_item);

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	// Récupération de la liste des élèves
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	if($_SESSION['USER_PROFIL']=='eleve')
	{
		$tab_eleve[] = array('eleve_id'=>$_SESSION['USER_ID'],'eleve_nom'=>$_SESSION['USER_NOM'],'eleve_prenom'=>$_SESSION['USER_PRENOM'],'eleve_id_gepi'=>$_SESSION['USER_ID_GEPI']);
	}
	else
	{
		$tab_eleve = DB_STRUCTURE_BILAN::DB_lister_eleves_cibles($liste_eleve,$with_gepi=TRUE,$with_langue=FALSE);
		if(!is_array($tab_eleve))
		{
			exit('Aucun élève trouvé correspondant aux identifiants transmis !');
		}
	}
	$eleve_nb = count($tab_eleve);

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	// Récupération de la liste des résultats des évaluations associées à ces items donnés d'une matiere donnée, pour les élèves selectionnés, sur la période sélectionnée
	// Attention, il faut éliminer certains items qui peuvent potentiellement apparaitre dans des relevés d'élèves alors qu'ils n'ont pas été interrogés sur la période considérée (mais un camarade oui).
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	$tab_score_a_garder = array();
	$DB_TAB = DB_STRUCTURE_BILAN::DB_lister_date_last_eleves_items($liste_eleve,$liste_item);
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_score_a_garder[$DB_ROW['eleve_id']][$DB_ROW['item_id']] = ($DB_ROW['date_last']<$date_mysql_debut) ? false : true ;
	}

	$date_mysql_debut = ($retroactif=='non') ? $date_mysql_debut : false;
	$DB_TAB = DB_STRUCTURE_BILAN::DB_lister_result_eleves_matiere($liste_eleve , $liste_item , $date_mysql_debut , $date_mysql_fin , $_SESSION['USER_PROFIL']) ;
	foreach($DB_TAB as $DB_ROW)
	{
		if($tab_score_a_garder[$DB_ROW['eleve_id']][$DB_ROW['item_id']])
		{
			$tab_eval[$DB_ROW['eleve_id']][$matiere_id][$DB_ROW['item_id']][] = array('note'=>$DB_ROW['note'],'date'=>$DB_ROW['date'],'info'=>$DB_ROW['info']);
			$tab_matiere_for_item[$DB_ROW['item_id']] = $matiere_id;	// sert pour la synthèse sur une sélection d'items issus de différentes matières
		}
	}
	$matiere_nb = count(array_unique($tab_matiere_for_item)); // 1 ici

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	// INCLUSION DU CODE COMMUN À PLUSIEURS PAGES
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	require('./_inc/code_releve_bilan_item.php');

	// Affichage du résultat
	if($affichage_direct)
	{
		echo'<hr />';
		echo'<ul class="puce">';
		echo'<li><a class="lien_ext" href="'.$dossier.$fichier_lien.'_individuel.pdf"><span class="file file_pdf">Archiver / Imprimer (format <em>pdf</em>).</span></a></li>';
		echo'</ul>';
		echo $releve_HTML_individuel;
	}
	else
	{
		if($type_bulletin)
		{
			echo'<h2>Bulletin</h2>';
			echo'<ul class="puce">';
			echo'<li><a class="lien_ext" href="'.$dossier.$fichier_lien.'_bulletin_note_appreciation.csv"><span class="file file_txt">Récupérer notes et appréciations à importer dans GEPI (format <em>csv</em> <img alt="" src="./_img/bulle_aide.png" title="Si le navigateur ouvre le fichier au lieu de l\'enregistrer, cliquer avec le bouton droit et choisir «&nbsp;Enregistrer&nbsp;sous...&nbsp;»." />).</span></a></li>';
			echo'<li><a class="lien_ext" href="'.$dossier.$fichier_lien.'_bulletin_note.csv"><span class="file file_txt">Récupérer les notes à importer dans GEPI (format <em>csv</em> <img alt="" src="./_img/bulle_aide.png" title="Si le navigateur ouvre le fichier au lieu de l\'enregistrer, cliquer avec le bouton droit et choisir «&nbsp;Enregistrer&nbsp;sous...&nbsp;»." />).</span></a></li>';
			echo'<li><a class="lien_ext" href="'.$dossier.$fichier_lien.'_bulletin_appreciation.csv"><span class="file file_txt">Récupérer les appréciations à importer dans GEPI (format <em>csv</em> <img alt="" src="./_img/bulle_aide.png" title="Si le navigateur ouvre le fichier au lieu de l\'enregistrer, cliquer avec le bouton droit et choisir «&nbsp;Enregistrer&nbsp;sous...&nbsp;»." />).</span></a></li>';
			echo'<li><a class="lien_ext" href="./releve-html.php?fichier='.$fichier_lien.'_bulletin"><span class="file file_htm">Explorer / Manipuler (format <em>html</em>).</span></a></li>';
			echo'</ul>';
		}
		if($type_synthese)
		{
			echo'<h2>Synthèse collective</h2>';
			echo'<ul class="puce">';
			echo'<li><a class="lien_ext" href="'.$dossier.$fichier_lien.'_synthese.pdf"><span class="file file_pdf">Archiver / Imprimer (format <em>pdf</em>).</span></a></li>';
			echo'<li><a class="lien_ext" href="./releve-html.php?fichier='.$fichier_lien.'_synthese"><span class="file file_htm">Explorer / Manipuler (format <em>html</em>).</span></a></li>';
			echo'</ul>';
		}
		if($type_individuel)
		{
			echo'<h2>Relevé individuel</h2>';
			echo'<ul class="puce">';
			echo'<li><a class="lien_ext" href="'.$dossier.$fichier_lien.'_individuel.pdf"><span class="file file_pdf">Archiver / Imprimer (format <em>pdf</em>).</span></a></li>';
			echo'<li><a class="lien_ext" href="./releve-html.php?fichier='.$fichier_lien.'_individuel"><span class="file file_htm">Explorer / Manipuler (format <em>html</em>).</span></a></li>';
			echo'</ul>';
		}
	}

}

else
{
	echo'Erreur avec les données transmises !';
}
?>
