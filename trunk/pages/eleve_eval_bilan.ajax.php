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
if(($_SESSION['SESAMATH_ID']==ID_DEMO)&&($_POST['f_action']!='Afficher_evaluations')&&($_POST['f_action']!='Voir_notes')){exit('Action désactivée pour la démo...');}

$action     = (isset($_POST['f_action']))     ? clean_texte($_POST['f_action'])     : '';
$date_debut = (isset($_POST['f_date_debut'])) ? clean_texte($_POST['f_date_debut']) : '';
$date_fin   = (isset($_POST['f_date_fin']))   ? clean_texte($_POST['f_date_fin'])   : '';
$devoir_id  = (isset($_POST['f_devoir']))     ? clean_entier($_POST['f_devoir'])    : 0;

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Afficher une liste d'évaluations
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='Afficher_evaluations') && $date_debut && $date_fin )
{
	// Formater les dates
	$date_debut_mysql = convert_date_french_to_mysql($date_debut);
	$date_fin_mysql   = convert_date_french_to_mysql($date_fin);
	// Vérifier que la date de début est antérieure à la date de fin
	if($date_debut_mysql>$date_fin_mysql)
	{
		exit('Erreur : la date de début est postérieure à la date de fin !');
	}
	// Lister les évaluations
	$DB_TAB = DB_STRUCTURE_lister_devoirs_eleve($_SESSION['USER_ID'],$_SESSION['ELEVE_CLASSE_ID'],$date_debut_mysql,$date_fin_mysql);
	if(!count($DB_TAB))
	{
		exit('Aucune évaluation trouvée sur cette période vous concernant !');
	}
	foreach($DB_TAB as $DB_ROW)
	{
		// Formater la date et la référence de l'évaluation
		$date_affich = convert_date_mysql_to_french($DB_ROW['devoir_date']);
		// Afficher une ligne du tableau
		echo'<tr>';
		echo	'<td><i>'.html($DB_ROW['devoir_date']).'</i>'.html($date_affich).'</td>';
		echo	'<td>'.html($DB_ROW['prof_nom'].' '.$DB_ROW['prof_prenom']{0}.'.').'</td>';
		echo	'<td>'.html($DB_ROW['devoir_info']).'</td>';
		echo	'<td class="nu" id="devoir_'.$DB_ROW['devoir_id'].'">';
		echo		'<q class="voir" title="Voir les notes saisies à ce devoir."></q>';
		echo	'</td>';
		echo'</tr>';
	}
	exit();
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Voir les notes saisies à un devoir
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='Voir_notes') && $devoir_id )
{
	// liste des items
	$DB_TAB_COMP = DB_STRUCTURE_lister_items_devoir($devoir_id,$info_pour_eleve=true);
	// Normalement, un devoir est toujours lié à au moins un item... sauf si l'item a été supprimé dans le référentiel !
	if(!count($DB_TAB_COMP))
	{
		exit('Ce devoir n\'est associé à aucun item !');
	}
	// Si l'élève peut formuler des demandes d'évaluations, on doit calculer le score (du coup, on choisit d'afficher le score pour tout le monde).
	$tab_liste_item = array_keys($DB_TAB_COMP);
	$liste_item_id = implode(',',$tab_liste_item);
	$tab_devoirs = array();
	$DB_TAB = DB_STRUCTURE_lister_result_eleve_items($_SESSION['USER_ID'],$liste_item_id);
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_devoirs[$DB_ROW['item_id']][] = array('note'=>$DB_ROW['note']);
	}
	// préparer les lignes
	$tab_affich  = array();
	foreach($tab_liste_item as $item_id)
	{
		$DB_ROW = $DB_TAB_COMP[$item_id][0];
		$item_ref = $DB_ROW['item_ref'];
		$texte_socle = ($DB_ROW['entree_id']) ? '[S] ' : '[–] ';
		$texte_lien_avant = ($DB_ROW['item_lien']) ? '<a class="lien_ext" href="'.html($DB_ROW['item_lien']).'">' : '';
		$texte_lien_apres = ($DB_ROW['item_lien']) ? '</a>' : '';
		$score = calculer_score($tab_devoirs[$item_id],$DB_ROW['referentiel_calcul_methode'],$DB_ROW['referentiel_calcul_limite']);
		$texte_demande_eval = ($_SESSION['ELEVE_DEMANDES']==0) ? '' : ( ($DB_ROW['item_cart']) ? '<q class="demander_add" lang="ids_'.$DB_ROW['matiere_id'].'_'.$item_id.'_'.$score.'" title="Ajouter aux demandes d\'évaluations."></q>' : '<q class="demander_non" title="Demande interdite."></q>' ) ;
		$tab_affich[$item_id] = '<tr><td>'.html($item_ref).'</td><td>'.$texte_socle.$texte_lien_avant.html($DB_ROW['item_nom']).$texte_lien_apres.$texte_demande_eval.'</td><td class="hc">-</td>'.affich_score_html($score,$methode_tri='score',$pourcent='').'</tr>';
	}
	// récupérer les saisies et les ajouter
	$DB_TAB = DB_STRUCTURE_lister_saisies_devoir_eleve($devoir_id,$_SESSION['USER_ID']);
	foreach($DB_TAB as $DB_ROW)
	{
		// Test pour éviter les pbs des élèves changés de groupes ou des items modifiés en cours de route
		if(isset($tab_affich[$DB_ROW['item_id']]))
		{
			$tab_affich[$DB_ROW['item_id']] = str_replace('>-<','>'.affich_note_html($DB_ROW['saisie_note'],'','',$tri=true).'<',$tab_affich[$DB_ROW['item_id']]);
		}
	}
	exit(implode('',$tab_affich));
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	On ne devrait pas en arriver là
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

exit('Erreur avec les données transmises !');

?>
