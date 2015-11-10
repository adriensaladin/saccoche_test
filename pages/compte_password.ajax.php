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
if($_SESSION['SESAMATH_ID']==ID_DEMO) {exit('Action désactivée pour la démo...');}

$password_ancien  = (isset($_POST['f_password0'])) ? Clean::password($_POST['f_password0']) : '' ;
$password_nouveau = (isset($_POST['f_password1'])) ? Clean::password($_POST['f_password1']) : '' ;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Mettre à jour son mdp
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($password_ancien!='') && ($password_nouveau!='') )
{
  if($_SESSION['USER_PROFIL_TYPE']=='webmestre')
  {
    exit( Webmestre::modifier_mdp_webmestre( $password_ancien , $password_nouveau ) );
  }
  elseif($_SESSION['USER_PROFIL_TYPE']=='partenaire')
  {
    exit( DB_WEBMESTRE_PARTENAIRE::DB_modifier_mdp_partenaire( $_SESSION['USER_ID'] , crypter_mdp($password_ancien) , crypter_mdp($password_nouveau) ) );
  }
  else
  {
    exit( DB_STRUCTURE_COMMUN::DB_modifier_mdp_utilisateur( $_SESSION['USER_ID'] , crypter_mdp($password_ancien) , crypter_mdp($password_nouveau) ) );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là !
// ////////////////////////////////////////////////////////////////////////////////////////////////////

exit('Erreur avec les données transmises !');

?>
