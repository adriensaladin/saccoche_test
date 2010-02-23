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
$tab_select_professeurs = (isset($_POST['select_professeurs'])) ? array_map('clean_entier',explode(',',$_POST['select_professeurs'])) : array() ;
$tab_select_matieres    = (isset($_POST['select_matieres']))    ? array_map('clean_entier',explode(',',$_POST['select_matieres']))    : array() ;

function positif($n) {return($n);}
$tab_select_professeurs = array_filter($tab_select_professeurs,'positif');
$tab_select_matieres    = array_filter($tab_select_matieres,'positif');
// Ajouter des professeurs à des matières
if($action=='ajouter')
{
	foreach($tab_select_professeurs as $user_id)
	{
		foreach($tab_select_matieres as $matiere_id)
		{
			DB_modifier_liaison_professeur_matiere($_SESSION['STRUCTURE_ID'],$user_id,$matiere_id,true);
		}
	}
}

// Retirer des professeurs à des matières
elseif($action=='retirer')
{
	foreach($tab_select_professeurs as $user_id)
	{
		foreach($tab_select_matieres as $matiere_id)
		{
			DB_modifier_liaison_professeur_matiere($_SESSION['STRUCTURE_ID'],$user_id,$matiere_id,false);
		}
	}
}

// Affichage du bilan des affectations des professeurs dans les matières ; en deux requêtes pour récupérer les professeurs sans matières et les matières sans professeurs
$tab_matiere = array();
$tab_user   = array();
$tab_matiere[0] = '<i>sans affectation</i>';
$tab_user[0]   = '';
// Récupérer la liste des matières
$DB_SQL = 'SELECT * FROM livret_matiere ';
$DB_SQL.= ($_SESSION['MATIERES']) ? 'WHERE livret_matiere_structure_id=:structure_id OR livret_matiere_id IN('.$_SESSION['MATIERES'].') AND livret_matiere_transversal=0 ' : 'WHERE livret_matiere_structure_id=:structure_id ';
$DB_SQL.= 'ORDER BY livret_matiere_nom ASC';
$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID']);
$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
foreach($DB_TAB as $DB_ROW)
{
	$tab_matiere[$DB_ROW['livret_matiere_id']] = html($DB_ROW['livret_matiere_nom']);
	$tab_user[$DB_ROW['livret_matiere_id']]   = '';
}
// Récupérer la liste des professeurs / matières
$DB_SQL = 'SELECT * FROM livret_user ';
$DB_SQL.= 'LEFT JOIN livret_jointure_user_matiere USING (livret_structure_id,livret_user_id) ';
$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_profil=:profil AND livret_user_statut=:statut AND livret_matiere_id!='.ID_MATIERE_TRANSVERSALE.' ';
$DB_SQL.= 'ORDER BY livret_user_nom ASC, livret_user_prenom ASC';
$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':profil'=>'professeur',':statut'=>1);
$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
foreach($DB_TAB as $DB_ROW)
{
	// Mettre de côté les professeurs non affectés ou affectés à une matière qui n'est plus associée à l'établissement...
	if( (is_null($DB_ROW['livret_matiere_id'])) || (!isset($tab_user[$DB_ROW['livret_matiere_id']])) )
	{
		$DB_ROW['livret_matiere_id'] = 0;
	}
	$tab_user[$DB_ROW['livret_matiere_id']]  .= html($DB_ROW['livret_user_nom'].' '.$DB_ROW['livret_user_prenom']).'<br />';
}
// Assemblage du tableau résultant
$TH = array();
$TB = array();
$TF = array();
$tab_mod = 5;
$i = $tab_mod-1;
$memo_tab_num = -1;
foreach($tab_matiere as $matiere_id => $matiere_nom)
{
	$tab_num = floor($i/$tab_mod);
	if($memo_tab_num!=$tab_num)
	{
		$memo_tab_num = $tab_num;
		$TH[$tab_num] = '';
		$TB[$tab_num] = '';
		$TF[$tab_num] = '';
	}
	$i++;
	$nb = mb_substr_count($tab_user[$matiere_id],'<br />','UTF-8');
	$s = ($nb>1) ? 's' : '' ;
	$TH[$tab_num] .= '<th>'.$matiere_nom.'</th>';
	$TB[$tab_num] .= '<td>'.mb_substr($tab_user[$matiere_id],0,-6,'UTF-8').'</td>';
	$TF[$tab_num] .= '<td>'.$nb.' professeur'.$s.'</td>';
}
echo'<hr />';
echo'<p><span class="astuce">Tous les professeurs sont aussi automatiquement affectés à la matière "Transversal".</span></p>';
for($tab_i=0;$tab_i<=$tab_num;$tab_i++)
{
	echo'<table class="affectation">';
	echo'<thead><tr>'.$TH[$tab_i].'</tr></thead>';
	echo'<tbody><tr>'.$TB[$tab_i].'</tr></tbody>';
	echo'<tfoot><tr>'.$TF[$tab_i].'</tr></tfoot>';
	echo'</table><p />';
}
?>
