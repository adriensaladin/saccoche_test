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

//	////////////////////////////////////////////////////////////////////////////////////////////////////
//	Générer une synthèse multi-matières
//	////////////////////////////////////////////////////////////////////////////////////////////////////

$groupe_id   = (isset($_POST['f_groupe']))             ? clean_entier($_POST['f_groupe'])     : 0;
$groupe_nom  = (isset($_POST['f_groupe_nom']))         ? clean_texte($_POST['f_groupe_nom'])  : '';
$periode_id  = (isset($_POST['f_periode']))            ? clean_entier($_POST['f_periode'])    : 0;
$date_debut  = (isset($_POST['f_date_debut']))         ? clean_texte($_POST['f_date_debut'])  : '';
$date_fin    = (isset($_POST['f_date_fin']))           ? clean_texte($_POST['f_date_fin'])    : '';
$retroactif  = (isset($_POST['f_retroactif']))         ? clean_texte($_POST['f_retroactif'])  : '';
$niveau_id   = (isset($_POST['f_niveau']))             ? clean_entier($_POST['f_niveau'])     : 0; // Niveau transmis uniquement si on restreint sur un niveau
$aff_coef    = (isset($_POST['f_coef']))               ? 1                                    : 0;
$aff_socle   = (isset($_POST['f_socle']))              ? 1                                    : 0;
$aff_lien    = (isset($_POST['f_lien']))               ? 1                                    : 0;
$only_socle  = (isset($_POST['f_restriction_socle']))  ? 1                                    : 0;
$only_niveau = (isset($_POST['f_restriction_niveau'])) ? $niveau_id                           : 0;
$couleur     = (isset($_POST['f_couleur']))            ? clean_texte($_POST['f_couleur'])     : '';
$legende     = (isset($_POST['f_legende']))            ? clean_texte($_POST['f_legende'])     : '';
$tab_eleve   = (isset($_POST['eleves'])) ? array_map('clean_entier',explode(',',$_POST['eleves'])) : array() ;

save_cookie_select('releve_synthese');

$tab_eleve     = array_filter($tab_eleve,'positif');
$liste_eleve   = implode(',',$tab_eleve);

if( $groupe_id && $groupe_nom && count($tab_eleve) && ( $periode_id || ($date_debut && $date_fin) ) && $retroactif && $couleur && $legende )
{

	ajouter_log_PHP( $log_objet='Demande de bilan' , $log_contenu=serialize($_POST) , $log_fichier=__FILE__ , $log_ligne=__LINE__ , $only_sesamath=true );

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

	$tab_item       = array();	// [item_id] => array(item_ref,item_nom,item_coef,item_cart,item_socle,item_lien,matiere_id,calcul_methode,calcul_limite,synthese_ref);
	$tab_liste_item = array();	// [i] => item_id
	$tab_eleve      = array();	// [i] => array(eleve_id,eleve_nom,eleve_prenom)
	$tab_matiere    = array();	// [matiere_id] => matiere_nom
	$tab_synthese   = array();	// [synthese_ref] => synthese_nom
	$tab_eval       = array();	// [eleve_id][item_id][devoir] => array(note,date,info) On utilise un tableau multidimensionnel vu qu'on ne sait pas à l'avance combien il y a d'évaluations pour un élève et un item donnés.

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	// Récupération de la liste des items travaillés durant la période choisie, pour les élèves selectionnés, toutes matières confondues
	// Récupération de la liste des synthèses concernées (nom de thèmes ou de domaines suivant les référentiels)
	// Récupération de la liste des matières concernées
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

	list($tab_item,$tab_synthese,$tab_matiere) = DB_STRUCTURE_recuperer_arborescence_synthese($liste_eleve,$matiere_id=false,$only_socle,$only_niveau,$mode_synthese='predefini',$date_mysql_debut,$date_mysql_fin);
	// $tab_matiere déjà renseigné à la requête précédente.

	$item_nb = count($tab_item);
	if(!$item_nb)
	{
		exit('Aucun item n\'a été évalué durant cette période pour ces élèves dans cette configuration !');
	}
	$tab_liste_item = array_keys($tab_item);
	$liste_item = implode(',',$tab_liste_item);

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
	// Récupération de la liste des résultats des évaluations associées à ces items donnés de plusieurs matieres, pour les élèves selectionnés, sur la période sélectionnée
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

	$date_mysql_debut = ($retroactif=='non') ? $date_mysql_debut : false;
	$DB_TAB = DB_STRUCTURE_lister_result_eleves_matieres($liste_eleve , $liste_item , $date_mysql_debut , $date_mysql_fin);
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_eval[$DB_ROW['eleve_id']][$DB_ROW['item_id']][] = array('note'=>$DB_ROW['note'],'date'=>$DB_ROW['date'],'info'=>$DB_ROW['info']);
	}

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	// INCLUSION DU CODE COMMUN À PLUSIEURS PAGES
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

	$format = 'multimatiere' ;
	require('./_inc/code_releve_synthese.php');

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	// On retourne les résultats
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

	if($_SESSION['USER_PROFIL']=='eleve')
	{
		echo'<ul class="puce">';
		echo'<li><label class="alerte"><a class="lien_ext" href="'.$dossier.$fichier_lien.'.pdf">Télécharger la synthèse au format PDF (imprimable).</a></label></li>';
		echo'</ul><p />';
		echo $releve_HTML;
	}
	else
	{
		echo'<ul class="puce">';
		echo'<li><a class="lien_ext" href="./releve-html.php?fichier='.$fichier_lien.'">Synthèse au format HTML (détails et informations complémentaires).</a></li>';
		echo'<li><a class="lien_ext" href="'.$dossier.$fichier_lien.'.pdf">Synthèse au format PDF (mis en page, prêt pour l\'impression).</a></li>';
		echo'</ul><p />';
	}
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
