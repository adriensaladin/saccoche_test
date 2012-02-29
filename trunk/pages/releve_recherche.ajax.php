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

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Récupération des données transmises
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

// info groupe
$groupe_type = (isset($_POST['f_groupe_type'])) ? clean_texte($_POST['f_groupe_type']) : ''; // d n c g b
$groupe_id   = (isset($_POST['f_groupe_id']))   ? clean_entier($_POST['f_groupe_id'])  : 0;
$groupe_nom  = (isset($_POST['f_groupe_nom']))  ? clean_texte($_POST['f_groupe_nom'])  : '';

$critere_objet = (isset($_POST['f_critere_objet'])) ? clean_texte($_POST['f_critere_objet']) : '';

// item(s) matière(s)
$tab_compet_liste = (isset($_POST['f_compet_liste'])) ? explode('_',$_POST['f_compet_liste']) : array() ;
$tab_compet_liste = array_map('clean_entier',$tab_compet_liste);
$compet_liste = implode(',',$tab_compet_liste);
$compet_nombre = count($tab_compet_liste);

// item ou pilier socle
$socle_item_id   = (isset($_POST['f_socle_item_id'])) ? clean_entier($_POST['f_socle_item_id']) : 0;
$socle_pilier_id = (isset($_POST['f_select_pilier'])) ? clean_entier($_POST['f_select_pilier']) : 0;

// Normalement ce sont des tableaux qui sont transmis, mais au cas où...
$critere_tab_seuil_acquis = ( (isset($_POST['f_critere_seuil_acquis'])) && (is_array($_POST['f_critere_seuil_acquis'])) ) ? $_POST['f_critere_seuil_acquis'] : array();
$critere_tab_seuil_valide = ( (isset($_POST['f_critere_seuil_valide'])) && (is_array($_POST['f_critere_seuil_acquis'])) ) ? $_POST['f_critere_seuil_valide'] : array();
$nb_criteres_acquis = count($critere_tab_seuil_acquis);
$nb_criteres_valide = count($critere_tab_seuil_valide);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Vérification des données transmises
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

$is_matiere_items_bilanMS   = ( ($critere_objet=='matiere_items_bilanMS')   && $compet_nombre   && $nb_criteres_acquis ) ? TRUE : FALSE ;
$is_matiere_items_bilanPA   = ( ($critere_objet=='matiere_items_bilanPA')   && $compet_nombre   && $nb_criteres_acquis ) ? TRUE : FALSE ;
$is_socle_item_pourcentage  = ( ($critere_objet=='socle_item_pourcentage')  && $socle_item_id   && $nb_criteres_acquis ) ? TRUE : FALSE ;
$is_socle_item_validation   = ( ($critere_objet=='socle_item_validation')   && $socle_item_id   && $nb_criteres_valide ) ? TRUE : FALSE ;
$is_socle_pilier_validation = ( ($critere_objet=='socle_pilier_validation') && $socle_pilier_id && $nb_criteres_valide ) ? TRUE : FALSE ;
$critere_valide = ( $is_matiere_items_bilanMS || $is_matiere_items_bilanPA || $is_socle_item_pourcentage || $is_socle_item_validation || $is_socle_pilier_validation ) ? TRUE : FALSE ;

$tab_types   = array('d'=>'all' , 'n'=>'niveau' , 'c'=>'classe' , 'g'=>'groupe' , 'b'=>'besoin');

if( (!$critere_valide) || (!$groupe_id) || (!$groupe_nom) || (!isset($tab_types[$groupe_type])) )
{
	exit('Erreur avec les données transmises !');
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Variables pour récupérer les données
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

$tab_item = array();	// [item_id] => array(item_ref,item_nom,item_coef,item_cart,item_socle,item_lien,calcul_methode,calcul_limite);
$tab_liste_item = array();	// [i] => item_id
$tab_eleve      = array();	// [i] => array(eleve_id,eleve_nom,eleve_prenom)
$tab_matiere    = array();	// [matiere_id] => matiere_nom
$tab_eval       = array();	// [eleve_id][matiere_id][item_id][devoir] => array(note,date,info) On utilise un tableau multidimensionnel vu qu'on ne sait pas à l'avance combien il y a d'évaluations pour un élève et un item donnés.
$tab_matiere_for_item = array();	// [item_id] => matiere_id

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Récupération de la liste des élèves
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

$tab_eleve = DB_STRUCTURE_COMMUN::DB_lister_users_actifs_regroupement( 'eleve' /*profil*/ , $tab_types[$groupe_type] , $groupe_id ) ;
$eleve_nb = count($tab_eleve);
if(!$eleve_nb)
{
	exit('Aucun élève trouvé dans le regroupement indiqué !');
}
$tab_eleve_id = array();
foreach($DB_TAB as $DB_ROW)
{
	$tab_eleve_id[] = $DB_ROW['user_id'];
}
$liste_eleve = implode(',',$tab_eleve_id);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// !!!!!!!!!! SUITE DU CODE EN CHANTIER / EN VRAC !!!!!!!!!!
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Récupération des résultats aux évaluations, si besoin
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( $is_matiere_items_bilanMS || $is_matiere_items_bilanPA )
{
	$DB_TAB = DB_STRUCTURE_BILAN::DB_lister_result_eleves_matiere($liste_eleve , $liste_item , $date_debut=false , $date_fin=false , $_SESSION['USER_PROFIL']) ;
	foreach($DB_TAB as $DB_ROW)
	{
		$user_id = ($_SESSION['USER_PROFIL']=='eleve') ? $_SESSION['USER_ID'] : $DB_ROW['eleve_id'] ;
		$tab_eval[$user_id][$DB_ROW['item_id']][] = array('note'=>$DB_ROW['note'],'date'=>$DB_ROW['date'],'info'=>$DB_ROW['info']);
	}
	// Récupération de calcul_methode et calcul_limite
	$DB_TAB = DB_STRUCTURE_COMMUN::DB_lister_referentiels_infos_details_matieres_niveaux( $matiere_id , $niveau_id );
	$calcul_methode = $DB_TAB[0]['referentiel_calcul_methode'];
	$calcul_limite  = $DB_TAB[0]['referentiel_calcul_limite'];
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Récupération de la liste des items travaillés durant la période choisie, pour la matière et les élèves selectionnés
// Récupération de la liste des matières travaillées
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
$tab_compet_liste = (isset($_POST['f_compet_liste'])) ? explode('_',$_POST['f_compet_liste']) : array() ;
$tab_compet_liste = array_map('clean_entier',$tab_compet_liste);
$liste_compet = implode(',',$tab_compet_liste);
list($tab_item,$tab_matiere) = DB_STRUCTURE_BILAN::DB_recuperer_arborescence_selection($liste_eleve,$liste_compet,$date_mysql_debut,$date_mysql_fin,$aff_domaine,$aff_theme);
// $tab_matiere déjà renseigné à la requête précédente.

$item_nb = count($tab_item);
if(!$item_nb)
{
	exit('Aucun item sélectionné n\'a été évalué pour ces élèves durant cette période !');
}
$tab_liste_item = array_keys($tab_item);
$liste_item = implode(',',$tab_liste_item);

// Récup infos items

		$listing_item_id = implode(',',array_keys($tab_item));
		$DB_TAB = DB_STRUCTURE_SOCLE::DB_lister_infos_items($listing_item_id,$detail=TRUE);
		foreach($DB_TAB as $DB_ROW)
		{
			$tab_item[$DB_ROW['item_id']] = array('item_ref'=>$DB_ROW['item_ref'],'item_nom'=>$DB_ROW['item_nom'],'item_coef'=>$DB_ROW['item_coef'],'item_cart'=>$DB_ROW['item_cart'],'item_socle'=>$DB_ROW['socle_id'],'item_lien'=>$DB_ROW['item_lien'],'matiere_id'=>$DB_ROW['matiere_id'],'calcul_methode'=>$DB_ROW['calcul_methode'],'calcul_limite'=>$DB_ROW['calcul_limite']);
		}

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
$DB_TAB = DB_STRUCTURE_BILAN::DB_lister_result_eleves_matieres($liste_eleve , $liste_item , $date_mysql_debut , $date_mysql_fin , $_SESSION['USER_PROFIL']);
foreach($DB_TAB as $DB_ROW)
{
	if($tab_score_a_garder[$DB_ROW['eleve_id']][$DB_ROW['item_id']])
	{
		$tab_eval[$DB_ROW['eleve_id']][$DB_ROW['matiere_id']][$DB_ROW['item_id']][] = array('note'=>$DB_ROW['note'],'date'=>$DB_ROW['date'],'info'=>$DB_ROW['info']);
		$tab_matiere_for_item[$DB_ROW['item_id']] = $DB_ROW['matiere_id'];	// sert pour la synthèse sur une sélection d'items issus de différentes matières
	}
}
$matiere_nb = count(array_unique($tab_matiere_for_item));

?>
