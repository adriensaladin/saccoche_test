<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2009-2015
 * 
 * ****************************************************************************************************
 * SACoche <http://sacoche.sesamath.net> - Suivi d'Acquisitions de Comp�tences
 * � Thomas Crespin pour S�samath <http://www.sesamath.net> - Tous droits r�serv�s.
 * Logiciel plac� sous la licence libre Affero GPL 3 <https://www.gnu.org/licenses/agpl-3.0.html>.
 * ****************************************************************************************************
 * 
 * Ce fichier est une partie de SACoche.
 * 
 * SACoche est un logiciel libre ; vous pouvez le redistribuer ou le modifier suivant les termes 
 * de la �GNU Affero General Public License� telle que publi�e par la Free Software Foundation :
 * soit la version 3 de cette licence, soit (� votre gr�) toute version ult�rieure.
 * 
 * SACoche est distribu� dans l�espoir qu�il vous sera utile, mais SANS AUCUNE GARANTIE :
 * sans m�me la garantie implicite de COMMERCIALISABILIT� ni d�AD�QUATION � UN OBJECTIF PARTICULIER.
 * Consultez la Licence Publique G�n�rale GNU Affero pour plus de d�tails.
 * 
 * Vous devriez avoir re�u une copie de la Licence Publique G�n�rale GNU Affero avec SACoche ;
 * si ce n�est pas le cas, consultez : <http://www.gnu.org/licenses/>.
 * 
 */

// On pourrait essayer de verifier que l'appel est l�gitime. Mais :
// - on ne peut utiliser $_SERVER['HTTP_REFERER'] qui n'est pas une variable de confiance
// - ouvrir et v�rifier la session pour chaque image serait lourd
// - passer des param�tres suppl�mentaires (genre id session + rep�re temporel) complexifie pour pas grand chose
// Du coup j'ai laiss� tel quel. De toutes fa�ons les param�tres sont nettoy�s, et utiliser ce fichier depuis l'ext�rieur n'a pas vraiment d'int�r�t...

/**
 * Fabrique, ou r�cup�re si elle existe, une image png inclin�e � 90� � partir du nom et du pr�nom de l'�l�ve.
 * Pour tourner le texte de 90� il y a 2 solutions :
 * 1. mettre � 90 le 3e param�tre de imagettftext() ; probl�me -> il y a des d�calages par exemple quand le pr�nom comporte un "y"
 * 2. utiliser imagerotate() ; probl�me -> on perd la transparence alpha car le 4e param�tre de imagerotate() ne passe pas sur les png
 * Une issue � consister � utiliser la m�thode 2 en ajoutant ensuite imagealphablending() et imagesavealpha() [http://fr.php.net/manual/fr/function.imagerotate.php#64531]
 * Et il faut aussi appeler ces deux fonctions au chargement de l'image d�j� cr��e...
 * Mais un nouveau probl�me apparait : certains serveurs (celui de Montpellier par exemple) g�rent mal ces fonctions de transparence (pour des librairies GD identiques...).
 * Du coup, comme les images sont de toutes fa�ons toujours utilis�es sur un m�me fond (CCCCFF / 204:204:255), le plus simple (et la solution) a �t� de renoncer � utiliser la transparence.
 * Donc imagecolorallocatealpha() a �t� remplac� par imagecolorallocate() et imagealphablending($image_finale, false); + imagesavealpha($image_finale, true); ont �t� retir�s.
 */

// Ce fichier est directement appel� par le navigateur et n'inclut pas tous les fichiers habituels.
header("Content-type: image/png");

// Constantes / Configuration serveur / Autoload classes / Fonction de sortie
require('../../_inc/_loader.php');
// Non charg� par le loader dont on ne prend que le d�but
require(CHEMIN_DOSSIER_INCLUDE.'class.Clean.php');
require(CHEMIN_DOSSIER_INCLUDE.'class.Image.php');

$dossier   = isset($_GET['dossier']) ? Clean::entier($_GET['dossier']) : 'x' ;
$nom       = isset($_GET['nom'])     ? Clean::nom($_GET['nom'])        : ' ' ;
$prenom    = isset($_GET['prenom'])  ? Clean::prenom($_GET['prenom'])  : ' ' ;
$item      = isset($_GET['item'])    ? Clean::texte($_GET['item'])     : '' ;
$br_line   = isset($_GET['br'])      ? 2                               : 1 ; // 2 pour nom / retour � la ligne / pr�nom ; 1 pour nom / pr�nom � la suite
$font_size = isset($_GET['size'])    ? Clean::entier($_GET['size'])    : 10 ;

$chemin  = CHEMIN_DOSSIER_BADGE.$dossier.DS;
$debut   = ($item) ? $item : $nom.'_'.$prenom ;
$fichier = $chemin.Clean::login($debut).'_'.$br_line.'_'.$font_size.'.png';

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Cr�er l'image si elle n'existe pas
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if(!file_exists($fichier))
{
  // S'assurer que le dossier, lui, existe bien au moins, sinon ce n'est pas la peine de poursuivre
  if(!is_dir($chemin))
  {
    header('Status: 404 Not Found', true, 404);
    exit();
  }
  // On commence par cr�er une image temporaire plus large et plus haute que n�cessaire
  $interligne     = $font_size*1.2;
  $hauteur_tmp    = $font_size*2*$br_line;
  $largeur_tmp    = $font_size*40;
  // imagecreatetruecolor() n'est pas utilis� ici sinon imagecopy() utilis� ensuite peut faire apparaitre une bande noire...
  // $image_tmp      = function_exists('imagecreatetruecolor') ? imagecreatetruecolor($largeur_tmp,$hauteur_tmp) : imagecreate($largeur_tmp,$hauteur_tmp) ;
  $image_tmp      = imagecreate($largeur_tmp,$hauteur_tmp) ;
  $couleur_fond   = imagecolorallocate($image_tmp,221,221,255); // Le premier appel � imagecolorallocate() remplit la couleur de fond si imagecreate().
  $couleur_fill   = imagefill($image_tmp, 0, 0, $couleur_fond); // Si imagecreatetruecolor(), l'image est noire et il faut la remplir explicitement.
  $couleur_texte  = imagecolorallocate($image_tmp,0,0,0);
  $police         = './arial.ttf'; // Dans le m�me dossier que ce script.
  // imagettftext() : 3e param = angle de rotation ; 4e et 5e param = coordonn�es du coin inf�rieur gauche du premier caract�re
  if($br_line==1)
  {
    $texte = ($item) ? $item : $nom.' '.$prenom ;
    $b_g_y_demande = $hauteur_tmp - 5 ;
    list($b_g_x , $b_g_y , $b_d_x , $b_d_y , $h_d_x , $h_d_y , $h_g_x , $h_g_y) = imagettftext($image_tmp,$font_size,0,5,$b_g_y_demande,$couleur_texte,$police,$texte);
  }
  else
  {
    // en deux fois au lieu d'utiliser $nom."\r\n".$prenom car l'interligne est sinon trop important
    $b_g_y_demande = 5 + $interligne ;
    list($b_g_x1 , $delete , $b_d_x1 , $delete , $h_d_x1 , $h_d_y1 , $h_g_x1 , $h_g_y ) = imagettftext($image_tmp,$font_size,0,5,$b_g_y_demande,$couleur_texte,$police,$nom);
    $b_g_y_demande += $interligne ;
    list($b_g_x2 , $b_g_y2 , $b_d_x2 , $b_d_y  , $h_d_x2 , $delete , $h_g_x2 , $delete) = imagettftext($image_tmp,$font_size,0,5,$b_g_y_demande,$couleur_texte,$police,$prenom);
    $b_d_x = max($b_d_x1,$b_d_x2);
    $h_g_x = min($h_g_x1,$h_g_x2);
  }
  // Maintenant on peut conna�tre les dimensions de l'image finale
  $largeur_finale = $b_d_x - $h_g_x + 3 ; // +2 car 1px de marge en bordure
  $hauteur_finale = $b_d_y - $h_g_y + 2 ; // idem
  // Les caract�res minuscules parmi g,j,p,q,y provoquent un d�calage non pris en compte par imagettftext()
  // sur certains serveurs pour des librairies gd pourtant rigoureusement identiques (gd_info() renvoyant [GD Version] => bundled 2.0.34 compatible).
  // On peut lire qu'appeler avant imagealphablending() est cens� r�gl� le probl�me [http://fr.php.net/manual/fr/function.imagettftext.php#100184], mais un d�calage demeure.
  // Enfin, sur les serveurs qui prennent en compte le d�calage, il est d'1px trop grand.
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
  // Dans le cas d'une seule colonne, pr�voir exactement 15px de hauteur (donc de largeur une fois tourn�, ce qui centre le texte et normalise la largeur de la colonne).
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
  // Cr�er l'image finale aux bonnes dimensions
  $image_finale = function_exists('imagecreatetruecolor') ? imagecreatetruecolor($largeur_finale,$hauteur_finale) : imagecreate($largeur_finale,$hauteur_finale) ;
  // imagettftext() : 3e et 4e param = coordonn�es du point de destination ; 5e et 6e param = coordonn�es du point source
  imagecopy($image_finale,$image_tmp,0,0,$h_g_x-1,$h_g_y-1,$largeur_finale,$hauteur_finale);
  imagedestroy($image_tmp);
  // Tourner l'image de 90�
  $image_finale = Image::imagerotateEmulation($image_finale);
  // L'enregistrer comme fichier
  imagepng($image_finale,$fichier);
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// R�cup�rer l'image si elle existe d�j�
// ////////////////////////////////////////////////////////////////////////////////////////////////////

else
{
  $image_finale = imagecreatefrompng($fichier);
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Envoyer l'image au navigateur
// ////////////////////////////////////////////////////////////////////////////////////////////////////

imagepng($image_finale);
imagedestroy($image_finale);

?>