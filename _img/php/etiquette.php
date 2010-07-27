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

// Fabrique, ou récupère si elle existe, une image png inclinée à 90° à partir du nom et du prénom de l'élève

header("Content-type: image/png");

require_once('../../_inc/fonction_clean.php');

$dossier = isset($_GET['dossier']) ? $_GET['dossier'] : '' ;
$nom     = isset($_GET['nom'])     ? $_GET['nom']     : ' ' ;
$prenom  = isset($_GET['prenom'])  ? $_GET['prenom']  : ' ' ;
$br_line = isset($_GET['br'])      ? 2                : 1 ; // 2 pour nom/prénom sur 2 colonnes ; 1 pour nom prénom à la suite

$fichier = '../../__tmp/badge/'.clean_entier($dossier).'/'.clean_login($nom).'_'.clean_login($prenom).'_'.$br_line.'.png';

// Créer l'image si elle n'existe pas
if(!file_exists($fichier))
{
	// On commence par créer une image temporaire plus haute que nécessaire
	$taille_police  = 10;
	$largeur        = 15*$br_line;
	$hauteur_tmp    = $taille_police*40;
	$image_tmp      = imagecreate($largeur,$hauteur_tmp);
	$couleur_fond   = imagecolorallocatealpha($image_tmp,255,204,204,127);
	$couleur_texte  = imagecolorallocate($image_tmp,0,0,0);
	$police         = './arial.ttf';
	$tab = ($br_line==1) ? imagettftext($image_tmp,$taille_police,90,$largeur-4,$hauteur_tmp-2,$couleur_texte,$police,$nom.' '.$prenom)
	                     : imagettftext($image_tmp,$taille_police,90,$largeur/2-4,$hauteur_tmp-2,$couleur_texte,$police,$nom."\r\n".$prenom) ;
	// Maintenant on peut connaître la hauteur requise et créer l'image finale aux bonnes dimensions
	$hauteur_finale = $hauteur_tmp - $tab[3] + 2 ;
	$image_finale   = imagecreate($largeur,$hauteur_finale);
	imagecopy($image_finale,$image_tmp,0,0,0,$hauteur_tmp-$hauteur_finale,$largeur,$hauteur_finale);
	imagepng($image_finale,$fichier);
	imagedestroy($image_tmp);
}
// Récupérer l'image si elle existe déjà
else
{
	$image_finale = imagecreatefrompng($fichier);
}

imagepng($image_finale);
imagedestroy($image_finale);
?>