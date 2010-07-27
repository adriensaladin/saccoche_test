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
if(($_SESSION['SESAMATH_ID']==ID_DEMO)&&($_POST['f_action']!='Afficher_bilan')&&($_POST['f_action']!='Afficher_validation')){exit('Action désactivée pour la démo...');}

$action      = (isset($_POST['f_action']))      ? clean_texte($_POST['f_action'])      : '';
$palier_id   = (isset($_POST['f_palier']))      ? clean_entier($_POST['f_palier'])     : 0;
$groupe_id   = (isset($_POST['f_groupe']))      ? clean_entier($_POST['f_groupe'])     : 0;
$groupe_type = (isset($_POST['f_groupe_type'])) ? clean_texte($_POST['f_groupe_type']) : '';
$eleve_id    = (isset($_POST['f_user']))        ? clean_entier($_POST['f_user'])       : 0;
$entree_id   = (isset($_POST['f_item']))        ? clean_entier($_POST['f_item'])       : 0;
$etat_valid  = (isset($_POST['f_etat']))        ? clean_texte($_POST['f_etat'])        : '';

$tab_types = array('Classes'=>'classe' , 'Groupes'=>'groupe' , 'Besoins'=>'groupe');
$tab_etats = array('v0'=>0 , 'v1'=>1 , 'v2'=>false);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Afficher le tableau avec les états de validations
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
if( ($action=='Afficher_bilan') && $palier_id && $groupe_id && isset($tab_types[$groupe_type]) )
{
	// Récupérer les élèves de la classe ou du groupe
	$DB_TAB = DB_STRUCTURE_lister_eleves_actifs_regroupement($tab_types[$groupe_type],$groupe_id);
	if(!count($DB_TAB))
	{
		exit('Aucun élève n\'est associé à ce groupe !');
	}
	$tab_eleve = array();
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_eleve[$DB_ROW['user_id']] = '<img alt="'.html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']).'" src="./_img/php/etiquette.php?dossier='.$_SESSION['BASE'].'&amp;nom='.urlencode($DB_ROW['user_nom']).'&amp;prenom='.urlencode($DB_ROW['user_prenom']).'" />';
	}
	// Récupérer l'arborescence du palier du socle
	$DB_TAB = DB_STRUCTURE_recuperer_arborescence_palier($palier_id);
	$tab_pilier = array();
	$tab_entree = array();
	$tab_arbre  = array();
	$pilier_id = 0;
	foreach($DB_TAB as $DB_ROW)
	{
		if($DB_ROW['pilier_id']!=$pilier_id)
		{
			$pilier_id = $DB_ROW['pilier_id'];
			$tab_pilier[$pilier_id] = html($DB_ROW['pilier_nom']);
			$tab_arbre[$pilier_id] = array();
			$entree_id = 0;
		}
		if( (!is_null($DB_ROW['entree_id'])) && ($DB_ROW['entree_id']!=$entree_id) )
		{
			$entree_id = $DB_ROW['entree_id'];
			$tab_entree[$entree_id] = html($DB_ROW['entree_nom']);
			$tab_arbre[$pilier_id][] = $entree_id;
		}
	}
	// Récupérer la liste des jointures (validations)
	$listing_eleve_id  = implode(',',array_keys($tab_eleve));
	$listing_entree_id = implode(',',array_keys($tab_entree));
	$DB_SQL = 'SELECT * ';
	$DB_SQL.= 'FROM sacoche_jointure_user_entree ';
	$DB_SQL.= 'WHERE user_id IN('.$listing_eleve_id.') AND entree_id IN('.$listing_entree_id.') ';
	$DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , null);
	$tab_jointure  = array();
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_jointure[$DB_ROW['user_id']][$DB_ROW['entree_id']] = array('etat'=>$DB_ROW['validation_entree_etat'],'bulle'=>html($DB_ROW['validation_entree_info'].' le '.convert_date_mysql_to_french($DB_ROW['validation_entree_date'])));
	}
	// Afficher le résultat
	$premier_pilier = true;
	foreach($tab_pilier as $pilier_id=>$pilier_aff)
	{
		// pour chaque pilier...
		echo'<table summary="'.$pilier_id.'">';
		// ligne avec les étiquettes
		echo'<thead><tr>';
		foreach($tab_eleve as $eleve_id=>$eleve_aff)
		{
			// (une pour chaque élève)
			$id = ($premier_pilier) ? ' id="U'.$eleve_id.'"' : ''; // id pour retrouver le nom d'un élève ; à n'afficher qu'une fois (unicité)
			echo'<th'.$id.'>'.$eleve_aff.'</th>';
		}
		$premier_pilier = false;
		// (le nom du pilier)
		echo'<td>'.$pilier_aff.'</td>';
		echo'</tr></thead>';
		// lignes avec les items
		echo'<tbody>';
		foreach($tab_arbre[$pilier_id] as $entree_id)
		{
			echo'<tr>';
			// pour chaque item...
			foreach($tab_eleve as $eleve_id=>$eleve_aff)
			{
				// (état de validation pour chaque élève)
				$id = 'U'.$eleve_id.'E'.$entree_id;
				echo (isset($tab_jointure[$eleve_id][$entree_id])) ? '<th id="'.$id.'" class="v'.$tab_jointure[$eleve_id][$entree_id]['etat'].'" title="'.$tab_jointure[$eleve_id][$entree_id]['bulle'].'" /></th>' : '<th id="'.$id.'" class="v2" title="Validation non renseignée." /></th>' ;
			}
			// (le nom de l'item)
			echo'<td>'.$tab_entree[$entree_id].'</td>';
			echo'</tr>';
		}
		echo'</tbody>';
		echo'</table><br />';
	}
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Afficher les informations pour valider un item précis pour un élève donné
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
elseif( ($action=='Afficher_validation') && $eleve_id && $entree_id )
{
	// Récupération de la liste des résultats
	$tab_eval = array();	// [item_id][]['note'] => note
	$tab_item = array();	// [item_id] => array(item_ref,item_nom,calcul_methode,calcul_limite);
	$DB_TAB = DB_STRUCTURE_lister_result_eleves_palier($eleve_id , $entree_id , $date_debut=false , $date_fin=false);
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_eval[$DB_ROW['item_id']][]['note'] = $DB_ROW['note'];
		$tab_item[$DB_ROW['item_id']] = array('item_ref'=>$DB_ROW['item_ref'],'item_nom'=>$DB_ROW['item_nom'],'matiere_id'=>$DB_ROW['matiere_id'],'calcul_methode'=>$DB_ROW['calcul_methode'],'calcul_limite'=>$DB_ROW['calcul_limite']);
	}
	// Elaboration du bilan relatif au socle : tableaux et variables pour mémoriser les infos
	$tab_etat = array('A'=>'v','VA'=>'o','NA'=>'r');
	$tab_score_socle_eleve = array('A'=>0,'VA'=>0,'NA'=>0,'nb'=>0); // et ensuite '%'=>
	$tab_infos_socle_eleve = array();
	// Pour chaque item associé à cet item du socle, ayant été évalué pour cet élève...
	if(count($tab_eval))
	{
		foreach($tab_eval as $item_id => $tab_devoirs)
		{
			extract($tab_item[$item_id]);	// $item_ref $item_nom $matiere_id $calcul_methode $calcul_limite
			// calcul du bilan de l'item
			$score = calculer_score($tab_devoirs,$calcul_methode,$calcul_limite);
			if($score!==false)
			{
				// on détermine si elle est acquise ou pas
				$indice = test_A($score) ? 'A' : ( test_NA($score) ? 'NA' : 'VA' ) ;
				// on enregistre les infos
				$tab_infos_socle_eleve[] = html($item_ref.' || '.$item_nom).'<span class="'.$tab_etat[$indice].'">&nbsp;['.$score.'%]&nbsp;</span>';
				$tab_score_socle_eleve[$indice]++;
				$tab_score_socle_eleve['nb']++;
			}
		}
	}
	// On calcule les états d'acquisition à partir des A / VA / NA
	$tab_score_socle_eleve['%'] = ($tab_score_socle_eleve['nb']) ? round( 50 * ( ($tab_score_socle_eleve['A']*2 + $tab_score_socle_eleve['VA']) / $tab_score_socle_eleve['nb'] ) ,0) : false ;
	// Elaboration du bilan relatif au socle : mise en page, cellule de stats
	if($tab_score_socle_eleve['%']===false)
	{
		echo'<span class="i">Aucun item évalué relié avec cette entrée du socle !</span>';
	}
	else
	{
		    if($tab_score_socle_eleve['%']<$_SESSION['CALCUL_SEUIL']['R']) {$etat = 'r';}
		elseif($tab_score_socle_eleve['%']>$_SESSION['CALCUL_SEUIL']['V']) {$etat = 'v';}
		else                                                               {$etat = 'o';}
		echo'<span class="'.$etat.'">&nbsp;'.$tab_score_socle_eleve['%'].'% validé ('.$tab_score_socle_eleve['A'].'A '.$tab_score_socle_eleve['VA'].'VA '.$tab_score_socle_eleve['NA'].'NA)&nbsp;</span>';
	}
	// Elaboration du bilan relatif au socle : mise en page, cellule des items
	echo'@'; // séparateur
	if( count($tab_infos_socle_eleve) )
	{
		echo implode('<br />',$tab_infos_socle_eleve);
	}
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Enregistrer la validation d'un item précis pour un élève donné
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
elseif( ($action=='Enregistrer_validation') && $eleve_id && $entree_id && isset($tab_etats[$etat_valid]) )
{
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
