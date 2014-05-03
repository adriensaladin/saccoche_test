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

$action          = (isset($_POST['f_action']))        ? Clean::texte($_POST['f_action'])         : '';
$champ_nom       = (isset($_POST['champ_nom']))       ? Clean::texte($_POST['champ_nom'])        : '';
$champ_val       = (isset($_POST['champ_val']))       ? Clean::texte($_POST['champ_val'])        : '';
$id              = (isset($_POST['f_id']))            ? Clean::entier($_POST['f_id'])            : 0;
$id_ent          = (isset($_POST['f_id_ent']))        ? Clean::texte($_POST['f_id_ent'])         : '';
$id_gepi         = (isset($_POST['f_id_gepi']))       ? Clean::texte($_POST['f_id_gepi'])        : '';
$sconet_id       = (isset($_POST['f_sconet_id']))     ? Clean::entier($_POST['f_sconet_id'])     : 0;
$sconet_num      = (isset($_POST['f_sconet_num']))    ? Clean::entier($_POST['f_sconet_num'])    : 0;
$reference       = (isset($_POST['f_reference']))     ? Clean::ref($_POST['f_reference'])        : '';
$profil          = (isset($_POST['f_profil']))        ? Clean::texte($_POST['f_profil'])         : '';
$nom             = (isset($_POST['f_nom']))           ? Clean::nom($_POST['f_nom'])              : '';
$prenom          = (isset($_POST['f_prenom']))        ? Clean::prenom($_POST['f_prenom'])        : '';
$login           = (isset($_POST['f_login']))         ? Clean::login($_POST['f_login'])          : '';
$courriel        = (isset($_POST['f_courriel']))      ? Clean::courriel($_POST['f_courriel'])    : '';
$sortie_date     = (isset($_POST['f_sortie_date']))   ? Clean::date_fr($_POST['f_sortie_date'])  : '' ;
$box_sortie_date = (isset($_POST['box_sortie_date'])) ? Clean::entier($_POST['box_sortie_date']) : 0;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Rechercher un utilisateur
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='rechercher') && in_array($champ_nom,array('id_ent','id_gepi','sconet_id','sconet_elenoet','reference','login','email','nom','prenom')) && $champ_val )
{
  $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_rechercher_users( $champ_nom , $champ_val );
  $nb_reponses = count($DB_TAB) ;
  if($nb_reponses==0)
  {
    exit('nada');
  }
  else if($nb_reponses>100)
  {
    exit($nb_reponses.' réponses : restreignez votre recherche !');
  }
  else
  {
    // Tableau avec noms des profils en session pour usage si modification de l'utilisateur
    $_SESSION['tmp'] = array();
    foreach($DB_TAB as $DB_ROW)
    {
      $_SESSION['tmp'][$DB_ROW['user_profil_sigle']] = $DB_ROW['user_profil_nom_long_singulier'];
      // Formater la date
      $date_mysql  = $DB_ROW['user_sortie_date'];
      $date_affich = ($date_mysql!=SORTIE_DEFAUT_MYSQL) ? convert_date_mysql_to_french($date_mysql) : '-' ;
      // Afficher une ligne du tableau
      echo'<tr id="id_'.$DB_ROW['user_id'].'">';
      echo  '<td>'.html($DB_ROW['user_id_ent']).'</td>';
      echo  '<td>'.html($DB_ROW['user_id_gepi']).'</td>';
      echo  '<td>'.html($DB_ROW['user_sconet_id']).'</td>';
      echo  '<td>'.html($DB_ROW['user_sconet_elenoet']).'</td>';
      echo  '<td>'.html($DB_ROW['user_reference']).'</td>';
      echo  '<td>'.html($DB_ROW['user_profil_sigle']).' <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="'.html(html($DB_ROW['user_profil_nom_long_singulier'])).'" /></td>'; // Volontairement 2 html() pour le title sinon &lt;* est pris comme une balise html par l'infobulle.
      echo  '<td>'.html($DB_ROW['user_nom']).'</td>';
      echo  '<td>'.html($DB_ROW['user_prenom']).'</td>';
      echo  '<td>'.html($DB_ROW['user_login']).'</td>';
      echo  '<td>'.html($DB_ROW['user_email']).'</td>';
      echo  '<td>'.$date_affich.'</td>';
      echo  '<td class="nu">';
      echo    '<q class="modifier" title="Modifier cet utilisateur."></q>';
      echo  '</td>';
      echo'</tr>'.NL;
    }
    exit();
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Modifier un utilisateur existant
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='modifier') && $id && $profil && $nom && $prenom && $login && ($box_sortie_date || $sortie_date) )
{
  // Vérifier le profil
  if(!isset($_SESSION['TAB_PROFILS_ADMIN']['TYPE'][$profil]))
  {
    exit('Erreur : profil incorrect !');
  }
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
  // Vérifier que l'identifiant sconet est disponible (parmi les utilisateurs de même type de profil)
  if($sconet_id)
  {
    if( DB_STRUCTURE_ADMINISTRATEUR::DB_tester_utilisateur_identifiant('sconet_id',$sconet_id,$id,$_SESSION['TAB_PROFILS_ADMIN']['TYPE'][$profil]) )
    {
      exit('Erreur : identifiant Sconet déjà utilisé !');
    }
  }
  // Vérifier que le n° sconet est disponible (parmi les utilisateurs de même type de profil)
  if($sconet_num)
  {
    if( DB_STRUCTURE_ADMINISTRATEUR::DB_tester_utilisateur_identifiant('sconet_elenoet',$sconet_num,$id,$_SESSION['TAB_PROFILS_ADMIN']['TYPE'][$profil]) )
    {
      exit('Erreur : numéro Sconet déjà utilisé !');
    }
  }
  // Vérifier que la référence est disponible (parmi les utilisateurs de même type de profil)
  if($reference)
  {
    if( DB_STRUCTURE_ADMINISTRATEUR::DB_tester_utilisateur_identifiant('reference',$reference,$id,$_SESSION['TAB_PROFILS_ADMIN']['TYPE'][$profil]) )
    {
      exit('Erreur : référence déjà utilisée !');
    }
  }
  // Vérifier que le login transmis est disponible (parmi tous les utilisateurs de l'établissement)
  if( DB_STRUCTURE_ADMINISTRATEUR::DB_tester_utilisateur_identifiant('login',$login,$id) )
  {
    exit('Erreur : login déjà existant !');
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
  // Cas de la date de sortie
  if($box_sortie_date)
  {
    $sortie_date = '-' ;
    $sortie_date_mysql = SORTIE_DEFAUT_MYSQL;
  }
  else
  {
    $sortie_date_mysql = convert_date_french_to_mysql($sortie_date);
  }
  // Mettre à jour l'enregistrement
  $tab_donnees = array(':sconet_id'=>$sconet_id,':sconet_num'=>$sconet_num,':reference'=>$reference,':nom'=>$nom,':prenom'=>$prenom,':email'=>$courriel,':login'=>$login,':id_ent'=>$id_ent,':id_gepi'=>$id_gepi,':sortie_date'=>$sortie_date_mysql);
  DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_user( $id , $tab_donnees );
  // Afficher le retour
  echo'<td>'.html($id_ent).'</td>';
  echo'<td>'.html($id_gepi).'</td>';
  echo'<td>'.html($sconet_id).'</td>';
  echo'<td>'.html($sconet_num).'</td>';
  echo'<td>'.html($reference).'</td>';
  echo'<td>'.html($profil).' <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="'.html(html($_SESSION['tmp'][$profil])).'" /></td>';
  echo'<td>'.html($nom).'</td>';
  echo'<td>'.html($prenom).'</td>';
  echo'<td>'.html($login).'</td>';
  echo'<td>'.html($courriel).'</td>';
  echo'<td>'.$sortie_date.'</td>';
  echo'<td class="nu">';
  echo  '<q class="modifier" title="Modifier cet utilisateur."></q>';
  echo'</td>';
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

exit('Erreur avec les données transmises !');

?>
