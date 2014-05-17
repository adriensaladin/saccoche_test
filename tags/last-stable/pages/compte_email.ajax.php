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

$courriel = (isset($_POST['f_courriel'])) ? Clean::courriel($_POST['f_courriel']) : NULL;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Mettre à jour son adresse e-mail (éventuellement vide pour la retirer)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($courriel!==NULL)
{
  // Vérifier que l'adresse e-mail est disponible (parmi tous les utilisateurs de l'établissement)
  if($courriel)
  {
    if( DB_STRUCTURE_ADMINISTRATEUR::DB_tester_utilisateur_identifiant('email',$courriel,$_SESSION['USER_ID']) )
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
  // C'est ok...
  DB_STRUCTURE_COMMUN::DB_modifier_user_parametre( $_SESSION['USER_ID'] , 'user_email' , $courriel );
  $_SESSION['USER_EMAIL'] = $courriel ;
  exit('ok');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là !
// ////////////////////////////////////////////////////////////////////////////////////////////////////

exit('Erreur avec les données transmises !');

?>
