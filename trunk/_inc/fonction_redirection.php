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

/*
 * Déclarations d'en-tête d'une page HTML (avant la section <head></head>).
 */
function entete()
{
	header('Content-Type: text/html; charset='.CHARSET);
	echo'<?xml version="1.0" encoding="'.CHARSET.'"?>';
	echo'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
	echo'<html xml:lang="fr" xmlns="http://www.w3.org/1999/xhtml">';
}

/*
 * Afficher une page HTML avec un message explicatif et un lien pour retourner en page d'accueil (si AJAX, renvoyer juste un message).
 * 
 * @param string $titre     titre de la page
 * @param string $contenu   contenu HTML affiché (ou AJAX retourné)
 * @param string $lien      facultatif ; texte <a href="...">...</a> si on veut autre chose qu'un lien vers l'accueil.
 * @return exit !
 */
function affich_message_exit($titre,$contenu,$lien='')
{
	if(SACoche=='index')
	{
		entete();
		echo'<head><title>SACoche » '.$titre.'</title><meta http-equiv="Content-Type" content="text/html; charset='.CHARSET.'" /></head>';
		echo'<body style="background:#EAEAFF;font:15px sans-serif;color:#D00">';
		echo'<p>'.$contenu.'</p>';
		echo ($lien) ? '<p>'.$lien.'</p>' : '<p><a href="./index.php">Retour en page d\'accueil de SACoche.</a></p>' ;
		echo'</body>';
		echo'</html>';
	}
	else
	{
		echo $contenu;
	}
	exit();
}

/*
 * Rediriger vers l'authentification SSO si détecté, ou afficher une page HTML avec un message explicatif et un lien pour retourner en page d'accueil (si AJAX, renvoyer juste un message).
 * 
 * @param void
 * @return void | exit !
 */
function redirection_SSO_ou_message_exit()
{
	$test_get = ( (isset($_GET['sso'])) && ( (isset($_GET['base'])) || (isset($_GET['uai'])) || (HEBERGEUR_INSTALLATION=='mono-structure') ) ) ? TRUE : FALSE ;
	$test_cookie = ( ( (isset($_COOKIE[COOKIE_STRUCTURE])) || (HEBERGEUR_INSTALLATION=='mono-structure') ) && (isset($_COOKIE[COOKIE_AUTHMODE])) && ($_COOKIE[COOKIE_AUTHMODE]!='normal') ) ? TRUE : FALSE ;
	// si html
	if(SACoche=='index')
	{
		if( $test_get || $test_cookie )
		{
			define('LOGIN_SSO',TRUE);
		}
		else
		{
			affich_message_exit('Authentification manquante','Session perdue, expirée ou incompatible.<br />Veuillez vous (re)-connecter&hellip;');
		}
	}
	// si ajax
	else
	{
		echo ( $test_get || $test_cookie ) ? 'Session perdue / expirée / incompatible. Veuillez actualiser la page.' : 'Session perdue / expirée / incompatible. Veuillez vous reconnecter&hellip;' ;
		exit();
	}
}

/*
 * Rediriger le navigateur avec header() et exit().
 * 
 * @param string $adresse
 * @return exit !
 */
function redirection_immediate($adresse='index.php')
{
	header('Status: 307 Temporary Redirect', true, 307);
	header('Location: '.$adresse);
	exit();
}

/**
 * Test si l'accès est bloqué sur demande du webmestre ou d'un administrateur (maintenance, sauvegarde/restauration, ...).
 * Si tel est le cas, alors exit().
 * 
 * Fonction isolée dans ce fichier car il est chargé parmi les premiers.
 * 
 * Nécessite que la session soit ouverte.
 * Appelé depuis les pages index.php + ajax.php + lors d'une demande d'identification d'un utilisateur (sauf webmestre)
 * 
 * En cas de blocage demandé par le webmestre, on ne laisse l'accès que :
 * - pour le webmestre déjà identifié
 * - pour la partie publique, si pas une demande d'identification, sauf demande webmestre
 * 
 * En cas de blocage demandé par un administrateur ou par l'automate (sauvegarde/restauration) pour un établissement donné, on ne laisse l'accès que :
 * - pour le webmestre déjà identifié
 * - pour un administrateur déjà identifié
 * - pour la partie publique, si pas une demande d'identification, sauf demande webmestre ou administrateur
 * 
 * @param string $BASE                       car $_SESSION['BASE'] non encore renseigné si demande d'identification
 * @param string $demande_connexion_profil   false si appel depuis index.php ou ajax.php, le profil si demande d'identification
 * @return void | exit !
 */
function tester_blocage_application($BASE,$demande_connexion_profil)
{
	// Blocage demandé par le webmestre pour tous les établissements (multi-structures) ou pour l'établissement (mono-structure).
	$fichier_blocage = CHEMIN_CONFIG.'blocage_webmestre_0.txt';
	if( (is_file($fichier_blocage)) && ($_SESSION['USER_PROFIL']!='webmestre') && (($_SESSION['USER_PROFIL']!='public')||($demande_connexion_profil!=false)) )
	{
		affich_message_exit($titre='Blocage par le webmestre',$contenu='Blocage par le webmestre - '.file_get_contents($fichier_blocage) );
	}
	// Blocage demandé par le webmestre pour un établissement donné (multi-structures).
	$fichier_blocage = CHEMIN_CONFIG.'blocage_webmestre_'.$BASE.'.txt';
	if( (is_file($fichier_blocage)) && ($_SESSION['USER_PROFIL']!='webmestre') && (($_SESSION['USER_PROFIL']!='public')||($demande_connexion_profil!=false)) )
	{
		affich_message_exit($titre='Blocage par le webmestre',$contenu='Blocage par le webmestre - '.file_get_contents($fichier_blocage) );
	}
	// Blocage demandé par un administrateur pour son établissement.
	$fichier_blocage = CHEMIN_CONFIG.'blocage_administrateur_'.$BASE.'.txt';
	if( (is_file($fichier_blocage)) && (!in_array($_SESSION['USER_PROFIL'],array('webmestre','administrateur'))) && (($_SESSION['USER_PROFIL']!='public')||(!in_array($demande_connexion_profil,array(FALSE,'webmestre','administrateur')))) )
	{
		affich_message_exit($titre='Blocage par un administrateur',$contenu='Blocage par un administrateur - '.file_get_contents($fichier_blocage) );
	}
	// Blocage demandé par l'automate pour un établissement donné.
	$fichier_blocage = CHEMIN_CONFIG.'blocage_automate_'.$BASE.'.txt';
	if( (is_file($fichier_blocage)) && (!in_array($_SESSION['USER_PROFIL'],array('webmestre','administrateur'))) && (($_SESSION['USER_PROFIL']!='public')||(!in_array($demande_connexion_profil,array(FALSE,'webmestre','administrateur')))) )
	{
		// Au cas où une procédure de sauvegarde / restauration / nettoyage / tranfert échouerait, un fichier de blocage automatique pourrait être créé et ne pas être effacé.
		// Pour cette raison on teste une durée de vie anormalement longue d'une tel fichier de blocage (puisqu'il ne devrait être que temporaire).
		if( time() - filemtime($fichier_blocage) < 5*60 )
		{
			affich_message_exit($titre='Blocage automatique',$contenu='Blocage automatique - '.file_get_contents($fichier_blocage) );
		}
		else
		{
			// La fonction debloquer_application sera lancée plus tard car elle requiert des fichiers pas encore chargés.
			$_SESSION['blocage_anormal'] = TRUE;
		}
	}
}

?>