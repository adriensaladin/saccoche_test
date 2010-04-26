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

// Mettre à jour l'élément de formulaire "f_devoir" et le renvoyer en HTML

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}
if($_SESSION['STRUCTURE_ID']==ID_DEMO) {}

$eval_type = (isset($_POST['eval_type'])) ? clean_texte($_POST['eval_type'])  : '';	// 'groupe' ou 'select'
$groupe_id = (isset($_POST['groupe_id'])) ? clean_entier($_POST['groupe_id']) : 0;	// utile uniquement pour $eval_type='groupe'

$tab_types = array('groupe','select');

if( (!$groupe_id) || (!in_array($eval_type,$tab_types)) )
{
	exit('Erreur avec les données transmises !');
}
// Lister les dernières évaluations d'une classe ou d'un groupe ou d'un groupe de besoin
$DB_SQL = 'SELECT sacoche_devoir.* ';
$DB_SQL.= 'FROM sacoche_devoir ';
$DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
$DB_SQL.= 'WHERE prof_id=:prof_id ';
$DB_SQL.= ($eval_type=='groupe') ? 'AND groupe_type!=:type4 AND groupe_id=:groupe_id ' : 'AND groupe_type=:type4 ' ;
$DB_SQL.= 'ORDER BY devoir_date DESC ';
$DB_SQL.= 'LIMIT 20 ';
$DB_VAR = array(':prof_id'=>$_SESSION['USER_ID'],':groupe_id'=>$groupe_id,':type4'=>'eval');
$DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
if(!count($DB_TAB))
{
	exit('<option value="" disabled="disabled">Aucun devoir n\'a été trouvé pour ce groupe d\'élèves !</option>');
}
foreach($DB_TAB as $DB_ROW)
{
	// Formater la date et la référence de l'évaluation
	$date_affich = convert_date_mysql_to_french($DB_ROW['devoir_date']);
	echo'<option value="'.$DB_ROW['devoir_id'].'">'.$date_affich.' | '.html($DB_ROW['devoir_info']).'</option>';
}
?>
