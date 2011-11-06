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

$tab_messages_erreur = array();

// Fichier appelé pour l'affichage de chaque page.
// Passage en GET des paramètres pour savoir quelle page charger.

// Atteste l'appel de cette page avant l'inclusion d'une autre
define('SACoche','index');

// Constantes / Fonctions de redirections / Configuration serveur / Session
require_once('./_inc/constantes.php');
require_once('./_inc/fonction_redirection.php');
require_once('./_inc/config_serveur.php');
require_once('./_inc/fonction_sessions.php');

// Page et section appelées ; normalement transmis en $_GET mais $_POST possibles depuis GEPI
    if(isset($_GET['page']))  { $PAGE = $_GET['page']; }
elseif(isset($_POST['page'])) { $PAGE = $_POST['page']; }
elseif(isset($_GET['sso']))   { $PAGE = 'compte_accueil'; }
else                          { $PAGE = 'public_accueil'; }
    if(isset($_GET['section']))  { $SECTION = $_GET['section']; }
elseif(isset($_POST['section'])) { $SECTION = $_POST['section']; }
else                             { $SECTION = ''; }

// Fichier d'informations sur l'hébergement (requis avant la gestion de la session).
$fichier_constantes = CHEMIN_CONFIG.'constantes.php';
if(is_file($fichier_constantes))
{
	require_once($fichier_constantes);
}
elseif($PAGE!='public_installation')
{
	affich_message_exit($titre='Informations hébergement manquantes',$contenu='Informations concernant l\'hébergeur manquantes.',$lien='<a href="./index.php?page=public_installation">Procédure d\'installation de SACoche.</a>');
}

// Ouverture de la session et gestion des droits d'accès
require_once('./_inc/tableau_droits.php');
if(!isset($tab_droits[$PAGE]))
{
	$tab_messages_erreur[] = 'Erreur : droits de la page "'.$PAGE.'" manquants.';
	$PAGE = (substr($PAGE,0,6)=='public') ? 'public_accueil' : 'compte_accueil' ;
}
gestion_session($tab_droits[$PAGE]);

// Pour le devel
if (DEBUG) afficher_infos_debug();

// Blocage éventuel par le webmestre ou un administrateur (on ne peut pas le tester avant car il faut avoir récupéré les données de session)
tester_blocage_application($_SESSION['BASE'],$demande_connexion_profil=false);

// Autres fonctions à charger
require_once('./_inc/fonction_clean.php');
require_once('./_inc/fonction_divers.php');
require_once('./_inc/fonction_affichage.php');

// Annuler un blocage par l'automate anormalement long
annuler_blocage_anormal();

// Patch fichier de config
if(is_file($fichier_constantes))
{
	// DEBUT PATCH CONFIG 1
	// A compter du 05/12/2010, ajout de paramètres dans le fichier de constantes pour paramétrer cURL. [à retirer dans quelques mois]
	if(!defined('SERVEUR_PROXY_USED') && function_exists('enregistrer_informations_session'))
	{
		fabriquer_fichier_hebergeur_info( array('SERVEUR_PROXY_USED'=>'','SERVEUR_PROXY_NAME'=>'','SERVEUR_PROXY_PORT'=>'','SERVEUR_PROXY_TYPE'=>'','SERVEUR_PROXY_AUTH_USED'=>'','SERVEUR_PROXY_AUTH_METHOD'=>'','SERVEUR_PROXY_AUTH_USER'=>'','SERVEUR_PROXY_AUTH_PASS'=>'') );
	}
	// FIN PATCH CONFIG 1
	// DEBUT PATCH CONFIG 2
	// A compter du 26/05/2011, ajout de paramètres dans le fichier de constantes pour les dates CNIL. [à retirer dans quelques mois]
	if(!defined('CNIL_NUMERO') && function_exists('enregistrer_informations_session'))
	{
		fabriquer_fichier_hebergeur_info( array('CNIL_NUMERO'=>HEBERGEUR_CNIL,'CNIL_DATE_ENGAGEMENT'=>'','CNIL_DATE_RECEPISSE'=>'') );
	}
	// FIN PATCH CONFIG 2
}

// Interface de connexion à la base, chargement et config (test sur $fichier_constantes car à éviter si procédure d'installation non terminée).
if(is_file($fichier_constantes))
{
	// Choix des paramètres de connexion à la base de données adaptée...
	// ...multi-structure ; base sacoche_structure_***
	if( (in_array($_SESSION['USER_PROFIL'],array('administrateur','directeur','professeur','parent','eleve'))) && (HEBERGEUR_INSTALLATION=='multi-structures') )
	{
		$fichier_mysql_config = 'serveur_sacoche_structure_'.$_SESSION['BASE'];
		$fichier_class_config = 'class.DB.config.sacoche_structure';
	}
	// ...multi-structure ; base sacoche_webmestre
	elseif( (in_array($_SESSION['USER_PROFIL'],array('webmestre','public'))) && (HEBERGEUR_INSTALLATION=='multi-structures') )
	{
		$fichier_mysql_config = 'serveur_sacoche_webmestre';
		$fichier_class_config = 'class.DB.config.sacoche_webmestre';
	}
	// ...mono-structure ; base sacoche_structure
	elseif(HEBERGEUR_INSTALLATION=='mono-structure')
	{
		$fichier_mysql_config = 'serveur_sacoche_structure';
		$fichier_class_config = 'class.DB.config.sacoche_structure';
	}
	else
	{
		affich_message_exit($titre='Configuration anormale',$contenu='Une anomalie dans les données d\'hébergement et/ou de session empêche l\'application de se poursuivre.');
	}
	// Ajout du chemin correspondant
	$fichier_mysql_config = CHEMIN_MYSQL.$fichier_mysql_config.'.php';
	$fichier_class_config = './_inc/'.$fichier_class_config.'.php';
	// Chargement du fichier de connexion à la BDD
	if(is_file($fichier_mysql_config))
	{
		require_once($fichier_mysql_config);
		require_once($fichier_class_config);
	}
	elseif($PAGE!='public_installation')
	{
		affich_message_exit($titre='Paramètres BDD manquants',$contenu='Paramètres de connexion à la base de données manquants.',$lien='<a href="./index.php?page=public_installation">Procédure d\'installation de SACoche.</a>');
	}
}

// Authentification requise par SSO
if(defined('LOGIN_SSO'))
{
	require('./pages/public_login_SSO.php');
}

ob_start();
// Chargement de la page concernée
$filename_php = './pages/'.$PAGE.'.php';
if(!is_file($filename_php))
{
	$tab_messages_erreur[] = 'Erreur : page "'.$filename_php.'" manquante (supprimée, déplacée, non créée...).';
	$PAGE = ($_SESSION['USER_PROFIL']=='public') ? 'public_accueil' :'compte_accueil' ;
	$filename_php = './pages/'.$PAGE.'.php';
}
require($filename_php);
// Affichage dans une variable
$CONTENU_PAGE = ob_get_contents();
ob_end_clean();

// Chargement du js associé de la page
$filename_js_normal = './pages/'.$PAGE.'.js';
$SCRIPT = (is_file($filename_js_normal)) ? '<script type="text/javascript" charset="utf-8" src="'.compacter($filename_js_normal,$VERSION_JS_FILE,'pack').'"></script>' : '' ;

// Titre du navigateur
$TITRE_NAVIGATEUR = 'SACoche » Espace '.$_SESSION['USER_PROFIL'].' » ';
$TITRE_NAVIGATEUR.= ($TITRE) ? $TITRE : 'Evaluer par comptétences et valider le socle commun' ;

// Affichage de l'en-tête
entete();
?>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="description" content="SACoche - Suivi d'Acquisition de Compétences - Evaluer par compétences - Valider le socle commun" />
	<meta name="keywords" content="SACoche Sésamath évaluer évaluation compétences compétence validation valider socle commun collège points Lomer" />
	<meta name="author" content="Thomas Crespin pour Sésamath" />
	<meta name="robots" content="index,follow" />
	<link rel="shortcut icon" type="images/x-icon" href="./favicon.ico" />
	<link rel="icon" type="image/png" href="./favicon.png" />
	<link rel="stylesheet" type="text/css" href="<?php echo compacter('./_css/style.css',VERSION_CSS_SCREEN,'mini') ?>" />
	<!--[if lte IE 8]><link rel="stylesheet" type="text/css" href="<?php echo compacter('./_css/style_IE.css',VERSION_CSS_SCREEN,'mini') ?>" /><![endif]-->
	<?php if(isset($_SESSION['CSS'])){echo'<style type="text/css">'.$_SESSION['CSS'].'</style>';} ?>
	<script type="text/javascript" charset="utf-8" src="<?php echo compacter('./_js/jquery-librairies.js',VERSION_JS_BIBLIO,'mini') ?>"></script>
	<script type="text/javascript" charset="utf-8" src="<?php echo compacter('./_js/script.js',VERSION_JS_GLOBAL,'mini') ?>"></script>
	<title><?php echo $TITRE_NAVIGATEUR ?></title>
</head>
<body>
	<?php 
	if($_SESSION['USER_PROFIL']!='public')
	{
		// Espace identifié : cadre_haut (avec le menu) et cadre_bas (avec le contenu).
		echo'<div id="cadre_haut">'."\r\n";
		echo'	<div id="info">'."\r\n";
		echo'		<span class="button"><img alt="site officiel" src="./_img/favicon.gif" /> <a class="lien_ext" href="'.SERVEUR_PROJET.'">Site officiel</a></span>'."\r\n";
		if(SERVEUR_TYPE!='PROD')
		{
			$protocole  = ( isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS']=='on') ) ? 'https://' : 'http://' ;
			$url_page   = $protocole.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
			$separateur = (strpos($url_page,'?')) ? '&' : '?' ;
			$get_debug  = DEBUG ? 'debug=0'  : 'debug=1' ;
			$txt_debug  = DEBUG ? 'ON' : 'OFF' ;
			echo'		<span class="button"><a href="'.html($url_page.$separateur.$get_debug).'"><img alt="debug" src="./_img/firephp.png" /> '.$txt_debug.'</a></span>'."\r\n";
		}
		echo'		<span class="button"><img alt="structure" src="./_img/home.png" /> '.html($_SESSION['DENOMINATION']).'</span>'."\r\n";
		echo'		<span class="button"><img alt="'.$_SESSION['USER_PROFIL'].'" src="./_img/menu/profil_'.$_SESSION['USER_PROFIL'].'.png" /> '.html($_SESSION['USER_PRENOM'].' '.$_SESSION['USER_NOM']).' ('.$_SESSION['USER_PROFIL'].')</span>'."\r\n";
		echo'		<span class="button"><span id="clock"><img alt="" src="./_img/clock_fixe.png" /> '.$_SESSION['DUREE_INACTIVITE'].' min</span><img alt="" src="./_img/point.gif" /></span>'."\r\n";
		echo'		<button id="deconnecter"><img alt="" src="./_img/bouton/deconnecter.png" /> Déconnexion</button>'."\r\n";
		echo'	</div>'."\r\n";
		echo'	<img id="logo" alt="SACoche" src="./_img/logo_petit2.png" />'."\r\n";
		$fichier_menu = ($_SESSION['USER_PROFIL']!='webmestre') ? '__menu_'.$_SESSION['USER_PROFIL'] : '__menu_'.$_SESSION['USER_PROFIL'].'_'.HEBERGEUR_INSTALLATION ;
		require_once('./pages/'.$fichier_menu.'.html'); // Le menu '<ul id="menu">...</ul>
		echo'</div>'."\r\n";
		echo'<div id="cadre_bas">'."\r\n";
		echo'	<h1>» '.$TITRE.'</h1>';
		if(count($tab_messages_erreur))
		{
			echo'<hr /><div class="danger o">'.implode('</div><div class="danger o">',$tab_messages_erreur).'</div><hr />';
		}
		echo 	$CONTENU_PAGE;
		echo'</div>'."\r\n";
	}
	else
	{
		// Accueil (identification ou procédure d'installation) : cadre unique (avec image SACoche & image hébergeur).
		echo'<div id="cadre_milieu">'."\r\n";
		$hebergeur_img  = ( (defined('HEBERGEUR_LOGO')) && (is_file('./__tmp/logo/'.HEBERGEUR_LOGO)) ) ? '<img alt="Hébergeur" src="./__tmp/logo/'.HEBERGEUR_LOGO.'" />' : '' ;
		$hebergeur_lien = ( (defined('HEBERGEUR_ADRESSE_SITE')) && HEBERGEUR_ADRESSE_SITE && ($hebergeur_img) ) ? '<a href="'.html(HEBERGEUR_ADRESSE_SITE).'">'.$hebergeur_img.'</a>' : $hebergeur_img ;
		$SACoche_lien   = '<a href="'.SERVEUR_PROJET.'"><img alt="Suivi d\'Acquisition de Compétences" src="./_img/logo_grand.gif" /></a>' ;
		echo ($PAGE=='public_accueil') ? '<h1 class="logo">'.$SACoche_lien.$hebergeur_lien.'</h1>' : '<h1>» '.$TITRE.'</h1>' ;
		echo 	$CONTENU_PAGE;
		echo'</div>'."\r\n";
	}
	?>
	<script type="text/javascript">
		var PAGE='<?php echo $PAGE ?>';
		var DUREE_AUTORISEE='<?php echo $_SESSION['DUREE_INACTIVITE'] ?>';
		var DUREE_AFFICHEE='<?php echo $_SESSION['DUREE_INACTIVITE'] ?>';
		var CONNEXION_USED='<?php echo (isset($_COOKIE[COOKIE_AUTHMODE])) ? $_COOKIE[COOKIE_AUTHMODE] : 'normal' ; ?>';
	</script>
	<?php echo $SCRIPT; ?>
	<!-- Objet flash pour lire un fichier audio grace au génial lecteur de neolao http://flash-mp3-player.net/ -->
	<h6><object class="playerpreview" id="myFlash" type="application/x-shockwave-flash" data="./_mp3/player_mp3_js.swf" height="1" width="1">
		<param name="movie" value="./_mp3/player_mp3_js.swf" />
		<param name="AllowScriptAccess" value="always" />
		<param name="FlashVars" value="listener=myListener&amp;interval=500" />
	</object></h6>
</body>
</html>
