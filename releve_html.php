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

// Fichier appelé pour l'affichage d'un relevé HTML enregistré temporairement.
// Passage en GET d'un paramètre pour savoir quelle page charger.

// Constantes / Configuration serveur / Autoload classes / Fonction de sortie
require('./_inc/_loader.php');

// Paramètre transmis ; attention à l'exploitation d'une vulnérabilité "include PHP" (http://www.certa.ssi.gouv.fr/site/CERTA-2003-ALE-003/)
$FICHIER = (isset($_GET['fichier'])) ? str_replace(array('/','\\'),'',$_GET['fichier']) : ''; // On ne nettoie pas le caractère "." car le paramètre peut le contenir.

// Fonctions
require(CHEMIN_DOSSIER_INCLUDE.'fonction_divers.php');

ob_start();
// Chargement de la page concernée
$filename_html = CHEMIN_DOSSIER_EXPORT.$FICHIER.'.html';
if(is_file($filename_html))
{
  require($filename_html);
}
else
{
  echo'<h2>Relevé manquant</h2>';
  echo'Les relevés sont conservés sur le serveur pendant une durée limitée...';
}
// Affichage dans une variable
$CONTENU_PAGE = ob_get_contents();
ob_end_clean();

// Extraire le css personnalisé pour le déplacer dans le head
$position_css = mb_strpos($CONTENU_PAGE,'</style>');
if($position_css)
{
  $CSS_PERSO    = mb_substr($CONTENU_PAGE,0,$position_css+8);
  $CONTENU_PAGE = mb_substr($CONTENU_PAGE,$position_css+8);
}
else
{
  $CSS_PERSO    = NULL;
}

// Titre du navigateur
$TITRE_NAVIGATEUR = 'SACoche - Relevé HTML';

// Fichiers à inclure
$tab_fichiers_head = array();
$tab_fichiers_head[] = array( 'css' , compacter('./_css/style.css','mini') );
$tab_fichiers_head[] = array( 'js'  , compacter('./_js/jquery-librairies.js','comm') ); // Ne pas minifier ce fichier qui est déjà un assemblage de js compactés : le gain est quasi nul et cela est souce d'erreurs
$tab_fichiers_head[] = array( 'js'  , compacter('./_js/script.js','pack') ); // La minification plante à sur le contenu de testURL() avec le message Fatal error: Uncaught exception 'JSMinException' with message 'Unterminated string literal.'

// Affichage de l'en-tête
declaration_entete( FALSE /*is_meta_robots*/ , TRUE /*is_favicon*/ , FALSE /*is_rss*/ , $tab_fichiers_head , $TITRE_NAVIGATEUR , $CSS_PERSO );
?>
<body>
  <?php echo $CONTENU_PAGE; ?>
  <script type="text/javascript">
    var PAGE='public_anti_maj_clock';
  </script>
</body>
</html>
