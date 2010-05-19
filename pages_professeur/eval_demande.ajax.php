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
if(($_SESSION['SESAMATH_ID']==ID_DEMO)&&($_POST['f_action']!='Afficher_demandes')){exit('Action désactivée pour la démo...');}

$action      = (isset($_POST['f_action']))      ? clean_texte($_POST['f_action'])      : '';			// pour le form0
$action      = (isset($_POST['f_quoi']))        ? clean_texte($_POST['f_quoi'])        : $action;	// pour le form1
$matiere_id  = (isset($_POST['f_matiere']))     ? clean_entier($_POST['f_matiere'])    : 0;
$matiere_nom = (isset($_POST['f_matiere_nom'])) ? clean_texte($_POST['f_matiere_nom']) : '';
$groupe_id   = (isset($_POST['f_groupe_id']))   ? clean_entier($_POST['f_groupe_id'])  : 0;
$groupe_type = (isset($_POST['f_groupe_type'])) ? clean_texte($_POST['f_groupe_type']) : '';
$groupe_nom  = (isset($_POST['f_groupe_nom']))  ? clean_texte($_POST['f_groupe_nom'])  : '';

$qui         = (isset($_POST['f_qui']))         ? clean_texte($_POST['f_qui'])         : '';
$date        = (isset($_POST['f_date']))        ? clean_texte($_POST['f_date'])        : '';
$info        = (isset($_POST['f_info']))        ? clean_texte($_POST['f_info'])        : '';
$devoir_id   = (isset($_POST['f_devoir']))      ? clean_entier($_POST['f_devoir'])     : 0;
$suite       = (isset($_POST['f_suite']))       ? clean_texte($_POST['f_suite'])       : '';

$tab_demande_id    = array();
$tab_user_id       = array();
$tab_competence_id = array();
// Récupérer et contrôler la liste des items transmis
$tab_ids = (isset($_POST['ids'])) ? explode(',',$_POST['ids']) : array() ;
if(count($tab_ids))
{
	foreach($tab_ids as $ids)
	{
		$tab_id = explode('x',$ids);
		$tab_demande_id[]    = $tab_id[0];
		$tab_user_id[]       = $tab_id[1];
		$tab_competence_id[] = $tab_id[2];
	}
	function positif($n) {return $n;}
	$tab_demande_id    = array_filter( array_map('clean_entier',$tab_demande_id)                  ,'positif');
	$tab_user_id       = array_filter( array_map('clean_entier',array_unique($tab_user_id))       ,'positif');
	$tab_competence_id = array_filter( array_map('clean_entier',array_unique($tab_competence_id)) ,'positif');
}
$nb_demandes    = count($tab_demande_id);
$nb_users       = count($tab_user_id);
$nb_competences = count($tab_competence_id);

$tab_types = array('Classes'=>'classe' , 'Groupes'=>'groupe' , 'Besoins'=>'groupe');
$tab_qui   = array('groupe','select');
$tab_suite = array('changer','retirer');

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Afficher une liste de demandes
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
if( ($action=='Afficher_demandes') && $matiere_id && $matiere_nom && $groupe_id && (isset($tab_types[$groupe_type])) && $groupe_nom )
{
	$retour = '';
	// Récupérer la liste des élèves concernés
	$DB_TAB = DB_OPT_eleves_regroupement($tab_types[$groupe_type],$groupe_id,$user_statut=1);
	if(!is_array($DB_TAB))
	{
		exit($DB_TAB);	// Erreur : aucun élève de ce regroupement n\'est enregistré !
	}
	$tab_eleves = array();
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_eleves[$DB_ROW['valeur']] = $DB_ROW['texte'];
	}
	$listing_user_id = implode(',', array_keys($tab_eleves) );
	// Lister les demandes
	$tab_demandes = array();
	$DB_TAB = DB_lister_demandes_prof($matiere_id,$listing_user_id);
	if(!count($DB_TAB))
	{
		exit('Aucune demande n\'a été formulée pour ces élèves et cette matière !');
	}
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_demandes[] = $DB_ROW['demande_id'] ;
		$score = ($DB_ROW['demande_score']!==null) ? $DB_ROW['demande_score'] : false ;
		$statut = ($DB_ROW['demande_statut']=='eleve') ? 'demande non traitée' : 'évaluation en préparation' ;
		$class  = ($DB_ROW['demande_statut']=='eleve') ? ' class="new"' : '' ;
		$langue = mb_substr( $DB_ROW['competence_ref'] , mb_strpos($DB_ROW['competence_ref'],'.')+1 );
		// Afficher une ligne du tableau 
		$retour .= '<tr'.$class.'>';
		$retour .= '<td class="nu"><input type="checkbox" name="f_ids" value="'.$DB_ROW['demande_id'].'x'.$DB_ROW['user_id'].'x'.$DB_ROW['item_id'].'" lang="'.html($langue).'" /></td>';
		$retour .= '<td>'.html($matiere_nom).'</td>';
		$retour .= '<td>'.html($DB_ROW['competence_ref']).' <img alt="" src="./_img/bulle_aide.png" title="'.html($DB_ROW['item_nom']).'" /></td>';
		$retour .= '<td>$'.$DB_ROW['item_id'].'$</td>';
		$retour .= '<td>'.html($groupe_nom).'</td>';
		$retour .= '<td>'.html($tab_eleves[$DB_ROW['user_id']]).'</td>';
		$retour .= affich_score_html($score,'score',$pourcent='');
		$retour .= '<td><i>'.html($DB_ROW['demande_date']).'</i>'.convert_date_mysql_to_french($DB_ROW['demande_date']).'</td>';
		$retour .= '<td>'.$statut.'</td>';
		$retour .= '</tr>';
	}
	// Calculer pour chaque item sa popularité (le nb de demandes pour les élèves affichés)
	$listing_demande_id = implode(',', $tab_demandes );
	$DB_SQL = 'SELECT item_id , COUNT(item_id) AS popularite ';
	$DB_SQL.= 'FROM sacoche_demande ';
	$DB_SQL.= 'WHERE demande_id IN('.$listing_demande_id.') AND user_id IN('.$listing_user_id.') ';
	$DB_SQL.= 'GROUP BY item_id ';
	$DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , null);
	$tab_bad = array();
	$tab_bon = array();
	foreach($DB_TAB as $DB_ROW)
	{
		$s = ($DB_ROW['popularite']>1) ? 's' : '' ;
		$tab_bad[] = '$'.$DB_ROW['item_id'].'$';
		$tab_bon[] = '<i>'.sprintf("%02u",$DB_ROW['popularite']).'</i>'.$DB_ROW['popularite'].' demande'.$s;
	}
	echo str_replace($tab_bad,$tab_bon,$retour);
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Créer une nouvelle évaluation
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
elseif( ($action=='creer') && $groupe_id && (isset($tab_types[$groupe_type])) && in_array($qui,$tab_qui) && $date && $info && in_array($suite,$tab_suite) && $nb_demandes && $nb_users && $nb_competences )
{
	// Dans le cas d'une évaluation sur une liste d'élèves sélectionnés
	if($qui=='select')
	{
		// Il faut commencer par créer un nouveau groupe de type "eval", utilisé uniquement pour cette évaluation (c'est transparent pour le professeur)
		$groupe_id = DB_ajouter_groupe('eval',$_SESSION['USER_ID'],'','',0);
		// Il faut y affecter tous les élèves choisis
		DB_modifier_liaison_devoir_user($groupe_id,$tab_user_id,'creer');
	}
	// Maintenant on peut insérer l'enregistrement de l'évaluation
	$date_mysql = convert_date_french_to_mysql($date);
	$devoir_id = DB_ajouter_devoir($_SESSION['USER_ID'],$groupe_id,$date_mysql,$info);
	// Insérer les enregistrements des items de l'évaluation
	DB_modifier_liaison_devoir_competence($devoir_id,$tab_competence_id,'creer');
	// Pour terminer, on change le statut des demandes ou on les supprime
	$listing_demande_id = implode(',',$tab_demande_id);
	if($suite=='changer')
	{
		DB_modifier_statut_demandes($listing_demande_id,$nb_demandes,'prof');
	}
	else
	{
		DB_supprimer_demandes($listing_demande_id,$nb_demandes);
	}
	exit('ok');
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Compléter une évaluation existante
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
elseif( ($action=='completer') && $groupe_id && (isset($tab_types[$groupe_type])) && in_array($qui,$tab_qui) && $devoir_id && in_array($suite,$tab_suite) && $nb_demandes && $nb_users && $nb_competences )
{
	// Dans le cas d'une évaluation sur une liste d'élèves sélectionnés
	if($qui=='select')
	{
		// sacoche_jointure_user_groupe
		DB_modifier_liaison_devoir_user($groupe_id,$tab_user_id,'ajouter');
	}
	// Maintenant on peut modifier les items de l'évaluation
	// sacoche_jointure_devoir_item
	DB_modifier_liaison_devoir_competence($devoir_id,$tab_competence_id,'ajouter');
	// Pour terminer, on change le statut des demandes ou on les supprime
	$listing_demande_id = implode(',',$tab_demande_id);
	if($suite=='changer')
	{
		DB_modifier_statut_demandes($listing_demande_id,$nb_demandes,'prof');
	}
	else
	{
		DB_supprimer_demandes($listing_demande_id,$nb_demandes);
	}
	exit('ok');
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Changer le statut pour "évaluation en préparation"
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
elseif( ($action=='changer') && $nb_demandes )
{
	$listing_demande_id = implode(',',$tab_demande_id);
	DB_modifier_statut_demandes($listing_demande_id,$nb_demandes,'prof');
	exit('ok');
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Retirer de la liste des demandes
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
elseif( ($action=='retirer') && $nb_demandes )
{
	$listing_demande_id = implode(',',$tab_demande_id);
	DB_supprimer_demandes($listing_demande_id,$nb_demandes);
	exit('ok');
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
