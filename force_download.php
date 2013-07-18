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

// Fichier appelé pour forcer le téléchargement d'un fichier txt | csv | xml (préférable à une ouverture dans le navigateur).
// Passage en GET d'un paramètre pour savoir quelle page charger.

// Constantes / Configuration serveur / Autoload classes / Fonction de sortie
require('./_inc/_loader.php');

// Paramètre transmis ; attention à l'exploitation d'une vulnérabilité "include PHP" (http://www.certa.ssi.gouv.fr/site/CERTA-2003-ALE-003/)
$FICHIER = (isset($_GET['fichier'])) ? str_replace(array('/','\\'),'',$_GET['fichier']) : ''; // On ne nettoie pas le caractère "." car le paramètre contient l'extension.

// Vérification de la cohérence du paramètre transmis et de l'existence du fichier concerné
if(!$FICHIER)
{
  exit_error( 'Paramètre manquant' /*titre*/ , 'Page appelée sans indiquer le nom du fichier à récupérer.' /*contenu*/ , '' /*lien*/ );
}
$fichier_chemin = CHEMIN_DOSSIER_EXPORT.$FICHIER;
if(!is_file($fichier_chemin))
{
  exit_error( 'Document manquant' /*titre*/ , 'Les fichiers sont conservés sur le serveur pendant une durée limitée !' /*contenu*/ , '' /*lien*/ );
}
$extension = strtolower(pathinfo($FICHIER,PATHINFO_EXTENSION));
if(!in_array($extension,array('csv','txt','xml')))
{
  exit_error( 'Paramètre incorrect' /*titre*/ , 'Le fichier demandé "'.html($FICHIER).'" a une extension interdite.' /*contenu*/ , '' /*lien*/ );
}

// Cette méthode pour forcer le téléchargement d'un fichier consomme des ressources serveur (par rapport à une banale rerirection).
// Ce n'est donc qu'à utiliser pour de petits fichiers txt ou csv ou xml dont on ne veut pas qu'ils s'ouvrent dans le navigateur.
// Remarque : il y a aussi la possibilité de les proposer zippés, mais cela complique la démarche de l'utilisateur.
header('Content-Description: File Transfer');
header('Content-Disposition: attachment; filename="'.$FICHIER.'"');
header('Content-Type: application/octet-stream'); // header('Content-Type: application/force-download'); // http://fr.php.net/manual/fr/function.readfile.php#70296
header('Content-Transfer-Encoding: binary');
header('Content-Length: '. filesize($fichier_chemin));
header('Pragma: no-cache');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0'); // IE n'aime pas "no-store" ni "no-cache".
header('Expires: 0');
ob_clean();
flush();
readfile($fichier_chemin);
exit();
?>