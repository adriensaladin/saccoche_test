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

/**
 * Fabrique, ou récupère si elle existe, une image png inclinée à 90° à partir du nom et du prénom de l'élève.
 * Pour tourner le texte de 90° il y a 2 solutions :
 * 1. mettre à 90 le 3e paramètre de imagettftext() ; problème -> il y a des décalages par exemple quand le prénom comporte un "y"
 * 2. utiliser imagerotate() ; problème -> on perd la transparence alpha car le 4e paramètre de imagerotate() ne passe pas sur les png
 * L'issue à consister à utiliser la méthode 2 en ajoutant ensuite imagealphablending() et imagesavealpha() [http://fr.php.net/manual/fr/function.imagerotate.php#64531]
 * Et il faut aussi appeler ces deux fonctions au chargement de l'image déjà créée...
 */

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
	// On commence par créer une image temporaire plus large et plus haute que nécessaire
	$taille_police  = 10;
	$interligne     = $taille_police*1.2;
	$hauteur_tmp    = $taille_police*2*$br_line;
	$largeur_tmp    = $taille_police*40;
	$image_tmp      = imagecreate($largeur_tmp,$hauteur_tmp);
	$couleur_fond   = imagecolorallocatealpha($image_tmp,255,204,204,127);
	$couleur_texte  = imagecolorallocate($image_tmp,0,0,0);
	$police         = './arial.ttf';
	// imagettftext() : 3e param = angle de rotation ; 4e et 5e param = coordonnées du coin inférieur gauche du premier caractère
	if($br_line==1)
	{
		$b_g_y_demande = $hauteur_tmp - 5 ;
		list($b_g_x , $b_g_y , $b_d_x , $b_d_y , $h_d_x , $h_d_y , $h_g_x , $h_g_y) = imagettftext($image_tmp,$taille_police,0,5,$b_g_y_demande,$couleur_texte,$police,$nom.' '.$prenom);
	}
	else
	{
		// en deux fois au lieu d'utiliser $nom."\r\n".$prenom car l'interligne est sinon trop important
		$b_g_y_demande = 5 + $interligne ;
		list($b_g_x1 , $delete , $b_d_x1 , $delete , $h_d_x1 , $h_d_y1 , $h_g_x1 , $h_g_y ) = imagettftext($image_tmp,$taille_police,0,5,$b_g_y_demande,$couleur_texte,$police,$nom);
		$b_g_y_demande += $interligne ;
		list($b_g_x2 , $b_g_y2 , $b_d_x2 , $b_d_y  , $h_d_x2 , $delete , $h_g_x2 , $delete) = imagettftext($image_tmp,$taille_police,0,5,$b_g_y_demande,$couleur_texte,$police,$prenom);
		$b_d_x = max($b_d_x1,$b_d_x2);
		$h_g_x = min($h_g_x1,$h_g_x2);
	}
	// Maintenant on peut connaître la hauteur requise et créer l'image finale aux bonnes dimensions
	$largeur_finale = $b_d_x - $h_g_x + 2 ; // +2 car 1px de marge en bordure
	$hauteur_finale = $b_d_y - $h_g_y + 2 ; // idem
	// Les caractères minuscules parmi g,j,p,q,y provoquent un décalage non pris en compte par imagettftext()
	// sur certains serveurs pour des librairies gd pourtant rigoureusement identiques (gd_info() renvoyant [GD Version] => bundled 2.0.34 compatible).
	// On peut lire qu'appeler avant imagealphablending() est censé réglé le problème [http://fr.php.net/manual/fr/function.imagettftext.php#100184], mais ce n'est pas le cas.
	// Enfin, sur les serveurs qui prennent en compte le décalage, il est d'1px trop grand.
	$test_pb_serveur = ($b_g_y_demande == $b_d_y) ? true : false ;
	$prenom_amoindri = str_replace( array('g','j','p','q','y') , '' , mb_substr($prenom,1) , $test_pb_lettres );
	if($test_pb_lettres && !$test_pb_serveur)
	{
		if($br_line==2) {$hauteur_finale -= 1;}
		if($br_line==1) {$h_g_y -= 1;}
	}
	elseif($test_pb_lettres && $test_pb_serveur)
	{
		if($br_line==2) {$hauteur_finale += 3;}
		if($br_line==1) {$h_g_y += 3;}
	}
	// Dans le cas d'une seule colonne, prévoir exactement 15px de hauteur (donc de largeur une fois tourné, ce qui centre et normalise la largeur de colonne)
	if($br_line==1)
	{
		$marge_complementaire = 15 - $hauteur_finale;
		if($marge_complementaire)
		{
			$arrondi_pair = ($test_pb_lettres) ? 1 : -1 ;
			$h_g_y -= ($marge_complementaire%2) ? ($marge_complementaire+$arrondi_pair)/2 : $marge_complementaire/2 ;
			$hauteur_finale = 15;
		}
	}
	$image_finale = imagecreate($largeur_finale,$hauteur_finale);
	// imagettftext() : 3e et 4e param = coordonnées du point de destination ; 5e et 6e param = coordonnées du point source
	imagecopy($image_finale,$image_tmp,0,0,$h_g_x-1,$h_g_y-1,$largeur_finale,$hauteur_finale);
	$image_finale = imagerotate($image_finale,90,0);
	imagealphablending($image_finale, true);
	imagesavealpha($image_finale, true); 
	imagepng($image_finale,$fichier);
	imagedestroy($image_tmp);
}
// Récupérer l'image si elle existe déjà
else
{
	$image_finale = imagecreatefrompng($fichier);
	imagealphablending($image_finale, true);
	imagesavealpha($image_finale, true); 
}

imagepng($image_finale);
imagedestroy($image_finale);
?>