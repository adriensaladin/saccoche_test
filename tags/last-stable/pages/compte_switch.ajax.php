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

$action   = (isset($_POST['f_action']))   ? Clean::texte($_POST['f_action'])      : '';
$login    = (isset($_POST['f_login']))    ? Clean::login($_POST['f_login'])       : '';
$password = (isset($_POST['f_password'])) ? Clean::password($_POST['f_password']) : '';
$user_id  = (isset($_POST['f_user_id']))  ? Clean::entier($_POST['f_user_id'])    : 0 ;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Basculer vers un autre compte
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='basculer') && $user_id )
{
  // Par sécurité et pour actualiser une éventuelle liaison (dé)faite depuis un autre compte, on ne stocke en session que l'identifiant de la clef des associations
  // La méthode appelée ci-dessous effectue de multiples vérifications complémentaires
  list( $_SESSION['USER_SWITCH_ID'] , $user_liste ) = DB_STRUCTURE_SWITCH::DB_recuperer_et_verifier_listing_comptes_associes( $_SESSION['USER_ID'] , $_SESSION['USER_SWITCH_ID'] );
  if(!$_SESSION['USER_SWITCH_ID'])
  {
      exit_json( FALSE , 'Aucune liaison de compte vous concernant n\'a été trouvée !' );
  }
  $tab_user = explode(',',$user_liste);
  if( !in_array( $user_id , $tab_user ) )
  {
    exit_json( FALSE , 'Le compte indiqué n\'est pas relié au votre !' );
  }
  // C'est ok
  $auth_DB_ROW = DB_STRUCTURE_PUBLIC::DB_recuperer_donnees_utilisateur( 'switch'  ,$user_id );
  $BASE = $_SESSION['BASE'];
  Session::close__open_new__init( FALSE /*memo_GET*/ );
  SessionUser::initialiser_utilisateur($BASE,$auth_DB_ROW);
  exit_json( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Ajouter une liaison
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='ajouter') && ($login!='') && ($password!='') )
{
  // initialisation
  $auth_resultat = 'Erreur avec les données transmises !';
  // Protection contre les attaques par force brute (laissé même pour cette page requiérant une authentification car la réponse en cas d'erreur de mdp y fait référence)
  if(!isset($_SESSION['FORCEBRUTE'][$PAGE]))
  {
    exit_json( FALSE , 'Session perdue ou absence de cookie : merci d\'actualiser la page.' );
  }
  else if( $_SERVER['REQUEST_TIME'] - $_SESSION['FORCEBRUTE'][$PAGE]['TIME'] < $_SESSION['FORCEBRUTE'][$PAGE]['DELAI'] )
  {
    $_SESSION['FORCEBRUTE'][$PAGE]['TIME'] = $_SERVER['REQUEST_TIME'];
    exit_json( FALSE , 'Sécurité : patienter '.$_SESSION['FORCEBRUTE'][$PAGE]['DELAI'].'s avant une nouvelle tentative.' );
  }
  // Pour un utilisateur d'établissement, y compris un administrateur
  if($login==$_SESSION['USER_LOGIN'])
  {
    exit_json( FALSE , 'Saisir les identifiants d\'un <span class="u">autre compte</span>, pas celui en cours !' );
  }
  list($auth_resultat,$auth_DB_ROW) = SessionUser::tester_authentification_utilisateur( $_SESSION['BASE'] , $login , $password , 'normal' /*mode_connection*/ );
  if($auth_resultat!='ok')
  {
    $_SESSION['FORCEBRUTE'][$PAGE]['DELAI']++;
    $_SESSION['FORCEBRUTE'][$PAGE]['TIME'] = $_SERVER['REQUEST_TIME'];
    exit_json( FALSE , $auth_resultat );
  }
  $user_id = $auth_DB_ROW['user_id'];
  // Par sécurité et pour actualiser une éventuelle liaison (dé)faite depuis un autre compte, on ne stocke en session que l'identifiant de la clef des associations
  // La méthode appelée ci-dessous effectue de multiples vérifications complémentaires
  list( $_SESSION['USER_SWITCH_ID'] , $user_liste ) = DB_STRUCTURE_SWITCH::DB_recuperer_et_verifier_listing_comptes_associes( $_SESSION['USER_ID'] , $_SESSION['USER_SWITCH_ID'] );
  // Si le user connecté n'a pas de liaison, il faut aussi vérifier que le user de l'autre compte n'en a pas non plus
  if(!$_SESSION['USER_SWITCH_ID'])
  {
    list( $_SESSION['USER_SWITCH_ID'] , $user_liste ) = DB_STRUCTURE_SWITCH::DB_recuperer_et_verifier_listing_comptes_associes( $user_id , $_SESSION['USER_SWITCH_ID'] );
  }
  // Soit c'est la vraiment la 1ère liaison à créer pour les deux
  if(!$_SESSION['USER_SWITCH_ID'])
  {
    $user_liste = ( $_SESSION['USER_ID'] < $user_id ) ? $_SESSION['USER_ID'].','.$user_id : $user_id.','.$_SESSION['USER_ID'] ;
    $_SESSION['USER_SWITCH_ID'] = DB_STRUCTURE_SWITCH::DB_ajouter_comptes_associes($user_liste);
  }
  // Soit c'est une nouvelle liaison à cumuler
  else
  {
    $tab_user = explode(',',$user_liste);
    if( !in_array( $user_id , $tab_user ) )
    {
      $user_id_add = $user_id;
    }
    elseif( !in_array( $_SESSION['USER_ID'] , $tab_user ) )
    {
      $user_id_add = $_SESSION['USER_ID'];
    }
    else
    {
      exit_json( FALSE , 'Ce compte est déjà relié au votre !' );
    }
    $tab_user[] = $user_id_add;
    sort( $tab_user , SORT_NUMERIC );
    $user_liste = implode(',',$tab_user);
    DB_STRUCTURE_SWITCH::DB_modifier_comptes_associes( $_SESSION['USER_SWITCH_ID'] , $user_liste );
  }
  // Afficher le retour
  exit_json( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Retirer une liaison
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='supprimer') && $user_id )
{
  // Par sécurité et pour actualiser une éventuelle liaison (dé)faite depuis un autre compte, on ne stocke en session que l'identifiant de la clef des associations
  // La méthode appelée ci-dessous effectue de multiples vérifications complémentaires
  list( $_SESSION['USER_SWITCH_ID'] , $user_liste ) = DB_STRUCTURE_SWITCH::DB_recuperer_et_verifier_listing_comptes_associes( $_SESSION['USER_ID'] , $_SESSION['USER_SWITCH_ID'] );
  if(!$_SESSION['USER_SWITCH_ID'])
  {
      exit_json( FALSE , 'Aucune liaison de compte vous concernant n\'a été trouvée !' );
  }
  $tab_user = explode(',',$user_liste);
  $user_key = array_search( $user_id , $tab_user );
  if( $user_key === FALSE )
  {
    exit_json( FALSE , 'Le compte indiqué n\'est pas relié au votre !' );
  }
  // Ok pour supprimer la liaison
  unset($tab_user[$user_key]);
  // Soit on n'est plus relié avec personne...
  if(count($tab_user)==1)
  {
    DB_STRUCTURE_SWITCH::DB_supprimer_comptes_associes($_SESSION['USER_SWITCH_ID']);
    $_SESSION['USER_SWITCH_ID'] = NULL;
  }
  // Soit on est encore relié à au moins une autre personne...
  else
  {
    $user_liste = implode(',',$tab_user);
    DB_STRUCTURE_SWITCH::DB_modifier_comptes_associes( $_SESSION['USER_SWITCH_ID'] , $user_liste );
  }
  // Afficher le retour
  exit_json( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là !
// ////////////////////////////////////////////////////////////////////////////////////////////////////

exit_json( FALSE , 'Erreur avec les données transmises !' );

?>
