<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2009-2015
 * 
 * ****************************************************************************************************
 * SACoche <http://sacoche.sesamath.net> - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath <http://www.sesamath.net> - Tous droits réservés.
 * Logiciel placé sous la licence libre Affero GPL 3 <https://www.gnu.org/licenses/agpl-3.0.html>.
 * ****************************************************************************************************
 * 
 * Ce fichier est une partie de SACoche.
 * 
 * SACoche est un logiciel libre ; vous pouvez le redistribuer ou le modifier suivant les termes 
 * de la “GNU Affero General Public License” telle que publiée par la Free Software Foundation :
 * soit la version 3 de cette licence, soit (à votre gré) toute version ultérieure.
 * 
 * SACoche est distribué dans l’espoir qu’il vous sera utile, mais SANS AUCUNE GARANTIE :
 * sans même la garantie implicite de COMMERCIALISABILITÉ ni d’ADÉQUATION À UN OBJECTIF PARTICULIER.
 * Consultez la Licence Publique Générale GNU Affero pour plus de détails.
 * 
 * Vous devriez avoir reçu une copie de la Licence Publique Générale GNU Affero avec SACoche ;
 * si ce n’est pas le cas, consultez : <http://www.gnu.org/licenses/>.
 * 
 */

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}
if(($_SESSION['SESAMATH_ID']==ID_DEMO)&&($_GET['action']!='initialiser')){exit('Action désactivée pour la démo...');}

$action    = (isset($_POST['action']))    ? $_POST['action']                  : '' ;
$groupe_id = (isset($_POST['groupe_id'])) ? Clean::entier($_POST['groupe_id']) : 0 ;
$user_id   = (isset($_POST['user_id']))   ? Clean::entier($_POST['user_id'])   : 0 ;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Ajouter un professeur à un groupe
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='ajouter') && ($groupe_id) && ($user_id) )
{
  DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_liaison_user_groupe_par_admin($user_id,'professeur',$groupe_id,'groupe',TRUE);
  exit('ok');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Retirer un professeur à un groupe
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='retirer') && ($groupe_id) && ($user_id) )
{
  DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_liaison_user_groupe_par_admin($user_id,'professeur',$groupe_id,'groupe',FALSE);
  exit('ok');
}


// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

exit('Erreur avec les données transmises !');

?>
