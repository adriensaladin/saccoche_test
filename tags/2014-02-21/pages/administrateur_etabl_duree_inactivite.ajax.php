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

$profil = (isset($_POST['f_profil'])) ? Clean::texte($_POST['f_profil']) : '' ;
$delai  = (isset($_POST['f_delai']))  ? Clean::entier($_POST['f_delai']) : 0  ;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Choix du délai avant une déconnexion automatique pour inactivité
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($profil && $delai)
{
  if( ($profil!='ALL') && !isset($_SESSION['TAB_PROFILS_ADMIN']['TYPE'][$profil]) )
  {
    exit('Profil incorrect !');
  }
  if( ($delai%10) || ($delai>120) )
  {
    exit('Délai incorrect !');
  }
  // Mettre à jour les paramètres dans la base
  DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_profil_parametre( $profil , 'user_profil_duree_inactivite' , $delai );
  // Mettre aussi à jour la session
  if( ($profil=='ALL') || ($profil=='ADM') )
  {
    $_SESSION['USER_DUREE_INACTIVITE'] = $delai;
  }
  if($profil=='ALL')
  {
    $_SESSION['TAB_PROFILS_ADMIN']['DUREE_INACTIVITE'] = array_fill_keys ( $_SESSION['TAB_PROFILS_ADMIN']['DUREE_INACTIVITE'] , $delai );
  }
  else
  {
    $_SESSION['TAB_PROFILS_ADMIN']['DUREE_INACTIVITE'][$profil] = $delai;
  }
  exit('ok');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

exit('Erreur avec les données transmises !');

?>
