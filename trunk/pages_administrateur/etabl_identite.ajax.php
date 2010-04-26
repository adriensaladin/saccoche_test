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
if($_SESSION['STRUCTURE_ID']==ID_DEMO) {exit('Action désactivée pour la démo...');}

$denomination  = (isset($_POST['f_denomination']))  ? clean_texte($_POST['f_denomination'])  : '';
$structure_uai = (isset($_POST['f_structure_uai'])) ? clean_uai($_POST['f_structure_uai'])   : '';
$structure_id  = (isset($_POST['f_structure_id']))  ? clean_entier($_POST['f_structure_id']) : 0;
$structure_key = (isset($_POST['f_structure_key'])) ? clean_texte($_POST['f_structure_key']) : '';

//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
// Mettre à jour les informations
//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

if( $denomination )
{
	$tab_parametres = array();
	$tab_parametres['denomination']  = $denomination;
	$tab_parametres['structure_uai'] = $structure_uai;
	$tab_parametres['structure_id']  = $structure_id;
	$tab_parametres['structure_key'] = $structure_key;
	DB_modifier_parametres($tab_parametres);
	// On modifie aussi la session
	$_SESSION['DENOMINATION']  = $denomination ;
	$_SESSION['STRUCTURE_UAI'] = $structure_uai ;
	$_SESSION['STRUCTURE_ID']  = $structure_id ;
	$_SESSION['STRUCTURE_KEY'] = $structure_key ;
	echo'ok';
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
