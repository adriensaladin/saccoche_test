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
$courriel = (isset($_POST['f_courriel'])) ? Clean::courriel($_POST['f_courriel']) : NULL;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Mettre à jour son adresse e-mail (éventuellement vide pour la retirer)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='courriel') && ($courriel!==NULL) )
{
  // Vérifier que l'utilisateur a les droits de la modifier / retirer
  if( ($_SESSION['USER_EMAIL_ORIGINE']=='admin') && ($_SESSION['USER_PROFIL_TYPE']!=='administrateur') && !test_user_droit_specifique($_SESSION['DROIT_MODIFIER_EMAIL']) )
  {
    exit_json( FALSE , 'Erreur : droit insuffisant, contactez un administrateur !' );
  }
  // Vérifier que l'adresse e-mail est disponible (parmi tous les utilisateurs de l'établissement)
  if($courriel)
  {
    $find_courriel = DB_STRUCTURE_ADMINISTRATEUR::DB_tester_utilisateur_identifiant('email',$courriel,$_SESSION['USER_ID']);
    if( $find_courriel )
    {
      exit_json( FALSE , 'Erreur : adresse e-mail déjà utilisée !' );
    }
    if( $find_courriel === NULL )
    {
      // On ne vérifie le domaine du serveur mail qu'en mode multi-structures car ce peut être sinon une installation sur un serveur local non ouvert sur l'extérieur.
      if(HEBERGEUR_INSTALLATION=='multi-structures')
      {
        $mail_domaine = tester_domaine_courriel_valide($courriel);
        if($mail_domaine!==TRUE)
        {
          exit_json( FALSE , 'Erreur avec le domaine "'.$mail_domaine.'" !' );
        }
      }
      $email_origine = 'user';
    }
  }
  else
  {
    $email_origine = '';
  }
  // C'est ok
  DB_STRUCTURE_COMMUN::DB_modifier_user_parametre( $_SESSION['USER_ID'] , 'user_email' , $courriel );
  $_SESSION['USER_EMAIL']         = $courriel ;
  $_SESSION['USER_EMAIL_ORIGINE'] = isset($email_origine) ? $email_origine : $_SESSION['USER_EMAIL_ORIGINE'] ; // si le mail n'a pas été changé alors il ne faut pas non plus modifier cette valeur
  // Construction du retour
  $info_origine = '';
  $info_edition = '';
  if( $_SESSION['USER_EMAIL'] && $_SESSION['USER_EMAIL_ORIGINE'] )
  {
    if($_SESSION['USER_EMAIL_ORIGINE']=='user')
    {
      $info_origine = '<span class="astuce">L\'adresse enregistrée a été saisie par vous-même.</span>';
    }
    else
    {
      $info_origine = '<span class="astuce">L\'adresse enregistrée a été importée ou saisie par un administrateur.</span>';
      if( ($_SESSION['USER_PROFIL_TYPE']=='administrateur') || test_user_droit_specifique($_SESSION['DROIT_MODIFIER_EMAIL']) )
      {
        $info_edition = '<span class="astuce">Vous êtes habilité à modifier cette adresse si vous le souhaitez.</span>';
      }
      else
      {
        $info_edition = '<span class="danger">Vous n\'êtes pas habilité à modifier l\'adresse vous-même ! Veuillez contacter un administrateur.</span>';
        $disabled = ' disabled';
      }
    }
  }
  else
  {
    $info_origine = '<span class="astuce">Il n\y a pas d\'adresse actuellement enregistrée.</span>';
  }
  exit_json( TRUE ,  array( 'info_adresse'=>$info_origine.'<br />'.$info_edition ) );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là !
// ////////////////////////////////////////////////////////////////////////////////////////////////////

exit_json( FALSE , 'Erreur avec les données transmises !' );

?>
