<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2009
 * 
 * ****************************************************************************************************
 * SACoche [http://competences.sesamath.net] - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath [http://www.sesamath.net]
 * Distribution sous licence libre prévue pour l'été 2010.
 * ****************************************************************************************************
 * 
 * Ouverture de la session et gestion des cas possibles.
 * Nécessite "fonction_sessions.php".
 * 
 */

$ALERTE_SSO = false;
$tab_msg_alerte = array();
$tab_msg_alerte['I.2']['index'] = 'Tentative d\'accès direct à une page réservée !\nRedirection vers l\'accueil...';
$tab_msg_alerte['I.2']['ajax']  = 'Session perdue. Déconnectez-vous et reconnectez-vous...';
$tab_msg_alerte['II.1.a']['index'] = 'Votre session a expiré !\nRedirection vers l\'accueil...';
$tab_msg_alerte['II.1.a']['ajax']  = 'Session expirée. Déconnectez-vous et reconnectez-vous...';
$tab_msg_alerte['II.3.a']['index'] = 'Tentative d\'accès direct à une page réservée !\nRedirection vers l\'accueil...';
$tab_msg_alerte['II.3.a']['ajax']  = 'Page réservée. Retournez à l\'accueil...';
$tab_msg_alerte['II.4.c']['index'] = 'Tentative d\'accès direct à une page réservée !\nRedirection vers l\'accueil...';
$tab_msg_alerte['II.4.c']['ajax']  = 'Page réservée. Déconnexion effectuée. Retournez à l\'accueil...';

if(!isset($_COOKIE[SESSION_NOM]))
{
	// I. Aucune session transmise
	open_new_session(); init_session();
	if($PROFIL_REQUIS!='public')
	{
		// I.2. Redirection : demande d'accès à une page réservée donc identification avant accès direct
		alert_redirection_exit($tab_msg_alerte['I.2'][SACoche]);
	}
}
else
{
	// II. id de session transmis
	open_old_session();
	if(!isset($_SESSION['PROFIL']))
	{
		// II.1. Pas de session retrouvée (sinon cette variable serait renseignée)
		if($PROFIL_REQUIS!='public')
		{
			// II.1.a. Session perdue ou expirée et demande d'accès à une page réservée : redirection pour une nouvelle identification
			$ALERTE_SSO = close_session(); open_new_session(); init_session();
			alert_redirection_exit($tab_msg_alerte['II.1.a'][SACoche]);
		}
		else
		{
			// II.1.b. Session perdue ou expirée et page publique : création d'une nouvelle session (éventuellement un message d'alerte pour indiquer session perdue ?)
			$ALERTE_SSO = close_session();open_new_session();init_session();
		}
	}
	elseif($_SESSION['PROFIL'] == 'public')
	{
		// II.3. Personne non identifiée
		if($PROFIL_REQUIS!='public')
		{
			// II.3.a. Espace non identifié => Espace identifié : redirection pour identification
			init_session();
			alert_redirection_exit($tab_msg_alerte['II.3.a'][SACoche]);
		}
		else
		{
			// II.3.b. Espace non identifié => Espace non identifié : RAS
		}
	}
	else
	{
		// II.4. Personne identifiée
		if($_SESSION['PROFIL'] == $PROFIL_REQUIS)
		{
			// II.4.a. Espace identifié => Espace identifié identique : RAS
		}
		elseif($PROFIL_REQUIS=='public')
		{
			// II.4.b. Espace identifié => Espace non identifié : création d'une nouvelle session vierge (éventuellement un message d'alerte pour indiquer session perdue ?)
			if (SACoche!='ajax')
			{
				// Ne pas déconnecter si on appelle le calendrier de l'espace public
				$ALERTE_SSO = close_session();open_new_session();init_session();
			}
		}
		elseif($PROFIL_REQUIS!='public')
		{
			// II.4.c. Espace identifié => Autre espace identifié incompatible : redirection pour une nouvelle identification
			init_session();
			alert_redirection_exit($tab_msg_alerte['II.4.c'][SACoche]);
		}
	}
}
?>