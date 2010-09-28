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

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}
if($_SESSION['SESAMATH_ID']==ID_DEMO){exit('Action désactivée pour la démo...');}

$image_style = (isset($_POST['image_style'])) ? clean_texte($_POST['image_style']) : '' ;
$texte_RR    = (isset($_POST['texte_RR']))    ? clean_symboles($_POST['texte_RR']) : '' ; // restriction alphabétique non imposée pour permettre - + etc.
$texte_R     = (isset($_POST['texte_R']))     ? clean_symboles($_POST['texte_R'])  : '' ; // restriction alphabétique non imposée pour permettre - + etc.
$texte_V     = (isset($_POST['texte_V']))     ? clean_symboles($_POST['texte_V'])  : '' ; // restriction alphabétique non imposée pour permettre - + etc.
$texte_VV    = (isset($_POST['texte_VV']))    ? clean_symboles($_POST['texte_VV']) : '' ; // restriction alphabétique non imposée pour permettre - + etc.
$color_NA    = (isset($_POST['color_NA']))    ? clean_texte($_POST['color_NA'])    : '' ;
$color_VA    = (isset($_POST['color_VA']))    ? clean_texte($_POST['color_VA'])    : '' ;
$color_A     = (isset($_POST['color_A']))     ? clean_texte($_POST['color_A'])     : '' ;

$longueur_NA = mb_strlen($color_NA);
$longueur_VA = mb_strlen($color_VA);
$longueur_A  = mb_strlen($color_A);
$test_jeu = (preg_match("/^[0-9a-z_-]+$/i", $image_style)) && (is_dir('./_img/note/'.$image_style)) ;
$test_txt = ($texte_RR!='') && ($texte_R!='') && ($texte_V!='') && ($texte_VV!='') && (mb_strlen($texte_RR)<4) && (mb_strlen($texte_R)<4) && (mb_strlen($texte_V)<4) && (mb_strlen($texte_VV)<4) ;
$test_NA  = (preg_match("/^\#[0-9a-f]{3,6}$/i", $color_NA)) && ($longueur_NA!=5) && ($longueur_NA!=6) ;
$test_VA  = (preg_match("/^\#[0-9a-f]{3,6}$/i", $color_VA)) && ($longueur_VA!=5) && ($longueur_VA!=6) ;
$test_A   = (preg_match("/^\#[0-9a-f]{3,6}$/i", $color_A))  && ($longueur_A !=5) && ($longueur_A !=6) ;

if( $test_jeu && $test_txt && $test_NA && $test_VA && $test_A )
{
	// Passer si besoin d'un code hexadécimal à 3 caractères vers un code hexadécimal à 6 caractères.
	if($longueur_NA==4) {$color_NA = '#'.$color_NA{1}.$color_NA{1}.$color_NA{2}.$color_NA{2}.$color_NA{3}.$color_NA{3};}
	if($longueur_VA==4) {$color_VA = '#'.$color_VA{1}.$color_VA{1}.$color_VA{2}.$color_VA{2}.$color_VA{3}.$color_VA{3};}
	if($longueur_A ==4) {$color_A  = '#'.$color_A{1} .$color_A{1} .$color_A{2} .$color_A{2} .$color_A{3} .$color_A{3} ;}
	// Mettre à jour la session + le css perso + la base
	$_SESSION['CSS_BACKGROUND-COLOR']['NA'] = $color_NA;
	$_SESSION['CSS_BACKGROUND-COLOR']['VA'] = $color_VA;
	$_SESSION['CSS_BACKGROUND-COLOR']['A']  = $color_A;
	$_SESSION['NOTE_IMAGE_STYLE']           = $image_style;
	$_SESSION['NOTE_TEXTE']['RR']           = $texte_RR;
	$_SESSION['NOTE_TEXTE']['R']            = $texte_R;
	$_SESSION['NOTE_TEXTE']['V']            = $texte_V;
	$_SESSION['NOTE_TEXTE']['VV']           = $texte_VV;
	actualiser_style_session();
	DB_STRUCTURE_modifier_parametres( array('css_background-color_NA'=>$color_NA,'css_background-color_VA'=>$color_VA,'css_background-color_A'=>$color_A,'note_image_style'=>$image_style,'note_texte_RR'=>$texte_RR,'note_texte_R'=>$texte_R,'note_texte_V'=>$texte_V,'note_texte_VV'=>$texte_VV) );
	echo'ok';
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
