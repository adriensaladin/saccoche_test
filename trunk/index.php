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
 * Fichier appelé pour l'affichage de chaque page.
 * Passage en GET des paramètres pour savoir quelle page charger.
 * 
 */

// Atteste l'appel de cette page avant l'inclusion d'une autre
define('SACoche','index');

// Fonctions de redirections et Configuration serveur
require_once('./_inc/fonction_redirection.php');
require_once('./_inc/config_serveur.php');

// Paramètres transmis
$DOSSIER = (isset($_GET['dossier'])) ? $_GET['dossier'] : 'public';
$FICHIER = (isset($_GET['fichier'])) ? $_GET['fichier'] : 'accueil';
$SECTION = (isset($_GET['section'])) ? $_GET['section'] : '';
$PREFIXE = ($DOSSIER!='webmestre') ? '' : '__' ;

// Connexion base de données SACoche
if($FICHIER!='installation')
{
	$filename_php = './__mysql_config/serveur_sacoche_'.SERVEUR_TYPE.'.php';
	if(is_file($filename_php))
	{
		include_once($filename_php);
		require_once('./_inc/class.DB.config.sacoche.php');
	}
	else
	{
		affich_message_exit($titre='Paramètres BDD manquants',$contenu='Paramètres de connexion à la base de données manquants.<br /><a href="./index.php?fichier=installation">Procédure d\'installation du site SACoche.</a>');
	}
}

// Connexion base de données Sésamath2 (serveur Sésamath uniquement)
$filename_php = './__mysql_config/serveur_sesamath2_'.SERVEUR_TYPE.'.php';
if(is_file($filename_php))
{
	include_once($filename_php);
	require_once('./_inc/class.DB.config.sesamath2.php');
}
require_once('./_inc/class.DB.php');

// Fonctions
require_once('./_inc/fonction_clean.php');
require_once('./_inc/fonction_sessions.php');
require_once('./_inc/fonction_requetes_administration.php');
require_once('./_inc/fonction_requetes_formulaires_select.php');
require_once('./_inc/fonction_requetes_referentiel.php');
require_once('./_inc/fonction_requetes_gestion.php');
require_once('./_inc/fonction_affichage.php');

// Ouverture de la session et gestion des droits d'accès
$PROFIL_REQUIS = $DOSSIER;
require_once('./_inc/gestion_sessions.php');

// Blocage des sites si maintenance
require_once('./_inc/gestion_maintenance.php');

ob_start();
// Chargement du menu concerné
$filename_php = './'.$PREFIXE.'pages_'.$DOSSIER.'/_menu.php';
if(is_file($filename_php))
{
	include($filename_php);
}
// Chargement de la page concernée
$filename_php = './'.$PREFIXE.'pages_'.$DOSSIER.'/'.$FICHIER.'.php';
if(!is_file($filename_php))
{
	echo'<p class="danger">Page "'.$filename_php.'" manquante (supprimée, déplacée, non créée...).</p>';
	$FICHIER = 'accueil';
	$filename_php = './'.$PREFIXE.'pages_'.$DOSSIER.'/'.$FICHIER.'.php';
	if(!is_file($filename_php))
	{
		$DOSSIER = 'public';
		$filename_php = './'.$PREFIXE.'pages_'.$DOSSIER.'/'.$FICHIER.'.php';
	}
}
include($filename_php);
// Affichage dans une variable
$CONTENU_PAGE = ob_get_contents();
ob_end_clean();

// Chargement du js associé de la page
$filename_js_normal = './'.$PREFIXE.'pages_'.$DOSSIER.'/'.$FICHIER.$VERSION_JS.'.js';
$filename_js_packed = './'.$PREFIXE.'pages_'.$DOSSIER.'/'.$FICHIER.$VERSION_JS.'.js.pack';
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

// Affichage de l'en-tête
entete();
?>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="shortcut icon" type="images/x-icon" href="./favicon.ico" />
	<link rel="icon" type="image/png" href="./favicon.png" />
	<link rel="stylesheet" type="text/css" href="./_css/<?php echo FILE_CSS ?>" />
	<link rel="stylesheet" type="text/css" href="./_css/style_print.css" media="print" />
	<link rel="alternate" type="application/rss+xml" href="./_rss/rss.xml" title="SACoche" />
	<script type="text/javascript" charset="utf-8" src="./_jquery/jquery14.min.js"></script>
	<script type="text/javascript" charset="utf-8" src="./_jquery/jquery.form.js"></script>
	<script type="text/javascript" charset="utf-8" src="./_jquery/jquery.validate.pack.js"></script>
	<script type="text/javascript" charset="utf-8" src="./_jquery/jquery.tablesorter.min.js"></script>
	<script type="text/javascript" charset="utf-8" src="./_jquery/jquery.tooltip.pack.js"></script>
	<script type="text/javascript" charset="utf-8" src="./_jquery/jquery.timers.js"></script>
	<script type="text/javascript" charset="utf-8" src="./_jquery/jquery.ajaxupload.js"></script>
	<!-- <script type="text/javascript" charset="utf-8" src="./_jquery/jquery.livequery.js"></script> Inutile depuis le passage à jquery 1.4 dont live gère mouseleave -->
	<script type="text/javascript" charset="utf-8" src="./_jquery/<?php echo FILE_JS ?>"></script>
	<title>SACoche - Espace <?php echo $DOSSIER ?> - <?php echo ($TITRE) ? $TITRE : 'Suivi d\'Acquisition de Compétences' ; ?></title>
</head>
<body>
	<h1 id="titre_page"><?php echo ($TITRE) ? $TITRE : '<img alt="Suivi d\'Acquisition de Compétences" src="./_img/logo_grand2.gif" />' ; ?></h1>
	<?php echo $CONTENU_PAGE;
	// var_dump($_SESSION);
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
