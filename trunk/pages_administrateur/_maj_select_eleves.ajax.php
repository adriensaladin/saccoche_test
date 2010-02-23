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

// Mettre à jour l'élément de formulaire "select_eleves" et le renvoyer en HTML

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}
if($_SESSION['STRUCTURE_ID']==ID_DEMO) {}

$groupe_type = (isset($_POST['f_groupe_type'])) ? clean_texte($_POST['f_groupe_type']) : '';
$groupe_id   = (isset($_POST['f_groupe_id']))   ? clean_entier($_POST['f_groupe_id'])  : 0;
$statut      = (isset($_POST['f_statut']))      ? clean_entier($_POST['f_statut'])     : 0;

$tab_types = array('d'=>'Divers' , 'n'=>'niveau' , 'c'=>'classe' , 'g'=>'groupe');

if( (!$groupe_id) || (!isset($tab_types[$groupe_type])) )
{
	exit('Erreur avec les données transmises !');
}

$groupe_type = $tab_types[$groupe_type];
if($groupe_type=='Divers')
{
	$groupe_type = ($groupe_id==1) ? 'sdf' : 'all' ;
}

echo afficher_select(DB_OPT_eleves_regroupement($_SESSION['STRUCTURE_ID'],$groupe_type,$groupe_id,$statut) , $select_nom=false , $option_first='non' , $selection=true , $optgroup='non');
?>
