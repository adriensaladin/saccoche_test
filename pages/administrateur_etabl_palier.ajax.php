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
if($_SESSION['SESAMATH_ID']==ID_DEMO) {exit('Action désactivée pour la démo...');}

$action = (isset($_POST['f_action'])) ? Clean::texte($_POST['f_action']) : '';

$tab_id = (isset($_POST['tab_id']))   ? Clean::map_entier(explode(',',$_POST['tab_id'])) : array() ;
$tab_id = array_filter($tab_id,'positif');
sort($tab_id);

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Choix de paliers du socle
// ////////////////////////////////////////////////////////////////////////////////////////////////////
if($action=='Choix_paliers')
{
	// Il n'y a que 3 paliers : on ne s'embête pas à comparer pour voir ce qui a changé, on effectue 3 update.
	for( $palier_id=1 ; $palier_id<4 ; $palier_id++ )
	{
		$palier_actif = (in_array($palier_id,$tab_id)) ? 1 : 0 ;
		DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_palier($palier_id,$palier_actif);
	}
	// On mémorise aussi la liste des piliers actifs (base + session)
	$liste_paliers_actifs = implode(',',$tab_id);
	DB_STRUCTURE_COMMUN::DB_modifier_parametres( array('liste_paliers_actifs'=>$liste_paliers_actifs) );
	$_SESSION['LISTE_PALIERS_ACTIFS'] = $liste_paliers_actifs;
	exit('ok');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

exit('Erreur avec les données transmises !');

?>
