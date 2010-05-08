<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010
 * 
 * ****************************************************************************************************
 * SACoche <http://competences.sesamath.net> - Suivi d'Acquisitions de Compétences
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

// Fichier appelé pour l'affichage de chaque page.
// Passage en GET des paramètres pour savoir quelle page charger.

// Atteste l'appel de cette page avant l'inclusion d'une autre
define('SACoche','index');

// Constantes / Fonctions de redirections / Configuration serveur
require_once('./_inc/constantes.php');
require_once('./_inc/fonction_redirection.php');
require_once('./_inc/config_serveur.php');

// Paramètres transmis
$DOSSIER = (isset($_GET['dossier'])) ? $_GET['dossier'] : 'public';
$FICHIER = (isset($_GET['fichier'])) ? $_GET['fichier'] : ( ($DOSSIER=='public') ? 'accueil' : 'compte_accueil' ) ;
$SECTION = (isset($_GET['section'])) ? $_GET['section'] : '';

// Fonctions
require_once('./_inc/fonction_clean.php');
require_once('./_inc/fonction_sessions.php');
require_once('./_inc/fonction_divers.php');
require_once('./_inc/fonction_requetes.php');
require_once('./_inc/fonction_formulaires_select.php');
require_once('./_inc/fonction_affichage.php');

// Ouverture de la session et gestion des droits d'accès
$PROFIL_REQUIS = $DOSSIER;
require_once('./_inc/gestion_sessions.php');

// Blocage des sites si maintenance
require_once('./_inc/gestion_maintenance.php');

// Informations sur l'hébergement
$fichier_constantes = './__hebergeur_info/constantes.php';
if(is_file($fichier_constantes))
{
	require_once($fichier_constantes);
	// Classe de connexion aux BDD
	require_once('./_inc/class.DB.php');
	// Choix des paramètres de connexion à la base de données adaptée...
	// ...multi-structure ; base sacoche_structure_***
	if( (in_array($_SESSION['USER_PROFIL'],array('administrateur','directeur','professeur','eleve'))) && (HEBERGEUR_INSTALLATION=='multi-structures') )
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
	$fichier_mysql_config = './__mysql_config/'.$fichier_mysql_config.'.php';
	$fichier_class_config = './_inc/'.$fichier_class_config.'.php';
	// Chargement du fichier de connexion à la BDD
	if(is_file($fichier_mysql_config))
	{
		require_once($fichier_mysql_config);
		require_once($fichier_class_config);
	}
	elseif($FICHIER!='installation')
	{
		affich_message_exit($titre='Paramètres BDD manquants',$contenu='Paramètres de connexion à la base de données manquants.<br /><a href="./index.php?fichier=installation">Procédure d\'installation du site SACoche.</a>');
	}
}
elseif($FICHIER!='installation')
{
	affich_message_exit($titre='Informations hébergement manquantes',$contenu='Informations concernant l\'hébergeur manquantes.<br /><a href="./index.php?fichier=installation">Procédure d\'installation du site SACoche.</a>');
}

ob_start();
// Chargement du menu concerné
$filename_php = './pages_'.$DOSSIER.'/_menu.php';
if(is_file($filename_php))
{
	include($filename_php);
}
// Chargement de la page concernée
$filename_php = './pages_'.$DOSSIER.'/'.$FICHIER.'.php';
if(!is_file($filename_php))
{
	echo'<p class="danger">Page "'.$filename_php.'" manquante (supprimée, déplacée, non créée...).</p>';
	$FICHIER = 'accueil';
	$filename_php = './pages_'.$DOSSIER.'/'.$FICHIER.'.php';
	if(!is_file($filename_php))
	{
		$DOSSIER = 'public';
		$filename_php = './pages_'.$DOSSIER.'/'.$FICHIER.'.php';
	}
}
include($filename_php);
// Affichage dans une variable
$CONTENU_PAGE = ob_get_contents();
ob_end_clean();

// Chargement du js associé de la page
$filename_js_normal = './pages_'.$DOSSIER.'/'.$FICHIER.$VERSION_JS.'.js';
$filename_js_packed = './pages_'.$DOSSIER.'/'.$FICHIER.$VERSION_JS.'.js.pack';
if(is_file($filename_js_normal))
{
	if(SERVEUR_TYPE == 'PROD')
	{
		// Sur le serveur en production, on compresse le js s'il ne l'est pas
		if( (!is_file($filename_js_packed)) || (filemtime($filename_js_packed)<filemtime($filename_js_normal)) )
		{
			require_once('./_inc/class.JavaScriptPacker.php'); // Attention, il faut lui envoyer de l'iso et pas de l'utf8.
			$js_normal = file_get_contents($filename_js_normal);
			$myPacker = new JavaScriptPacker(utf8_decode($js_normal), 62, true, false);
			$js_packed = $myPacker->pack();
			file_put_contents($filename_js_packed,utf8_encode($js_packed));
		}
		$SCRIPT = '<script type="text/javascript" charset="utf-8" src="'.$filename_js_packed.'"></script>';
	}
	else
	{
		// Sur le serveur local, on travaille avec le js normal pour le debugguer si besoin
		$SCRIPT = '<script type="text/javascript" charset="utf-8" src="'.$filename_js_normal.'"></script>';
	}
}
else
{
	$SCRIPT = '';
}

// Titre du navigateur
$TITRE_NAVIGATEUR = 'SACoche - Espace '.$DOSSIER.' - ';
$TITRE_NAVIGATEUR.= ($TITRE) ? $TITRE : 'Suivi d\'Acquisition de Compétences' ;

// Titre de la page
if($DOSSIER!='public')
{
	// Espace identifié : titre indiqué et logo encadré en image de fond.
	$TITRE_PAGE = '<h1 id="titre_page">'.$TITRE.'</h1>';
}
else
{
	// Accueil (identification) : image SACoche + image hébergeur.
	$hebergeur_img  = ( (defined('HEBERGEUR_LOGO')) && (is_file('./__hebergeur_info/'.HEBERGEUR_LOGO)) ) ? '<img alt="Hébergeur" src="./__hebergeur_info/'.HEBERGEUR_LOGO.'" />' : '' ;
	$hebergeur_lien = ( (defined('HEBERGEUR_ADRESSE_SITE')) && HEBERGEUR_ADRESSE_SITE && ($hebergeur_img) ) ? '<a href="'.html(HEBERGEUR_ADRESSE_SITE).'">'.$hebergeur_img.'</a>' : $hebergeur_img ;
	$SACoche_lien   = '<a href="http://competences.sesamath.net"><img alt="Suivi d\'Acquisition de Compétences" src="./_img/logo_grand2.gif" /></a>' ;
	$TITRE_PAGE = '<h1>'.$SACoche_lien.$hebergeur_lien.'</h1>';
}

// Affichage de l'en-tête
entete();
?>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="shortcut icon" type="images/x-icon" href="./favicon.ico" />
	<link rel="icon" type="image/png" href="./favicon.png" />
	<link rel="stylesheet" type="text/css" href="./_css/<?php echo FILE_CSS_SCREEN ?>" />
	<link rel="stylesheet" type="text/css" href="./_css/<?php echo FILE_CSS_PRINT ?>" media="print" />
	<link rel="alternate" type="application/rss+xml" href="./_rss/rss.xml" title="SACoche" />
	<script type="text/javascript" charset="utf-8" src="./_js/<?php echo FILE_JS_BIBLIO ?>"></script>
	<script type="text/javascript" charset="utf-8" src="./_js/<?php echo FILE_JS_SCRIPT ?>"></script>
	<title><?php echo $TITRE_NAVIGATEUR ?></title>
</head>
<body>
	<?php echo $TITRE_PAGE; ?>
	<?php echo $CONTENU_PAGE; ?>
	<?php 
	// echo'<pre>';var_dump($_SESSION);echo'</pre>';
	?>
	<script type="text/javascript">
		var DOSSIER='<?php echo $DOSSIER ?>';
		var FICHIER='<?php echo $FICHIER ?>';
		var DUREE_AUTORISEE='<?php echo $_SESSION['DUREE_INACTIVITE'] ?>';
		var DUREE_RESTANTE='<?php echo $_SESSION['DUREE_INACTIVITE'] ?>';
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
