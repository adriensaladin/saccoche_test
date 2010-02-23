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

// Fichier appelé pour l'affichage d'une aide en ligne.
// Passage en GET d'un paramètre pour savoir quelle page charger.

// Atteste l'appel de cette page avant l'inclusion d'une autre
define('SACoche','aide');

// Fonctions de redirections et Configuration serveur
require_once('./_inc/fonction_redirection.php');
require_once('./_inc/config_serveur.php');

// Paramètres transmis
$FICHIER = (isset($_GET['fichier'])) ? $_GET['fichier'] : 'accueil';

// Fonctions
require_once('./_inc/fonction_affichage.php');

ob_start();
// Chargement de la page concernée
$filename_php = './aide/'.$FICHIER.'.php';
if(is_file($filename_php))
{
	include($filename_php);
}
else
{
	$TITRE = 'Site en construction...';
	echo'Page "'.$filename_php.'" manquante.';
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
	<script type="text/javascript" charset="utf-8" src="./_jquery/jquery14.min.js"></script>
	<script type="text/javascript" charset="utf-8" src="./_jquery/jquery.tooltip.pack.js"></script>
	<script type="text/javascript" charset="utf-8" src="./_jquery/<?php echo FILE_JS ?>"></script>
	<title>SACoche - Documentation - <?php echo $TITRE; ?></title>
</head>
<body>
	<h1 id="titre_aide"><?php echo $TITRE; ?></h1>
	<div id="aide_en_ligne">
		<?php echo $CONTENU_PAGE; ?>
		<p />
		<div class="hc">
			<a href="javascript:window.close();"><img alt="Fermer l'aide." src="./_img/action/action_retourner.png" /> Fermer</a>
			<a href="javascript:window.print();"><img alt="Fermer l'aide." src="./_img/action/action_imprimer.png" /> Imprimer</a>
		</div>
		<div class="hc">
			<a href="javascript:history.go(-1);"><img alt="Page précédente." src="./_img/fleche_g1.gif" /> Précédente</a>	||
			<a href="javascript:history.go(1);">Suivante <img alt="Page suivante." src="./_img/fleche_d1.gif" /></a>
		</div>
	</div>
	<script type="text/javascript">
		var DOSSIER='public';
		var FICHIER='<?php echo $FICHIER ?>';
	</script>
</body>
</html>
