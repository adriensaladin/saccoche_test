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

$BASE     = (isset($_POST['f_base']))     ? Clean::entier(  $_POST['f_base']    ) : 0 ;
$courriel = (isset($_POST['f_courriel'])) ? Clean::courriel($_POST['f_courriel']) : '';
$captcha  = (isset($_POST['f_captcha']))  ? Clean::texte(   $_POST['f_captcha'] ) : '';

if( !$courriel || ( (HEBERGEUR_INSTALLATION=='multi-structures') && !$BASE ) )
{
  exit_json( FALSE , 'Erreur avec les données transmises !' );
}

// Protection contre les attaques par force brute des robots (piratage compte ou envoi intempestif de courriels)
if(!isset($_SESSION['FORCEBRUTE'][$PAGE]))
{
  exit_json( FALSE , 'Session perdue ou absence de cookie : merci d\'actualiser la page.' );
}
else if( $_SERVER['REQUEST_TIME'] - $_SESSION['FORCEBRUTE'][$PAGE]['TIME'] < $_SESSION['FORCEBRUTE'][$PAGE]['DELAI'] )
{
  $_SESSION['FORCEBRUTE'][$PAGE]['TIME'] = $_SERVER['REQUEST_TIME'];
  exit_json( FALSE , 'Sécurité : patienter '.$_SESSION['FORCEBRUTE'][$PAGE]['DELAI'].'s avant une nouvelle tentative.' );
}

// On vérifie le captcha.
if( $captcha != $_SESSION['FORCEBRUTE'][$PAGE]['CAPTCHA'] )
{
  $_SESSION['FORCEBRUTE'][$PAGE]['DELAI']++;
  $_SESSION['FORCEBRUTE'][$PAGE]['TIME'] = $_SERVER['REQUEST_TIME'];
  exit_json( FALSE , 'Ordre incorrect ! Nouvelle tentative autorisée dans '.$_SESSION['FORCEBRUTE'][$PAGE]['DELAI'].'s.' );
}

// On vérifie le domaine du serveur mail même en mode mono-structure parce que de toutes façons il faudra ici envoyer un mail, donc l'installation doit être ouverte sur l'extérieur.
$mail_domaine = tester_domaine_courriel_valide($courriel);
if($mail_domaine!==TRUE)
{
  exit_json( FALSE , 'Erreur avec le domaine "'.$mail_domaine.'" !' );
}

// En cas de multi-structures, il faut charger les paramètres de connexion à la base concernée
if(HEBERGEUR_INSTALLATION=='multi-structures')
{
  charger_parametres_mysql_supplementaires($BASE);
}

// On teste si l'adresse mail est trouvée
$DB_ROW = DB_STRUCTURE_PUBLIC::DB_recuperer_user_for_new_mdp('user_email',$courriel);
if(empty($DB_ROW))
{
  $_SESSION['FORCEBRUTE'][$PAGE]['DELAI']++;
  $_SESSION['FORCEBRUTE'][$PAGE]['TIME'] = $_SERVER['REQUEST_TIME'];
  exit_json( FALSE , 'Adresse inconnue ! Autre tentative autorisée dans '.$_SESSION['FORCEBRUTE'][$PAGE]['DELAI'].'s.' );
}

// On enregistre un ticket pour cette demande
$user_pass_key = crypter_mdp($DB_ROW['user_id'].$DB_ROW['user_email'].$DB_ROW['user_password'].$DB_ROW['user_connexion_date']);
$code_mdp = ($BASE) ? $user_pass_key.'g'.$BASE : $user_pass_key ;
DB_STRUCTURE_PUBLIC::DB_modifier_user_password_or_key ($DB_ROW['user_id'] , '' /*user_password*/ , $user_pass_key /*user_pass_key*/ );

// On envoi le courriel à l'utilisateur
$mail_contenu = 'Bonjour,'."\r\n";
$mail_contenu.= "\r\n";
$mail_contenu.= 'Une demande de nouveaux identifiants vient d\'être formulée concernant le compte SACoche ayant cette adresse de courriel :'."\r\n";
$mail_contenu.= $DB_ROW['user_email']."\r\n";
$mail_contenu.= "\r\n";
$mail_contenu.= 'Pour confirmer la génération d\'un nouveau mot de passe, veuillez cliquer sur ce lien :'."\r\n";
$mail_contenu.= URL_DIR_SACOCHE.'?code_mdp='.$code_mdp."\r\n";
$mail_contenu.= fabriquer_texte_courriel( array('excuses_derangement','info_connexion','no_reply','signature') , $DB_ROW['user_email'] );
$courriel_bilan = Sesamail::mail( $DB_ROW['user_email'] , 'Demande de nouveaux identifiants' , $mail_contenu , $DB_ROW['user_email'] /*replyto*/ );
if(!$courriel_bilan)
{
  exit_json( FALSE , 'Erreur lors de l\'envoi du courriel !' );
}

// OK !
exit_json( TRUE );

?>