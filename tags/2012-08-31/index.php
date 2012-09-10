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

// Constantes / Configuration serveur / Autoload classes / Fonction de sortie
require('./_inc/_loader.php');

// Page et section appelées ; normalement transmis en $_GET mais $_POST possibles depuis GEPI
    if(isset($_GET['page']))  { $PAGE = $_GET['page']; }
elseif(isset($_POST['page'])) { $PAGE = $_POST['page']; }
elseif(isset($_GET['sso']))   { $PAGE = 'compte_accueil'; }
else                          { $PAGE = 'public_accueil'; }
    if(isset($_GET['section']))  { $SECTION = $_GET['section']; }
elseif(isset($_POST['section'])) { $SECTION = $_POST['section']; }
else                             { $SECTION = ''; }

// Fichier d'informations sur l'hébergement (requis avant la gestion de la session).
if(is_file(CHEMIN_FICHIER_CONFIG_INSTALL))
{
	require(CHEMIN_FICHIER_CONFIG_INSTALL);
}
elseif($PAGE!='public_installation')
{
	exit_error( 'Informations hébergement manquantes' /*titre*/ , 'Les informations relatives à l\'hébergeur n\'ont pas été trouvées.' /*contenu*/ , TRUE /*setup*/ );
}

// Le fait de lister les droits d'accès de chaque page empêche de surcroit l'exploitation d'une vulnérabilité "include PHP" (http://www.certa.ssi.gouv.fr/site/CERTA-2003-ALE-003/).
require(CHEMIN_DOSSIER_INCLUDE.'tableau_droits.php');
if(!isset($tab_droits[$PAGE]))
{
	$tab_messages_erreur[] = 'Erreur : droits de la page "'.$PAGE.'" manquants.';
	$PAGE = (substr($PAGE,0,6)=='public') ? 'public_accueil' : 'compte_accueil' ;
}

// Ouverture de la session et gestion des droits d'accès
Session::execute($tab_droits[$PAGE]);

// Pour le devel
if (DEBUG) afficher_infos_debug();

// Blocage éventuel par le webmestre ou un administrateur ou l'automate (on ne peut pas le tester avant car il faut avoir récupéré les données de session)
LockAcces::stopper_si_blocage( $_SESSION['BASE'] , FALSE /*demande_connexion_profil*/ );

// Autres fonctions à charger
require(CHEMIN_DOSSIER_INCLUDE.'fonction_divers.php');

// Patch fichier de config
if(is_file(CHEMIN_FICHIER_CONFIG_INSTALL))
{
	// DEBUT PATCH CONFIG 1
	// A compter du 05/12/2010, ajout de paramètres dans le fichier de constantes pour paramétrer cURL. [TODO] peut être retiré dans un an environ
	if(!defined('SERVEUR_PROXY_USED'))
	{
		fabriquer_fichier_hebergeur_info( array('SERVEUR_PROXY_USED'=>'','SERVEUR_PROXY_NAME'=>'','SERVEUR_PROXY_PORT'=>'','SERVEUR_PROXY_TYPE'=>'','SERVEUR_PROXY_AUTH_USED'=>'','SERVEUR_PROXY_AUTH_METHOD'=>'','SERVEUR_PROXY_AUTH_USER'=>'','SERVEUR_PROXY_AUTH_PASS'=>'') );
	}
	// FIN PATCH CONFIG 1
	// DEBUT PATCH CONFIG 2
	// A compter du 26/05/2011, ajout de paramètres dans le fichier de constantes pour les dates CNIL. [TODO] peut être retiré dans un an environ
	if(!defined('CNIL_NUMERO'))
	{
		fabriquer_fichier_hebergeur_info( array('CNIL_NUMERO'=>HEBERGEUR_CNIL,'CNIL_DATE_ENGAGEMENT'=>'','CNIL_DATE_RECEPISSE'=>'') );
	}
	// FIN PATCH CONFIG 2
	// DEBUT PATCH CONFIG 3
	// A compter du 14/03/2012, ajout de paramètres dans le fichier de constantes pour les fichiers associés aux devoirs. [TODO] peut être retiré dans un an environ
	if(!defined('FICHIER_DUREE_CONSERVATION'))
	{
		fabriquer_fichier_hebergeur_info( array('FICHIER_TAILLE_MAX'=>500,'FICHIER_DUREE_CONSERVATION'=>12) );
	}
	// FIN PATCH CONFIG 3
}

// Interface de connexion à la base, chargement et config (test sur CHEMIN_FICHIER_CONFIG_INSTALL car à éviter si procédure d'installation non terminée).
if(is_file(CHEMIN_FICHIER_CONFIG_INSTALL))
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
		exit_error( 'Configuration anormale' /*titre*/ , 'Une anomalie dans les données d\'hébergement et/ou de session empêche l\'application de se poursuivre.' /*contenu*/ );
	}
	// Chargement du fichier de connexion à la BDD
	define('CHEMIN_FICHIER_CONFIG_MYSQL',CHEMIN_DOSSIER_MYSQL.$fichier_mysql_config.'.php');
	if(is_file(CHEMIN_FICHIER_CONFIG_MYSQL))
	{
		require(CHEMIN_FICHIER_CONFIG_MYSQL);
		require(CHEMIN_DOSSIER_INCLUDE.$fichier_class_config.'.php');
	}
	elseif($PAGE!='public_installation')
	{
		exit_error( 'Paramètres BDD manquants' /*titre*/ , 'Les paramètres de connexion à la base de données n\'ont pas été trouvés.' /*contenu*/ , TRUE /*setup*/ );
	}
}

// Authentification requise par SSO
if(Session::$_sso_redirect)
{
	require(CHEMIN_DOSSIER_PAGES.'public_login_SSO.php');
}

ob_start();
// Chargement de la page concernée
$filename_php = CHEMIN_DOSSIER_PAGES.$PAGE.'.php';
if(!is_file($filename_php))
{
	$tab_messages_erreur[] = 'Erreur : page "'.$filename_php.'" manquante (supprimée, déplacée, non créée...).';
	$PAGE = ($_SESSION['USER_PROFIL']=='public') ? 'public_accueil' :'compte_accueil' ;
	$filename_php = CHEMIN_DOSSIER_PAGES.$PAGE.'.php';
}
require($filename_php);
// Affichage dans une variable
$CONTENU_PAGE = ob_get_contents();
ob_end_clean();

// Titre du navigateur
$TITRE_NAVIGATEUR = 'SACoche » Espace '.$_SESSION['USER_PROFIL'].' » ';
$TITRE_NAVIGATEUR.= ($TITRE) ? $TITRE : 'Evaluer par comptétences et valider le socle commun' ;

// Css personnalisé
$CSS_PERSO = (isset($_SESSION['CSS'])) ? '<style type="text/css">'.$_SESSION['CSS'].'</style>' : NULL ;

// Fichiers à inclure
$filename_js_normal = './pages/'.$PAGE.'.js';
$tab_fichiers_head = array();
$tab_fichiers_head[] = array( 'css' , compacter('./_css/style.css','mini') );
$tab_fichiers_head[] = array( 'js'  , compacter('./_js/jquery-librairies.js','mini') );
$tab_fichiers_head[] = array( 'js'  , compacter('./_js/script.js','pack') ); // la minification plante à sur le contenu de testURL() avec le message Fatal error: Uncaught exception 'JSMinException' with message 'Unterminated string literal.'
if($PAGE=='officiel_accueil')    $tab_fichiers_head[] = array( 'js'  , compacter('./_js/highcharts.js','mini') );
if(is_file($filename_js_normal)) $tab_fichiers_head[] = array( 'js' , compacter($filename_js_normal,'pack') );

// Affichage de l'en-tête
declaration_entete( TRUE /*is_meta_robots*/ , TRUE /*is_favicon*/ , TRUE /*is_rss*/ , $tab_fichiers_head , $TITRE_NAVIGATEUR , $CSS_PERSO );
?>
<body>
	<?php 
	if($_SESSION['USER_PROFIL']!='public')
	{
		// Espace identifié : cadre_haut (avec le menu) et cadre_bas (avec le contenu).
		echo'<div id="cadre_haut">'."\r\n";
		echo'	<img id="logo" alt="SACoche" src="./_img/logo_petit2.png" width="147" height="46" />'."\r\n";
		echo'	<div id="top_info">'."\r\n";
		echo'		<span class="button favicon"><a class="lien_ext" href="'.SERVEUR_PROJET.'">Site officiel</a></span>'."\r\n";
		if(SERVEUR_TYPE!='PROD')
		{
			$protocole  = ( isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS']=='on') ) ? 'https://' : 'http://' ;
			$url_page   = $protocole.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
			$separateur = (strpos($url_page,'?')) ? '&' : '?' ;
			$span_class = DEBUG ? 'firephp' : 'firebug' ;
			$get_debug  = DEBUG ? 'debug=0' : 'debug=1' ;
			$txt_debug  = DEBUG ? 'on&rarr;off' : 'off&rarr;on' ;
			echo'		<span class="button '.$span_class.'"><a href="'.html($url_page.$separateur.$get_debug).'">'.$txt_debug.'</a></span>'."\r\n";
		}
		echo'		<span class="button home">'.html($_SESSION['ETABLISSEMENT']['DENOMINATION']).'</span>'."\r\n";
		echo'		<span class="button profil_'.$_SESSION['USER_PROFIL'].'">'.html($_SESSION['USER_PRENOM'].' '.$_SESSION['USER_NOM']).' ('.$_SESSION['USER_PROFIL'].')</span>'."\r\n";
		echo'		<span class="button clock_fixe"><span id="clock">'.$_SESSION['DUREE_INACTIVITE'].' min</span></span>'."\r\n";
		echo'		<button id="deconnecter" class="deconnecter">Déconnexion</button>'."\r\n";
		echo'	</div>'."\r\n";
		// Le menu '<ul id="menu">...</ul>
		if($_SESSION['USER_PROFIL']=='webmestre')
		{
			require(CHEMIN_DOSSIER_PAGES.'__menu_'.$_SESSION['USER_PROFIL'].'_'.HEBERGEUR_INSTALLATION.'.html');
		}
		else
		{
			$contenu_menu = file_get_contents(CHEMIN_DOSSIER_PAGES.'__menu_'.$_SESSION['USER_PROFIL'].'.html');
			// La présence de certains éléments du menu dépend des choix de l'établissement
			if( ($_SESSION['USER_PROFIL']=='professeur') || ($_SESSION['USER_PROFIL']=='directeur') )
			{
				$tab_paliers_actifs = explode(',',$_SESSION['LISTE_PALIERS_ACTIFS']);
				for( $palier_id=1 ; $palier_id<4 ; $palier_id++ )
				{
					if(!in_array($palier_id,$tab_paliers_actifs))
					{
						$tab_bad = array(      '<li><a class="officiel_palier'.$palier_id.'"' , 'palier '.$palier_id.'</a></li>'     );
						$tab_bon = array( '<!-- <li><a class="officiel_palier'.$palier_id.'"' , 'palier '.$palier_id.'</a></li> -->' );
						$contenu_menu = str_replace( $tab_bad , $tab_bon , $contenu_menu );
					}
				}
			}
			echo $contenu_menu;
		}
		
		echo'</div>'."\r\n";
		echo'<div id="cadre_navig"><a id="go_haut" href="#cadre_haut" title="Haut de page"></a><a id="go_bas" href="#ancre_bas" title="Bas de page"></a></div>'."\r\n";
		echo'<div id="cadre_bas">'."\r\n";
		echo'	<h1>» '.$TITRE.'</h1>';
	}
	else
	{
		// Accueil (identification ou procédure d'installation) : cadre unique (avec image SACoche & image hébergeur).
		echo'<div id="cadre_milieu">'."\r\n";
		if($PAGE=='public_accueil')
		{
			$tab_image_infos = ( (defined('HEBERGEUR_LOGO')) && (is_file(CHEMIN_DOSSIER_LOGO.HEBERGEUR_LOGO)) ) ? getimagesize(CHEMIN_DOSSIER_LOGO.HEBERGEUR_LOGO) : array() ;
			$hebergeur_img   = count($tab_image_infos) ? '<img alt="Hébergeur" src="'.URL_DIR_LOGO.HEBERGEUR_LOGO.'" '.$tab_image_infos[3].' />' : '' ;
			$hebergeur_lien  = ( (defined('HEBERGEUR_ADRESSE_SITE')) && HEBERGEUR_ADRESSE_SITE && ($hebergeur_img) ) ? '<a href="'.html(HEBERGEUR_ADRESSE_SITE).'">'.$hebergeur_img.'</a>' : $hebergeur_img ;
			$SACoche_lien    = '<a href="'.SERVEUR_PROJET.'"><img alt="Suivi d\'Acquisition de Compétences" src="./_img/logo_grand.gif" width="208" height="71" /></a>' ;
			echo'<h1 class="logo">'.$SACoche_lien.$hebergeur_lien.'</h1>';
		}
		else
		{
			echo'<h1>» '.$TITRE.'</h1>';
		}
	}
	if(count($tab_messages_erreur))
	{
		echo'<hr /><div class="danger o">'.implode('</div><div class="danger o">',$tab_messages_erreur).'</div>';
	}
	echo $CONTENU_PAGE;
	echo'<span id="ancre_bas"></span></div>'."\r\n";
	?>
	<script type="text/javascript">
		var PAGE='<?php echo $PAGE ?>';
		var DUREE_AUTORISEE='<?php echo $_SESSION['DUREE_INACTIVITE'] ?>';
		var DUREE_AFFICHEE='<?php echo $_SESSION['DUREE_INACTIVITE'] ?>';
		var CONNEXION_USED='<?php echo (isset($_COOKIE[COOKIE_AUTHMODE])) ? $_COOKIE[COOKIE_AUTHMODE] : 'normal' ; ?>';
	</script>
	<!-- Objet flash pour lire un fichier audio grace au génial lecteur de neolao http://flash-mp3-player.net/ -->
	<h6><object class="playerpreview" id="myFlash" type="application/x-shockwave-flash" data="./_mp3/player_mp3_js.swf" height="1" width="1">
		<param name="movie" value="./_mp3/player_mp3_js.swf" />
		<param name="AllowScriptAccess" value="always" />
		<param name="FlashVars" value="listener=myListener&amp;interval=500" />
	</object></h6>
</body>
</html>
