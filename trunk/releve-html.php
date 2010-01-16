<?php
/**
 * @version $Id: releve-html.php 7 2009-10-30 20:50:17Z thomas $
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2009
 * 
 * ****************************************************************************************************
 * SACoche [http://competences.sesamath.net] - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath [http://www.sesamath.net]
 * Distribution sous licence libre prévue pour l'été 2010.
 * ****************************************************************************************************
 * 
 * Fichier appelé pour l'affichage d'un relevé HTML enregistré temporairement.
 * Passage en GET d'un paramètre pour savoir quelle page charger.
 * 
 */

// Atteste l'appel de cette page avant l'inclusion d'une autre
define('SACoche','releve');

// Fonctions de redirections et Configuration serveur
require_once('./_inc/fonction_redirection.php');
require_once('./_inc/config_serveur.php');

// Paramètres transmis
$FICHIER = (isset($_GET['fichier'])) ? $_GET['fichier'] : '';

// Fonctions
require_once('./_inc/fonction_affichage.php');

ob_start();
// Chargement de la page concernée
$filename_html = './__tmp/export/'.$FICHIER.'.html';
if(is_file($filename_html))
{
	include($filename_html);
}
else
{
	echo'<h2>Relevé manquant</h2>';
	echo'Les relevés sont conservés sur le serveur pendant une durée limitée...';
}
// Affichage dans une variable
$CONTENU_PAGE = ob_get_contents();
ob_end_clean();

// Affichage de l'en-tête
entete();
?>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="shortcut icon" type="images/x-icon" href="./favicon.ico" />
	<link rel="icon" type="image/png" href="./favicon.png" />
	<link rel="stylesheet" type="text/css" href="./_css/<?php echo FILE_CSS ?>" />
	<link rel="stylesheet" type="text/css" href="./_css/style_print.css" media="print" />
	<script type="text/javascript" charset="utf-8" src="./_jquery/jquery-min.js"></script>
	<script type="text/javascript" charset="utf-8" src="./_jquery/jquery.tablesorter.min.js"></script>
	<script type="text/javascript" charset="utf-8" src="./_jquery/jquery.tooltip.pack.js"></script>
	<script type="text/javascript" charset="utf-8" src="./_jquery/<?php echo FILE_JS ?>"></script>
	<title>SACoche - Relevé HTML</title>
</head>
<body>
	<?php echo $CONTENU_PAGE; ?>
	<script type="text/javascript">
		var DOSSIER='public';
	</script>
</body>
</html>
