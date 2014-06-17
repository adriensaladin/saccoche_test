<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010-2014
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

$action       = (isset($_POST['f_action']))      ? Clean::texte($_POST['f_action'])      : '';
$id           = (isset($_POST['f_id']))          ? Clean::entier($_POST['f_id'])         : 0;
$id_ent       = (isset($_POST['f_id_ent']))      ? Clean::texte($_POST['f_id_ent'])      : '';
$id_gepi      = (isset($_POST['f_id_gepi']))     ? Clean::texte($_POST['f_id_gepi'])     : '';
$profil       = 'ADM';
$nom          = (isset($_POST['f_nom']))         ? Clean::nom($_POST['f_nom'])           : '';
$prenom       = (isset($_POST['f_prenom']))      ? Clean::prenom($_POST['f_prenom'])     : '';
$login        = (isset($_POST['f_login']))       ? Clean::login($_POST['f_login'])       : '';
$password     = (isset($_POST['f_password']))    ? Clean::password($_POST['f_password']) : '' ;
$box_login    = (isset($_POST['box_login']))     ? Clean::entier($_POST['box_login'])    : 0;
$box_password = (isset($_POST['box_password']))  ? Clean::entier($_POST['box_password']) : 0;
$courriel     = (isset($_POST['f_courriel']))    ? Clean::courriel($_POST['f_courriel']) : '';

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Ajouter un nouvel administrateur
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='ajouter') && $nom && $prenom && ($box_login || $login) && ($box_password || $password) )
{
  // Vérifier que l'identifiant ENT est disponible (parmi tous les utilisateurs de l'établissement)
  if($id_ent)
  {
    if( DB_STRUCTURE_ADMINISTRATEUR::DB_tester_utilisateur_identifiant('id_ent',$id_ent) )
    {
      exit('Erreur : identifiant ENT déjà utilisé !');
    }
  }
  // Vérifier que l'identifiant GEPI est disponible (parmi tous les utilisateurs de l'établissement)
  if($id_gepi)
  {
    if( DB_STRUCTURE_ADMINISTRATEUR::DB_tester_utilisateur_identifiant('id_gepi',$id_gepi) )
    {
      exit('Erreur : identifiant Gepi déjà utilisé !');
    }
  }
  if($box_login)
  {
    // Construire puis tester le login (parmi tous les utilisateurs de l'établissement)
    $login = fabriquer_login($prenom,$nom,$profil);
    if( DB_STRUCTURE_ADMINISTRATEUR::DB_tester_utilisateur_identifiant('login',$login) )
    {
      // Login pris : en chercher un autre en remplaçant la fin par des chiffres si besoin
      $login = DB_STRUCTURE_ADMINISTRATEUR::DB_rechercher_login_disponible($login);
    }
  }
  else
  {
    // Vérifier que le login transmis est disponible (parmi tous les utilisateurs de l'établissement)
    if( DB_STRUCTURE_ADMINISTRATEUR::DB_tester_utilisateur_identifiant('login',$login) )
    {
      exit('Erreur : login déjà existant !');
    }
  }
  if($box_password)
  {
    // Générer un mdp aléatoire
    $password = fabriquer_mdp($profil);
  }
  else
  {
    // Vérifier que le mdp transmis est d'une longueur compatible
    if(mb_strlen($password)<$_SESSION['TAB_PROFILS_ADMIN']['MDP_LONGUEUR_MINI'][$profil])
    {
      exit('Erreur : mot de passe trop court pour ce profil !');
    }
  }
  // Vérifier que l'adresse e-mail est disponible (parmi tous les utilisateurs de l'établissement)
  if($courriel)
  {
    if( DB_STRUCTURE_ADMINISTRATEUR::DB_tester_utilisateur_identifiant('email',$courriel) )
    {
      exit('Erreur : adresse e-mail déjà utilisée !');
    }
    // On ne vérifie le domaine du serveur mail qu'en mode multi-structures car ce peut être sinon une installation sur un serveur local non ouvert sur l'extérieur.
    if(HEBERGEUR_INSTALLATION=='multi-structures')
    {
      $mail_domaine = tester_domaine_courriel_valide($courriel);
      if($mail_domaine!==TRUE)
      {
        exit('Erreur avec le domaine "'.$mail_domaine.'" !');
      }
    }
  }
  // Insérer l'enregistrement
  $user_id = DB_STRUCTURE_COMMUN::DB_ajouter_utilisateur( 0 /*user_sconet_id*/ , 0 /*sconet_num*/ , '' /*reference*/ , $profil , $nom , $prenom , NULL /*user_naissance_date*/ , $courriel , $login , crypter_mdp($password) , 0 /*eleve_classe_id*/ , $id_ent , $id_gepi );
  // Afficher le retour
  echo'<tr id="id_'.$user_id.'" class="new">';
  echo  '<td>'.html($id_ent).'</td>';
  echo  '<td>'.html($id_gepi).'</td>';
  echo  '<td>'.html($nom).'</td>';
  echo  '<td>'.html($prenom).'</td>';
  echo  '<td class="new">'.html($login).' <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Pensez à noter le login !" /></td>';
  echo  '<td class="new">'.html($password).' <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Pensez à noter le mot de passe !" /></td>';
  echo  '<td>'.html($courriel).'</td>';
  echo  '<td class="nu">';
  echo    '<q class="modifier" title="Modifier cet administrateur."></q>';
  echo    '<q class="supprimer" title="Retirer cet administrateur."></q>';
  echo  '</td>';
  echo'</tr>';
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Modifier un administrateur existant
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='modifier') && $id && $nom && $prenom && ($box_login || $login) && ( $box_password || $password ) )
{
  $tab_donnees = array();
  // Vérifier que l'identifiant ENT est disponible (parmi tous les utilisateurs de l'établissement)
  if($id_ent)
  {
    if( DB_STRUCTURE_ADMINISTRATEUR::DB_tester_utilisateur_identifiant('id_ent',$id_ent,$id) )
    {
      exit('Erreur : identifiant ENT déjà utilisé !');
    }
  }
  // Vérifier que l'identifiant GEPI est disponible (parmi tous les utilisateurs de l'établissement)
  if($id_gepi)
  {
    if( DB_STRUCTURE_ADMINISTRATEUR::DB_tester_utilisateur_identifiant('id_gepi',$id_gepi,$id) )
    {
      exit('Erreur : identifiant Gepi déjà utilisé !');
    }
  }
  // Vérifier que le login transmis est disponible (parmi tous les utilisateurs de l'établissement)
  if(!$box_login)
  {
    if( DB_STRUCTURE_ADMINISTRATEUR::DB_tester_utilisateur_identifiant('login',$login,$id) )
    {
      exit('Erreur : login déjà existant !');
    }
    $tab_donnees[':login'] = $login;
  }
  // Vérifier que l'adresse e-mail est disponible (parmi tous les utilisateurs de l'établissement)
  if($courriel)
  {
    if( DB_STRUCTURE_ADMINISTRATEUR::DB_tester_utilisateur_identifiant('email',$courriel,$id) )
    {
      exit('Erreur : adresse e-mail déjà utilisée !');
    }
    // On ne vérifie le domaine du serveur mail qu'en mode multi-structures car ce peut être sinon une installation sur un serveur local non ouvert sur l'extérieur.
    if(HEBERGEUR_INSTALLATION=='multi-structures')
    {
      $mail_domaine = tester_domaine_courriel_valide($courriel);
      if($mail_domaine!==TRUE)
      {
        exit('Erreur avec le domaine "'.$mail_domaine.'" !');
      }
    }
  }
  // Cas du mot de passe
  if(!$box_password)
  {
    $tab_donnees[':password'] = crypter_mdp($password);
  }
  // Mettre à jour l'enregistrement
  $tab_donnees += array(':nom'=>$nom,':prenom'=>$prenom,':email'=>$courriel,':id_ent'=>$id_ent,':id_gepi'=>$id_gepi);
  DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_user( $id , $tab_donnees );
  // Mettre à jour aussi éventuellement la session
  if($id==$_SESSION['USER_ID'])
  {
    $_SESSION['USER_NOM']    = $nom ;
    $_SESSION['USER_PRENOM'] = $prenom ;
  }
  // Afficher le retour
  echo'<td>'.html($id_ent).'</td>';
  echo'<td>'.html($id_gepi).'</td>';
  echo'<td>'.html($nom).'</td>';
  echo'<td>'.html($prenom).'</td>';
  echo'<td>'.html($login).'</td>';
  echo ($box_password) ? '<td class="i">champ crypté</td>' : '<td class="new">'.$password.' <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Pensez à noter le mot de passe !" /></td>' ;
  echo'<td>'.html($courriel).'</td>';
  echo'<td class="nu">';
  echo  '<q class="modifier" title="Modifier ce administrateur."></q>';
  echo  ($id!=$_SESSION['USER_ID']) ? '<q class="supprimer" title="Retirer cet administrateur."></q>' : '<q class="supprimer_non" title="Un administrateur ne peut pas supprimer son propre compte."></q>' ;
  echo'</td>';
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Retirer un administrateur existant
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='supprimer') && $id )
{
  if($id==$_SESSION['USER_ID'])
  {
    exit('Erreur : un administrateur ne peut pas supprimer son propre compte !');
  }
  // Supprimer l'enregistrement
  DB_STRUCTURE_ADMINISTRATEUR::DB_supprimer_utilisateur( $id , $profil );
  // Log de l'action
  SACocheLog::ajouter('Suppression d\'un utilisateur ('.$profil.' '.$id.').');
  // Afficher le retour
  exit('<td>ok</td>');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là !
// ////////////////////////////////////////////////////////////////////////////////////////////////////

exit('Erreur avec les données transmises !');

?>
