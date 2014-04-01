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

$action = (isset($_POST['f_action'])) ? Clean::texte($_POST['f_action']) : '';
$tab_id = (isset($_POST['tab_id']))   ? explode(',',$_POST['tab_id'])    : array() ;

require(CHEMIN_DOSSIER_INCLUDE.'tableau_sso.php');

unset($tab_serveur_cas['']);
$tab_cas_nom   = array_merge( array('perso') , array_keys($tab_serveur_cas) );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Choix des serveurs phpCAS sans vérif SSL
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='Choix_serveurs')
{
  // Nettoyer la liste transmise
  $tab_id = array_diff($tab_cas_nom, $tab_id);
  $serveurs_listing = count($tab_id) ? ','.implode(',',$tab_id).',' : '' ;
  // ok
  FileSystem::fabriquer_fichier_hebergeur_info( array(
    'PHPCAS_NO_CERTIF_LISTING' => $serveurs_listing,
  ) );
  exit('ok');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

exit('Erreur avec les données transmises !');

?>
