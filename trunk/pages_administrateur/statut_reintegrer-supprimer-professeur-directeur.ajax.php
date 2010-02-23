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
if(($_SESSION['STRUCTURE_ID']==ID_DEMO)&&($_GET['action']!='initialiser')){exit('Action désactivée pour la démo...');}

$action = (isset($_GET['action'])) ? $_GET['action'] : '';
$tab_select_users   = (isset($_POST['select_users']))   ? array_map('clean_entier',explode(',',$_POST['select_users']))   : array() ;

function positif($n) {return($n);}
$tab_select_users   = array_filter($tab_select_users,'positif');
$nb = count($tab_select_users);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Réintégrer des professeurs et/ou directeurs
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='reintegrer') && $nb )
{
	foreach($tab_select_users as $user_id)
	{
		// Mettre à jour l'enregistrement
		$DB_SQL = 'UPDATE livret_user ';
		$DB_SQL.= 'SET livret_user_statut=:statut ';
		$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_id=:user_id ';
		$DB_SQL.= 'LIMIT 1';
		$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':user_id'=>$user_id,':statut'=>1);
		DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	}
	// Afficher le retour
	$s = ($nb>1) ? 's' : '';
	echo'<hr />'.$nb.' professeur'.$s.' et/ou directeur'.$s.' réintégré'.$s.'.';
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Supprimer des professeurs et/ou directeurs
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

elseif( ($action=='supprimer') && $nb )
{
	foreach($tab_select_users as $user_id)
	{
		// Effacer l'enregistrement
		$DB_SQL = 'DELETE FROM livret_user ';
		$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_id=:user_id ';
		$DB_SQL.= 'LIMIT 1';
		$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':user_id'=>$user_id);
		DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
		$DB_SQL = 'DELETE FROM livret_jointure_user_groupe ';
		$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_id=:user_id';
		$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':user_id'=>$user_id);
		DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
		$DB_SQL = 'DELETE FROM livret_jointure_user_matiere ';
		$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_id=:user_id';
		$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':user_id'=>$user_id);
		DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
		$DB_SQL = 'DELETE livret_jointure_devoir_competence FROM livret_jointure_devoir_competence ';
		$DB_SQL.= 'LEFT JOIN livret_devoir USING (livret_structure_id,livret_devoir_id) ';
		$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_prof_id=:user_id';
		$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':user_id'=>$user_id);
		DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
		$DB_SQL = 'DELETE livret_groupe FROM livret_groupe ';
		$DB_SQL.= 'LEFT JOIN livret_devoir ON livret_groupe.livret_groupe_prof_id=livret_devoir.livret_prof_id ';
		$DB_SQL.= 'WHERE livret_groupe.livret_structure_id=:structure_id AND livret_groupe_prof_id=:user_id';
		$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':user_id'=>$user_id);
		DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
		$DB_SQL = 'DELETE FROM livret_devoir ';
		$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_prof_id=:user_id';
		$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':user_id'=>$user_id);
		DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
		$DB_SQL = 'UPDATE livret_saisie ';
		$DB_SQL.= 'SET livret_prof_id=0 ';
		$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_prof_id=:user_id';
		$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':user_id'=>$user_id);
		DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	}
	// Afficher le retour
	$s = ($nb>1) ? 's' : '';
	echo'<hr />'.$nb.' professeur'.$s.' et/ou directeur'.$s.' supprimé'.$s.'.';
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
