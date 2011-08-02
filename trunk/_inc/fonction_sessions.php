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

// Fonctions pour gérer les sessions.
// La session est transmise via le cookie "$_COOKIE['SESSION_NOM']".

// Paramétrage de la session
define('SESSION_NOM','SACoche-session');
session_name(SESSION_NOM);
session_cache_limiter('nocache');
session_cache_expire(180);

/*
 * Ouvrir une session existante
 * 
 * @param void
 * @return bool
 */
function open_old_session()
{
	$ID = $_COOKIE[SESSION_NOM];
	session_id($ID);
	return session_start();
}

/*
 * Ouvrir une nouvelle session
 * 
 * @param void
 * @return bool
 */
function open_new_session()
{
	$ID = uniqid().md5('grain_de_sable'.mt_rand());	// Utiliser l'option préfixe ou entropie de uniqid() insère un '.' qui peut provoquer une erreur disant que les seuls caractères autorisés sont a-z, A-Z, 0-9 et -
	session_id($ID);
	return session_start();
}

/*
 * Initialiser une session ouverte
 * 
 * @param void
 * @return void
 */
function init_session()
{
	$_SESSION = array();
	// Numéro de la base
	$_SESSION['BASE']             = 0;
	// Données associées à l'utilisateur.
	$_SESSION['USER_PROFIL']      = 'public';	// public / webmestre / administrateur / directeur / professeur / eleve
	$_SESSION['USER_ID']          = 0;
	$_SESSION['USER_NOM']         = '-';
	$_SESSION['USER_PRENOM']      = '-';
	// Données associées à l'établissement.
	$_SESSION['SESAMATH_ID']      = 0;
	$_SESSION['CONNEXION_MODE']   = 'normal';
	$_SESSION['DUREE_INACTIVITE'] = 30;
}

/*
 * Fermer une session existante
 * 
 * @param void
 * @return void
 */
function close_session()
{
	$_SESSION = array();
	setcookie(session_name(),'',time()-42000,'');
	session_destroy();
}

/*
 * Rechercher une session existante et gérer les différents cas possibles.
 * 
 * @param array $TAB_PROFILS_AUTORISES
 * @return void | exit ! (sur une string si ajax, une page html, ou modification $PAGE pour process SSO)
 */
function gestion_session($TAB_PROFILS_AUTORISES)
{
	if(!isset($_COOKIE[SESSION_NOM]))
	{
		// 1. Aucune session transmise
		open_new_session(); init_session();
		if(!$TAB_PROFILS_AUTORISES['public'])
		{
			// 1.1. Demande d'accès à une page réservée, donc besoin d'identification
			if(isset($_GET['verif_cookie']))
			{
				// 1.1.1. En fait l'utilisateur vient déjà de s'identifier : c'est donc anormal, le cookie de session n'a pas été trouvé car le navigateur client n'enregistre pas les cookies
				affich_message_exit($titre='Problème de cookies',$contenu='Session non retrouvée !<br />Configurez votre navigateur pour qu\'il accepte les cookies.');
			}
			else
			{
				// 1.1.2. Session perdue ou expirée, ou demande d'accès direct (lien profond) : redirection pour une nouvelle identification
				redirection_SSO_ou_message_exit(); // Si SSO au prochain coup on ne passera plus par là.
			}
		}
		else
		{
			// 1.2 Accès à une page publique : RAS
		}
	}
	else
	{
		// 2. id de session transmis
		open_old_session();
		if(!isset($_SESSION['USER_PROFIL']))
		{
			// 2.1. Pas de session retrouvée (sinon cette variable serait renseignée)
			if(!$TAB_PROFILS_AUTORISES['public'])
			{
				// 2.1.1. Session perdue ou expirée et demande d'accès à une page réservée : redirection pour une nouvelle identification
				close_session(); open_new_session(); init_session();
				redirection_SSO_ou_message_exit(); // On peut initialiser la session avant car si SSO au prochain coup on ne passera plus par là.
			}
			else
			{
				// 2.1.2. Session perdue ou expirée et page publique : création d'une nouvelle session, pas de message d'alerte pour indiquer que la session perdue
				close_session();open_new_session();init_session();
			}
		}
		elseif($_SESSION['USER_PROFIL'] == 'public')
		{
			// 2.2. Session retrouvée, utilisateur non identifié
			if(!$TAB_PROFILS_AUTORISES['public'])
			{
				// 2.2.1. Espace non identifié => Espace identifié : redirection pour identification
				redirection_SSO_ou_message_exit(); // Pas d'initialisation de session sinon la redirection avec le SSO tourne en boucle.
			}
			else
			{
				// 2.2.2. Espace non identifié => Espace non identifié : RAS
			}
		}
		else
		{
			// 2.3. Session retrouvée, utilisateur identifié
			if($TAB_PROFILS_AUTORISES[$_SESSION['USER_PROFIL']])
			{
				// 2.3.1. Espace identifié => Espace identifié identique : RAS
			}
			elseif($TAB_PROFILS_AUTORISES['public'])
			{
				// 2.3.2. Espace identifié => Espace non identifié : création d'une nouvelle session vierge, pas de message d'alerte pour indiquer que la session perdue
				// A un moment il fallait tester que ce n'était pas un appel ajax,pour éviter une déconnexion si appel au calendrier qui était dans l'espace public, mais ce n'est plus le cas...
				// Par contre il faut conserver la session de SimpleSAMLphp pour laisser à l'utilisateur la choix de se déconnecter ou non de son SSO.
				$SimpleSAMLphp_SESSION = ($_SESSION['CONNEXION_MODE']=='gepi') ? $_SESSION['SimpleSAMLphp_SESSION'] : FALSE ;
				close_session();open_new_session();init_session();
				if($SimpleSAMLphp_SESSION) { $_SESSION['SimpleSAMLphp_SESSION'] = $SimpleSAMLphp_SESSION; }
			}
			elseif(!$TAB_PROFILS_AUTORISES['public']) // (forcément)
			{
				// 2.3.3. Espace identifié => Autre espace identifié incompatible : redirection pour une nouvelle identification
				// Pas de redirection SSO sinon on tourne en boucle (il faudrait faire une déconnexion SSO préalable).
				affich_message_exit($titre='Page interdite avec votre profil',$contenu='Vous avez appelé une page inaccessible avec votre identification actuelle !<br />Déconnectez-vous ou retournez à la page précédente.');
			}
		}
	}
}

?>