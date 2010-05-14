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
if($_SESSION['SESAMATH_ID']==ID_DEMO) {exit('Action désactivée pour la démo...');}

$sesamath_id        = (isset($_POST['f_sesamath_id']))       ? clean_entier($_POST['f_sesamath_id'])      : 0;
$sesamath_uai       = (isset($_POST['f_sesamath_uai']))      ? clean_uai($_POST['f_sesamath_uai'])        : '';
$sesamath_type_nom  = (isset($_POST['f_sesamath_type_nom'])) ? clean_texte($_POST['f_sesamath_type_nom']) : '';
$sesamath_key       = (isset($_POST['f_sesamath_key']))      ? clean_texte($_POST['f_sesamath_key'])      : '';

//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
// Mettre à jour les informations
//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

if( $sesamath_id && $sesamath_type_nom && $sesamath_key )
{
	$retour = enregistrer_structure_Sesamath($sesamath_id,$sesamath_key);
	if($retour!='ok')
	{
		exit($retour);
	}
	// Si on arrive là, alors tout s'est bien passé.
	$tab_parametres = array();
	$tab_parametres['sesamath_id']       = $sesamath_id;
	$tab_parametres['sesamath_uai']      = $sesamath_uai;
	$tab_parametres['sesamath_type_nom'] = $sesamath_type_nom;
	$tab_parametres['sesamath_key']      = $sesamath_key;
	DB_modifier_parametres($tab_parametres);
	// On modifie aussi la session
	$_SESSION['SESAMATH_ID']       = $sesamath_id ;
	$_SESSION['SESAMATH_UAI']      = $sesamath_uai ;
	$_SESSION['SESAMATH_TYPE_NOM'] = $sesamath_type_nom ;
	$_SESSION['SESAMATH_KEY']      = $sesamath_key ;
	echo'ok';
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
