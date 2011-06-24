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
$TITRE = "Connexion serveur CAS";
/*
 * URL directe mono-structure           : http://adresse.com?page=public_login_CAS
 * URL directe multi-structure publique : http://adresse.com?page=public_login_CAS&f_base=...
 * URL directe multi-structure spéciale : http://adresse.com?page=public_login_CAS&uai=...
 */
?>

<?php

/*
 * Dans le cadre d'une installation académique multi-structure, depuis un portail ENT où un user serait déjà connecté,
 * il se peut qu'une connection directe ne puisse être établie qu'avec l'UAI (connu de l'ENT) en non avec le numéro de la base SACoche (inconnu de l'ENT).
 * Dans ce cas, on récupère le numéro de la base et on rappelle la page avec, pour ne pas avoir à recommencer à chaque échange avec le serveur CAS pendant l'authentification.
 */

$UAI = (isset($_GET['uai'])) ? clean_uai($_GET['uai']) : '' ;

if( (HEBERGEUR_INSTALLATION=='multi-structures') && ($UAI!='' ) )
{
	$DB_ROW = DB_WEBMESTRE_recuperer_structure_by_UAI($UAI);
	if(count($DB_ROW))
	{
		alert_redirection_exit($texte_alert='',$adresse='index.php?page=public_login_CAS&f_base='.$DB_ROW['sacoche_base']);
	}
	else
	{
		affich_message_exit($titre='Donnée incorrecte',$contenu='Le numéro UAI transmis n\'est pas référencé sur cette installation.');
	}
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Préparation de la connexion au serveur CAS
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

$BASE = (isset($_GET['f_base'])) ? intval($_GET['f_base']) : 0 ;

// En cas de multi-structures, il faut savoir dans quelle base récupérer les informations

if(HEBERGEUR_INSTALLATION=='multi-structures')
{
	if($BASE)
	{
		charger_parametres_mysql_supplementaires($BASE);
	}
	else
	{
		affich_message_exit($titre='Donnée manquante',$contenu='Paramètre indiquant la base concernée non transmis.');
	}
}

// Mettre à jour la base si nécessaire
maj_base_si_besoin($BASE);

// On charge les paramètres de connexion du serveur CAS

$DB_TAB = DB_STRUCTURE_lister_parametres('"connexion_mode","cas_serveur_host","cas_serveur_port","cas_serveur_root"');
foreach($DB_TAB as $DB_ROW)
{
	${$DB_ROW['parametre_nom']} = $DB_ROW['parametre_valeur'];
}
if( (isset($connexion_mode,$cas_serveur_host,$cas_serveur_port,$cas_serveur_root)==false) || ($connexion_mode!='cas') )
{
	affich_message_exit($titre='Données incompatibles',$contenu='Base de l\'établissement non configurée par l\'administrateur pour une connexion CAS.');
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Connexion au serveur CAS pour s'identifier
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

// Inclure la classe phpCAS
require_once('./_inc/class.CAS.1.2.0.php');
// Pour tester, cette méthode statique créé un fichier de log sur ce qui se passe avec CAS
// phpCAS::setDebug('debugcas.txt');
// Initialiser la connexion avec CAS  ; le premier argument est la version du protocole CAS ; le dernier argument indique qu'on utilise la session existante
phpCAS::client(CAS_VERSION_2_0, $cas_serveur_host, (int)$cas_serveur_port, $cas_serveur_root, false);
phpCAS::setLang(PHPCAS_LANG_FRENCH);
// On indique qu'il n'y a pas de validation du certificat SSL à faire
phpCAS::setNoCasServerValidation();
// Gestion du single sign-out
phpCAS::handleLogoutRequests(false);
// Demander à CAS d'aller interroger le serveur
// Cette méthode permet de forcer CAS à demander au client de s'authentifier s'il ne trouve aucun client d'authentifié.
// (redirige vers le serveur d'authentification si aucun utilisateur authentifié n'a été trouvé par le client CAS)
phpCAS::forceAuthentication();
// Rapatrier les informations si elles sont validées par CAS (qui envoie alors un ticket en GET)
$auth = phpCAS::checkAuthentication();
// Récupérer l'identifiant (login ou numéro interne...) de l'utilisateur authentifié pour le traiter dans l'application
$login = phpCAS::getUser();

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Comparer avec les données de la base
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

$connexion = connecter_user($BASE,$login,$password=false,$mode_connection='cas');

if($connexion=='ok')
{
	// Redirection vers l'espace en cas de succès
	alert_redirection_exit($texte_alert='',$adresse='index.php?page=compte_accueil&verif_cookie');
}
else
{
	// Affichage d'un message d'erreur en cas d'échec
	affich_message_exit($titre='Compte inaccessible.',$contenu=$connexion);
}
?>