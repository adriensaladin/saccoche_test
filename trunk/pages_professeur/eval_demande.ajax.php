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
if(($_SESSION['STRUCTURE_ID']==ID_DEMO)&&($_POST['f_action']!='Afficher_demandes')){exit('Action désactivée pour la démo...');}

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
	function positif($n) {return($n);}
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
	$DB_TAB = DB_OPT_eleves_regroupement($_SESSION['STRUCTURE_ID'],$tab_types[$groupe_type],$groupe_id,$user_statut=1);
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
	$DB_SQL = 'SELECT livret_demande.*, ';
	$DB_SQL.= 'CONCAT(livret_niveau_ref,".",livret_domaine_ref,livret_theme_ordre,livret_competence_ordre) AS competence_ref , ';
	$DB_SQL.= 'livret_competence_nom, livret_user_nom, livret_user_prenom ';
	$DB_SQL.= 'FROM livret_demande ';
	$DB_SQL.= 'LEFT JOIN livret_competence_item USING (livret_structure_id,livret_competence_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_theme USING (livret_structure_id,livret_theme_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_domaine USING (livret_structure_id,livret_domaine_id) ';
	$DB_SQL.= 'LEFT JOIN livret_niveau USING (livret_niveau_id) ';
	$DB_SQL.= 'LEFT JOIN livret_user USING (livret_structure_id,livret_user_id) ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_id IN('.$listing_user_id.') AND livret_demande.livret_matiere_id=:matiere_id ';
	$DB_SQL.= 'ORDER BY livret_niveau_ref ASC, livret_domaine_ref ASC, livret_theme_ordre ASC, livret_competence_ordre ASC';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':matiere_id'=>$matiere_id);
	$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	if(!count($DB_TAB))
	{
		exit('Aucune demande n\'a été formulée pour ces élèves et cette matière !');
	}
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_demandes[] = $DB_ROW['livret_demande_id'] ;
		$score = ($DB_ROW['livret_demande_score']!==null) ? $DB_ROW['livret_demande_score'] : false ;
		$statut = ($DB_ROW['livret_demande_statut']=='eleve') ? 'demande non traitée' : 'évaluation en préparation' ;
		$class  = ($DB_ROW['livret_demande_statut']=='eleve') ? ' class="new"' : '' ;
		$langue = mb_substr( $DB_ROW['competence_ref'] , mb_strpos($DB_ROW['competence_ref'],'.')+1 );
		// Afficher une ligne du tableau 
		$retour .= '<tr'.$class.'>';
		$retour .= '<td class="nu"><input type="checkbox" name="f_ids" value="'.$DB_ROW['livret_demande_id'].'x'.$DB_ROW['livret_user_id'].'x'.$DB_ROW['livret_competence_id'].'" lang="'.html($langue).'" /></td>';
		$retour .= '<td>'.html($matiere_nom).'</td>';
		$retour .= '<td>'.html($DB_ROW['competence_ref']).' <img alt="" src="./_img/bulle_aide.png" title="'.html($DB_ROW['livret_competence_nom']).'" /></td>';
		$retour .= '<td>$'.$DB_ROW['livret_competence_id'].'$</td>';
		$retour .= '<td>'.html($groupe_nom).'</td>';
		$retour .= '<td>'.html($tab_eleves[$DB_ROW['livret_user_id']]).'</td>';
		$retour .= affich_score_html($score,'score',$pourcent='');
		$retour .= '<td><i>'.html($DB_ROW['livret_demande_date']).'</i>'.convert_date_mysql_to_french($DB_ROW['livret_demande_date']).'</td>';
		$retour .= '<td>'.$statut.'</td>';
		$retour .= '</tr>';
	}
	// Calculer pour chaque item sa popularité (le nb de demandes pour les élèves affichés)
	$listing_demande_id = implode(',', $tab_demandes );
	$DB_SQL = 'SELECT livret_competence_id , COUNT(livret_competence_id) AS popularite ';
	$DB_SQL.= 'FROM livret_demande ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_demande_id IN('.$listing_demande_id.') AND livret_user_id IN('.$listing_user_id.') ';
	$DB_SQL.= 'GROUP BY livret_competence_id ';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID']);
	$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	$tab_bad = array();
	$tab_bon = array();
	foreach($DB_TAB as $DB_ROW)
	{
		$s = ($DB_ROW['popularite']>1) ? 's' : '' ;
		$tab_bad[] = '$'.$DB_ROW['livret_competence_id'].'$';
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
		$groupe_type = 'eval';
		// Il faut commencer par créer un nouveau groupe de type "eval", utilisé uniquement pour cette évaluation (c'est transparent pour le professeur)
		$DB_SQL = 'INSERT INTO livret_groupe(livret_structure_id,livret_groupe_type,livret_groupe_prof_id,livret_groupe_ref,livret_groupe_nom,livret_niveau_id) ';
		$DB_SQL.= 'VALUES(:structure_id,:type,:prof_id,:ref,:nom,:niveau)';
		$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':type'=>$groupe_type,':prof_id'=>$_SESSION['USER_ID'],':ref'=>'',':nom'=>'',':niveau'=>0);
		DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
		$groupe_id   = DB::getLastOid(SACOCHE_BD_NAME);
		// Il faut y affecter tous les élèves choisis
		foreach($tab_user_id as $user_id)
		{
			$DB_SQL = 'INSERT INTO livret_jointure_user_groupe (livret_structure_id,livret_user_id,livret_groupe_id) ';
			$DB_SQL.= 'VALUES(:structure_id,:user_id,:groupe_id)';
			$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':user_id'=>$user_id,':groupe_id'=>$groupe_id);
			DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
		}
	}
	// Maintenant on peut insérer l'enregistrement de l'évaluation
	$date_mysql = convert_date_french_to_mysql($date);
	$DB_SQL = 'INSERT INTO livret_devoir(livret_structure_id,livret_prof_id,livret_groupe_id,livret_devoir_date,livret_devoir_info) ';
	$DB_SQL.= 'VALUES(:structure_id,:prof_id,:groupe_id,:date,:info)';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':prof_id'=>$_SESSION['USER_ID'],':groupe_id'=>$groupe_id,':date'=>$date_mysql,':info'=>$info);
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	$devoir_id = DB::getLastOid(SACOCHE_BD_NAME);
	// Insérer les enregistrements de items de l'évaluation
	$DB_SQL = 'INSERT INTO livret_jointure_devoir_competence(livret_structure_id,livret_devoir_id,livret_competence_id,livret_jointure_ordre) ';
	$DB_SQL.= 'VALUES(:structure_id,:devoir_id,:competence_id,:ordre)';
	foreach($tab_competence_id as $competence_id)
	{
		$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':devoir_id'=>$devoir_id,':competence_id'=>$competence_id,':ordre'=>0);
		DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	}
	// Pour terminer, on change le statut des demandes ou on les supprime
	$listing_demandes_id = implode(',',$tab_demande_id);
	if($suite=='changer')
	{
		$DB_SQL = 'UPDATE livret_demande SET livret_demande_statut=:demande_statut ';
		$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_demande_id IN('.$listing_demandes_id.') ';
		$DB_SQL.= 'LIMIT '.$nb_demandes;
		$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':demande_statut'=>'prof');
		DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	}
	else
	{
		$DB_SQL = 'DELETE FROM livret_demande ';
		$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_demande_id IN('.$listing_demandes_id.') ';
		$DB_SQL.= 'LIMIT '.$nb_demandes;
		$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID']);
		DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
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
		// On récupère dans la base la liste des élèves associés à ce groupe pour la comparer à la liste transmise
		// DB::query(SACOCHE_BD_NAME , 'SET group_concat_max_len = ...'); // Pour lever si besoin une limitation de GROUP_CONCAT (group_concat_max_len est par défaut limité à une chaine de 1024 caractères).
		$DB_SQL = 'SELECT GROUP_CONCAT(livret_user_id SEPARATOR " ") AS users_listing FROM livret_jointure_user_groupe ';
		$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_groupe_id=:groupe_id ';
		$DB_SQL.= 'GROUP BY livret_groupe_id';
		$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':groupe_id'=>$groupe_id);
		$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
		$tab_eleves_avant = (count($DB_TAB)) ? explode(' ',$DB_TAB[0]['users_listing']) : array() ;
		// On retire si besoin les élèves dans la base associés à ce groupe qui ne le sont plus dans la liste transmise
		// -> sans objet
		// On ajoute si besoin les élèves dans la liste transmise qui ne sont pas dans la base associés à ce groupe
		$tab_eleves_plus = array_diff($tab_user_id,$tab_eleves_avant);
		if(count($tab_eleves_plus))
		{
			foreach($tab_eleves_plus as $user_id)
			{
				$DB_SQL = 'INSERT INTO livret_jointure_user_groupe (livret_structure_id,livret_user_id,livret_groupe_id) ';
				$DB_SQL.= 'VALUES(:structure_id,:user_id,:groupe_id)';
				$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':user_id'=>$user_id,':groupe_id'=>$groupe_id);
				DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
			}
		}
	}
	// Maintenant on peut modifier les items de l'évaluation
	// livret_jointure_devoir_competence
	/*
	On ne peut pas faire un REPLACE car si un enregistrement est présent ça fait un DELETE+INSERT et du coup on perd l'info sur l'ordre des items.
	Alors on récupère la liste des items et on cherche les différences pour faire des DELETE et INSERT sélectifs
	*/
	// livret_jointure_devoir_competence -> on récupère les anciennes compétences
	$tab_old_competences = array();
	$DB_SQL = 'SELECT livret_competence_id FROM livret_jointure_devoir_competence ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_devoir_id=:devoir_id ';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':devoir_id'=>$devoir_id);
	$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_old_competences[] = $DB_ROW['livret_competence_id'];
	}
	// livret_jointure_devoir_competence -> on supprime les anciennes compétences non nouvellement sélectionnées
	// -> sans objet
	// livret_jointure_devoir_competence -> on ajoute les nouvelles compétences non anciennement présentes
	$tab_competences_ajouter = array_diff($tab_competence_id,$tab_old_competences);
	if(count($tab_competences_ajouter))
	{
		foreach($tab_competences_ajouter as $competence_id)
		{
			$DB_SQL = 'INSERT INTO livret_jointure_devoir_competence(livret_structure_id,livret_devoir_id,livret_competence_id) ';
			$DB_SQL.= 'VALUES(:structure_id,:devoir_id,:competence_id)';
			$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':devoir_id'=>$devoir_id,':competence_id'=>$competence_id);
			DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
		}
	}
	// Pour terminer, on change le statut des demandes ou on les supprime
	$listing_demandes_id = implode(',',$tab_demande_id);
	if($suite=='changer')
	{
		$DB_SQL = 'UPDATE livret_demande SET livret_demande_statut=:demande_statut ';
		$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_demande_id IN('.$listing_demandes_id.') ';
		$DB_SQL.= 'LIMIT '.$nb_demandes;
		$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':demande_statut'=>'prof');
		DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	}
	else
	{
		$DB_SQL = 'DELETE FROM livret_demande ';
		$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_demande_id IN('.$listing_demandes_id.') ';
		$DB_SQL.= 'LIMIT '.$nb_demandes;
		$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID']);
		DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	}
	exit('ok');
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Changer le statut pour "évaluation en préparation"
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
elseif( ($action=='changer') && $nb_demandes )
{
	$listing_demandes_id = implode(',',$tab_demande_id);
	$DB_SQL = 'UPDATE livret_demande SET livret_demande_statut=:demande_statut ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_demande_id IN('.$listing_demandes_id.') ';
	$DB_SQL.= 'LIMIT '.$nb_demandes;
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':demande_statut'=>'prof');
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	exit('ok');
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Retirer de la liste des demandes
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
elseif( ($action=='retirer') && $nb_demandes )
{
	$listing_demandes_id = implode(',',$tab_demande_id);
	$DB_SQL = 'DELETE FROM livret_demande ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_demande_id IN('.$listing_demandes_id.') ';
	$DB_SQL.= 'LIMIT '.$nb_demandes;
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID']);
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	exit('ok');
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
